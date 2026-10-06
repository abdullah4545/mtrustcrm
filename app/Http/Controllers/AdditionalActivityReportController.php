<?php
namespace App\Http\Controllers;

use App\Exports\ActivityReportExport;
use App\Models\Activity;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class AdditionalActivityReportController extends Controller
{
    private const COLUMNS = [
        'serial'=>'Sl','date'=>'Date & Time','organization_name'=>'Name of Organ.','department'=>'Depart.',
        'contact_person'=>'Cont. Person','from_location'=>'From','to_location'=>'To','distance'=>'Dist',
        'vehicle'=>'Vehicles','work_details'=>'Vis. Output','ta'=>'TA','da'=>'DA','total'=>'Total','remarks'=>'Remarks',
        'status'=>'Status','payment_status'=>'Payment Status','tour_type'=>'Tour Type','created_by'=>'Created By',
    ];
    private const DEFAULT = ['serial','date','organization_name','department','contact_person','from_location','to_location','distance','vehicle','work_details','ta','da','total','remarks','status','payment_status','tour_type','created_by'];

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('permission:report.activity.view');
        $this->middleware('permission:report.view_all|report.view_branch|report.view_self');
    }
    public function tourIndex(){ return $this->indexView('tour'); }
    public function sortIndex(){ return $this->indexView('sort'); }
    private function indexView(string $mode){
        $q=DB::table('users')->select('id','name')->orderBy('name');
        if(!Auth::user()->can('staff.filter')) $q->whereRaw('1=0');
        if(!Auth::user()->can('report.view_all')) $q->where('id',Auth::id());
        return view('backend.content.activity.report.simple',[
            'mode'=>$mode,'users'=>$q->get(),'columns'=>self::COLUMNS,'defaultColumns'=>self::DEFAULT,
            'canViewStatus'=>Auth::user()->can('activity.status.view')
        ]);
    }
    public function tourData(Request $r){ return $this->data($r,'tour'); }
    public function sortData(Request $r){ return $this->data($r,'sort'); }
    private function data(Request $r,string $mode){
        $f=$this->filters($r,$mode); $cols=$this->columns($f['columns']??[]);
        $items=$this->query($f,$mode)->with(['creator','travels'])->orderBy('date')->orderBy('id')->get();
        return response()->json(['columns'=>collect($cols)->map(fn($c)=>['key'=>$c,'label'=>self::COLUMNS[$c]])->values(),
            'rows'=>$items->values()->map(fn($a,$i)=>$this->row($a,$cols,$i+1)),
            'summary'=>['count'=>$items->count(),'ta'=>number_format((float)$items->sum('ta'),2),'da'=>number_format((float)$items->sum('da'),2),'total'=>number_format((float)$items->sum('total'),2)]]);
    }
    public function tourPdf(Request $r){ return $this->pdf($r,'tour',false); }
    public function tourPrint(Request $r){ return $this->pdf($r,'tour',true); }
    public function sortPdf(Request $r){ return $this->pdf($r,'sort',false); }
    public function sortPrint(Request $r){ return $this->pdf($r,'sort',true); }
    private function pdf(Request $r,string $mode,bool $stream){
        $f=$this->filters($r,$mode); $cols=$this->columns($f['columns']??[]); $items=$this->query($f,$mode)->with(['creator','travels'])->orderBy('date')->orderBy('id')->get();
        $employee=!empty($f['created_by'])?DB::table('users')->where('id',$f['created_by'])->first():null;
        $pdf=Pdf::loadView('backend.content.activity.report.pdf',['activities'=>$items,'employee'=>$employee,'columns'=>$cols,'columnLabels'=>self::COLUMNS,
            'fromDate'=>!empty($f['from_date'])?Carbon::parse($f['from_date'])->format('d-m-Y'):null,'toDate'=>!empty($f['to_date'])?Carbon::parse($f['to_date'])->format('d-m-Y'):null,
            'totalTa'=>$items->sum('ta'),'totalDa'=>$items->sum('da'),'grandTotal'=>$items->sum('total'),'statusFilter'=>$f['status']??null])->setPaper('a4','landscape');
        $name=($mode==='tour'?'tour-report':'activity-sort-list').'-'.now()->format('Y-m-d-His').'.pdf';
        return $stream?$pdf->stream($name):$pdf->download($name);
    }
    public function tourExcel(Request $r){ return $this->excel($r,'tour'); }
    public function sortExcel(Request $r){ return $this->excel($r,'sort'); }
    private function excel(Request $r,string $mode){
        $f=$this->filters($r,$mode); if($mode==='tour') $f['tour_only']=true; $cols=$this->columns($f['columns']??[]);
        return Excel::download(new ActivityReportExport($f,$cols,self::COLUMNS),($mode==='tour'?'tour-report':'activity-sort-list').'-'.now()->format('Y-m-d-His').'.xlsx');
    }
    private function query(array $f,string $mode):Builder{
        return Activity::query()->when($mode==='tour',fn($q)=>$q->whereHas('travels',fn($t)=>$t->where('tour_type','tour')))
            ->when(!empty($f['from_date']),fn($q)=>$q->whereDate('date','>=',$f['from_date']))->when(!empty($f['to_date']),fn($q)=>$q->whereDate('date','<=',$f['to_date']))
            ->when(!empty($f['created_by']),fn($q)=>$q->where('created_by',$f['created_by']))->when(!empty($f['status']),fn($q)=>$q->where('status',$f['status']))
            ->when(!empty($f['payment_status']),fn($q)=>$q->where('payment_status',$f['payment_status']))->when(!empty($f['branch_id']),fn($q)=>$q->where('branch_id',$f['branch_id']));
    }
    private function filters(Request $r,string $mode):array{
        $rules=['from_date'=>['nullable','date'],'to_date'=>['nullable','date','after_or_equal:from_date'],'created_by'=>['nullable','integer'],'columns'=>['nullable','array'],'columns.*'=>['string',Rule::in(array_keys(self::COLUMNS))]];
        if($mode==='sort'){ $rules['status']=['nullable',Rule::in(['pending','approved','rejected'])]; $rules['payment_status']=['nullable',Rule::in(['unpaid','waiting_for_payment','paid'])]; }
        $f=$r->validate($rules); $u=Auth::user(); if(!$u->can('staff.filter')) unset($f['created_by']); if(!$u->can('activity.status.view')) unset($f['status']);
        if(!$u->can('report.view_all')){$f['branch_id']=$u->branch_id;$f['created_by']=$u->id;} return $f;
    }
    private function columns(array $c):array{ $v=array_values(array_intersect($c,array_keys(self::COLUMNS))); if(!Auth::user()->can('activity.status.view'))$v=array_values(array_diff($v,['status'])); return $v?:array_values(array_filter(self::DEFAULT,fn($x)=>$x!=='status'||Auth::user()->can('activity.status.view'))); }
    private function row(Activity $a,array $cols,int $n):array{ $r=[]; foreach($cols as $c)$r[$c]=match($c){'serial'=>$n,'date'=>($a->activity_at??$a->created_at??$a->date)?($a->activity_at??$a->created_at??$a->date)->copy()->timezone('Asia/Dhaka')->format('d M Y, h:i A'):'','ta','da','total'=>number_format((float)$a->{$c},2),'created_by'=>$a->creator?->name??'N/A','status'=>ucfirst((string)$a->status),'payment_status'=>ucwords(str_replace('_',' ',(string)$a->payment_status)),'tour_type'=>$a->travels->pluck('tour_type')->map(fn($v)=>$v==='tour'?'Tour':'Local Tour')->unique()->implode(', '),default=>$a->{$c}??''}; return $r; }
}

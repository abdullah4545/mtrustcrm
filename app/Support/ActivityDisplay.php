<?php
namespace App\Support;
use App\Models\Activity;

class ActivityDisplay
{
    public static function organization(Activity $a,bool $html=false): string
    {
        $name=$a->organization_name?:'-';
        $address=$a->organization?->address;
        return $html ? e($name).($address?'<br><small class="text-muted"><span aria-label="Location" style="font-family:DejaVu Sans,sans-serif">⌖</span> '.e($address).'</small>':'') : $name.($address?"\n📍 ".$address:'');
    }
    public static function route(Activity $a,bool $html=false): string
    {
        $rows=$a->travels;
        if($rows->isEmpty()) $rows=collect([(object)['entry_at'=>$a->activity_at,'vehicle'=>$a->vehicle,'from_location'=>$a->from_location,'to_location'=>$a->to_location,'distance'=>$a->distance]]);
        return $rows->map(function($r)use($a,$html){
            $time=($r->entry_at??$a->activity_at)?->copy()->timezone('Asia/Dhaka')->format('h:i A')??'';
            $distance=rtrim(rtrim(number_format((float)$r->distance,2,'.',''),'0'),'.').' km';
            if(!$html) return trim($time.' '.($r->vehicle?:'').' · '.($r->from_location?:'-').' → '.($r->to_location?:'-').' ('.$distance.')');
            return '<div class="crm-route mb-1"><small>'.e($time).'</small> <span class="badge bg-light text-dark">'.e($r->vehicle?:'-').'</span> <span>'.e($r->from_location?:'-').'</span> → <span>'.e($r->to_location?:'-').'</span> <span style="display:inline-block;border:1px solid #aab7c4;border-radius:50%;padding:4px 7px;white-space:nowrap">'.e($distance).'</span></div>';
        })->implode($html?'':"\n");
    }
}

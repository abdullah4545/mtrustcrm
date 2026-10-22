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
    public static function route(Activity $a,bool $html=false,bool $badges=false): string
    {
        $rows=$a->travels;
        if($rows->isEmpty()) $rows=collect([(object)['entry_at'=>$a->activity_at,'vehicle'=>$a->vehicle,'from_location'=>$a->from_location,'to_location'=>$a->to_location,'distance'=>$a->distance]]);
        return $rows->map(function($r)use($a,$html,$badges){
            $time=($r->entry_at??$a->activity_at)?->copy()->timezone('Asia/Dhaka')->format('h:i A')??'';
            $distance=rtrim(rtrim(number_format((float)$r->distance,2,'.',''),'0'),'.').' km';
            $text=trim($time.' '.($r->vehicle?:'-').' '.($r->from_location?:'-').' → '.($r->to_location?:'-').' '.$distance);
            if(!$html) return $text;
            if($badges) {
                return '<div class="crm-route-plain" style="display:block;white-space:nowrap;line-height:1.9">'.e($time).' <span class="badge bg-info-subtle text-info" style="background:#e8f4fc;color:#175b91">'.e($r->vehicle?:'-').'</span> '.e($r->from_location?:'-').' → '.e($r->to_location?:'-').' <span class="badge bg-success-subtle text-success" style="background:#e8f6ee;color:#167044">'.e($distance).'</span></div>';
            }
            return '<div class="crm-route-plain" style="display:block;white-space:nowrap;line-height:1.7">'.e($text).'</div>';
        })->implode($html?'':"\n");
    }
}

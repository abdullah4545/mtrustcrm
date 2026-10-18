<?php
namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PrintedActivityReport
{
    public static function view($activities, array $data)
    {
        $token = (string) Str::uuid();
        // Snapshot the complete printed dataset, never IDs supplied by the browser.
        Cache::put('printed-activity:'.$token, [
            'user_id' => auth()->id(), 'ids' => $activities->modelKeys(),
            'bill_versions'=>$activities->mapWithKeys(fn($a)=>[$a->id=>hash('sha256',implode('|',[$a->total,$a->claimed_total,$a->deducted_total,$a->updated_at?->format('Y-m-d H:i:s')]))])->all(),
        ], now()->addMinutes(30));
        return view('backend.content.activity.report.print', $data + [
            'activities' => $activities, 'printToken' => $token,
        ]);
    }
}

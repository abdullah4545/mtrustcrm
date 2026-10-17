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
        ], now()->addMinutes(30));
        return view('backend.content.activity.report.print', $data + [
            'activities' => $activities, 'printToken' => $token,
        ]);
    }
}

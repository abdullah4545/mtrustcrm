<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class GeoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:geo.view');
    }

    public function districts($divisionId)
    {
        return DB::table('districts')
            ->where('division_id', $divisionId)
            ->select('id','name')
            ->orderBy('name')
            ->get();
    }

    public function upazilas($districtId)
    {
        return DB::table('upazilas')
            ->where('district_id', $districtId)
            ->select('id','name')
            ->orderBy('name')
            ->get();
    }

    public function unions($upazilaId)
    {
        return DB::table('unions')
            ->where('upazila_id', $upazilaId)
            ->select('id','name')
            ->orderBy('name')
            ->get();
    }
}
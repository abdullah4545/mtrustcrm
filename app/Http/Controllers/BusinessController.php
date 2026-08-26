<?php

namespace App\Http\Controllers;
 
use App\Models\Business;
use Illuminate\Http\Request;

class BusinessController extends Controller
{

    public function __construct()
    { 
        $this->middleware('permission:business.manage')
        ->only(['index','update','seoindex','seoupdate','socialindex','socialupdate']);
    }
    public function index()
    {
        $business = Business::firstOrCreate(['id' => 1]);
        return view('backend.content.business.settings', compact('business'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'business_name' => ['required','string','max:255'],
            'business_email' => ['nullable','email','max:255'],
            'business_phone' => ['nullable','string','max:50'],
            'business_address' => ['nullable','string','max:255'],
            'timezone' => ['nullable','string','max:100'],
            'currency' => ['nullable','string','max:20'],
            'currency_symbol' => ['nullable','string','max:20'],
            'vat' => ['nullable','numeric','min:0'],
            'logo' => ['nullable','image','max:2048'],
            'fav_icon' => ['nullable','image','max:1024'],
        ]);

        $business = Business::firstOrCreate(['id' => 1]);

        $business->business_name = $request->business_name;
        $business->business_email = $request->business_email;
        $business->business_phone = $request->business_phone;
        $business->business_address = $request->business_address;

        $business->timezone = $request->timezone ?? 'Asia/Dhaka';
        $business->currency = $request->currency ?? 'BDT';
        $business->currency_symbol = $request->currency_symbol ?? '৳';
        $business->vat = $request->vat ?? 0;

        // ✅ Your exact image upload pattern (logo)
        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $path = 'public/images/business/';
            $image->move($path, $name);
            $business->logo = $path . $name;
        }

        // ✅ Your exact image upload pattern (fav_icon)
        if ($request->hasFile('fav_icon')) {
            $image = $request->file('fav_icon');
            $name = time() . '_favicon.' . $image->getClientOriginalExtension();
            $path = 'public/images/business/';
            $image->move($path, $name);
            $business->fav_icon = $path . $name;
        }

        $business->save();

        return response()->json([
            'status' => true,
            'message' => 'Business settings updated successfully.',
            'data' => $business
        ]);
    }

    public function seoindex()
    {
        $business = Business::firstOrCreate(['id' => 1]);
        return view('backend.content.business.seo', compact('business'));
    }

    public function seoupdate(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'meta_image' => 'nullable|image|max:2048',
        ]);

        $business = Business::firstOrCreate(['id' => 1]);

        $business->title = $request->title;
        $business->meta_title = $request->meta_title;
        $business->meta_description = $request->meta_description;
        $business->meta_keywords = $request->meta_keywords;
 
        if ($request->hasFile('meta_image')) {
            $image = $request->file('meta_image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $path = 'public/images/business/';
            $image->move($path, $name);
            $business->meta_image = $path . $name;
        }

        $business->update();

        return response()->json([
            'status' => true,
            'message' => 'SEO settings updated successfully',
            'data' => $business
        ]);
    }

    public function socialindex()
    {
        $business = Business::firstOrCreate(['id' => 1]);
        return view('backend.content.business.social', compact('business'));
    }


    public function socialupdate(Request $request)
    {
        $request->validate([
            'facebook'  => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'twitter'   => 'nullable|string|max:255',
            'linkedin'  => 'nullable|string|max:255',
            'youtube'   => 'nullable|string|max:255',
            'whatsapp'  => 'nullable|string|max:255',
            'tiktok'    => 'nullable|string|max:255',
            'pinterest' => 'nullable|string|max:255',
        ]);

        $business = Business::firstOrCreate(['id' => 1]);

        $business->facebook  = $request->facebook;
        $business->instagram = $request->instagram;
        $business->twitter   = $request->twitter;
        $business->linkedin  = $request->linkedin;
        $business->youtube   = $request->youtube;
        $business->whatsapp  = $request->whatsapp;
        $business->tiktok    = $request->tiktok;
        $business->pinterest = $request->pinterest;

        $business->save();

        return response()->json([
            'status' => true,
            'message' => 'Social settings updated successfully',
            'data' => $business
        ]);
    }

}

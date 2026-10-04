<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
class ProfileController extends Controller {
 public function __construct(){ $this->middleware('auth'); $this->middleware('permission:profile.view')->only('edit'); $this->middleware('permission:profile.update')->only('update'); $this->middleware('permission:profile.change_password')->only('updatePassword'); }
 public function edit(Request $r){ return view('backend.content.profile.edit',['user'=>$r->user()]); }
 public function update(Request $r){ $u=$r->user(); $d=$r->validate(['name'=>'required|string|max:255','email'=>['nullable','email','max:255',Rule::unique('users','email')->ignore($u->id)],'phone'=>'nullable|string|max:30','present_address'=>'nullable|string|max:500','parmanent_address'=>'nullable|string|max:500','profile'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:2048']); if($r->hasFile('profile')){$dir=public_path('uploads/users'); if(!is_dir($dir))mkdir($dir,0775,true);$f=$r->file('profile');$n='profile_'.$u->id.'_'.time().'.'.$f->getClientOriginalExtension();$f->move($dir,$n);$d['profile']='public/uploads/users/'.$n;} $u->update($d); return back()->with('success','Profile updated successfully.'); }
 public function updatePassword(Request $r){$d=$r->validate(['current_password'=>'required|current_password','password'=>'required|string|min:8|confirmed']);$r->user()->update(['password'=>Hash::make($d['password'])]);return back()->with('success','Password changed successfully.');}
}

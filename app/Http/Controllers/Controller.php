<?php

namespace App\Http\Controllers;

use App\Models\pay;
use App\Models\User;
use App\Exports\PaysExport;
use App\Imports\UsersImport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function login(Request $request){
        if(!empty($request->remember_token)){
            $remember = $request->remember_token;
        }else{
            $remember = null;
        }
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials,$remember)) {
            $request->session()->regenerate();
            if (Auth::check()) {
                if(Auth::user()->roles == '0'){
                    return redirect(route('index')); 
                }else{
                    return redirect()->route('logout');
                }
            } else {
                return redirect(route('index'));
            }  
        }else{
            return redirect(route('index'));
        }
    }
    public function logout(){
        Auth::logout();
        return redirect(route('index'));
    }

    public function index(){
        return view('welcome');
    }

    public function insertAdmin(){
        User::updateOrCreate([
            'name'=>'admin',
            'email'=>'admin@gmail.com',
        ],[
            'password'=>Hash::make(12345678),
            'department'=>'0',
            'roles'=>'0',
        ]);
        return redirect()->route('index');
    }

    public function insertUser(Request $request){
        $name = $request->name_user;
        $department = $request->department_user;

        User::create([
            'name'=>$name,
            'department'=>$department,
            'roles'=>'1',
        ]);
        return redirect()->route('index');
    }

    public function insertPay(Request $request){
        $department = $request->department;

        for($p = 0; $p < count($department); $p++){
            $lst_user = User::where('department', $department[$p])->get('id');
            if ($request->checkdel) {
                User::where('department', $department[$p])->delete();
            }else{
                foreach ($lst_user as $itm_lstUser) {
                    pay::updateOrCreate([
                        'id_user'=>$itm_lstUser->id
                    ]);
                }
            }
        }
        if ($request->checkdel) {
            return redirect()->route('index');
        }
        $sum = pay::sum('spending');
        if($sum > 0){
            $lstPay = pay::all();
            $count = $lstPay->count();
            $max = pay::max('spending');
            $infoMax = DB::table('users')->join('pays', 'users.id', '=', 'pays.id_user')
                ->where('pays.spending', $max)
                ->select('users.department','users.name','pays.id')
                ->get();
            foreach ($infoMax as $info) {
                $name = $info->name;
                $id = $info->id;
                break;
            }
            $share = ceil($sum/$count);
            foreach ($lstPay as $value) {
                $dk = $value->spending-$share;
                if ($value->id == $id && $dk > 0) {
                    pay::where('id',$value->id)->update(['price'=>$share,'status'=>'1']);
                }elseif ($value->id != $id && $dk > 0) {
                    pay::where('id',$value->id)->update(['price'=>$share,'status'=>'Nhận từ '.$name.'  '.number_format($dk)]);
                }elseif ($dk < 0) {
                    pay::where('id',$value->id)->update(['price'=>$share,'status'=>'Gửi cho '.$name.'  '.number_format(trim($dk, "-"))]);
                }elseif ($dk == 0) {
                    pay::where('id',$value->id)->update(['price'=>$share,'status'=>'Không tính tiền']);
                }
            }
        }
        return redirect()->route('index');
    }

    public function insertSpending(Request $request){
        if ($request->spending == null) { $spending = 0; }else{ $spending = trim($request->spending); }
        $pay = pay::find($request->id);
        $pay->spending = $spending;
        if($request->hasFile('img_qrcode')){
            $file = $request->file('img_qrcode');
            $extension = $file ->getClientOriginalExtension();
            $name = $request->id.'.'.$extension;
            Storage::delete('public/'.$name);
            // Storage::delete('public/'.$request->id);
            Storage::putFileAs('public', $file, $name);
            $pay->img = $name;
        };
        $pay->save();
        
        $sum = pay::sum('spending');
        if($sum > 0){
            $lstPay = pay::all();
            $count = $lstPay->count();
            $max = pay::max('spending');
            $infoMax = DB::table('users')->join('pays', 'users.id', '=', 'pays.id_user')
                ->where('pays.spending', $max)
                ->select('users.department','users.name','pays.id')
                ->get();
            foreach ($infoMax as $info) {
                $name = $info->name;
                $id = $info->id;
                break;
            }
            $share = ceil($sum/$count);
            foreach ($lstPay as $value) {
                $dk = $value->spending-$share;
                if ($value->id == $id && $dk > 0) {
                    pay::where('id',$value->id)->update(['price'=>$share,'status'=>'1']);
                }elseif ($value->id != $id && $dk > 0) {
                    pay::where('id',$value->id)->update(['price'=>$share,'status'=>'Nhận từ '.$name.'  '.number_format($dk)]);
                }elseif ($dk < 0) {
                    pay::where('id',$value->id)->update(['price'=>$share,'status'=>'Gửi cho '.$name.'  '.number_format(trim($dk, "-"))]);
                }elseif ($dk == 0) {
                    pay::where('id',$value->id)->update(['price'=>$share,'status'=>'Không tính tiền']);
                }
            }
        }else{
            pay::where('spending',0)->update(['price'=>0]);
        }
        return redirect()->route('index');

    }

    public function checkPay(Request $request){
        $pay = pay::where('id',$request->id)->get();
        foreach($pay as $p){
            if($p->status != '1'){
                pay::where('id',$request->id)->update([
                    'status'=>'1'
                ]);
            }
        }
        return redirect()->route('index');
    }

    public function truncatePay(){
        pay::truncate();
        return redirect()->route('index');
    }

    public function destroyMemberPay(Request $request){
        pay::where('id',$request->id)->delete();

        $sum = pay::sum('spending');
        if($sum > 0){
            $lstPay = pay::all();
            $count = $lstPay->count();
            $max = pay::max('spending');
            $infoMax = DB::table('users')->join('pays', 'users.id', '=', 'pays.id_user')
                ->where('pays.spending', $max)
                ->select('users.department','users.name','pays.id')
                ->get();
            foreach ($infoMax as $info) {
                $name = $info->name;
                $id = $info->id;
                break;
            }
            $share = ceil($sum/$count);
            foreach ($lstPay as $value) {
                $dk = $value->spending-$share;
                if ($value->id == $id && $dk > 0) {
                    pay::where('id',$value->id)->update(['price'=>$share,'status'=>'1']);
                }elseif ($value->id != $id && $dk > 0) {
                    pay::where('id',$value->id)->update(['price'=>$share,'status'=>'Nhận từ '.$name.'  '.number_format($dk)]);
                }elseif ($dk < 0) {
                    pay::where('id',$value->id)->update(['price'=>$share,'status'=>'Gửi cho '.$name.'  '.number_format(trim($dk, "-"))]);
                }elseif ($dk == 0) {
                    pay::where('id',$value->id)->update(['price'=>$share,'status'=>'Không tính tiền']);
                }
            }
        }
        return redirect()->route('index');
    }

    public function export() 
    {
        return Excel::download(new PaysExport, 'pays.xlsx');
    }

    public function import(Request $request){
        Excel::import(new UsersImport, $request->file_user);
        return redirect()->route('index');
    }
}

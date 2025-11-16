<?php

namespace App\Http\Controllers;

use App\Models\CaptchaSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CaptchaSettingsController extends Controller
{
    public function index(){
        $data['modules'] = ['setup/add-captcha.js'];
        return view('captcha-setting/captcha-setting', $data);
    }
    public function createCaptcha(Request $request){
        $data=$request->all();
        $captcha = new CaptchaSettings();
        
        $userId = Auth::id();
        if(isset($data['id'])){
            $captcha = CaptchaSettings::find($data['id']);
            if($captcha){
                $captcha->user_id = $userId;
                $captcha->current_date=$data['current_date'];
                $captcha->service_name = $data['service_name'];
                $captcha->api_key = $data['api_key'];
                $captcha->status = isset($data['status']) ? 1 : 0;
                $captcha->save();             
                return redirect()->route('captchaSetting')->with('success', 'Captcha created successfully');
            } else {
                return redirect()->route('captchaSetting')->with('error', 'Captcha not found');
            }
        }
        $captcha->user_id = $userId;
        $captcha->current_date=$data['current_date'];
        $captcha->service_name = $data['service_name'];
        $captcha->api_key = $data['api_key'];
       $captcha->status = isset($data['status']) ? 1 : 0;
        $captcha->save();
      return redirect()->route('captchaSetting')->with('success', 'Captcha created successfully');
           
    }
    public function getCaptchaSettingData(){
        $captchaSettings = CaptchaSettings::where('user_id', auth()->id())->get();
        return datatables()->of($captchaSettings)->make(true);
    }
    public function deleteCaptchaSettingData($id){
       $captchaSetting = CaptchaSettings::find($id);
        if (!$captchaSetting) {
            return redirect()->route('captchaSetting')->with('error', 'Captcha Setting not found');
        }

        // if (Auth::user()->role !== 'admin' && $proxy->user_id !== Auth::id()) {
        //     return response()->json(['success' => false, 'message' => 'Access denied']);
        // }

        $captchaSetting->delete();
        return response()->json(['success' => true, 'message' => 'Captcha Setting deleted successfully']);
    }
    public function fetchCaptchaSettingData($id){
        $captcha = CaptchaSettings::find($id);
        if($captcha){
            return response()->json([
                'success' => true,
                'data' => $captcha
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Captcha settings not found'
            ]); 
        }
    }
}

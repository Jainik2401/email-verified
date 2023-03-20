<?php

namespace App\Http\Controllers;

use Session;
use App\Models\User;
use App\Models\Referral;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function loadSignup()
    {
        return view('register');
    }
    public function Signup(Request $Request)
    {
        $Request->validate([
            'name' => 'required|min:2|max:50',
            'email' => 'required|min:2|max:50|email|unique:users',
            'password' => 'required|min:2|max:50'
        ]);

        $referral_code = Str::random(6);
        $remember_token = Str::random(25);

        if (isset($Request->referral)) {
            $user = User::where('referral_code', $Request->referral)->get();
            if (count($user) > 0) {
                $user_id = User::insertGetId([
                    'name' => $Request->name,
                    'email' => $Request->email,
                    'password' => Hash::make($Request->password),
                    'referral_code' => $referral_code,
                    'remember_token' => $remember_token
                ]);
                Referral::insert([
                    'referral_code' => $user[0]['referral_code'],
                    'user_id' => $user_id,
                    'parent_user_id' => $user[0]['id']
                ]);
            } else {
                return redirect('/signin');
                // return back()->with('error', 'Enter valied referral code!');
            }
        } else {
            User::insert([
                'name' => $Request->name,
                'email' => $Request->email,
                'password' => Hash::make($Request->password),
                'referral_code' => $referral_code,
                'remember_token' => $remember_token
            ]);
        }
        $domain = URL::to('/');
        //referral link 
        $url = $domain . '/referralregister?ref=' . $referral_code;

        $data['url'] = $url;
        $data['name'] = $Request->name;
        $data['email'] = $Request->email;
        $data['password'] = $Request->password;
        Mail::send('registerMail', ['data' => $data], function ($message) use ($data) {
            $message->to($data['email'])->subject('Registered!!');
        });
        //verification
        $url = $domain . '/email-verification/' . $remember_token;

        $data['url'] = $url;
        $data['name'] = $Request->name;
        $data['email'] = $Request->email;
        Mail::send('verifiedMail', ['data' => $data], function ($message) use ($data) {
            $message->to($data['email'])->subject('Verification!!');
        });
        return redirect('/signin');
        // return back()->with('success', 'Your regisration has been successfull!');
    }
    public function loadReferral(Request $Request)
    {
        if (isset($Request->ref)) {
            $referral = $Request->ref;
            $userdata = User::where('referral_code', $referral)->get();
            if (count($userdata) > 0) {
                return view('referralregister', compact('referral'));
            } else {
                return view('404');
            }
        } else {
            return redirect('/');
        }
    }
    public function verifiedUser(Request $Request, $token)
    {
        $userdata = User::where('remember_token', $token)->get();

        if (count($userdata) > 0) {
            if ($userdata[0]['is_verified'] == 1) {
                return view('verified', ['message' => 'Your mail is already verified!!']);
            }
            User::where('id', $userdata[0]['id'])->update([
                'is_verified' => 1,
                'email_verified_at' => date('Y-m-d H:i:s')
            ]);
            return view('verified', ['message' => 'Your ' . $userdata[0]['email'] . 'is successfully verified!!']);
        } else {
            return view('verified', ['message' => '404 Not found!!']);
        }
    }
}
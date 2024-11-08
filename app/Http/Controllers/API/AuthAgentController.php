<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAgent;

class AuthAgentController extends Controller
{

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        if(UserAgent::where('email',$request->email)->count() <= 0 ) return response(['message' => 'email salah']);

        $customer = UserAgent::where('email',$request->email)->with('agents')->first();

        if(password_verify($request->password,$customer->password)){
            $accessToken =  $customer->createToken('authToken',['agent'])->accessToken; 

            return response(['message' => 'ok', 'data' => $customer, 'access_token' => $accessToken]);
        } else {
            return response(['message' => 'password salah']);
        }
        
    }
}
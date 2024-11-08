<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserCustomer;
//use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseFormatter;

class AuthCustomerController extends Controller
{
    /*protected $guard = 'api-customer';

    public function guard()
    {
        return auth()->guard('api-customer');
    }*/

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        if(UserCustomer::where('email',$request->email)->count() <= 0 ) return response(['message' => 'email salah']);

        $customer = UserCustomer::where('email',$request->email)->with('customers')->first();

        if(password_verify($request->password,$customer->password)){
            $accessToken =  $customer->createToken('authToken',['customer'])->accessToken; 

            //$accessToken = auth()->user()->createToken('authToken')->accessToken;
            
            $permissions = DB::select("select feature_name
            from customer_features
            where customer_id=".$customer->customer_id." and is_active='Y' 
            order by feature_name asc");

            return response([
                'message' => 'ok', 
                'data' => $customer, 
                'permission' => $permissions,
                'access_token' => $accessToken
            ]);
        } else {
            return response(['message' => 'password salah']);
        }

        //$user = Auth::guard('api-customer');
        /*if (Auth::guard('api-customer')->attempt(['email' => $request->email, 'password' => $request->password, 'approved' => 1])){
            //config(['auth.guards.api.provider' => 'user_customers']);
            
            $user = UserCustomer::select('user_customers.*')->find(auth()->guard('api-customer')->user()->id);
            //$success = $user;
            //dd(bcrypt($request->password));
            $accessToken =  $user->createToken('authToken',['customer'])->accessToken; 

            //$accessToken = auth()->user()->createToken('authToken')->accessToken;

            return response(['message' => 'ok', 'data' => $user, 'access_token' => $accessToken]);
        }else{
            return ResponseFormatter::error('', 'Email atau password salah','400');
        }*/

        
    }
}
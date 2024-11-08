<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseFormatter;
//logout
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'nik' => 'required|max:20',
            'email' => 'email|required|unique:users',
            'password' => 'required|confirmed'
        ]);

        $validatedData['password'] = Hash::make($request->password);

        $user = User::create($validatedData);

        $accessToken = $user->createToken('authToken')->accessToken;

        return response(['data' => $user, 'access_token' => $accessToken], 201);
    }

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'nik' => 'required',
            'password' => 'required'
        ]);

        if (!auth()->attempt(['nik' => $request->nik, 'password' => $request->password, 'approved' => 1])){
        //if (!auth()->attempt($loginData)) {
            //return response(['message' => 'This User does not exist, check your details'], 400);
            return response([
                'message' => 'failed', 
                'data' => 'NIK atau password salah'
            ]);
        }

        $accessToken = auth()->user()->createToken('authToken',['admin'])->accessToken;
        
        $permissions = DB::select("select E.title
        from users A
        join role_user B on A.id=B.user_id
        join roles C on B.role_id=C.id
        join permission_role D on C.id = D.role_id
        join permissions E on E.id = D.permission_id
        where A.nik='".$request->nik."'");

        return response([
            'message' => 'ok', 
            'data' => auth()->user(), 
            'permission' => $permissions,
            'access_token' => $accessToken
        ]);
    }

    public function logout(Request $request){
        $user = Auth::user()->token();
        $tokens = $user->tokens->pluck('id');
        Token::whereIn('id', $tokens)
            ->update(['revoked', true]);

        RefreshToken::whereIn('access_token_id', $tokens)->update(['revoked' => true]);
    }
}
<?php

namespace App\Http\Controllers\API\Frontend;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\UserAgent;
use App\Models\UserCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail; //send e-mail

class FrontResetPasswordController extends Controller
{
    protected $modelAgent, $modelCustomer, $chars;
    public function __construct(
        UserAgent $modelAgent,
        UserCustomer $modelCustomer
    )
    {
        $this->modelAgent = $modelAgent;
        $this->modelCustomer = $modelCustomer;
        $this->chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }

    public function generate_string($input, $strength = 16) {
        $input_length = strlen($input);
        $random_string = '';
        for($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
    
        return $random_string;
    }

    public function store(Request $request)
    {
        //generate random password
        $password = $this->generate_string($this->chars,10);
        $data['email'] = $request->email;
        $data['password'] = Hash::make($password);

        //check if user as agent or customer
        $user = $this->modelAgent->where('email', $request->email)->where('approved',1)->first();
        if (!$user) {
            $user = $this->modelCustomer->where('email', $request->email)->where('approved',1)->first();
            if(!$user){
                return ResponseFormatter::error([],'Data email tidak ditemukan atau tidak aktif','403');
            }else{
                //customer
                $object = $this->modelCustomer->findOrFail($user->id);
                $object->fill($data);
                $object->save();
            }
        }else{
            //agent
            $object = $this->modelAgent->findOrFail($user->id);
            $object->fill($data);
            $object->save();
        }

        //send email
        $body = [
            'title' => 'Reset Password',
            'password' => $password
        ];
        
        Mail::to("yudhatp@gmail.com")->send(new \App\Mail\ResetPasswordMail($body));
        

        /*$data = $this->model->create($request->all());
        if($data){
            return ResponseFormatter::success($data,'OK');
        }else{
            return ResponseFormatter::success('','OK');
        }*/
    }
}
<?php

namespace App\Services;

class BaseService
{
    //protected $authUser;
    //protected $authUserId;
    //protected $lintasConfig;

    //protected $env;
    //protected $productionEnv = 'production';
    public function __construct()
    {
        //$this->lintasConfig = config('lintas');
        //$this->env = config('app.env');
        
        // if (\Auth::check()) {
        // $this->authUser         = auth()->user();
        // $this->authUserId       = $this->authUser->id;
        // }
    }

    public function responseMessage($message, $statusCode, $isConfirm = false)
    {
        return response()->json(
            [
                "message" => $message,
                "is_confirm" => $isConfirm
            ],
            $statusCode
        );
    }
}

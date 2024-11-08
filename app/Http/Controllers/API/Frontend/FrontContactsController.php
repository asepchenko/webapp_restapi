<?php

namespace App\Http\Controllers\API\Frontend;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ComproContact;
use Illuminate\Http\Request;

class FrontContactsController extends Controller
{
    protected $model;
    public function __construct(ComproContact $model)
    {
        $this->model = $model;
    }

    public function store(Request $request)
    {
        $data = $this->model->create($request->all());
        if($data){
            return ResponseFormatter::success($data,'OK');
        }else{
            return ResponseFormatter::success('','OK');
        }
    }
}
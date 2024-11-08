<?php

namespace App\Http\Controllers\API\Frontend;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ComproBanner;
use Illuminate\Http\Request;

class FrontBannersController extends Controller
{
    protected $model;
    public function __construct(ComproBanner $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $today = date('Y-m-d');
        //->skip(0)
        //->take(1)
        $datas = $this->model
                    ->where('start_date','<=',$today)
                    ->where('end_date','>=',$today)
                    ->get();
        return ResponseFormatter::success($datas,'OK');
    }
}
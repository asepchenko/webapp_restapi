<?php

namespace App\Http\Controllers\API\Frontend;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ComproMainBanner;
use Illuminate\Http\Request;

class FrontMainBannersController extends Controller
{
    protected $model;
    public function __construct(ComproMainBanner $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $datas = $this->model->select('sequence','title','subtitle','image')
                    ->orderBy('sequence','asc')
                    ->get();
        return ResponseFormatter::success($datas,'OK');
    }
}
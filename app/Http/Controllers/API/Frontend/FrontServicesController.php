<?php

namespace App\Http\Controllers\API\Frontend;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ComproService;
use Illuminate\Http\Request;

class FrontServicesController extends Controller
{
    protected $model;
    public function __construct(ComproService $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $datas = $this->model->where('is_active','Y')->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function show($id)
    {
        $datas = $this->model->firstOrFail($id);
        return ResponseFormatter::success($datas,'OK');
    }
}
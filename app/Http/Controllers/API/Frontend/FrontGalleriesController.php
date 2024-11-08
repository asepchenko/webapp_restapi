<?php

namespace App\Http\Controllers\API\Frontend;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ComproGallery;
use Illuminate\Http\Request;

class FrontGalleriesController extends Controller
{
    protected $model;
    public function __construct(ComproGallery $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $datas = $this->model->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function show($id)
    {
        $datas = $this->model->firstOrFail($id);
        return ResponseFormatter::success($datas,'OK');
    }
}
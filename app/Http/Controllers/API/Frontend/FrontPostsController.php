<?php

namespace App\Http\Controllers\API\Frontend;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ComproPost;
use Illuminate\Http\Request;

class FrontPostsController extends Controller
{
    protected $model;
    public function __construct(ComproPost $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $datas = $this->model
                    ->where('status','PUBLISH')
                    ->whereNotNull('published_at')
                    ->orderBy('published_at','desc')
                    ->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function latestPost(Request $request)
    {
        $datas = $this->model
                    ->where('status','PUBLISH')
                    ->whereNotNull('published_at')
                    ->orderBy('published_at','desc')
                    ->skip(0)
                    ->take(3)
                    ->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function show($slug)
    {
        $datas = $this->model->where('slug',$slug)->get();
        if(count($datas)){
            return ResponseFormatter::success($datas[0],'OK');
        }else{
            return ResponseFormatter::error('','Data Not Found','404');
        }
    }
}
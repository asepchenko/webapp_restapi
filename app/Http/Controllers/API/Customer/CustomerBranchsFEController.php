<?php

namespace App\Http\Controllers\API\Customer;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\CustomerBranch;
use Illuminate\Http\Request;

class CustomerBranchsFEController extends Controller
{
    protected $model;
    public function __construct(CustomerBranch $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $datas = $this->model->with('cities','brands')
                    ->where('customer_id',auth()->user()->customer_id)
                    ->get();
        return ResponseFormatter::success($datas,'OK');
    }
}
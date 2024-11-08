<?php

namespace App\Http\Controllers\API\Customer;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Repositories\InvoiceRepository;
use Illuminate\Http\Request;

//store
use App\Services\InvoiceService;

class CustomerInvoiceController extends Controller
{
    protected $model, $repo, $service;
    public function __construct(
        InvoiceRepository $repo,
        Invoice $model,
        InvoiceDetail $modelDetail,
        InvoiceService $service
    )
    {
        $this->repo = $repo;
        $this->model = $model;
        $this->modelDetail = $modelDetail;
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $datas = $this->model->where('customer_id',auth()->user()->customer_id)
                    ->whereNotIn('last_status',['Draft'])
                    ->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function detail($no)
    {
        $datas = $this->repo->showByCustID($no,auth()->user()->customer_id);
        /*$datas = $this->model->with('details','customers')
                    ->where('customer_id',auth()->user()->customer_id)
                    ->where('invoice_number',$no)
                    ->first();*/
        return ResponseFormatter::success($datas,'OK');
    }

    public function listDetail($no)
    {
        $datas = $this->modelDetail->with('orders')
                    ->where('invoice_number',$no)
                    ->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function store(Request $request)
    {
        return $this->service->payment($request, $request->all());
    }

    public function accept(Request $request)
    {
        return $this->service->accept($request, $request->all());
    }
}
<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    protected $service;
    public function __construct(InvoiceService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $datas = $this->service->index($request);
        return $datas;
    }

    public function list(Request $request)
    {
        $datas = $this->service->list($request);
        return $datas;
    }

    public function orderListByCustomerID($customer_id)
    {
        $datas = $this->service->orderListByCustomerID($customer_id);
        return $datas;
    }

    public function orderList(Request $request)
    {
        $datas = $this->service->orderList($request);
        return $datas;
    }

    public function show($id)
    {
        return $this->service->show($id);
    }

    public function store(Request $request)
    {
        return $this->service->create($request, $request->all());
    }

    public function update(Request $request, $id)
    {
        return $this->service->update($request, $request->all(), $id);
    }

    public function destroy($id)
    {
        return $this->service->destroy($id);
    }

    public function closing(Request $request)
    {
        return $this->service->closing($request, $request->all());
    }

    public function verify(Request $request)
    {
        return $this->service->verify($request, $request->all());
    }

    public function accept(Request $request)
    {
        return $this->service->accept($request, $request->all());
    }

    public function payment(Request $request)
    {
        return $this->service->payment($request, $request->all());
    }
}
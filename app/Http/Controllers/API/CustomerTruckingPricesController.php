<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\CustomerTruckingPriceService;
use Illuminate\Http\Request;

class CustomerTruckingPricesController extends Controller
{
    protected $service;
    public function __construct(CustomerTruckingPriceService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $datas = $this->service->index($request);
        return $datas;
    }

    public function indexPending(Request $request)
    {
        $datas = $this->service->indexPending($request);
        return $datas;
    }

    public function indexByCustomerID($id)
    {
        $datas = $this->service->indexByCustomerID($id);
        return $datas;
    }
    
    public function getTruckingPriceRates($customer, $origin, $destination, $truck)
    {
        $datas = $this->service->getTruckingPriceRates($customer, $origin, $destination, $truck);
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

    public function approve(Request $request)
    {
        return $this->service->approve($request);
    }

    public function massApprove(Request $request)
    {
        return $this->service->massApprove($request);
    }

    public function destroy($id)
    {
        return $this->service->destroy($id);
    }
}
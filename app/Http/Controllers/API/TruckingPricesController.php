<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\TruckingPriceService;
use Illuminate\Http\Request;

class TruckingPricesController extends Controller
{
    protected $service;
    public function __construct(TruckingPriceService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $datas = $this->service->index($request);
        return $datas;
    }

    public function getTruckingPriceRates($origin, $destination, $type)
    {
        $datas = $this->service->getTruckingPriceRates($origin, $destination, $type);
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
}
<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    protected $service;
    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $datas = $this->service->index($request);
        return $datas;
    }

    public function indexByDate($start_date, $end_date)
    {
        $datas = $this->service->indexByDate($start_date, $end_date);
        return $datas;
    }

    public function list(Request $request)
    {
        $datas = $this->service->list($request);
        return $datas;
    }

    public function listByUserID(Request $request)
    {
        $datas = $this->service->listByUserID($request);
        return $datas;
    }

    public function show($id)
    {
        return $this->service->show($id);
    }

    public function store(Request $request)
    {
        return $this->service->create($request);
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
}
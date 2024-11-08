<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\BillService;
use Illuminate\Http\Request;

class BillsController extends Controller
{
    protected $service;
    public function __construct(BillService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $datas = $this->service->index($request);
        return $datas;
    }

    public function orderListByAgentID($agent_id)
    {
        $datas = $this->service->orderListByAgentID($agent_id);
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

    public function storeByAdmin(Request $request)
    {
        return $this->service->createByAdmin($request, $request->all());
    }

    public function update(Request $request, $id)
    {
        return $this->service->update($request, $request->all(), $id);
    }

    public function destroy($id)
    {
        return $this->service->destroy($id);
    }

    public function pay(Request $request)
    {
        return $this->service->pay($request, $request->all());
    }

    public function closing(Request $request)
    {
        return $this->service->closing($request, $request->all());
    }
}
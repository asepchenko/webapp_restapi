<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\OrderAgentService;
use Illuminate\Http\Request;

class OrderAgentsController extends Controller
{
    protected $service;
    public function __construct(OrderAgentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $datas = $this->service->index($request);
        return $datas;
    }

    public function indexByOrderID($id)
    {
        $datas = $this->service->indexByOrderID($id);
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
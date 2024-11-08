<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\AgentMasterPriceService;
use Illuminate\Http\Request;

class AgentMasterPricesController extends Controller
{
    protected $service;
    public function __construct(AgentMasterPriceService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $datas = $this->service->index($request);
        return $datas;
    }

    public function indexByAgentID($id)
    {
        $datas = $this->service->indexByAgentID($id);
        return $datas;
    }

    public function getMasterPriceRates($agent, $origin, $destination, $service)
    {
        $datas = $this->service->getMasterPriceRates($agent, $origin, $destination, $service);
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
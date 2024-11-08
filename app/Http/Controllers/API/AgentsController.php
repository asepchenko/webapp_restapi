<?php

namespace App\Http\Controllers\API;

use Gate;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\AgentService;
use Illuminate\Http\Request;

class AgentsController extends Controller
{
    protected $service;
    public function __construct(AgentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        if (! Gate::allows('master_agent_access')) {
            return ResponseFormatter::error([],'Forbidden','403');
        }
        $datas = $this->service->index($request);
        return $datas;
    }

    public function indexByCityID($id)
    {
        $datas = $this->service->indexByCityID($id);
        return $datas;
    }

    public function indexByCityAddressID($id)
    {
        $datas = $this->service->indexByCityAddressID($id);
        return $datas;
    }

    public function show($id)
    {
        if (! Gate::allows('master_agent_access')) {
            return ResponseFormatter::error([],'Forbidden','403');
        }
        return $this->service->show($id);
    }

    public function store(Request $request)
    {
        if (! Gate::allows('master_agent_create')) {
            return ResponseFormatter::error([],'Forbidden','403');
        }
        return $this->service->create($request, $request->all());
    }

    public function update(Request $request, $id)
    {
        if (! Gate::allows('master_agent_update')) {
            return ResponseFormatter::error([],'Forbidden','403');
        }
        return $this->service->update($request, $request->all(), $id);
    }

    public function destroy($id)
    {
        if (! Gate::allows('master_agent_delete')) {
            return ResponseFormatter::error([],'Forbidden','403');
        }
        return $this->service->destroy($id);
    }
}
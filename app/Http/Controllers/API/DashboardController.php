<?php

namespace App\Http\Controllers\API;

use Gate, Response;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $service;
    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        if (! Gate::allows('dashboard_access')) {
            return ResponseFormatter::error([],'Forbidden','403');
        }
        $datas = $this->service->index($request);
        return $datas;
    }

    /*public function show($id)
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
    }*/
}
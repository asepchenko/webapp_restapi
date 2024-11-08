<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\ManifestService;
use Illuminate\Http\Request;

class ManifestsController extends Controller
{
    protected $service;
    public function __construct(ManifestService $service)
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
    
    public function sttList(Request $request, $manifest_number)
    {
        $datas = $this->service->sttList($request, $manifest_number);
        return $datas;
    }

    public function manifestByDriverID($driver_id)
    {
        $datas = $this->service->manifestByDriverID($driver_id);
        return $datas;
    }

    public function manifestDriverDetail($manifest_number)
    {
        $datas = $this->service->manifestDriverDetail($manifest_number);
        return $datas;
    }

    public function manifestDriverAgent($manifest_number)
    {
        $datas = $this->service->manifestDriverAgent($manifest_number);
        return $datas;
    }

    public function manifestUpdateTracking(Request $request)
    {
        $datas = $this->service->manifestUpdateTracking($request, $request->all());
        return $datas;
    }

    public function manifestAgent(Request $request, $manifest_number)
    {
        $datas = $this->service->manifestAgent($request, $manifest_number);
        return $datas;
    }

    public function schedule(Request $request)
    {
        $datas = $this->service->schedule($request);
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
}
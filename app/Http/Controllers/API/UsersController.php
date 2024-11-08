<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    protected $service;
    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $datas = $this->service->index($request);
        return $datas;
    }

    public function show($id)
    {
        return $this->service->show($id);
    }

    public function changePassword(Request $request)
    {
        return $this->service->changePassword($request, $request->all());
    }

    public function changeProfile(Request $request)
    {
        return $this->service->changeProfile($request, $request->all());
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
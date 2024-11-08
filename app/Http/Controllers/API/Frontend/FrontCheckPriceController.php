<?php

namespace App\Http\Controllers\API\Frontend;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Service;
use App\Models\Location;
use App\Models\TruckingPrice;
use Illuminate\Http\Request;

class FrontCheckPriceController extends Controller
{
    protected $model, $modelService, $modelLocation, $modelTrucking;
    public function __construct(
        City $model,
        Service $modelService,
        Location $modelLocation,
        TruckingPrice $modelTrucking)
    {
        $this->model = $model;
        $this->modelService = $modelService;
        $this->modelLocation = $modelLocation;
        $this->modelTrucking = $modelTrucking;
    }

    public function listCity(Request $request)
    {
        $datas = $this->model->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function listTruckTypes(Request $request)
    {
        $datas = $this->modelTrucking->with('trucktypes')->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function listService(Request $request)
    {
        $datas = $this->modelService->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function checkPrice(Request $request){
        if($request->layanan == "T"){
            //trucking
            $datas = $this->modelTrucking->where('origin', $request->asal)
                                        ->where('destination', $request->tujuan)
                                        ->where('truck_type_id', $request->truck_type_id)
                                        ->get('price');
            
            if(count($datas)>0){
                $result = $datas[0]->price;
                return ResponseFormatter::success($result,'OK');
            }else{
                return ResponseFormatter::success([],'Not Found');
            }
        }else{
            $datas = $this->modelLocation->where('origin', $request->asal)
                                    ->where('destination', $request->tujuan)
                                    ->where('service_id', $request->via)
                                    ->get('publish_price');
            if(count($datas)>0){
                $result = $datas[0]->publish_price;
                return ResponseFormatter::success($result,'OK');
            }else{
                return ResponseFormatter::success([],'Not Found');
            }
        }
        
    }
}
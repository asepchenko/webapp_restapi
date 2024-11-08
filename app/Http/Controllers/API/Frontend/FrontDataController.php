<?php

namespace App\Http\Controllers\API\Frontend;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Customer;
use App\Models\Truck;
use Illuminate\Http\Request;

class FrontDataController extends Controller
{
    protected $cityModel, $customerModel, $truckModel;
    public function __construct(
        City $cityModel,
        Customer $customerModel,
        Truck $truckModel
    )
    {
        $this->cityModel = $cityModel;
        $this->customerModel = $customerModel;
        $this->truckModel = $truckModel;
    }

    public function index(Request $request)
    {
        $cities = $this->cityModel->count('id');
        $customers = $this->customerModel->count('id');
        $trucks = $this->truckModel->count('id');
        
        $data = [
            'total_city'        => $cities,
            'total_customer'    => $customers,
            'total_truck'       => $trucks,
        ];
                    
        return ResponseFormatter::success($data,'OK');
    }
}
<?php

namespace App\Http\Controllers\API\Customer;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\OrderReference;
use App\Models\Location;
use App\Models\Service;
use App\Models\ServiceGroup;
use App\Models\PaymentType;
use App\Models\TruckType;
use App\Models\CustomerBranch;
use App\Models\CustomerMasterPrice;
use App\Models\CustomerTruckingPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

//store
use App\Services\OrderService;

class CustomerOrdersController extends Controller
{
    protected $model, $modelRef, $modelTracking, $service, $locationModel, $cityModel, $truckModel, $serviceModel, $serviceGroupModel, $paymentTypeModel, $custBranchModel, $custPriceModel, $custTruckingPriceModel;
    public function __construct(
        Order $model,
        OrderReference $modelRef,
        OrderTracking $modelTracking,
        City $cityModel,
        TruckType $truckModel,
        Location $locationModel,
        Service $serviceModel,
        ServiceGroup $serviceGroupModel, 
        PaymentType $paymentTypeModel,
        CustomerBranch $custBranchModel,
        CustomerMasterPrice $custPriceModel,
        CustomerTruckingPrice $custTruckingPriceModel,
        OrderService $service
    )
    {
        $this->model = $model;
        $this->modelRef = $modelRef;
        $this->modelTracking = $modelTracking;
        $this->cityModel = $cityModel;
        $this->truckModel = $truckModel;
        $this->locationModel = $locationModel;
        $this->serviceModel = $serviceModel;
        $this->serviceGroupModel = $serviceGroupModel;
        $this->paymentTypeModel = $paymentTypeModel;
        $this->custBranchModel = $custBranchModel;
        $this->custPriceModel = $custPriceModel;
        $this->custTruckingPriceModel = $custTruckingPriceModel;
        $this->service = $service;
    }

    public function index(Request $request)
    {
        //->where('last_status','<>','Open')
        $datas = $this->model->with('customers','customer_branchs','origins','destinations','units','servicegroups','trackings')
                    ->where('customer_id',auth()->user()->customer_id)
                    ->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function getIndexByDate($start_date, $end_date)
    {
        $where = " and A.pickup_date >='".$start_date."'";
        $where .= " and A.pickup_date <='".$end_date."'";

        $datas = DB::select("
        select A.awb_no, A.order_number,A.total_colly,
        B.customer_name, C.branch_name,
        E.city_name as origin, F.city_name as destination,
        case when A.last_status = 'Delivered' Then 'Delivered' else 'On Process Delivery' end as last_status,
        A.pickup_date, A.delivered_date, D.service_name,
        (select G.recipient from order_trackings G where A.order_number=G.order_number
        and G.status_name='Delivered' limit 1) as recipient
        from orders A
        join customers B on A.customer_id=B.id
        join customer_branchs C on A.customer_branch_id=C.id
        left join services D on A.service_id=D.id
        join cities E on A.origin=E.id
        join cities F on A.destination=F.id
        where A.customer_id=".auth()->user()->customer_id." ".$where."
        order by A.pickup_date desc");
        return ResponseFormatter::success($datas,'OK');
    }

    public function show($order_number)
    {
        $datas = $this->model->with('customers','customer_branchs','origins','destinations','services','servicegroups','units','costs','references')
                    ->where('customer_id',auth()->user()->customer_id)
                    ->where('order_number',$order_number)
                    ->first();
        return ResponseFormatter::success($datas,'OK');
    }

    public function getOrderLimit5()
    {
        $datas = $this->model->with('customers','customer_branchs','origins','destinations','services','servicegroups','units','costs','references')
                    ->where('customer_id',auth()->user()->customer_id)
                    ->orderBy('updated_at','desc')->limit(5)->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function track($order_number)
    {
        $datas = $this->modelTracking->with('cities')->where('order_number',$order_number)
                    ->where('is_admin_view',0)
                    ->orderBy('status_date','desc')
                    ->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function getPrice(Request $request, $customer, $origin, $destination, $service)
    {
        $location = $this->locationModel->where('origin', $origin)
                                        ->where('destination',$destination)
                                        ->get('id');
        if(count($location) > 0){
            $data = $this->custPriceModel
                ->where('customer_id', $customer)
                ->where('location_id', $location[0]->id)
                ->where('service_id', $service)
                ->get();
            if(count($data) > 0){
                return ResponseFormatter::success($data[0],'OK');
            }else{
                return ResponseFormatter::success([],'OK');
            }
        }else{
            return ResponseFormatter::success([],'OK');
        }
    }

    public function getTruckingPriceRates($customer, $origin, $destination, $truck)
    {
        $data = $this->custTruckingPriceModel
                ->where('customer_id', $customer)
                ->where('origin', $origin)
                ->where('destination', $destination)
                ->where('truck_Type_id', $truck)
                ->get();
        if(count($data) > 0){
            return ResponseFormatter::success($data[0],'OK');
        }else{
            return ResponseFormatter::success([],'OK');
        }
    }

    public function getCustBranch(Request $request, $id)
    {
        $datas = $this->custBranchModel->where('customer_id',$id)->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function getCity(Request $request)
    {
        $datas = $this->cityModel->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function getServiceGroup(Request $request)
    {
        $datas = $this->serviceGroupModel->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function getCustCityBranch(Request $request, $id)
    {
        $datas = $this->custBranchModel->with('cities')->where('id',$id)->get();
        return ResponseFormatter::success($datas[0],'OK');
    }
    
    public function getService(Request $request)
    {
        $datas = $this->serviceModel->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function getPaymentType(Request $request)
    {
        $datas = $this->paymentTypeModel->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function getTruck(Request $request)
    {
        $datas = $this->truckModel->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function store(Request $request)
    {
        return $this->service->create($request);
    }

    public function getOrderRef(Request $request, $order_number)
    {
        $datas = $this->modelRef->where('order_number', $order_number)->get();
        return ResponseFormatter::success($datas,'OK');
    }
}
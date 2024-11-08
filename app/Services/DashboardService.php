<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Services\BaseService;
use App\Helpers\ResponseFormatter;
use Exception;

use App\Repositories\DashboardRepository;

class DashboardService extends BaseService
{
    protected $repo;

    public function __construct(
        DashboardRepository $repo
    ) {
        parent::__construct();
        $this->repo = $repo;
    }

    public function index(Request $request)
    {
        $data_total_order = $this->repo->getDataTotalOrder($request);
        $data_total_new_customer = $this->repo->getDataTotalNewCustomer($request);
        $data_total_order_on_delivery = $this->repo->getTotalOrderOnDelivery($request);
        $data_total_order_delivered = $this->repo->getTotalOrderDelivered($request);

        $data_order_monthly = $this->repo->getDataOrderMonthly($request);

        $data_new_customer = $this->repo->getNewCustomer($request);
        $data_new_order = $this->repo->getNewOrder($request);

        $data_order_realtime = $this->repo->getOrderRealTime($request);

        $data = [
            'total_order' => $data_total_order,
            'total_new_customer' => $data_total_new_customer,
            'total_order_on_delivery' => $data_total_order_on_delivery,
            'total_order_delivered' => $data_total_order_delivered,
            'order_monthly' => $data_order_monthly,
            'new_customers' => $data_new_customer,
            'new_orders' => $data_new_order,
            'order_realtime' => $data_order_realtime
        ];
        
        return ResponseFormatter::success($data,'OK');
    }

    /*public function show($id)
    {
        if ($id == 0) {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
        $data = $this->repo->show($id);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function create(Request $request, array $data)
    {
        DB::beginTransaction();
        try {

            $data['user_id'] = auth()->user()->id;
            $result = $this->repo->create($data);

            DB::commit();
            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function update(Request $request, array $data, $id)
    {
        DB::beginTransaction();
        try {
            $data['user_id'] = auth()->user()->id;
            $result = $this->repo->update($data, $id);
            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->repo->delete($id);

            DB::commit();
            return ResponseFormatter::success('','OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }*/
}

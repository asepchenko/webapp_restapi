<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\Bill;
use App\Models\BillDetail;
use App\Models\Order;

class BillRepository extends BaseRepository
{

    public function __construct(
        Bill $model,
        BillDetail $billDetailModel,
        Order $orderModel
    ) {
        $this->model = $model;
        $this->billDetailModel = $billDetailModel;
        $this->orderModel = $orderModel;
    }

    public function getIndex($request)
    {
        return $this->model->with('details','agents')->get(); //where('last_status','Open')->get();
    }

    public function show($id)
    {
        $data = $this->model->with('details','agents','user_verify')
            ->where('bill_number', $id)
            ->first();
        return $data;
    }

    //list order delivered which not yet invoiced
    public function getOrderListByAgentID($agent_id)
    {
        $order = DB::select("select A.order_number
        from orders A
        join order_agents B on A.order_number=B.order_number
        where B.agent_id=".$agent_id."
        and A.last_status='Delivered' and A.order_number not in(select order_number
        from bill_details)");

        $orderArr = json_decode(json_encode($order), true);
        //to array

        return $this->orderModel->with('customers','customer_branchs','agent')
            ->whereIn('order_number', $orderArr)
            ->get();
    }

    public function showByAgentID($id, $agent_id)
    {
        $data = $this->model->with('details','agents')
            ->where('agent_id', $agent_id)
            ->where('bill_number', $id)
            ->first();
        return $data;
    }

    public function getDataByID($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->get(['other_cost','discount','discount_percent','tax_percent','income_tax_percent','subtotal','grand_total']);
        return $data; //[0]; //->trip_number;
    }

    public function getDueDate($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->get('due_date');
        return $data[0]->due_date;
    }
    
    public function getLastStatusByInvoiceNumber($id)
    {
        $data = $this->model
            ->where('bill_number', $id)
            ->get('last_status');
        return $data[0]->last_status;
    }

    public function getInvoiceNumber($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->get('bill_number');
        return $data[0]->bill_number;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function createWithID(array $data)
    {
        $id = $this->model->create($data)->id;
        return $id;
    }

    public function update(array $data, $id)
    {
        $object = $this->model->findOrFail($id);
        $object->fill($data);
        $object->save();
        return $object->fresh();
    }

    public function delete($id)
    {
        return $this->model->where('id', $id)->delete();
    }

    public function deleteByInvoiceNumber($invoice_number)
    {
        return $this->model->where('bill_number', $invoice_number)->delete();
    }
}

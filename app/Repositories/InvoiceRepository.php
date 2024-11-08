<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Order;

class InvoiceRepository extends BaseRepository
{

    public function __construct(
        Invoice $model,
        Order $orderModel,
        InvoiceDetail $invoiceDetailModel
    ) {
        $this->model = $model;
        $this->orderModel = $orderModel;
        $this->invoiceDetailModel = $invoiceDetailModel;
    }

    public function getIndex($request)
    {
        return $this->model->with('users','customers')->get();
    }

    public function getList($request)
    {
        return $this->model->where('last_status','closing')
            ->with('details','destinations','users')
            ->get();
    }

    //list order delivered which not yet invoiced
    public function getOrderList($request)
    {
        $order = $this->invoiceDetailModel->get('order_number');
        return $this->orderModel->with('customers','customer_branchs','services','origins','destinations','servicegroups')
            ->where('last_status','Delivered')
            ->whereNotIn('order_number', $order)
            ->get();
    }

    public function getInvoiceNumber($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->get('invoice_number');
        return $data[0]->invoice_number;
    }

    public function getLastStatusByInvoiceNumber($id)
    {
        $data = $this->model
            ->where('invoice_number', $id)
            ->get('last_status');
        return $data[0]->last_status;
    }

    public function getTermin($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->get('termin');
        return $data[0]->termin;
    }

    public function getDueDate($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->get('due_date');
        return $data[0]->due_date;
    }

    public function show($id)
    {
        $data = $this->model->with('details','customers','user_verify','user_approval')
            ->where('invoice_number', $id)
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
    
    //list order delivered which not yet invoiced
    public function getOrderListByCustomerID($customer_id)
    {
        $order = DB::select("select A.order_number
        from orders A
        where A.customer_id=".$customer_id."
        and A.last_status='Delivered' and A.order_number not in(select order_number
        from invoice_details)");

        $orderArr = json_decode(json_encode($order), true);
        //to array

        return $this->orderModel->with('customers','customer_branchs','agent','servicegroups')
            ->whereIn('order_number', $orderArr)
            ->get();
    }

    public function showByCustID($id, $cust_id)
    {
        $data = $this->model->with('details','customers','user_approval')
            ->where('customer_id', $cust_id)
            ->where('invoice_number', $id)
            ->first();
        return $data;
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
        return $this->model->where('invoice_number', $invoice_number)->delete();
    }
}

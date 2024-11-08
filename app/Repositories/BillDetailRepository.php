<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\BillDetail;

class BillDetailRepository extends BaseRepository
{

    public function __construct(
        BillDetail $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('users')->get();
    }

    public function getDataByID($id)
    {
        $data = DB::select("select B.id, B.bill_number
        from bill_details A
        join bills B on A.bill_number=B.bill_number
        where A.id=".$id."");
        /*$data = DB::table('bill_details')
            ->selectRaw('bills.id as id, bills.bill_number as bill_number')
            ->join('bills', 'bill_details.bill_number', '=', 'bills.bill_number')
            ->where('bill_details.id', $id)
            ->get();*/
        return $data; //[0]; //->trip_number;
    }

    public function show($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->first();
        return $data;
    }

    public function getSumSubTotal(array $order_number)
    {
        $data = $this->model->whereIn('order_number', $order_number)->sum('subtotal');
        return $data;
    }

    public function getListOrderByInvoiceNumber($invoice_number)
    {
        $data = $this->model
            ->where('bill_number', $invoice_number)
            ->get('order_number')->toArray();
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
        return $this->model->where('bill_number', $invoice_number)->delete();
    }
}

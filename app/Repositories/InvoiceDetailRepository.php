<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\InvoiceDetail;

class InvoiceDetailRepository extends BaseRepository
{

    public function __construct(
        InvoiceDetail $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('users')->get();
    }

    public function show($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->first();
        return $data;
    }

    public function getDataByID($id)
    {
        $data = DB::select("select B.id, B.invoice_number
        from invoice_details A
        join invoices B on A.invoice_number=B.invoice_number
        where A.id=".$id."");
        return $data; //[0]; //->trip_number;
    }

    public function getListOrderByInvoiceNumber($invoice_number)
    {
        //$data = $this->model
        //    ->where('invoice_number', $invoice_number)
        //    ->get('order_number')->toArray();
            
        /*$data = DB::table('invoice_details')
            ->where('invoice_number', $invoice_number)
            ->get('order_number')->toArray();*/
        $data = DB::select("select order_number 
        from invoice_details where invoice_number='".$invoice_number."'");
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

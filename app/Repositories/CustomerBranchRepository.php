<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\CustomerBranch;

class CustomerBranchRepository extends BaseRepository
{

    public function __construct(
        CustomerBranch $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('users')->get();
    }

    public function getIndexByCustomerID($id)
    {
        $data = $this->model
            ->with('users','brands','cities')
            ->where('customer_id', $id)
            ->get();
        return $data;
    }

    public function show($id)
    {
        $data = $this->model
            ->with('cities')
            ->where('id', $id)
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

    public function checkIsHasUsed($id)
    {
        $check = DB::select("select B.order_number
        from customer_branchs A
        join orders B on A.id=B.customer_branch_id
        where A.id=".$id);
        if(count($check) > 0){
            return true;
        }else{
            return false;
        }
    }

    public function delete($id)
    {
        return $this->model->where('id', $id)->delete();
    }

    public function deleteByCustID($id)
    {
        return $this->model->where('customer_id', $id)->delete();
    }
}

<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\InvoiceApproval;

class InvoiceApprovalRepository extends BaseRepository
{

    public function __construct(
        InvoiceApproval $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('users','user_approval')->get();
    }

    public function getApprovalUser()
    {
        $data = $this->model
            ->orderBy('updated_at', 'desc')
            ->limit(1)
            ->get('approval_user_id');
        return $data[0]->approval_user_id;
    }

    public function show($id)
    {
        $data = $this->model->with('user_approval')
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

    public function delete($id)
    {
        return $this->model->where('id', $id)->delete();
    }
}

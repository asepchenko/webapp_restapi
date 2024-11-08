<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\User;

class UserRepository extends BaseRepository
{

    public function __construct(
        User $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('roleuser','departemenuser','branch')->get();
    }

    public function getByBranchID($branch_id)
    {
        $data = $this->model->where('branch_id', $branch_id)->get('id');
        return $data;
    }

    public function show($id)
    {
        $data = $this->model->with('roleuser','departemenuser','branch')
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

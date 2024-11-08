<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\Notification;

class NotificationRepository extends BaseRepository
{

    public function __construct(
        Notification $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('users')->get();
    }

    public function getIndexByLKE($request)
    {
        return $this->model->with('users')
                            ->where('user_type','LKE')
                            ->whereMonth('created_at',Carbon::now()->format('M'))
                            ->orderBy('created_at','desc')->get();
    }

    public function show($id)
    {
        $data = $this->model
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

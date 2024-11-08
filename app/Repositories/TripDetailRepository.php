<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\TripDetail;

class TripDetailRepository extends BaseRepository
{

    public function __construct(
        TripDetail $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('users')->get();
    }

    public function getListManifestNumber($trip_number)
    {
        return $this->model->where('trip_number', $trip_number)->get('manifest_number')->toArray();
    }

    public function getListOtherManifestNumber($manifest_number)
    {
        $tmp = $this->model->where('manifest_number', $manifest_number)->get('trip_number')->first();
        return $this->model->where('trip_number', $tmp->trip_number)
                            ->where('manifest_number', '<>', $manifest_number)
                            ->get('manifest_number')->toArray();
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

    public function deleteByTripNumber($trip_number)
    {
        return $this->model->where('trip_number', $trip_number)->delete();
    }
}

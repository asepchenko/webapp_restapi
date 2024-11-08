<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\ManifestCogs;

class ManifestCogRepository extends BaseRepository
{

    public function __construct(
        ManifestCogs $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('users','cities')->get();
    }

    public function getByTripNumber($no)
    {
        $data = $this->model->with('cities')
            ->where('trip_number', $no)
            ->get();
        return $data;
    }

    public function getOtherId($trip_number, $id)
    {
        return $this->model
            ->where('trip_number', $trip_number)
            ->where('id', '<>', $id)
            ->get('id')->toArray();
        //return $data;
    }

    public function getDataByManifestNumber($manifest_number)
    {
        $data = $this->model
            ->where('manifest_number', $manifest_number)
            ->get(['cogs_kg','kg','multiplier_number','id']);
        return $data;//[0]; //->cogs_kg;
    }

    public function getArrayListCityByManifestNumber(array $manifest_number)
    {
        $data = $this->model
            ->whereIn('manifest_number', $manifest_number)
            ->groupBy('city_id')
            ->get('city_id')->toArray();
        return $data;
    }

    public function getDataByID($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->get(['cogs_kg','kg','multiplier_number']);
        return $data;
    }

    public function getSumKilogram($trip_number)
    {
        $data = $this->model->where('trip_number', $trip_number)->sum('kg');
        return $data;
    }

    public function show($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->get();
        if(count($data) > 0){
                return $data[0];
            }else{
                return NULL;
            }
    }

    public function getSumCogsKg(array $manifest_number)
    {
        $data = $this->model->whereIn('manifest_number', $manifest_number)->sum('cogs_kg');
        return $data;
    }

    public function getSumCogsKgByTrip($trip_number)
    {
        $data = $this->model->where('trip_number', $trip_number)->sum('cogs_kg');
        return $data;
    }

    public function getCountManifestByTrip($trip_number)
    {
        $data = $this->model->where('trip_number', $trip_number)->count('manifest_number');
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

    public function updateByManifestNumber(array $data, $manifest_number)
    {
        $object = $this->model->where('manifest_number', $manifest_number)->firstOrFail();
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

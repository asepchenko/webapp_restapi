<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\Document;

class DocumentRepository extends BaseRepository
{

    public function __construct(
        Document $model
    ) {
        $this->model = $model;
    }

    //for admin
    public function getIndex($request)
    {
        return $this->model->with('agent','users','details')->where('last_status','=','Close')->get();
    }

    public function getFileName($id)
    {
        $data = $this->model->where('id', $id)->get()->first();
        return $data->filename;
    }

    public function getRecipient($id)
    {
        $data = $this->model->where('id', $id)->get()->first();
        return $data->recipient_user_id;
    }

    public function show($id)
    {
        $data = $this->model
            ->with('details')
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

<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface BaseRepositoryInterface
{
    public function getAll(Request $request);
    public function getById($id);
    public function getByMultipleField(array $conditions);
    public function create(array $data);
    public function update(array $data, $id);
    public function delete($id);
}
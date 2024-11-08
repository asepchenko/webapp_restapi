<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Services\BaseService;
use App\Helpers\ResponseFormatter;
use Exception;

use App\Repositories\AgentPicRepository;

class AgentPicService extends BaseService
{
    protected $repo;

    public function __construct(
        AgentPicRepository $repo
    ) {
        parent::__construct();
        $this->repo = $repo;
    }

    public function index(Request $request)
    {
        $data = $this->repo->getIndex($request);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function indexByAgentID($id)
    {
        $data = $this->repo->getIndexByAgentID($id);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function show($id)
    {
        $data = $this->repo->show($id);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function create(Request $request, array $data)
    {
        DB::beginTransaction();
        try {

            //validating
            $validator = Validator::make($data, [
                'name'  => 'required|string|max:191',
                'email' => 'required|email|max:191|unique:user_agents,email'
            ],
            [
                'name.required' => 'Nama tidak boleh kosong',
                'email.required'=> 'Email tidak boleh kosong', 
                'email.email'=> 'Email tidak valid', 
                'email.unique' => 'Email sudah terdaftar sebelumnya, silahkan gunakan email lain',
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            $data['password'] = bcrypt('password');
            $data['user_id'] = auth()->user()->id;
            $result = $this->repo->create($data);

            DB::commit();
            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function update(Request $request, array $data, $id)
    {
        DB::beginTransaction();
        try {
            //validating
            $validator = Validator::make($data, [
                'name'  => 'required|string|max:191',
                'email' => ['required','email','max:191',Rule::unique('user_agents', 'email')->ignore($id)]
            ],
            [
                'name.required' => 'Nama tidak boleh kosong',
                'email.required'=> 'Email tidak boleh kosong', 
                'email.email'=> 'Email tidak valid', 
                'email.unique' => 'Email sudah terdaftar sebelumnya, silahkan gunakan email lain',
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            $data['user_id'] = auth()->user()->id;
            $result = $this->repo->update($data, $id);
            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            //update first
            $data['deleted_by'] = auth()->user()->id;
            $this->repo->update($data, $id);
            $this->repo->delete($id);

            DB::commit();
            return ResponseFormatter::success('','OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}

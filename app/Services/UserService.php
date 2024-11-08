<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Services\BaseService;
use App\Helpers\ResponseFormatter;
use Exception;

use App\Models\DepartemenUser;
use App\Models\RoleUser;
use App\Repositories\UserRepository;

class UserService extends BaseService
{
    protected $repo;

    public function __construct(
        UserRepository $repo,
        DepartemenUser $deptUserModel,
        RoleUser $roleUserModel
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->deptUserModel = $deptUserModel;
        $this->roleUserModel = $roleUserModel;
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

    public function show($id)
    {
        $data = $this->repo->show($id);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function changePassword(Request $request, array $data)
    {
        DB::beginTransaction();
        try {
            //validating
            $validator = Validator::make($data, [
                'old_password' => ['required', 'string'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            //check old password
            if(!Hash::check($request->old_password, auth()->user()->password)){
                throw new Exception('Password lama tidak sesuai');
            }

            $data = [];

            $data['password']   = bcrypt($request->password);
            $result = $this->repo->update($data, $request->id);
            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function changeProfile(Request $request, array $data)
    {
        DB::beginTransaction();
        try {
            //validating
            $validator = Validator::make($data, [
                'name' => ['required', 'string'],
                'email' => ['required', 'email'],
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            $result = $this->repo->update($data, $request->id);
            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function create(Request $request, array $data)
    {
        DB::beginTransaction();
        try {

            //validating
            $validator = Validator::make($data, [
                'driver_id' => 'unique:users,driver_id'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            $data = [];
            $data_dept = [];
            $data_role = [];

            //insert user
            $data['branch_id']  = $request->branch_id;
            $data['nik']        = $request->nik;
            $data['name']       = $request->name;
            $data['email']      = $request->email;
            $data['driver_id']  = $request->driver_id;
            $data['password']   = bcrypt('password');
            $data['verified']   = $request->verified;
            $data['approved']   = 1;
            $user_id = $this->repo->createWithID($data);

            //insert to departemen
            $data_dept['departemen_id']  = $request->departemen_id;
            $data_dept['user_id']        = $user_id;
            $this->deptUserModel->create($data_dept);

            //insert to roles
            $data_role['role_id']  = $request->role_id;
            $data_role['user_id']  = $user_id;
            $this->roleUserModel->create($data_role);

            DB::commit();
            return ResponseFormatter::success($user_id,'OK');
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
                'driver_id' => [Rule::unique('users', 'driver_id')->ignore($id)]
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            $data = [];
            $data_dept = [];
            $data_role = [];

            //delete old data
            $this->deptUserModel->where('user_id', $id)->delete();
            $this->roleUserModel->where('user_id', $id)->delete();

            //insert user
            $data['branch_id']  = $request->branch_id;
            $data['driver_id']  = $request->driver_id;
            $data['nik']        = $request->nik;
            $data['name']       = $request->name;
            $data['email']      = $request->email;
            $data['approved']   = $request->approved;
            $result = $this->repo->update($data, $id);

            //insert to departemen
            $data_dept['departemen_id']  = $request->departemen_id;
            $data_dept['user_id']        = $id;
            $this->deptUserModel->create($data_dept);

            //insert to roles
            $data_role['role_id']  = $request->role_id;
            $data_role['user_id']  = $id;
            $this->roleUserModel->create($data_role);

            //$result = $this->repo->update($data, $id);
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

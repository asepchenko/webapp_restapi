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

use App\Repositories\CustomerBranchRepository;

class CustomerBranchService extends BaseService
{
    protected $repo;

    public function __construct(
        CustomerBranchRepository $repo
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

    public function indexByCustomerID($id)
    {
        $data = $this->repo->getIndexByCustomerID($id);
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
                'branch_name'  => 'required|string|max:32',
                'address'  => 'required|string|max:160',
            ],
            [
                'branch_name.required' => 'Nama cabang tidak boleh kosong',
                'address.required' => 'Alamat tidak boleh kosong',
            ]);

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
                'branch_name'  => 'required|string|max:32',
                'address'  => 'required|string|max:160',
            ],
            [
                'branch_name.required' => 'Nama cabang tidak boleh kosong',
                'address.required' => 'Alamat tidak boleh kosong',
            ]);
            
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

            //check if data has been used in orders
            $check = $this->repo->checkIsHasUsed($id);

            if($check){
                throw new Exception('Master cabang ini sudah pernah digunakan, tidak bisa dihapus');
            }

            //get cust ID
            $tmp = $this->repo->show($id);
            $custID = $tmp->customer_id;
            
            //count data first
            $branchs = $this->repo->getIndexByCustomerID($custID);
            if(count($branchs) > 1){
                //update first
                $data['deleted_by'] = auth()->user()->id;
                $this->repo->update($data, $id);
                $this->repo->delete($id);
            }else{
                throw new Exception('Data Cabang minimal 1');
            }
            
            DB::commit();
            return ResponseFormatter::success('','OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}

<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
//use Illuminate\Support\Facades\Storage; //file upload
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Services\BaseService;
use App\Services\CustomerMouService;

use App\Helpers\ResponseFormatter;
use Exception;

use App\Repositories\CustomerRepository;
use App\Repositories\CustomerBrandRepository;
use App\Repositories\CustomerBranchRepository;
use App\Repositories\CustomerMasterPriceRepository;
use App\Repositories\CustomerPicRepository;
use App\Repositories\CustomerMouRepository;
use App\Repositories\OrderRepository;

class CustomerService extends BaseService
{
    protected $repo;

    public function __construct(
        CustomerRepository $repo,
        CustomerBrandRepository $repoBrand,
        CustomerBranchRepository $repoBranch,
        CustomerMasterPriceRepository $repoMasterPrice,
        CustomerPicRepository $repoPIC,
        CustomerMouRepository $repoMou,
        CustomerMouService $serviceMoU,
        OrderRepository $repoOrder
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->repoBrand = $repoBrand;
        $this->repoBranch = $repoBranch;
        $this->repoMasterPrice = $repoMasterPrice;
        $this->repoPIC = $repoPIC;
        $this->repoMou = $repoMou;
        $this->serviceMoU = $serviceMoU;
        $this->repoOrder = $repoOrder;
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

    public function create(Request $request, array $data)
    {
        DB::beginTransaction();
        try {

            //validating
            $validator = Validator::make($data, [
                'customer_name'  => 'required|string|max:64',
                'address'  => 'required|string|max:160',
                'email' => 'required|email|max:128|unique:customers,email'
            ],
            [
                'customer_name.required' => 'Nama customer tidak boleh kosong',
                'address.required' => 'Alamat tidak boleh kosong',
                'email.required'=> 'Email tidak boleh kosong', 
                'email.email'=> 'Email tidak valid', 
                'email.unique' => 'Email sudah terdaftar sebelumnya, silahkan gunakan email lain',
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            $data['user_id'] = auth()->user()->id;
            $id = $this->repo->createWithID($data);

            //create a branch
            $data_branch = [];
            $data_branch['customer_id'] = $id;
            $data_branch['branch_code'] = 'PST';
            $data_branch['branch_name'] = 'PUSAT';
            $data_branch['city_id']     = $request->city_id;
            $data_branch['address']     = $request->address;
            $data_branch['user_id']     = auth()->user()->id;
            $data_branch['is_active']   = 'Y';
            $result = $this->repoBranch->create($data_branch);

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
                'customer_name'  => 'required|string|max:64',
                'address'  => 'required|string|max:160',
                'email' => ['required','email','max:128',Rule::unique('customers', 'email')->ignore($id)]
            ],
            [
                'customer_name.required' => 'Nama customer tidak boleh kosong',
                'address.required' => 'Alamat tidak boleh kosong',
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

            //check already in MOU
            $mous = $this->repoMou->getListByCustID($id);
            if(count($mous) > 0){
                throw new Exception('Data Tidak bisa dihapus karena sudah ada MOU');
            }

            //check already in transaction
            $orders = $this->repoOrder->getListByCustID($id);
            if(count($orders) > 0){
                throw new Exception('Data Tidak bisa dihapus karena sudah ada transaksi');
            }

            //because using softdelete, then dont delete related data
            //delete all related data
            //$this->repoBrand->deleteByCustID($id);
            //$this->repoBranch->deleteByCustID($id);
            //$this->repoMasterPrice->deleteByCustID($id);
            ////$this->repoPIC->deleteByCustID($id);
            //$this->serviceMoU->deleteByCustID($id);

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

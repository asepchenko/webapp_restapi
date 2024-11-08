<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Services\BaseService;
//use App\Services\NotificationService;
use App\Helpers\ResponseFormatter;
use Exception;

use App\Repositories\CustomerRepository;
use App\Repositories\CustomerMasterPriceRepository;
use App\Repositories\NotificationRepository;

class CustomerMasterPriceService extends BaseService
{
    protected $repo, $custRepo, $repoNotif;

    public function __construct(
        CustomerMasterPriceRepository $repo,
        CustomerRepository $custRepo,
        NotificationRepository $repoNotif
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->custRepo = $custRepo;
        $this->repoNotif = $repoNotif;
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

    public function indexPending(Request $request)
    {
        $data = $this->repo->getIndexPending($request);
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
    
    public function getMasterPriceRates($customer, $origin, $destination, $service)
    {
        $data = $this->repo->getMasterPriceRates($customer, $origin, $destination, $service);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','200');
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


    public function createNotification($id, $customer_id, $judul)
    {
        $nama_cust = $this->custRepo->getCustomerName($customer_id);
        $data = $this->repo->show($id);

        $content = 'Harap dilakukan proses approve atau reject data harga pada customer : '.$nama_cust.'';
        $content .= '\nAsal : '.$data->locations->origins->city_name.'';
        $content .= '\nTujuan : '.$data->locations->destinations->city_name.'';
        $content .= '\nVia : '.$data->services->service_name.'';

        $data_notif = [
            'title' => $judul,
            'content' => $content,
            'user_type' => 'LKE',
            'url' => '/approval/customer-master-prices',
            'is_read' => 'T',
            'user_id' => auth()->user()->id
        ];

        return $this->repoNotif->create($data_notif);
    }

    public function create(Request $request, array $data)
    {
        DB::beginTransaction();
        try {

            //validating
            /*$validator = Validator::make($data, [
                'customermasterprice_name' => 'required|max:255|unique:customermasterprices,customermasterprice_name'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/

            //check if location id & service id already exist
            $check = $this->repo->checkAlreadyExist($request->customer_id, $request->price_code);

            if($check){
                throw new Exception('Master harga ini sudah tersedia');
            }
            
            $data['margin'] = $request->price - $request->cogs_price;
            $data['user_id'] = auth()->user()->id;
            $result = $this->repo->createWithID($data);
            $this->createNotification($result, $request->customer_id,'Harga Customer Baru');

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
            /*$validator = Validator::make($data, [
                'customermasterprice_name' => ['required','max:255',Rule::unique('customermasterprices', 'customermasterprice_name')->ignore($id)]
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/
            $data['margin'] = $request->price - $request->cogs_price;
            $data['status'] = 'PENDING';
            $data['user_id'] = auth()->user()->id;
            $result = $this->repo->update($data, $id);
            $this->createNotification($id, $request->customer_id,'Update Harga Customer Baru');

            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function approve(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = [];
            $data['status'] = 'APPROVED';
            $data['approved_at'] = now();
            $data['approved_by'] = auth()->user()->id;
            $result = $this->repo->update($data, $request->id);
            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function massApprove(Request $request)
    {
        DB::beginTransaction();
        try {
            $ids = explode(',', $request->ids);
            foreach ($ids as $value) {
                $data = [];
                //insert detail
                $data['status'] = 'APPROVED';
                $data['approved_at'] = now();
                $data['approved_by'] = auth()->user()->id;
                $result = $this->repo->update($data, $value);
            }

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
                throw new Exception('Master harga ini sudah pernah digunakan, tidak bisa dihapus');
            }

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

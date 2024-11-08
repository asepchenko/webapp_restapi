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

use App\Repositories\DriverRepository;
use App\Repositories\ManifestRepository;

class DriverService extends BaseService
{
    protected $repo, $manifestRepo;

    public function __construct(
        DriverRepository $repo,
        ManifestRepository $manifestRepo
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->manifestRepo = $manifestRepo;
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
            /*$validator = Validator::make($data, [
                'driver_name' => 'required|max:255|unique:drivers,driver_name'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/
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
            /*$validator = Validator::make($data, [
                'driver_name' => ['required','max:255',Rule::unique('drivers', 'driver_name')->ignore($id)]
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/
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
            $jum = $this->manifestRepo->getManifestByDriver($id);
            if(count($jum) > 0){
                throw new Exception('Data Tidak bisa dihapus karena sudah ada transaksi');
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

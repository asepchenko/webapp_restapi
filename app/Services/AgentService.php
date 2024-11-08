<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; //file upload
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Services\BaseService;
use App\Helpers\ResponseFormatter;
use Exception;

use App\Repositories\AgentRepository;
use App\Repositories\AgentMasterPriceRepository;
use App\Repositories\AgentPicRepository;

class AgentService extends BaseService
{
    protected $repo;

    public function __construct(
        AgentRepository $repo,
        AgentMasterPriceRepository $repoMasterPrice,
        AgentPicRepository $repoPIC
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->repoMasterPrice = $repoMasterPrice;
        $this->repoPIC = $repoPIC;
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

    public function indexByCityID($id)
    {
        $data = $this->repo->getIndexByCityID($id);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','200');
        }
    }

    public function indexByCityAddressID($id)
    {
        $data = $this->repo->getIndexByCityAddressID($id);
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

    public function create(Request $request, array $data)
    {
        DB::beginTransaction();
        try {

            $validator = Validator::make($data, [
                'mou_file' => 'required|file|mimes:doc,docx,pdf|max:5128'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            //file upload processing
            $ext = $request->mou_file->getClientOriginalExtension();
            $timestamp = time();
            $ori_file = $request->mou_file->getClientOriginalName();
            $ori_filename = pathinfo($ori_file, PATHINFO_FILENAME);
            $fileName = $timestamp. "_" . $ori_filename . "." . $ext;
            $request->mou_file->storeAs('agent/mou', $fileName);

            $data['mou_file'] = $fileName;
            $data['user_id']  = auth()->user()->id;
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
            $validator = Validator::make($data, [
                'mou_file' => 'file|mimes:doc,docx,pdf|max:5128'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            //get file first
            $recent_mou = $this->repo->getFileName($id);

            //file upload processing
            if(isset($request->mou_file)){
                $ext = $request->mou_file->getClientOriginalExtension();
                $timestamp = time();
                $ori_file = $request->mou_file->getClientOriginalName();
                $ori_filename = pathinfo($ori_file, PATHINFO_FILENAME);
                $fileName = $timestamp. "_" . $ori_filename . "." . $ext;
                $request->mou_file->storeAs('agent/mou', $fileName);

                $data['mou_file'] = $fileName;

                //delete old file
                Storage::delete('agent/mou/'.$recent_mou);
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
            //because using softdelete then dont delete related data
            //delete all related data
            //$this->repoMasterPrice->deleteByAgentID($id);
            //$this->repoPIC->deleteByAgentID($id);

            //because using softdelete then dont delete file
            //get file first
            //$recent_mou = $this->repo->getFileName($id);
            //delete file
            //Storage::delete('agent/mou/'.$recent_mou);

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

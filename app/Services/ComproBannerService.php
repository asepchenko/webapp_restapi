<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage; //file upload
use Illuminate\Validation\Rule;

use App\Services\BaseService;
use App\Helpers\ResponseFormatter;
use Exception;

use App\Repositories\ComproBannerRepository;

class ComproBannerService extends BaseService
{
    protected $repo;

    public function __construct(
        ComproBannerRepository $repo
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
                'image' => 'required|file|mimes:jpg,jpeg,png,webp|max:5128'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            //file upload processing
            $ext = $request->image->getClientOriginalExtension();
            $timestamp = time();
            $ori_file = $request->image->getClientOriginalName();
            $ori_filename = pathinfo($ori_file, PATHINFO_FILENAME);
            $fileName = $timestamp. "_" . $ori_filename . "." . $ext;
            $request->image->storeAs('public/banners', $fileName);

            $data['image'] = $fileName;
            
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
                'image' => 'file|mimes:mp4,jpg,jpeg,png,webp|max:5128'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            //get image file first
            $image = $this->repo->getImageName($id);

            //check if user change image
            if (isset($request->image)) {

                //file upload processing
                $ext = $request->image->getClientOriginalExtension();
                $timestamp = time();
                $ori_file = $request->image->getClientOriginalName();
                $ori_filename = pathinfo($ori_file, PATHINFO_FILENAME);
                $fileName = $timestamp. "_" . $ori_filename . "." . $ext;
                $request->image->storeAs('public/banners', $fileName);

                $data['image'] = $fileName;

                //delete old file
                Storage::delete('public/banners/'.$image);
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
            //get image file first
            $image = $this->repo->getImageName($id);

            $this->repo->delete($id);

            //delete file
            Storage::delete('public/banners/'.$image);

            DB::commit();
            return ResponseFormatter::success('','OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}

<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use DateTime;
use App\Models\Manifest;

class ManifestRepository extends BaseRepository
{

    protected $month, $year;

    public function __construct(
        Manifest $model
    ) {
        $this->model = $model;
        $this->month = date('m');
        $this->year = date('Y');
    }

    public function getIndex($request)
    {
        return $this->model->with('users','destinations','details')->get();
		//return $this->model->with('users','destinations','details')->OrderBy('created_at','desc')->limit(100)->get();
    }

    public function getList($request)
    {
        return $this->model->where('last_status','closing')
            ->with('details','destinations','users')
            ->get();
    }

    public function getManifestByDriver($id)
    {
        $data = $this->model->where('driver_id', $id)->get();
        return $data;
    }

    public function getManifestDriverAgent($manifest_number)
    {
        //id dari order agents
        $data = DB::select("select DISTINCT C.id, D.agent_name, E.city_name, E.id as city_id
        from manifests A
        join manifest_details B on A.manifest_number=B.manifest_number
        join order_agents C on B.order_number = C.order_number
        join agents D on C.agent_id = D.id
        join cities E on D.city_id = E.id
        where A.manifest_number='".$manifest_number."'
        union all
        select DISTINCT C.id, D.branch_name as agent_name, E.city_name, E.id as city_id
        from manifests A
        join manifest_details B on A.manifest_number=B.manifest_number
        join order_agents C on B.order_number = C.order_number
        join branchs D on C.branch_id = D.id
        join cities E on D.city_id = E.id
        where A.manifest_number='".$manifest_number."'");
        return $data;
    }

    public function isDriverTruckMatch($driver_id, $truck_id)
    {
        //->whereNull('last_tracking')
        //->whereNotIn('last_tracking',['Delivered','Transit'])
        $tmp = $this->model->where('driver_id', $driver_id)
                            ->where('is_already_track','N')
                            ->get();
        if(count($tmp) > 0){
            //driver has already assigned to another truck
            
            if($tmp[0]->truck_id == $truck_id){
                //match
                return true;
            }else{
                //not match
                return false;
            }
        }else{
            //driver has not assigned to another truck
            //check truck
            //->whereNull('last_tracking')
            //->whereOr('last_tracking','<>','Delivered')
            $tmp_truck = $this->model->where('truck_id', $truck_id)
                            ->where('is_already_track','N')
                            ->get();
            if(count($tmp_truck) > 0){
                //truck has already assigned to another driver
                if($tmp_truck[0]->driver_id == $driver_id){
                    //match
                    return true;
                }else{
                    //not match
                    return false;
                }
            }else{
                //truck has not assigned to another driver
                return true;
            }
        }
    }

    //for mobile apps
    public function getManifestByDriveriD($id)
    {
        $mft = DB::select("select distinct A.manifest_number as manifest_number from manifests A 
        join manifest_details B on A.manifest_number=B.manifest_number 
        join orders C on B.order_number = C.order_number 
        where A.driver_id=".$id." and C.delivered_date is null");

        $mftArr = json_decode(json_encode($mft), true);

        //->whereIn('last_tracking',['On Process Delivery'])
        $data = $this->model->with('destinations','details')
                            ->where('driver_id', $id)
                            ->whereIn('manifest_number', $mftArr)
                            ->where('is_already_track','N')
                            ->where('last_status','Trip')
                            ->get();
        return $data;
    }

    //check driver must be assigned to manifest
    public function getManifestDriverDetail($id)
    {
        //->whereIn('last_tracking',['On Process Delivery'])
        $data = $this->model->with('details','destinations','drivers','trucks')
            ->where('is_already_track','N')
            ->where('manifest_number', $id)
            ->where('last_status','Trip')
            ->where('driver_id', auth()->user()->driver_id)
            ->first();
        return $data;
    }

    //get list stt in same agent whicn not in other manifest
    public function getSttList($request, $manifest_number)
    {
        $temp = DB::select("select B.agent_id
        from manifest_details A
        join order_agents B on A.order_number=B.order_number
        where A.manifest_number='".$manifest_number."'
        order by B.sequence asc limit 1");

        if($temp){
            $data = DB::select("select A.order_number, A.awb_no, C.customer_name, D.branch_name
            from orders A
            join order_agents B on A.order_number=B.order_number
            join customers C on A.customer_id=C.id
            join customer_branchs D on A.customer_branch_id=D.id
            where A.order_number not in(select order_number from
            manifest_details
            ) 
            and A.last_status='Closing' and B.agent_id in(".$temp[0]->agent_id.")");
        }else{
            //no agent
            $data = DB::select("select A.order_number, A.awb_no, C.customer_name, D.branch_name
            from orders A
            join customers C on A.customer_id=C.id
            join customer_branchs D on A.customer_branch_id=D.id
            where A.order_number not in(select order_number from
            manifest_details
            ) and A.last_status='Closing'");
        }

        return $data;
    }

    public function getManifestAgent($request, $manifest_number)
    {
        //-- and B.sequence=1
        $temp = DB::select("select B.agent_id
        from manifest_details A
        join order_agents B on A.order_number=B.order_number
        where A.manifest_number='".$manifest_number."' 
        and B.agent_id is not null
        order by B.sequence asc limit 1");

        if($temp){
            $data = DB::select("select agent_name from agents where id=".$temp[0]->agent_id."");
        }else{
            //no agent
            $data = DB::select("select '' as agent_name");
        }

        return $data;
    }

    public function getSchedule($request)
    {
        //->where('last_status', '=', 'closing')
        return $this->model->whereYear('manifest_date', '=', $this->year)
            ->whereMonth('manifest_date', '=', $this->month)
            ->where('is_already_track', '=', 'N')
            ->with('details','destinations','drivers','trucks','users')
            ->get();
    }

    public function show($id)
    {
        $data = $this->model->with('details','destinations','drivers','trucks')
            ->where('manifest_number', $id)
            ->first();
        return $data;
    }

    public function getManifestNumber($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->get('manifest_number');
        return $data[0]->manifest_number;
    }

    public function getLastStatusByManifestNumber($manifest_number)
    {
        $data = $this->model
            ->where('manifest_number', $manifest_number)
            ->get('last_status');
        return $data[0]->last_status;
    }

    public function isAlreadyTrack($manifest_number)
    {
        $data = $this->model
            ->where('manifest_number', $manifest_number)
            ->get('is_already_track');
            //if()
        return $data[0]->is_already_track;
    }

    public function isAllowClosing($manifest_number)
    {
        $data = $this->model
            ->where('manifest_number', $manifest_number)
            ->whereNotNull('driver_id')
            ->whereNotNull('truck_id')
            ->whereNotNull('manifest_date')
            ->get();
      
        if(count($data)>0){
            return true;
        }else{
            return false;
        }
    }

    public function getManifestDestKilo($id)
    {
        $data = $this->model
            ->where('manifest_number', $id)
            ->get(['destination','total_kg']);
        return $data; //[0]; //->destination;
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

    public function updateByManifestNumber(array $data, $manifest_number)
    {
        $object = $this->model->where('manifest_number', $manifest_number)->firstOrFail();
        $object->fill($data);
        $object->save();
        return $object->fresh();
    }

    public function delete($id)
    {
        return $this->model->where('id', $id)->delete();
    }

    public function deleteByManifestNumber($manifest_number)
    {
        return $this->model->where('manifest_number', $manifest_number)->delete();
    }
}

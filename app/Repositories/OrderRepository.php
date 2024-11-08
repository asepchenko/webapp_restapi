<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\Order;

class OrderRepository extends BaseRepository
{

    public function __construct(
        Order $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        /*return $this->model->where('branch_id', auth()->user()->branch_id)
            ->with(
            'customers','customer_branchs','origins','destinations','servicegroups','trackings'
            )->orderBy('order_number','desc')->get();
            */

        $data = DB::select("
        select A.awb_no, A.order_number,
        B.customer_name, C.branch_name,
        E.city_name as origin, F.city_name as destination,
        A.last_status, A.pickup_date, A.delivered_date, D.service_name,
        (select G.recipient from order_trackings G where A.order_number=G.order_number
        and G.status_name='Delivered' limit 1) as recipient
        from orders A
        join customers B on A.customer_id=B.id
        join customer_branchs C on A.customer_branch_id=C.id
        join services D on A.service_id=D.id
        join cities E on A.origin=E.id
        join cities F on A.destination=F.id
        where A.branch_id=".auth()->user()->branch_id."
        order by A.pickup_date desc");
        return $data;
    }

    public function getIndexByDate($start_date, $end_date)
    {
        $where = " and A.pickup_date >='".$start_date."'";
        $where .= " and A.pickup_date <='".$end_date."'";

        if(auth()->user()->branch_id == 1){
            $data = DB::select("
            select A.awb_no, A.order_number,
            B.customer_name, C.branch_name,
            E.city_name as origin, F.city_name as destination,
            A.last_status, A.pickup_date, A.delivered_date, D.service_name,
            (select G.recipient from order_trackings G where A.order_number=G.order_number
            and G.status_name='Delivered' limit 1) as recipient
            from orders A
            join customers B on A.customer_id=B.id
            join customer_branchs C on A.customer_branch_id=C.id
            left join services D on A.service_id=D.id
            join cities E on A.origin=E.id
            join cities F on A.destination=F.id
            where A.branch_id=".auth()->user()->branch_id." ".$where."
            order by A.pickup_date desc");
        }else{
            $data = DB::select("select AA.awb_no, AA.order_number,
            AA.customer_name, AA.branch_name,
            AA.origin, AA.destination, AA.delivered_date, AA.pickup_date, AA.service_name,AA.recipient,
            AA.last_status
        from (select A.awb_no, A.order_number, A.delivered_date, A.last_status,
        (select GG.recipient from order_trackings GG where A.order_number=GG.order_number
            and GG.status_name='Delivered' limit 1) as recipient,
        -- A.total_colly, A.total_kg_agent,
        B.customer_name, C.branch_name,
        J.city_name as origin, 
        ifnull((select Y.city_name
        from order_agents X
        join branchs Z on X.branch_id=Z.id
        join cities Y on Z.city_id=Y.id
        where X.order_number=A.order_number and X.sequence=G.sequence+1),J.city_name) as destination,
        A.pickup_date, D.service_name
        from orders A
        join customers B on A.customer_id=B.id
        join customer_branchs C on A.customer_branch_id=C.id
        join services D on A.service_id=D.id
        join cities F on A.destination=F.id
        join order_agents G on A.order_number=G.order_number
        join branchs H on G.branch_id=H.id
        join cities J on H.city_id=J.id
        where G.branch_id=".auth()->user()->branch_id." ".$where."
        ) as AA
        order by AA.pickup_date desc");
        }
        return $data;
    }

    //harus dituning, memory besar sampai 256 MB
    public function getList($request)
    {
        /*return $this->model->where('branch_id', auth()->user()->branch_id)
            ->where('last_status','closing')
            ->with(
            'customers','customer_branchs','origins','destinations','agent','units','servicegroups','services','users'
            )->get();*/

        //raw
        $data = DB::select("select A.order_number, A.awb_no, A.pickup_date, B.customer_name, C.branch_name, D.service_name,
        E.city_name as origin, F.city_name as destination, G.group_name as service,
        (select group_concat(Z.agent_name,',') from agents Z
        join order_agents X on Z.id=X.agent_id where X.agent_id is not null 
        and X.order_number=A.order_number
        group by X.order_number) as agents
        from orders A
        join customers B on A.customer_id=B.id
        join customer_branchs C on C.id=A.customer_branch_id
        left join services D on A.service_id=D.id
        join cities E on A.origin=E.id
        join cities F on A.destination=F.id
        join service_groups G on A.service_group_id=G.id
        where A.last_status='closing'
        and A.branch_id=".auth()->user()->branch_id."");

        return $data;
    }

    public function getListByUserID($request)
    {
        return $this->model->where('user_id', auth()->user()->id)
            ->with(
            'customers','customer_branchs','origins','destinations','units','servicegroups','services'
            )->orderBy('updated_at','desc')->limit(5)->get();
    }

    public function getListByCustID($id)
    {
        $data = $this->model->where('customer_id', $id)->get();
        return $data;
    }

    public function getSumColly(array $order_number)
    {
        $data = $this->model->whereIn('order_number', $order_number)->sum('total_colly');
        return $data;
    }

    public function getSumKilogram(array $order_number)
    {
        $data = $this->model->whereIn('order_number', $order_number)->sum('total_kg');
        return $data;
    }

    public function getSumKilogramAgent(array $order_number)
    {
        $data = $this->model->whereIn('order_number', $order_number)->sum('total_kg_agent');
        return $data;
    }

    public function isDontHaveAgent(array $order_number)
    {
        $data = $this->model->whereIn('order_number', $order_number)
        ->doesnthave('agents')->get();
        if($data){
            return true;
        }else{
            return false;
        }
    }

    public function getOrderNumber($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->get('order_number');
        return $data[0]->order_number;
    }

    public function getLastStatusByOrderNumber($order_number)
    {
        $data = $this->model
            ->where('order_number', $order_number)
            ->get('last_status');
        return $data[0]->last_status;
    }

    //by order_number
    public function show($id)
    {
        $data = $this->model
            ->with('customers','customer_branchs','customer_master_prices','trucking_price','origins','destinations','services','servicegroups','units','costs')
            ->where('order_number', $id)
            ->first();
        return $data;
    }

    public function sumDataByOrderNumberCityID(array $order_number)
    {
        $data = DB::table('orders')
            ->selectRaw('orders.destination as city_id,  
            sum(orders.total_colly) as colly,
            sum(orders.total_kg) as kg')
            ->whereIn('orders.order_number', $order_number)
            ->groupBy('orders.destination')
            ->get()->toArray();
        return $data;
    }

    public function isSameDestination(array $order_number)
    {
        $count = $this->model->whereIn('order_number', $order_number)->distinct()->count('destination');
        if($count > 1){
            return false;
        }else{
            return true;
        }
    }

    public function isSameCustomer(array $order_number)
    {
        $count = $this->model->whereIn('order_number', $order_number)->distinct()->count('customer_id');
        if($count > 1){
            return false;
        }else{
            return true;
        }
    }

    public function getCustomerID(array $order_number)
    {
        $cust = $this->model->whereIn('order_number', $order_number)->distinct()->get('customer_id');
        return $cust[0]->customer_id;
    }

    public function getServiceGroupID(array $order_number)
    {
        $cust = $this->model->whereIn('order_number', $order_number)->distinct()->get('service_group_id');
        return $cust[0]->service_group_id;
    }

    public function getOrder($order_number)
    {
        return $this->model->where('order_number', $order_number)->get();
    }

    public function isTrucking(array $order_number)
    {
        $count = $this->model->where('service_group_id',3)
                            ->whereIn('order_number', $order_number)
                            ->distinct()->count('service_group_id');
        if($count > 0){
            return true;
        }else{
            return false;
        }
    }

    public function isSameGroupService(array $order_number)
    {
        $count = $this->model->whereIn('order_number', $order_number)->distinct()->count('service_group_id');
        if($count > 1){
            return false;
        }else{
            return true;
        }
    }

    public function isSameService(array $order_number)
    {
        $count = $this->model->whereIn('order_number', $order_number)->distinct()->count('service_id');
        if($count > 1){
            return false;
        }else{
            return true;
        }
    }

    public function getDestination($order_number)
    {
        $data = $this->model->where('order_number', $order_number)->first();
        return $data->destination;
    }

    public function getSameDestination(array $order_number)
    {
        $data = $this->model->whereIn('order_number', $order_number)->get()->first();
        if($data){
            return $data->destination;
        }else{
            return NULL;
        }
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

    public function updateByOrderNumber(array $data, $order_number)
    {
        $object = $this->model->where('order_number', $order_number)->firstOrFail();
        $object->fill($data);
        $object->save();
        return $object->fresh();
    }

    public function delete($id)
    {
        return $this->model->where('id', $id)->delete();
    }

    public function deleteByOrderNumber($order_number)
    {
        return $this->model->where('order_number', $order_number)->delete();
    }
}

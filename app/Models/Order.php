<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\City;
use App\Models\Customer;
use App\Models\CustomerBranch;
use App\Models\CustomerMasterPrice;
use App\Models\CustomerTruckingPrice;
use App\Models\User;
use App\Models\Service;
use App\Models\ServiceGroup;
use App\Models\OrderAgent;
use App\Models\OrderUnit;
use App\Models\OrderCost;
use App\Models\OrderReferemce;
use App\Models\OrderTracking;
use App\Traits\MultiTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    //use MultiTenant;

    public $table = 'orders';

    protected $fillable = [
        'branch_id',
        'agent_id',
        'pickup_date',
        'order_number',
        'awb_no',
        'customer_id',
        'customer_branch_id',
        'service_id',
        'service_group_id',
        'truck_type_id',
        'origin',
        'destination',
        'customer_master_price_id',
        'trucking_price_id',
        'total_colly',
        'total_kg',
        'total_kg_agent',
        'contains',
        'description',
        'payment_type',
        'delivered_date',
        'last_status',
        'last_status_acc',
        'user_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'datetime:d-M-y H:i',
    ];

    //for fix timezone diff between app and db
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-M-y H:i');
    }

    public function getKilogramAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function setCreatedAtAttribute( $value ) {
        $this->attributes['created_at'] = (new Carbon($value))->toDateString();
    }

    public function setPickupDateAttribute( $value ) {
        $this->attributes['pickup_date'] = (new Carbon($value))->toDateString();
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function customers()
    {
        return $this->hasOne(Customer::class, 'id','customer_id');
    }

    public function customer_branchs()
    {
        return $this->hasOne(CustomerBranch::class, 'id','customer_branch_id');
    }

    public function customer_master_prices()
    {
        return $this->hasOne(CustomerMasterPrice::class, 'id','customer_master_price_id');
    }

    public function trucking_price()
    {
        return $this->hasOne(CustomerTruckingPrice::class, 'id','trucking_price_id')->with('trucktypes');
    }

    public function origins()
    {
        return $this->hasOne(City::class, 'id','origin');
    }

    public function destinations()
    {
        return $this->hasOne(City::class, 'id','destination');
    }

    public function services()
    {
        return $this->hasOne(Service::class, 'id','service_id')->with('servicegroups')->withDefault([
            'service_name' => '-',
        ]);
    }

    public function servicegroups()
    {
        return $this->hasOne(ServiceGroup::class, 'id','service_group_id');
    }

    public function units()
    {
        return $this->hasMany(OrderUnit::class, 'order_number','order_number');
    }

    public function costs()
    {
        return $this->hasOne(OrderCost::class, 'order_number','order_number');
    }

    public function references()
    {
        return $this->hasMany(OrderReference::class, 'order_number','order_number');
    }

    public function agent()
    {
        return $this->hasMany(OrderAgent::class, 'order_number','order_number')->with('agents');
    }

    public function trackings()
    {
        return $this->hasOne(OrderTracking::class, 'order_number','order_number')->orderBy('created_at','desc');
    }
}
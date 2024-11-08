<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\Location;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentMasterPrice extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'agent_master_prices';

    protected $fillable = [
        'agent_id',
        'price_code',
        'location_id',
        'service_id',
        'price',
        'administrative_cost',
        'insurance_fee',
        'other_cost',
        'margin',
        'user_id',
        'created_at',
        'updated_at',
        'deleted_by'
    ];

    public function getPriceAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    /*public function getAdministrativeCostAttribute($value)
    {
        return number_format($value,0,".",",");
    }

    public function getInsuranceFeeAttribute($value)
    {
        return number_format($value,0,".",",");
    }

    public function getOtherCostAttribute($value)
    {
        return number_format($value,0,".",",");
    }

    public function getMarginAttribute($value)
    {
        return number_format($value,0,".",",");
    }*/
    
    protected $casts = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'datetime:d-M-y H:i',
    ];

    //for fix timezone diff between app and db
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-M-y H:i');
    }

    public function locations()
    {
        return $this->hasOne(Location::class, 'id','location_id')->with('origins','destinations');
    }

    public function services()
    {
        return $this->hasOne(Service::class, 'id','service_id');
    }
    
    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
}
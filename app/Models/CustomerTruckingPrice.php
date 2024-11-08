<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\TruckType;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerTruckingPrice extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'customer_trucking_prices';

    protected $fillable = [
        'customer_id',
        'price_code',
        'truck_type_id',
        'origin',
        'destination',
        'cogs_price',
        'price',
        'user_id',
        'status',
        'created_at',
        'updated_at',
        'approved_by',
        'approved_at',
        'deleted_by'
    ];

    public function getCogsPriceAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getPriceAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    protected $casts = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'datetime:d-M-y H:i',
    ];

    //for fix timezone diff between app and db
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-M-y H:i');
    }

    public function customers()
    {
        return $this->hasOne(Customer::class, 'id','customer_id');
    }

    public function origins()
    {
        return $this->hasOne(City::class, 'id','origin');
    }

    public function destinations()
    {
        return $this->hasOne(City::class, 'id','destination');
    }

    public function trucktypes()
    {
        return $this->hasOne(TruckType::class, 'id','truck_type_id');
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function approved()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function getMarginAttribute()
    {
        return number_format(($this->attributes['price'] - $this->attributes['cogs_price']),2,",",".");
    }

    protected $appends = ['margin'];
}
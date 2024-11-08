<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\City;
use App\Models\TruckType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TruckingPrice extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'trucking_prices';

    protected $fillable = [
        'truck_type_id',
        'origin',
        'destination',
        'price',
        'cogs_price',
        'user_id',
        'created_at',
        'updated_at',
        'deleted_by'
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

    public function getPriceAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getCogsPriceAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function trucktypes()
    {
        return $this->hasOne(TruckType::class, 'id','truck_type_id');
    }

    public function origins()
    {
        return $this->hasOne(City::class, 'id','origin');
    }

    public function destinations()
    {
        return $this->hasOne(City::class, 'id','destination');
    }

    public function getMarginAttribute()
    {
        return $this->attributes['price'] - $this->attributes['cogs_price'];
    }

    protected $appends = ['margin'];
}
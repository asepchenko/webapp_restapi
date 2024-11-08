<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\City;
use App\Models\User;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'locations';

    protected $fillable = [
        'price_code',
        'origin',
        'destination',
        'service_id',
        'sales_bottom',
        'publish_price',
        'distance',
        'min_days',
        'max_days',
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

    public function getPublishPriceAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getSalesBottomAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function origins()
    {
        return $this->hasOne(City::class, 'id','origin')->withDefault([
			'city_code' => '',
            'city_name' => '',
        ]);
    }

    public function destinations()
    {
        return $this->hasOne(City::class, 'id','destination')->withDefault([
			'city_code' => '',
            'city_name' => '',
        ]);
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
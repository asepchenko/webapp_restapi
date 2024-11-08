<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\Location;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationRate extends Model
{
    use HasFactory;

    public $table = 'location_rates';

    protected $fillable = [
        'location_id',
        'service_id',
        'rate',
        'distance',
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

    public function getRateAttribute($value)
    {
        return number_format($value,0,".",",");
    }

    public function getDistanceAttribute($value)
    {
        return number_format($value,0,".",",");
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
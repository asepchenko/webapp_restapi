<?php

namespace App\Models;

use DateTimeInterface;
use Carbon\Carbon;
use App\Models\User;
use App\Models\City;
use App\Models\Driver;
use App\Models\Truck;
use App\Models\ManifestDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manifest extends Model
{
    use HasFactory;

    public $table = 'manifests';

    protected $fillable = [
        'manifest_number',
        'manifest_date',
        'driver_id',
        'truck_id',
        'police_number',
        'total_colly',
        'total_kg',
        'total_order',
        'origin',
        'destination',
        'description',
        'address',
        'last_status',
        'last_tracking',
        'is_already_track',
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

    public function setManifestDateAttribute( $value ) {
        $this->attributes['manifest_date'] = (new Carbon($value))->toDateString();
    }

    public function getTotalKgAttribute($value)
    {
        return number_format($value,2,",",".");
    }


    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function destinations()
    {
        return $this->hasOne(City::class, 'id','destination')->withDefault([
            'city_name' => '',
        ]);
    }

    public function drivers()
    {
        return $this->hasOne(Driver::class, 'id','driver_id')->withDefault([
            'driver_name' => '',
        ]);
    }

    public function trucks()
    {
        return $this->hasOne(Truck::class, 'id','truck_id')->with('trucktypes')->withDefault([
            'police_number' => '',
        ]);
    }

    public function details()
    {
        return $this->hasMany(ManifestDetail::class, 'manifest_number','manifest_number')->with('orders');
    }
}
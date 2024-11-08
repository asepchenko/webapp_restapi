<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\TripDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    public $table = 'trips';

    protected $fillable = [
        'trip_number',
        'operational_cost',
        'multiplier_number',
        'last_status',
        'last_tracking_status',
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

    public function getOperationalCostAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getMultiplierNumberAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function details()
    {
        return $this->hasMany(TripDetail::class, 'trip_number','trip_number')->with('manifests');
    }
}
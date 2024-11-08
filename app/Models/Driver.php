<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\Truck;
use App\Models\Bank;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'drivers';

    protected $fillable = [
        'driver_name',
        'driver_license',
        'driver_license_type',
        'driver_license_exp_date',
        'account_number',
        'bank_id',
        'truck_id',
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

    public function trucks()
    {
        return $this->hasOne(Truck::class, 'id','truck_id')->withDefault([
            'police_number' => '',
        ]);
    }

    public function banks()
    {
        return $this->hasOne(Bank::class, 'id','bank_id')->withDefault([
            'bank_name' => '',
        ]);
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function setDriverNameAttribute($value)
    {
        $this->attributes['driver_name'] = strtoupper($value);
    }
}
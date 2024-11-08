<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\TruckType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Truck extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'trucks';

    protected $fillable = [
        'truck_type_id',
        'police_number',
        'production_year',
        'reg_exp_date',
        'reg_tax_exp_date',
        'examination_exp_date',
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

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function trucktypes()
    {
        return $this->hasOne(TruckType::class, 'id','truck_type_id');
    }

    public function setPoliceNumberAttribute($value)
    {
        $this->attributes['police_number'] = strtoupper($value);
    }
}
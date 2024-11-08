<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\Province;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'cities';

    protected $fillable = [
        'province_id',
        'city_code',
        'city_name',
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

    public function provinces()
    {
        return $this->hasOne(Province::class, 'id','province_id')->withDefault([
            'province_name' => '',
        ]);
    }
    
    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function setCityNameAttribute($value)
    {
        $this->attributes['city_name'] = strtoupper($value);
    }
}
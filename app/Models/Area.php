<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\AreaCity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'areas';

    protected $fillable = [
        'area_name',
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

    public function details()
    {
        return $this->hasMany(AreaCity::class, 'area_id','id')->with('cities');
    }

    public function setAreaNameAttribute($value)
    {
        $this->attributes['area_name'] = strtoupper($value);
    }
}
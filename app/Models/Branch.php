<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'branchs';

    protected $fillable = [
        'branch_code',
        'branch_name',
        'city_id',
        'address',
        'phone',
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

    public function cities()
    {
        return $this->hasOne(City::class, 'id','city_id');
    }
    
    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function employees()
    {
        return $this->hasMany(User::class, 'branch_id','id');
    }
}
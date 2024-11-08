<?php

namespace App\Models;

use DateTimeInterface;
use Carbon\Carbon;
use App\Models\CustomerBranch;
use App\Models\CustomerBrand;
use App\Models\CustomerPic;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'customers';

    protected $fillable = [
        'customer_code',
        'customer_name',
        'address',
        'city_id',
        'phone_number','email',
        'tax_number','tax',
        'last_mou_id',
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

    public function branchs()
    {
        return $this->hasMany(CustomerBranch::class, 'customer_id','id');
    }

    public function brands()
    {
        return $this->hasMany(CustomerBrand::class, 'customer_id','id');
    }

    public function pics()
    {
        return $this->hasMany(CustomerPic::class, 'customer_id','id');
    }

    public function cities()
    {
        return $this->hasOne(City::class, 'id','city_id')->withDefault([
            'city_name' => '',
        ]);
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
}
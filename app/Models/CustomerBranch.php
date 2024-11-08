<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\CustomerBrand;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerBranch extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'customer_branchs';

    protected $fillable = [
        'customer_id',
        'customer_brand_id',
        'branch_code',
        'branch_name',
        'city_id',
        'address',
        'description',
        'is_active',
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

    public function brands()
    {
        return $this->hasOne(CustomerBrand::class, 'id','customer_brand_id')->withDefault([
            'brand_name' => '',
        ]);
    }

    public function cities()
    {
        return $this->hasOne(City::class, 'id','city_id');
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
}
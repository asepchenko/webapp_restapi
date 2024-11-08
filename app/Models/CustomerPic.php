<?php

namespace App\Models;

use DateTimeInterface;
use Carbon\Carbon;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPic extends Model
{
    use HasFactory;

    public $table = 'customer_pics';

    protected $fillable = [
        'customer_id',
        'pic_name','pic_phone','pic_email','pic_address','pic_birthdate',
        'city_id',
        'is_active',
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

    public function setPicBirthdateAttribute( $value ) {
        $this->attributes['pic_birthdate'] = (new Carbon($value))->toDateString();
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
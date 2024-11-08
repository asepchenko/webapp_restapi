<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\RoleUser;
use App\Models\Customer;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class UserCustomer extends Authenticatable
{
    use SoftDeletes, HasApiTokens, Notifiable;

    public $table = 'user_customers';

    protected $fillable = [
        'customer_id',
        'name',
        'email',
        'password',
        'phone_number',
        'address',
        'birthday',
        'verified',
        'approved',
        'created_at',
        'updated_at',
        'deleted_by'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'created_at' => 'datetime:d-M-y h:m',
        'updated_at' => 'datetime:d-M-y h:m',
        'email_verified_at' => 'datetime',
    ];

    public function setBirthdayAttribute( $value ) {
        $this->attributes['birthday'] = (new Carbon($value))->toDateString();
    }

    public function roleuser()
    {
        return $this->hasOne(RoleUser::class, 'user_id','id')->with('roles')->withDefault([
            'title' => '',
        ]);
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function customers()
    {
        return $this->hasOne(Customer::class, 'id','customer_id');
    }
}
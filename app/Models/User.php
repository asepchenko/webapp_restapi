<?php

namespace App\Models;

use App\Models\Branch;
use App\Models\DepartemenUser;
use App\Models\Role;
use App\Models\RoleUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use SoftDeletes, HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nik',
        'name',
        'email',
        'password',
        'branch_id',
        'driver_id',
        'verified',
        'approved',
        'deleted_by'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->hasOne(Branch::class, 'id','branch_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)->with('permissions');
    }

    public function roleuser()
    {
        return $this->hasOne(RoleUser::class, 'user_id','id')->with('roles')->withDefault([
            'title' => '',
        ]);
    }

    public function departemenuser()
    {
        return $this->hasOne(DepartemenUser::class, 'user_id','id')->with('departemens')->withDefault([
            'departemen_code' => '',
            'departemen_name' => '',
        ]);
    }
}

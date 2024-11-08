<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\Agent;
use App\Models\RoleUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class UserAgent extends Authenticatable
{
    use SoftDeletes, HasApiTokens, HasFactory, Notifiable;

    public $table = 'user_agents';

    protected $fillable = [
        'agent_id',
        'name',
        'email',
        'password',
        'verified',
        'approved',
        'phone_number',
        'user_id',
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

    public function agents()
    {
        return $this->hasOne(Agent::class, 'id','agent_id');
    }
}
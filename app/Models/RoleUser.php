<?php

namespace App\Models;

use App\Models\RoleUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    use HasFactory;

    public $table = 'role_user';
    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'user_id',
    ];

    public function roles()
    {
        return $this->hasOne(Role::class, 'id','role_id');
    }
}
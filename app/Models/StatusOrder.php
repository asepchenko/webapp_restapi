<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusOrder extends Model
{
    use HasFactory;

    public $table = 'status_orders';

    protected $fillable = [
        'status_name',
        'user_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime:d-M-y h:m',
        'updated_at' => 'datetime:d-M-y h:m',
    ];

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
}
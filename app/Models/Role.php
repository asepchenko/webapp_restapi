<?php

namespace App\Models;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    public $table = 'roles';

    protected $fillable = [
        'id',
        'title',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime:d-M-y h:m',
        'updated_at' => 'datetime:d-M-y h:m',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
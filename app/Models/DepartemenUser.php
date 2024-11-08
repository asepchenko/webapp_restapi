<?php

namespace App\Models;

use App\Models\Departemen;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartemenUser extends Model
{
    use HasFactory;

    public $table = 'departemen_user';
    public $timestamps = false;

    protected $fillable = [
        'departemen_id',
        'user_id',
    ];

    public function departemens()
    {
        return $this->hasOne(Departemen::class, 'id','departemen_id');
    }
}
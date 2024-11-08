<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentCity extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'agent_cities';

    protected $fillable = [
        'agent_id',
        'city_id',
        'user_id',
        'created_at',
        'updated_at',
        'deleted_by'
    ];

    protected $casts = [
        'created_at' => 'datetime:d-M-y H:i',
        'updated_at' => 'datetime:d-M-y H:i',
    ];

    //for fix timezone diff between app and db
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-M-y H:i');
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function cities()
    {
        return $this->hasOne(City::class, 'id','city_id');
    }
}
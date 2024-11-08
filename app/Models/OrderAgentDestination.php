<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\User;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderAgentDestination extends Model
{
    use HasFactory;

    public $table = 'order_agent_destinations';

    protected $fillable = [
        'order_number',
        'agent_id_origin',
        'branch_id_origin',
        'origin',
        'agent_id_destination',
        'branch_id_destination',
        'destination',
        'pickup_date',
        'delivered_date',
        'user_id',
        'created_at',
        'updated_at',
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

    public function origins()
    {
        return $this->hasOne(City::class, 'id','origin');
    }

    public function destinations()
    {
        return $this->hasOne(City::class, 'id','destination');
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
}
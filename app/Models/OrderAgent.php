<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderAgent extends Model
{
    use HasFactory;

    public $table = 'order_agents';

    protected $fillable = [
        'order_number',
        'agent_id',
        'branch_id',
        'origin',
        'destination',
        'sequence',
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

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function agents()
    {
        return $this->hasOne(Agent::class, 'id','agent_id')->with('areas')->withDefault([
            'agent_name' => '-',
            'mou_file' => '',
        ]);
    }

    public function branchs()
    {
        return $this->hasOne(Branch::class, 'id','branch_id')->with('cities');
    }

    public function origins()
    {
        return $this->hasOne(City::class, 'id','origin');
    }

    public function destinations()
    {
        return $this->hasOne(City::class, 'id','destination');
    }
}
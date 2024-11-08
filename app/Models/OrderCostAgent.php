<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\Agent;
use App\Models\Orders;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCostAgent extends Model
{
    use HasFactory;

    public $table = 'order_cost_agents';

    protected $fillable = [
        'agent_id',
        'order_number',
        'price',
        'cogs_price',
        'discount',
        'gross_total',
        'nett',
        'tax',
        'tax_percent',
        'nett_with_tax',
        'grand_total',
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

    public function getPriceAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getGrandTotalAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function orders()
    {
        return $this->hasOne(Orders::class, 'order_number','order_number');
    }

    public function agents()
    {
        return $this->hasOne(Agent::class, 'id','agent_id');
    }
}
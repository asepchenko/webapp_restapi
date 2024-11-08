<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillDetail extends Model
{
    use HasFactory;

    public $table = 'bill_details';

    protected $fillable = [
        'bill_number',
        'order_number',
        'price',
        'total_kg',
        'subtotal',
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

    public function orders()
    {
        return $this->hasOne(Order::class, 'order_number','order_number')->with('units','customers','customer_branchs','destinations','costs','services','servicegroups','trucking_price');
    }
}
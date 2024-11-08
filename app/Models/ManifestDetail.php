<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManifestDetail extends Model
{
    use HasFactory;

    public $table = 'manifest_details';

    protected $fillable = [
        'manifest_number',
        'order_number',
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
        return $this->hasOne(Order::class, 'order_number','order_number')->with('units','customers','customer_branchs','origins','destinations','services','servicegroups','trucking_price');
    }
}
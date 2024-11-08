<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\Orders;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCost extends Model
{
    use HasFactory;

    public $table = 'order_costs';

    protected $fillable = [
        'order_number',
        'price',
        'cogs_price',
        'discount',
        'gross_total',
        'nett',
        'tax',
        'tax_percent',
        'nett_with_tax',
        'commission',
        'gross_margin',
        'administrative_cost',
        'insurance_fee',
        'other_cost',
        'packing_cost',
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

    public function getPackingCostAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getInsuranceFeeAttribute($value)
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

}
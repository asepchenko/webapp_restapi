<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentDetail extends Model
{
    use HasFactory;

    public $table = 'document_details';

    protected $fillable = [
        'document_id',
        'order_number',
        'awb_no',
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
        return $this->hasOne(User::class, 'id','user_id')->withDefault([
            'name' => '',
        ]);
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'awb_no','awb_no')->with('customers');
    }
}
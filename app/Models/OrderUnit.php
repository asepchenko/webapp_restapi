<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderUnit extends Model
{
    use HasFactory;

    public $table = 'order_units';

    protected $fillable = [
        'order_number',
        'weight_type',
        'height',
        'width',
        'length',
        'colly',
        'kilogram',
        'volume',
        'divider',
        'contains',
        'description',
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

    public function getHeightAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getWidthAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getLengthAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getKilogramAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getVolumeAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
}
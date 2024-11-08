<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManifestCogs extends Model
{
    use HasFactory;

    public $table = 'manifest_cogs';

    protected $fillable = [
        'manifest_number',
        'trip_number',
        'city_id',
        'sort_number',
        'multiplier',
        'multiplier_number',
        'percentage',
        'cogs_kg',
        'cogs_avg',
        'diff_cogs_avg_ops',
        'avg_ops_cost',
        'cogs_real_kg',
        'cogs_real_city',
        'kg',
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

    public function getKgAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getMultiplierAttribute($value)
    {
        return number_format($value,0,",",".");
    }

    public function getCogsKgAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getCogsAvgAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getAvgOpsCostAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getCogsRealKgAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getCogsRealCityAttribute($value)
    {
        return number_format($value,2,",",".");
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
<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\Manifest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripDetail extends Model
{
    use HasFactory;

    public $table = 'trip_details';

    protected $fillable = [
        'trip_number',
        'manifest_number',
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

    public function manifests()
    {
        return $this->hasOne(Manifest::class, 'manifest_number','manifest_number')->with('details','destinations','drivers','trucks');
    }
}
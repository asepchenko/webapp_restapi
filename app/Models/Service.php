<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\ServiceGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'services';

    protected $fillable = [
        'service_group_id',
        'service_code',
        'service_name',
        'description',
        'user_id',
        'created_at',
        'updated_at',
        'deleted_by'
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

    public function servicegroups()
    {
        return $this->hasOne(ServiceGroup::class, 'id','service_group_id')->withDefault([
            'group_name' => '',
        ]);
    }

    public function setServiceNameAttribute($value)
    {
        $this->attributes['service_name'] = strtoupper($value);
    }
}
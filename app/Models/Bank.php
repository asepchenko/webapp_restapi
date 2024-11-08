<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'banks';

    protected $fillable = [
        'bank_name',
        'user_id',
        'created_at',
        'updated_at',
        'deleted_by'
    ];

    protected $casts = [
        'created_at' => 'datetime:d-M-y H:i',
        'updated_at' => 'datetime:d-M-y H:i',
    ];

    //for fix timezone diff between app and db
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-M-y H:i');
    }

    /*public function getUpdatedAtAttribute($value)
    {
        if($value == NULL) {
            return NULL;
        } else {
            return \Carbon\Carbon::parse($value)->timezone('Asia/Jakarta')->format('d-M-Y H:i');
        }
    }*/

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
}
<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\Order;
use App\Models\User;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//file
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Testing\MimeType;

class OrderTracking extends Model
{
    use HasFactory;

    public $table = 'order_trackings';

    protected $fillable = [
        'order_number',
        'status_date',
        'status_name',
        'filename',
        'city_id',
        'recipient',
        'description',
        'is_admin_view',
        'user_id',
        'created_at',
        'updated_at',
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

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'order_number','order_number');
    }

    public function cities()
    {
        return $this->hasOne(City::class, 'id','city_id')->withDefault([
            'city_name' => '',
        ]);
    }

    public function getFilenameBase64Attribute()
    {
        if($this->attributes['filename'] != "" || $this->attributes['filename'] != NULL){
            $filename = $this->attributes['filename'];
            $file = Storage::get('orders/file/' . $filename);
            $base64 = base64_encode($file);
            $mimetype = MimeType::from($filename);
            $data = 'data:' . $mimetype . ';base64,' . $base64;
        }else{
            $data = '';
        }
        return $data;
    }

    protected $appends = ['filename_base64'];
}
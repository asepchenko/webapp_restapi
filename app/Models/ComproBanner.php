<?php

namespace App\Models;

use DateTimeInterface;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//image
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Testing\MimeType;

class ComproBanner extends Model
{
    use HasFactory;

    public $table = 'compro_banners';

    protected $fillable = [
        'caption',
        'image',
        'start_date',
        'end_date',
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

    public function setStartDateAttribute( $value ) {
        $this->attributes['start_date'] = (new Carbon($value))->toDateString();
    }

    public function setEndDateAttribute( $value ) {
        $this->attributes['end_date'] = (new Carbon($value))->toDateString();
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function getImageBase64Attribute()
    {
        $filename = $this->attributes['image'];
        $file = Storage::get('public/banners/' . $filename);
        $base64 = base64_encode($file);
        $mimetype = MimeType::from($filename);
        $clip_data = 'data:' . $mimetype . ';base64,' . $base64;
        return $clip_data;
    }

    protected $appends = ['image_base64'];
}
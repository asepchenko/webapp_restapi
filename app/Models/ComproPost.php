<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//image
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Testing\MimeType;

class ComproPost extends Model
{
    use HasFactory;

    public $table = 'compro_posts';

    protected $fillable = [
        'title',
        'slug',
        'category',
        'content',
        'image',
        'status',
        'user_id',
        'created_at',
        'updated_at',
        'published_at'
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

    public function getImageBase64Attribute()
    {
        $filename = $this->attributes['image'];
        $file = Storage::get('public/posts/' . $filename);
        $base64 = base64_encode($file);
        $mimetype = MimeType::from($filename);
        $clip_data = 'data:' . $mimetype . ';base64,' . $base64;
        return $clip_data;
    }

    protected $appends = ['image_base64'];
}
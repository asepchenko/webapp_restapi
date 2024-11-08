<?php

namespace App\Models;

use DateTimeInterface;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
//file
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Testing\MimeType;

class CustomerMou extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'customer_mous';

    protected $fillable = [
        'customer_id',
        'mou_file',
        'mou_number',
        'mou_start_date', 
        'mou_end_date',
        'user_id',
        'created_at',
        'updated_at',
        'deleted_by'
    ];

    public function setMouStartDateAttribute( $value ) {
        $this->attributes['mou_start_date'] = (new Carbon($value))->toDateString();
    }

    public function setMouEndDateAttribute( $value ) {
        $this->attributes['mou_end_date'] = (new Carbon($value))->toDateString();
    }

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

    public function getMouBase64Attribute()
    {
        $filename = $this->attributes['mou_file'];
        $file = Storage::get('customer/mou/' . $filename);
        $base64 = base64_encode($file);
        $mimetype = MimeType::from($filename);
        $mou_data = 'data:' . $mimetype . ';base64,' . $base64;
        return $mou_data;
    }

    protected $appends = ['mou_base64'];
}
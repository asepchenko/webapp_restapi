<?php

namespace App\Models;

use DateTimeInterface;
use Carbon\Carbon;
use App\Models\Area;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
//file
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Testing\MimeType;

class Agent extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'agents';

    protected $fillable = [
        'agent_code',
        'agent_name',
        'address',
        'city_id',
        'area_id',
        'phone_number',
        'mou_file', 'mou_start_date', 'mou_end_date',
        'tax_number','tax',
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

    public function setMouStartDateAttribute( $value ) {
        $this->attributes['mou_start_date'] = (new Carbon($value))->toDateString();
    }

    public function setMouEndDateAttribute( $value ) {
        $this->attributes['mou_end_date'] = (new Carbon($value))->toDateString();
    }

    public function areas()
    {
        return $this->hasOne(Area::class, 'id','area_id')->with('details')->withDefault([
            'area_name' => '',
        ]);
    }

    public function cities()
    {
        return $this->hasOne(City::class, 'id','city_id')->withDefault([
            'city_name' => '',
        ]);
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function getMouBase64Attribute()
    {
        $filename = $this->attributes['mou_file'];
        if($filename != ""){
            $file = Storage::get('agent/mou/' . $filename);
            $base64 = base64_encode($file);
            $mimetype = MimeType::from($filename);
            $mou_data = 'data:' . $mimetype . ';base64,' . $base64;
            return $mou_data;
        }else{
            return '';
        }
    }

    protected $appends = ['mou_base64'];
}
<?php

namespace App\Models;

use DateTimeInterface;
use Carbon\Carbon;
use App\Models\Agent;
use App\Models\User;
use App\Models\DocumentDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//file
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Testing\MimeType;

class Document extends Model
{
    use HasFactory;

    public $table = 'documents';

    protected $fillable = [
        'document_no',
        'filename',
        'awb_no',
        'last_status',
        'recipient_user_id',
        'recipient_date',
        'sent_date',
        'agent_id',
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

    public function setSentDateAttribute( $value ) {
        $this->attributes['sent_date'] = (new Carbon($value))->toDateString();
    }

    public function agent()
    {
        return $this->hasOne(Agent::class, 'id','agent_id');
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id','recipient_user_id')->withDefault([
            'name' => '',
        ]);
    }

    public function details()
    {
        return $this->hasMany(DocumentDetail::class, 'document_id','id');
    }

    public function getFilenameBase64Attribute()
    {
        $filename = $this->attributes['filename'];
        if($filename != ""){
            $file = Storage::get('document/agent/' . $filename);
            $base64 = base64_encode($file);
            $mimetype = MimeType::from($filename);
            $file = 'data:' . $mimetype . ';base64,' . $base64;
        }else{
            $file = "";
        }
        return $file;
    }

    protected $appends = ['filename_base64'];
}
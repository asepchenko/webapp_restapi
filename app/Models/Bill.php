<?php

namespace App\Models;

use DateTimeInterface;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Agent;
use App\Models\BillDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//file
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Testing\MimeType;

class Bill extends Model
{
    use HasFactory;

    public $table = 'bills';

    protected $fillable = [
        'bill_number',
        'bill_number_manual',
        'bill_date',
        'bill_receipt_date',
        'agent_id',
        'due_date',
        'payment_date',
        'verified_date',
        'other_cost',
        'discount_percent',
        'discount',
        'tax_percent',
        'tax',
        'income_tax_percent',
        'income_tax',
        'filename',
        'total_volume',
        'total_colly',
        'subtotal',
        'grand_total',
        'last_status',
        'user_id',
        'verified_user_id',
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

    public function setDueDateAttribute( $value ) {
        $this->attributes['due_date'] = (new Carbon($value))->toDateString();
    }

    public function getTotalVolumeAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getTaxAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getIncomeTaxAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getTotalCollyAttribute($value)
    {
        return number_format($value,0,",",".");
    }

    public function getOtherCostAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getDiscountAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getSubtotalAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function getGrandTotalAttribute($value)
    {
        return number_format($value,2,",",".");
    }

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function user_verify()
    {
        return $this->hasOne(User::class, 'id','verified_user_id');
    }

    public function agents()
    {
        return $this->hasOne(Agent::class, 'id','agent_id');
    }

    public function details()
    {
        return $this->hasMany(billDetail::class, 'bill_number','bill_number')->with('orders');
    }

    public function getFilenameBase64Attribute()
    {
        $filename = $this->attributes['filename'];
        if($filename != ""){
            $file = Storage::get('bill/' . $filename);
            $base64 = base64_encode($file);
            $mimetype = MimeType::from($filename);
            $file = 'data:' . $mimetype . ';base64,' . $base64;
        }else{
            $file = "";
        }
        return $file;
    }

    public function getDPPAttribute()
    {
        return $this->attributes['subtotal'] - $this->attributes['discount'];
    }
    
    protected $appends = ['filename_base64','dpp'];
}
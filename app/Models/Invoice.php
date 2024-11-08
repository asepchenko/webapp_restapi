<?php

namespace App\Models;

use DateTimeInterface;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Customer;
use App\Models\InvoiceDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//file
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Testing\MimeType;

class Invoice extends Model
{
    use HasFactory;

    public $table = 'invoices';

    protected $fillable = [
        'invoice_number',
        'invoice_number_manual',
        'invoice_date',
        'customer_id',
        'service_group_id',
        'termin',
        'due_date',
        'send_date',
        'received_date',
        'payment_date',
        'verified_date',
        'other_cost',
        'is_disc_percent',
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
        'approval_user_id',
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

    public function user_approval()
    {
        return $this->hasOne(User::class, 'id','approval_user_id');
    }

    public function customers()
    {
        return $this->hasOne(Customer::class, 'id','customer_id');
    }

    public function details()
    {
        return $this->hasMany(InvoiceDetail::class, 'invoice_number','invoice_number')->with('orders');
    }

    public function getFilenameBase64Attribute()
    {
        $filename = $this->attributes['filename'];
        if($filename != ""){
            $file = Storage::get('invoice/' . $filename);
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

    public function getStatusCustomAttribute()
    {
        if($this->attributes['last_status'] == "Draft"){
            return "Piutang Pasif (belum faktur)";
        }else{
            return "";
        }
    }

    protected $appends = ['filename_base64','dpp','status_custom'];
}
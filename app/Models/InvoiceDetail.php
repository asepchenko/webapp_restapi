<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InvoiceDetail extends Model
{
    use HasFactory;

    public $table = 'invoice_details';

    protected $fillable = [
        'invoice_number',
        'order_number',
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

    public function users()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function orders()
    {
        return $this->hasOne(Order::class, 'order_number','order_number')->with('units','customers','customer_branchs','destinations','costs','services','servicegroups','trucking_price');
        //return $this->belongsTo(Order::class,'order_number')->with('customers','customer_branchs','destinations','costs');
    }

    public function getDriverNameAttribute()
    {
        $ord = $this->attributes['order_number'];
        $tmp = DB::select("select E.driver_name
        from orders A
        join manifest_details B on A.order_number=B.order_number
        join manifests C on B.manifest_number=C.manifest_number
        join invoice_details D on D.order_number=A.order_number
        join drivers E on C.driver_id=E.id
        where A.order_number='".$ord."'");
        if(count($tmp)>0){
            return $tmp[0]->driver_name;
        }else{
            return "";
        }
    }

    public function getTruckNameAttribute()
    {
        $ord = $this->attributes['order_number'];
        $tmp = DB::select("select concat(G.type_name, ' - ', F.police_number) as truck_name
        from orders A
        join manifest_details B on A.order_number=B.order_number
        join manifests C on B.manifest_number=C.manifest_number
        join invoice_details D on D.order_number=A.order_number
        join drivers E on C.driver_id=E.id
        join trucks F on C.truck_id=F.id
        join truck_types G on F.truck_type_id=G.id
        where A.order_number='".$ord."'");
        if(count($tmp)>0){
            return $tmp[0]->truck_name;
        }else{
            return "";
        }
    }

    public function getRefNoAttribute()
    {
        $ord = $this->attributes['order_number'];
        $tmp = DB::select("select ifnull(E.reference_number,'-') as ref_no
        from orders A
        join manifest_details B on A.order_number=B.order_number
        join manifests C on B.manifest_number=C.manifest_number
        join invoice_details D on D.order_number=A.order_number
        left join order_references E on A.order_number=E.order_number
        where A.order_number='".$ord."'");
        if(count($tmp)>0){
            return $tmp[0]->ref_no;
        }else{
            return "";
        }
    }
    protected $appends = ['driver_name','truck_name','ref_no'];
}
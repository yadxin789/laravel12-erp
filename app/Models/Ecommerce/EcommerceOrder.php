<?php
namespace App\Models\Ecommerce;
use Illuminate\Database\Eloquent\Model;
class EcommerceOrder extends Model {
    protected $fillable = ['platform','platform_order_no','buyer_name','buyer_phone','shipping_address','total_amount','status','items','synced_at'];
    protected $casts = ['items' => 'array', 'synced_at' => 'datetime'];
}

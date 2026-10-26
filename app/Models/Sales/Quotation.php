<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\QuotationStatus;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quote_no', 'customer_id', 'quote_date', 'valid_until', 'status',
        'total_amount', 'total_tax', 'total_net_amount', 'currency', 'remark',
        'created_by', 'approved_by', 'approved_at', 'updated_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:4',
        'total_tax' => 'decimal:4',
        'total_net_amount' => 'decimal:4',
        'status' => QuotationStatus::class,
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\MasterData\Customer::class);
    }

    public function lines()
    {
        return $this->hasMany(QuotationLine::class);
    }

    public function canSubmit()
    {
        return $this->status === QuotationStatus::Draft;
    }

    /**
     * 已审核（submitted）的报价单才能生成销售订单；converted 后不可重复转换，避免产生重复订单。
     */
    public function canConvert()
    {
        return $this->status === QuotationStatus::Submitted;
    }
}

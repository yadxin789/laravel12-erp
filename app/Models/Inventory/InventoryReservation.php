<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id', 'warehouse_id', 'batch_number', 'quantity', 'source_document_id',
        'source_document_type', 'source_document_no', 'reservation_date', 'expiry_date',
        'status', 'remark', 'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function item()
    {
        return $this->belongsTo(\App\Models\MasterData\Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\MasterData\Warehouse::class);
    }
}

<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'barcode', 'name', 'spec', 'description', 'image_path', 'spec_sheet_path', 'category_id', 'unit_id', 'purchase_unit_id',
        'purchase_conversion_rate', 'item_type', 'batch_management', 'serial_management',
        'quality_inspection_required', 'safety_stock', 'min_order_quantity', 'standard_cost',
        'active', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'batch_management' => 'boolean',
        'serial_management' => 'boolean',
        'quality_inspection_required' => 'boolean',
        'active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function purchaseUnit()
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id');
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'item_suppliers');
    }

    public function itemSuppliers()
    {
        return $this->hasMany(ItemSupplier::class);
    }

    public function bomHeaders()
    {
        return $this->hasMany(\App\Models\Production\BomHeader::class);
    }

    public function routingHeaders()
    {
        return $this->hasMany(\App\Models\Production\RoutingHeader::class);
    }

    public function inventoryBalances()
    {
        return $this->hasMany(\App\Models\Inventory\InventoryBalance::class);
    }

    /**
     * inventoryBalances 的别名，供 AI 分析服务使用。
     */
    public function balances()
    {
        return $this->inventoryBalances();
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }
        return asset('storage/' . $this->image_path);
    }

    /**
     * 是否已上传规格书（PDF）。
     */
    public function hasSpecSheet(): bool
    {
        return ! empty($this->spec_sheet_path);
    }

    /**
     * 规格书在线预览地址。
     */
    public function getSpecSheetUrlAttribute(): ?string
    {
        if (! $this->hasSpecSheet()) {
            return null;
        }

        return route('admin.items.spec-sheet.preview', $this);
    }

    public function getOrCreateBarcode(): string
    {
        if (!$this->barcode) {
            $this->barcode = 'ITEM-' . str_pad($this->id, 8, '0', STR_PAD_LEFT);
            $this->save();
        }
        return $this->barcode;
    }
}

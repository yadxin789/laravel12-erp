<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|string|max:50|unique:items,code,' . ($this->item?->id ?? ''),
            'barcode' => 'nullable|string|max:100|unique:items,barcode,' . ($this->item?->id ?? ''),
            'name' => 'required|string|max:200',
            'spec' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:item_categories,id',
            'unit_id' => 'required|exists:units,id',
            'purchase_unit_id' => 'nullable|exists:units,id',
            'purchase_conversion_rate' => 'nullable|numeric|min:0',
            'item_type' => 'required|string|max:30',
            'batch_management' => 'boolean',
            'serial_management' => 'boolean',
            'quality_inspection_required' => 'boolean',
            'safety_stock' => 'nullable|numeric|min:0',
            'min_order_quantity' => 'nullable|numeric|min:0',
            'standard_cost' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'active' => 'boolean',
        ];
    }
}

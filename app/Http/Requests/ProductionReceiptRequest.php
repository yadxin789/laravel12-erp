<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductionReceiptRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'production_order_id' => 'required|exists:production_orders,id',
            'receipt_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'remark' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|numeric|min:0',
            'lines.*.batch_number' => 'nullable|string|max:50',
            'lines.*.production_date' => 'nullable|date',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceiptRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'receipt_type' => 'required|string|max:30',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'receipt_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'remark' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|numeric|min:0',
            'lines.*.purchase_order_line_id' => 'nullable|exists:purchase_order_lines,id',
            'lines.*.batch_number' => 'nullable|string|max:50',
            'lines.*.production_date' => 'nullable|date',
            'lines.*.expiry_date' => 'nullable|date',
        ];
    }
}

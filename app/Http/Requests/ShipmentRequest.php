<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShipmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'shipment_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'remark' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|numeric|min:0',
            'lines.*.sales_order_line_id' => 'nullable|exists:sales_order_lines,id',
            'lines.*.batch_number' => 'nullable|string|max:50',
            'lines.*.serial_number' => 'nullable|string|max:100',
        ];
    }
}

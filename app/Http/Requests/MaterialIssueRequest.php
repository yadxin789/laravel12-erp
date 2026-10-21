<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaterialIssueRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'issue_type' => 'required|string|max:30',
            'production_order_id' => 'nullable|exists:production_orders,id',
            'work_center_id' => 'nullable|exists:work_centers,id',
            'issue_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'remark' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|numeric|min:0',
            'lines.*.production_order_line_id' => 'nullable|exists:production_order_lines,id',
            'lines.*.batch_number' => 'nullable|string|max:50',
        ];
    }
}

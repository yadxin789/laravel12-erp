<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductionOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'bom_header_id' => 'required|exists:bom_headers,id',
            'quantity' => 'required|numeric|min:0',
            'routing_header_id' => 'nullable|exists:routing_headers,id',
            'work_center_id' => 'nullable|exists:work_centers,id',
            'production_line_id' => 'nullable|exists:production_lines,id',
            'plan_start_date' => 'nullable|date',
            'plan_end_date' => 'nullable|date|after_or_equal:plan_start_date',
            'remark' => 'nullable|string',
        ];
    }
}

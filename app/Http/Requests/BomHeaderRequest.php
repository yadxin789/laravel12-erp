<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BomHeaderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|string|max:50|unique:bom_headers,code,' . ($this->bom_header?->id ?? ''),
            'item_id' => 'required|exists:items,id',
            'version' => 'required|string|max:20',
            'effective_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:effective_date',
            'quantity' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|numeric|min:0',
            'lines.*.scrap_rate' => 'nullable|numeric|min:0',
            'lines.*.unit_id' => 'nullable|exists:units,id',
        ];
    }
}

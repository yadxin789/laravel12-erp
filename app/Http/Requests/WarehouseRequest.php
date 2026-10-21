<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|string|max:30|unique:warehouses,code,' . ($this->warehouse?->id ?? ''),
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'warehouse_type' => 'nullable|string|max:30',
            'active' => 'boolean',
        ];
    }
}

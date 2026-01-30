<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;
        // For update, route parameter is 'product' (from apiResource)
        $productId = $this->route('product');

        return [
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:product_categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'model_id' => 'nullable|exists:product_models,id',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . ($productId ?? 'NULL') . ',id,tenant_id,' . $tenantId . ',deleted_at,NULL',
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . ($productId ?? 'NULL') . ',id,tenant_id,' . $tenantId . ',deleted_at,NULL',
            'base_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'compatible_ids' => 'nullable|array',
            'compatible_ids.*' => 'exists:products,id',
        ];
    }
}

<?php

namespace Modules\Sales\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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

        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'shipping_address' => 'nullable|string',
        ];
    }

    /**
     * Configure the validator instance to check tenant ownership.
     */
    public function withValidator($validator)
    {
        $tenantId = $this->user()->tenant_id;

        $validator->after(function ($validator) use ($tenantId) {
            // Validate that all products belong to the tenant
            if ($this->has('items')) {
                foreach ($this->items as $index => $item) {
                    $product = \Modules\Inventory\Models\Product::where('id', $item['product_id'])
                        ->where('tenant_id', $tenantId)
                        ->first();
                    
                    if (!$product) {
                        $validator->errors()->add("items.{$index}.product_id", 'Product does not belong to your tenant.');
                    }
                }
            }

            // Validate customer belongs to tenant if provided
            if ($this->has('customer_id') && $this->customer_id) {
                $customer = \Modules\Customer\Models\Customer::where('id', $this->customer_id)
                    ->where('tenant_id', $tenantId)
                    ->first();
                
                if (!$customer) {
                    $validator->errors()->add('customer_id', 'Customer does not belong to your tenant.');
                }
            }
        });
    }
}

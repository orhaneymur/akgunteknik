<?php

namespace Modules\Inventory\Services;

use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\PriceList;
use Modules\Inventory\Models\ProductPriceList;
use Modules\Customer\Models\Customer;

class PriceCalculationService
{
    /**
     * Get price for a product based on customer and quantity
     */
    public function getPrice(Product $product, ?Customer $customer = null, int $quantity = 1): float
    {
        // 1. Try to get price from customer's price list
        if ($customer && $customer->customerGroup) {
            $priceList = $this->getPriceListForCustomer($customer);
            if ($priceList) {
                $price = $this->getPriceFromList($product, $priceList, $quantity);
                if ($price !== null) {
                    return $this->applyGroupDiscount($price, $customer);
                }
            }
        }

        // 2. Try default price list
        $defaultPriceList = PriceList::where('tenant_id', $product->tenant_id)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();

        if ($defaultPriceList) {
            $price = $this->getPriceFromList($product, $defaultPriceList, $quantity);
            if ($price !== null) {
                return $customer ? $this->applyGroupDiscount($price, $customer) : $price;
            }
        }

        // 3. Fallback to base price
        $basePrice = $product->base_price ?? 0;
        return $customer ? $this->applyGroupDiscount($basePrice, $customer) : $basePrice;
    }

    /**
     * Get price list for customer based on customer type and group
     */
    protected function getPriceListForCustomer(Customer $customer): ?PriceList
    {
        // Map customer type to price list type
        $priceListType = $customer->customer_type === 'b2c' ? 'retail' : 'wholesale';

        $priceList = PriceList::where('tenant_id', $customer->tenant_id)
            ->where('type', $priceListType)
            ->where('is_active', true)
            ->first();

        return $priceList;
    }

    /**
     * Get price from price list based on quantity
     */
    protected function getPriceFromList(Product $product, PriceList $priceList, int $quantity): ?float
    {
        $productPrice = ProductPriceList::where('price_list_id', $priceList->id)
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($query) use ($quantity) {
                $query->whereNull('max_quantity')
                    ->orWhere('max_quantity', '>=', $quantity);
            })
            ->orderBy('min_quantity', 'desc') // Get highest matching tier
            ->first();

        return $productPrice ? (float) $productPrice->price : null;
    }

    /**
     * Apply customer group discount
     */
    protected function applyGroupDiscount(float $price, Customer $customer): float
    {
        if (!$customer->customerGroup || !$customer->customerGroup->discount_percentage) {
            return $price;
        }

        $discount = $price * ($customer->customerGroup->discount_percentage / 100);
        return $price - $discount;
    }
}

<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Inventory\Models\PriceList;
use Modules\Inventory\Models\ProductPriceList;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PriceListController extends BaseController
{
    public function index(Request $request)
    {
        $priceLists = PriceList::where('tenant_id', $request->user()->tenant_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $this->respondSuccess($priceLists, 'Price lists retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:price_lists,code,NULL,id,tenant_id,' . $request->user()->tenant_id,
            'type' => 'required|in:wholesale,retail,custom',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        try {
            DB::beginTransaction();

            // If setting as default, unset other defaults
            if ($request->is_default) {
                PriceList::where('tenant_id', $request->user()->tenant_id)
                    ->update(['is_default' => false]);
            }

            $priceList = PriceList::create([
                'tenant_id' => $request->user()->tenant_id,
                'name' => $request->name,
                'code' => $request->code,
                'type' => $request->type,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
                'is_default' => $request->is_default ?? false,
                'sort_order' => $request->sort_order ?? 0,
            ]);

            DB::commit();

            return $this->respondSuccess($priceList, 'Price list created successfully.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondError(['error' => $e->getMessage()], 'Failed to create price list.', 500);
        }
    }

    public function show(Request $request, $id)
    {
        $priceList = PriceList::with('productPrices.product')
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$priceList) {
            return $this->respondError([], 'Price list not found.', 404);
        }

        return $this->respondSuccess($priceList, 'Price list retrieved successfully.');
    }

    public function update(Request $request, $id)
    {
        $priceList = PriceList::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$priceList) {
            return $this->respondError([], 'Price list not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:50|unique:price_lists,code,' . $id . ',id,tenant_id,' . $request->user()->tenant_id,
            'type' => 'sometimes|in:wholesale,retail,custom',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        try {
            DB::beginTransaction();

            // If setting as default, unset other defaults
            if ($request->has('is_default') && $request->is_default) {
                PriceList::where('tenant_id', $request->user()->tenant_id)
                    ->where('id', '!=', $id)
                    ->update(['is_default' => false]);
            }

            $priceList->update($request->all());

            DB::commit();

            return $this->respondSuccess($priceList, 'Price list updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondError(['error' => $e->getMessage()], 'Failed to update price list.', 500);
        }
    }

    public function addProduct(Request $request, $id)
    {
        $priceList = PriceList::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$priceList) {
            return $this->respondError([], 'Price list not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'price' => 'required|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        $productPrice = ProductPriceList::updateOrCreate(
            [
                'price_list_id' => $id,
                'product_id' => $request->product_id,
                'min_quantity' => $request->min_quantity ?? 1,
            ],
            [
                'price' => $request->price,
                'max_quantity' => $request->max_quantity,
                'is_active' => true,
            ]
        );

        return $this->respondSuccess($productPrice, 'Product added to price list successfully.');
    }

    public function bulkUpdate(Request $request, $id)
    {
        $priceList = PriceList::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$priceList) {
            return $this->respondError([], 'Price list not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.min_quantity' => 'nullable|integer|min:1',
            'products.*.max_quantity' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->products as $productData) {
                ProductPriceList::updateOrCreate(
                    [
                        'price_list_id' => $id,
                        'product_id' => $productData['product_id'],
                        'min_quantity' => $productData['min_quantity'] ?? 1,
                    ],
                    [
                        'price' => $productData['price'],
                        'max_quantity' => $productData['max_quantity'] ?? null,
                        'is_active' => true,
                    ]
                );
            }

            DB::commit();

            return $this->respondSuccess(null, 'Products updated in price list successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondError(['error' => $e->getMessage()], 'Failed to update products.', 500);
        }
    }
}

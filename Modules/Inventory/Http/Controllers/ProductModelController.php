<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Inventory\Models\ProductModel;
use Modules\Inventory\Models\Brand;
use Illuminate\Support\Facades\Validator;

class ProductModelController extends BaseController
{
    public function index(Request $request)
    {
        $query = ProductModel::with('brand')
            ->where('tenant_id', $request->user()->tenant_id);

        // Filter by brand
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $models = $query->orderBy('sort_order')->get();

        return $this->respondSuccess($models, 'Product models retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        // Validate brand belongs to tenant
        $brand = Brand::where('id', $request->brand_id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$brand) {
            return $this->respondError([], 'Brand not found or does not belong to your tenant.', 404);
        }

        // Check unique code per tenant and brand
        if ($request->code) {
            $exists = ProductModel::where('tenant_id', $request->user()->tenant_id)
                ->where('brand_id', $request->brand_id)
                ->where('code', $request->code)
                ->exists();

            if ($exists) {
                return $this->respondError(['code' => 'Model code already exists for this brand.'], 'Validation Error', 422);
            }
        }

        $model = ProductModel::create([
            'tenant_id' => $request->user()->tenant_id,
            'brand_id' => $request->brand_id,
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return $this->respondSuccess($model->load('brand'), 'Product model created successfully.', 201);
    }

    public function update(Request $request, $id)
    {
        $model = ProductModel::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$model) {
            return $this->respondError([], 'Product model not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'brand_id' => 'sometimes|required|exists:brands,id',
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        // Validate brand if changed
        if ($request->has('brand_id')) {
            $brand = Brand::where('id', $request->brand_id)
                ->where('tenant_id', $request->user()->tenant_id)
                ->first();

            if (!$brand) {
                return $this->respondError([], 'Brand not found or does not belong to your tenant.', 404);
            }
        }

        // Check unique code if changed
        if ($request->has('code') && $request->code) {
            $exists = ProductModel::where('tenant_id', $request->user()->tenant_id)
                ->where('brand_id', $request->brand_id ?? $model->brand_id)
                ->where('code', $request->code)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return $this->respondError(['code' => 'Model code already exists for this brand.'], 'Validation Error', 422);
            }
        }

        $model->update($request->all());

        return $this->respondSuccess($model->load('brand'), 'Product model updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $model = ProductModel::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$model) {
            return $this->respondError([], 'Product model not found.', 404);
        }

        // Check if model has products
        if ($model->products()->count() > 0) {
            return $this->respondError([], 'Cannot delete model with existing products.', 400);
        }

        $model->delete();

        return $this->respondSuccess(null, 'Product model deleted successfully.');
    }
}

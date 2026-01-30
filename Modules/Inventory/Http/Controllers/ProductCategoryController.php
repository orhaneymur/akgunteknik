<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Inventory\Models\ProductCategory;
use Illuminate\Support\Facades\Validator;

class ProductCategoryController extends BaseController
{
    public function index(Request $request)
    {
        $categories = ProductCategory::where('tenant_id', $request->user()->tenant_id)
            ->orderBy('sort_order')
            ->get();

        return $this->respondSuccess($categories, 'Product categories retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:product_categories,code,NULL,id,tenant_id,' . $request->user()->tenant_id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        $category = ProductCategory::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return $this->respondSuccess($category, 'Product category created successfully.', 201);
    }

    public function update(Request $request, $id)
    {
        $category = ProductCategory::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$category) {
            return $this->respondError([], 'Product category not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:50|unique:product_categories,code,' . $id . ',id,tenant_id,' . $request->user()->tenant_id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        $category->update($request->all());

        return $this->respondSuccess($category, 'Product category updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $category = ProductCategory::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$category) {
            return $this->respondError([], 'Product category not found.', 404);
        }

        // Check if category has products
        if ($category->products()->count() > 0) {
            return $this->respondError([], 'Cannot delete category with existing products.', 400);
        }

        $category->delete();

        return $this->respondSuccess(null, 'Product category deleted successfully.');
    }
}

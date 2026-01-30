<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Inventory\Models\Brand;
use Illuminate\Support\Facades\Validator;

class BrandController extends BaseController
{
    public function index(Request $request)
    {
        $brands = Brand::where('tenant_id', $request->user()->tenant_id)
            ->orderBy('sort_order')
            ->get();

        return $this->respondSuccess($brands, 'Brands retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:brands,code,NULL,id,tenant_id,' . $request->user()->tenant_id,
            'logo' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        $brand = Brand::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $request->name,
            'code' => $request->code,
            'logo' => $request->logo,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return $this->respondSuccess($brand, 'Brand created successfully.', 201);
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$brand) {
            return $this->respondError([], 'Brand not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:50|unique:brands,code,' . $id . ',id,tenant_id,' . $request->user()->tenant_id,
            'logo' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        $brand->update($request->all());

        return $this->respondSuccess($brand, 'Brand updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $brand = Brand::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$brand) {
            return $this->respondError([], 'Brand not found.', 404);
        }

        // Check if brand has products
        if ($brand->products()->count() > 0) {
            return $this->respondError([], 'Cannot delete brand with existing products.', 400);
        }

        $brand->delete();

        return $this->respondSuccess(null, 'Brand deleted successfully.');
    }
}

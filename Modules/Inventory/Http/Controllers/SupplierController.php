<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Inventory\Models\Supplier;
use Illuminate\Support\Facades\Validator;

class SupplierController extends BaseController
{
    public function index(Request $request)
    {
        $query = Supplier::where('tenant_id', $request->user()->tenant_id);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%");
            });
        }

        // If 'all' parameter is provided, return all without pagination
        if ($request->has('all')) {
            $suppliers = $query->latest()->get();
            return $this->respondSuccess($suppliers, 'All suppliers retrieved successfully.');
        }

        // Pagination with default 15 items per page
        $perPage = $request->input('per_page', 15);
        $suppliers = $query->latest()->paginate($perPage);

        return $this->respondSuccess($suppliers, 'Suppliers retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        $supplier = Supplier::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $request->name,
            'contact_name' => $request->contact_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return $this->respondSuccess($supplier, 'Supplier created successfully.', 201);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$supplier) {
            return $this->respondError([], 'Supplier not found.', 404);
        }

        $supplier->update($request->all());

        return $this->respondSuccess($supplier, 'Supplier updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $supplier = Supplier::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$supplier) {
            return $this->respondError([], 'Supplier not found.', 404);
        }

        // Soft delete
        $supplier->delete();

        return $this->respondSuccess(null, 'Supplier deleted successfully.');
    }
}

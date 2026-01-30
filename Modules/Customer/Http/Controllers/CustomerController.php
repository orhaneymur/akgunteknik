<?php

namespace Modules\Customer\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Customer\Models\Customer;
use Modules\Customer\Http\Requests\StoreCustomerRequest;
use Illuminate\Support\Facades\Validator;

class CustomerController extends BaseController
{
    public function index(Request $request)
    {
        $query = Customer::with('customerGroup')->where('tenant_id', $request->user()->tenant_id);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('tax_number', 'like', "%{$search}%");
            });
        }

        // If 'all' parameter is provided, return all without pagination
        if ($request->has('all')) {
            $customers = $query->latest()->get();
            return $this->respondSuccess($customers, 'All customers retrieved successfully.');
        }

        // Pagination with default 15 items per page
        $perPage = $request->input('per_page', 15);
        $customers = $query->latest()->paginate($perPage);
        
        return $this->respondSuccess($customers, 'Customers retrieved successfully.');
    }

    public function store(StoreCustomerRequest $request)
    {
        // Validation is handled by FormRequest

        // Get default customer group if not provided
        $customerGroupId = $request->customer_group_id;
        if (!$customerGroupId) {
            $defaultGroup = \Modules\Customer\Models\CustomerGroup::where('tenant_id', $request->user()->tenant_id)
                ->where('code', 'STANDARD')
                ->first();
            $customerGroupId = $defaultGroup ? $defaultGroup->id : null;
        }

        $customer = Customer::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $request->name,
            'customer_type' => $request->customer_type ?? 'b2b',
            'customer_group_id' => $customerGroupId,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'tax_number' => $request->tax_number,
            'tax_office' => $request->tax_office,
        ]);

        return $this->respondSuccess($customer, 'Customer created successfully.', 201);
    }

    public function show(Request $request, $id)
    {
        $customer = Customer::with('customerGroup')
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$customer) {
            return $this->respondError([], 'Customer not found.', 404);
        }

        return $this->respondSuccess($customer, 'Customer details retrieved.');
    }

    public function update(StoreCustomerRequest $request, $id)
    {
        $customer = Customer::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$customer) {
            return $this->respondError([], 'Customer not found.', 404);
        }

        // Validation is handled by FormRequest

        $customer->update($request->all());

        return $this->respondSuccess($customer, 'Customer updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $customer = Customer::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$customer) {
            return $this->respondError([], 'Customer not found.', 404);
        }

        // Soft delete
        $customer->delete();

        return $this->respondSuccess(null, 'Customer deleted successfully.');
    }
}

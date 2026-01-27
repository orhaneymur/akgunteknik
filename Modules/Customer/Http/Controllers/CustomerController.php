<?php

namespace Modules\Customer\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Customer\Models\Customer;
use Illuminate\Support\Facades\Validator;

class CustomerController extends BaseController
{
    public function index(Request $request)
    {
        $customers = Customer::where('tenant_id', $request->user()->tenant_id)
            ->latest()
            ->get();
        return $this->respondSuccess($customers, 'Customers retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        $customer = Customer::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $request->name,
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
        $customer = Customer::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$customer) {
            return $this->respondError([], 'Customer not found.', 404);
        }

        return $this->respondSuccess($customer, 'Customer details retrieved.');
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$customer) {
            return $this->respondError([], 'Customer not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        $customer->update($request->all());

        return $this->respondSuccess($customer, 'Customer updated successfully.');
    }
}

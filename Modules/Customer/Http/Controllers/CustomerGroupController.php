<?php

namespace Modules\Customer\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Customer\Models\CustomerGroup;
use Illuminate\Support\Facades\Validator;

class CustomerGroupController extends BaseController
{
    public function index(Request $request)
    {
        $groups = CustomerGroup::where('tenant_id', $request->user()->tenant_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $this->respondSuccess($groups, 'Customer groups retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:customer_groups,code,NULL,id,tenant_id,' . $request->user()->tenant_id,
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        $group = CustomerGroup::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $request->name,
            'code' => $request->code,
            'discount_percentage' => $request->discount_percentage ?? 0,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return $this->respondSuccess($group, 'Customer group created successfully.', 201);
    }

    public function update(Request $request, $id)
    {
        $group = CustomerGroup::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$group) {
            return $this->respondError([], 'Customer group not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:50|unique:customer_groups,code,' . $id . ',id,tenant_id,' . $request->user()->tenant_id,
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        $group->update($request->all());

        return $this->respondSuccess($group, 'Customer group updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $group = CustomerGroup::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$group) {
            return $this->respondError([], 'Customer group not found.', 404);
        }

        // Check if group has customers
        if ($group->customers()->count() > 0) {
            return $this->respondError([], 'Cannot delete group with existing customers.', 400);
        }

        $group->delete();

        return $this->respondSuccess(null, 'Customer group deleted successfully.');
    }
}

<?php

namespace Modules\Core\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Core\Models\Warehouse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        $users = User::with('branch')
            ->where('tenant_id', $request->user()->tenant_id)
            ->get();

        return $this->respondSuccess($users, 'Users retrieved successfully.');
    }

    public function store(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        
        // Convert empty string to null for warehouse_id
        $data = $request->all();
        if (isset($data['warehouse_id']) && $data['warehouse_id'] === '') {
            $data['warehouse_id'] = null;
        }

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,NULL,id,tenant_id,' . $tenantId,
            'password' => 'required|string|min:8',
            'role' => 'required|in:owner,admin,manager,staff',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        // Validate warehouse belongs to tenant
        if ($data['warehouse_id']) {
            $warehouse = Warehouse::where('id', $data['warehouse_id'])
                ->where('tenant_id', $tenantId)
                ->first();
            
            if (!$warehouse) {
                return $this->respondError(['warehouse_id' => ['Selected warehouse does not belong to your tenant.']], 'Validation Error', 422);
            }
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'warehouse_id' => $data['warehouse_id'],
            'tenant_id' => $tenantId,
        ]);

        return $this->respondSuccess($user, 'User created successfully.', 201);
    }

    public function show(Request $request, $id)
    {
        $user = User::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$user) {
            return $this->respondError([], 'User not found.', 404);
        }

        return $this->respondSuccess($user, 'User details retrieved successfully.');
    }

    public function update(Request $request, $id)
    {
        $tenantId = $request->user()->tenant_id;
        $user = User::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$user) {
            return $this->respondError([], 'User not found.', 404);
        }

        // Convert empty string to null for warehouse_id
        $data = $request->all();
        if (isset($data['warehouse_id']) && $data['warehouse_id'] === '') {
            $data['warehouse_id'] = null;
        }

        $validator = Validator::make($data, [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id . ',id,tenant_id,' . $tenantId,
            'password' => 'nullable|string|min:8',
            'role' => 'sometimes|in:owner,admin,manager,staff',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        $updateData = $data;
        unset($updateData['password']);
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return $this->respondSuccess($user, 'User updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $user = User::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$user) {
            return $this->respondError([], 'User not found.', 404);
        }

        // Prevent deleting yourself
        if ($user->id === $request->user()->id) {
            return $this->respondError([], 'You cannot delete your own account.', 403);
        }

        $user->delete();

        return $this->respondSuccess([], 'User deleted successfully.');
    }
}

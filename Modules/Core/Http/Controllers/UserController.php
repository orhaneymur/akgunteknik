<?php

namespace Modules\Core\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:owner,manager,staff',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'warehouse_id' => $request->warehouse_id,
            'tenant_id' => $request->user()->tenant_id,
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
        $user = User::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$user) {
            return $this->respondError([], 'User not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'sometimes|in:owner,manager,staff',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        $data = $request->except(['password']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

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

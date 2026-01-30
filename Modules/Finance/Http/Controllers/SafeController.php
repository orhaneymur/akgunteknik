<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Finance\Models\Safe;
use Illuminate\Support\Facades\Validator;

class SafeController extends BaseController
{
    public function index(Request $request)
    {
        $safes = Safe::where('tenant_id', $request->user()->tenant_id)
            ->latest()
            ->get();

        return $this->respondSuccess($safes, 'Safes retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,bank',
            'currency' => 'required|string|max:3',
            'iban' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        $safe = Safe::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $request->name,
            'type' => $request->type,
            'currency' => $request->currency,
            'iban' => $request->iban,
            'balance' => 0,
        ]);

        return $this->respondSuccess($safe, 'Safe created successfully.', 201);
    }

    public function show(Request $request, $id)
    {
        $safe = Safe::with([
            'transactions' => function ($query) {
                $query->latest()->limit(50);
            }
        ])
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$safe) {
            return $this->respondError([], 'Safe not found.', 404);
        }

        return $this->respondSuccess($safe, 'Safe retrieved successfully.');
    }

    public function update(Request $request, $id)
    {
        $safe = Safe::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$safe) {
            return $this->respondError([], 'Safe not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:cash,bank',
            'currency' => 'sometimes|required|string|max:3',
            'iban' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        $safe->update($request->only(['name', 'type', 'currency', 'iban']));

        return $this->respondSuccess($safe, 'Safe updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $safe = Safe::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$safe) {
            return $this->respondError([], 'Safe not found.', 404);
        }

        $safe->delete();

        return $this->respondSuccess([], 'Safe deleted successfully.');
    }
}

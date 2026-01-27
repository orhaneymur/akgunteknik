<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Finance\Models\Safe;
use Modules\Finance\Models\Transaction;

class SafeController extends Controller
{
    public function index()
    {
        return Safe::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,bank',
            'currency' => 'required|string|max:3',
            'iban' => 'nullable|string'
        ]);

        return Safe::create($validated);
    }

    public function show($id)
    {
        $safe = Safe::with([
            'transactions' => function ($query) {
                $query->latest()->limit(50);
            }
        ])->findOrFail($id);

        return $safe;
    }

    public function update(Request $request, $id)
    {
        $safe = Safe::findOrFail($id);
        $safe->update($request->all());
        return $safe;
    }

    public function destroy($id)
    {
        Safe::destroy($id);
        return response()->noContent();
    }
}

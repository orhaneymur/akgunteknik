<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Finance\Models\ExpenseCategory;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        return ExpenseCategory::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string',
            'description' => 'nullable|string'
        ]);

        return ExpenseCategory::create($validated);
    }

    public function update(Request $request, $id)
    {
        $category = ExpenseCategory::findOrFail($id);
        $category->update($request->all());
        return $category;
    }

    public function destroy($id)
    {
        ExpenseCategory::destroy($id);
        return response()->noContent();
    }
}

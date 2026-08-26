<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use Illuminate\Http\Request;

class ExpenseTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:business.manage');
    }

    public function index()
    {
        $types = ExpenseType::orderBy('sort_order')->orderBy('name')->get();
        return view('backend.content.expense_types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:expense_types,name',
            'status' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $data['status'] = $request->boolean('status', true);
        ExpenseType::create($data);
        return back()->with('message','DA / Expense type created successfully');
    }

    public function update(Request $request, ExpenseType $expenseType)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:expense_types,name,'.$expenseType->id,
            'status' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $data['status'] = $request->boolean('status');
        $expenseType->update($data);
        return back()->with('message','DA / Expense type updated successfully');
    }

    public function destroy(ExpenseType $expenseType)
    {
        $expenseType->delete();
        return back()->with('message','DA / Expense type deleted successfully');
    }
}

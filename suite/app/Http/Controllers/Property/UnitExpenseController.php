<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\Unit;
use App\Modules\Property\Models\UnitExpense;
use Illuminate\Http\Request;

class UnitExpenseController extends Controller
{
    public function store(Request $request, Property $property, Unit $unit)
    {
        abort_unless($unit->property_id === $property->id, 404);

        $validated = $request->validate([
            'category'    => 'required|in:maintenance,rates,insurance,levy,other',
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0.01',
            'date'        => 'required|date',
            'notes'       => 'nullable|string',
        ]);

        $unit->expenses()->create($validated);

        return back()->with('success', 'Expense recorded.');
    }

    public function destroy(Property $property, Unit $unit, UnitExpense $expense)
    {
        abort_unless($unit->property_id === $property->id && $expense->unit_id === $unit->id, 404);

        $expense->delete();

        return back()->with('success', 'Expense removed.');
    }
}

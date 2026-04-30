<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SalesExpenseController extends Controller
{
    public function store(Request $request)
{
    // proteksi admin
    if (auth()->user()->role !== 'admin') {
        abort(403);
    }

    $request->validate([
        'user_id' => 'required|exists:users,id',
        'amount' => 'required|numeric|min:1',
        'expense_date' => 'required|date'
    ]);

    \DB::table('sales_expenses')->insert([
        'user_id' => $request->user_id,
        'amount' => $request->amount,
        'type' => 'makan',
        'expense_date' => $request->expense_date,
        'notes' => 'Biaya makan sales',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return back()->with('success', 'Biaya makan berhasil disimpan');
}
}

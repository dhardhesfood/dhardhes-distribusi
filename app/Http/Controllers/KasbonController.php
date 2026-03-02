<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kasbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KasbonController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Kasbon::with(['user','creator'])
            ->orderByDesc('created_at');

        /*
        |--------------------------------------------------------------------------
        | ROLE RESTRICTION
        |--------------------------------------------------------------------------
        | Sales hanya boleh melihat kasbon miliknya sendiri
        */
        if (auth()->user()->role === 'sales') {
            $query->where('user_id', auth()->id());
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER PERIODE
        |--------------------------------------------------------------------------
        */
        if ($request->filled('filter')) {

            if ($request->filter === 'daily') {
                $query->whereDate('created_at', Carbon::today());
            }

            if ($request->filter === 'weekly') {
                $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
            }

            if ($request->filter === 'monthly') {
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
            }
        }

        $kasbons = $query->get();

        /*
        |--------------------------------------------------------------------------
        | TOTAL KASBON OPEN (REAL REMAINING)
        |--------------------------------------------------------------------------
        */
        if (auth()->user()->role === 'admin') {
            $totalOpen = $kasbons
                ->where('status','open')
                ->sum(function($k){
                    return $k->amount_total - $k->amount_paid;
                });
        } else {
            $totalOpen = $kasbons
                ->where('status','open')
                ->where('user_id', auth()->id())
                ->sum(function($k){
                    return $k->amount_total - $k->amount_paid;
                });
        }

        $groupedKasbons = $kasbons->groupBy('user_id');

        /*
        |--------------------------------------------------------------------------
        | HITUNG TOTAL FEE (SAMA DENGAN DASHBOARD FEE)
        |--------------------------------------------------------------------------
        | Konsinyasi + Cash Sale Locked
        */

        $salesQuery = DB::table('users as u')
            ->leftJoin('sales_transactions as st', 'st.user_id', '=', 'u.id')
            ->where('u.role', 'sales')
            ->select(
                'u.id',
                DB::raw('COALESCE(SUM(st.total_fee),0) as total_konsinyasi'),
                DB::raw('(
                    SELECT COALESCE(SUM(cs.fee_total),0)
                    FROM cash_sales cs
                    WHERE cs.user_id = u.id
                      AND cs.status = "locked"
                ) as total_tunai')
            )
            ->groupBy('u.id');

        if (auth()->user()->role === 'sales') {
            $salesQuery->where('u.id', auth()->id());
        }

        $rawFees = $salesQuery->get();

        $fees = [];

        foreach ($rawFees as $row) {
            $fees[$row->id] = (float)$row->total_konsinyasi + (float)$row->total_tunai;
        }

        return view('kasbons.index', compact(
            'kasbons',
            'groupedKasbons',
            'totalOpen',
            'fees'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $users = User::all();
        return view('kasbons.create', compact('users'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'amount'     => 'required|numeric|min:1',
            'description'=> 'nullable|string'
        ]);

        Kasbon::create([
            'user_id'        => $request->user_id,
            'created_by'     => auth()->id(),
            'amount_total'   => $request->amount,
            'amount_paid'    => 0,
            'type'           => 'manual',
            'reference_id'   => null,
            'reference_type' => null,
            'description'    => $request->description,
            'status'         => 'open',
        ]);

        return redirect()->route('kasbons.index')
            ->with('success','Kasbon berhasil dibuat.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(Kasbon $kasbon)
    {
        $users = User::all();
        return view('kasbons.edit', compact('kasbon','users'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Kasbon $kasbon)
    {
        if ($kasbon->type === 'shortage') {
            return redirect()->route('kasbons.index')
                ->withErrors('Kasbon shortage tidak bisa diedit manual.');
        }

        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'amount'     => 'required|numeric|min:1',
            'description'=> 'nullable|string'
        ]);

        $kasbon->update([
            'user_id'      => $request->user_id,
            'amount_total' => $request->amount,
            'description'  => $request->description,
        ]);

        return redirect()->route('kasbons.index')
            ->with('success','Kasbon berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */
    public function destroy(Kasbon $kasbon)
    {
        if ($kasbon->type === 'shortage') {
            return redirect()->route('kasbons.index')
                ->withErrors('Kasbon shortage tidak bisa dihapus.');
        }

        $kasbon->delete();

        return redirect()->route('kasbons.index')
            ->with('success','Kasbon berhasil dihapus.');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use App\Models\SalesSettlement;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReceivableController extends Controller
{
    public function index(Request $request)
    {
        $query = Receivable::with([
            'transaction.store',
            'transaction.visit',
            'payments.user'
        ]);

        // ===============================
        // FILTER STATUS
        // ===============================
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $receivables = $query
            ->orderBy('due_date', 'asc')
            ->get()
            ->map(function ($item) {

                $today = Carbon::today();
                $aging = null;
                $isOverdue = false;

                if ($item->due_date) {
                    $due = Carbon::parse($item->due_date);
                    $aging = $today->diffInDays($due, false);

                    if ($aging < 0 && $item->status !== 'paid') {
                        $isOverdue = true;
                    }
                }

                // =========================================
                // TAMBAHAN INFO ASAL PIUTANG
                // =========================================
                $item->visit_id = optional($item->transaction->visit)->id;
                $item->visit_date = optional($item->transaction->visit)->visit_date;
                $item->transaction_id = $item->sales_transaction_id;

                $item->aging_days = $aging;
                $item->is_overdue = $isOverdue;

                return $item;
            });

        return view('receivables.index', [
            'receivables'   => $receivables,
            'currentStatus' => $request->status ?? 'all'
        ]);
    }

    public function pay(Request $request, Receivable $receivable)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,transfer',
            'payment_date'   => 'required|date',
            'notes'          => 'nullable|string'
        ]);

        DB::transaction(function () use ($request, $receivable) {

            $amount = (int) $request->amount;
            $paymentDate = Carbon::parse($request->payment_date)->format('Y-m-d');

            // ==========================================
            // HARD LOCK: CEK SETTLEMENT SUDAH CLOSED?
            // ==========================================
            $settlement = SalesSettlement::where('user_id', auth()->id())
                ->whereDate('settlement_date', $paymentDate)
                ->where('status', 'closed')
                ->first();

            if ($settlement) {
                abort(403, 'Settlement tanggal tersebut sudah ditutup.');
            }

            if ($amount > $receivable->remaining_amount) {
                throw new \Exception('Jumlah bayar melebihi sisa piutang.');
            }

            ReceivablePayment::create([
                'receivable_id' => $receivable->id,
                'user_id'       => auth()->id(),
                'amount'        => $amount,
                'payment_method'=> $request->payment_method,
                'payment_date'  => $paymentDate,
                'notes'         => $request->notes,
            ]);

            $newPaid      = $receivable->paid_amount + $amount;
            $newRemaining = $receivable->remaining_amount - $amount;

            $status = 'partial';

            if ($newRemaining == 0) {
                $status = 'paid';
            }

            $receivable->update([
                'paid_amount'      => $newPaid,
                'remaining_amount' => $newRemaining,
                'status'           => $status
            ]);
        });

        return back()->with('success', 'Pembayaran piutang berhasil dicatat.');
    }
}
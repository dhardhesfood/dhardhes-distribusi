<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SalesDisciplineController extends Controller
{
    public function run()
    {
        // double safety (meskipun sudah di route admin)
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        Artisan::call('app:check-sales-discipline');

        return back()->with('success', 'Disiplin sales berhasil dihitung ulang.');
    }
}
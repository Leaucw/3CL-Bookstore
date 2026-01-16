<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:manager']);
    }

    /**
     * Monthly report: customers registered in the selected month.
     * ?month=YYYY-MM (defaults to current month)
     */
    public function customersMonthly(Request $request)
    {
        $monthParam = $request->input('month');

        if ($monthParam && preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
            $start = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
        } else {
            $start = now()->startOfMonth();
            $monthParam = $start->format('Y-m');
        }
        $end = (clone $start)->endOfMonth();

        // Aggregate daily counts for customers created in this month
        $rows = DB::table('users')
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->where('role', 'customer')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        // Fill zeroes for missing days
        $period = CarbonPeriod::create($start, $end);
        $labels = [];
        $data   = [];
        foreach ($period as $day) {
            $dateKey   = $day->toDateString();
            $labels[]  = $day->format('j');                // 1..31
            $data[]    = (int) ($rows[$dateKey]->c ?? 0);  // count or 0
        }

        $total     = array_sum($data);
        $max       = max($data) ?: 1; // avoid div by zero
        $monthName = $start->format('F Y');

        return view('manager.reports.customers', compact(
            'labels','data','total','max','monthParam','monthName','start','end'
        ));
    }
}

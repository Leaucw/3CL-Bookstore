<?php
//Author: Leau Chee Way
namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Reports\{DailySalesReport, WeeklySalesReport, MonthlySalesReport};

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $start = Carbon::parse($request->query('start', now()->subMonth()->toDateString()))->startOfDay();
        $end   = Carbon::parse($request->query('end', now()->toDateString()))->endOfDay();
        $group = $request->query('group', 'day');
        $top   = (int) $request->query('top', 10);

        $reportClass = match ($group) {
            'week'  => WeeklySalesReport::class,
            'month' => MonthlySalesReport::class,
            default => DailySalesReport::class,
        };

        $report = new $reportClass(
            start: $start,
            end: $end,
            top: $top,
            types: ['sale'],          
            dateCol: 'sm.created_at'  
        );

        $data = $report->build();

        return view('manager.reports.sales', [
            'start'      => $start,
            'end'        => $end,
            'group'      => $group,
            'top'        => $top,
            'kpi'        => $data['kpi'],
            'avgOrder'   => $data['avgMovement'], 
            'series'     => $data['series'],
            'topBooks'   => $data['topBooks'],
            'byCategory' => $data['byCategory'],
        ]);
    }
}

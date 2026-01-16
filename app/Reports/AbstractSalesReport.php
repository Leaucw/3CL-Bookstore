<?php
//Author: Leau Chee Way
namespace App\Reports;

use Illuminate\Support\Facades\DB;

abstract class AbstractSalesReport
{
    public function __construct(
        protected \Carbon\CarbonInterface $start,
        protected \Carbon\CarbonInterface $end,
        protected int $top = 10,
        protected array $types = ['sale'],   
        protected string $dateCol = 'sm.created_at' 
    ) {}

    abstract protected function bucketExpr(): string;

    final public function build(): array
    {
        $bucket       = $this->bucketExpr();
        $kpi          = $this->computeKPI();
        $avgMovement  = $this->computeAverageMovement($kpi);
        $series       = $this->buildSeries($bucket);
        $topBooks     = $this->buildTopBooks();
        $byCategory   = $this->buildByCategory();

        return compact('kpi','avgMovement','series','topBooks','byCategory');
    }

    protected function baseQuery()
    {
        return DB::table('stock_movements as sm')
            ->join('books as b', 'b.id', '=', 'sm.book_id')
            ->whereBetween($this->dateCol, [$this->start, $this->end])
            ->whereIn('sm.type', $this->types);
    }

    protected function computeKPI()
    {
        return $this->baseQuery()
            ->selectRaw('SUM(ABS(sm.quantity_change) * b.price) as revenue')
            ->selectRaw('COUNT(DISTINCT sm.id) as movements')
            ->selectRaw('SUM(ABS(sm.quantity_change)) as items')
            ->first();
    }

    protected function computeAverageMovement($kpi): float
    {
        return ($kpi && ($kpi->movements ?? 0) > 0)
            ? round($kpi->revenue / $kpi->movements, 2)
            : 0.0;
    }

    protected function buildSeries(string $bucket)
    {
        return $this->baseQuery()
            ->selectRaw("$bucket as bucket")
            ->selectRaw('SUM(ABS(sm.quantity_change) * b.price) as revenue')
            ->selectRaw('SUM(ABS(sm.quantity_change)) as items')
            ->selectRaw('COUNT(DISTINCT sm.id) as movements')
            ->groupByRaw($bucket)
            ->orderByRaw($bucket)
            ->get();
    }

    protected function buildTopBooks()
    {
        return $this->baseQuery()
            ->select('b.id','b.title')
            ->selectRaw('SUM(ABS(sm.quantity_change)) as qty')
            ->selectRaw('SUM(ABS(sm.quantity_change) * b.price) as revenue')
            ->groupBy('b.id','b.title')
            ->orderByDesc('revenue')
            ->limit($this->top)
            ->get();
    }

    protected function buildByCategory()
    {
        return $this->baseQuery()
            ->leftJoin('book_category as bc', 'bc.book_id', '=', 'b.id')
            ->leftJoin('categories as c', 'c.id', '=', 'bc.category_id')
            ->selectRaw('COALESCE(c.id, 0) as category_id')
            ->selectRaw("COALESCE(c.name, 'Uncategorized') as category_name")
            ->selectRaw('SUM(ABS(sm.quantity_change)) as qty')
            ->selectRaw('SUM(ABS(sm.quantity_change) * b.price) as revenue')
            ->groupBy('c.id','c.name')
            ->orderByDesc('revenue')
            ->limit(50)
            ->get();
    }
}

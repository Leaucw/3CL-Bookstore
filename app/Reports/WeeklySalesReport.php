<?php
//Author: Leau Chee Way
namespace App\Reports;

class WeeklySalesReport extends AbstractSalesReport
{
    protected function bucketExpr(): string
    {
        return "YEARWEEK(sm.created_at, 1)";
    }
}

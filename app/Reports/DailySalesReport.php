<?php
//Author: Leau Chee Way
namespace App\Reports;

class DailySalesReport extends AbstractSalesReport
{
    protected function bucketExpr(): string
    {
        return "DATE(sm.created_at)";
    }
}

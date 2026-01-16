<?php
//Author: Leau Chee Way
namespace App\Reports;

class MonthlySalesReport extends AbstractSalesReport
{
    protected function bucketExpr(): string
    {
        return "DATE_FORMAT(sm.created_at, '%Y-%m')";
    }
}

<?php

// Simple test script to verify the getSalesByPaymentMethod method
require_once __DIR__ . '/../../system/bootstrap.php';

use App\Controllers\Reports;

$reports = new Reports();

// Test the method with some sample dates
$startDate = '2026-01-01';
$endDate = '2026-12-31';

try {
    $result = $reports->getSalesByPaymentMethod($startDate, $endDate);
    echo "Method executed successfully!\n";
    echo "Result: " . json_encode($result) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
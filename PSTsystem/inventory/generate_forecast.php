<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

require_once __DIR__ . '/classes/SalesForecasting.php';

try {
    $forecasting = new SalesForecasting($mysqli);
} catch (Exception $e) {
    $_SESSION['error'] = 'Failed to initialize forecasting: ' . $e->getMessage();
    header('Location: forecast_reports.php');
    exit;
}

$type = isset($_GET['type']) ? $_GET['type'] : 'summary';
$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'csv';

// Default days
$days = isset($_GET['days']) ? (int)$_GET['days'] : 14;
$days = max(7, min(90, $days));

if ($type === 'summary') {
    // Build simple summary counts
    $data = $forecasting->getAllProductForecasts(200);
    $counts = ['critical'=>0,'high'=>0,'medium'=>0,'low'=>0,'normal'=>0];
    foreach ($data as $d) {
        $u = $d['urgency'];
        if (isset($counts[$u])) $counts[$u]++;
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=forecast_summary_'.date('Ymd_His').'.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Urgency','Count']);
    foreach ($counts as $k=>$v) { fputcsv($out, [$k, $v]); }
    fclose($out);
    exit;
}

if ($type === 'critical') {
    $data = $forecasting->getAllProductForecasts(200);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=forecast_critical_'.date('Ymd_His').'.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Product','Current Stock','Avg Daily Demand','Days Until Stockout','Urgency']);
    foreach ($data as $d) {
        if ($d['urgency'] === 'critical' || $d['urgency'] === 'high') {
            fputcsv($out, [$d['product_name'], $d['current_stock'], $d['avg_daily_demand'], $d['days_until_stockout'], $d['urgency']]);
        }
    }
    fclose($out);
    exit;
}

// Detailed forecast per product
if ($type === 'detailed') {
    $data = $forecasting->getAllProductForecasts(500);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=forecast_detailed_'.date('Ymd_His').'.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Product','Current Stock','Threshold','Avg Daily Demand','Days Until Stockout','Optimal Reorder Point','EOQ','Recommended Order Qty','Urgency','Confidence']);
    foreach ($data as $d) {
        fputcsv($out, [
            $d['product_name'], $d['current_stock'], $d['threshold'], $d['avg_daily_demand'], $d['days_until_stockout'],
            $d['optimal_reorder_point'], $d['economic_order_quantity'], $d['recommended_order_quantity'], $d['urgency'], $d['confidence']
        ]);
    }
    fclose($out);
    exit;
}

// Per-product forecast CSV
if ($type === 'product_forecast' && isset($_GET['product_id'])) {
    $pid = $_GET['product_id'];
    $forecast = $forecasting->predictFutureDemand($pid, $days);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=forecast_'.preg_replace('/[^a-zA-Z0-9_-]/','',$pid).'_'.date('Ymd_His').'.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Date','Predicted Demand','Confidence','Trend','Seasonal','External']);
    foreach ($forecast as $f) {
        fputcsv($out, [
            $f['date'], $f['predicted_demand'], $f['confidence'],
            $f['factors']['trend'] ?? 0, $f['factors']['seasonal'] ?? 1, $f['factors']['external'] ?? 1
        ]);
    }
    fclose($out);
    exit;
}

// Default: redirect with success
$_SESSION['success'] = 'Forecast generated successfully';
header('Location: forecast_reports.php');
exit;
?>



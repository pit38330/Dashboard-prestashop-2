<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Récupérer les paramètres du filtre
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;
$country_id = isset($_GET['country']) && !empty($_GET['country']) ? $_GET['country'] : null;
$order_status = isset($_GET['order_status']) && !empty($_GET['order_status']) ? $_GET['order_status'] : null;

// Récupérer les données filtrées
$salesData = getSalesData($conn, $start_date, $end_date, $country_id, $order_status);

// Renvoyer les données au format JSON
header('Content-Type: application/json');
echo json_encode($salesData);
?>

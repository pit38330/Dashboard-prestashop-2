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

// Configurer l'en-tête pour le téléchargement
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=exports_ventes_' . date('Y-m-d') . '.csv');

// Créer le fichier CSV
$output = fopen('php://output', 'w');

// Définir l'encodage UTF-8 avec BOM pour Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// En-têtes du CSV
fputcsv($output, [
    'Pays',
    'CA HT (Produits)',
    'CA HT (Livraison)',
    'CA HT Total',
    'Remboursements HT',
    'CA HT Final',
    'Nombre de commandes',
    'Références de commandes'
], ';');

// Lignes de données
foreach ($salesData as $row) {
    fputcsv($output, [
        $row['country_name'],
        number_format($row['total_products_ht'], 2, ',', ''),
        number_format($row['total_shipping_ht'], 2, ',', ''),
        number_format($row['total_revenue_with_shipping_ht'], 2, ',', ''),
        number_format($row['total_refunds_ht'], 2, ',', ''),
        number_format($row['total_revenue_ht_after_refunds'], 2, ',', ''),
        $row['total_orders'],
        $row['order_references']
    ], ';');
}

fclose($output);
exit;
?>

<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Récupérer les paramètres
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
$category_id = isset($_GET['category']) ? $_GET['category'] : null;

// Récupérer les données des ventes par produit
$productSalesData = getSalesDataByProduct($conn, $start_date, $end_date, $category_id);

// Renvoyer les données au format JSON
header('Content-Type: application/json');
echo json_encode($productSalesData);

function getSalesDataByProduct($conn, $start_date = null, $end_date = null, $category_id = null) {
    $sql = "SELECT
                p.id_product,
                pl.name AS product_name,
                p.reference,
                SUM(od.product_quantity) AS total_quantity,
                SUM(od.total_price_tax_excl) AS total_revenue_ht
            FROM ps_order_detail od
            JOIN ps_product p ON od.product_id = p.id_product
            JOIN ps_product_lang pl ON p.id_product = pl.id_product
            WHERE pl.id_lang = 1";

    if ($start_date && $end_date) {
        $sql .= " AND od.date_add BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
    }

    if ($category_id) {
        $sql .= " AND p.id_category_default = $category_id";
    }

    $sql .= " GROUP BY p.id_product, pl.name ORDER BY total_revenue_ht DESC";

    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

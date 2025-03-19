<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si PHPSpreadsheet est installé
if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    die('Veuillez installer la bibliothèque PHPSpreadsheet: composer require phpoffice/phpspreadsheet');
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Récupérer les paramètres du filtre
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;
$country_id = isset($_GET['country']) && !empty($_GET['country']) ? $_GET['country'] : null;
$order_status = isset($_GET['order_status']) && !empty($_GET['order_status']) ? $_GET['order_status'] : null;

// Récupérer les données filtrées
$salesData = getSalesData($conn, $start_date, $end_date, $country_id, $order_status);

// Créer un nouveau document Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Ventes par pays');

// Définir les en-têtes
$sheet->setCellValue('A1', 'Pays');
$sheet->setCellValue('B1', 'CA HT (Produits)');
$sheet->setCellValue('C1', 'CA HT (Livraison)');
$sheet->setCellValue('D1', 'CA HT Total');
$sheet->setCellValue('E1', 'Remboursements HT');
$sheet->setCellValue('F1', 'CA HT Final');
$sheet->setCellValue('G1', 'Nombre de commandes');
$sheet->setCellValue('H1', 'Références de commandes');

// Mettre en forme les en-têtes
$sheet->getStyle('A1:H1')->getFont()->setBold(true);

// Ajouter les données
$row = 2;
foreach ($salesData as $data) {
    $sheet->setCellValue('A' . $row, $data['country_name']);
    $sheet->setCellValue('B' . $row, $data['total_products_ht']);
    $sheet->setCellValue('C' . $row, $data['total_shipping_ht']);
    $sheet->setCellValue('D' . $row, $data['total_revenue_with_shipping_ht']);
    $sheet->setCellValue('E' . $row, $data['total_refunds_ht']);
    $sheet->setCellValue('F' . $row, $data['total_revenue_ht_after_refunds']);
    $sheet->setCellValue('G' . $row, $data['total_orders']);
    $sheet->setCellValue('H' . $row, $data['order_references']);
    
    // Formatage des cellules monétaires
    $sheet->getStyle('B' . $row . ':F' . $row)->getNumberFormat()->setFormatCode('#,##0.00 €');
    
    $row++;
}

// Ajuster la largeur des colonnes
foreach(range('A', 'H') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Créer le writer pour Excel 2007+
$writer = new Xlsx($spreadsheet);

// Définir les en-têtes HTTP pour le téléchargement
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="exports_ventes_' . date('Y-m-d') . '.xlsx"');
header('Cache-Control: max-age=0');

// Envoyer le fichier à la sortie
$writer->save('php://output');
exit;
?>

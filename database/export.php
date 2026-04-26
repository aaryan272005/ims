<?php

require_once __DIR__ . '/../partials/security.php';
require_roles(['admin', 'user'], false, '../dashboard.php', 'Sales access is limited to dashboard, products, and POS.');

include('connection.php');
require_once __DIR__ . '/SimpleXLSXGen.php';
require_once __DIR__ . '/../fpdf186/fpdf.php';

use Shuchkin\SimpleXLSXGen;

$type = $_GET['type'] ?? '';
$format = $_GET['format'] ?? '';

switch ($type) {
    case 'products':
        $query = "
            SELECT
                p.product_name AS Product,
                p.description AS Description,
                GROUP_CONCAT(DISTINCT s.supplier_name ORDER BY s.supplier_name SEPARATOR ', ') AS Supplier,
                COALESCE(st.quantity, 0) AS Stock,
                CASE
                    WHEN COALESCE(st.quantity, 0) = 0 THEN 'Out of Stock'
                    WHEN COALESCE(st.quantity, 0) < 20 THEN 'Low Stock'
                    ELSE 'In Stock'
                END AS Status,
                CONCAT(u.first_name, ' ', u.last_name) AS Created_By,
                DATE_FORMAT(p.created_at, '%d-%m-%Y %H:%i') AS Created_At,
                DATE_FORMAT(p.updated_at, '%d-%m-%Y %H:%i') AS Updated_At
            FROM products p
            LEFT JOIN users u ON u.id = p.created_by
            LEFT JOIN stock st ON st.product_id = p.id
            LEFT JOIN product_supplier_map psm ON psm.product_id = p.id
            LEFT JOIN supplier s ON s.id = psm.supplier_id
            GROUP BY p.id, p.product_name, p.description, st.quantity, u.first_name, u.last_name, p.created_at, p.updated_at
            ORDER BY p.created_at DESC
        ";
        break;
    case 'suppliers':
        $query = "
            SELECT
                s.supplier_name AS `Supplier Name`,
                s.supplier_location AS `Supplier Location`,
                s.email AS `Contact Details`,
                GROUP_CONCAT(DISTINCT p.product_name ORDER BY p.product_name SEPARATOR ', ') AS Products,
                CONCAT(u.first_name, ' ', u.last_name) AS `Created By`,
                DATE_FORMAT(s.created_at, '%d-%m-%Y %H:%i') AS `Created At`,
                DATE_FORMAT(s.updated_at, '%d-%m-%Y %H:%i') AS `Updated At`
            FROM supplier s
            LEFT JOIN users u ON u.id = s.created_by
            LEFT JOIN product_supplier_map psm ON psm.supplier_id = s.id
            LEFT JOIN products p ON p.id = psm.product_id
            GROUP BY s.id, s.supplier_name, s.supplier_location, s.email, u.first_name, u.last_name, s.created_at, s.updated_at
            ORDER BY s.created_at DESC
        ";
        break;
    case 'orders':
        $query = "
            SELECT
                p.product_name AS Product,
                s.supplier_name AS Supplier,
                po.quantity_order AS Qty_Ordered,
                po.quantity_received AS Qty_Received,
                (po.quantity_order - po.quantity_received) AS Qty_Remaining,
                UPPER(
                    CASE
                        WHEN po.quantity_received <= 0 THEN 'pending'
                        WHEN po.quantity_received < po.quantity_order THEN 'incomplete'
                        ELSE 'complete'
                    END
                ) AS Status,
                CONCAT(u.first_name, ' ', u.last_name) AS Ordered_By,
                DATE_FORMAT(po.created_at, '%d-%m-%Y %H:%i') AS Created_At,
                DATE_FORMAT(po.updated_at, '%d-%m-%Y %H:%i') AS Updated_At
            FROM purchase_orders po
            LEFT JOIN products p ON p.id = po.product_id
            LEFT JOIN supplier s ON s.id = po.supplier_id
            LEFT JOIN users u ON u.id = po.created_by
            ORDER BY po.created_at DESC
        ";
        break;
    case 'deliveries':
        $query = "
            SELECT
                order_id AS Order_ID,
                quantity_received AS Quantity,
                DATE_FORMAT(date_received, '%d-%m-%Y %H:%i') AS Date_Received
            FROM delivery_history
            ORDER BY date_received DESC
        ";
        break;
    default:
        die('Invalid Report');
}

$stmt = $conn->prepare($query);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($format === 'excel') {
    $data = [
        ['VyaparTrack Inventory Report'],
        ['Report Type: ' . ucfirst($type)],
        ['Generated On: ' . date('d-m-Y H:i')],
        [],
    ];

    if (!empty($rows)) {
        $data[] = array_keys($rows[0]);
        foreach ($rows as $row) {
            $data[] = array_values($row);
        }
    }

    SimpleXLSXGen::fromArray($data)->downloadAs($type . '_report.xlsx');
    exit;
}

if ($format === 'pdf') {
    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'VyaparTrack Inventory Report', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 8, ucfirst($type) . ' Report', 0, 1, 'C');
    $pdf->Ln(5);

    if (!empty($rows)) {
        $headers = array_keys($rows[0]);
        $width = max(20, (int) floor(270 / max(1, count($headers))));

        $pdf->SetFont('Arial', 'B', 9);
        foreach ($headers as $header) {
            $pdf->Cell($width, 10, substr((string) $header, 0, 24), 1, 0, 'C');
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 8);
        foreach ($rows as $row) {
            foreach ($row as $value) {
                $pdf->Cell($width, 9, substr((string) $value, 0, 24), 1, 0, 'C');
            }
            $pdf->Ln();
        }
    }

    $pdf->Output($type . '_report.pdf', 'D');
    exit;
}

die('Invalid export format');

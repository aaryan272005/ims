<?php

require_once __DIR__ . '/../partials/security.php';

require_roles(['admin', 'sales'], false, '../dashboard.php', 'You only have access to the sales workflow.');

$token = trim((string) ($_GET['token'] ?? ''));
$downloads = $_SESSION['pos_invoice_downloads'] ?? [];
$entry = is_array($downloads) ? ($downloads[$token] ?? null) : null;

if ($token === '' || !is_array($entry)) {
    http_response_code(404);
    exit('Invoice not found');
}

$filePath = (string) ($entry['path'] ?? '');
$fileName = basename((string) ($entry['name'] ?? 'invoice.pdf'));

if ($filePath === '' || !is_file($filePath)) {
    unset($_SESSION['pos_invoice_downloads'][$token]);
    http_response_code(404);
    exit('Invoice file not found');
}

$pdfBytes = file_get_contents($filePath);
@unlink($filePath);
unset($_SESSION['pos_invoice_downloads'][$token]);

if ($pdfBytes === false) {
    http_response_code(500);
    exit('Unable to read invoice');
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($pdfBytes));
echo $pdfBytes;
exit();

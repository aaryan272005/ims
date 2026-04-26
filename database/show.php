<?php

require_once __DIR__ . '/../partials/security.php';
require_once __DIR__ . '/connection.php';

require_login('../login.php');

$table_name = $_SESSION['table'] ?? '';

if ($table_name === 'products') {
    $stmt = $conn->prepare(
        "SELECT p.*
         FROM products p
         ORDER BY p.created_at DESC, p.id DESC"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($table_name === 'supplier') {
    $stmt = $conn->prepare(
        "SELECT s.*
         FROM supplier s
         ORDER BY s.created_at DESC, s.id DESC"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($table_name === 'users') {
    $stmt = $conn->prepare(
        "SELECT u.*
         FROM users u
         ORDER BY u.created_at DESC, u.id DESC"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

throw new RuntimeException('Invalid table requested');

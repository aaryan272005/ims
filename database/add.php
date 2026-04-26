<?php

require_once __DIR__ . '/../partials/security.php';
require_once __DIR__ . '/../partials/validation.php';
require_once __DIR__ . '/connection.php';

require_admin(false, '../dashboard.php');

$redirect = $_SESSION['redirect_to'] ?? 'dashboard.php';
require_post_csrf(false, "../{$redirect}");

$table_name = $_SESSION['table'] ?? '';
$user_id = (int) ($_SESSION['user_id'] ?? 0);
$allowed_tables = ['products', 'supplier', 'users'];

if (!in_array($table_name, $allowed_tables, true)) {
    $_SESSION['response'] = [
        'success' => false,
        'message' => 'Invalid request',
    ];
    header("Location: ../{$redirect}");
    exit();
}

try {
    if ($table_name === 'products') {
        $payload = validate_product_payload(
            (string) ($_POST['product_name'] ?? ''),
            (string) ($_POST['description'] ?? ''),
            $_POST['price'] ?? null,
            $_POST['suppliers'] ?? []
        );

        $image_name = '';
        if (isset($_FILES['img']) && (int) ($_FILES['img']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $img = $_FILES['img'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array((string) ($img['type'] ?? ''), $allowed_types, true)) {
                throw new InvalidArgumentException('Invalid image type');
            }

            $image_name = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename((string) $img['name']));
            $upload_dir = __DIR__ . '/../uploads/products/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (!move_uploaded_file((string) $img['tmp_name'], $upload_dir . $image_name)) {
                throw new RuntimeException('Unable to upload product image');
            }
        }

        $conn->beginTransaction();

        $stmt = $conn->prepare(
            'INSERT INTO products (product_name, description, price, img, created_by, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $payload['product_name'],
            $payload['description'],
            $payload['price'],
            $image_name,
            $user_id,
        ]);

        $product_id = (int) $conn->lastInsertId();
        $mapStmt = $conn->prepare(
            'INSERT INTO product_supplier_map (product_id, supplier_id, created_at, updated_at)
             VALUES (?, ?, NOW(), NOW())'
        );

        foreach ($payload['suppliers'] as $supplierId) {
            $mapStmt->execute([$product_id, $supplierId]);
        }

        $conn->commit();

        $_SESSION['response'] = [
            'success' => true,
            'message' => 'Product created successfully',
        ];
    } elseif ($table_name === 'supplier') {
        $payload = validate_supplier_payload(
            (string) ($_POST['supplier_name'] ?? ''),
            (string) ($_POST['supplier_location'] ?? ''),
            (string) ($_POST['email'] ?? '')
        );

        $stmt = $conn->prepare(
            'INSERT INTO supplier (supplier_name, supplier_location, email, created_by, created_at, updated_at)
             VALUES (?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $payload['supplier_name'],
            $payload['supplier_location'],
            $payload['email'],
            $user_id,
        ]);

        $_SESSION['response'] = [
            'success' => true,
            'message' => 'Supplier created successfully',
        ];
    } elseif ($table_name === 'users') {
        $payload = validate_user_payload(
            (string) ($_POST['first_name'] ?? ''),
            (string) ($_POST['last_name'] ?? ''),
            (string) ($_POST['email'] ?? ''),
            (string) ($_POST['password'] ?? '')
        );
        $role = validate_user_role((string) ($_POST['role'] ?? 'user'));

        $stmt = $conn->prepare(
            'INSERT INTO users (first_name, last_name, email, password, role, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $payload['first_name'],
            $payload['last_name'],
            $payload['email'],
            password_hash($payload['password'], PASSWORD_DEFAULT),
            $role,
        ]);

        $_SESSION['response'] = [
            'success' => true,
            'message' => 'User successfully added.',
        ];
    }
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    $_SESSION['response'] = [
        'success' => false,
        'message' => $e instanceof InvalidArgumentException ? $e->getMessage() : 'Unable to save your changes right now',
    ];
}

header("Location: ../{$redirect}");
exit();

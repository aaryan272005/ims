<?php

require_once __DIR__ . '/../partials/security.php';
require_once __DIR__ . '/../partials/validation.php';
require_once __DIR__ . '/connection.php';

require_admin(true);
header('Content-Type: application/json');

require_post_csrf(true);

$table = (string) ($_POST['table'] ?? '');

try {
    if ($table === 'products') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            throw new InvalidArgumentException('Invalid product data');
        }

        $payload = validate_product_text_payload(
            (string) ($_POST['product_name'] ?? ''),
            (string) ($_POST['description'] ?? '')
        );

        $image_name = null;
        if (isset($_FILES['image']) && (int) ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $img = $_FILES['image'];
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

            $stmt = $conn->prepare(
                'UPDATE products SET product_name = ?, description = ?, img = ?, updated_at = NOW() WHERE id = ?'
            );
            $stmt->execute([$payload['product_name'], $payload['description'], $image_name, $id]);
        } else {
            $stmt = $conn->prepare(
                'UPDATE products SET product_name = ?, description = ?, updated_at = NOW() WHERE id = ?'
            );
            $stmt->execute([$payload['product_name'], $payload['description'], $id]);
        }

        echo json_encode([
            'success' => true,
            'product_name' => $payload['product_name'],
            'description' => $payload['description'],
            'img' => $image_name,
        ]);
        exit();
    }

    if ($table === 'supplier') {
        $id = (int) ($_POST['supplier_id'] ?? 0);
        if ($id <= 0) {
            throw new InvalidArgumentException('Invalid supplier data');
        }

        $payload = validate_supplier_payload(
            (string) ($_POST['supplier_name'] ?? ''),
            (string) ($_POST['supplier_location'] ?? ''),
            (string) ($_POST['email'] ?? '')
        );

        $stmt = $conn->prepare(
            'UPDATE supplier SET supplier_name = ?, supplier_location = ?, email = ?, updated_at = NOW() WHERE id = ?'
        );
        $stmt->execute([
            $payload['supplier_name'],
            $payload['supplier_location'],
            $payload['email'],
            $id,
        ]);

        echo json_encode(['success' => true]);
        exit();
    }

    if ($table === 'users') {
        $id = (int) ($_POST['user_id'] ?? 0);
        if ($id <= 0) {
            throw new InvalidArgumentException('Invalid user data');
        }

        $payload = validate_user_profile_payload(
            (string) ($_POST['first_name'] ?? ''),
            (string) ($_POST['last_name'] ?? ''),
            (string) ($_POST['email'] ?? '')
        );

        $stmt = $conn->prepare(
            'UPDATE users SET first_name = ?, last_name = ?, email = ?, updated_at = NOW() WHERE id = ?'
        );
        $stmt->execute([
            $payload['first_name'],
            $payload['last_name'],
            $payload['email'],
            $id,
        ]);

        echo json_encode(['success' => true]);
        exit();
    }

    if ($table === 'purchase_orders') {
        $order_id = (int) ($_POST['order_id'] ?? 0);
        $quantity_delivered = validate_delivery_quantity((int) ($_POST['quantity_delivered'] ?? 0));
        if ($order_id <= 0) {
            throw new InvalidArgumentException('Invalid order');
        }

        $conn->beginTransaction();

        $stmt = $conn->prepare('SELECT product_id, quantity_order, quantity_received FROM purchase_orders WHERE id = ?');
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new InvalidArgumentException('Order not found');
        }

        $new_received = (int) $order['quantity_received'] + $quantity_delivered;
        if ($new_received > (int) $order['quantity_order']) {
            throw new InvalidArgumentException('Delivered quantity exceeds ordered quantity');
        }

        $status = compute_order_status((int) $order['quantity_order'], $new_received);
        $remaining = max(0, (int) $order['quantity_order'] - $new_received);
        $stmt = $conn->prepare(
            'UPDATE purchase_orders SET quantity_received = ?, quantity_remaining = ?, status = ?, updated_at = NOW() WHERE id = ?'
        );
        $stmt->execute([$new_received, $remaining, $status, $order_id]);

        if ($quantity_delivered > 0) {
            $historyStmt = $conn->prepare(
                'INSERT INTO delivery_history (order_id, quantity_received, date_received) VALUES (?, ?, NOW())'
            );
            $historyStmt->execute([$order_id, $quantity_delivered]);

            $stockStmt = $conn->prepare(
                'INSERT INTO stock (product_id, quantity) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)'
            );
            $stockStmt->execute([(int) $order['product_id'], $quantity_delivered]);
        }

        $conn->commit();
        echo json_encode(['success' => true, 'status' => $status]);
        exit();
    }

    throw new InvalidArgumentException('Invalid update request');
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => $e instanceof InvalidArgumentException ? $e->getMessage() : 'Unable to update right now',
    ]);
    exit();
}

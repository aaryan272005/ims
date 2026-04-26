<?php

function app_text_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}

function normalize_digits(string $value): string
{
    return preg_replace('/\D+/', '', $value) ?? '';
}

function validate_phone_10(string $phone): string
{
    $digits = normalize_digits($phone);
    if (!preg_match('/^[6-9]\d{9}$/', $digits)) {
        throw new InvalidArgumentException('Enter a valid 10-digit mobile number');
    }

    return $digits;
}

function validate_product_payload(string $productName, string $description, $price, array $suppliers): array
{
    $productName = trim($productName);
    $description = trim($description);
    $priceValue = filter_var($price, FILTER_VALIDATE_FLOAT);
    $supplierIds = [];

    foreach ($suppliers as $supplierId) {
        $id = (int) $supplierId;
        if ($id > 0) {
            $supplierIds[] = $id;
        }
    }

    $supplierIds = array_values(array_unique($supplierIds));

    if ($productName === '' || $description === '' || $priceValue === false || $priceValue <= 0 || empty($supplierIds)) {
        throw new InvalidArgumentException('Enter a valid name, description, price, and supplier');
    }

    if (app_text_length($productName) > 100) {
        throw new InvalidArgumentException('Product name cannot be more than 100 characters');
    }

    if (app_text_length($description) > 200) {
        throw new InvalidArgumentException('Description cannot be more than 200 characters');
    }

    return [
        'product_name' => $productName,
        'description' => $description,
        'price' => (float) $priceValue,
        'suppliers' => $supplierIds,
    ];
}

function validate_product_text_payload(string $productName, string $description): array
{
    $productName = trim($productName);
    $description = trim($description);

    if ($productName === '' || $description === '') {
        throw new InvalidArgumentException('Invalid product data');
    }

    if (app_text_length($productName) > 100) {
        throw new InvalidArgumentException('Product name cannot be more than 100 characters');
    }

    if (app_text_length($description) > 200) {
        throw new InvalidArgumentException('Description cannot be more than 200 characters');
    }

    return [
        'product_name' => $productName,
        'description' => $description,
    ];
}

function validate_supplier_payload(string $name, string $location, string $email): array
{
    $name = trim($name);
    $location = trim($location);
    $email = trim($email);

    if ($name === '' || $location === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Enter a valid supplier name, location, and email');
    }

    if (app_text_length($name) > 100) {
        throw new InvalidArgumentException('Supplier name cannot be more than 100 characters');
    }

    if (app_text_length($location) > 100) {
        throw new InvalidArgumentException('Supplier location cannot be more than 100 characters');
    }

    if (app_text_length($email) > 100) {
        throw new InvalidArgumentException('Supplier email cannot be more than 100 characters');
    }

    return [
        'supplier_name' => $name,
        'supplier_location' => $location,
        'email' => $email,
    ];
}

function validate_user_payload(string $firstName, string $lastName, string $email, string $password): array
{
    $firstName = trim($firstName);
    $lastName = trim($lastName);
    $email = trim($email);

    if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        throw new InvalidArgumentException('Enter valid user details and use a password with at least 8 characters');
    }

    if (app_text_length($firstName) > 50 || app_text_length($lastName) > 50) {
        throw new InvalidArgumentException('First and last name cannot be more than 50 characters');
    }

    if (app_text_length($email) > 100) {
        throw new InvalidArgumentException('Email cannot be more than 100 characters');
    }

    return [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'password' => $password,
    ];
}

function validate_user_profile_payload(string $firstName, string $lastName, string $email): array
{
    $firstName = trim($firstName);
    $lastName = trim($lastName);
    $email = trim($email);

    if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Invalid user data');
    }

    if (app_text_length($firstName) > 50 || app_text_length($lastName) > 50) {
        throw new InvalidArgumentException('First and last name cannot be more than 50 characters');
    }

    if (app_text_length($email) > 100) {
        throw new InvalidArgumentException('Email cannot be more than 100 characters');
    }

    return [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
    ];
}

function validate_user_role(string $role): string
{
    $normalizedRole = strtolower(trim($role));
    $allowedRoles = ['admin', 'user', 'sales'];

    if (!in_array($normalizedRole, $allowedRoles, true)) {
        throw new InvalidArgumentException('Select a valid user access level');
    }

    return $normalizedRole;
}

function validate_order_rows_payload($productIds, $supplierIds, $quantities): array
{
    if (!is_array($productIds) || !is_array($supplierIds) || !is_array($quantities)) {
        throw new InvalidArgumentException('Invalid order request');
    }

    if (count($productIds) !== count($supplierIds) || count($supplierIds) !== count($quantities)) {
        throw new InvalidArgumentException('Invalid order request');
    }

    $rows = [];

    foreach ($supplierIds as $index => $supplierId) {
        $product = (int) ($productIds[$index] ?? 0);
        $supplier = (int) $supplierId;
        $quantityRaw = $quantities[$index] ?? '';
        $quantity = (int) $quantityRaw;

        $isRowEmpty = $product <= 0 && $supplier <= 0 && trim((string) $quantityRaw) === '';
        if ($isRowEmpty) {
            continue;
        }

        if ($product <= 0 || $supplier <= 0 || $quantity <= 0) {
            throw new InvalidArgumentException('Please enter valid product, supplier and quantity for each order row');
        }

        $rows[] = [
            'product_id' => $product,
            'supplier_id' => $supplier,
            'quantity' => $quantity,
        ];
    }

    if (empty($rows)) {
        throw new InvalidArgumentException('Add at least one valid product quantity');
    }

    return $rows;
}

function compute_order_status(int $orderedQuantity, int $receivedQuantity): string
{
    if ($receivedQuantity <= 0) {
        return 'pending';
    }

    if ($receivedQuantity < $orderedQuantity) {
        return 'incomplete';
    }

    return 'complete';
}

function validate_delivery_quantity(int $quantityDelivered): int
{
    if ($quantityDelivered < 0) {
        throw new InvalidArgumentException('Please enter a valid delivered quantity.');
    }

    return $quantityDelivered;
}

function validate_pos_customer_payload(array $customer): array
{
    $name = trim((string) ($customer['name'] ?? ''));
    $phone = validate_phone_10((string) ($customer['phone'] ?? ''));
    $gst = trim((string) ($customer['gst'] ?? ''));

    if ($name === '') {
        throw new InvalidArgumentException('Plz enter details to generate bill');
    }

    if (app_text_length($name) > 100) {
        throw new InvalidArgumentException('Customer name cannot be more than 100 characters');
    }

    if ($gst !== '' && app_text_length($gst) > 30) {
        throw new InvalidArgumentException('GST value cannot be more than 30 characters');
    }

    return [
        'name' => $name,
        'phone' => $phone,
        'gst' => $gst === '' ? '-' : $gst,
    ];
}

function normalize_pos_cart_payload($cart): array
{
    if (!is_array($cart) || empty($cart)) {
        throw new InvalidArgumentException('Cart is empty');
    }

    $normalized = [];

    foreach ($cart as $productId => $item) {
        $id = (int) $productId;
        $quantity = is_array($item) ? (int) ($item['qty'] ?? 0) : 0;

        if ($id <= 0 || $quantity <= 0) {
            throw new InvalidArgumentException('Invalid cart item');
        }

        if (!isset($normalized[$id])) {
            $normalized[$id] = ['qty' => 0];
        }

        $normalized[$id]['qty'] += $quantity;
    }

    if (empty($normalized)) {
        throw new InvalidArgumentException('Cart is empty');
    }

    return $normalized;
}

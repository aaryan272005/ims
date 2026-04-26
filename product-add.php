<?php

require_once __DIR__ . '/partials/security.php';

require_login('login.php');

if (!has_role('admin')) {
    $_SESSION['response'] = [
        'success' => false,
        'message' => 'Product creation is admin only.',
    ];
    header('Location: product-view.php');
    exit();
}

$_SESSION['table'] = 'products';
$_SESSION['redirect_to'] = 'product-add.php';
$temp = $_SESSION['table'];
$_SESSION['table'] = 'supplier';
$suppliers = include('database/show.php');
$_SESSION['table'] = $temp;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Products ~ VyaparTrack</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div id="DashboardMainContainer">
        <?php include('partials/app-sidebar.php'); ?>
        <div class="DashboardContent_container">
            <?php include('partials/app-topNav.php'); ?>
            <div class="dashboardContent">
                <div class="dashboard_content_main">
                    <h1 class="section_header"><i class="fa fa-plus"></i> Create Product</h1>
                    <div id="userAddFormContainer">
                        <form action="database/add.php" method="POST" class="userForm" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <label>Product Name:</label>
                            <input type="text" placeholder="Enter Product Name..." name="product_name" maxlength="100" required>

                            <label>Description:</label>
                            <textarea class="productTextArea" placeholder="Enter Product Description..." name="description" maxlength="200" required></textarea>

                            <label>Price (Rs):</label>
                            <input type="number" step="0.01" placeholder="Enter Product Price..." name="price" required>

                            <label>Suppliers:</label>
                            <select name="suppliers[]" id="supplierInput" multiple required>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= (int) $supplier['id'] ?>"><?= htmlspecialchars($supplier['supplier_name'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>

                            <div class="imageUploadWrapper">
                                <label for="img" class="uploadBtn"><i class="fa fa-upload"></i> Upload Product Image</label>
                                <input type="file" name="img" id="img" hidden required>
                                <span id="fileName">No file selected</span>
                            </div>

                            <button type="submit" class="userFormBtn">
                                <i class="fa fa-plus"></i> Create Product
                            </button>
                        </form>

                        <?php include('partials/flash-response.php'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/dashboard.js"></script>
    <script src="js/tooltips.js?v=<?= filemtime(__DIR__ . '/js/tooltips.js') ?>"></script>
    <script>
        const fileInput = document.getElementById("img");
        if (fileInput) {
            fileInput.addEventListener("change", function () {
                document.getElementById("fileName").innerText = this.files[0]?.name || "No file selected";
            });
        }
    </script>
</body>
</html>

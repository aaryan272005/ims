<?php
session_start();
include('database/connection.php');

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$query = "SELECT p.*, s.quantity 
          FROM products p
          LEFT JOIN stock s ON p.id = s.product_id";

$stmt = $conn->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8">
    <title>POS</title>

    ```
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .posWrapper {
            display: flex;
            gap: 20px;
        }

        .productGrid {
            flex: 2;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .productCard {
            background: #fff;
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: 0.2s;
        }

        .productCard:hover {
            transform: translateY(-5px);
        }

        .productCard:active {
            transform: scale(0.95);
        }

        .productCard.out {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .productCard img {
            width: 100%;
            height: 140px;
            object-fit: contain;
        }

        .productName {
            font-weight: 600;
        }

        .productPrice {
            color: #3498db;
            font-weight: bold;
        }

        .cartBox {
            flex: 1;
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .cartItem {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .qtyBtn {
            cursor: pointer;
            padding: 4px 8px;
            border: none;
            background: #eee;
            border-radius: 4px;
        }

        .removeBtn {
            color: red;
            cursor: pointer;
        }

        .checkoutBtn {
            width: 100%;
            background: #2ecc71;
            color: #fff;
            padding: 12px;
            border: none;
            margin-top: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .checkoutBtn:hover {
            background: #27ae60;
        }
    </style>
    ```

</head>

<body>

    <div id="DashboardMainContainer">

        <?php include('partials/app-sidebar.php'); ?>

        <div class="DashboardContent_container">

            <?php include('partials/app-topNav.php'); ?>

            <div class="dashboardContent">

                <h1><i class="fa-solid fa-receipt"></i> POS Billing</h1>

                <div class="posWrapper">

                    <!-- PRODUCTS -->

                    <div class="productGrid">

                        <?php foreach ($products as $p):
                            $stock = $p['quantity'] ?? 0;
                        ?>

                            <div class="productCard <?= $stock <= 0 ? 'out' : '' ?>"
                                data-id="<?= $p['id'] ?>"
                                data-stock="<?= $stock ?>"
                                onclick="addToCart(event,<?= $p['id'] ?>,'<?= $p['product_name'] ?>',<?= $p['price'] ?>)">

                                <img src="<?= !empty($p['img']) ? 'uploads/products/' . $p['img'] : 'images/default.png' ?>">

                                <div class="productName"><?= $p['product_name'] ?></div>
                                <div class="productPrice">₹<?= $p['price'] ?></div>

                                <div class="stockText" style="margin-top:5px;font-size:13px;
color: <?= $stock == 0 ? 'red' : ($stock < 10 ? 'orange' : 'green') ?>;
font-weight:600;">
                                    <?php
                                    if ($stock == 0) echo "Out of Stock";
                                    elseif ($stock < 10) echo "Low Stock ($stock)";
                                    else echo "In Stock ($stock)";
                                    ?>
                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                    <!-- CART -->

                    <div class="cartBox">

                        <h3><i class="fa-solid fa-cart-shopping"></i> Cart</h3>

                        <div id="cartItems"></div>

                        <hr>

                        <p>Subtotal: ₹<span id="subtotal">0</span></p>
                        <p>GST (18%): ₹<span id="gst">0</span></p>
                        <h3>Total: ₹<span id="total">0</span></h3>

                        <button class="checkoutBtn" onclick="checkout()">
                            <i class="fa-solid fa-credit-card"></i> Checkout
                        </button>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        let cart = {};

        // Helper: update stock text/color on a product card
        function updateStockUI(id) {
            let card = document.querySelector(`.productCard[data-id="${id}"]`);
            if (!card) return;
            let stockText = card.querySelector(".stockText");
            let stock = parseInt(card.dataset.stock);

            if (stock <= 0) {
                stockText.innerText = "Out of Stock";
                stockText.style.color = "red";
                card.classList.add("out");
            } else if (stock < 10) {
                stockText.innerText = `Low Stock (${stock})`;
                stockText.style.color = "orange";
                card.classList.remove("out");
            } else {
                stockText.innerText = `In Stock (${stock})`;
                stockText.style.color = "green";
                card.classList.remove("out");
            }
        }

        function addToCart(e, id, name, price) {

            let card = document.querySelector(`.productCard[data-id="${id}"]`);
            let currentStock = parseInt(card.dataset.stock);

            if (currentStock <= 0) {
                Swal.fire("Out of Stock", "Product not available", "error");
                return;
            }

            // add to cart
            if (!cart[id]) cart[id] = {
                name,
                price,
                qty: 1
            };
            else cart[id].qty++;

            // 🔥 UPDATE UI STOCK
            card.dataset.stock = currentStock - 1;
            updateStockUI(id);

            renderCart();
        }

        function changeQty(id, d) {
            let item = cart[id];
            if (!item) return;

            let card = document.querySelector(`.productCard[data-id="${id}"]`);
            let currentStock = parseInt(card.dataset.stock);

            if (d > 0) {
                // Adding to cart — validate stock
                if (currentStock <= 0) {
                    Swal.fire("Out of Stock", "No more stock available for " + item.name, "error");
                    return;
                }
                item.qty += 1;
                card.dataset.stock = currentStock - 1;
            } else {
                // Removing from cart — restore stock
                item.qty -= 1;
                card.dataset.stock = currentStock + 1;
                if (item.qty <= 0) delete cart[id];
            }

            updateStockUI(id);
            renderCart();
        }

        // Set cart quantity directly from input
        function setQty(id, newQty) {
            let item = cart[id];
            if (!item) return;

            newQty = parseInt(newQty);
            if (isNaN(newQty) || newQty < 0) newQty = 0;

            let card = document.querySelector(`.productCard[data-id="${id}"]`);
            let currentStock = parseInt(card.dataset.stock);
            let oldQty = item.qty;

            // Total available = current stock + what's already in cart
            let totalAvailable = currentStock + oldQty;

            if (newQty > totalAvailable) {
                Swal.fire("Insufficient Stock", "Only " + totalAvailable + " units available for " + item.name, "error");
                renderCart(); // re-render to reset input
                return;
            }

            // Update stock: restore old qty, subtract new qty
            card.dataset.stock = totalAvailable - newQty;

            if (newQty <= 0) {
                delete cart[id];
            } else {
                item.qty = newQty;
            }

            updateStockUI(id);
            renderCart();
        }

        function removeItem(id) {
            let item = cart[id];
            if (item) {
                // Restore stock before removing
                let card = document.querySelector(`.productCard[data-id="${id}"]`);
                let currentStock = parseInt(card.dataset.stock);
                card.dataset.stock = currentStock + item.qty;
                updateStockUI(id);
            }
            delete cart[id];
            renderCart();
        }

        function renderCart() {
            let html = '',
                subtotal = 0;

            for (let id in cart) {
                let i = cart[id];
                let total = i.price * i.qty;
                subtotal += total;

                html += `
<div class="cartItem">
<div>
<b>${i.name}</b><br>
₹${i.price} × ${i.qty}
</div>
<div style="display:flex;align-items:center;gap:6px;">
<button class="qtyBtn" onclick="changeQty(${id},-1)">-</button>
<input type="number" min="0" value="${i.qty}" onchange="setQty(${id},this.value)"
  style="width:50px;text-align:center;border:1px solid #ccc;border-radius:4px;padding:4px;font-size:14px;">
<button class="qtyBtn" onclick="changeQty(${id},1)">+</button>
<span class="removeBtn" onclick="removeItem(${id})">
<i class="fa-solid fa-trash"></i>
</span>
</div>
</div>`;
            }

            let gst = subtotal * 0.18;
            let total = subtotal + gst;

            document.getElementById("cartItems").innerHTML = html;
            document.getElementById("subtotal").innerText = subtotal.toFixed(2);
            document.getElementById("gst").innerText = gst.toFixed(2);
            document.getElementById("total").innerText = total.toFixed(2);
        }

        // ✅ ONLY CHANGE: checkout function
        function checkout() {
            if (Object.keys(cart).length === 0) {
                Swal.fire("Empty Cart", "Add items first", "warning");
                return;
            }

            Swal.fire({
                title: "Customer Details",
                html: `
<input id="name" class="swal2-input" placeholder="Name">
<input id="phone" class="swal2-input" placeholder="Phone" maxlength="10">
<input id="gst" class="swal2-input" placeholder="GST">
`,
                confirmButtonText: "Generate Bill",
                showCancelButton: true,
                cancelButtonText: "Cancel Order",

                preConfirm: () => {

                    let name = document.getElementById("name").value;
                    let phone = document.getElementById("phone").value;
                    let gst = document.getElementById("gst").value;

                    if (name === "") {
                        Swal.showValidationMessage("Name is required");
                        return false;
                    }

                    if (phone === "") {
                        Swal.showValidationMessage("Phone number is required");
                        return false;
                    }

                    if (!/^[0-9]{10}$/.test(phone)) {
                        Swal.showValidationMessage("Enter valid 10-digit mobile number");
                        return false;
                    }

                    return {
                        name,
                        phone,
                        gst
                    };
                }
            }).then(r => {

                // ✅ CANCEL → clear cart only
                if (r.dismiss) {
                    cart = {};
                    renderCart();
                    return;
                }

                if (r.isConfirmed) {

                    fetch('database/pos-sale.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                cart: cart,
                                customer: r.value
                            })
                        })
                        .then(res => res.blob())
                        .then(blob => {

                            let url = URL.createObjectURL(blob);
                            let a = document.createElement('a');
                            a.href = url;
                            a.download = "invoice.pdf";
                            a.click();

                            Swal.fire("Success", "Invoice Downloaded", "success");

                            cart = {};
                            renderCart();
                        });
                }
            });
        }
    </script>

    <script src="js/dashboard.js"></script>

</body>

</html>
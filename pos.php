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

        .ripple {
            position: absolute;
            width: 100px;
            height: 100px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            animation: rippleEffect 0.5s;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @keyframes rippleEffect {
            from {
                transform: scale(0);
                opacity: 0.5;
            }

            to {
                transform: scale(3);
                opacity: 0;
            }
        }
    </style>

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

        function addToCart(e, id, name, price) {

            let card = document.querySelector(`.productCard[data-id="${id}"]`);
            let currentStock = parseInt(card.dataset.stock);

            if (currentStock <= 0) {
                Swal.fire("Out of Stock", "Product not available", "error");
                return;
            }

            if (cart[id] && cart[id].qty >= currentStock) {
                Swal.fire("Stock Limit", "Max stock reached", "warning");
                return;
            }

            let ripple = document.createElement("span");
            ripple.classList.add("ripple");
            card.appendChild(ripple);
            setTimeout(() => ripple.remove(), 500);

            if (!cart[id]) cart[id] = {
                name,
                price,
                qty: 1
            };
            else cart[id].qty++;

            renderCart();
        }

        function changeQty(id, d) {

            let card = document.querySelector(`.productCard[data-id="${id}"]`);
            let stock = parseInt(card.dataset.stock);

            let item = cart[id];

            if (!item) return;

            if (item.qty + d > stock) {
                Swal.fire("Stock Limit", "Cannot exceed stock", "warning");
                return;
            }

            item.qty += d;

            if (item.qty <= 0) delete cart[id];

            renderCart();
        }

        function removeItem(id) {
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
<div>
<button class="qtyBtn" onclick="changeQty(${id},-1)">-</button>
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

        function checkout() {
            if (Object.keys(cart).length === 0) {
                Swal.fire("Empty Cart", "Add items first", "warning");
                return;
            }

            Swal.fire({
                title: "Customer Details",
                html: `
<input id="name" class="swal2-input" placeholder="Name">
<input id="phone" class="swal2-input" placeholder="Phone" maxlength="10" inputmode="numeric">
<input id="gst" class="swal2-input" placeholder="GST">
`,
                confirmButtonText: "Generate Bill",
                preConfirm: () => {

                    let name = document.getElementById("name").value;
                    let phone = document.getElementById("phone").value;
                    let gst = document.getElementById("gst").value;

                    // ✅ PHONE VALIDATION (India - 10 digits starting 6-9)
                    let phoneRegex = /^[6-9]\d{9}$/;

                    if (phone !== "" && !phoneRegex.test(phone)) {
                        Swal.showValidationMessage("Enter valid 10-digit Indian phone number");
                        return false;
                    }

                    return {
                        name,
                        phone,
                        gst
                    };
                }
            }).then(r => {

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
                        .then(async res => {

                            if (!res.ok) {
                                let text = await res.text();
                                throw new Error(text);
                            }

                            return res.blob();
                        })
                        .then(blob => {

                            let url = URL.createObjectURL(blob);
                            let a = document.createElement('a');
                            a.href = url;
                            a.download = "invoice.pdf";
                            a.click();

                            Swal.fire("Success", "Invoice Downloaded", "success");

                            /* 🔥 UPDATE STOCK LIVE */
                            for (let id in cart) {

                                let qtySold = cart[id].qty;

                                let card = document.querySelector(`.productCard[data-id="${id}"]`);
                                let stockText = card.querySelector(".stockText");

                                let currentStock = parseInt(card.dataset.stock);
                                let newStock = currentStock - qtySold;

                                card.dataset.stock = newStock;

                                if (newStock <= 0) {
                                    stockText.innerText = "Out of Stock";
                                    stockText.style.color = "red";
                                    card.classList.add("out");
                                } else if (newStock < 10) {
                                    stockText.innerText = `Low Stock (${newStock})`;
                                    stockText.style.color = "orange";
                                } else {
                                    stockText.innerText = `In Stock (${newStock})`;
                                    stockText.style.color = "green";
                                }
                            }

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
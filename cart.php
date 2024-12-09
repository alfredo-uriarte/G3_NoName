<?php

include_once 'includes/header.php';


session_start();
require_once 'config/Database.php';
require_once 'models/Cart.php';
require_once 'models/Product.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$database = new Database();
$db = $database->connect();

// Cart instance
$cart = new Cart($db);

// Get cart items for the logged-in user
$cart_items = $cart->getCartItems($_SESSION['user_id']);

// Handle form submission for adding a product to the cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    if (isset($_POST['update_quantity'])) {
        $cart->updateQuantity($_SESSION['user_id'], $product_id, $quantity);          // Update quantity if "update_quantity" is set
    } else {
        $cart->addToCart($_SESSION['user_id'], $product_id, $quantity);          // Add the product to the cart if "update_quantity" is not set
    }
    header('Location: cart.php');      // After adding or updating, redirect to refresh the page and show updated cart
    exit();
}

// Handle removing an item from the cart
if (isset($_GET['remove'])) {
    $product_id = $_GET['remove'];
    $cart->removeFromCart($_SESSION['user_id'], $product_id);
    header('Location: cart.php');      // After removing, redirect to the same page to refresh the cart
    exit();
}

// Calculate the total price of the cart
$total = 0;
foreach ($cart_items as &$item) {
    $item_total = $item['price'] * $item['quantity'];
    $total += $item_total;
    $item['item_total'] = $item_total;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <!-- <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index_1.php">E-Commerce Store</a>
        </div>
    </nav> -->

    <div class="container mt-4">
        <h1>Your Shopping Cart</h1>

        <?php if (empty($cart_items)): ?>
            <div class="alert alert-info">
                Your cart is empty. <a href="index_1.php">Continue shopping</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">Cart Items</div>
                        <div class="card-body">
                            <?php foreach ($cart_items as &$item): ?>
                                <div class="row mb-3 align-items-center">
                                    <div class="col-md-2">
                                        <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'assets/placeholder.jpg'); ?>"
                                            class="img-fluid" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                        <p class="text-muted">$<?php echo number_format($item['price'], 2); ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <form action="cart.php" method="POST">
                                            <input type="number" name="quantity" min="1" value="<?= htmlspecialchars($item['quantity']) ?>" onchange="this.form.submit()">
                                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['product_id']) ?>">
                                            <input type="hidden" name="update_quantity" value="1">
                                        </form>

                                    </div>
                                    <div class="col-md-2 text-end">
                                        <strong>$<?php echo number_format($item['item_total'], 2); ?></strong>
                                    </div>
                                    <div class="col-md-1">
                                        <a href="cart.php?remove=<?= htmlspecialchars($item['product_id']) ?>" class="remove-btn btn btn-danger btn-sm"> <i class="fas fa-trash"></i></a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">Cart Summary</div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <strong>$<?php echo number_format($total, 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>HST (13%)</span>
                                <strong>$<?php echo number_format($total * 0.13, 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="h5">Total</span>
                                <strong class="h5">$<?php echo number_format($total * 1.1, 2); ?></strong>
                            </div>
                            <a href="checkout.php" class="btn btn-primary w-100"> Proceed to Checkout </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
include_once 'includes/footer.php';
?>
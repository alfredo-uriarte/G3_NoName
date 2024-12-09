<?php

include_once 'includes/header.php';


session_start();
require_once 'fpdf/fpdf.php';
require_once 'config/Database.php';
require_once 'models/Cart.php';
require_once 'models/Order.php';
require_once 'models/User.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$database = new Database();
$db = $database->connect();

// Initialize classes
$cart = new Cart($db);
$order = new Order($db);
$user = new User($db);

// Fetch cart items
$cart_items = $cart->getCartItems($_SESSION['user_id']);

// If cart is empty, redirect
if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate inputs
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $shipping_address = filter_input(INPUT_POST, 'shipping_address', FILTER_SANITIZE_STRING);
        $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);

        if (empty($name) || empty($email) || empty($phone) || empty($shipping_address) || empty($payment_method)) {
            throw new Exception("Please fill in all required fields.");
        }
        
        // Create order
        $order_id = $order->createOrder($_SESSION['user_id'], $cart_items);

        if ($order_id) {
            // Save order details for PDF generation later
            $_SESSION['order_details'] = [
                'order_id' => $order_id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'shipping_address' => $shipping_address,
                'payment_method' => $payment_method,
                'cart_items' => $cart_items
            ];

            // Clear cart after successful order
            $cart->clearCart($_SESSION['user_id']);

            // Redirect to order confirmation, after saving order details
            $_SESSION['last_order_id'] = $order_id;
            header('Location: order-confirmation.php');
            exit();
        } else {
            throw new Exception("Failed to create order. Please try again.");
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Your E-Commerce Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="styles.css"> -->
     <!-- <link rel="stylesheet" href="style.css"> -->
    <!-- <style>
        body {
            font-family: 'Titillium Web', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }


    </style> -->
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    </nav>
    <div class="checkout-container">
        <h1>Checkout</h1>
        <?php if (isset($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <div class="checkout-wrapper">
            <div class="cart-summary">
                <h2>Order Summary</h2>
                <?php
                $total = 0;
                foreach ($cart_items as $item):
                    $item_total = $item['price'] * $item['quantity'];
                    $total += $item_total;
                ?>
                    <div class="cart-item">
                        <span><?php echo $item['name']; ?></span>
                        <span><?php echo $item['quantity']; ?> x $<?php echo number_format($item['price'], 2); ?></span>
                        <span>$<?php echo number_format($item_total, 2); ?></span>
                    </div>
                <?php endforeach; ?>

                <div class="total">
                    Total: $<?php echo number_format($total, 2); ?>
                </div>
            </div>

            <div class="checkout-form">
                <h2>Shipping & Payment Details</h2>
                <form method="post">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" required>

                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" required>

                    <label for="phone">Phone Number</label>
                    <input type="text" name="phone" id="phone" required>

                    <label for="shipping_address">Shipping Address</label>
                    <textarea name="shipping_address" id="shipping_address" rows="4" required></textarea>

                    <select id="payment_method" name="payment_method" required>
                        <option value="credit_card">Credit Card</option>
                        <option value="paypal">PayPal</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash_on_delivery">Cash on Delivery</option>
                        <option value="stripe">Stripe</option>
                    </select>

                    <button class="checkout-button" type="submit">Place Order</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>


<?php
include_once 'includes/footer.php';
?>
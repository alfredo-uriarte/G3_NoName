<?php
session_start();
require_once 'fpdf/fpdf.php';
require_once 'config/Database.php';
require_once 'models/Order.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Start output buffering
ob_start();

// Handle PDF download if requested
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['order_details'])) {
        $order_details = $_SESSION['order_details'];

        // Generate PDF
        $pdf = new FPDF();
        $pdf->AddPage();

        // Header Section
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 15, 'Order Confirmation', 0, 1, 'C');
        $pdf->Ln(5);

        // Customer Information Section
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Customer Information', 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(50, 10, 'Order ID:', 0, 0);
        $pdf->Cell(50, 10, $order_details['order_id'], 0, 1);
        $pdf->Cell(50, 10, 'Name:', 0, 0);
        $pdf->Cell(50, 10, $order_details['name'], 0, 1);
        $pdf->Cell(50, 10, 'Email:', 0, 0);
        $pdf->Cell(50, 10, $order_details['email'], 0, 1);
        $pdf->Cell(50, 10, 'Phone:', 0, 0);
        $pdf->Cell(50, 10, $order_details['phone'], 0, 1);
        $pdf->Cell(50, 10, 'Shipping Address:', 0, 0);
        $pdf->MultiCell(0, 10, $order_details['shipping_address']);
        $pdf->Ln(10);

        // Payment Method Section
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Payment Method: ' . ucfirst($order_details['payment_method']), 0, 1);
        $pdf->Ln(10);

        // Table Header for Cart Items
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(90, 10, 'Item', 1, 0, 'C', true);
        $pdf->Cell(30, 10, 'Quantity', 1, 0, 'C', true);
        $pdf->Cell(30, 10, 'Price', 1, 0, 'C', true);
        $pdf->Cell(30, 10, 'Total', 1, 1, 'C', true);
        $pdf->SetFont('Arial', '', 12);

        // Table Content for Cart Items
        $total = 0;
        foreach ($order_details['cart_items'] as $item) {
            $item_total = $item['price'] * $item['quantity'];
            $total += $item_total;
            $pdf->Cell(90, 10, $item['name'], 1);
            $pdf->Cell(30, 10, $item['quantity'], 1, 0, 'C');
            $pdf->Cell(30, 10, '$' . number_format($item['price'], 2), 1, 0, 'C');
            $pdf->Cell(30, 10, '$' . number_format($item_total, 2), 1, 1, 'C');
        }

        // Total Section
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(150, 10, 'Total Amount', 1, 0, 'R', true);
        $pdf->Cell(30, 10, '$' . number_format($total, 2), 1, 1, 'C', true);
        $pdf->Ln(10);

        // Send PDF to browser for download
        $file_path = 'order_summary_' . $order_details['order_id'] . '.pdf';
        $pdf->Output('D', $file_path); // 'D' forces download
        exit();
    }
}

$database = new Database();
$db = $database->connect();

$order = new Order($db);

// Fetch order details
$order_details = $order->getOrder($_SESSION['user_id']);
$order_items = $order->getOrderItems($_SESSION['user_id']);

// Clear the last order session to prevent repeated views
//unset($_SESSION['last_order_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="confirmation-container">
        <h1>Order Confirmation</h1>
        <p>Thank you for your purchase, <?php echo htmlspecialchars($order_details['username']); ?>!</p>

        <!-- <div class="order-details">
            <h2>Order Details</h2>
            <p>Order ID: #<?php echo htmlspecialchars($order_details['order_id']); ?></p>
            <p>Total Amount: $<?php echo number_format($order_details['total_amount'], 2); ?></p>

            <h3>Order Items</h3>
            <?php foreach ($order_items as $item): ?>
                <div class="order-item">
                    <div>
                        <?php echo htmlspecialchars($item['name']); ?>
                        (x<?php echo intval($item['quantity']); ?>)
                    </div>
                    <div>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                </div>
            <?php endforeach; ?>
        </div> -->

        <div class="actions">
            <a href="index_1.php">Continue Shopping</a>
            <form method="post">
                <button class="pdf-button" type="submit">Download</button>
            </form>
        </div>
    </div>
</body>

</html>
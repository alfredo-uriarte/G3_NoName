<?php

include_once 'includes/header.php';

session_start();
require_once 'config/Database.php';
require_once 'models/Product.php';
require_once 'models/Cart.php';

// Database connection
$database = new Database();
$db = $database->connect();

// Instance Product class
$product = new Product($db);

// Fetch products (with optional filtering)
$filters = [];
if (isset($_GET['category'])) {
    $filters['category_id'] = $_GET['category'];
}
$products_stmt = $product->read($filters);
$products = $products_stmt->fetchAll();

// Fetch categories for sidebar
$category_stmt = $db->query("SELECT * FROM categories");
$categories = $category_stmt->fetchAll();

// Get cart items for the logged-in user
$cart = new Cart($db);
// $cart_items = $cart->getCartItems($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>E-Commerce Store</title>
</head>

<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <!-- <a class="navbar-brand" href="#">E-Commerce Store</a> -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="home_1.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <i class="fas fa-shopping-cart"></i> Cart
                        <span class="badge bg-danger">
                            <?php
                            // Display cart item count
                            echo isset($_SESSION['user_id']) ? $cart->getTotalItems($_SESSION['user_id']) : 0;
                            ?>
                        </span>
                    </a>
                </li>
            </ul>

            <!-- Centered Greeting Message -->
            <div class="mx-auto text-center">
                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['first_name'])): ?>
                    <span class="navbar-text">
                        Hi, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!
                    </span>
                <?php endif; ?>
            </div>

            <!-- Login/Logout Buttons -->
            <div class="d-flex">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary me-2">Login</a>
                    <a href="register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-4 mb-5">
    <div class="row">
        <!-- Categories Sidebar -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Categories</span>
                    <!-- Reset Button -->
                    <a href="index_1.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($categories as $category): ?>
                        <li class="list-group-item">
                            <a href="index_1.php?category=<?php echo $category['category_id']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-md-9">
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($products as $product): ?>
                    <div class="col">
                        <div class="card h-100 product-card">
                            <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'assets/placeholder.jpg'); ?>"
                                class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h4 text-primary">$<?php echo number_format($product['price'], 2); ?></span>
                                    <form action="cart.php" method="POST">
                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-cart-plus"></i> Add to Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
include_once 'includes/footer.php';
?>

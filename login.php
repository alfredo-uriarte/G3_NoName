<?php
session_start();

// Prevent already logged-in users from accessing login page
if (isset($_SESSION['user_id'])) {
    header('Location: index_1.php');
    exit();
}

require_once 'config/Database.php';
require_once 'models/User.php';

// Initialize variables
$username = '';
$password = '';
$error_message = '';

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password']; // Don't sanitize password for verification

    // Validate input
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        // Database connection
        $database = new Database();
        $db = $database->connect();

        // Create user object
        $user = new User($db);

        // Attempt login
        $logged_in_user = $user->login($username, $password);

        if ($logged_in_user) {
            // Successful login
            $_SESSION['user_id'] = $logged_in_user['user_id'];
            $_SESSION['username'] = $logged_in_user['username'];
            $_SESSION['email'] = $logged_in_user['email'];
            $_SESSION['first_name'] = $logged_in_user['first_name'];

            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            // Redirect to dashboard or home page
            header('Location: index_1.php');
            exit();
        } else {
            $error_message = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login - E-Commerce Store</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="login_page">
        <div class="login-container">
            <div class="login-header">
                <h2>Login to Your Account</h2>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="error-message" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required aria-required="true"
                        value="<?php echo htmlspecialchars($username); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required aria-required="true">
                </div>

                <button type="submit" class="login-button">
                    Login
                </button>
            </form>

            <div class="register-link">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
</body>

</html>
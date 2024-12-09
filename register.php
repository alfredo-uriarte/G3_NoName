<?php
session_start();
require_once 'config/Database.php';
require_once 'models/User.php';

// Redirect logged-in users
if (isset($_SESSION['user_id'])) {
    header('Location: index_1.php');
    exit();
}

// Initialize database connection
$database = new Database();
$db = $database->connect();

// Initialize user object
$user = new User($db);

// Initialize error and success messages
$errors = [];
$success_message = '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);

    // Validation checks
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }

    if (empty($first_name)) {
        $errors[] = "First name is required";
    }

    if (empty($last_name)) {
        $errors[] = "Last name is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Additional password strength check
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
        $errors[] = "Password must include uppercase, lowercase, number, and special character";
    }

    // If no errors, attempt to register
    if (empty($errors)) {
        try {
            // Prepare user object
            $user->username = $username;
            $user->email = $email;
            $user->password = $password;
            $user->first_name = $first_name;
            $user->last_name = $last_name;
            $user->phone_number = $phone_number;
            $user->address = $address;

            // Attempt registration
            if ($user->register()) {
                $success_message = "Registration successful! You can now log in.";
                
                // Optional: Automatically log in user or redirect to login
                $_SESSION['registration_success'] = true;
            } else {
                $errors[] = "Registration failed. Username or email might already exist.";
            }
        } catch (Exception $e) {
            $errors[] = "Registration error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <div class="registration-form">
        <h2>User Registration</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-message" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>
            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required 
                    minlength="3"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    aria-required="true"
                >
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    aria-required="true"
                >
            </div>

            <div class="form-group">
                <label for="first_name">First Name</label>
                <input 
                    type="text" 
                    id="first_name" 
                    name="first_name" 
                    required
                    value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                    aria-required="true"
                >
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input 
                    type="text" 
                    id="last_name" 
                    name="last_name" 
                    required
                    value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                    aria-required="true"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    minlength="8"
                    aria-required="true"
                >
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required 
                    minlength="8"
                    aria-required="true"
                >
            </div>

            <div class="form-group">
                <label for="phone_number">Phone Number (Optional)</label>
                <input 
                    type="tel" 
                    id="phone_number" 
                    name="phone_number"
                    value="<?php echo htmlspecialchars($_POST['phone_number'] ?? ''); ?>"
                >
            </div>

            <div class="form-group">
                <label for="address">Address (Optional)</label>
                <textarea 
                    id="address" 
                    name="address"
                    rows="3"
                ><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="submit-btn">Register</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Log in</a>
        </div>
    </div>

    <script>
    // Optional: Client-side validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match');
        }
    });
    </script>
</body>
</html>
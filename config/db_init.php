<?php
// Database credentials
$host = 'localhost';
$username = 'root'; // Adjust if necessary
$password = ''; // Adjust if necessary
$dbname = 'ecommerce'; // Your database name

// Create a connection
$conn = new mysqli($host, $username, $password);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create the database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database '$dbname' created successfully or already exists.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Drop tables if they exist
$dropTables = [
    "DROP TABLE IF EXISTS invoices",
    "DROP TABLE IF EXISTS order_items",
    "DROP TABLE IF EXISTS orders",
    "DROP TABLE IF EXISTS cart",
    "DROP TABLE IF EXISTS products",
    "DROP TABLE IF EXISTS categories",
    "DROP TABLE IF EXISTS users"
];

foreach ($dropTables as $query) {
    if ($conn->query($query) === TRUE) {
        echo "Table dropped successfully.<br>";
    } else {
        echo "Error dropping table: " . $conn->error . "<br>";
    }
}

// Create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        phone_number VARCHAR(15),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS categories (
        category_id INT AUTO_INCREMENT PRIMARY KEY,
        category_name VARCHAR(100) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS products (
        product_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        category_id INT,
        stock_quantity INT NOT NULL,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(category_id)
    )",
    "CREATE TABLE IF NOT EXISTS cart (
        cart_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        product_id INT,
        quantity INT NOT NULL,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (product_id) REFERENCES products(product_id)
    )",
    "CREATE TABLE IF NOT EXISTS orders (
        order_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        total_amount DECIMAL(10, 2) NOT NULL,
        status ENUM('Pending', 'Completed', 'Cancelled') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )",
    "CREATE TABLE IF NOT EXISTS order_items (
        order_item_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT,
        quantity INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(order_id),
        FOREIGN KEY (product_id) REFERENCES products(product_id)
    )",
    "CREATE TABLE IF NOT EXISTS invoices (
        invoice_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        invoice_number VARCHAR(100) UNIQUE NOT NULL,
        invoice_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        total_amount DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(order_id)
    )"
];

// Execute table creation queries
foreach ($tables as $query) {
    if ($conn->query($query) === TRUE) {
        echo "Table created or already exists.<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

// Insert dummy data
$data = [
    // Insert categories
    "INSERT INTO categories (category_name) VALUES
    ('Fiction'), ('Non-Fiction'), ('Science'), ('Biography'),
    ('Mystery'), ('Fantasy'), ('History'), ('Children')",

    // Insert products
    "INSERT INTO products (name, description, price, category_id, stock_quantity, image_url) VALUES
    ('The Great Gatsby', 'A classic novel by F. Scott Fitzgerald.', 10.99, 1, 50, 'images/gatsby.jpg'),
    ('Sapiens: A Brief History of Humankind', 'Explores the history and impact of Homo sapiens.', 15.99, 2, 30, 'images/sapiens.jpg'),
    ('Astrophysics for People in a Hurry', 'Neil deGrasse Tyson\'s guide to the universe.', 12.49, 3, 20, 'images/astrophysics.jpg'),
    ('The Diary of a Young Girl', 'Anne Frank\'s moving diary.', 9.99, 4, 25, 'images/annefrank.jpg'),
    ('The Hound of the Baskervilles', 'A Sherlock Holmes mystery by Arthur Conan Doyle.', 11.99, 5, 40, 'images/baskervilles.jpg'),
    ('Harry Potter and the Sorcerer\'s Stone', 'The first book in the Harry Potter series.', 8.99, 6, 60, 'images/harrypotter.jpg'),
    ('A People\'s History of the United States', 'An alternative take on American history.', 14.99, 7, 15, 'images/history.jpg'),
    ('The Cat in the Hat', 'A classic children\'s book by Dr. Seuss.', 6.99, 8, 70, 'images/catinthehat.jpg')",

    // Insert users
    "INSERT INTO users (username, email, password, first_name, last_name, phone_number, address) VALUES
    ('johndoe', 'john.doe@example.com', MD5('password123'), 'John', 'Doe', '123-456-7890', '123 Elm Street, Springfield'),
    ('janedoe', 'jane.doe@example.com', MD5('password456'), 'Jane', 'Doe', '987-654-3210', '456 Oak Avenue, Smallville'),
    ('admin', 'admin@example.com', MD5('adminpass'), 'Admin', 'User', NULL, NULL)",

    // Insert cart items
    "INSERT INTO cart (user_id, product_id, quantity) VALUES
    (1, 1, 2), (1, 3, 1), (2, 6, 1), (2, 8, 3)",

    // Insert orders
    "INSERT INTO orders (user_id, total_amount, status) VALUES
    (1, 33.47, 'Completed'), (2, 29.96, 'Pending')",

    // Insert order items
    "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
    (1, 1, 2, 21.98), (1, 3, 1, 12.49), (2, 6, 1, 8.99), (2, 8, 3, 20.97)",

    // Insert invoices
    "INSERT INTO invoices (order_id, invoice_number, total_amount) VALUES
    (1, 'INV20231101', 33.47), (2, 'INV20231102', 29.96)"
];

// Execute insert queries
foreach ($data as $query) {
    if ($conn->query($query) === TRUE) {
        echo "Data inserted successfully.<br>";
    } else {
        echo "Error inserting data: " . $conn->error . "<br>";
    }
}

// Close the connection
$conn->close();
?>

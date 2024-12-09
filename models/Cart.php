<?php
class Cart {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addToCart($user_id, $product_id, $quantity) {
        // Check if the product already exists in the cart
        $query = "SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
    
        $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($existing_item) {
            // If the product already exists, update the quantity
            $new_quantity = $existing_item['quantity'] + $quantity;
            $update_query = "UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id";
            $update_stmt = $this->db->prepare($update_query);
            $update_stmt->bindParam(':quantity', $new_quantity, PDO::PARAM_INT);
            $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $update_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $update_stmt->execute();
        } else {
            // Add the product to the cart if it doesn't already exist
            $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
            $insert_stmt = $this->db->prepare($insert_query);
            $insert_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $insert_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $insert_stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $insert_stmt->execute();
        }
    }
    
    // Retrieve all items in the user's cart
    public function getCartItems($user_id) {
        $query = "
            SELECT cart.*, products.price, products.name, products.image_url
            FROM cart
            JOIN products ON cart.product_id = products.product_id
            WHERE cart.user_id = :user_id
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    
        // Fetch all cart items and return them
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //var_dump($cart_items);
        
        return $cart_items;
    }
    

    // Remove an item from the cart
    public function removeFromCart($user_id, $product_id) {
        // Remove product from the cart table for the specific user
        $query = "DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
    }
    

    // Update product quantity in the cart
    public function updateQuantity($user_id, $product_id, $quantity) {
        $query = "UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
    }
    

    // Clear the user's cart
    public function clearCart($user_id) {
        $query = "DELETE FROM cart WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    // retrieve the total number of items in the cart. If cart is empty, return 0
    public function getTotalItems($user_id) {
        $query = "SELECT SUM(quantity) as total_items FROM cart WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_items'] ?? 0;
    }

}
?>
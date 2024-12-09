<?php
class Order
{
    private $conn;
    private $table = 'orders';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createOrder($user_id, $cart_items)
    {
        try {
            $this->conn->beginTransaction();

            // Calculate total amount
            $total_amount = 0;
            foreach ($cart_items as $item) {
                $total_amount += $item['price'] * $item['quantity'];
            }

            // Create order
            $query = "INSERT INTO " . $this->table . "
                    SET user_id = :user_id,
                        total_amount = :total_amount";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":total_amount", $total_amount);
            $stmt->execute();

            $order_id = $this->conn->lastInsertId();

            // Create order items
            foreach ($cart_items as $item) {
                $query = "INSERT INTO order_items
                        SET order_id = :order_id,
                            product_id = :product_id,
                            quantity = :quantity,
                            price = :price";

                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":order_id", $order_id);
                $stmt->bindParam(":product_id", $item['product_id']);
                $stmt->bindParam(":quantity", $item['quantity']);
                $stmt->bindParam(":price", $item['price']);
                $stmt->execute();
            }

            // Create invoice
            $invoice_number = 'INV-' . date('Ymd') . '-' . $order_id;
            $query = "INSERT INTO invoices
                    SET order_id = :order_id,
                        invoice_number = :invoice_number,
                        total_amount = :total_amount";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":order_id", $order_id);
            $stmt->bindParam(":invoice_number", $invoice_number);
            $stmt->bindParam(":total_amount", $total_amount);
            $stmt->execute();

            $this->conn->commit();
            return $order_id;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getOrder($order_id)
    {
        $query = "SELECT o.*, u.username, u.email
                FROM " . $this->table . " o
                JOIN users u ON o.user_id = u.user_id
                WHERE o.order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $order_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOrderItems($order_id)
    {
        $query = "SELECT oi.*, p.name, p.image_url
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                WHERE oi.order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $order_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

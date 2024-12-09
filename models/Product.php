<?php
class Product
{
    private $conn;
    private $table = 'products';

    // Properties matching your database columns
    public $product_id;
    public $name;
    public $description;
    public $price;
    public $category_id;
    public $stock_quantity;
    public $image_url;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Read all products with optional filtering
    public function read($filters = [])
    {
        $query = "SELECT p.*, c.category_name 
                FROM " . $this->table . " p
                LEFT JOIN categories c ON p.category_id = c.category_id";

        if (!empty($filters)) {
            $query .= " WHERE ";
            $conditions = [];
            foreach ($filters as $key => $value) {
                $conditions[] = "p.$key = :$key";
            }
            $query .= implode(" AND ", $conditions);
        }

        $stmt = $this->conn->prepare($query);

        foreach ($filters as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return $stmt;
    }

    // Create new product
    public function create()
    {
        $query = "INSERT INTO " . $this->table . "
                SET name = :name, 
                    description = :description, 
                    price = :price,
                    category_id = :category_id,
                    stock_quantity = :stock_quantity,
                    image_url = :image_url";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = floatval($this->price);
        $this->category_id = intval($this->category_id);
        $this->stock_quantity = intval($this->stock_quantity);
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":stock_quantity", $this->stock_quantity);
        $stmt->bindParam(":image_url", $this->image_url);

        return $stmt->execute();
    }

    // Update product
    public function update()
    {
        $query = "UPDATE " . $this->table . "
                SET name = :name,
                    description = :description,
                    price = :price,
                    category_id = :category_id,
                    stock_quantity = :stock_quantity,
                    image_url = :image_url
                WHERE product_id = :product_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize and bind values (similar to create method)
        // ... [sanitization code as above] ...

        $stmt->bindParam(":product_id", $this->product_id);
        // ... [bind other parameters as above] ...

        return $stmt->execute();
    }

    // Delete product
    public function delete()
    {
        $query = "DELETE FROM " . $this->table . " WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $this->product_id);
        return $stmt->execute();
    }
}

<?php
class User
{
    private $conn;
    private $table = 'users';

    // Properties
    public $user_id;
    public $username;
    public $email;
    public $password;
    public $first_name;
    public $last_name;
    public $phone_number;
    public $address;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function register()
    {
        $query = "INSERT INTO " . $this->table . "
                SET username = :username,
                    email = :email,
                    password = :password,
                    first_name = :first_name,
                    last_name = :last_name,
                    phone_number = :phone_number,
                    address = :address";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT); // Hash password
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->phone_number = htmlspecialchars(strip_tags($this->phone_number));
        $this->address = htmlspecialchars(strip_tags($this->address));

        // Bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":phone_number", $this->phone_number);
        $stmt->bindParam(":address", $this->address);

        return $stmt->execute();
    }

    public function login($username, $password)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);

        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(":username", $username);

        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($password, $row['password'])) {
                return $row;
            }
        }

        return false;
    }
}

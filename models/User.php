<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $role;
    public $full_name;
    public $email;
    public $last_login;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ກວດສອບການເຂົ້າລະບົບ
    public function login() {
        $query = "SELECT id, username, password, role, full_name, email, is_active 
                  FROM " . $this->table_name . " 
                  WHERE username = :username AND is_active = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $this->username);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            if(password_verify($this->password, $row['password'])) {
                $this->id = $row['id'];
                $this->role = $row['role'];
                $this->full_name = $row['full_name'];
                $this->email = $row['email'];
                
                // ອັບເດດເວລາເຂົ້າລະບົບຄັ້ງສຸດທ້າຍ
                $this->updateLastLogin();
                return true;
            }
        }
        return false;
    }

    // ອັບເດດເວລາເຂົ້າລະບົບຄັ້ງສຸດທ້າຍ
    private function updateLastLogin() {
        $query = "UPDATE " . $this->table_name . " SET last_login = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
    }

    // ສ້າງຜູ້ໃຊ້ໃໝ່
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, password, role, full_name, email) 
                  VALUES (:username, :password, :role, :full_name, :email)";
        
        $stmt = $this->conn->prepare($query);
        
        // ເຂົ້າລະຫັດລະຫັດຜ່ານ
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':email', $this->email);
        
        return $stmt->execute();
    }

    // ດຶງຂໍ້ມູນຜູ້ໃຊ້ທັງໝົດ
    public function readAll() {
        $query = "SELECT id, username, role, full_name, email, created_at, last_login, is_active 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // ດຶງຂໍ້ມູນຜູ້ໃຊ້ຕາມ ID
    public function readOne() {
        $query = "SELECT id, username, role, full_name, email, is_active 
                  FROM " . $this->table_name . " 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $this->username = $row['username'];
            $this->role = $row['role'];
            $this->full_name = $row['full_name'];
            $this->email = $row['email'];
            $this->is_active = $row['is_active'];
            return true;
        }
        return false;
    }

    // ກວດສອບວ່າມີຊື່ຜູ້ໃຊ້ນີ້ແລ້ວບໍ
    public function usernameExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $this->username);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}
?>

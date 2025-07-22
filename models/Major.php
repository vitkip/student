<?php
require_once __DIR__ . '/../config/database.php';

class Major {
    private $conn;
    private $table_name = "majors";

    public $id;
    public $name;
    public $code;
    public $description;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ສ້າງສາຂາວິຊາໃໝ່
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, code, description, status) 
                  VALUES (:name, :code, :description, :status)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':status', $this->status);
        
        return $stmt->execute();
    }

    // ດຶງຂໍ້ມູນສາຂາວິຊາທັງໝົດ
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // ດຶງຂໍ້ມູນສາຂາວິຊາທີ່ເປີດໃຊ້ງານເທົ່ານັ້ນ
    public function readActive() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'active' ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // ດຶງຂໍ້ມູນສາຂາວິຊາຕາມ ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $this->name = $row['name'];
            $this->code = $row['code'];
            $this->description = $row['description'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    // ອັບເດດຂໍ້ມູນສາຂາວິຊາ
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, code = :code, description = :description, status = :status
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // ລົບສາຂາວິຊາ
    public function delete() {
        // ກວດສອບວ່າມີນັກສຶກສາໃນສາຂານີ້ບໍ
        $check_query = "SELECT COUNT(*) as count FROM students WHERE major_id = :id";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(':id', $this->id);
        $check_stmt->execute();
        $result = $check_stmt->fetch();
        
        if ($result['count'] > 0) {
            return false; // ບໍ່ສາມາດລົບໄດ້ເພາະມີນັກສຶກສາໃນສາຂານີ້
        }
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // ກວດສອບວ່າມີລະຫັດສາຂານີ້ແລ້ວບໍ
    public function codeExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE code = :code";
        if (!empty($this->id)) {
            $query .= " AND id != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        
        if (!empty($this->id)) {
            $stmt->bindParam(':id', $this->id);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>

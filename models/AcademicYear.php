<?php
require_once __DIR__ . '/../config/database.php';

class AcademicYear {
    private $conn;
    private $table_name = "academic_years";

    public $id;
    public $year;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ສ້າງປີການສຶກສາໃໝ່
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (year, status) 
                  VALUES (:year, :status)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':year', $this->year);
        $stmt->bindParam(':status', $this->status);
        
        return $stmt->execute();
    }

    // ດຶງຂໍ້ມູນປີການສຶກສາທັງໝົດ
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY year DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // ດຶງຂໍ້ມູນປີການສຶກສາທີ່ເປີດໃຊ້ງານເທົ່ານັ້ນ
    public function readActive() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'active' ORDER BY year DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // ດຶງຂໍ້ມູນປີການສຶກສາຕາມ ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $this->year = $row['year'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    // ອັບເດດຂໍ້ມູນປີການສຶກສາ
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET year = :year, status = :status
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':year', $this->year);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // ລົບປີການສຶກສາ
    public function delete() {
        // ກວດສອບວ່າມີນັກສຶກສາໃນປີການສຶກສານີ້ບໍ
        $check_query = "SELECT COUNT(*) as count FROM students WHERE academic_year_id = :id";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(':id', $this->id);
        $check_stmt->execute();
        $result = $check_stmt->fetch();
        
        if ($result['count'] > 0) {
            return false; // ບໍ່ສາມາດລົບໄດ້ເພາະມີນັກສຶກສາໃນປີການສຶກສານີ້
        }
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // ກວດສອບວ່າມີປີການສຶກສານີ້ແລ້ວບໍ
    public function yearExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE year = :year";
        if (!empty($this->id)) {
            $query .= " AND id != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $this->year);
        
        if (!empty($this->id)) {
            $stmt->bindParam(':id', $this->id);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>

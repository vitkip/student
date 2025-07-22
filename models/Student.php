<?php
require_once __DIR__ . '/../config/database.php';

class Student {
    private $conn;
    private $table_name = "students";

    public $id;
    public $student_id;
    public $first_name;
    public $last_name;
    public $gender;
    public $dob;
    public $email;
    public $phone;
    public $village;
    public $district;
    public $province;
    public $accommodation_type;
    public $photo;
    public $major_id;
    public $academic_year_id;
    public $previous_school;
    public $registered_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ສ້າງນັກສຶກສາໃໝ່
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (student_id, first_name, last_name, gender, dob, email, phone, 
                   village, district, province, accommodation_type, photo, 
                   major_id, academic_year_id, previous_school) 
                  VALUES (:student_id, :first_name, :last_name, :gender, :dob, :email, :phone,
                          :village, :district, :province, :accommodation_type, :photo,
                          :major_id, :academic_year_id, :previous_school)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':student_id', $this->student_id);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':gender', $this->gender);
        $stmt->bindParam(':dob', $this->dob);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':village', $this->village);
        $stmt->bindParam(':district', $this->district);
        $stmt->bindParam(':province', $this->province);
        $stmt->bindParam(':accommodation_type', $this->accommodation_type);
        $stmt->bindParam(':photo', $this->photo);
        $stmt->bindParam(':major_id', $this->major_id);
        $stmt->bindParam(':academic_year_id', $this->academic_year_id);
        $stmt->bindParam(':previous_school', $this->previous_school);
        
        return $stmt->execute();
    }

    // ດຶງຂໍ້ມູນນັກສຶກສາທັງໝົດພ້ອມ pagination
    public function readAll($page = 1, $records_per_page = 10, $search = '') {
        $from_record_num = ($page - 1) * $records_per_page;
        
        $query = "SELECT s.*, m.name as major_name, m.code as major_code, ay.year 
                  FROM " . $this->table_name . " s
                  LEFT JOIN majors m ON s.major_id = m.id
                  LEFT JOIN academic_years ay ON s.academic_year_id = ay.id";
        
        if (!empty($search)) {
            $query .= " WHERE s.first_name LIKE :search 
                       OR s.last_name LIKE :search 
                       OR s.student_id LIKE :search
                       OR s.email LIKE :search";
        }
        
        $query .= " ORDER BY s.registered_at DESC 
                   LIMIT :from_record_num, :records_per_page";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($search)) {
            $search_term = "%{$search}%";
            $stmt->bindParam(':search', $search_term);
        }
        
        $stmt->bindParam(':from_record_num', $from_record_num, PDO::PARAM_INT);
        $stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // ນັບຈໍານວນນັກສຶກສາທັງໝົດ
    public function countAll($search = '') {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        
        if (!empty($search)) {
            $query .= " WHERE first_name LIKE :search 
                       OR last_name LIKE :search 
                       OR student_id LIKE :search
                       OR email LIKE :search";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($search)) {
            $search_term = "%{$search}%";
            $stmt->bindParam(':search', $search_term);
        }
        
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'];
    }

    // ດຶງຂໍ້ມູນນັກສຶກສາຕາມ ID
    public function readOne() {
        $query = "SELECT s.*, m.name as major_name, ay.year 
                  FROM " . $this->table_name . " s
                  LEFT JOIN majors m ON s.major_id = m.id
                  LEFT JOIN academic_years ay ON s.academic_year_id = ay.id
                  WHERE s.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $this->student_id = $row['student_id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->gender = $row['gender'];
            $this->dob = $row['dob'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->village = $row['village'];
            $this->district = $row['district'];
            $this->province = $row['province'];
            $this->accommodation_type = $row['accommodation_type'];
            $this->photo = $row['photo'];
            $this->major_id = $row['major_id'];
            $this->academic_year_id = $row['academic_year_id'];
            $this->previous_school = $row['previous_school'];
            $this->registered_at = $row['registered_at'];
            return true;
        }
        return false;
    }

    // ອັບເດດຂໍ້ມູນນັກສຶກສາ
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET student_id = :student_id, first_name = :first_name, last_name = :last_name,
                      gender = :gender, dob = :dob, email = :email, phone = :phone,
                      village = :village, district = :district, province = :province,
                      accommodation_type = :accommodation_type, major_id = :major_id,
                      academic_year_id = :academic_year_id, previous_school = :previous_school";
        
        if (!empty($this->photo)) {
            $query .= ", photo = :photo";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':student_id', $this->student_id);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':gender', $this->gender);
        $stmt->bindParam(':dob', $this->dob);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':village', $this->village);
        $stmt->bindParam(':district', $this->district);
        $stmt->bindParam(':province', $this->province);
        $stmt->bindParam(':accommodation_type', $this->accommodation_type);
        $stmt->bindParam(':major_id', $this->major_id);
        $stmt->bindParam(':academic_year_id', $this->academic_year_id);
        $stmt->bindParam(':previous_school', $this->previous_school);
        $stmt->bindParam(':id', $this->id);
        
        if (!empty($this->photo)) {
            $stmt->bindParam(':photo', $this->photo);
        }
        
        return $stmt->execute();
    }

    // ລົບນັກສຶກສາ
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // ກວດສອບວ່າມີລະຫັດນັກສຶກສານີ້ແລ້ວບໍ
    public function studentIdExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE student_id = :student_id";
        if (!empty($this->id)) {
            $query .= " AND id != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $this->student_id);
        
        if (!empty($this->id)) {
            $stmt->bindParam(':id', $this->id);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // ສ້າງລະຫັດນັກສຶກສາໃໝ່
    public function generateStudentId() {
        $year = date('Y');
        $query = "SELECT student_id FROM " . $this->table_name . " 
                  WHERE student_id LIKE '{$year}%' 
                  ORDER BY student_id DESC LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $last_id = $row['student_id'];
            $number = (int)substr($last_id, 4) + 1;
        } else {
            $number = 1;
        }
        
        return $year . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    // ສະຖິຕິນັກສຶກສາຕາມສາຂາ
    public function getStatsByMajor() {
        $query = "SELECT m.name as major_name, COUNT(s.id) as student_count 
                  FROM majors m 
                  LEFT JOIN " . $this->table_name . " s ON m.id = s.major_id 
                  GROUP BY m.id, m.name 
                  ORDER BY student_count DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // ສະຖິຕິນັກສຶກສາຕາມປີການສຶກສາ
    public function getStatsByAcademicYear() {
        $query = "SELECT ay.year, COUNT(s.id) as student_count 
                  FROM academic_years ay 
                  LEFT JOIN " . $this->table_name . " s ON ay.id = s.academic_year_id 
                  GROUP BY ay.id, ay.year 
                  ORDER BY ay.year DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>

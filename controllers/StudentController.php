<?php
// ກວດສອບວ່າມີ session ແລ້ວຫຼືບໍ່
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Major.php';
require_once __DIR__ . '/../models/AcademicYear.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/AuthController.php';

class StudentController {
    private $db;
    private $student;
    private $major;
    private $academicYear;

    public function __construct() {
        AuthController::requireLogin();
        
        $database = new Database();
        $this->db = $database->getConnection();
        $this->student = new Student($this->db);
        $this->major = new Major($this->db);
        $this->academicYear = new AcademicYear($this->db);
    }

    // ສ້າງນັກສຶກສາໃໝ່
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // ອັບໂຫລດຮູບ
            $photo_name = '';
            if (!empty($_FILES['photo']['name'])) {
                $photo_name = $this->uploadPhoto($_FILES['photo']);
                if (!$photo_name) {
                    $_SESSION['error'] = 'ເກີດຂໍ້ຜິດພາດໃນການອັບໂຫລດຮູບ';
                    return false;
                }
            }

            // ກໍານົດຄ່າໃຫ້ Student
            $this->student->student_id = $_POST['student_id'] ?? '';
            $this->student->first_name = $_POST['first_name'] ?? '';
            $this->student->last_name = $_POST['last_name'] ?? '';
            $this->student->gender = $_POST['gender'] ?? '';
            $this->student->dob = $_POST['dob'] ?? '';
            $this->student->email = $_POST['email'] ?? '';
            $this->student->phone = $_POST['phone'] ?? '';
            $this->student->village = $_POST['village'] ?? '';
            $this->student->district = $_POST['district'] ?? '';
            $this->student->province = $_POST['province'] ?? '';
            $this->student->accommodation_type = $_POST['accommodation_type'] ?? 'ມີວັດຢູ່ແລ້ວ';
            $this->student->photo = $photo_name;
            $this->student->major_id = $_POST['major_id'] ?? null;
            $this->student->academic_year_id = $_POST['academic_year_id'] ?? null;
            $this->student->previous_school = $_POST['previous_school'] ?? '';

            // ຕິດຕາມການປ້ອນຂໍ້ມູນ
            if (empty($this->student->first_name) || empty($this->student->last_name)) {
                $_SESSION['error'] = 'ກະລຸນາປ້ອນຊື່ ແລະ ນາມສະກຸນ';
                return false;
            }

            // ກວດສອບລະຫັດນັກສຶກສາຊ້ໍາ
            if ($this->student->studentIdExists()) {
                $_SESSION['error'] = 'ລະຫັດນັກສຶກສານີ້ມີໃນລະບົບແລ້ວ';
                return false;
            }

            if ($this->student->create()) {
                $_SESSION['success'] = 'ເພີ່ມຂໍ້ມູນນັກສຶກສາສໍາເລັດ';
                return true;
            } else {
                $_SESSION['error'] = 'ເກີດຂໍ້ຜິດພາດໃນການເພີ່ມຂໍ້ມູນ';
                return false;
            }
        }
        return false;
    }

    // ອັບເດດຂໍ້ມູນນັກສຶກສາ
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->student->id = $id;
            
            // ດຶງຂໍ້ມູນເກົ່າ
            if (!$this->student->readOne()) {
                $_SESSION['error'] = 'ບໍ່ພົບຂໍ້ມູນນັກສຶກສາ';
                return false;
            }

            $old_photo = $this->student->photo;

            // ອັບໂຫລດຮູບໃໝ່ (ຖ້າມີ)
            $photo_name = $old_photo;
            if (!empty($_FILES['photo']['name'])) {
                $new_photo = $this->uploadPhoto($_FILES['photo']);
                if ($new_photo) {
                    $photo_name = $new_photo;
                    // ລົບຮູບເກົ່າ
                    if ($old_photo && file_exists(__DIR__ . '/../public/uploads/' . $old_photo)) {
                        unlink(__DIR__ . '/../public/uploads/' . $old_photo);
                    }
                }
            }

            // ກໍານົດຄ່າໃໝ່
            $this->student->student_id = $_POST['student_id'] ?? '';
            $this->student->first_name = $_POST['first_name'] ?? '';
            $this->student->last_name = $_POST['last_name'] ?? '';
            $this->student->gender = $_POST['gender'] ?? '';
            $this->student->dob = $_POST['dob'] ?? '';
            $this->student->email = $_POST['email'] ?? '';
            $this->student->phone = $_POST['phone'] ?? '';
            $this->student->village = $_POST['village'] ?? '';
            $this->student->district = $_POST['district'] ?? '';
            $this->student->province = $_POST['province'] ?? '';
            $this->student->accommodation_type = $_POST['accommodation_type'] ?? 'ມີວັດຢູ່ແລ້ວ';
            $this->student->photo = $photo_name;
            $this->student->major_id = $_POST['major_id'] ?? null;
            $this->student->academic_year_id = $_POST['academic_year_id'] ?? null;
            $this->student->previous_school = $_POST['previous_school'] ?? '';

            // ກວດສອບລະຫັດນັກສຶກສາຊ້ໍາ
            if ($this->student->studentIdExists()) {
                $_SESSION['error'] = 'ລະຫັດນັກສຶກສານີ້ມີໃນລະບົບແລ້ວ';
                return false;
            }

            if ($this->student->update()) {
                $_SESSION['success'] = 'ອັບເດດຂໍ້ມູນນັກສຶກສາສໍາເລັດ';
                return true;
            } else {
                $_SESSION['error'] = 'ເກີດຂໍ້ຜິດພາດໃນການອັບເດດຂໍ້ມູນ';
                return false;
            }
        }
        return false;
    }

    // ລົບນັກສຶກສາ
    public function delete($id) {
        $this->student->id = $id;
        
        // ດຶງຂໍ້ມູນເພື່ອລົບຮູບ
        if ($this->student->readOne()) {
            $photo = $this->student->photo;
            
            if ($this->student->delete()) {
                // ລົບຮູບ
                if ($photo && file_exists(__DIR__ . '/../public/uploads/' . $photo)) {
                    unlink(__DIR__ . '/../public/uploads/' . $photo);
                }
                $_SESSION['success'] = 'ລົບຂໍ້ມູນນັກສຶກສາສໍາເລັດ';
                return true;
            }
        }
        
        $_SESSION['error'] = 'ເກີດຂໍ້ຜິດພາດໃນການລົບຂໍ້ມູນ';
        return false;
    }

    // ອັບໂຫລດຮູບ
    private function uploadPhoto($file) {
        $upload_dir = __DIR__ . '/../public/uploads/';
        
        // ສ້າງໂຟນເດີຖ້າຍັງບໍ່ມີ
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed_types)) {
            $_SESSION['error'] = 'ອະນຸຍາດໄຟລ໌ຮູບເທົ່ານັ້ນ (JPEG, PNG, GIF)';
            return false;
        }

        if ($file['size'] > $max_size) {
            $_SESSION['error'] = 'ຂະໜາດໄຟລ໌ບໍ່ເກີນ 5MB';
            return false;
        }

        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            return $new_filename;
        }

        return false;
    }

    // ດຶງຂໍ້ມູນສາຂາທີ່ເປີດໃຊ້ງານ
    public function getActiveMajors() {
        return $this->major->readActive();
    }

    // ດຶງຂໍ້ມູນປີການສຶກສາທີ່ເປີດໃຊ້ງານ
    public function getActiveAcademicYears() {
        return $this->academicYear->readActive();
    }

    // ສ້າງລະຫັດນັກສຶກສາໃໝ່
    public function generateStudentId() {
        return $this->student->generateStudentId();
    }
}
?>

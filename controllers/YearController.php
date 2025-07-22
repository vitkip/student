<?php
// ກວດສອບວ່າມີ session ແລ້ວຫຼືບໍ່
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/AcademicYear.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/AuthController.php';

class YearController {
    private $db;
    private $academicYear;

    public function __construct() {
        AuthController::requireAdmin(); // ເປັນ Admin ເທົ່ານັ້ນ
        
        $database = new Database();
        $this->db = $database->getConnection();
        $this->academicYear = new AcademicYear($this->db);
    }

    // ສ້າງປີການສຶກສາໃໝ່
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->academicYear->year = trim($_POST['year'] ?? '');
            $this->academicYear->status = $_POST['status'] ?? 'active';

            // ກວດສອບການປ້ອນຂໍ້ມູນ
            if (empty($this->academicYear->year)) {
                $_SESSION['error'] = 'ກະລຸນາປ້ອນປີການສຶກສາ';
                return false;
            }

            // ກວດສອບຮູບແບບປີການສຶກສາ (YYYY-YYYY)
            if (!preg_match('/^\d{4}-\d{4}$/', $this->academicYear->year)) {
                $_SESSION['error'] = 'ຮູບແບບປີການສຶກສາບໍ່ຖືກຕ້ອງ (ຕົວຢ່າງ: 2025-2026)';
                return false;
            }

            // ກວດສອບປີການສຶກສາຊ້ໍາ
            if ($this->academicYear->yearExists()) {
                $_SESSION['error'] = 'ປີການສຶກສານີ້ມີໃນລະບົບແລ້ວ';
                return false;
            }

            if ($this->academicYear->create()) {
                $_SESSION['success'] = 'ເພີ່ມປີການສຶກສາສໍາເລັດ';
                return true;
            } else {
                $_SESSION['error'] = 'ເກີດຂໍ້ຜິດພາດໃນການເພີ່ມປີການສຶກສາ';
                return false;
            }
        }
        return false;
    }

    // ອັບເດດປີການສຶກສາ
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->academicYear->id = $id;
            $this->academicYear->year = trim($_POST['year'] ?? '');
            $this->academicYear->status = $_POST['status'] ?? 'active';

            // ກວດສອບການປ້ອນຂໍ້ມູນ
            if (empty($this->academicYear->year)) {
                $_SESSION['error'] = 'ກະລຸນາປ້ອນປີການສຶກສາ';
                return false;
            }

            // ກວດສອບຮູບແບບປີການສຶກສາ
            if (!preg_match('/^\d{4}-\d{4}$/', $this->academicYear->year)) {
                $_SESSION['error'] = 'ຮູບແບບປີການສຶກສາບໍ່ຖືກຕ້ອງ (ຕົວຢ່າງ: 2025-2026)';
                return false;
            }

            // ກວດສອບປີການສຶກສາຊ້ໍາ
            if ($this->academicYear->yearExists()) {
                $_SESSION['error'] = 'ປີການສຶກສານີ້ມີໃນລະບົບແລ້ວ';
                return false;
            }

            if ($this->academicYear->update()) {
                $_SESSION['success'] = 'ອັບເດດປີການສຶກສາສໍາເລັດ';
                return true;
            } else {
                $_SESSION['error'] = 'ເກີດຂໍ້ຜິດພາດໃນການອັບເດດປີການສຶກສາ';
                return false;
            }
        }
        return false;
    }

    // ລົບປີການສຶກສາ
    public function delete($id) {
        $this->academicYear->id = $id;
        
        if ($this->academicYear->delete()) {
            $_SESSION['success'] = 'ລົບປີການສຶກສາສໍາເລັດ';
            return true;
        } else {
            $_SESSION['error'] = 'ບໍ່ສາມາດລົບປີການສຶກສານີ້ໄດ້ເພາະມີນັກສຶກສາໃນປີການສຶກສານີ້';
            return false;
        }
    }

    // ດຶງຂໍ້ມູນປີການສຶກສາທັງໝົດ
    public function getAll() {
        return $this->academicYear->readAll();
    }

    // ດຶງຂໍ້ມູນປີການສຶກສາຕາມ ID
    public function getById($id) {
        $this->academicYear->id = $id;
        if ($this->academicYear->readOne()) {
            return $this->academicYear;
        }
        return null;
    }
}
?>

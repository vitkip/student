<?php
// ກວດສອບວ່າມີ session ແລ້ວຫຼືບໍ່
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/Major.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/AuthController.php';

class MajorController {
    private $db;
    private $major;

    public function __construct() {
        AuthController::requireAdmin(); // ເປັນ Admin ເທົ່ານັ້ນ
        
        $database = new Database();
        $this->db = $database->getConnection();
        $this->major = new Major($this->db);
    }

    // ສ້າງສາຂາວິຊາໃໝ່
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->major->name = trim($_POST['name'] ?? '');
            $this->major->code = trim($_POST['code'] ?? '');
            $this->major->description = trim($_POST['description'] ?? '');
            $this->major->status = $_POST['status'] ?? 'active';

            // ກວດສອບການປ້ອນຂໍ້ມູນ
            if (empty($this->major->name)) {
                $_SESSION['error'] = 'ກະລຸນາປ້ອນຊື່ສາຂາວິຊາ';
                return false;
            }

            if (empty($this->major->code)) {
                $_SESSION['error'] = 'ກະລຸນາປ້ອນລະຫັດສາຂາ';
                return false;
            }

            // ກວດສອບລະຫັດສາຂາຊ້ໍາ
            if ($this->major->codeExists()) {
                $_SESSION['error'] = 'ລະຫັດສາຂານີ້ມີໃນລະບົບແລ້ວ';
                return false;
            }

            if ($this->major->create()) {
                $_SESSION['success'] = 'ເພີ່ມສາຂາວິຊາສໍາເລັດ';
                return true;
            } else {
                $_SESSION['error'] = 'ເກີດຂໍ້ຜິດພາດໃນການເພີ່ມສາຂາວິຊາ';
                return false;
            }
        }
        return false;
    }

    // ອັບເດດສາຂາວິຊາ
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->major->id = $id;
            $this->major->name = trim($_POST['name'] ?? '');
            $this->major->code = trim($_POST['code'] ?? '');
            $this->major->description = trim($_POST['description'] ?? '');
            $this->major->status = $_POST['status'] ?? 'active';

            // ກວດສອບການປ້ອນຂໍ້ມູນ
            if (empty($this->major->name)) {
                $_SESSION['error'] = 'ກະລຸນາປ້ອນຊື່ສາຂາວິຊາ';
                return false;
            }

            if (empty($this->major->code)) {
                $_SESSION['error'] = 'ກະລຸນາປ້ອນລະຫັດສາຂາ';
                return false;
            }

            // ກວດສອບລະຫັດສາຂາຊ້ໍາ
            if ($this->major->codeExists()) {
                $_SESSION['error'] = 'ລະຫັດສາຂານີ້ມີໃນລະບົບແລ້ວ';
                return false;
            }

            if ($this->major->update()) {
                $_SESSION['success'] = 'ອັບເດດສາຂາວິຊາສໍາເລັດ';
                return true;
            } else {
                $_SESSION['error'] = 'ເກີດຂໍ້ຜິດພາດໃນການອັບເດດສາຂາວິຊາ';
                return false;
            }
        }
        return false;
    }

    // ລົບສາຂາວິຊາ
    public function delete($id) {
        $this->major->id = $id;
        
        if ($this->major->delete()) {
            $_SESSION['success'] = 'ລົບສາຂາວິຊາສໍາເລັດ';
            return true;
        } else {
            $_SESSION['error'] = 'ບໍ່ສາມາດລົບສາຂາວິຊານີ້ໄດ້ເພາະມີນັກສຶກສາໃນສາຂານີ້';
            return false;
        }
    }

    // ດຶງຂໍ້ມູນສາຂາວິຊາທັງໝົດ
    public function getAll() {
        return $this->major->readAll();
    }

    // ດຶງຂໍ້ມູນສາຂາວິຊາຕາມ ID
    public function getById($id) {
        $this->major->id = $id;
        if ($this->major->readOne()) {
            return $this->major;
        }
        return null;
    }
}
?>

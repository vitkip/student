<?php
// ກວດສອບວ່າມີ session ແລ້ວຫຼືບໍ່
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/database.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    // ເຂົ້າລະບົບ
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->user->username = $_POST['username'] ?? '';
            $this->user->password = $_POST['password'] ?? '';

            if (empty($this->user->username) || empty($this->user->password)) {
                $_SESSION['error'] = 'ກະລຸນາປ້ອນຊື່ຜູ້ໃຊ້ ແລະ ລະຫັດຜ່ານ';
                return false;
            }

            if ($this->user->login()) {
                $_SESSION['user_id'] = $this->user->id;
                $_SESSION['username'] = $this->user->username;
                $_SESSION['role'] = $this->user->role;
                $_SESSION['full_name'] = $this->user->full_name;
                $_SESSION['success'] = 'ເຂົ້າລະບົບສໍາເລັດ';
                return true;
            } else {
                $_SESSION['error'] = 'ຊື່ຜູ້ໃຊ້ ຫຼື ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ';
                return false;
            }
        }
        return false;
    }

    // ອອກຈາກລະບົບ
    public function logout() {
        session_destroy();
        header('Location: /students/index.php');
        exit();
    }

    // ກວດສອບວ່າໄດ້ເຂົ້າລະບົບແລ້ວບໍ
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // ກວດສອບສິດ Admin
    public static function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    // ບັງຄັບໃຫ້ເຂົ້າລະບົບ
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            $_SESSION['error'] = 'ກະລຸນາເຂົ້າລະບົບກ່ອນ';
            header('Location: /students/index.php');
            exit();
        }
    }

    // ບັງຄັບໃຫ້ເປັນ Admin
    public static function requireAdmin() {
        self::requireLogin();
        if (!self::isAdmin()) {
            $_SESSION['error'] = 'ທ່ານບໍ່ມີສິດເຂົ້າເຖິງໜ້ານີ້';
            header('Location: /students/views/dashboard.php');
            exit();
        }
    }
}
?>

<?php
// ກວດສອບວ່າມີ session ແລ້ວຫຼືບໍ່
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/StudentController.php';

// ກວດສອບ ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = 'ບໍ່ພົບລະຫັດນັກສຶກສາ';
    header('Location: /students/views/students/index.php');
    exit();
}

$student_id = (int)$_GET['id'];
$studentController = new StudentController();

// ລົບນັກສຶກສາ
if ($studentController->delete($student_id)) {
    header('Location: /students/views/students/index.php');
} else {
    header('Location: /students/views/students/index.php');
}
exit();
?>

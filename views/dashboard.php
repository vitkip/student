<?php
// ກວດສອບວ່າມີ session ແລ້ວຫຼືບໍ່
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Major.php';
require_once __DIR__ . '/../models/AcademicYear.php';
require_once __DIR__ . '/../config/database.php';

AuthController::requireLogin();

$title = 'ໜ້າຫຼັກ';

// ເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
$database = new Database();
$db = $database->getConnection();

// ສ້າງ instances
$student = new Student($db);
$major = new Major($db);
$academicYear = new AcademicYear($db);

// ດຶງສະຖິຕິ
$total_students = $student->countAll();
$stats_by_major = $student->getStatsByMajor();
$stats_by_year = $student->getStatsByAcademicYear();
$total_majors = count($major->readAll());
$total_years = count($academicYear->readAll());

include __DIR__ . '/includes/header.php';
?>

<!-- Dashboard Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">
        <i class="fas fa-tachometer-alt mr-3 text-blue-600"></i>ໜ້າຫຼັກ
    </h1>
    <p class="text-gray-600">ຍິນດີຕ້ອນຮັບ, <?php echo $_SESSION['full_name']; ?>!</p>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Students -->
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-users text-3xl text-blue-500"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">ນັກສຶກສາທັງໝົດ</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo number_format($total_students); ?></p>
            </div>
        </div>
    </div>

    <!-- Total Majors -->
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-book text-3xl text-green-500"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">ສາຂາວິຊາ</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo number_format($total_majors); ?></p>
            </div>
        </div>
    </div>

    <!-- Total Academic Years -->
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-calendar text-3xl text-yellow-500"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">ປີການສຶກສາ</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo number_format($total_years); ?></p>
            </div>
        </div>
    </div>

    <!-- Current Year -->
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-clock text-3xl text-purple-500"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">ປີປັດຈຸບັນ</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo date('Y'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Students by Major -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-chart-pie mr-2 text-blue-600"></i>ນັກສຶກສາຕາມສາຂາວິຊາ
        </h2>
        <div class="space-y-4">
            <?php foreach ($stats_by_major as $stat): ?>
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($stat['major_name']); ?></p>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                        <?php 
                        $percentage = $total_students > 0 ? ($stat['student_count'] / $total_students) * 100 : 0;
                        ?>
                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>
                <span class="ml-4 text-sm font-semibold text-gray-600"><?php echo number_format($stat['student_count']); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Students by Academic Year -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-chart-bar mr-2 text-green-600"></i>ນັກສຶກສາຕາມປີການສຶກສາ
        </h2>
        <div class="space-y-4">
            <?php foreach ($stats_by_year as $stat): ?>
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($stat['year']); ?></p>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                        <?php 
                        $percentage = $total_students > 0 ? ($stat['student_count'] / $total_students) * 100 : 0;
                        ?>
                        <div class="bg-green-600 h-2 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>
                <span class="ml-4 text-sm font-semibold text-gray-600"><?php echo number_format($stat['student_count']); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">
        <i class="fas fa-bolt mr-2 text-yellow-500"></i>ການດໍາເນີນງານດ່ວນ
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="/students/views/students/create.php" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200 group">
            <i class="fas fa-user-plus text-2xl text-blue-600 group-hover:scale-110 transition duration-200"></i>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">ເພີ່ມນັກສຶກສາ</p>
                <p class="text-xs text-gray-600">ລົງທະບຽນນັກສຶກສາໃໝ່</p>
            </div>
        </a>

        <a href="students/" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition duration-200 group">
            <i class="fas fa-list text-2xl text-green-600 group-hover:scale-110 transition duration-200"></i>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">ລາຍຊື່ນັກສຶກສາ</p>
                <p class="text-xs text-gray-600">ເບິ່ງລາຍຊື່ທັງໝົດ</p>
            </div>
        </a>

        <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="reports/" class="flex items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition duration-200 group">
            <i class="fas fa-chart-bar text-2xl text-indigo-600 group-hover:scale-110 transition duration-200"></i>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">ລາຍງານ</p>
                <p class="text-xs text-gray-600">ສົ່ງອອກຂໍ້ມູນ</p>
            </div>
        </a>

        <a href="majors/" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition duration-200 group">
            <i class="fas fa-book text-2xl text-yellow-600 group-hover:scale-110 transition duration-200"></i>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">ຈັດການສາຂາ</p>
                <p class="text-xs text-gray-600">ເພີ່ມ/ແກ້ໄຂສາຂາວິຊາ</p>
            </div>
        </a>

        <a href="years/" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition duration-200 group">
            <i class="fas fa-calendar text-2xl text-purple-600 group-hover:scale-110 transition duration-200"></i>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">ຈັດການປີການສຶກສາ</p>
                <p class="text-xs text-gray-600">ເພີ່ມ/ແກ້ໄຂປີການສຶກສາ</p>
            </div>
        </a>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

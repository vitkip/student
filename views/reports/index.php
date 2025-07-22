<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Student.php';
require_once __DIR__ . '/../../models/Major.php';
require_once __DIR__ . '/../../models/AcademicYear.php';
require_once __DIR__ . '/../../config/database.php';

AuthController::requireLogin();

// ตรวจสอบสิทธิ์ admin
if ($_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'ທ່ານບໍ່ມີສິດໃນການເຂົ້າເຖິງໜ້ານີ້';
    header('Location: /students/views/dashboard.php');
    exit();
}

$title = 'ລາຍງານແລະການສົ່ງອອກຂໍ້ມູນ';

// เชื่อมต่อฐานข้อมูล
$database = new Database();
$db = $database->getConnection();

// ดึงข้อมูลสำหรับ filter
$major = new Major($db);
$majors = $major->readAll();

$year = new AcademicYear($db);
$academic_years = $year->readAll();

// ดึงสถิติ
$student = new Student($db);
$total_students = $student->countAll();
$stats_by_major = $student->getStatsByMajor();
$stats_by_year = $student->getStatsByAcademicYear();

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="fas fa-chart-bar mr-3 text-blue-600"></i>ລາຍງານແລະການສົ່ງອອກຂໍ້ມູນ
        </h1>
        <p class="text-gray-600">ສ້າງລາຍງານແລະສົ່ງອອກຂໍ້ມູນເປັນ Excel ຫຼື PDF</p>
    </div>
    <div class="mt-4 md:mt-0">
        <a href="/students/views/dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition duration-200 inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>ກັບໄປໜ້າຫຼັກ
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">ນັກສຶກສາທັງໝົດ</h3>
                <p class="text-3xl font-bold text-blue-600"><?php echo number_format($total_students); ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-book text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">ສາຂາວິຊາ</h3>
                <p class="text-3xl font-bold text-green-600"><?php echo count($majors); ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <i class="fas fa-calendar text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">ປີການສຶກສາ</h3>
                <p class="text-3xl font-bold text-purple-600"><?php echo count($academic_years); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Export Forms -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Student Report Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-users mr-2 text-blue-600"></i>ລາຍງານນັກສຶກສາ
        </h2>
        
        <form action="/students/controllers/ExportController.php" method="GET" target="_blank">
            <input type="hidden" name="type" value="students">
            
            <div class="space-y-4">
                <div>
                    <label for="student_search" class="block text-sm font-medium text-gray-700 mb-2">ຄົ້ນຫາ (ທາງເລືອກ)</label>
                    <input type="text" 
                           id="student_search" 
                           name="search" 
                           placeholder="ຄົ້ນຫາດ້ວຍຊື່, ລະຫັດ, ອີເມລ"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="major_filter" class="block text-sm font-medium text-gray-700 mb-2">ສາຂາວິຊາ (ທາງເລືອກ)</label>
                    <select id="major_filter" 
                            name="major_id" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">ທຸກສາຂາວິຊາ</option>
                        <?php foreach ($majors as $major_item): ?>
                        <option value="<?php echo $major_item['id']; ?>"><?php echo htmlspecialchars($major_item['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="year_filter" class="block text-sm font-medium text-gray-700 mb-2">ປີການສຶກສາ (ທາງເລືອກ)</label>
                    <select id="year_filter" 
                            name="academic_year_id" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">ທຸກປີການສຶກສາ</option>
                        <?php foreach ($academic_years as $year_item): ?>
                        <option value="<?php echo $year_item['id']; ?>"><?php echo htmlspecialchars($year_item['year']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex space-x-3">
                    <button type="submit" 
                            name="format" 
                            value="excel" 
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-file-excel mr-2"></i>ສົ່ງອອກ Excel
                    </button>
                    <button type="submit" 
                            name="format" 
                            value="pdf" 
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-file-pdf mr-2"></i>ສົ່ງອອກ PDF
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Other Reports Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-chart-pie mr-2 text-green-600"></i>ລາຍງານອື່ນໆ
        </h2>
        
        <div class="space-y-4">
            <!-- Majors Report -->
            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium text-gray-900 mb-2">ລາຍງານສາຂາວິຊາ</h3>
                <div class="flex space-x-2">
                    <a href="/students/controllers/ExportController.php?type=majors&format=excel" 
                       target="_blank"
                       class="flex-1 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-center text-sm transition duration-200">
                        <i class="fas fa-file-excel mr-1"></i>Excel
                    </a>
                    <a href="/students/controllers/ExportController.php?type=majors&format=pdf" 
                       target="_blank"
                       class="flex-1 bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded text-center text-sm transition duration-200">
                        <i class="fas fa-file-pdf mr-1"></i>PDF
                    </a>
                </div>
            </div>
            
            <!-- Academic Years Report -->
            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium text-gray-900 mb-2">ລາຍງານປີການສຶກສາ</h3>
                <div class="flex space-x-2">
                    <a href="/students/controllers/ExportController.php?type=years&format=excel" 
                       target="_blank"
                       class="flex-1 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-center text-sm transition duration-200">
                        <i class="fas fa-file-excel mr-1"></i>Excel
                    </a>
                    <a href="/students/controllers/ExportController.php?type=years&format=pdf" 
                       target="_blank"
                       class="flex-1 bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded text-center text-sm transition duration-200">
                        <i class="fas fa-file-pdf mr-1"></i>PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Students by Major -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-chart-bar mr-2 text-blue-600"></i>ນັກສຶກສາຕາມສາຂາວິຊາ
        </h2>
        <div class="space-y-3">
            <?php foreach ($stats_by_major as $stat): ?>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span class="font-medium text-gray-900"><?php echo htmlspecialchars($stat['major_name']); ?></span>
                <div class="flex items-center space-x-2">
                    <div class="w-20 bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $total_students > 0 ? ($stat['student_count'] / $total_students * 100) : 0; ?>%"></div>
                    </div>
                    <span class="text-sm font-semibold text-gray-700"><?php echo $stat['student_count']; ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Students by Academic Year -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-chart-pie mr-2 text-green-600"></i>ນັກສຶກສາຕາມປີການສຶກສາ
        </h2>
        <div class="space-y-3">
            <?php foreach ($stats_by_year as $stat): ?>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span class="font-medium text-gray-900"><?php echo htmlspecialchars($stat['year']); ?></span>
                <div class="flex items-center space-x-2">
                    <div class="w-20 bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: <?php echo $total_students > 0 ? ($stat['student_count'] / $total_students * 100) : 0; ?>%"></div>
                    </div>
                    <span class="text-sm font-semibold text-gray-700"><?php echo $stat['student_count']; ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
// เพิ่ม JavaScript สำหรับปรับปรุง UX
document.addEventListener('DOMContentLoaded', function() {
    // แสดงการโหลดเมื่อส่งออกข้อมูล
    const exportButtons = document.querySelectorAll('button[name="format"], a[href*="ExportController.php"]');
    
    exportButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // แสดงข้อความโหลด
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>ກໍາລັງສ້າງລາຍງານ...';
            this.disabled = true;
            
            // คืนค่าปุ่มหลังจาก 3 วินาที
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            }, 3000);
        });
    });
});
</script>

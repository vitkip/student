<?php
// ກວດສອບວ່າມີ session ແລ້ວຫຼືບໍ່
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Student.php';
require_once __DIR__ . '/../../models/Major.php';
require_once __DIR__ . '/../../models/AcademicYear.php';
require_once __DIR__ . '/../../config/database.php';

AuthController::requireLogin();

$title = 'ລາຍຊື່ນັກສຶກສາ';

// ການຈັດການ pagination ແລະ search
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// ເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
$database = new Database();
$db = $database->getConnection();
$student = new Student($db);

// ດຶງຂໍ້ມູນນັກສຶກສາ
$students = $student->readAll($page, $records_per_page, $search);
$total_records = $student->countAll($search);
$total_pages = ceil($total_records / $records_per_page);

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="fas fa-users mr-3 text-blue-600"></i>ລາຍຊື່ນັກສຶກສາ
        </h1>
        <p class="text-gray-600">ຈັດການຂໍ້ມູນນັກສຶກສາທັງໝົດ</p>
    </div>
    <div class="mt-4 md:mt-0 flex flex-col sm:flex-row gap-3">
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <div class="relative">
            <button id="export-dropdown-button" 
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition duration-200 inline-flex items-center">
                <i class="fas fa-download mr-2"></i>ສົ່ງອອກ
                <i class="fas fa-chevron-down ml-2"></i>
            </button>
            <div id="export-dropdown" 
                 class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                <div class="py-2">
                    <a href="/students/controllers/ExportController.php?type=students&format=excel<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       target="_blank"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-file-excel mr-2 text-green-600"></i>ສົ່ງອອກ Excel
                    </a>
                    <a href="/students/controllers/ExportController.php?type=students&format=pdf<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       target="_blank"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-file-pdf mr-2 text-red-600"></i>ສົ່ງອອກ PDF
                    </a>
                    <div class="border-t border-gray-200 my-1"></div>
                    <a href="/students/views/reports/" 
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-chart-bar mr-2 text-blue-600"></i>ລາຍງານລະອຽດ
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <a href="create.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-200 inline-flex items-center">
            <i class="fas fa-plus mr-2"></i>ເພີ່ມນັກສຶກສາໃໝ່
        </a>
    </div>
</div>

<!-- Search and Filter -->
<div class="bg-white rounded-lg shadow-md p-6 mb-8">
    <form method="GET" class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">ຄົ້ນຫາ</label>
            <input type="text" 
                   id="search" 
                   name="search" 
                   value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="ຄົ້ນຫາດ້ວຍຊື່, ນາມສະກຸນ, ລະຫັດນັກສຶກສາ ຫຼື ອີເມລ"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex items-end space-x-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                <i class="fas fa-search mr-2"></i>ຄົ້ນຫາ
            </button>
            <?php if (!empty($search)): ?>
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition duration-200">
                <i class="fas fa-times mr-2"></i>ຢຸດຄົ້ນຫາ
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Students Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">
            ລາຍຊື່ນັກສຶກສາ (<?php echo number_format($total_records); ?> ຄົນ)
        </h2>
    </div>
    
    <?php if (empty($students)): ?>
    <div class="p-8 text-center">
        <i class="fas fa-user-slash text-4xl text-gray-400 mb-4"></i>
        <p class="text-gray-500 text-lg">ບໍ່ພົບຂໍ້ມູນນັກສຶກສາ</p>
        <?php if (!empty($search)): ?>
        <p class="text-gray-400 mt-2">ລອງຄົ້ນຫາດ້ວຍຄໍາສັບອື່ນ</p>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ຮູບ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ລະຫັດ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ເພດ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ຊື່ ແລະ ນາມສະກຸນ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ສາຂາວິຊາ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ປີການສຶກສາ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ການດໍາເນີນການ</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($students as $student): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if (!empty($student['photo'])): ?>
                        <img src="/students/public/uploads/<?php echo htmlspecialchars($student['photo']); ?>" 
                             alt="Photo" class="h-12 w-12 rounded-full object-cover">
                        <?php else: ?>
                        <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                            <i class="fas fa-user text-gray-500"></i>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <?php echo htmlspecialchars($student['student_id']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php echo htmlspecialchars($student['gender']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                        </div>
                        <div class="text-sm text-gray-500">
                            <?php echo htmlspecialchars($student['email']); ?>
                        </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php echo htmlspecialchars($student['major_name'] ?? '-'); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php echo htmlspecialchars($student['year'] ?? '-'); ?>
                    </td>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <a href="view.php?id=<?php echo $student['id']; ?>" 
                           class="text-blue-600 hover:text-blue-900" title="ເບິ່ງລາຍລະອຽດ">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="edit.php?id=<?php echo $student['id']; ?>" 
                           class="text-indigo-600 hover:text-indigo-900" title="ແກ້ໄຂ">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="/students/controllers/ExportController.php?action=student_card&id=<?php echo $student['id']; ?>" 
                           class="text-green-600 hover:text-green-900" title="ພິມບັດນັກສຶກສາ"
                           target="_blank">
                            <i class="fas fa-id-card"></i>
                        </a>
                        <a href="delete.php?id=<?php echo $student['id']; ?>" 
                           class="text-red-600 hover:text-red-900" title="ລົບ"
                           onclick="return confirmDelete('ທ່ານຕ້ອງການລົບນັກສຶກສາ <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?> ແທ້ບໍ?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden">
            <?php if ($page > 1): ?>
            <a href="?page=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
               class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                ກ່ອນໜ້ານີ້
            </a>
            <?php endif; ?>
            <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
               class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                ຕໍ່ໄປ
            </a>
            <?php endif; ?>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    ສະແດງ <span class="font-medium"><?php echo (($page - 1) * $records_per_page) + 1; ?></span>
                    ເຖິງ <span class="font-medium"><?php echo min($page * $records_per_page, $total_records); ?></span>
                    ຂອງ <span class="font-medium"><?php echo number_format($total_records); ?></span> ລາຍການ
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?php echo $i == $page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
// Dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const dropdownButton = document.getElementById('export-dropdown-button');
    const dropdown = document.getElementById('export-dropdown');
    
    if (dropdownButton && dropdown) {
        dropdownButton.addEventListener('click', function(e) {
            e.preventDefault();
            dropdown.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdownButton.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }
});

// Confirm delete function
function confirmDelete(message) {
    return confirm(message);
}
</script>

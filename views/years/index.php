<?php
// ກວດສອບວ່າມີ session ແລ້ວຫຼືບໍ່
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/YearController.php';

$title = 'ຈັດການປີການສຶກສາ';

$yearController = new YearController();

// ຈັດການການສົ່ງຟອມ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $yearController->create();
                break;
            case 'update':
                if (isset($_POST['id'])) {
                    $yearController->update($_POST['id']);
                }
                break;
        }
    }
    header('Location: /students/views/years/index.php');
    exit();
}

// ຈັດການການລົບ
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $yearController->delete($_GET['delete']);
    header('Location: /students/views/years/index.php');
    exit();
}

// ດຶງຂໍ້ມູນປີການສຶກສາທັງໝົດ
$academic_years = $yearController->getAll();

// ດຶງຂໍ້ມູນສໍາລັບແກ້ໄຂ (ຖ້າມີ)
$edit_year = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_year = $yearController->getById($_GET['edit']);
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="fas fa-calendar mr-3 text-blue-600"></i>ຈັດການປີການສຶກສາ
        </h1>
        <p class="text-gray-600">ເພີ່ມ, ແກ້ໄຂ ແລະ ຈັດການປີການສຶກສາທັງໝົດ</p>
    </div>
    <div class="mt-4 md:mt-0">
        <a href="../dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition duration-200 inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>ກັບໜ້າຫຼັກ
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Form Section -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <?php echo $edit_year ? 'ແກ້ໄຂປີການສຶກສາ' : 'ເພີ່ມປີການສຶກສາໃໝ່'; ?>
            </h2>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="<?php echo $edit_year ? 'update' : 'create'; ?>">
                <?php if ($edit_year): ?>
                <input type="hidden" name="id" value="<?php echo $edit_year->id; ?>">
                <?php endif; ?>

                <!-- Academic Year -->
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-2">
                        ປີການສຶກສາ <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="year" 
                           name="year" 
                           required
                           pattern="^\d{4}-\d{4}$"
                           value="<?php echo htmlspecialchars($_POST['year'] ?? ($edit_year ? $edit_year->year : '')); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="ຕົວຢ່າງ: 2025-2026">
                    <p class="text-xs text-gray-500 mt-1">ຮູບແບບ: YYYY-YYYY</p>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        ສະຖານະ
                    </label>
                    <select id="status" 
                            name="status" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php 
                        $selected_status = $_POST['status'] ?? ($edit_year ? $edit_year->status : 'active');
                        ?>
                        <option value="active" <?php echo $selected_status === 'active' ? 'selected' : ''; ?>>ເປີດໃຊ້ງານ</option>
                        <option value="inactive" <?php echo $selected_status === 'inactive' ? 'selected' : ''; ?>>ປິດໃຊ້ງານ</option>
                    </select>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center space-x-4">
                    <?php if ($edit_year): ?>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition duration-200">
                        <i class="fas fa-save mr-2"></i>ອັບເດດ
                    </button>
                    <a href="index.php" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition duration-200 text-center">
                        ຍົກເລີກ
                    </a>
                    <?php else: ?>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition duration-200">
                        <i class="fas fa-plus mr-2"></i>ເພີ່ມປີການສຶກສາ
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Quick Add Current Year -->
        <?php if (!$edit_year): ?>
        <div class="bg-blue-50 rounded-lg p-4 mt-6">
            <h3 class="text-sm font-medium text-blue-900 mb-2">ເພີ່ມດ່ວນ</h3>
            <div class="space-y-2">
                <?php
                $current_year = date('Y');
                $next_year = $current_year + 1;
                $years_to_add = [
                    "$current_year-$next_year",
                    ($current_year + 1) . '-' . ($current_year + 2),
                    ($current_year + 2) . '-' . ($current_year + 3)
                ];
                ?>
                <?php foreach ($years_to_add as $year_suggestion): ?>
                <button type="button" 
                        onclick="document.getElementById('year').value='<?php echo $year_suggestion; ?>'"
                        class="block w-full text-left px-3 py-2 text-sm bg-white border border-blue-200 rounded hover:bg-blue-100 transition duration-200">
                    <?php echo $year_suggestion; ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- List Section -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    ລາຍຊື່ປີການສຶກສາ (<?php echo count($academic_years); ?> ປີ)
                </h2>
            </div>

            <?php if (empty($academic_years)): ?>
            <div class="p-8 text-center">
                <i class="fas fa-calendar text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500 text-lg">ຍັງບໍ່ມີປີການສຶກສາໃນລະບົບ</p>
                <p class="text-gray-400 mt-2">ເພີ່ມປີການສຶກສາໃໝ່ໂດຍໃຊ້ຟອມດ້ານຊ້າຍ</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ປີການສຶກສາ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ສະຖານະ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ການດໍາເນີນການ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($academic_years as $year): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($year['year']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $year['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $year['status'] === 'active' ? 'ເປີດໃຊ້ງານ' : 'ປິດໃຊ້ງານ'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="?edit=<?php echo $year['id']; ?>" 
                                   class="text-indigo-600 hover:text-indigo-900" title="ແກ້ໄຂ">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $year['id']; ?>" 
                                   class="text-red-600 hover:text-red-900" title="ລົບ"
                                   onclick="return confirmDelete('ທ່ານຕ້ອງການລົບປີການສຶກສາ <?php echo htmlspecialchars($year['year']); ?> ແທ້ບໍ?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

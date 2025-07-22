<?php
// ກວດສອບວ່າມີ session ແລ້ວຫຼືບໍ່
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/MajorController.php';

$title = 'ຈັດການສາຂາວິຊາ';

$majorController = new MajorController();

// ຈັດການການສົ່ງຟອມ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $majorController->create();
                break;
            case 'update':
                if (isset($_POST['id'])) {
                    $majorController->update($_POST['id']);
                }
                break;
        }
    }
    header('Location: /students/views/majors/index.php');
    exit();
}

// ຈັດການການລົບ
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $majorController->delete($_GET['delete']);
    header('Location: /students/views/majors/index.php');
    exit();
}

// ດຶງຂໍ້ມູນສາຂາວິຊາທັງໝົດ
$majors = $majorController->getAll();

// ດຶງຂໍ້ມູນສໍາລັບແກ້ໄຂ (ຖ້າມີ)
$edit_major = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_major = $majorController->getById($_GET['edit']);
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="fas fa-book mr-3 text-blue-600"></i>ຈັດການສາຂາວິຊາ
        </h1>
        <p class="text-gray-600">ເພີ່ມ, ແກ້ໄຂ ແລະ ຈັດການສາຂາວິຊາທັງໝົດ</p>
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
                <?php echo $edit_major ? 'ແກ້ໄຂສາຂາວິຊາ' : 'ເພີ່ມສາຂາວິຊາໃໝ່'; ?>
            </h2>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="<?php echo $edit_major ? 'update' : 'create'; ?>">
                <?php if ($edit_major): ?>
                <input type="hidden" name="id" value="<?php echo $edit_major->id; ?>">
                <?php endif; ?>

                <!-- Major Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        ຊື່ສາຂາວິຊາ <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           required
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ($edit_major ? $edit_major->name : '')); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="ປ້ອນຊື່ສາຂາວິຊາ">
                </div>

                <!-- Major Code -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                        ລະຫັດສາຂາ <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="code" 
                           name="code" 
                           required
                           value="<?php echo htmlspecialchars($_POST['code'] ?? ($edit_major ? $edit_major->code : '')); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="ປ້ອນລະຫັດສາຂາ">
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        ຄໍາອະທິບາຍ
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="ປ້ອນຄໍາອະທິບາຍສາຂາວິຊາ"><?php echo htmlspecialchars($_POST['description'] ?? ($edit_major ? $edit_major->description : '')); ?></textarea>
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
                        $selected_status = $_POST['status'] ?? ($edit_major ? $edit_major->status : 'active');
                        ?>
                        <option value="active" <?php echo $selected_status === 'active' ? 'selected' : ''; ?>>ເປີດໃຊ້ງານ</option>
                        <option value="inactive" <?php echo $selected_status === 'inactive' ? 'selected' : ''; ?>>ປິດໃຊ້ງານ</option>
                    </select>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center space-x-4">
                    <?php if ($edit_major): ?>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition duration-200">
                        <i class="fas fa-save mr-2"></i>ອັບເດດ
                    </button>
                    <a href="index.php" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition duration-200 text-center">
                        ຍົກເລີກ
                    </a>
                    <?php else: ?>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition duration-200">
                        <i class="fas fa-plus mr-2"></i>ເພີ່ມສາຂາວິຊາ
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- List Section -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    ລາຍຊື່ສາຂາວິຊາ (<?php echo count($majors); ?> ສາຂາ)
                </h2>
            </div>

            <?php if (empty($majors)): ?>
            <div class="p-8 text-center">
                <i class="fas fa-book text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500 text-lg">ຍັງບໍ່ມີສາຂາວິຊາໃນລະບົບ</p>
                <p class="text-gray-400 mt-2">ເພີ່ມສາຂາວິຊາໃໝ່ໂດຍໃຊ້ຟອມດ້ານຊ້າຍ</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ລະຫັດ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ຊື່ສາຂາວິຊາ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ສະຖານະ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ການດໍາເນີນການ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($majors as $major): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($major['code']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($major['name']); ?>
                                </div>
                                <?php if (!empty($major['description'])): ?>
                                <div class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($major['description']); ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $major['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $major['status'] === 'active' ? 'ເປີດໃຊ້ງານ' : 'ປິດໃຊ້ງານ'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="?edit=<?php echo $major['id']; ?>" 
                                   class="text-indigo-600 hover:text-indigo-900" title="ແກ້ໄຂ">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $major['id']; ?>" 
                                   class="text-red-600 hover:text-red-900" title="ລົບ"
                                   onclick="return confirmDelete('ທ່ານຕ້ອງການລົບສາຂາວິຊາ <?php echo htmlspecialchars($major['name']); ?> ແທ້ບໍ?')">
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

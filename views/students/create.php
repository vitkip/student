<?php
// ກວດສອບວ່າມີ session ແລ້ວຫຼືບໍ່
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/StudentController.php';

$title = 'ເພີ່ມນັກສຶກສາໃໝ່';

// ສ້າງ StudentController instance
$studentController = new StudentController();

// ຈັດການການສົ່ງຟອມ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($studentController->create()) {
        header('Location: /students/views/students/index.php');
        exit();
    }
}

// ດຶງຂໍ້ມູນສໍາລັບ dropdown
$majors = $studentController->getActiveMajors();
$academic_years = $studentController->getActiveAcademicYears();
$new_student_id = $studentController->generateStudentId();

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="fas fa-user-plus mr-3 text-blue-600"></i>ເພີ່ມນັກສຶກສາໃໝ່
        </h1>
        <p class="text-gray-600">ປ້ອນຂໍ້ມູນນັກສຶກສາໃໝ່ເຂົ້າໃນລະບົບ</p>
    </div>
    <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition duration-200 inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i>ກັບຄືນ
    </a>
</div>

<!-- Student Form -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
        <!-- Basic Information -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-user mr-2 text-blue-600"></i>ຂໍ້ມູນທົ່ວໄປ
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Student ID -->
                <div>
                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">
                        ລະຫັດນັກສຶກສາ <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="student_id" 
                           name="student_id" 
                           required
                           value="<?php echo htmlspecialchars($_POST['student_id'] ?? $new_student_id); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="ລະຫັດນັກສຶກສາ">
                </div>

                <!-- Photo -->
                <div>
                    <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">
                        ຮູບນັກສຶກສາ
                    </label>
                    <input type="file" 
                           id="photo" 
                           name="photo" 
                           accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">ອະນຸຍາດໄຟລ໌: JPG, PNG, GIF (ຂະໜາດບໍ່ເກີນ 5MB)</p>
                </div>

                <!-- First Name -->
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                        ຊື່ <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="first_name" 
                           name="first_name" 
                           required
                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="ປ້ອນຊື່">
                </div>

                <!-- Last Name -->
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                        ນາມສະກຸນ <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="last_name" 
                           name="last_name" 
                           required
                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="ປ້ອນນາມສະກຸນ">
                </div>

                <!-- Gender -->
                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                        ເພດ <span class="text-red-500">*</span>
                    </label>
                    <select id="gender" 
                            name="gender" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">ເລືອກເພດ</option>
                        <option value="ພຣະ" <?php echo ($_POST['gender'] ?? '') === 'ພຣະ' ? 'selected' : ''; ?>>ພຣະ</option>
                        <option value="ສ.ນ" <?php echo ($_POST['gender'] ?? '') === 'ສ.ນ' ? 'selected' : ''; ?>>ສ.ນ</option>
                        <option value="ຊາຍ" <?php echo ($_POST['gender'] ?? '') === 'ຊາຍ' ? 'selected' : ''; ?>>ຊາຍ</option>
                        <option value="ຍິງ" <?php echo ($_POST['gender'] ?? '') === 'ຍິງ' ? 'selected' : ''; ?>>ຍິງ</option>
                        <option value="ອຶ່ນໆ" <?php echo ($_POST['gender'] ?? '') === 'ອຶ່ນໆ' ? 'selected' : ''; ?>>ອຶ່ນໆ</option>
                    </select>
                </div>

                <!-- Date of Birth -->
                <div>
                    <label for="dob" class="block text-sm font-medium text-gray-700 mb-2">
                        ວັນເກີດ <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="dob" 
                           name="dob" 
                           required
                           value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-address-book mr-2 text-green-600"></i>ຂໍ້ມູນການຕິດຕໍ່
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        ອີເມລ
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="ປ້ອນອີເມລ">
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        ເບີໂທ
                    </label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="ປ້ອນເບີໂທ">
                </div>

                <!-- Village -->
                <div>
                    <label for="village" class="block text-sm font-medium text-gray-700 mb-2">
                        ບ້ານ
                    </label>
                    <input type="text" 
                           id="village" 
                           name="village" 
                           value="<?php echo htmlspecialchars($_POST['village'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="ປ້ອນຊື່ບ້ານ">
                </div>

                <!-- District -->
                <div>
                    <label for="district" class="block text-sm font-medium text-gray-700 mb-2">
                        ອໍາເພອ
                    </label>
                    <input type="text" 
                           id="district" 
                           name="district" 
                           value="<?php echo htmlspecialchars($_POST['district'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="ປ້ອນຊື່ອໍາເພອ">
                </div>

                <!-- Province -->
                <div>
                    <label for="province" class="block text-sm font-medium text-gray-700 mb-2">
                        ແຂວງ
                    </label>
                    <input type="text" 
                           id="province" 
                           name="province" 
                           value="<?php echo htmlspecialchars($_POST['province'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="ປ້ອນຊື່ແຂວງ">
                </div>

                <!-- Accommodation Type -->
                <div>
                    <label for="accommodation_type" class="block text-sm font-medium text-gray-700 mb-2">
                        ປະເພດທີ່ພັກ
                    </label>
                    <select id="accommodation_type" 
                            name="accommodation_type" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="ມີວັດຢູ່ແລ້ວ" <?php echo ($_POST['accommodation_type'] ?? 'ມີວັດຢູ່ແລ້ວ') === 'ມີວັດຢູ່ແລ້ວ' ? 'selected' : ''; ?>>ມີວັດຢູ່ແລ້ວ</option>
                        <option value="ຫາວັດໃຫ້" <?php echo ($_POST['accommodation_type'] ?? '') === 'ຫາວັດໃຫ້' ? 'selected' : ''; ?>>ຫາວັດໃຫ້</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Academic Information -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-graduation-cap mr-2 text-purple-600"></i>ຂໍ້ມູນການສຶກສາ
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Major -->
                <div>
                    <label for="major_id" class="block text-sm font-medium text-gray-700 mb-2">
                        ສາຂາວິຊາ
                    </label>
                    <select id="major_id" 
                            name="major_id" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">ເລືອກສາຂາວິຊາ</option>
                        <?php foreach ($majors as $major): ?>
                        <option value="<?php echo $major['id']; ?>" <?php echo ($_POST['major_id'] ?? '') == $major['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($major['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Academic Year -->
                <div>
                    <label for="academic_year_id" class="block text-sm font-medium text-gray-700 mb-2">
                        ປີການສຶກສາ
                    </label>
                    <select id="academic_year_id" 
                            name="academic_year_id" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">ເລືອກປີການສຶກສາ</option>
                        <?php foreach ($academic_years as $year): ?>
                        <option value="<?php echo $year['id']; ?>" <?php echo ($_POST['academic_year_id'] ?? '') == $year['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($year['year']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Previous School -->
                <div class="md:col-span-2">
                    <label for="previous_school" class="block text-sm font-medium text-gray-700 mb-2">
                        ໂຮງຮຽນເກົ່າ
                    </label>
                    <input type="text" 
                           id="previous_school" 
                           name="previous_school" 
                           value="<?php echo htmlspecialchars($_POST['previous_school'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="ປ້ອນຊື່ໂຮງຮຽນເກົ່າ">
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition duration-200">
                ຍົກເລີກ
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                <i class="fas fa-save mr-2"></i>ບັນທຶກຂໍ້ມູນ
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

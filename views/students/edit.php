<?php
// ກວດສອບວ່າມີ session ແລ້ວຫຼືບໍ່
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/StudentController.php';
require_once __DIR__ . '/../../models/Student.php';
require_once __DIR__ . '/../../config/database.php';

$title = 'ແກ້ໄຂຂໍ້ມູນນັກສຶກສາ';

// ກວດສອບ ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = 'ບໍ່ພົບລະຫັດນັກສຶກສາ';
    header('Location: /students/views/students/index.php');
    exit();
}

$student_id = (int)$_GET['id'];
$studentController = new StudentController();

// ດຶງຂໍ້ມູນນັກສຶກສາ
$database = new Database();
$db = $database->getConnection();
$student = new Student($db);
$student->id = $student_id;

if (!$student->readOne()) {
    $_SESSION['error'] = 'ບໍ່ພົບຂໍ້ມູນນັກສຶກສາ';
    header('Location: /students/views/students/index.php');
    exit();
}

// ຈັດການການອັບເດດ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($studentController->update($student_id)) {
        header('Location: /students/views/students/index.php');
        exit();
    }
}

// ດຶງຂໍ້ມູນສໍາລັບ dropdown
$majors = $studentController->getActiveMajors();
$academic_years = $studentController->getActiveAcademicYears();

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="fas fa-user-edit mr-3 text-blue-600"></i>ແກ້ໄຂຂໍ້ມູນນັກສຶກສາ
        </h1>
        <p class="text-gray-600">ແກ້ໄຂຂໍ້ມູນຂອງ <?php echo htmlspecialchars($student->first_name . ' ' . $student->last_name); ?></p>
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
                           value="<?php echo htmlspecialchars($_POST['student_id'] ?? $student->student_id); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="ລະຫັດນັກສຶກສາ">
                </div>

                <!-- Current Photo Preview -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        ຮູບປັດຈຸບັນ
                    </label>
                    <?php if (!empty($student->photo)): ?>
                    <div class="mb-2">
                        <img src="/students/public/uploads/<?php echo htmlspecialchars($student->photo); ?>" 
                             alt="Current Photo" class="h-20 w-20 rounded-lg object-cover">
                    </div>
                    <?php else: ?>
                    <div class="mb-2">
                        <div class="h-20 w-20 rounded-lg bg-gray-300 flex items-center justify-center">
                            <i class="fas fa-user text-gray-500 text-2xl"></i>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- New Photo Upload -->
                    <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">
                        ເປີ່ຍນຮູບໃໝ່
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
                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? $student->first_name); ?>"
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
                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? $student->last_name); ?>"
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
                        <?php 
                        $genders = ['ພຣະ', 'ສ.ນ', 'ຊາຍ', 'ຍິງ', 'ອຶ່ນໆ'];
                        $selected_gender = $_POST['gender'] ?? $student->gender;
                        foreach ($genders as $gender): ?>
                        <option value="<?php echo $gender; ?>" <?php echo $selected_gender === $gender ? 'selected' : ''; ?>>
                            <?php echo $gender; ?>
                        </option>
                        <?php endforeach; ?>
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
                           value="<?php echo htmlspecialchars($_POST['dob'] ?? $student->dob); ?>"
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
                           value="<?php echo htmlspecialchars($_POST['email'] ?? $student->email); ?>"
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
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? $student->phone); ?>"
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
                           value="<?php echo htmlspecialchars($_POST['village'] ?? $student->village); ?>"
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
                           value="<?php echo htmlspecialchars($_POST['district'] ?? $student->district); ?>"
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
                           value="<?php echo htmlspecialchars($_POST['province'] ?? $student->province); ?>"
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
                        <?php 
                        $accommodation_types = ['ມີວັດຢູ່ແລ້ວ', 'ຫາວັດໃຫ້'];
                        $selected_accommodation = $_POST['accommodation_type'] ?? $student->accommodation_type;
                        foreach ($accommodation_types as $type): ?>
                        <option value="<?php echo $type; ?>" <?php echo $selected_accommodation === $type ? 'selected' : ''; ?>>
                            <?php echo $type; ?>
                        </option>
                        <?php endforeach; ?>
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
                        <?php 
                        $selected_major = $_POST['major_id'] ?? $student->major_id;
                        foreach ($majors as $major): ?>
                        <option value="<?php echo $major['id']; ?>" <?php echo $selected_major == $major['id'] ? 'selected' : ''; ?>>
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
                        <?php 
                        $selected_year = $_POST['academic_year_id'] ?? $student->academic_year_id;
                        foreach ($academic_years as $year): ?>
                        <option value="<?php echo $year['id']; ?>" <?php echo $selected_year == $year['id'] ? 'selected' : ''; ?>>
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
                           value="<?php echo htmlspecialchars($_POST['previous_school'] ?? $student->previous_school); ?>"
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
                <i class="fas fa-save mr-2"></i>ອັບເດດຂໍ້ມູນ
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

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

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = 'ບໍ່ພົບລະຫັດນັກສຶກສາ';
    header('Location: /students/views/students/index.php');
    exit();
}

$student_id = (int)$_GET['id'];

// ເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
$database = new Database();
$db = $database->getConnection();

// ສ້າງ objects
$student = new Student($db);
$major = new Major($db);
$year = new AcademicYear($db);

// ດຶງຂໍ້ມູນນັກສຶກສາ
$student->id = $student_id;
if (!$student->readOne()) {
    $_SESSION['error'] = 'ບໍ່ພົບຂໍ້ມູນນັກສຶກສາ';
    header('Location: /students/views/students/index.php');
    exit();
}

// ດຶງຂໍ້ມູນສາຂາ
$major->id = $student->major_id;
$major->readOne();

// ດຶງຂໍ້ມູນປີການສຶກສາ
$year->id = $student->academic_year_id;
$year->readOne();

$page_title = 'ລາຍລະອຽດນັກສຶກສາ';
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - ລະບົບລົງທະບຽນການຮຽນ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Noto Sans Lao', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- ຫົວຂໍ້ -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo $page_title; ?></h1>
                    <p class="text-gray-600">ລາຍລະອຽດຂໍ້ມູນນັກສຶກສາ</p>
                </div>
                <div class="flex space-x-3">
                    <a href="/students/views/students/edit.php?id=<?php echo $student->id; ?>" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-300">
                        <i class="fas fa-edit mr-2"></i>ແກ້ໄຂ
                    </a>
                    <a href="/students/views/students/index.php" 
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-300">
                        <i class="fas fa-arrow-left mr-2"></i>ກັບໄປ
                    </a>
                </div>
            </div>

            <!-- ຂໍ້ມູນນັກສຶກສາ -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- ຮູບພາບ -->
                        <div class="lg:col-span-1">
                            <div class="text-center">
                                <?php if (!empty($student->photo) && file_exists(__DIR__ . '/../../public/uploads/' . $student->photo)): ?>
                                    <img src="/students/public/uploads/<?php echo htmlspecialchars($student->photo); ?>" 
                                         alt="ຮູບນັກສຶກສາ" 
                                         class="w-64 h-64 object-cover rounded-lg border-4 border-gray-200 shadow-md mx-auto">
                                <?php else: ?>
                                    <div class="w-64 h-64 bg-gray-200 rounded-lg border-4 border-gray-200 shadow-md mx-auto flex items-center justify-center">
                                        <i class="fas fa-user text-6xl text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mt-4">
                                    <h2 class="text-2xl font-bold text-gray-900">
                                        <?php echo htmlspecialchars($student->first_name . ' ' . $student->last_name); ?>
                                    </h2>
                                    <p class="text-lg text-blue-600 font-medium mt-1">
                                        ລະຫັດນັກສຶກສາ: <?php echo htmlspecialchars($student->student_id); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ຂໍ້ມູນທົ່ວໄປ -->
                        <div class="lg:col-span-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- ຂໍ້ມູນສ່ວນຕົວ -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                                        ຂໍ້ມູນສ່ວນຕົວ
                                    </h3>
                                    
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600">ຊື່</label>
                                            <p class="text-lg text-gray-900"><?php echo htmlspecialchars($student->first_name); ?></p>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600">ນາມສະກຸນ</label>
                                            <p class="text-lg text-gray-900"><?php echo htmlspecialchars($student->last_name); ?></p>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600">ວັນເດືອນປີເກີດ</label>
                                            <p class="text-lg text-gray-900">
                                                <?php 
                                                if ($student->dob) {
                                                    echo date('d/m/Y', strtotime($student->dob));
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600">ເພດ</label>
                                            <p class="text-lg text-gray-900">
                                                <?php 
                                                echo $student->gender == 'male' ? 'ຊາຍ' : 'ຍິງ';
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- ຂໍ້ມູນການສຶກສາ -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                                        ຂໍ້ມູນການສຶກສາ
                                    </h3>
                                    
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600">ສາຂາວິຊາ</label>
                                            <p class="text-lg text-gray-900"><?php echo htmlspecialchars($major->name ?? '-'); ?></p>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600">ປີການສຶກສາ</label>
                                            <p class="text-lg text-gray-900"><?php echo htmlspecialchars($year->year ?? '-'); ?></p>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600">ສະຖານະ</label>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                ກຳລັງສຶກສາ
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ຂໍ້ມູນການຕິດຕໍ່ -->
                <?php if (!empty($student->email) || !empty($student->phone)): ?>
                <div class="border-t border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">ຂໍ້ມູນການຕິດຕໍ່</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php if (!empty($student->email)): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">ອີເມວ</label>
                            <p class="text-lg text-gray-900"><?php echo htmlspecialchars($student->email); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($student->phone)): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">ເບີໂທ</label>
                            <p class="text-lg text-gray-900"><?php echo htmlspecialchars($student->phone); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ຂໍ້ມູນທີ່ຢູ່ -->
                <div class="border-t border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">ທີ່ຢູ່</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">ບ້ານ</label>
                            <p class="text-lg text-gray-900"><?php echo htmlspecialchars($student->village ?? '-'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">ເມືອງ</label>
                            <p class="text-lg text-gray-900"><?php echo htmlspecialchars($student->district ?? '-'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">ແຂວງ</label>
                            <p class="text-lg text-gray-900"><?php echo htmlspecialchars($student->province ?? '-'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- ຂໍ້ມູນລະບົບ -->
                <div class="border-t border-gray-200 bg-gray-50 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">ຂໍ້ມູນລະບົບ</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-600">
                        <div>
                            <label class="block font-medium">ວັນທີລົງທະບຽນ</label>
                            <p><?php echo date('d/m/Y H:i', strtotime($student->registered_at)); ?></p>
                        </div>
                        <div>
                            <label class="block font-medium">ປະເພດທີ່ພັກ</label>
                            <p><?php echo htmlspecialchars($student->accommodation_type ?? '-'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</body>
</html>

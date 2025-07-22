<?php
// ກວດສອບວ່າມີ session ແລ້ວຫຼືບໍ່
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/controllers/AuthController.php';

// ຖ້າເຂົ້າລະບົບແລ້ວໃຫ້ໄປໜ້າ dashboard
if (AuthController::isLoggedIn()) {
    header('Location: /students/views/dashboard.php');
    exit();
}

$title = 'ເຂົ້າລະບົບ';

// ຈັດການການເຂົ້າລະບົບ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $auth = new AuthController();
    if ($auth->login()) {
        header('Location: /students/views/dashboard.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - ລະບົບລົງທະບຽນການສຶກສາ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Noto Sans Lao', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Login Card -->
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600 px-8 py-6 text-center">
                <i class="fas fa-graduation-cap text-white text-4xl mb-4"></i>
                <h1 class="text-white text-2xl font-bold">ລະບົບລົງທະບຽນການສຶກສາ</h1>
                <p class="text-blue-100 mt-2">ກະລຸນາເຂົ້າລະບົບເພື່ອດໍາເນີນການຕໍ່</p>
            </div>

            <!-- Login Form -->
            <div class="px-8 py-6">
                <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-gray-700 text-sm font-medium mb-2">
                            <i class="fas fa-user mr-2"></i>ຊື່ຜູ້ໃຊ້
                        </label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                               placeholder="ປ້ອນຊື່ຜູ້ໃຊ້"
                               value="<?php echo $_POST['username'] ?? ''; ?>">
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-gray-700 text-sm font-medium mb-2">
                            <i class="fas fa-lock mr-2"></i>ລະຫັດຜ່ານ
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                   placeholder="ປ້ອນລະຫັດຜ່ານ">
                            <button type="button" 
                                    onclick="togglePassword()"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <i class="fas fa-eye" id="passwordToggle"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-sign-in-alt mr-2"></i>ເຂົ້າລະບົບ
                    </button>
                </form>

                <!-- Demo Accounts -->
                <div class="mt-8 border-t pt-6">
                    <h3 class="text-gray-700 text-sm font-medium mb-4 text-center">ບັນຊີທົດລອງ:</h3>
                    <div class="space-y-2 text-xs text-gray-600">
                        <div class="bg-gray-50 p-3 rounded">
                            <strong>Admin:</strong> admin / password123
                        </div>
                        <div class="bg-gray-50 p-3 rounded">
                            <strong>User:</strong> user / password123
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-white text-sm">
            <p>&copy; <?php echo date('Y'); ?> ລະບົບລົງທະບຽນການສຶກສາ. ທຸກສິດຖືກສະຫງວນໄວ້.</p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('passwordToggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.classList.remove('fa-eye');
                passwordToggle.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordToggle.classList.remove('fa-eye-slash');
                passwordToggle.classList.add('fa-eye');
            }
        }

        // Auto hide error messages
        setTimeout(function() {
            const errorAlert = document.querySelector('.bg-red-100');
            if (errorAlert) {
                errorAlert.style.transition = 'opacity 0.5s ease-out';
                errorAlert.style.opacity = '0';
                setTimeout(function() {
                    errorAlert.remove();
                }, 500);
            }
        }, 5000);
    </script>
</body>
</html>

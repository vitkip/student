<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'ລະບົບລົງທະບຽນການສຶກສາ'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'lao': ['Noto Sans Lao', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: 'Noto Sans Lao', sans-serif; 
        }
        
        /* Custom mobile-friendly styles */
        @media (max-width: 640px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            /* Make buttons more touch-friendly */
            .btn-touch {
                min-height: 44px;
                min-width: 44px;
                padding: 12px 16px;
            }
            
            /* Improve readability on small screens */
            .text-responsive {
                font-size: 14px;
                line-height: 1.5;
            }
        }
        
        @media (max-width: 768px) {
            /* Hide desktop user info on medium screens */
            .desktop-user-info {
                display: none;
            }
        }
        
        /* Smooth transitions for mobile menu */
        .mobile-menu-transition {
            transition: all 0.3s ease-in-out;
        }
        
        /* Ensure mobile menu doesn't overflow */
        .mobile-menu-container {
            max-height: calc(100vh - 80px);
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-blue-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-graduation-cap text-white text-2xl"></i>
                    <h1 class="text-white text-xl font-semibold hidden sm:block">ລະບົບລົງທະບຽນການສຶກສາ</h1>
                    <h1 class="text-white text-lg font-semibold sm:hidden">ລະບົບລົງທະບຽນ</h1>
                </div>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="flex items-center space-x-4">
                    <!-- Desktop Menu -->
                    <div class="hidden lg:flex space-x-6">
                        <a href="/students/views/dashboard.php" class="text-white hover:text-blue-200 transition duration-200">
                            <i class="fas fa-tachometer-alt mr-2"></i>ໜ້າຫຼັກ
                        </a>
                        <a href="/students/views/students/" class="text-white hover:text-blue-200 transition duration-200">
                            <i class="fas fa-users mr-2"></i>ນັກສຶກສາ
                        </a>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="/students/views/majors/" class="text-white hover:text-blue-200 transition duration-200">
                            <i class="fas fa-book mr-2"></i>ສາຂາວິຊາ
                        </a>
                        <a href="/students/views/years/" class="text-white hover:text-blue-200 transition duration-200">
                            <i class="fas fa-calendar mr-2"></i>ປີການສຶກສາ
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- User Info - Desktop -->
                    <div class="hidden md:flex items-center space-x-3 desktop-user-info">
                        <span class="text-white text-sm">
                            <i class="fas fa-user mr-2"></i><?php echo $_SESSION['full_name']; ?>
                        </span>
                        <a href="/students/controllers/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition duration-200 btn-touch">
                            <i class="fas fa-sign-out-alt mr-2"></i><span class="hidden lg:inline">ອອກລະບົບ</span><span class="lg:hidden">ອອກ</span>
                        </a>
                    </div>
                    
                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-button" class="lg:hidden text-white focus:outline-none focus:text-blue-200">
                        <i id="menu-icon" class="fas fa-bars text-xl"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Mobile Menu -->
            <?php if (isset($_SESSION['user_id'])): ?>
            <div id="mobile-menu" class="lg:hidden pb-4 hidden mobile-menu-transition">
                <div class="flex flex-col space-y-3 border-t border-blue-500 pt-4 mobile-menu-container">
                    <!-- User Info - Mobile -->
                    <div class="md:hidden text-white text-sm px-4 py-3 bg-blue-700 rounded-lg">
                        <i class="fas fa-user mr-2"></i><?php echo $_SESSION['full_name']; ?>
                        <span class="text-xs text-blue-200 ml-2 block sm:inline">(<?php echo $_SESSION['role'] === 'admin' ? 'ຜູ້ດູແລ' : 'ຜູ້ໃຊ້'; ?>)</span>
                    </div>
                    
                    <!-- Navigation Links -->
                    <a href="/students/views/dashboard.php" class="text-white hover:text-blue-200 py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200 btn-touch">
                        <i class="fas fa-tachometer-alt mr-3 w-5 text-center"></i>ໜ້າຫຼັກ
                    </a>
                    <a href="/students/views/students/" class="text-white hover:text-blue-200 py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200 btn-touch">
                        <i class="fas fa-users mr-3 w-5 text-center"></i>ນັກສຶກສາ
                    </a>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="/students/views/majors/" class="text-white hover:text-blue-200 py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200 btn-touch">
                        <i class="fas fa-book mr-3 w-5 text-center"></i>ສາຂາວິຊາ
                    </a>
                    <a href="/students/views/years/" class="text-white hover:text-blue-200 py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200 btn-touch">
                        <i class="fas fa-calendar mr-3 w-5 text-center"></i>ປີການສຶກສາ
                    </a>
                    <?php endif; ?>
                    
                    <!-- Logout Button - Mobile -->
                    <div class="pt-3 border-t border-blue-500">
                        <a href="/students/controllers/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-3 rounded-lg transition duration-200 flex items-center justify-center btn-touch">
                            <i class="fas fa-sign-out-alt mr-2"></i>ອອກລະບົບ
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-check-circle mr-3"></i>
            <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-exclamation-circle mr-3"></i>
            <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-6">

    <!-- Mobile Menu JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('menu-icon');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    if (mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.remove('hidden');
                        mobileMenu.classList.add('block');
                        menuIcon.classList.remove('fa-bars');
                        menuIcon.classList.add('fa-times');
                    } else {
                        mobileMenu.classList.add('hidden');
                        mobileMenu.classList.remove('block');
                        menuIcon.classList.remove('fa-times');
                        menuIcon.classList.add('fa-bars');
                    }
                });
                
                // Close mobile menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                        mobileMenu.classList.add('hidden');
                        mobileMenu.classList.remove('block');
                        menuIcon.classList.remove('fa-times');
                        menuIcon.classList.add('fa-bars');
                    }
                });
                
                // Close mobile menu when window is resized to desktop size
                window.addEventListener('resize', function() {
                    if (window.innerWidth >= 1024) { // lg breakpoint
                        mobileMenu.classList.add('hidden');
                        mobileMenu.classList.remove('block');
                        menuIcon.classList.remove('fa-times');
                        menuIcon.classList.add('fa-bars');
                    }
                });
            }
        });
    </script>

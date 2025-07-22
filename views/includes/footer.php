    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">ກ່ຽວກັບລະບົບ</h3>
                    <p class="text-gray-300">ລະບົບລົງທະບຽນການສຶກສາສໍາລັບຈັດການຂໍ້ມູນນັກສຶກສາ ສາຂາວິຊາ ແລະ ປີການສຶກສາ</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">ເມນູຫຼັກ</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="/students/views/dashboard.php" class="hover:text-white">ໜ້າຫຼັກ</a></li>
                        <li><a href="students/" class="hover:text-white">ຈັດການນັກສຶກສາ</a></li>
                        <li><a href="majors/" class="hover:text-white">ຈັດການສາຂາວິຊາ</a></li>
                        <li><a href="years/" class="hover:text-white">ຈັດການປີການສຶກສາ</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">ຕິດຕໍ່</h3>
                    <div class="text-gray-300 space-y-2">
                        <p><i class="fas fa-envelope mr-2"></i>admin@example.com</p>
                        <p><i class="fas fa-phone mr-2"></i>+856 20 1234 5678</p>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
                <p>&copy; <?php echo date('Y'); ?> ລະບົບລົງທະບຽນການສຶກສາ. ທຸກສິດຖືກສະຫງວນໄວ້.</p>
            </div>
        </div>
    </footer>

    <script>
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);

        // Confirm delete actions
        function confirmDelete(message = 'ທ່ານຕ້ອງການລົບຂໍ້ມູນນີ້ແທ້ບໍ?') {
            return confirm(message);
        }
    </script>
</body>
</html>

# ລະບົບລົງທະບຽນການສຶກສາ (register-learning)

ລະບົບຈັດການນັກສຶກສາທີ່ພັດທະນາດ້ວຍ PHP 8+, PDO, MySQL ແລະ TailwindCSS

## ຄຸນສົມບັດຫຼັກ

### 🔐 ລະບົບຜູ້ໃຊ້
- **ເຂົ້າລະບົບ/ອອກລະບົບ** ທີ່ປອດໄພ
- **ບົດບາດຜູ້ໃຊ້**: Admin ແລະ User
- **ເກັບລະຫັດຜ່ານແບບ Hash** (password_hash/password_verify)

### 👥 ຈັດການນັກສຶກສາ
- **ເພີ່ມ/ແກ້ໄຂ/ລົບ** ຂໍ້ມູນນັກສຶກສາ
- **ອັບໂຫລດຮູບນັກສຶກສາ** (JPG, PNG, GIF)
- **ຄົ້ນຫາ** ນັກສຶກສາດ້ວຍຊື່, ລະຫັດ, ອີເມລ
- **Pagination** ສໍາລັບລາຍຊື່ນັກສຶກສາ
- **ສ້າງລະຫັດນັກສຶກສາອັດຕະໂນມັດ**

### 📚 ຈັດການສາຂາວິຊາ
- **CRUD ສາຂາວິຊາ** (Admin ເທົ່ານັ້ນ)
- **ລະຫັດສາຂາ** ທີ່ບໍ່ຊ້ໍາກັນ
- **ສະຖານະ**: ເປີດ/ປິດໃຊ້ງານ

### 📅 ຈັດການປີການສຶກສາ
- **CRUD ປີການສຶກສາ** (Admin ເທົ່ານັ້ນ)
- **ຮູບແບບປີ**: 2025-2026
- **ເພີ່ມດ່ວນ** ປີການສຶກສາໃໝ່

### 📊 Dashboard ແລະ ສະຖິຕິ
- **ສະຖິຕິນັກສຶກສາ** ຕາມສາຂາ ແລະ ປີການສຶກສາ
- **ກາຟິກແທ່ງ** ສະແດງຂໍ້ມູນ
- **ການດໍາເນີນການດ່ວນ**

## ຄວາມຕ້ອງການລະບົບ

- **PHP**: 8.0 ຫຼືສູງກວ່າ
- **MySQL**: 5.7 ຫຼືສູງກວ່າ (ຫຼື MariaDB 10.2+)
- **Web Server**: Apache ຫຼື Nginx
- **Extensions**: PDO, PDO_MySQL, GD ສໍາລັບອັບໂຫລດຮູບ

## ການຕິດຕັ້ງ

### 1. ດາວໂຫລດແລະຄັດລອກໄຟລ໌
```bash
# ຄັດລອກໂຟລເດີ students ໄປຍັງ htdocs ຂອງ XAMPP
cp -r students/ C:/xampp/htdocs/
```

### 2. ນໍາເຂົ້າຖານຂໍ້ມູນ
1. ເປີດ phpMyAdmin (http://localhost/phpmyadmin)
2. ສ້າງຖານຂໍ້ມູນໃໝ່ຊື່ `register-learning`
3. ນໍາເຂົ້າໄຟລ໌ `register.sql`

### 3. ຕັ້ງຄ່າການເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
ແກ້ໄຂໄຟລ໌ `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'register-learning';
private $username = 'root';      // ປ່ຽນຕາມການຕັ້ງຄ່າ
private $password = '';          // ປ່ຽນຕາມການຕັ້ງຄ່າ
```

### 4. ຕັ້ງຄ່າ Permissions
```bash
# ໃຫ້ສິດ Write ສໍາລັບໂຟລເດີອັບໂຫລດ
chmod 755 public/uploads/
```

### 5. ເຂົ້າເຖິງລະບົບ
- **URL**: http://localhost/students/
- **Admin**: username: `admin`, password: `password123`
- **User**: username: `user`, password: `password123`

## ໂຄງສ້າງໄຟລ໌

```
students/
├── index.php                    # ໜ້າເຂົ້າລະບົບ
├── register.sql                 # ໄຟລ໌ SQL ຖານຂໍ້ມູນ
├── config/
│   └── database.php            # ການຕັ້ງຄ່າຖານຂໍ້ມູນ
├── models/
│   ├── User.php                # Model ຜູ້ໃຊ້
│   ├── Student.php             # Model ນັກສຶກສາ
│   ├── Major.php               # Model ສາຂາວິຊາ
│   └── AcademicYear.php        # Model ປີການສຶກສາ
├── controllers/
│   ├── AuthController.php      # Controller ການເຂົ້າລະບົບ
│   ├── StudentController.php   # Controller ນັກສຶກສາ
│   ├── MajorController.php     # Controller ສາຂາວິຊາ
│   ├── YearController.php      # Controller ປີການສຶກສາ
│   └── logout.php              # ອອກລະບົບ
├── views/
│   ├── includes/
│   │   ├── header.php          # Header ທົ່ວໄປ
│   │   └── footer.php          # Footer ທົ່ວໄປ
│   ├── dashboard.php           # ໜ້າຫຼັກ
│   ├── students/
│   │   ├── index.php           # ລາຍຊື່ນັກສຶກສາ
│   │   ├── create.php          # ເພີ່ມນັກສຶກສາ
│   │   ├── edit.php            # ແກ້ໄຂນັກສຶກສາ
│   │   └── delete.php          # ລົບນັກສຶກສາ
│   ├── majors/
│   │   └── index.php           # ຈັດການສາຂາວິຊາ
│   └── years/
│       └── index.php           # ຈັດການປີການສຶກສາ
└── public/
    └── uploads/                # ໂຟລເດີເກັບຮູບ
```

## ຄຸນສົມບັດດ້ານຄວາມປອດໄພ

### 🔒 ປ້ອງກັນ SQL Injection
- ໃຊ້ **PDO Prepared Statements** ທຸກຄໍາສັ່ງ SQL
- **Parameter Binding** ທຸກ Input

### 🛡️ ການຄວບຄຸມການເຂົ້າເຖິງ
- **Session Management** ທີ່ປອດໄພ
- **Role-based Access Control** (Admin/User)
- **ກວດສອບສິດ** ກ່ອນທຸກການດໍາເນີນການ

### 🔐 ການເກັບລະຫັດຜ່ານ
- **Password Hashing** ດ້ວຍ `password_hash()`
- **Password Verification** ດ້ວຍ `password_verify()`

### 🧹 ການອັບໂຫລດໄຟລ໌
- **ກວດສອບປະເພດໄຟລ໌** (ຮູບເທົ່ານັ້ນ)
- **ຈໍາກັດຂະໜາດ** (5MB)
- **ເປີ່ຍນຊື່ໄຟລ໌** ເພື່ອຄວາມປອດໄພ

## ການປັບແຕ່ງ

### ຂະໜາດໄຟລ໌ອັບໂຫລດ
ແກ້ໄຂໃນ `controllers/StudentController.php`:
```php
$max_size = 5 * 1024 * 1024; // 5MB
```

### ຈໍານວນລາຍການຕໍ່ໜ້າ
ແກ້ໄຂໃນ `views/students/index.php`:
```php
$records_per_page = 10; // ປ່ຽນຕົວເລກ
```

### ປະເພດໄຟລ໌ອະນຸຍາດ
ແກ້ໄຂໃນ `controllers/StudentController.php`:
```php
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
```

## ການແກ້ໄຂບັນຫາທົ່ວໄປ

### ບັນຫາການເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
1. ກວດສອບການຕັ້ງຄ່າໃນ `config/database.php`
2. ໃຫ້ແນ່ໃຈວ່າ MySQL ທໍາງານຢູ່
3. ກວດສອບຊື່ຖານຂໍ້ມູນ, ຊື່ຜູ້ໃຊ້, ລະຫັດຜ່ານ

### ບັນຫາການອັບໂຫລດຮູບ
1. ກວດສອບ PHP configuration:
   ```ini
   file_uploads = On
   upload_max_filesize = 10M
   post_max_size = 10M
   ```
2. ກວດສອບ permissions ຂອງໂຟລເດີ `public/uploads/`

### ບັນຫາ Session
1. ກວດສອບວ່າ PHP session ເປີດຢູ່:
   ```ini
   session.auto_start = 1
   ```

## ລິຂະສິດ

ໂຄຣງການນີ້ຖືກພັດທະນາສໍາລັບການສຶກສາ ແລະ ສາມາດນໍາໃຊ້ໄດ້ເສລີ.

## ການສະໜັບສະໜູນ

ຫາກມີຄໍາຖາມຫຼືບັນຫາ, ກະລຸນາຕິດຕໍ່:
- **Email**: admin@example.com
- **Phone**: +856 20 1234 5678

---

**ພັດທະນາໂດຍ**: ທີມງານພັດທະນາລະບົບ  
**ເວີຊັ້ນ**: 1.0.0  
**ວັນທີປັບປຸງ**: <?php echo date('d/m/Y'); ?>

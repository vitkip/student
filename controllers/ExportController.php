<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Major.php';
require_once __DIR__ . '/../models/AcademicYear.php';
require_once __DIR__ . '/../config/database.php';

AuthController::requireLogin();

// ตรวจสอบการขอสร้างบัตรนักศึกษา
if (isset($_GET['action']) && $_GET['action'] === 'student_card') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        generateStudentCard($_GET['id']);
        exit();
    } else {
        $_SESSION['error'] = 'ກະລຸນາລະບຸລະຫັດນັກສຶກສາ';
        header('Location: /students/views/students/index.php');
        exit();
    }
}

// ตรวจสอบสิทธิ์ admin สำหรับการเข้าถึง export function อื่นๆ
if ($_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'ທ່ານບໍ່ມີສິດໃນການເຂົ້າເຖິງໜ້ານີ້';
    header('Location: /students/views/dashboard.php');
    exit();
}

// เชื่อมต่อฐานข้อมูล
$database = new Database();
$db = $database->getConnection();

// รับพารามิเตอร์
$export_type = $_GET['type'] ?? '';
$format = $_GET['format'] ?? '';
$search = $_GET['search'] ?? '';
$major_id = $_GET['major_id'] ?? '';
$academic_year_id = $_GET['academic_year_id'] ?? '';

if (empty($export_type) || empty($format)) {
    $_SESSION['error'] = 'ກະລຸນາເລືອກປະເພດການສົ່ງອອກແລະຮູບແບບ';
    header('Location: /students/views/reports/index.php');
    exit();
}

// ดึงข้อมูลตามประเภท
$data = [];
$title = '';

switch ($export_type) {
    case 'students':
        $student = new Student($db);
        $data = $student->getAllForReport($search, $major_id, $academic_year_id);
        $title = 'ລາຍງານຂໍ້ມູນນັກສຶກສາ';
        break;
        
    case 'majors':
        $major = new Major($db);
        $data = $major->readAll();
        $title = 'ລາຍງານຂໍ້ມູນສາຂາວິຊາ';
        break;
        
    case 'years':
        $year = new AcademicYear($db);
        $data = $year->readAll();
        $title = 'ລາຍງານຂໍ້ມູນປີການສຶກສາ';
        break;
        
    default:
        $_SESSION['error'] = 'ປະເພດການສົ່ງອອກບໍ່ຖືກຕ້ອງ';
        header('Location: /students/views/reports/index.php');
        exit();
}

// ส่งออกตามรูปแบบ
switch ($format) {
    case 'excel':
        exportToExcel($data, $export_type, $title);
        break;
        
    case 'pdf':
        exportToPDF($data, $export_type, $title);
        break;
        
    case 'student_cards':
        if ($export_type !== 'students') {
            $_SESSION['error'] = 'ບັດນັກສຶກສາສາມາດສ້າງໄດ້ສະເພາະຂໍ້ມູນນັກສຶກສາເທົ່ານັ້ນ';
            header('Location: /students/views/reports/index.php');
            exit();
        }
        generateMultipleStudentCards($data);
        break;
        
    default:
        $_SESSION['error'] = 'ຮູບແບບການສົ່ງອອກບໍ່ຖືກຕ້ອງ';
        header('Location: /students/views/reports/index.php');
        exit();
}

function exportToExcel($data, $type, $title) {
    // สร้างไฟล์ CSV (Excel-compatible)
    $filename = sanitizeFilename($title) . '_' . date('Y-m-d_H-i-s') . '.csv';
    
    // ตั้งค่า header สำหรับ Excel
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Expires: 0');
    header('Pragma: public');
    
    // เปิด output stream
    $output = fopen('php://output', 'w');
    
    // เพิ่ม BOM สำหรับ UTF-8 (สำคัญสำหรับแสดงภาษาลาวใน Excel)
    fwrite($output, "\xEF\xBB\xBF");
    
    // เพิ่มหัวรายงาน
    fputcsv($output, [$title]);
    fputcsv($output, ['ລະບົບລົງທະບຽນການສຶກສາ']);
    fputcsv($output, ['ວັນທີສ້າງລາຍງານ: ' . date('d/m/Y H:i') . ' ນ.']);
    fputcsv($output, ['']);
    
    // เพิ่มข้อมูลสรุป (Summary) แบบคอลัมน์
    if ($type == 'students') {
        // คำนวณข้อมูลสรุปสำหรับนักศึกษา
        $totalStudents = count($data);
        $maleCount = $femaleCount = $monkCount = $nunCount = $otherCount = 0;
        $majors = $years = [];
        
        foreach ($data as $row) {
            $gender = $row['gender'] ?? 'ອຶ່ນໆ';
            switch ($gender) {
                case 'ຊາຍ': $maleCount++; break;
                case 'ຍິງ': $femaleCount++; break;
                case 'ພຣະ': $monkCount++; break;
                case 'ສ.ນ': $nunCount++; break;
                default: $otherCount++; break;
            }
            
            $majorName = $row['major_name'] ?? 'ບໍ່ລະບຸ';
            $yearName = $row['year'] ?? 'ບໍ່ລະບຸ';
            
            if (!isset($majors[$majorName])) $majors[$majorName] = 0;
            if (!isset($years[$yearName])) $years[$yearName] = 0;
            
            $majors[$majorName]++;
            $years[$yearName]++;
        }
        
        // จัดเตรียมข้อมูลสรุปแบบคอลัมน์
        fputcsv($output, ['ສະຫຼຸບຂໍ້ມູນ']);
        $summary_headers = ['ຈໍານວນນັກສຶກສາທັງໝົດ', 'ນັກສຶກສາຊາຍ', 'ນັກສຶກສາຍິງ'];
        $summary_values = [$totalStudents . ' ຄົນ', $maleCount . ' ຄົນ', $femaleCount . ' ຄົນ'];

        if ($monkCount > 0) {
            $summary_headers[] = 'ພຣະ';
            $summary_values[] = $monkCount . ' ຄົນ';
        }
        if ($nunCount > 0) {
            $summary_headers[] = 'ສ.ນ';
            $summary_values[] = $nunCount . ' ຄົນ';
        }
        if ($otherCount > 0) {
            $summary_headers[] = 'ອຶ່ນໆ';
            $summary_values[] = $otherCount . ' ຄົນ';
        }
        
        $summary_headers[] = 'ຈໍານວນສາຂາວິຊາ';
        $summary_values[] = count($majors) . ' ສາຂາ';

        if (!empty($majors)) {
            arsort($majors);
            $topMajor = array_key_first($majors);
            $topMajorCount = $majors[$topMajor];
            $summary_headers[] = 'ສາຂາທີ່ມີນັກສຶກສາຫຼາຍສຸດ';
            $summary_values[] = $topMajor . ' (' . $topMajorCount . ' ຄົນ)';
        }

        if (!empty($years)) {
            arsort($years);
            $topYear = array_key_first($years);
            $topYearCount = $years[$topYear];
            $summary_headers[] = 'ປີການສຶກສາທີ່ມີນັກສຶກສາຫຼາຍສຸດ';
            $summary_values[] = $topYear . ' (' . $topYearCount . ' ຄົນ)';
        }
        
        fputcsv($output, $summary_headers);
        fputcsv($output, $summary_values);

    } else if ($type == 'majors') {
        $totalMajors = count($data);
        $withDescription = 0;
        
        foreach ($data as $row) {
            if (!empty($row['description']) && $row['description'] !== '-') {
                $withDescription++;
            }
        }
        
        fputcsv($output, ['ສະຫຼຸບຂໍ້ມູນ']);
        fputcsv($output, ['ຈໍານວນສາຂາວິຊາທັງໝົດ', 'ສາຂາທີ່ມີຄໍາອະທິບາຍ', 'ສາຂາທີ່ບໍ່ມີຄໍາອະທິບາຍ']);
        fputcsv($output, [$totalMajors . ' ສາຂາ', $withDescription . ' ສາຂາ', ($totalMajors - $withDescription) . ' ສາຂາ']);

    } else if ($type == 'years') {
        $totalYears = count($data);
        $years_list = array_column($data, 'year');
        
        fputcsv($output, ['ສະຫຼຸບຂໍ້ມູນ']);
        $summary_headers = ['ຈໍານວນປີການສຶກສາທັງໝົດ'];
        $summary_values = [$totalYears . ' ປີ'];

        if (!empty($years_list)) {
            $oldestYear = min($years_list);
            $newestYear = max($years_list);
            $summary_headers[] = 'ປີການສຶກສາເກົ່າສຸດ';
            $summary_values[] = $oldestYear;
            $summary_headers[] = 'ປີການສຶກສາໃໝ່ສຸດ';
            $summary_values[] = $newestYear;
        }
        fputcsv($output, $summary_headers);
        fputcsv($output, $summary_values);
    }
    
    fputcsv($output, ['']);  // เว้นบรรทัด
    
    switch ($type) {
        case 'students':
            // Headers - ปรับให้ตรงกับ PDF
            fputcsv($output, [
                'ລໍາດັບ',
                'ລະຫັດນັກສຶກສາ',
                'ເພດ',
                'ຊື່ ນາມສະກຸນ',
                'ອີເມວ',
                'ສາຂາວິຊາ',
                'ປີການສຶກສາ',
                'ທີ່ຢູ່',
                // เพิ่มข้อมูลพื้นฐานอื่นๆ สำหรับ Excel
                'ເບີໂທ',
                'ວັນເກີດ',
                'ໂຮງຮຽນເກົ່າ',
                'ປະເພດທີ່ພັກ',
                'ວັນທີລົງທະບຽນ'
            ]);
            
            // Data
            $rowNumber = 1;
            foreach ($data as $row) {
                // เตรียมข้อมูลที่อยู่แบบเดียวกับ PDF
                $address = trim(($row['village'] ?? '') . ' ' . ($row['district'] ?? '') . ' ' . ($row['province'] ?? ''));
                if (empty($address)) {
                    $address = '-';
                }
                
                // เตรียมชื่อ-นามสกุล
                $fullName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                if (empty($fullName)) {
                    $fullName = '-';
                }
                
                fputcsv($output, [
                    $rowNumber++,
                    $row['student_id'] ?? '',
                    $row['gender'] ?? 'ບໍ່ໄດ້ລະບຸ',
                    $fullName,                  
                    $row['email'] ?? '-',
                    $row['major_name'] ?? '-',
                    $row['year'] ?? '-',
                    $address,
                    // ข้อมูลเพิ่มเติมสำหรับ Excel
                    $row['phone'] ?? '-',
                    $row['dob'] ? date('d/m/Y', strtotime($row['dob'])) : '-',
                    $row['previous_school'] ?? '-',
                    $row['accommodation_type'] ?? '-',
                    $row['registered_at'] ? date('d/m/Y H:i', strtotime($row['registered_at'])) : '-'
                ]);
            }
            break;
            
        case 'majors':
            fputcsv($output, ['ລໍາດັບ', 'ລະຫັດສາຂາ', 'ຊື່ສາຂາວິຊາ', 'ຄໍາອະທິບາຍ']);
            $rowNumber = 1;
            foreach ($data as $row) {
                fputcsv($output, [
                    $rowNumber++,
                    $row['id'] ?? '',
                    $row['name'] ?? '',
                    $row['description'] ?? '-'
                ]);
            }
            break;
            
        case 'years':
            fputcsv($output, ['ລໍາດັບ', 'ລະຫັດປີ', 'ປີການສຶກສາ', 'ຄໍາອະທິບາຍ']);
            $rowNumber = 1;
            foreach ($data as $row) {
                fputcsv($output, [
                    $rowNumber++,
                    $row['id'] ?? '',
                    $row['year'] ?? '',
                    $row['description'] ?? '-'
                ]);
            }
            break;
    }
    
    fclose($output);
    exit();
}

function exportToPDF($data, $type, $title) {
    // สร้าง HTML สำหรับ PDF
    $html = generatePDFHTML($data, $type, $title);
    
    // ใช้ mPDF หรือ TCPDF (ในที่นี้จะใช้วิธีง่ายๆ ด้วย HTML)
    $filename = sanitizeFilename($title) . '_' . date('Y-m-d_H-i-s') . '.pdf';
    
    // สำหรับการทดสอบ จะใช้วิธีแปลง HTML เป็น PDF ด้วย browser
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
    
    // JavaScript สำหรับพิมพ์เป็น PDF
    echo '<script>
        window.onload = function() {
            window.print();
        };
    </script>';
    exit();
}

// ฟังก์ชั่นสำหรับสร้างบัตรนักศึกษาหลายใบ PDF
function generateMultipleStudentCards($students) {
    // สร้าง QR code สำหรับแต่ละนักศึกษา
    require_once __DIR__ . '/../libs/phpqrcode.php';
    $qrcodeDir = __DIR__ . '/../public/qrcodes/';
    if (!is_dir($qrcodeDir)) { mkdir($qrcodeDir, 0777, true); }

    foreach ($students as $student) {
        $studentId = $student['student_id'];
        $fullName = trim($student['first_name'] . ' ' . $student['last_name']);
        $qrData = 'ID:' . $studentId . ',Name:' . $fullName;
        $qrFile = $qrcodeDir . $studentId . '.png';
        if (!file_exists($qrFile)) {
            QRcode::png($qrData, $qrFile, QR_ECLEVEL_L, 4);
        }
    }

    // สร้าง HTML สำหรับบัตรนักศึกษาหลายใบ
    $html = '<!DOCTYPE html>
    <html lang="lo">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ບັດນັກສຶກສາຫຼາຍໃບ</title>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            @page {
                /* ขนาดมาตรฐานบัตรนักศึกษา 3.375 x 2.125 นิ้ว (85.7 x 54 มม.) */
                size: 86mm 54mm;
                margin: 0;
            }
            body {
                font-family: "Noto Sans Lao", Arial, sans-serif;
                margin: 0;
                padding: 0;
            }
            .card {
                width: 86mm;
                height: 54mm;
                background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
                color: white;
                position: relative;
                border-radius: 3mm;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                page-break-after: always;
                margin-bottom: 5mm;
            }
            .card-inner {
                position: relative;
                width: 100%;
                height: 100%;
                padding: 3mm;
                box-sizing: border-box;
            }
            .header {
                text-align: center;
                margin-bottom: 2mm;
                position: relative;
                z-index: 2;
            }
            .university-name {
                font-size: 12pt;
                font-weight: bold;
                margin-bottom: 1mm;
                text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            }
            .card-title {
                font-size: 8pt;
                text-transform: uppercase;
                letter-spacing: 1px;
                color: #f0f9ff;
            }
            .photo-container {
                float: left;
                width: 20mm;
                height: 25mm;
                background-color: white;
                border: 1px solid #ddd;
                margin-right: 3mm;
            }
            .photo {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .student-info {
                font-size: 7pt;
                line-height: 1.5;
            }
            .student-id {
                font-size: 10pt;
                font-weight: bold;
                margin-bottom: 1mm;
                color: #fff;
            }
            .student-name {
                font-weight: bold;
                font-size: 8pt;
                margin-bottom: 1mm;
            }
            .label {
                color: rgba(255, 255, 255, 0.8);
                font-size: 6pt;
                text-transform: uppercase;
            }
            .value {
                color: white;
            }
            .footer {
                position: absolute;
                bottom: 3mm;
                left: 3mm;
                right: 3mm;
                text-align: center;
                font-size: 5pt;
                color: rgba(255, 255, 255, 0.8);
            }
            .watermark {
                position: absolute;
                bottom: -10mm;
                right: -10mm;
                width: 50mm;
                height: 50mm;
                background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\' fill=\'%23ffffff15\'%3E%3Cpath d=\'M50 5A45 45 0 1 1 5 50 45 45 0 0 1 50 5m0-5a50 50 0 1 0 50 50A50 50 0 0 0 50 0z\'/%3E%3Cpath d=\'M47.41 52.59A2.59 2.59 0 1 1 50 50a2.59 2.59 0 0 1-2.59 2.59zm-5.18-15.54a2.59 2.59 0 1 0 2.59 2.59 2.59 2.59 0 0 0-2.59-2.59zm10.36 0a2.59 2.59 0 1 0 2.59 2.59 2.59 2.59 0 0 0-2.59-2.59zm-5.18-10.36a2.59 2.59 0 1 0 2.59 2.59 2.59 2.59 0 0 0-2.59-2.59z\'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                opacity: 0.2;
                z-index: 1;
            }
            .qr-code {
                position: absolute;
                bottom: 6mm;
                right: 3mm;
                width: 16mm;
                height: 16mm;
                background-color: white;
                padding: 1mm;
                border-radius: 1mm;
            }
            .signature-line {
                position: absolute;
                bottom: 10mm;
                left: 3mm;
                width: 25mm;
                border-top: 0.5px solid rgba(255, 255, 255, 0.6);
                text-align: center;
                font-size: 5pt;
                padding-top: 1mm;
            }
            .barcode {
                position: absolute;
                bottom: 3mm;
                left: 50%;
                transform: translateX(-50%);
                font-size: 8pt;
                letter-spacing: 2px;
                color: white;
                font-family: "Courier New", monospace;
            }
            @media print {
                body { margin: 0; }
                .page-break { page-break-before: always; }
            }
        </style>
    </head>
    <body>';

    // สร้างบัตรสำหรับแต่ละนักศึกษา
    foreach ($students as $index => $student) {
        $fullName = trim($student['first_name'] . ' ' . $student['last_name']);
        $majorName = $student['major_name'] ?? '-';
        $yearName = $student['year'] ?? '-';
        $studentId = $student['student_id'];
        
        $html .= '
        <div class="card' . ($index > 0 ? ' page-break' : '') . '">
            <div class="watermark"></div>
            <div class="card-inner">
                <div class="header">
                    <div class="university-name">ມະຫາວິທະຍາໄລພຸດທະສາດສະໜາ</div>
                    <div class="card-title">ບັດນັກສຶກສາ</div>
                </div>
                
                <div class="photo-container">
                    ' . (empty($student['photo']) ? 
                    '<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#f3f4f6;">
                        <span style="font-size:14pt; color:#9ca3af;">ບໍ່ມີຮູບ</span>
                     </div>' : 
                    '<img src="/students/public/uploads/' . htmlspecialchars($student['photo']) . '" alt="Student Photo" class="photo">') . '
                </div>
                
                <div class="student-info">
                    <div class="student-id">' . htmlspecialchars($studentId) . '</div>
                    <div class="student-name">' . htmlspecialchars($fullName) . '</div>
                    
                    <div>
                        <span class="label">ເພດ:</span>
                        <span class="value">' . htmlspecialchars($student['gender'] ?? 'ບໍ່ໄດ້ລະບຸ') . '</span>
                    </div>
                    
                    <div>
                        <span class="label">ສາຂາວິຊາ:</span>
                        <span class="value">' . htmlspecialchars($majorName) . '</span>
                    </div>
                    
                    <div>
                        <span class="label">ປີການສຶກສາ:</span>
                        <span class="value">' . htmlspecialchars($yearName) . '</span>
                    </div>
                    
                    <div>
                        <span class="label">ວັນອອກບັດ:</span>
                        <span class="value">' . date('d/m/Y') . '</span>
                    </div>
                </div>
                
                <div class="qr-code">
                    <img src="/students/public/qrcodes/' . htmlspecialchars($studentId) . '.png" alt="QR Code" width="100%" height="100%">
                </div>
                
                <div class="signature-line">
                    ຜູ້ອໍານວຍການ
                </div>
                
                <div class="footer">
                    ມະຫາວິທະຍາໄລພຸດທະສາດສະໜາ - ບັດນີ້ຄວນເກັບຮັກສາຢ່າງລະມັດລະວັງ
                </div>
                
                <div class="barcode">
                    *' . htmlspecialchars($studentId) . '*
                </div>
            </div>
        </div>';
    }
    
    $html .= '</body></html>';
    
    // ส่ง HTML ออกไป
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
    
    // JavaScript สำหรับพิมพ์เป็น PDF
    echo '<script>
        window.onload = function() {
            window.print();
        };
    </script>';
    exit();
}

// ฟังก์ชั่นสำหรับสร้างบัตรนักศึกษา PDF
function generateStudentCard($student_id) {
    require_once __DIR__ . '/../models/Student.php';
    require_once __DIR__ . '/../config/database.php';
    
    // เชื่อมต่อฐานข้อมูล
    $database = new Database();
    $db = $database->getConnection();
    $student = new Student($db);
    $student->id = $student_id;
    
    // ดึงข้อมูลนักศึกษา
    if (!$student->readOne()) {
        $_SESSION['error'] = 'ບໍ່ພົບຂໍ້ມູນນັກສຶກສາ';
        header('Location: /students/views/students/index.php');
        exit();
    }
    
    // สร้าง QR code สำหรับนักศึกษา
    require_once __DIR__ . '/../libs/phpqrcode.php';
    $qrcodeDir = __DIR__ . '/../public/qrcodes/';
    if (!is_dir($qrcodeDir)) { mkdir($qrcodeDir, 0777, true); }
    $qrData = 'ID:' . $student->student_id . ',Name:' . $student->first_name . ' ' . $student->last_name;
    $qrFile = $qrcodeDir . $student->student_id . '.png';
    if (!file_exists($qrFile)) {
        QRcode::png($qrData, $qrFile, QR_ECLEVEL_L, 4);
    }

    // สร้าง HTML สำหรับบัตรนักศึกษา
    $card_html = '<!DOCTYPE html>
    <html lang="lo">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ບັດນັກສຶກສາ - ' . htmlspecialchars($student->student_id) . '</title>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            @page {
                /* ขนาดมาตรฐานบัตรนักศึกษา 3.375 x 2.125 นิ้ว (85.7 x 54 มม.) */
                size: 86mm 54mm;
                margin: 0;
            }
            body {
                font-family: "Noto Sans Lao", Arial, sans-serif;
                margin: 0;
                padding: 0;
                width: 86mm;
                height: 54mm;
                position: relative;
                background-color: white;
                overflow: hidden;
            }
            .card {
                width: 86mm;
                height: 54mm;
                background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
                color: white;
                position: relative;
                border-radius: 3mm;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            .card-inner {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                padding: 3mm;
                box-sizing: border-box;
            }
            .header {
                text-align: center;
                margin-bottom: 2mm;
                position: relative;
                z-index: 2;
            }
            .university-name {
                font-size: 12pt;
                font-weight: bold;
                margin-bottom: 1mm;
                text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            }
            .card-title {
                font-size: 8pt;
                text-transform: uppercase;
                letter-spacing: 1px;
                color: #f0f9ff;
            }
            .photo-container {
                float: left;
                width: 20mm;
                height: 25mm;
                background-color: white;
                border: 1px solid #ddd;
                margin-right: 3mm;
            }
            .photo {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .student-info {
                font-size: 7pt;
                line-height: 1.5;
            }
            .student-id {
                font-size: 10pt;
                font-weight: bold;
                margin-bottom: 1mm;
                color: #fff;
            }
            .student-name {
                font-weight: bold;
                font-size: 8pt;
                margin-bottom: 1mm;
            }
            .label {
                color: rgba(255, 255, 255, 0.8);
                font-size: 6pt;
                text-transform: uppercase;
            }
            .value {
                color: white;
            }
            .footer {
                position: absolute;
                bottom: 3mm;
                left: 3mm;
                right: 3mm;
                text-align: center;
                font-size: 5pt;
                color: rgba(255, 255, 255, 0.8);
            }
            .watermark {
                position: absolute;
                bottom: -10mm;
                right: -10mm;
                width: 50mm;
                height: 50mm;
                background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\' fill=\'%23ffffff15\'%3E%3Cpath d=\'M50 5A45 45 0 1 1 5 50 45 45 0 0 1 50 5m0-5a50 50 0 1 0 50 50A50 50 0 0 0 50 0z\'/%3E%3Cpath d=\'M47.41 52.59A2.59 2.59 0 1 1 50 50a2.59 2.59 0 0 1-2.59 2.59zm-5.18-15.54a2.59 2.59 0 1 0 2.59 2.59 2.59 2.59 0 0 0-2.59-2.59zm10.36 0a2.59 2.59 0 1 0 2.59 2.59 2.59 2.59 0 0 0-2.59-2.59zm-5.18-10.36a2.59 2.59 0 1 0 2.59 2.59 2.59 2.59 0 0 0-2.59-2.59z\'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                opacity: 0.2;
                z-index: 1;
            }
            .qr-code {
                position: absolute;
                bottom: 6mm;
                right: 3mm;
                width: 16mm;
                height: 16mm;
                background-color: white;
                padding: 1mm;
                border-radius: 1mm;
            }
            .signature-line {
                position: absolute;
                bottom: 10mm;
                left: 3mm;
                width: 25mm;
                border-top: 0.5px solid rgba(255, 255, 255, 0.6);
                text-align: center;
                font-size: 5pt;
                padding-top: 1mm;
            }
            .barcode {
                position: absolute;
                bottom: 3mm;
                left: 50%;
                transform: translateX(-50%);
                font-size: 8pt;
                letter-spacing: 2px;
                color: white;
                font-family: "Courier New", monospace;
            }
            @media print {
                body { margin: 0; }
                .page-break { page-break-before: always; }
            }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="watermark"></div>
            <div class="card-inner">
                <div class="header">
                    <div class="university-name">ມະຫາວິທະຍາໄລພຸດທະສາດສະໜາ</div>
                    <div class="card-title">ບັດນັກສຶກສາ</div>
                </div>
                
                <div class="photo-container">
                    ' . (empty($student->photo) ? 
                    '<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#f3f4f6;">
                        <span style="font-size:14pt; color:#9ca3af;">ບໍ່ມີຮູບ</span>
                     </div>' : 
                    '<img src="/students/public/uploads/' . htmlspecialchars($student->photo) . '" alt="Student Photo" class="photo">') . '
                </div>
                
                <div class="student-info">
                    <div class="student-id">' . htmlspecialchars($student->student_id) . '</div>
                    <div class="student-name">' . htmlspecialchars($student->first_name . ' ' . $student->last_name) . '</div>
                    
                    <div>
                        <span class="label">ເພດ:</span>
                        <span class="value">' . htmlspecialchars($student->gender) . '</span>
                    </div>
                    
                    <div>
                        <span class="label">ສາຂາວິຊາ:</span>
                        <span class="value">' . htmlspecialchars($student->getMajorName()) . '</span>
                    </div>
                    
                    <div>
                        <span class="label">ປີການສຶກສາ:</span>
                        <span class="value">' . htmlspecialchars($student->getYearName()) . '</span>
                    </div>
                    
                    <div>
                        <span class="label">ວັນອອກບັດ:</span>
                        <span class="value">' . date('d/m/Y') . '</span>
                    </div>
                </div>
                
                <div class="qr-code">
                    <img src="/students/public/qrcodes/' . htmlspecialchars($student->student_id) . '.png" alt="QR Code" width="100%" height="100%">
                </div>
                
                <div class="signature-line">
                    ຜູ້ອໍານວຍການ
                </div>
                
                <div class="footer">
                    ມະຫາວິທະຍາໄລພຸດທະສາດສະໜາ - ບັດນີ້ຄວນເກັບຮັກສາຢ່າງລະມັດລະວັງ
                </div>
                
                <div class="barcode">
                    *' . htmlspecialchars($student->student_id) . '*
                </div>
            </div>
        </div>
    </body>
    </html>';
    
    // สำหรับการทดสอบ จะใช้วิธีแปลง HTML เป็น PDF ด้วย browser
    header('Content-Type: text/html; charset=utf-8');
    echo $card_html;
    
    // JavaScript สำหรับพิมพ์เป็น PDF
    echo '<script>
        window.onload = function() {
            window.print();
        };
    </script>';
    exit();
}

function generatePDFHTML($data, $type, $title) {
    $html = '<!DOCTYPE html>
    <html lang="lo">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($title) . '</title>
         <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            body { 
                font-family: "Noto Sans Lao", Arial, sans-serif; 
                margin: 20px;
                font-size: 14px;
                line-height: 1.4;
            }
            .header { 
                text-align: center; 
                margin-bottom: 30px;
                border-bottom: 3px solid #2563eb;
                padding-bottom: 20px;
            }
            .title { 
                font-size: 28px; 
                font-weight: bold; 
                margin-bottom: 5px;
                color: #1f2937;
            }
            .subtitle { 
                font-size: 18px; 
                color: #6b7280;
                margin-bottom: 5px;
            }
            .date-info {
                font-size: 14px;
                color: #9ca3af;
            }
            .summary-section {
                background-color: #f3f4f6;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
                border-left: 4px solid #2563eb;
            }
            .summary-title {
                font-size: 18px;
                font-weight: bold;
                color: #1f2937;
                margin-bottom: 15px;
                display: flex;
                align-items: center;
            }
            .summary-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
            }
            .summary-item {
                background: white;
                padding: 15px;
                border-radius: 6px;
                border: 1px solid #e5e7eb;
            }
            .summary-label {
                font-size: 12px;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 5px;
            }
            .summary-value {
                font-size: 24px;
                font-weight: bold;
                color: #2563eb;
            }
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-top: 20px;
                font-size: 12px;
            }
            th, td { 
                border: 1px solid #e5e7eb; 
                padding: 10px 8px; 
                text-align: left;
            }
            th { 
                background-color: #f9fafb; 
                font-weight: bold;
                color: #374151;
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 0.3px;
            }
            .row-number {
                text-align: center;
                background-color: #f3f4f6;
                font-weight: bold;
                color: #6b7280;
                width: 40px;
            }
            tr:nth-child(even) {
                background-color: #f9fafb;
            }
            tr:hover {
                background-color: #eff6ff;
            }
            .footer {
                margin-top: 40px;
                padding-top: 20px;
                border-top: 2px solid #e5e7eb;
                text-align: right;
                font-size: 12px;
                color: #6b7280;
            }
            .signature-section {
                margin-top: 30px;
                display: flex;
                justify-content: space-between;
            }
            .signature-box {
                text-align: center;
                width: 200px;
            }
            .signature-line {
                border-top: 1px solid #000;
                margin-top: 60px;
                padding-top: 5px;
                font-size: 12px;
            }
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
                .page-break { page-break-before: always; }
            }
            .gender-male { color: #2563eb; }
            .gender-female { color: #dc2626; }
            .gender-monk { color: #f59e0b; font-weight: bold; }
            .gender-nun { color: #8b5cf6; font-weight: bold; }
            .gender-other { color: #6b7280; }
            .status-badge {
                padding: 2px 6px;
                border-radius: 4px;
                font-size: 10px;
                font-weight: bold;
            }
            .status-active {
                background-color: #dcfce7;
                color: #166534;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="title">' . htmlspecialchars($title) . '</div>
            <div class="subtitle">ລະບົບລົງທະບຽນການສຶກສາ</div>
            <div class="date-info">ວັນທີສ້າງລາຍງານ: ' . date('d/m/Y H:i') . ' ນ.</div>
        </div>';
    
    // เพิ่มสรุปข้อมูล
    $summary = generateSummary($data, $type);
    if (!empty($summary)) {
        $html .= '<div class="summary-section">
            <div class="summary-title">📊 ສະຫຼຸບຂໍ້ມູນ</div>
            <div class="summary-grid">' . $summary . '</div>
        </div>';
    }
    
    $html .= '<table>';
    
    $rowNumber = 1;
    
    switch ($type) {
        case 'students':
            $html .= '<thead>
                <tr>
                    <th class="row-number">ລໍາດັບ</th>
                    <th>ລະຫັດນັກສຶກສາ</th>
                    <th>ເພດ</th>
                    <th>ຊື່ ນາມສະກຸນ</th>                   
                    <th>ອີເມວ</th>
                    <th>ສາຂາວິຊາ</th>
                    <th>ປີການສຶກສາ</th>
                    <th>ທີ່ຢູ່</th>
                </tr>
            </thead>
            <tbody>';
            
            foreach ($data as $row) {
                // จัดการการแสดงเพศตามข้อมูลในฐานข้อมูล
                $genderClass = 'gender-other';
                $genderText = $row['gender'] ?? 'ບໍ່ໄດ້ລະບຸ';
                
                // กำหนด CSS class ตามเพศ
                switch($row['gender']) {
                    case 'ຊາຍ':
                        $genderClass = 'gender-male';
                        break;
                    case 'ຍິງ':
                        $genderClass = 'gender-female';
                        break;
                    case 'ພຣະ':
                        $genderClass = 'gender-monk';
                        break;
                    case 'ສ.ນ':
                        $genderClass = 'gender-nun';
                        break;
                    case 'ອຶ່ນໆ':
                    default:
                        $genderClass = 'gender-other';
                        break;
                }
                $address = trim(($row['village'] ?? '') . ' ' . ($row['district'] ?? '') . ' ' . ($row['province'] ?? ''));
                
                $html .= '<tr>
                    <td class="row-number">' . $rowNumber . '</td>
                    <td>' . htmlspecialchars($row['student_id'] ?? '') . '</td>
                    <td class="' . $genderClass . '">' . htmlspecialchars($genderText) . '</td>
                    <td><strong>' . htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) . '</strong></td>          
                    <td>' . htmlspecialchars($row['email'] ?? '-') . '</td>
                    <td>' . htmlspecialchars($row['major_name'] ?? '-') . '</td>
                    <td>' . htmlspecialchars($row['year'] ?? '-') . '</td>
                    <td>' . htmlspecialchars($address ?: '-') . '</td>
                </tr>';
                $rowNumber++;
            }
            break;
            
        case 'majors':
            $html .= '<thead>
                <tr>
                    <th class="row-number">ລໍາດັບ</th>
                    <th>ລະຫັດສາຂາ</th>
                    <th>ຊື່ສາຂາວິຊາ</th>
                    <th>ຄໍາອະທິບາຍ</th>
                </tr>
            </thead>
            <tbody>';
            
            foreach ($data as $row) {
                $html .= '<tr>
                    <td class="row-number">' . $rowNumber . '</td>
                    <td><strong>' . htmlspecialchars($row['id'] ?? '') . '</strong></td>
                    <td>' . htmlspecialchars($row['name'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['description'] ?? '-') . '</td>
                </tr>';
                $rowNumber++;
            }
            break;
            
        case 'years':
            $html .= '<thead>
                <tr>
                    <th class="row-number">ລໍາດັບ</th>
                    <th>ລະຫັດປີ</th>
                    <th>ປີການສຶກສາ</th>
                    <th>ຄໍາອະທິບາຍ</th>
                </tr>
            </thead>
            <tbody>';
            
            foreach ($data as $row) {
                $html .= '<tr>
                    <td class="row-number">' . $rowNumber . '</td>
                    <td><strong>' . htmlspecialchars($row['id'] ?? '') . '</strong></td>
                    <td>' . htmlspecialchars($row['year'] ?? '') . '</td>
                    <td>' . htmlspecialchars($row['description'] ?? '-') . '</td>
                </tr>';
                $rowNumber++;
            }
            break;
    }
    
    $html .= '</tbody>
        </table>
        
        <div class="signature-section">
            <div class="signature-box">
                <div>ຜູ້ກະກຽມລາຍງານ</div>
                <div class="signature-line">' . htmlspecialchars($_SESSION['full_name']) . '</div>
            </div>
            <div class="signature-box">
                <div>ຜູ້ອໍານວຍການ</div>
                <div class="signature-line">ລົງຊື່ແລະຕາໂປງ</div>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>ລະບົບລົງທະບຽນການສຶກສາ</strong></p>
            <p>ພິມເມື່ອ: ' . date('d/m/Y H:i:s') . ' ນ.</p>
            <p>ຈໍານວນລາຍການທັງໝົດ: ' . count($data) . ' ລາຍການ</p>
        </div>
    </body>
    </html>';
    
    return $html;
}

function generateSummary($data, $type) {
    $summary = '';
    
    switch ($type) {
        case 'students':
            $totalStudents = count($data);
            $maleCount = 0;
            $femaleCount = 0;
            $monkCount = 0;
            $nunCount = 0;
            $otherCount = 0;
            $majors = [];
            $years = [];
            
            foreach ($data as $row) {
                // นับเพศตามข้อมูลจริงในฐานข้อมูล
                $gender = $row['gender'] ?? 'ອຶ່ນໆ';
                switch ($gender) {
                    case 'ຊາຍ':
                        $maleCount++;
                        break;
                    case 'ຍິງ':
                        $femaleCount++;
                        break;
                    case 'ພຣະ':
                        $monkCount++;
                        break;
                    case 'ສ.ນ':
                        $nunCount++;
                        break;
                    case 'ອຶ່ນໆ':
                    default:
                        $otherCount++;
                        break;
                }
                
                // นับสาขา
                $majorName = $row['major_name'] ?? 'ບໍ່ລະບຸ';
                if (!isset($majors[$majorName])) {
                    $majors[$majorName] = 0;
                }
                $majors[$majorName]++;
                
                // นับปีการศึกษา
                $yearName = $row['year'] ?? 'ບໍ່ລະບຸ';
                if (!isset($years[$yearName])) {
                    $years[$yearName] = 0;
                }
                $years[$yearName]++;
            }
            
            $summary .= '<div class="summary-item">
                <div class="summary-label">ຈໍານວນນັກສຶກສາທັງໝົດ</div>
                <div class="summary-value">' . number_format($totalStudents) . ' ຄົນ</div>
            </div>';
            
            $summary .= '<div class="summary-item">
                <div class="summary-label">ນັກສຶກສາຊາຍ</div>
                <div class="summary-value" style="color: #2563eb;">' . number_format($maleCount) . ' ຄົນ</div>
            </div>';
            
            $summary .= '<div class="summary-item">
                <div class="summary-label">ນັກສຶກສາຍິງ</div>
                <div class="summary-value" style="color: #dc2626;">' . number_format($femaleCount) . ' ຄົນ</div>
            </div>';
            
            if ($monkCount > 0) {
                $summary .= '<div class="summary-item">
                    <div class="summary-label">ພຣະ</div>
                    <div class="summary-value" style="color: #f59e0b;">' . number_format($monkCount) . ' ຄົນ</div>
                </div>';
            }
            
            if ($nunCount > 0) {
                $summary .= '<div class="summary-item">
                    <div class="summary-label">ສ.ນ</div>
                    <div class="summary-value" style="color: #8b5cf6;">' . number_format($nunCount) . ' ຄົນ</div>
                </div>';
            }
            
            if ($otherCount > 0) {
                $summary .= '<div class="summary-item">
                    <div class="summary-label">ອຶ່ນໆ</div>
                    <div class="summary-value" style="color: #6b7280;">' . number_format($otherCount) . ' ຄົນ</div>
                </div>';
            }
            
            $summary .= '<div class="summary-item">
                <div class="summary-label">ຈໍານວນສາຂາວິຊາ</div>
                <div class="summary-value" style="color: #059669;">' . count($majors) . ' ສາຂາ</div>
            </div>';
            
            // แสดงสาขาที่มีนักศึกษามากที่สุด
            if (!empty($majors)) {
                arsort($majors);
                $topMajor = array_key_first($majors);
                $topMajorCount = $majors[$topMajor];
                
                $summary .= '<div class="summary-item">
                    <div class="summary-label">ສາຂາທີ່ມີນັກສຶກສາຫຼາຍສຸດ</div>
                    <div class="summary-value" style="color: #7c3aed; font-size: 14px;">' . htmlspecialchars($topMajor) . '<br><small>(' . $topMajorCount . ' ຄົນ)</small></div>
                </div>';
            }
            
            // แสดงปีการศึกษาที่มีนักศึกษามากที่สุด
            if (!empty($years)) {
                arsort($years);
                $topYear = array_key_first($years);
                $topYearCount = $years[$topYear];
                
                $summary .= '<div class="summary-item">
                    <div class="summary-label">ປີການສຶກສາທີ່ມີນັກສຶກສາຫຼາຍສຸດ</div>
                    <div class="summary-value" style="color: #ea580c; font-size: 14px;">' . htmlspecialchars($topYear) . '<br><small>(' . $topYearCount . ' ຄົນ)</small></div>
                </div>';
            }
            break;
            
        case 'majors':
            $totalMajors = count($data);
            
            $summary .= '<div class="summary-item">
                <div class="summary-label">ຈໍານວນສາຂາວິຊາທັງໝົດ</div>
                <div class="summary-value">' . number_format($totalMajors) . ' ສາຂາ</div>
            </div>';
            
            // นับสาขาที่มีคำอธิบาย
            $withDescription = 0;
            foreach ($data as $row) {
                if (!empty($row['description']) && $row['description'] !== '-') {
                    $withDescription++;
                }
            }
            
            $summary .= '<div class="summary-item">
                <div class="summary-label">ສາຂາທີ່ມີຄໍາອະທິບາຍ</div>
                <div class="summary-value" style="color: #059669;">' . number_format($withDescription) . ' ສາຂາ</div>
            </div>';
            
            $summary .= '<div class="summary-item">
                <div class="summary-label">ສາຂາທີ່ບໍ່ມີຄໍາອະທິບາຍ</div>
                <div class="summary-value" style="color: #dc2626;">' . number_format($totalMajors - $withDescription) . ' ສາຂາ</div>
            </div>';
            break;
            
        case 'years':
            $totalYears = count($data);
            
            $summary .= '<div class="summary-item">
                <div class="summary-label">ຈໍານວນປີການສຶກສາທັງໝົດ</div>
                <div class="summary-value">' . number_format($totalYears) . ' ປີ</div>
            </div>';
            
            // หาปีที่เก่าที่สุดและใหม่ที่สุด
            $years = array_column($data, 'year');
            if (!empty($years)) {
                $oldestYear = min($years);
                $newestYear = max($years);
                
                $summary .= '<div class="summary-item">
                    <div class="summary-label">ປີການສຶກສາເກົ່າສຸດ</div>
                    <div class="summary-value" style="color: #7c3aed;">' . htmlspecialchars($oldestYear) . '</div>
                </div>';
                
                $summary .= '<div class="summary-item">
                    <div class="summary-label">ປີການສຶກສາໃໝ່ສຸດ</div>
                    <div class="summary-value" style="color: #059669;">' . htmlspecialchars($newestYear) . '</div>
                </div>';
            }
            break;
    }
    
    return $summary;
}

function sanitizeFilename($filename) {
    // ลบตัวอักษรที่ไม่ต้องการในชื่อไฟล์
    $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $filename);
    return $filename;
}
?>

<?php
/**
 * File: proses_absen.php
 * Deskripsi: Memproses data absensi santri dan menyimpannya ke Google Sheets
 * 
 * CATATAN PENTING:
 * 1. File ini membutuhkan Google Client Library untuk PHP
 * 2. Install menggunakan Composer: composer require google/apiclient
 * 3. Siapkan file credentials.json dari Google Cloud Console
 * 4. Aktifkan Google Sheets API di Google Cloud Console
 */

// ========== DEBUGGING & ERROR REPORTING ==========
// Suppress deprecated warnings untuk compatibility dengan PHP 8+
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

// Set memory limit untuk mengatasi issue dengan Guzzle
ini_set('memory_limit', '256M');

// Set timeout untuk request HTTP
ini_set('default_socket_timeout', 30);

// Cegah akses langsung tanpa data POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ========== KONFIGURASI GOOGLE SHEETS ==========
/* 
 * CARA MENDAPATKAN SPREADSHEET ID:
 * 1. Buka Google Sheets di browser
 * 2. Lihat URL: https://docs.google.com/spreadsheets/d/SPREADSHEET_ID_DISINI/edit#gid=0
 * 3. Salin bagian SPREADSHEET_ID_DISINI
 * 4. Paste ke variabel $spreadsheetId di bawah
 */
$spreadsheetId = '1W6s0Osq_09tN3KKuXBFLj3noVlCNp7GRJT8f-MwIn04'; // GANTI DENGAN ID SPREADSHEET ANDA
$credentialsPath = 'credentials.json';     // Path ke file credentials.json Anda
$sheetName = 'Log Absensi';               // Nama sheet target (case-sensitive)

// Muat Composer Autoloader
if (!file_exists('vendor/autoload.php')) {
    die('ERROR: Composer autoloader tidak ditemukan. Jalankan: composer install');
}

// Suppress warnings dari Google Client Library untuk PHP 8+ compatibility
$previousErrorReporting = error_reporting();
error_reporting($previousErrorReporting & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);
require_once 'vendor/autoload.php';
error_reporting($previousErrorReporting & ~E_DEPRECATED & ~E_STRICT);

// Fungsi untuk validasi dan sanitasi input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk logging error (opsional)
function logError($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] ERROR: $message" . PHP_EOL;
    file_put_contents('error_log.txt', $logMessage, FILE_APPEND | LOCK_EX);
}

try {
    echo "<!-- DEBUG: Starting processing... -->\n";
    
    // ========== VALIDASI FILE CREDENTIALS ==========
    if (!file_exists($credentialsPath)) {
        throw new Exception("File credentials tidak ditemukan: $credentialsPath");
    }
    echo "<!-- DEBUG: Credentials file found -->\n";
    
    // ========== INISIALISASI GOOGLE CLIENT ==========
    echo "<!-- DEBUG: Initializing Google Client... -->\n";
    
    // Suppress warnings saat membuat Google Client
    $client = @new Google_Client();
    if (!$client) {
        throw new Exception('Gagal membuat Google Client. Pastikan library google/apiclient terinstall dengan benar.');
    }
    
    // Set configuration dengan error handling
    try {
        $client->setAuthConfig($credentialsPath);
        $client->addScope(Google_Service_Sheets::SPREADSHEETS);
        
        // Set HTTP client configuration untuk mengatasi issue timeout/curl
        $httpClient = new GuzzleHttp\Client([
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify' => false, // Disable SSL verification jika ada masalah
        ]);
        $client->setHttpClient($httpClient);
        
        echo "<!-- DEBUG: Google Client configured -->\n";
    } catch (Exception $e) {
        throw new Exception('Error saat konfigurasi Google Client: ' . $e->getMessage());
    }
    
    // Test koneksi dengan membuat service
    try {
        $sheetsService = @new Google_Service_Sheets($client);
        if (!$sheetsService) {
            throw new Exception('Gagal membuat Google Sheets Service');
        }
        echo "<!-- DEBUG: Google Sheets service created -->\n";
    } catch (Exception $e) {
        throw new Exception('Error saat membuat Sheets Service: ' . $e->getMessage());
    }
    
    // ========== VALIDASI DATA FORM ==========
    echo "<!-- DEBUG: Validating form data... -->\n";
    $kegiatan = sanitizeInput($_POST['kegiatan'] ?? '');
    $kelompok = sanitizeInput($_POST['kelompok'] ?? '');
    $dataSantri = $_POST['santri'] ?? [];

    // Validasi input utama
    if (empty($kegiatan)) {
        throw new Exception('Kegiatan harus dipilih');
    }
    
    if (empty($kelompok)) {
        throw new Exception('Kelompok santri harus dipilih');
    }

    if (empty($dataSantri)) {
        throw new Exception('Data santri tidak ditemukan');
    }
    
    echo "<!-- DEBUG: Form validation passed. Kegiatan: $kegiatan, Kelompok: $kelompok -->\n";

    // Siapkan data untuk Google Sheets
    $timestamp = date('Y-m-d H:i:s');
    $tanggal = date('Y-m-d');
    $dataUntukSheet = [];

    // Proses setiap santri
    foreach ($dataSantri as $santri) {
        $idSantri = sanitizeInput($santri['id'] ?? '');
        $namaSantri = sanitizeInput($santri['nama'] ?? '');
        $kamarSantri = sanitizeInput($santri['kamar'] ?? '');
        $statusKehadiran = sanitizeInput($santri['status'] ?? '');
        $keterangan = sanitizeInput($santri['keterangan'] ?? '');

        // HANYA SIMPAN JIKA STATUS BUKAN 'Hadir'
        // Ini untuk efisiensi agar log tidak penuh dengan data kehadiran
        if ($statusKehadiran !== 'Hadir' && !empty($statusKehadiran)) {
            $row = [
                $timestamp,           // Kolom A: Timestamp
                $idSantri,           // Kolom B: ID_Santri
                $namaSantri,         // Kolom C: Nama_Santri
                $kegiatan,           // Kolom D: Jenis_Kegiatan
                $statusKehadiran,    // Kolom E: Status_Kehadiran
                $keterangan,         // Kolom F: Keterangan
                $tanggal            // Kolom G: Tanggal
            ];
            
            $dataUntukSheet[] = $row;
        }
    }

    echo "<!-- DEBUG: Processed " . count($dataUntukSheet) . " non-present entries -->\n";
    
    // Jika tidak ada data yang perlu disimpan (semua santri hadir)
    if (empty($dataUntukSheet)) {
        echo "<!-- DEBUG: All students present, no data to send to sheets -->\n";
        
        // Log aktivitas untuk referensi
        $logContent = "\n=== ABSENSI LENGKAP ===\n";
        $logContent .= "Kegiatan: $kegiatan\n";
        $logContent .= "Kelompok: " . ucfirst($kelompok) . "\n";
        $logContent .= "Tanggal: $tanggal\n";
        $logContent .= "Timestamp: $timestamp\n";
        $logContent .= "Status: Semua santri hadir\n";
        $logContent .= "Jumlah santri: " . count($dataSantri) . "\n";
        $logContent .= str_repeat("=", 30) . "\n";
        
        file_put_contents('absensi_log.txt', $logContent, FILE_APPEND | LOCK_EX);
        
        header('Location: index.php?status=sukses&message=semua_hadir');
        exit;
    }

    // ========== KIRIM DATA KE GOOGLE SHEETS ==========
    echo "<!-- DEBUG: Preparing to send data to Google Sheets -->\n";
    
    // Siapkan range untuk menulis data (mulai dari baris kosong pertama)
    $range = $sheetName . '!A:G';
    echo "<!-- DEBUG: Target range: $range -->\n";
    
    // Test akses ke spreadsheet terlebih dahulu
    try {
        echo "<!-- DEBUG: Testing spreadsheet access... -->\n";
        
        // Set timeout dan retry untuk request
        $retries = 3;
        $testResponse = null;
        $lastError = null;
        
        for ($i = 0; $i < $retries; $i++) {
            try {
                // Suppress warnings dan coba akses spreadsheet
                $testResponse = @$sheetsService->spreadsheets->get($spreadsheetId);
                if ($testResponse) {
                    break; // Berhasil, keluar dari loop
                }
            } catch (Exception $e) {
                $lastError = $e;
                echo "<!-- DEBUG: Attempt " . ($i + 1) . " failed: " . $e->getMessage() . " -->\n";
                
                if ($i < $retries - 1) {
                    sleep(1); // Wait sebentar sebelum retry
                }
            }
        }
        
        if (!$testResponse) {
            $errorMsg = $lastError ? $lastError->getMessage() : 'Unknown error';
            throw new Exception("Tidak dapat mengakses spreadsheet dengan ID: $spreadsheetId setelah $retries percobaan. Error: $errorMsg");
        }
        
        $title = $testResponse->getProperties() ? $testResponse->getProperties()->getTitle() : 'Unknown';
        echo "<!-- DEBUG: Spreadsheet accessible. Title: $title -->\n";
        
    } catch (Exception $e) {
        throw new Exception("Tidak dapat mengakses spreadsheet dengan ID: $spreadsheetId. Error: " . $e->getMessage());
    }
    
    // Siapkan body request
    try {
        $valueRange = @new Google_Service_Sheets_ValueRange();
        if (!$valueRange) {
            throw new Exception('Gagal membuat ValueRange object');
        }
        
        $valueRange->setValues($dataUntukSheet);
        echo "<!-- DEBUG: Value range prepared with " . count($dataUntukSheet) . " rows -->\n";
    } catch (Exception $e) {
        throw new Exception('Error saat menyiapkan data: ' . $e->getMessage());
    }
    
    // Konfigurasi untuk append data
    $options = [
        'valueInputOption' => 'USER_ENTERED',  // Menggunakan USER_ENTERED untuk formatting otomatis
        'insertDataOption' => 'INSERT_ROWS'
    ];
    
    echo "<!-- DEBUG: Sending data to Google Sheets... -->\n";
    
    // Kirim data ke Google Sheets dengan error handling dan retry
    $response = null;
    $maxRetries = 3;
    $lastError = null;
    
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            echo "<!-- DEBUG: Attempt $attempt to send data... -->\n";
            
            // Suppress warnings dan coba kirim data
            $response = @$sheetsService->spreadsheets_values->append(
                $spreadsheetId,
                $range,
                $valueRange,
                $options
            );
            
            if ($response) {
                echo "<!-- DEBUG: Data sent successfully on attempt $attempt -->\n";
                break; // Berhasil, keluar dari loop
            }
            
        } catch (Exception $e) {
            $lastError = $e;
            echo "<!-- DEBUG: Attempt $attempt failed: " . $e->getMessage() . " -->\n";
            
            if ($attempt < $maxRetries) {
                sleep(2); // Wait 2 detik sebelum retry
                echo "<!-- DEBUG: Retrying in 2 seconds... -->\n";
            }
        }
    }
    
    if (!$response) {
        $errorMsg = $lastError ? $lastError->getMessage() : 'Unknown error';
        throw new Exception("Gagal mengirim data ke Google Sheets setelah $maxRetries percobaan. Error: $errorMsg");
    }
    
    // Verifikasi hasil
    if ($response->getUpdates()) {
        $updatedRows = $response->getUpdates()->getUpdatedRows();
        $updatedCells = $response->getUpdates()->getUpdatedCells();
        echo "<!-- DEBUG: SUCCESS! Added $updatedRows rows, $updatedCells cells to Google Sheets -->\n";
        
        // Log sukses ke file untuk audit trail
        $successLog = "\n=== GOOGLE SHEETS SUCCESS ===\n";
        $successLog .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
        $successLog .= "Kegiatan: $kegiatan\n";
        $successLog .= "Kelompok: $kelompok\n";
        $successLog .= "Rows Added: $updatedRows\n";
        $successLog .= "Cells Updated: $updatedCells\n";
        $successLog .= "Data: " . json_encode($dataUntukSheet) . "\n";
        $successLog .= str_repeat("=", 40) . "\n";
        file_put_contents('google_sheets_success.log', $successLog, FILE_APPEND | LOCK_EX);
        
    } else {
        throw new Exception('Tidak ada data yang berhasil ditulis ke Google Sheets');
    }

    // Backup lokal untuk audit trail
    $logFile = 'absensi_log.txt';
    $logContent = "\n=== ABSENSI " . strtoupper($kegiatan) . " - " . strtoupper($kelompok) . " ===\n";
    $logContent .= "Tanggal: $tanggal\n";
    $logContent .= "Timestamp: $timestamp\n";
    $logContent .= "Status: SENT TO GOOGLE SHEETS\n\n";
    
    foreach ($dataUntukSheet as $row) {
        $logContent .= "ID: {$row[1]} | Nama: {$row[2]} | Status: {$row[4]} | Keterangan: {$row[5]}\n";
    }
    $logContent .= "\n" . str_repeat("=", 50) . "\n";
    
    file_put_contents($logFile, $logContent, FILE_APPEND | LOCK_EX);

    // Redirect dengan pesan sukses
    $jumlahRecord = count($dataUntukSheet);
    header("Location: index.php?status=sukses&records=$jumlahRecord");
    exit;

} catch (Exception $e) {
    // ========== ERROR HANDLING & DEBUGGING ==========
    $errorMessage = $e->getMessage();
    $errorFile = $e->getFile();
    $errorLine = $e->getLine();
    $errorTrace = $e->getTraceAsString();
    
    // Log detailed error untuk debugging
    $detailedError = "\n=== ERROR DETAIL ===\n";
    $detailedError .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
    $detailedError .= "Message: $errorMessage\n";
    $detailedError .= "File: $errorFile\n";
    $detailedError .= "Line: $errorLine\n";
    $detailedError .= "POST Data: " . json_encode($_POST) . "\n";
    $detailedError .= "Trace:\n$errorTrace\n";
    $detailedError .= str_repeat("=", 40) . "\n";
    
    logError($detailedError);
    
    // Tampilkan error detail untuk debugging (JANGAN redirect)
    echo "<html><head><title>Error Debug</title></head><body>";
    echo "<h2>🚨 ERROR DETECTED - DEBUG MODE</h2>";
    echo "<div style='background: #ffebee; padding: 20px; border: 1px solid #f44336; border-radius: 4px;'>";
    echo "<h3>Error Message:</h3>";
    echo "<p><strong>$errorMessage</strong></p>";
    echo "<h3>File & Line:</h3>";
    echo "<p>$errorFile:$errorLine</p>";
    echo "<h3>POST Data:</h3>";
    echo "<pre>" . htmlspecialchars(json_encode($_POST, JSON_PRETTY_PRINT)) . "</pre>";
    
    // Cek kondisi spesifik
    echo "<h3>System Check:</h3>";
    echo "<ul>";
    echo "<li>Credentials file exists: " . (file_exists($credentialsPath) ? '✅ YES' : '❌ NO') . "</li>";
    echo "<li>Vendor autoload exists: " . (file_exists('vendor/autoload.php') ? '✅ YES' : '❌ NO') . "</li>";
    echo "<li>Spreadsheet ID: " . (empty($spreadsheetId) ? '❌ EMPTY' : "✅ $spreadsheetId") . "</li>";
    echo "<li>Sheet Name: " . (empty($sheetName) ? '❌ EMPTY' : "✅ $sheetName") . "</li>";
    echo "</ul>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Pastikan file <code>credentials.json</code> ada dan valid</li>";
    echo "<li>Pastikan Spreadsheet ID benar (ambil dari URL Google Sheets)</li>";
    echo "<li>Pastikan nama sheet 'Log Absensi' benar (case-sensitive)</li>";
    echo "<li>Pastikan service account punya akses edit ke Google Sheet</li>";
    echo "<li>Cek log file <code>error_log.txt</code> untuk detail lebih lanjut</li>";
    echo "</ol>";
    
    echo "</div>";
    echo "<br><a href='index.php'>← Kembali ke Halaman Utama</a>";
    echo "</body></html>";
    
    // Jangan redirect, biarkan error terlihat untuk debugging
    exit;
}

/**
 * PETUNJUK LENGKAP SETUP GOOGLE SHEETS API:
 * 
 * 1. GOOGLE CLOUD CONSOLE SETUP:
 *    - Buka https://console.cloud.google.com/
 *    - Buat project baru atau pilih project existing
 *    - Aktifkan Google Sheets API di Library
 *    - Buat Service Account di IAM & Admin > Service Accounts
 *    - Download file JSON credentials
 * 
 * 2. GOOGLE SHEETS SETUP:
 *    - Buat Google Sheets baru
 *    - Buat 3 sheet: "Santri Putra", "Santri Putri", "Log Absensi"
 *    - Berikan akses edit kepada email service account
 *    - Salin ID dari URL Google Sheets
 * 
 * 3. PHP SETUP:
 *    - Install Composer jika belum ada
 *    - Jalankan: composer require google/apiclient
 *    - Uncomment kode Google Sheets API di atas
 *    - Ganti path credentials dan spreadsheet ID
 * 
 * 4. STRUKTUR FOLDER:
 *    absensi/
 *    ├── index.php
 *    ├── proses_absen.php
 *    ├── style.css
 *    ├── credentials.json (file dari Google Cloud)
 *    ├── vendor/ (folder Composer)
 *    └── composer.json
 * 
 * 5. TESTING:
 *    - Sementara gunakan file log lokal untuk testing
 *    - Setelah Google Sheets API siap, comment bagian file log
 *    - Uncomment kode Google Sheets API
 */
?>
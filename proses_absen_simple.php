<?php
/**
 * File: proses_absen_simple.php
 * Deskripsi: Memproses data absensi santri menggunakan simple cURL (NO GUZZLE)
 * Solusi untuk menghindari compatibility issues dengan PHP 8+
 */

// ========== DEBUGGING & ERROR REPORTING ==========
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');
ini_set('memory_limit', '256M');

// Cegah akses langsung tanpa data POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ========== KONFIGURASI ==========
$spreadsheetId = '1W6s0Osq_09tN3KKuXBFLj3noVlCNp7GRJT8f-MwIn04';
$credentialsPath = 'credentials.json';
$sheetName = 'Log Absensi';

// ========== PRE-FLIGHT CHECKS ==========
if (!file_exists($credentialsPath)) {
    throw new Exception("File credentials.json tidak ditemukan! Silakan baca CREDENTIALS_SETUP_GUIDE.md untuk cara setup.");
}

// Include FIXED Google Sheets class
require_once 'google_sheets_fixed.php';

// Fungsi untuk validasi dan sanitasi input (untuk display HTML)
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk membersihkan input untuk Google Sheets (NO HTML encoding)
function cleanForSheets($data) {
    $data = trim($data);
    $data = stripslashes($data);
    // NO htmlspecialchars() - Google Sheets butuh data mentah!
    return $data;
}

// Fungsi untuk logging error
function logError($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] ERROR: $message" . PHP_EOL;
    file_put_contents('error_log.txt', $logMessage, FILE_APPEND | LOCK_EX);
}

try {
    echo "<!-- DEBUG: Starting simple processing... -->\n";
    
    // ========== VALIDASI DATA FORM ==========
    $kegiatan = sanitizeInput($_POST['kegiatan'] ?? '');
    $kelompok = sanitizeInput($_POST['kelompok'] ?? '');
    $dataSantri = $_POST['santri'] ?? [];

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

    // ========== PROSES DATA SANTRI ==========
    $timestamp = date('Y-m-d H:i:s');
    $tanggal = date('Y-m-d');
    $dataUntukSheet = [];

    foreach ($dataSantri as $santri) {
        // Gunakan cleanForSheets() untuk data yang akan dikirim ke Google Sheets
        $idSantri = cleanForSheets($santri['id'] ?? '');
        $namaSantri = cleanForSheets($santri['nama'] ?? '');
        $kamarSantri = cleanForSheets($santri['kamar'] ?? '');
        $statusKehadiran = cleanForSheets($santri['status'] ?? '');
        $keterangan = cleanForSheets($santri['keterangan'] ?? '');
        
        // Clean kegiatan juga (bisa mengandung karakter khusus)
        $kegiatanClean = cleanForSheets($kegiatan);

        // HANYA SIMPAN JIKA STATUS BUKAN 'Hadir'
        if ($statusKehadiran !== 'Hadir' && !empty($statusKehadiran)) {
            // Pastikan setiap elemen adalah string terpisah untuk kolom terpisah
            $row = [
                $timestamp,        // Kolom A: Timestamp
                $idSantri,         // Kolom B: ID Santri  
                $namaSantri,       // Kolom C: Nama Santri
                $kegiatanClean,    // Kolom D: Kegiatan (dibersihkan)
                $statusKehadiran,  // Kolom E: Status
                $keterangan,       // Kolom F: Keterangan
                $tanggal          // Kolom G: Tanggal
            ];
            
            $dataUntukSheet[] = $row;
        }
    }

    echo "<!-- DEBUG: Processed " . count($dataUntukSheet) . " non-present entries -->\n";
    
    // Jika tidak ada data yang perlu disimpan (semua santri hadir)
    if (empty($dataUntukSheet)) {
        echo "<!-- DEBUG: All students present, no data to send to sheets -->\n";
        
        // Log aktivitas
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

    // ========== KIRIM DATA KE GOOGLE SHEETS (SIMPLE cURL) ==========
    echo "<!-- DEBUG: Initializing Simple Google Sheets... -->\n";
    
    try {
        $sheets = new SimpleGoogleSheets($credentialsPath, $spreadsheetId);
        echo "<!-- DEBUG: Simple Google Sheets client created -->\n";
        
        // Test connection
        $testResult = $sheets->testConnection();
        echo "<!-- DEBUG: Connection test passed. Spreadsheet: " . ($testResult['properties']['title'] ?? 'Unknown') . " -->\n";
        
        // Debug: Tampilkan struktur data yang akan dikirim
        echo "<!-- DEBUG: Data structure for Google Sheets: -->\n";
        echo "<!-- Number of rows: " . count($dataUntukSheet) . " -->\n";
        if (!empty($dataUntukSheet)) {
            echo "<!-- First row columns: " . count($dataUntukSheet[0]) . " -->\n";
            echo "<!-- First row sample: " . json_encode($dataUntukSheet[0]) . " -->\n";
        }
        
        // Send data
        echo "<!-- DEBUG: Sending data to Google Sheets... -->\n";
        $result = $sheets->appendData($sheetName, $dataUntukSheet);
        
        if ($result && isset($result['updates'])) {
            $updatedRows = $result['updates']['updatedRows'] ?? count($dataUntukSheet);
            echo "<!-- DEBUG: SUCCESS! Added $updatedRows rows to Google Sheets -->\n";
            
            // Log sukses
            $successLog = "\n=== GOOGLE SHEETS SUCCESS ===\n";
            $successLog .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
            $successLog .= "Kegiatan: $kegiatan\n";
            $successLog .= "Kelompok: $kelompok\n";
            $successLog .= "Rows Added: $updatedRows\n";
            $successLog .= "Method: Simple cURL\n";
            $successLog .= "Data: " . json_encode($dataUntukSheet) . "\n";
            $successLog .= str_repeat("=", 40) . "\n";
            file_put_contents('google_sheets_success.log', $successLog, FILE_APPEND | LOCK_EX);
            
        } else {
            throw new Exception('Tidak ada data yang berhasil ditulis ke Google Sheets');
        }

    } catch (Exception $e) {
        throw new Exception('Error dengan Simple Google Sheets: ' . $e->getMessage());
    }

    // Backup lokal
    $logFile = 'absensi_log.txt';
    $logContent = "\n=== ABSENSI " . strtoupper($kegiatan) . " - " . strtoupper($kelompok) . " ===\n";
    $logContent .= "Tanggal: $tanggal\n";
    $logContent .= "Timestamp: $timestamp\n";
    $logContent .= "Status: SENT TO GOOGLE SHEETS (Simple cURL)\n\n";
    
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
    // ========== ERROR HANDLING ==========
    $errorMessage = $e->getMessage();
    $errorFile = $e->getFile();
    $errorLine = $e->getLine();
    
    // Log error
    $detailedError = "\n=== ERROR DETAIL (SIMPLE) ===\n";
    $detailedError .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
    $detailedError .= "Message: $errorMessage\n";
    $detailedError .= "File: $errorFile\n";
    $detailedError .= "Line: $errorLine\n";
    $detailedError .= "POST Data: " . json_encode($_POST) . "\n";
    $detailedError .= str_repeat("=", 40) . "\n";
    
    logError($detailedError);
    
    // Tampilkan error untuk debugging
    echo "<html><head><title>Error Debug - Simple Version</title></head><body>";
    echo "<h2>🚨 ERROR DETECTED - SIMPLE cURL VERSION</h2>";
    echo "<div style='background: #ffebee; padding: 20px; border: 1px solid #f44336; border-radius: 4px;'>";
    echo "<h3>Error Message:</h3>";
    echo "<p><strong>$errorMessage</strong></p>";
    echo "<h3>File & Line:</h3>";
    echo "<p>$errorFile:$errorLine</p>";
    
    echo "<h3>System Check:</h3>";
    echo "<ul>";
    $credExists = file_exists($credentialsPath);
    echo "<li>Credentials file exists: " . ($credExists ? '✅ YES' : '❌ NO - <strong>THIS IS THE PROBLEM!</strong>') . "</li>";
    echo "<li>cURL enabled: " . (function_exists('curl_init') ? '✅ YES' : '❌ NO') . "</li>";
    echo "<li>OpenSSL enabled: " . (function_exists('openssl_sign') ? '✅ YES' : '❌ NO') . "</li>";
    echo "<li>Spreadsheet ID: " . (empty($spreadsheetId) ? '❌ EMPTY' : "✅ $spreadsheetId") . "</li>";
    echo "<li>Sheet Name: " . (empty($sheetName) ? '❌ EMPTY' : "✅ $sheetName") . "</li>";
    echo "</ul>";
    
    if (!$credExists) {
        echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; margin: 10px 0;'>";
        echo "<h4>🚨 MISSING CREDENTIALS FILE!</h4>";
        echo "<p><strong>The main cause of 'Malformed URL' error is missing credentials.json</strong></p>";
        echo "<p>📖 <strong>Read:</strong> <a href='CREDENTIALS_SETUP_GUIDE.md' target='_blank'>CREDENTIALS_SETUP_GUIDE.md</a></p>";
        echo "<p>📝 <strong>Example:</strong> <a href='credentials.json.example' target='_blank'>credentials.json.example</a></p>";
        echo "<ol>";
        echo "<li>Create Google Service Account</li>";
        echo "<li>Download credentials.json</li>";
        echo "<li>Place in project root</li>";
        echo "<li>Share spreadsheet with service account email</li>";
        echo "</ol>";
        echo "</div>";
    }
    
    echo "<h3>Advantages of Fixed Version:</h3>";
    echo "<ul>";
    echo "<li>✅ No Guzzle dependency (no PHP 8+ compatibility issues)</li>";
    echo "<li>✅ Uses native cURL (more stable)</li>";
    echo "<li>✅ Direct JWT token generation</li>";
    echo "<li>✅ Better error handling & validation</li>";
    echo "<li>✅ Clear setup instructions</li>";
    echo "</ul>";
    
    echo "</div>";
    echo "<br><a href='index.php'>← Kembali ke Halaman Utama</a>";
    echo "<br><a href='test_simple_connection.php'>🔧 Test Simple Connection</a>";
    echo "</body></html>";
    
    exit;
}
?>
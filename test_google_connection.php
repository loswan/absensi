<?php
/**
 * File: test_google_connection.php
 * Deskripsi: Test koneksi ke Google Sheets API untuk debugging
 * CARA PAKAI: Buka http://localhost/absensi/test_google_connection.php di browser
 */

// Aktifkan error reporting tapi suppress deprecated warnings
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');
ini_set('default_socket_timeout', 30);

echo "<html><head><title>Test Google Sheets Connection</title></head><body>";
echo "<h2>🧪 Test Koneksi Google Sheets API</h2>";

// Konfigurasi (sama seperti di proses_absen.php)
$spreadsheetId = '112871308566065916323'; // GANTI dengan ID Anda
$credentialsPath = 'credentials.json';
$sheetName = 'Log Absensi';

echo "<div style='background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3>Konfigurasi:</h3>";
echo "<ul>";
echo "<li><strong>Spreadsheet ID:</strong> $spreadsheetId</li>";
echo "<li><strong>Credentials Path:</strong> $credentialsPath</li>";
echo "<li><strong>Sheet Name:</strong> $sheetName</li>";
echo "</ul>";
echo "</div>";

// Test 1: Cek file autoloader
echo "<h3>🔍 Test 1: Composer Autoloader</h3>";
if (file_exists('vendor/autoload.php')) {
    echo "✅ <code>vendor/autoload.php</code> ditemukan<br>";
    
    // Suppress warnings saat loading autoloader
    $previousErrorReporting = error_reporting();
    error_reporting($previousErrorReporting & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);
    require_once 'vendor/autoload.php';
    error_reporting($previousErrorReporting & ~E_DEPRECATED & ~E_STRICT);
    
    echo "✅ Autoloader berhasil dimuat (dengan warning suppression)<br>";
} else {
    echo "❌ <code>vendor/autoload.php</code> tidak ditemukan. Jalankan: <code>composer install</code><br>";
    exit;
}

// Test 2: Cek file credentials
echo "<h3>🔍 Test 2: Credentials File</h3>";
if (file_exists($credentialsPath)) {
    echo "✅ File <code>$credentialsPath</code> ditemukan<br>";
    
    // Coba baca dan validate JSON
    $credentialContent = file_get_contents($credentialsPath);
    $credentialJson = json_decode($credentialContent, true);
    
    if ($credentialJson) {
        echo "✅ File credentials valid JSON<br>";
        echo "📧 Service Account Email: " . ($credentialJson['client_email'] ?? 'Not found') . "<br>";
        echo "🆔 Project ID: " . ($credentialJson['project_id'] ?? 'Not found') . "<br>";
    } else {
        echo "❌ File credentials bukan JSON valid<br>";
    }
} else {
    echo "❌ File <code>$credentialsPath</code> tidak ditemukan<br>";
    echo "💡 <strong>Solusi:</strong> Download file JSON credentials dari Google Cloud Console<br>";
    exit;
}

// Test 3: Inisialisasi Google Client
echo "<h3>🔍 Test 3: Google Client</h3>";
try {
    $client = @new Google_Client();
    if (!$client) {
        throw new Exception('Gagal membuat Google Client object');
    }
    echo "✅ Google_Client berhasil dibuat (dengan warning suppression)<br>";
    
    $client->setAuthConfig($credentialsPath);
    echo "✅ Auth config berhasil di-set<br>";
    
    $client->addScope(Google_Service_Sheets::SPREADSHEETS);
    echo "✅ Scope Google Sheets berhasil ditambahkan<br>";
    
    // Set HTTP client dengan konfigurasi untuk mengatasi Guzzle issues
    try {
        $httpClient = new GuzzleHttp\Client([
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify' => false,
        ]);
        $client->setHttpClient($httpClient);
        echo "✅ HTTP Client dikonfigurasi untuk kompatibilitas<br>";
    } catch (Exception $e) {
        echo "⚠️ Warning: Tidak dapat mengkonfigurasi HTTP Client: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error saat inisialisasi Google Client: " . $e->getMessage() . "<br>";
    exit;
}

// Test 4: Google Sheets Service
echo "<h3>🔍 Test 4: Google Sheets Service</h3>";
try {
    $sheetsService = @new Google_Service_Sheets($client);
    if (!$sheetsService) {
        throw new Exception('Gagal membuat Google Sheets Service object');
    }
    echo "✅ Google Sheets Service berhasil dibuat (dengan warning suppression)<br>";
} catch (Exception $e) {
    echo "❌ Error saat membuat Sheets Service: " . $e->getMessage() . "<br>";
    exit;
}

// Test 5: Akses Spreadsheet
echo "<h3>🔍 Test 5: Akses Spreadsheet</h3>";
try {
    $spreadsheet = $sheetsService->spreadsheets->get($spreadsheetId);
    echo "✅ Berhasil mengakses spreadsheet<br>";
    echo "📊 Judul Spreadsheet: <strong>" . $spreadsheet->getProperties()->getTitle() . "</strong><br>";
    
    // List semua sheet
    $sheets = $spreadsheet->getSheets();
    echo "📋 Daftar Sheet yang tersedia:<br>";
    echo "<ul>";
    foreach ($sheets as $sheet) {
        $title = $sheet->getProperties()->getTitle();
        echo "<li>$title" . ($title === $sheetName ? " ✅ <em>(Target)</em>" : "") . "</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "❌ Error saat mengakses spreadsheet: " . $e->getMessage() . "<br>";
    echo "💡 <strong>Kemungkinan penyebab:</strong><br>";
    echo "<ul>";
    echo "<li>Spreadsheet ID salah</li>";
    echo "<li>Service account belum diberi akses ke spreadsheet</li>";
    echo "<li>Spreadsheet tidak ada atau terhapus</li>";
    echo "</ul>";
    exit;
}

// Test 6: Baca data dari sheet
echo "<h3>🔍 Test 6: Baca Data Sheet</h3>";
try {
    $range = $sheetName . '!A1:G1'; // Baca header
    $response = $sheetsService->spreadsheets_values->get($spreadsheetId, $range);
    $values = $response->getValues();
    
    if (!empty($values)) {
        echo "✅ Berhasil membaca data dari sheet '$sheetName'<br>";
        echo "📊 Header columns: " . implode(', ', $values[0]) . "<br>";
    } else {
        echo "⚠️ Sheet '$sheetName' kosong atau tidak ada data<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error saat membaca sheet: " . $e->getMessage() . "<br>";
    echo "💡 Pastikan nama sheet '$sheetName' benar (case-sensitive)<br>";
}

// Test 7: Test Write (simulasi)
echo "<h3>🔍 Test 7: Test Write Data</h3>";
try {
    $testData = [
        [
            date('Y-m-d H:i:s'),
            'TEST001',
            'Test Santri',
            'Test Kegiatan',
            'Sakit',
            'Testing koneksi',
            date('Y-m-d')
        ]
    ];
    
    $range = $sheetName . '!A:G';
    $valueRange = new Google_Service_Sheets_ValueRange();
    $valueRange->setValues($testData);
    
    $options = [
        'valueInputOption' => 'USER_ENTERED',
        'insertDataOption' => 'INSERT_ROWS'
    ];
    
    $response = $sheetsService->spreadsheets_values->append(
        $spreadsheetId,
        $range,
        $valueRange,
        $options
    );
    
    if ($response->getUpdates()) {
        $updatedRows = $response->getUpdates()->getUpdatedRows();
        echo "✅ <strong>SUCCESS!</strong> Berhasil menulis $updatedRows baris ke Google Sheets<br>";
        echo "🎉 <strong>Integrasi Google Sheets berfungsi dengan baik!</strong><br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error saat menulis data: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>📝 Kesimpulan:</h3>";
echo "<p>Jika semua test di atas menunjukkan ✅, maka integrasi Google Sheets Anda sudah siap!</p>";
echo "<p><a href='index.php'>← Kembali ke Aplikasi Absensi</a></p>";

echo "</body></html>";
?>
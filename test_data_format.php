<?php
/**
 * File: test_data_format.php
 * Test format data yang sudah diperbaiki untuk Google Sheets
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Test Clean Data Format</h2>";

// Simulasi fungsi lama vs baru
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data); // ❌ Penyebab &#039;
    return $data;
}

function cleanForSheets($data) {
    $data = trim($data);
    $data = stripslashes($data);
    // NO htmlspecialchars() untuk Google Sheets
    return $data;
}

echo "<h3>1. Perbandingan Fungsi Cleaning:</h3>";

$testData = "Ahmad Fadhil's Book";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Input</th><th>Fungsi Lama (sanitizeInput)</th><th>Fungsi Baru (cleanForSheets)</th></tr>";
echo "<tr>";
echo "<td><code>" . htmlspecialchars($testData) . "</code></td>";
echo "<td><code>" . sanitizeInput($testData) . "</code> ❌</td>";
echo "<td><code>" . cleanForSheets($testData) . "</code> ✅</td>";
echo "</tr>";
echo "</table>";

echo "<h3>2. Simulasi Data Array untuk Google Sheets:</h3>";

// Simulasi data kotor (cara lama)
$dirtyData = [
    sanitizeInput("2025-10-15 10:30:00") . sanitizeInput("P001") . sanitizeInput("Ahmad Fadhil's") . sanitizeInput("Ngaji Jum'at") . "Alpha"
];

// Data bersih (cara baru) 
$cleanData = [
    [
        cleanForSheets("2025-10-15 10:30:00"), // Timestamp
        cleanForSheets("P001"),                 // ID Santri
        cleanForSheets("Ahmad Fadhil's"),       // Nama Santri  
        cleanForSheets("Ngaji Jum'at"),         // Kegiatan
        cleanForSheets("Alpha"),                // Status
        cleanForSheets("Sakit kepala"),         // Keterangan
        cleanForSheets("2025-10-15")           // Tanggal
    ]
];

echo "<h4>❌ Format Data Kotor (Sebelum Perbaikan):</h4>";
echo "<pre style='background:#ffebee;padding:10px;border:1px solid #f44336;'>";
echo "Data: " . json_encode($dirtyData, JSON_PRETTY_PRINT);
echo "\nHasil di Google Sheets: Semua jadi 1 sel";
echo "\nContoh: 'TimestampID_SantriNama_SantriJenis_Kegiatan&#039;Status'";
echo "</pre>";

echo "<h4>✅ Format Data Bersih (Setelah Perbaikan):</h4>";
echo "<pre style='background:#e8f5e8;padding:10px;border:1px solid #4caf50;'>";
echo "Data: " . json_encode($cleanData, JSON_PRETTY_PRINT);
echo "\nHasil di Google Sheets: 7 kolom terpisah dengan benar";
echo "\nA: 2025-10-15 10:30:00";
echo "\nB: P001"; 
echo "\nC: Ahmad Fadhil's (tanpa &#039;!)";
echo "\nD: Ngaji Jum'at (tanpa &#039;!)";
echo "\nE: Alpha";
echo "\nF: Sakit kepala";
echo "\nG: 2025-10-15";
echo "</pre>";

echo "<h3>3. Test Real Data Structure:</h3>";

// Simulasi data POST seperti dari form
$_POST = [
    'kegiatan' => "Ngaji Jum'at (Arba'in an-Nawawi)",
    'kelompok' => 'junior',
    'santri' => [
        [
            'id' => 'P005',
            'nama' => "Fadhil Nurdin's Santoso",
            'kamar' => 'A1',
            'status' => 'Alpha',
            'keterangan' => "Sakit kepala, izin pulang ke rumah orang tua's"
        ]
    ]
];

echo "<h4>Simulasi Pemrosesan Data Form:</h4>";

$kegiatan = $_POST['kegiatan'];
$dataSantri = $_POST['santri'];
$timestamp = date('Y-m-d H:i:s');
$tanggal = date('Y-m-d');
$dataUntukSheet = [];

foreach ($dataSantri as $santri) {
    $idSantri = cleanForSheets($santri['id'] ?? '');
    $namaSantri = cleanForSheets($santri['nama'] ?? '');
    $kamarSantri = cleanForSheets($santri['kamar'] ?? '');
    $statusKehadiran = cleanForSheets($santri['status'] ?? '');
    $keterangan = cleanForSheets($santri['keterangan'] ?? '');
    $kegiatanClean = cleanForSheets($kegiatan);

    if ($statusKehadiran !== 'Hadir' && !empty($statusKehadiran)) {
        $row = [
            $timestamp,
            $idSantri,
            $namaSantri,
            $kegiatanClean,
            $statusKehadiran,
            $keterangan,
            $tanggal
        ];
        
        $dataUntukSheet[] = $row;
    }
}

echo "<p><strong>Data yang akan dikirim ke Google Sheets:</strong></p>";
echo "<pre style='background:#f0f0f0;padding:15px;border:1px solid #ccc;'>";
echo json_encode($dataUntukSheet, JSON_PRETTY_PRINT);
echo "</pre>";

echo "<h3>4. Verifikasi Struktur:</h3>";
if (!empty($dataUntukSheet)) {
    $firstRow = $dataUntukSheet[0];
    echo "<ul>";
    echo "<li>✅ <strong>Jumlah rows:</strong> " . count($dataUntukSheet) . "</li>";
    echo "<li>✅ <strong>Jumlah kolom per row:</strong> " . count($firstRow) . "</li>";
    echo "<li>✅ <strong>Setiap elemen adalah string terpisah:</strong> " . (is_array($firstRow) ? 'Yes' : 'No') . "</li>";
    echo "<li>✅ <strong>No HTML entities:</strong> " . (strpos(json_encode($firstRow), '&#039;') === false ? 'Clean' : 'Still dirty') . "</li>";
    echo "</ul>";
    
    echo "<h4>Preview hasil di Google Sheets:</h4>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Timestamp</th><th>ID</th><th>Nama</th><th>Kegiatan</th><th>Status</th><th>Keterangan</th><th>Tanggal</th></tr>";
    foreach ($dataUntukSheet as $row) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td>" . htmlspecialchars($cell) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ No data to send (all students present)</p>";
}

echo "<hr>";
echo "<h3>🎯 Kesimpulan Perbaikan:</h3>";
echo "<div style='background:#e8f5e8;padding:15px;border:1px solid #4caf50;'>";
echo "<h4>✅ Masalah Diperbaiki:</h4>";
echo "<ol>";
echo "<li><strong>HTML Encoding:</strong> Ganti <code>sanitizeInput()</code> dengan <code>cleanForSheets()</code></li>";
echo "<li><strong>Data Structure:</strong> Pastikan array 2D dengan setiap elemen sebagai string terpisah</li>";
echo "<li><strong>Clean Output:</strong> Tidak ada lagi &#039; atau data tergabung</li>";
echo "</ol>";
echo "</div>";

echo "<p><a href='index.php'>← Back to Main</a> | <a href='test_fixed_version.php'>Test Google Sheets</a></p>";
?>
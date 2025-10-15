<?php
/**
 * File: test_dynamic_integration.php
 * Test integrasi dinamis Google Sheets dengan data santri
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Test Dynamic Google Sheets Integration</h2>";

// Konfigurasi
$spreadsheetId = '1W6s0Osq_09tN3KKuXBFLj3noVlCNp7GRJT8f-MwIn04';
$credentialsPath = 'credentials.json';

echo "<h3>1. Pre-flight Checks</h3>";
echo "<ul>";
echo "<li>Credentials file: " . (file_exists($credentialsPath) ? '✅ EXISTS' : '❌ MISSING') . "</li>";
echo "<li>Spreadsheet ID: ✅ $spreadsheetId</li>";
echo "<li>Target sheets: Santri Putra, Santri Putri</li>";
echo "</ul>";

if (!file_exists($credentialsPath)) {
    echo "<div style='background:#ffebee;padding:15px;border:1px solid #f44336;'>";
    echo "<h4>❌ Credentials Required</h4>";
    echo "<p>File credentials.json tidak ditemukan.</p>";
    echo "<p>Silakan buat file credentials.json sesuai panduan di CREDENTIALS_SETUP_GUIDE.md</p>";
    echo "</div>";
    exit;
}

try {
    echo "<h3>2. Initialize Google Sheets Client</h3>";
    
    require_once 'google_sheets_fixed.php';
    $sheets = new SimpleGoogleSheets($credentialsPath, $spreadsheetId);
    
    echo "<p>✅ Google Sheets client created successfully</p>";
    echo "<p>Access token obtained: " . (strlen($sheets->accessToken) > 10 ? 'YES' : 'NO') . "</p>";
    
    echo "<h3>3. Test Santri Data Retrieval</h3>";
    
    // Fungsi untuk mengambil data santri (sama seperti di index.php)
    function getSantriData($sheets, $sheetName) {
        try {
            $url = sprintf(
                'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s!A2:C',
                $sheets->spreadsheetId,
                rawurlencode($sheetName)
            );
            
            echo "<p><strong>Request URL for '$sheetName':</strong><br><code>$url</code></p>";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $sheets->accessToken,
                    'User-Agent: SantriAbsensiApp/1.0'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            echo "<p>HTTP Response Code: $httpCode</p>";
            
            if ($curlError) {
                throw new Exception("cURL error: $curlError");
            }
            
            if ($httpCode !== 200) {
                echo "<p>❌ Error Response: $response</p>";
                throw new Exception("HTTP error $httpCode");
            }
            
            $result = json_decode($response, true);
            return isset($result['values']) ? $result['values'] : [];
            
        } catch (Exception $e) {
            echo "<p>❌ Error: " . $e->getMessage() . "</p>";
            return [];
        }
    }
    
    // Test ambil data santri putra
    echo "<h4>📋 Santri Putra</h4>";
    $santriPutra = getSantriData($sheets, 'Santri Putra');
    echo "<p><strong>Total records:</strong> " . count($santriPutra) . "</p>";
    
    if (count($santriPutra) > 0) {
        echo "<p>✅ Data berhasil diambil</p>";
        echo "<p><strong>Sample data (first 3 rows):</strong></p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nama</th><th>Kamar</th></tr>";
        for ($i = 0; $i < min(3, count($santriPutra)); $i++) {
            $row = $santriPutra[$i];
            echo "<tr>";
            echo "<td>" . ($row[0] ?? 'N/A') . "</td>";
            echo "<td>" . ($row[1] ?? 'N/A') . "</td>";
            echo "<td>" . ($row[2] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>⚠️ Tidak ada data atau sheet 'Santri Putra' tidak ditemukan</p>";
    }
    
    // Test ambil data santri putri
    echo "<h4>📋 Santri Putri</h4>";
    $santriPutri = getSantriData($sheets, 'Santri Putri');
    echo "<p><strong>Total records:</strong> " . count($santriPutri) . "</p>";
    
    if (count($santriPutri) > 0) {
        echo "<p>✅ Data berhasil diambil</p>";
        echo "<p><strong>Sample data (first 3 rows):</strong></p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nama</th><th>Kamar</th></tr>";
        for ($i = 0; $i < min(3, count($santriPutri)); $i++) {
            $row = $santriPutri[$i];
            echo "<tr>";
            echo "<td>" . ($row[0] ?? 'N/A') . "</td>";
            echo "<td>" . ($row[1] ?? 'N/A') . "</td>";
            echo "<td>" . ($row[2] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>⚠️ Tidak ada data atau sheet 'Santri Putri' tidak ditemukan</p>";
    }
    
    echo "<h3>4. JSON Format Test</h3>";
    
    $jsonSantriPutra = json_encode($santriPutra);
    $jsonSantriPutri = json_encode($santriPutri);
    
    echo "<p><strong>JSON Santri Putra (truncated):</strong></p>";
    echo "<pre style='background:#f0f0f0;padding:10px;max-height:150px;overflow:auto;'>";
    echo htmlspecialchars(substr($jsonSantriPutra, 0, 500)) . (strlen($jsonSantriPutra) > 500 ? '...' : '');
    echo "</pre>";
    
    echo "<p><strong>JSON Santri Putri (truncated):</strong></p>";
    echo "<pre style='background:#f0f0f0;padding:10px;max-height:150px;overflow:auto;'>";
    echo htmlspecialchars(substr($jsonSantriPutri, 0, 500)) . (strlen($jsonSantriPutri) > 500 ? '...' : '');
    echo "</pre>";
    
    echo "<h3>5. Integration Status</h3>";
    
    $totalSantri = count($santriPutra) + count($santriPutri);
    
    if ($totalSantri > 0) {
        echo "<div style='background:#e8f5e8;padding:15px;border:1px solid #4caf50;'>";
        echo "<h4>✅ INTEGRATION SUCCESSFUL!</h4>";
        echo "<ul>";
        echo "<li><strong>Santri Putra:</strong> " . count($santriPutra) . " records</li>";
        echo "<li><strong>Santri Putri:</strong> " . count($santriPutri) . " records</li>";
        echo "<li><strong>Total:</strong> $totalSantri santri</li>";
        echo "<li><strong>Status:</strong> Ready for dynamic display</li>";
        echo "</ul>";
        echo "<p><strong>Your index.php will now load santri data dynamically from Google Sheets!</strong></p>";
        echo "</div>";
    } else {
        echo "<div style='background:#fff3cd;padding:15px;border:1px solid #ffc107;'>";
        echo "<h4>⚠️ NO DATA FOUND</h4>";
        echo "<p>Tidak ada data santri ditemukan. Pastikan:</p>";
        echo "<ul>";
        echo "<li>Sheet 'Santri Putra' dan 'Santri Putri' ada di spreadsheet</li>";
        echo "<li>Data dimulai dari row 2 (row 1 untuk header)</li>";
        echo "<li>Format: Kolom A=ID, B=Nama, C=Kamar</li>";
        echo "<li>Service account memiliki akses ke spreadsheet</li>";
        echo "</ul>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background:#ffebee;padding:15px;border:1px solid #f44336;'>";
    echo "<h4>❌ Integration Error</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>📋 Next Steps:</h3>";
echo "<ol>";
echo "<li><strong>If successful:</strong> Visit <a href='index.php'>index.php</a> to see dynamic santri data</li>";
echo "<li><strong>If no data:</strong> Add santri data to your Google Sheets</li>";
echo "<li><strong>If errors:</strong> Check credentials and spreadsheet access</li>";
echo "</ol>";

echo "<p><a href='index.php'>← Back to Main</a> | <a href='test_fixed_version.php'>Test Google Sheets</a></p>";
?>
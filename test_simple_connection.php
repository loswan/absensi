<?php
/**
 * File: test_simple_connection.php  
 * Deskripsi: Test koneksi Google Sheets menggunakan Simple cURL (NO GUZZLE)
 * Solusi untuk PHP 8+ compatibility issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');

echo "<html><head><title>Test Simple Google Sheets Connection</title></head><body>";
echo "<h2>🔧 Test Simple Connection (cURL Only - No Guzzle)</h2>";

$spreadsheetId = '1W6s0Osq_09tN3KKuXBFLj3noVlCNp7GRJT8f-MwIn04';
$credentialsPath = 'credentials.json';
$sheetName = 'Log Absensi';

echo "<div style='background: #e8f5e8; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3>✅ Advantages of Simple Version:</h3>";
echo "<ul>";
echo "<li>No Guzzle HTTP dependency (avoids PHP 8+ issues)</li>";
echo "<li>Uses native cURL (more stable and compatible)</li>";
echo "<li>Direct JWT token generation</li>";
echo "<li>Simpler error handling</li>";
echo "<li>No vendor dependencies conflicts</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3>Konfigurasi:</h3>";
echo "<ul>";
echo "<li><strong>Spreadsheet ID:</strong> $spreadsheetId</li>";
echo "<li><strong>Credentials Path:</strong> $credentialsPath</li>";
echo "<li><strong>Sheet Name:</strong> $sheetName</li>";
echo "</ul>";
echo "</div>";

// Test 1: Cek requirements
echo "<h3>🔍 Test 1: System Requirements</h3>";
$requirements = [
    'cURL' => function_exists('curl_init'),
    'JSON' => function_exists('json_decode'),
    'OpenSSL' => function_exists('openssl_sign'),
    'Base64' => function_exists('base64_encode')
];

$allGood = true;
foreach ($requirements as $req => $available) {
    if ($available) {
        echo "✅ $req available<br>";
    } else {
        echo "❌ $req NOT available<br>";
        $allGood = false;
    }
}

if (!$allGood) {
    echo "<p style='color: red;'>❌ System requirements not met. Cannot proceed.</p>";
    exit;
}

// Test 2: Cek credentials file  
echo "<h3>🔍 Test 2: Credentials File</h3>";
if (file_exists($credentialsPath)) {
    echo "✅ File <code>$credentialsPath</code> ditemukan<br>";
    
    $credentialContent = file_get_contents($credentialsPath);
    $credentialJson = json_decode($credentialContent, true);
    
    if ($credentialJson) {
        echo "✅ File credentials valid JSON<br>";
        echo "📧 Service Account Email: " . ($credentialJson['client_email'] ?? 'Not found') . "<br>";
        echo "🆔 Project ID: " . ($credentialJson['project_id'] ?? 'Not found') . "<br>";
        
        if (isset($credentialJson['private_key'])) {
            echo "✅ Private key found<br>";
        } else {
            echo "❌ Private key not found<br>";
            exit;
        }
    } else {
        echo "❌ File credentials bukan JSON valid<br>";
        exit;
    }
} else {
    echo "❌ File <code>$credentialsPath</code> tidak ditemukan<br>";
    exit;
}

// Test 3: Load Simple Google Sheets class
echo "<h3>🔍 Test 3: Simple Google Sheets Class</h3>";
if (file_exists('google_sheets_simple.php')) {
    echo "✅ Simple Google Sheets class file found<br>";
    require_once 'google_sheets_simple.php';
    echo "✅ Class loaded successfully<br>";
} else {
    echo "❌ google_sheets_simple.php not found<br>";
    exit;
}

// Test 4: Initialize class
echo "<h3>🔍 Test 4: Initialize Simple Google Sheets</h3>";
try {
    $sheets = new SimpleGoogleSheets($credentialsPath, $spreadsheetId);
    echo "✅ SimpleGoogleSheets object created successfully<br>";
} catch (Exception $e) {
    echo "❌ Error creating SimpleGoogleSheets: " . $e->getMessage() . "<br>";
    exit;
}

// Test 5: Test connection
echo "<h3>🔍 Test 5: Test Connection to Spreadsheet</h3>";
try {
    echo "⏳ Testing connection...<br>";
    $result = $sheets->testConnection();
    
    if ($result && isset($result['properties'])) {
        echo "✅ <strong>Connection successful!</strong><br>";
        echo "📊 Spreadsheet Title: <strong>" . $result['properties']['title'] . "</strong><br>";
        
        if (isset($result['sheets'])) {
            echo "📋 Available Sheets:<br>";
            echo "<ul>";
            foreach ($result['sheets'] as $sheet) {
                $title = $sheet['properties']['title'];
                echo "<li>$title" . ($title === $sheetName ? " ✅ <em>(Target)</em>" : "") . "</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "❌ Connection failed - unexpected response format<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Connection error: " . $e->getMessage() . "<br>";
    echo "<p style='color: orange;'>💡 <strong>Possible causes:</strong></p>";
    echo "<ul>";
    echo "<li>Incorrect Spreadsheet ID</li>";
    echo "<li>Service account doesn't have access to the spreadsheet</li>";
    echo "<li>Network/firewall issues</li>";
    echo "<li>Credentials expired or invalid</li>";
    echo "</ul>";
}

// Test 6: Test write data
echo "<h3>🔍 Test 6: Test Write Data</h3>";
try {
    $testData = [
        [
            date('Y-m-d H:i:s'),
            'TEST001',
            'Test Simple Connection',
            'Test Kegiatan',
            'Sakit',
            'Testing simple cURL method',
            date('Y-m-d')
        ]
    ];
    
    echo "⏳ Attempting to write test data...<br>";
    $writeResult = $sheets->appendData($sheetName, $testData);
    
    if ($writeResult && isset($writeResult['updates'])) {
        $updatedRows = $writeResult['updates']['updatedRows'] ?? 1;
        echo "✅ <strong>WRITE SUCCESS!</strong> Added $updatedRows row(s) to Google Sheets<br>";
        echo "🎉 <strong>Simple cURL method is working perfectly!</strong><br>";
        
        echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb; border-radius: 5px;'>";
        echo "<h4>🎯 RECOMMENDED ACTION:</h4>";
        echo "<p>The simple cURL method is working! Update your main form to use:</p>";
        echo "<p><strong>Action:</strong> <code>proses_absen_simple.php</code> instead of <code>proses_absen.php</code></p>";
        echo "</div>";
        
    } else {
        echo "⚠️ Write completed but unexpected response format<br>";
        echo "Response: " . json_encode($writeResult) . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Write error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>📝 Summary:</h3>";
echo "<p>Simple cURL method bypasses all Guzzle/PHP 8+ compatibility issues by using native PHP functions only.</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>If all tests above show ✅, the simple method is ready to use</li>";
echo "<li>Update your form action to use <code>proses_absen_simple.php</code></li>";
echo "<li>No more Guzzle/Google Client library issues!</li>";
echo "</ol>";

echo "<p><a href='index.php'>← Back to Main App</a> | <a href='update_form_action.php'>🔧 Auto-Update Form</a></p>";

echo "</body></html>";
?>
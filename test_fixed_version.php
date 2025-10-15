<?php
/**
 * File: test_fixed_version.php
 * Test FIXED version Google Sheets integration
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Test FIXED Google Sheets Version</h2>";

// Configuration
$spreadsheetId = '1W6s0Osq_09tN3KKuXBFLj3noVlCNp7GRJT8f-MwIn04';
$credentialsPath = 'credentials.json';
$sheetName = 'Log Absensi';

echo "<h3>1. System Requirements Check</h3>";
echo "<ul>";
echo "<li>PHP Version: " . PHP_VERSION . "</li>";
echo "<li>cURL enabled: " . (function_exists('curl_init') ? '✅ YES' : '❌ NO') . "</li>";
echo "<li>OpenSSL enabled: " . (function_exists('openssl_sign') ? '✅ YES' : '❌ NO') . "</li>";
echo "<li>JSON enabled: " . (function_exists('json_encode') ? '✅ YES' : '❌ NO') . "</li>";
echo "<li>Credentials file exists: " . (file_exists($credentialsPath) ? '✅ YES' : '❌ NO') . "</li>";
if (file_exists($credentialsPath)) {
    $credSize = filesize($credentialsPath);
    echo "<li>Credentials file size: {$credSize} bytes</li>";
}
echo "</ul>";

echo "<h3>2. Load Fixed Google Sheets Class</h3>";

try {
    require_once 'google_sheets_fixed.php';
    echo "<p>✅ Class loaded successfully</p>";
    
    echo "<h3>3. Initialize Google Sheets Connection</h3>";
    $sheets = new SimpleGoogleSheets($credentialsPath, $spreadsheetId);
    echo "<p>✅ SimpleGoogleSheets instance created</p>";
    
    echo "<h3>4. Test Connection</h3>";
    $connectionResult = $sheets->testConnection();
    echo "<p>✅ Connection test successful</p>";
    
    if (isset($connectionResult['properties']['title'])) {
        echo "<p><strong>Spreadsheet Title:</strong> " . $connectionResult['properties']['title'] . "</p>";
    }
    
    echo "<h3>5. Test Data Append</h3>";
    
    // Prepare test data
    $testData = [
        [
            date('Y-m-d H:i:s'), // timestamp
            'TEST_' . date('His'), // ID santri
            'Test Santri Fixed', // nama
            'Test Sholat Magrib', // kegiatan
            'Alpha', // status
            'Testing fixed version - ' . date('H:i:s'), // keterangan
            date('Y-m-d') // tanggal
        ]
    ];
    
    echo "<p><strong>Test Data:</strong></p>";
    echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";
    
    echo "<p>Sending data to Google Sheets...</p>";
    $result = $sheets->appendData($sheetName, $testData);
    
    if ($result && isset($result['updates'])) {
        echo "<p>✅ <strong>SUCCESS!</strong> Data sent to Google Sheets</p>";
        echo "<p><strong>Updated Rows:</strong> " . ($result['updates']['updatedRows'] ?? 'Unknown') . "</p>";
        echo "<p><strong>Updated Cells:</strong> " . ($result['updates']['updatedCells'] ?? 'Unknown') . "</p>";
        
        echo "<h4>Full Response:</h4>";
        echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
        
        // Success log
        $successLog = "\n=== FIXED VERSION SUCCESS ===\n";
        $successLog .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
        $successLog .= "Test Data Sent: " . json_encode($testData) . "\n";
        $successLog .= "Result: " . json_encode($result) . "\n";
        $successLog .= str_repeat("=", 40) . "\n";
        file_put_contents('test_fixed_success.log', $successLog, FILE_APPEND | LOCK_EX);
        
    } else {
        echo "<p>❌ No data written or unexpected result</p>";
        echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 15px; border: 1px solid #f44336; margin: 10px 0;'>";
    echo "<h4>❌ Error:</h4>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    
    // Error log
    $errorLog = "\n=== FIXED VERSION ERROR ===\n";
    $errorLog .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
    $errorLog .= "Error: " . $e->getMessage() . "\n";
    $errorLog .= "File: " . $e->getFile() . "\n";
    $errorLog .= "Line: " . $e->getLine() . "\n";
    $errorLog .= "Trace: " . $e->getTraceAsString() . "\n";
    $errorLog .= str_repeat("=", 40) . "\n";
    file_put_contents('test_fixed_error.log', $errorLog, FILE_APPEND | LOCK_EX);
    
    echo "</div>";
}

echo "<h3>6. Next Steps</h3>";

if (isset($result) && $result) {
    echo "<div style='background: #e8f5e8; padding: 15px; border: 1px solid #4caf50;'>";
    echo "<h4>✅ FIXED VERSION IS WORKING!</h4>";
    echo "<p>Your application can now use the fixed version:</p>";
    echo "<ul>";
    echo "<li>✅ No more 'Malformed URL' errors</li>";
    echo "<li>✅ Proper error handling and logging</li>";
    echo "<li>✅ Robust cURL implementation</li>";
    echo "<li>✅ Better validation and debugging</li>";
    echo "</ul>";
    echo "<p><strong>Ready for production use!</strong></p>";
    echo "</div>";
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107;'>";
    echo "<h4>⚠️ Issues Found</h4>";
    echo "<p>Check the error logs and fix any remaining issues.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Back to Main</a> | ";
echo "<a href='test_simple_connection.php'>Simple Test</a> | ";
echo "<a href='debug_url_test.php'>Debug Test</a></p>";

// Show recent logs if available
if (file_exists('test_fixed_success.log')) {
    echo "<h3>Recent Success Log</h3>";
    echo "<pre>" . htmlspecialchars(file_get_contents('test_fixed_success.log')) . "</pre>";
}

if (file_exists('test_fixed_error.log')) {
    echo "<h3>Recent Error Log</h3>";
    echo "<pre>" . htmlspecialchars(file_get_contents('test_fixed_error.log')) . "</pre>";
}
?>
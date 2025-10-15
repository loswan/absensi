<?php
/**
 * File: debug_url_test.php
 * Test URL formation dan cURL connection untuk Google Sheets
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include simple Google Sheets class
require_once 'google_sheets_simple.php';

echo "<h2>🔍 DEBUG URL & cURL Test</h2>";

// Test configuration
$spreadsheetId = '1W6s0Osq_09tN3KKuXBFLj3noVlCNp7GRJT8f-MwIn04';
$credentialsPath = 'credentials.json';
$sheetName = 'Log Absensi';

echo "<h3>1. System Check</h3>";
echo "<ul>";
echo "<li>PHP Version: " . PHP_VERSION . "</li>";
echo "<li>cURL enabled: " . (function_exists('curl_init') ? '✅ YES' : '❌ NO') . "</li>";
echo "<li>OpenSSL enabled: " . (function_exists('openssl_sign') ? '✅ YES' : '❌ NO') . "</li>";
echo "<li>Credentials file: " . (file_exists($credentialsPath) ? '✅ EXISTS' : '❌ MISSING') . "</li>";
echo "</ul>";

echo "<h3>2. URL Formation Test</h3>";

// Test URL encoding
$encodedSheetName = rawurlencode($sheetName);
echo "<p><strong>Original Sheet Name:</strong> '$sheetName'</p>";
echo "<p><strong>Encoded Sheet Name:</strong> '$encodedSheetName'</p>";

$baseUrl = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/$encodedSheetName:append";
$queryParams = http_build_query([
    'valueInputOption' => 'USER_ENTERED',
    'insertDataOption' => 'INSERT_ROWS'
]);
$fullUrl = $baseUrl . '?' . $queryParams;

echo "<p><strong>Base URL:</strong><br><code>$baseUrl</code></p>";
echo "<p><strong>Query Params:</strong><br><code>$queryParams</code></p>";
echo "<p><strong>Full URL:</strong><br><code>$fullUrl</code></p>";

// Test URL validity
if (filter_var($baseUrl, FILTER_VALIDATE_URL)) {
    echo "<p>✅ Base URL is valid</p>";
} else {
    echo "<p>❌ Base URL is invalid</p>";
}

echo "<h3>3. Simple Connection Test</h3>";

try {
    echo "<p>Creating SimpleGoogleSheets instance...</p>";
    $sheets = new SimpleGoogleSheets($credentialsPath, $spreadsheetId);
    echo "<p>✅ SimpleGoogleSheets created successfully</p>";
    
    echo "<p>Testing connection to spreadsheet...</p>";
    $testResult = $sheets->testConnection();
    echo "<p>✅ Connection test passed</p>";
    echo "<p><strong>Spreadsheet Title:</strong> " . ($testResult['properties']['title'] ?? 'Unknown') . "</p>";
    
    echo "<h3>4. Test Data Append</h3>";
    
    // Test data
    $testData = [
        [
            date('Y-m-d H:i:s'),
            'TEST001',
            'Test Santri',
            'Test Kegiatan',
            'Alpha',
            'Test debugging URL',
            date('Y-m-d')
        ]
    ];
    
    echo "<p>Sending test data...</p>";
    echo "<p><strong>Test Data:</strong><br><pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre></p>";
    
    $result = $sheets->appendData($sheetName, $testData);
    
    if ($result && isset($result['updates'])) {
        echo "<p>✅ Data successfully sent to Google Sheets!</p>";
        echo "<p><strong>Result:</strong><br><pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre></p>";
    } else {
        echo "<p>❌ No data written to Google Sheets</p>";
        echo "<p><strong>Result:</strong><br><pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre></p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 15px; border: 1px solid #f44336;'>";
    echo "<h4>❌ Error Occurred:</h4>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<h3>5. Manual cURL Test</h3>";

if (isset($sheets) && $sheets) {
    try {
        // Manual cURL test for debugging
        echo "<p>Testing manual cURL call...</p>";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheetId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_VERBOSE => true,
            CURLOPT_STDERR => fopen('php://temp', 'w+'),
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        
        // Get verbose output
        rewind(curl_getinfo($ch, CURLOPT_STDERR));
        $verboseLog = stream_get_contents(curl_getinfo($ch, CURLOPT_STDERR));
        
        curl_close($ch);
        
        echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
        echo "<p><strong>cURL Error Number:</strong> $curlErrno</p>";
        echo "<p><strong>cURL Error:</strong> " . ($curlError ?: 'None') . "</p>";
        
        if ($httpCode === 200) {
            echo "<p>✅ Manual cURL test successful</p>";
        } else {
            echo "<p>❌ Manual cURL test failed</p>";
            echo "<p><strong>Response:</strong><br><pre>" . htmlspecialchars($response) . "</pre></p>";
        }
        
        if ($verboseLog) {
            echo "<details><summary>cURL Verbose Log</summary><pre>$verboseLog</pre></details>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Manual cURL test error: " . $e->getMessage() . "</p>";
    }
}

echo "<br><hr>";
echo "<a href='index.php'>← Back to Main</a> | ";
echo "<a href='test_simple_connection.php'>Basic Test</a> | ";
echo "<a href='proses_absen_simple.php' onclick='return false;'>Simple Process</a>";
?>
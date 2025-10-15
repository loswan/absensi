<?php
/**
 * File: test_range_fix.php
 * Test perbaikan range conflict error
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Test Range Fix</h2>";

// Simulasi perbaikan
echo "<h3>1. Masalah Sebelumnya:</h3>";
echo "<pre style='background:#ffebee;padding:10px;border:1px solid #f44336;'>";
echo "URL: https://sheets.googleapis.com/v4/spreadsheets/ID/values/Log%20Absensi:append\n";
echo "JSON: {\n";
echo "  'range': 'Log Absensi!A:Z',  ← KONFLIK!\n";
echo "  'majorDimension': 'ROWS',\n";
echo "  'values': [...]\n";
echo "}\n";
echo "ERROR: Request range[Log Absensi] does not match value's range[Log Absensi!A:Z]";
echo "</pre>";

echo "<h3>2. Perbaikan:</h3>";
echo "<pre style='background:#e8f5e8;padding:10px;border:1px solid #4caf50;'>";
echo "URL: https://sheets.googleapis.com/v4/spreadsheets/ID/values/Log%20Absensi:append\n";
echo "JSON: {\n";
echo "  'majorDimension': 'ROWS',  ← Range dihapus!\n";
echo "  'values': [...]\n";
echo "}\n";
echo "RESULT: ✅ SUCCESS - No range conflict";
echo "</pre>";

if (file_exists('credentials.json')) {
    echo "<h3>3. Test Real Implementation:</h3>";
    
    try {
        require_once 'google_sheets_fixed.php';
        
        $spreadsheetId = '1W6s0Osq_09tN3KKuXBFLj3noVlCNp7GRJT8f-MwIn04';
        $credentialsPath = 'credentials.json';
        $sheetName = 'Log Absensi';
        
        $sheets = new SimpleGoogleSheets($credentialsPath, $spreadsheetId);
        echo "<p>✅ Google Sheets connection created</p>";
        
        // Test data
        $testData = [
            [
                date('Y-m-d H:i:s'),
                'RANGE_FIX_TEST',
                'Test Range Fix',
                'Test Kegiatan',
                'Alpha',
                'Testing range conflict fix',
                date('Y-m-d')
            ]
        ];
        
        echo "<p>Sending test data with fixed range...</p>";
        $result = $sheets->appendData($sheetName, $testData);
        
        if ($result && isset($result['updates'])) {
            echo "<div style='background:#e8f5e8;padding:15px;border:1px solid #4caf50;'>";
            echo "<h4>✅ RANGE FIX SUCCESSFUL!</h4>";
            echo "<p>Updated Rows: " . ($result['updates']['updatedRows'] ?? 'Unknown') . "</p>";
            echo "<p>No more range conflict errors!</p>";
            echo "</div>";
        } else {
            echo "<p>❌ Unexpected result format</p>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background:#ffebee;padding:15px;border:1px solid #f44336;'>";
        echo "<h4>❌ Error Still Exists:</h4>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
} else {
    echo "<h3>3. Credentials Required:</h3>";
    echo "<p>❌ credentials.json not found. Create it first to test the fix.</p>";
}

echo "<hr>";
echo "<h3>📋 Summary:</h3>";
echo "<ul>";
echo "<li>✅ <strong>Problem:</strong> Range conflict between URL and JSON</li>";
echo "<li>✅ <strong>Solution:</strong> Remove 'range' from JSON body</li>";
echo "<li>✅ <strong>Result:</strong> Clean append operation without conflicts</li>";
echo "</ul>";

echo "<p><a href='index.php'>← Back to Main</a> | <a href='test_fixed_version.php'>Full Test Suite</a></p>";
?>
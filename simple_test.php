<?php
echo "=== SIMPLE TEST ===\n";

// Check if credentials exist
$credentialsPath = 'credentials.json';
echo "Checking credentials file: $credentialsPath\n";

if (file_exists($credentialsPath)) {
    echo "✅ Credentials file exists\n";
    echo "File size: " . filesize($credentialsPath) . " bytes\n";
    
    $content = file_get_contents($credentialsPath);
    if ($content) {
        echo "✅ File content loaded\n";
        
        $json = json_decode($content, true);
        if ($json && json_last_error() === JSON_ERROR_NONE) {
            echo "✅ JSON is valid\n";
            echo "Client email: " . ($json['client_email'] ?? 'Missing') . "\n";
            echo "Has private key: " . (isset($json['private_key']) ? 'Yes' : 'No') . "\n";
        } else {
            echo "❌ JSON parse error: " . json_last_error_msg() . "\n";
        }
    } else {
        echo "❌ Cannot read file content\n";
    }
} else {
    echo "❌ Credentials file not found\n";
}

// Test cURL
echo "\nTesting cURL...\n";
if (function_exists('curl_init')) {
    echo "✅ cURL is available\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.google.com');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        echo "❌ cURL error: $curlError\n";
    } else {
        echo "✅ cURL works, HTTP code: $httpCode\n";
    }
} else {
    echo "❌ cURL not available\n";
}

echo "\n=== TEST COMPLETED ===\n";
?>
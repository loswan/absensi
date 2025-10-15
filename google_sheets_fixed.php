<?php
/**
 * File: google_sheets_fixed.php
 * FIXED VERSION - Robust Google Sheets implementation dengan proper error handling
 */

class SimpleGoogleSheets {
    private $credentials;
    public $accessToken;      // Changed to public for external access
    public $spreadsheetId;    // Changed to public for external access
    private $debug = true;
    
    public function __construct($credentialsPath, $spreadsheetId) {
        if (!file_exists($credentialsPath)) {
            throw new Exception("File credentials tidak ditemukan: $credentialsPath");
        }
        
        $credentialsContent = file_get_contents($credentialsPath);
        if (!$credentialsContent) {
            throw new Exception("Tidak dapat membaca file credentials");
        }
        
        $this->credentials = json_decode($credentialsContent, true);
        if (!$this->credentials || json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Format credentials.json tidak valid: " . json_last_error_msg());
        }
        
        // Validasi required fields
        $requiredFields = ['client_email', 'private_key'];
        foreach ($requiredFields as $field) {
            if (empty($this->credentials[$field])) {
                throw new Exception("Field '$field' tidak ditemukan di credentials.json");
            }
        }
        
        // Validasi spreadsheet ID
        if (empty($spreadsheetId)) {
            throw new Exception("Spreadsheet ID tidak boleh kosong");
        }
        
        // Clean spreadsheet ID
        $this->spreadsheetId = trim($spreadsheetId);
        
        $this->log("Initializing Google Sheets with ID: " . $this->spreadsheetId);
        
        try {
            $this->accessToken = $this->getAccessToken();
            $this->log("Access token obtained successfully");
        } catch (Exception $e) {
            throw new Exception("Gagal mendapatkan access token: " . $e->getMessage());
        }
    }
    
    private function log($message) {
        if ($this->debug) {
            error_log("[SimpleGoogleSheets] " . date('Y-m-d H:i:s') . " - $message");
        }
    }
    
    private function getAccessToken() {
        $this->log("Starting JWT token generation...");
        
        // Generate JWT token
        $header = json_encode(['typ' => 'JWT', 'alg' => 'RS256']);
        
        $now = time();
        $payload = json_encode([
            'iss' => $this->credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/spreadsheets',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]);
        
        $base64Header = $this->base64UrlEncode($header);
        $base64Payload = $this->base64UrlEncode($payload);
        
        $signature = '';
        $privateKey = $this->credentials['private_key'];
        
        // Clean private key
        if (strpos($privateKey, '-----BEGIN PRIVATE KEY-----') === false) {
            throw new Exception("Format private key tidak valid");
        }
        
        if (!openssl_sign($base64Header . "." . $base64Payload, $signature, $privateKey, 'sha256WithRSAEncryption')) {
            throw new Exception("Gagal membuat signature JWT: " . openssl_error_string());
        }
        
        $base64Signature = $this->base64UrlEncode($signature);
        $jwt = $base64Header . "." . $base64Payload . "." . $base64Signature;
        
        $this->log("JWT token generated, requesting access token...");
        
        // Request access token
        $postData = http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://oauth2.googleapis.com/token',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent: SimpleGoogleSheets/1.0'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        
        curl_close($ch);
        
        if ($curlErrno !== 0) {
            throw new Exception("cURL error ($curlErrno): $curlError");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP error $httpCode: $response");
        }
        
        $tokenData = json_decode($response, true);
        if (!$tokenData || json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: " . json_last_error_msg());
        }
        
        if (!isset($tokenData['access_token'])) {
            throw new Exception("Access token tidak ditemukan dalam response: $response");
        }
        
        return $tokenData['access_token'];
    }
    
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    public function appendData($sheetName, $data) {
        // Validasi input
        if (empty($sheetName)) {
            throw new Exception("Sheet name tidak boleh kosong");
        }
        
        if (empty($data) || !is_array($data)) {
            throw new Exception("Data tidak valid atau kosong");
        }
        
        $this->log("Appending data to sheet: $sheetName");
        
        // Build URL yang benar
        $encodedSheetName = rawurlencode($sheetName);
        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s:append',
            $this->spreadsheetId,
            $encodedSheetName
        );
        
        // Add query parameters
        $queryParams = [
            'valueInputOption' => 'USER_ENTERED',
            'insertDataOption' => 'INSERT_ROWS'
        ];
        
        $fullUrl = $url . '?' . http_build_query($queryParams);
        
        $this->log("Request URL: " . $fullUrl);
        
        $postData = json_encode([
            'majorDimension' => 'ROWS',
            'values' => $data
        ]);
        
        $this->log("POST data: " . $postData);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $fullUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json',
                'User-Agent: SimpleGoogleSheets/1.0'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        
        curl_close($ch);
        
        $this->log("Response code: $httpCode");
        
        if ($curlErrno !== 0) {
            throw new Exception("cURL error ($curlErrno): $curlError");
        }
        
        if ($httpCode !== 200) {
            $this->log("Error response: " . $response);
            throw new Exception("HTTP error $httpCode: $response");
        }
        
        $result = json_decode($response, true);
        if (!$result || json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: " . json_last_error_msg());
        }
        
        $this->log("Data successfully appended");
        return $result;
    }
    
    public function testConnection() {
        $this->log("Testing connection to spreadsheet...");
        
        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s',
            $this->spreadsheetId
        );
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'User-Agent: SimpleGoogleSheets/1.0'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        
        curl_close($ch);
        
        if ($curlErrno !== 0) {
            throw new Exception("cURL error ($curlErrno): $curlError");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP error $httpCode: $response");
        }
        
        $result = json_decode($response, true);
        if (!$result || json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: " . json_last_error_msg());
        }
        
        $this->log("Connection test successful");
        return $result;
    }
}
?>
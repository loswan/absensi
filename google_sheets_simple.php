<?php
/**
 * File: google_sheets_simple.php
 * Deskripsi: Alternatif sederhana untuk Google Sheets tanpa Guzzle
 * Menggunakan cURL langsung untuk menghindari compatibility issues
 */

class SimpleGoogleSheets {
    private $credentials;
    private $accessToken;
    private $spreadsheetId;
    
    public function __construct($credentialsPath, $spreadsheetId) {
        if (!file_exists($credentialsPath)) {
            throw new Exception("File credentials tidak ditemukan: $credentialsPath");
        }
        
        $this->credentials = json_decode(file_get_contents($credentialsPath), true);
        if (!$this->credentials) {
            throw new Exception("Format credentials.json tidak valid");
        }
        
        // Validasi spreadsheet ID format
        if (empty($spreadsheetId) || !preg_match('/^[a-zA-Z0-9_-]+$/', $spreadsheetId)) {
            throw new Exception("Format spreadsheet ID tidak valid: $spreadsheetId");
        }
        
        $this->spreadsheetId = trim($spreadsheetId);
        $this->accessToken = $this->getAccessToken();
    }
    
    private function getAccessToken() {
        // Generate JWT token untuk service account
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
        
        if (!openssl_sign($base64Header . "." . $base64Payload, $signature, $privateKey, 'sha256')) {
            throw new Exception("Gagal membuat signature JWT");
        }
        
        $base64Signature = $this->base64UrlEncode($signature);
        $jwt = $base64Header . "." . $base64Payload . "." . $base64Signature;
        
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
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception("cURL error: " . curl_error($ch));
        }
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP error $httpCode: $response");
        }
        
        $tokenData = json_decode($response, true);
        if (!isset($tokenData['access_token'])) {
            throw new Exception("Gagal mendapatkan access token: $response");
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
        
        // URL encoding yang proper untuk sheet name
        $encodedSheetName = rawurlencode($sheetName);
        
        // Build URL dengan format yang benar
        $baseUrl = "https://sheets.googleapis.com/v4/spreadsheets/{$this->spreadsheetId}/values/{$encodedSheetName}:append";
        $queryParams = http_build_query([
            'valueInputOption' => 'USER_ENTERED',
            'insertDataOption' => 'INSERT_ROWS'
        ]);
        $fullUrl = $baseUrl . '?' . $queryParams;
        
        $postData = json_encode([
            'range' => $sheetName . '!A:Z',
            'majorDimension' => 'ROWS',
            'values' => $data
        ]);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $fullUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception("cURL error: " . curl_error($ch));
        }
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP error $httpCode: $response");
        }
        
        $result = json_decode($response, true);
        return $result;
    }
    
    public function testConnection() {
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$this->spreadsheetId}";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception("cURL error: " . curl_error($ch));
        }
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP error $httpCode: $response");
        }
        
        $result = json_decode($response, true);
        return $result;
    }
}
?>
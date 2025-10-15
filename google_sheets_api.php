<?php
/**
 * File: google_sheets_api.php
 * Google Sheets API Handler
 * Pemisahan logika untuk koneksi dan operasi Google Sheets
 */

class GoogleSheetsAPI {
    private $sheets;
    private $spreadsheetId;
    private $credentialsPath;
    public $connectionError = '';
    
    public function __construct($spreadsheetId, $credentialsPath = 'credentials.json') {
        $this->spreadsheetId = $spreadsheetId;
        $this->credentialsPath = $credentialsPath;
        $this->initializeConnection();
    }
    
    /**
     * Inisialisasi koneksi ke Google Sheets
     */
    private function initializeConnection() {
        try {
            // Cek apakah file credentials ada
            if (!file_exists($this->credentialsPath)) {
                throw new Exception('File credentials.json tidak ditemukan. Silakan baca CREDENTIALS_SETUP_GUIDE.md untuk setup.');
            }
            
            // Include Google Sheets class
            require_once 'google_sheets_fixed.php';
            
            // Inisialisasi Google Sheets client
            $this->sheets = new SimpleGoogleSheets($this->credentialsPath, $this->spreadsheetId);
            
        } catch (Exception $e) {
            $this->connectionError = 'Error koneksi ke Google Sheets: ' . $e->getMessage();
            error_log($this->connectionError);
            $this->sheets = null;
        }
    }
    
    /**
     * Mengambil data santri dari sheet tertentu
     * @param string $sheetName Nama sheet (Santri Putra / Santri Putri)
     * @return array Data santri dalam format [ID, Nama, Kamar]
     */
    public function getSantriData($sheetName) {
        if (!$this->sheets) {
            return [];
        }
        
        try {
            // Buat URL untuk mengambil data dari range A2:C (skip header)
            $url = sprintf(
                'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s!A2:C',
                $this->sheets->spreadsheetId,
                rawurlencode($sheetName)
            );
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->sheets->accessToken,
                    'User-Agent: SantriAbsensiApp/1.0'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                throw new Exception("cURL error: $curlError");
            }
            
            if ($httpCode !== 200) {
                throw new Exception("HTTP error $httpCode: $response");
            }
            
            $result = json_decode($response, true);
            
            // Kembalikan data values jika ada, atau array kosong
            return isset($result['values']) ? $result['values'] : [];
            
        } catch (Exception $e) {
            error_log("Error getting santri data from sheet '$sheetName': " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mengirim data absensi ke Google Sheets
     * @param string $sheetName Nama sheet tujuan
     * @param array $data Data absensi dalam format array 2D
     * @return array|bool Result dari Google Sheets API atau false jika error
     */
    public function sendAbsensiData($sheetName, $data) {
        if (!$this->sheets) {
            throw new Exception('Google Sheets tidak terkoneksi: ' . $this->connectionError);
        }
        
        if (empty($data)) {
            throw new Exception('Data absensi tidak boleh kosong');
        }
        
        return $this->sheets->appendData($sheetName, $data);
    }
    
    /**
     * Test koneksi ke Google Sheets
     * @return bool True jika koneksi berhasil
     */
    public function testConnection() {
        if (!$this->sheets) {
            return false;
        }
        
        try {
            $result = $this->sheets->testConnection();
            return isset($result['properties']['title']);
        } catch (Exception $e) {
            error_log("Connection test failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mendapatkan informasi spreadsheet
     * @return array|null Info spreadsheet atau null jika error
     */
    public function getSpreadsheetInfo() {
        if (!$this->sheets) {
            return null;
        }
        
        try {
            return $this->sheets->testConnection();
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Cek apakah koneksi tersedia
     * @return bool True jika terkoneksi
     */
    public function isConnected() {
        return $this->sheets !== null && empty($this->connectionError);
    }
}

/**
 * Helper Functions
 */

/**
 * Fungsi untuk membersihkan input untuk Google Sheets (tanpa HTML encoding)
 * @param string $data Input data
 * @return string Data yang sudah dibersihkan
 */
function cleanForSheets($data) {
    $data = trim($data);
    $data = stripslashes($data);
    // NO htmlspecialchars() - Google Sheets butuh data mentah!
    return $data;
}

/**
 * Fungsi untuk validasi dan sanitasi input (untuk display HTML)
 * @param string $data Input data
 * @return string Data yang sudah disanitasi
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Fungsi untuk logging error
 * @param string $message Pesan error
 */
function logError($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] ERROR: $message" . PHP_EOL;
    file_put_contents('error_log.txt', $logMessage, FILE_APPEND | LOCK_EX);
}
?>
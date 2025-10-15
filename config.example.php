<?php
/**
 * File: config.example.php
 * Deskripsi: Contoh file konfigurasi untuk Google Sheets
 * CARA PAKAI: 
 * 1. Copy file ini menjadi config.php
 * 2. Isi dengan data yang benar
 * 3. Include di proses_absen.php
 */

// ========== KONFIGURASI GOOGLE SHEETS ==========

/* 
 * SPREADSHEET ID - Cara mendapatkan:
 * 1. Buka Google Sheets di browser
 * 2. Lihat URL: https://docs.google.com/spreadsheets/d/[SPREADSHEET_ID]/edit#gid=0
 * 3. Copy bagian [SPREADSHEET_ID]
 * 4. Paste ke variabel di bawah
 */
$spreadsheetId = '1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms'; // CONTOH - GANTI DENGAN MILIK ANDA

/*
 * CREDENTIALS PATH
 * Path ke file credentials.json yang di-download dari Google Cloud Console
 */
$credentialsPath = 'credentials.json';

/*
 * SHEET NAME
 * Nama sheet di Google Sheets untuk menyimpan log absensi
 * PENTING: Case-sensitive! Harus exact sama dengan nama sheet
 */
$sheetName = 'Log Absensi';

// ========== KONFIGURASI DEBUG ==========

/*
 * DEBUG MODE
 * true = Tampilkan error detail di browser (untuk development)
 * false = Sembunyikan error detail (untuk production)
 */
$debugMode = true;

/*
 * LOG FILES
 * Path untuk file-file log
 */
$errorLogFile = 'error_log.txt';
$successLogFile = 'google_sheets_success.log';
$absensiLogFile = 'absensi_log.txt';

// ========== VALIDASI KONFIGURASI ==========

// Fungsi untuk memvalidasi konfigurasi
function validateConfig() {
    global $spreadsheetId, $credentialsPath, $sheetName;
    
    $errors = [];
    
    if (empty($spreadsheetId) || $spreadsheetId === '1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms') {
        $errors[] = 'Spreadsheet ID belum diisi atau masih menggunakan contoh';
    }
    
    if (!file_exists($credentialsPath)) {
        $errors[] = "File credentials tidak ditemukan: $credentialsPath";
    }
    
    if (empty($sheetName)) {
        $errors[] = 'Nama sheet tidak boleh kosong';
    }
    
    if (!empty($errors)) {
        throw new Exception('Konfigurasi tidak valid: ' . implode(', ', $errors));
    }
    
    return true;
}

// ========== CONTOH STRUKTUR GOOGLE SHEETS ==========
/*
STRUKTUR SHEET "Log Absensi":

| A          | B         | C           | D              | E                | F          | G        |
|------------|-----------|-------------|----------------|------------------|------------|----------|
| Timestamp  | ID_Santri | Nama_Santri | Jenis_Kegiatan | Status_Kehadiran | Keterangan | Tanggal  |
| 2025-10-15 | P001      | Ahmad Fauzi | Sholat Maghrib | Sakit            | Demam      | 2025-10-15|
| 14:30:00   |           |             |                |                  |            |          |

KETERANGAN KOLOM:
- Timestamp: Format YYYY-MM-DD HH:MM:SS
- ID_Santri: P001, P002, ... untuk putra; PI001, PI002, ... untuk putri  
- Nama_Santri: Nama lengkap santri
- Jenis_Kegiatan: Sholat Maghrib, Ngaji Kamis (Fathul Mu'in), dll
- Status_Kehadiran: Sakit, Izin, Alfa (TIDAK menyimpan yang "Hadir")
- Keterangan: Alasan jika sakit/izin
- Tanggal: Format YYYY-MM-DD
*/

?>

<!-- 
CARA MENGGUNAKAN FILE INI:

1. SETUP AWAL:
   - Copy file ini menjadi config.php
   - Edit variabel $spreadsheetId dengan ID Spreadsheet Anda
   - Pastikan file credentials.json ada

2. INCLUDE DI proses_absen.php:
   // Tambahkan di awal file proses_absen.php
   require_once 'config.php';
   validateConfig(); // Validasi konfigurasi

3. TESTING:
   - Jalankan test_google_connection.php untuk memastikan koneksi OK
   - Test aplikasi dengan mengisi form absensi

4. PRODUCTION:
   - Set $debugMode = false untuk production
   - Pastikan file config.php tidak di-commit ke Git (ada di .gitignore)
-->
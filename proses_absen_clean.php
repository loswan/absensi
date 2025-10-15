<?php
/**
 * File: proses_absen_clean.php
 * Clean & Modern Form Processor untuk Absensi Santri
 * Menggunakan Google Sheets API yang sudah dipisah
 */

// ========== KONFIGURASI ==========
$spreadsheetId = '1W6s0Osq_09tN3KKuXBFLj3noVlCNp7GRJT8f-MwIn04';
$sheetName = 'Log Absensi';

// ========== SECURITY & VALIDATION ==========
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Include Google Sheets API
require_once 'google_sheets_api.php';

try {
    // ========== VALIDASI INPUT ==========
    $kegiatan = sanitizeInput($_POST['kegiatan'] ?? '');
    $kelompok = sanitizeInput($_POST['kelompok'] ?? '');
    $dataSantri = $_POST['santri'] ?? [];
    $dataStatus = $_POST['status'] ?? [];
    $dataKeterangan = $_POST['keterangan'] ?? [];

    if (empty($kegiatan)) {
        throw new Exception('Kegiatan harus dipilih');
    }
    
    if (empty($kelompok)) {
        throw new Exception('Kelompok santri harus dipilih');
    }

    if (empty($dataSantri)) {
        throw new Exception('Data santri tidak ditemukan');
    }

    // ========== PROSES DATA ABSENSI ==========
    $timestamp = date('Y-m-d H:i:s');
    $tanggal = date('Y-m-d');
    $dataUntukSheet = [];
    $totalSantri = count($dataSantri);
    $santriTidakHadir = 0;

    foreach ($dataSantri as $santriId => $santri) {
        $idSantri = cleanForSheets($santri['id'] ?? '');
        $namaSantri = cleanForSheets($santri['nama'] ?? '');
        $kamarSantri = cleanForSheets($santri['kamar'] ?? '');
        $statusKehadiran = cleanForSheets($dataStatus[$santriId] ?? 'Hadir');
        $keterangan = cleanForSheets($dataKeterangan[$santriId] ?? '');
        $kegiatanClean = cleanForSheets($kegiatan);

        // HANYA SIMPAN JIKA STATUS BUKAN 'Hadir'
        if ($statusKehadiran !== 'Hadir' && !empty($statusKehadiran)) {
            $row = [
                $timestamp,        // A: Timestamp
                $idSantri,         // B: ID Santri
                $namaSantri,       // C: Nama Santri
                $kegiatanClean,    // D: Kegiatan
                $statusKehadiran,  // E: Status
                $keterangan,       // F: Keterangan
                $tanggal          // G: Tanggal
            ];
            
            $dataUntukSheet[] = $row;
            $santriTidakHadir++;
        }
    }

    // ========== HANDLE RESULT ==========
    if (empty($dataUntukSheet)) {
        // Semua santri hadir
        logActivity([
            'type' => 'semua_hadir',
            'kegiatan' => $kegiatan,
            'kelompok' => $kelompok,
            'total_santri' => $totalSantri,
            'timestamp' => $timestamp,
            'tanggal' => $tanggal
        ]);
        
        header('Location: index.php?status=sukses&message=semua_hadir');
        exit;
    }

    // ========== KIRIM KE GOOGLE SHEETS ==========
    $googleSheets = new GoogleSheetsAPI($spreadsheetId);
    
    if (!$googleSheets->isConnected()) {
        throw new Exception('Tidak dapat terhubung ke Google Sheets: ' . $googleSheets->connectionError);
    }

    $result = $googleSheets->sendAbsensiData($sheetName, $dataUntukSheet);
    
    if ($result && isset($result['updates'])) {
        $updatedRows = $result['updates']['updatedRows'] ?? $santriTidakHadir;
        
        // Log success
        logActivity([
            'type' => 'success',
            'kegiatan' => $kegiatan,
            'kelompok' => $kelompok,
            'total_santri' => $totalSantri,
            'santri_tidak_hadir' => $santriTidakHadir,
            'rows_updated' => $updatedRows,
            'timestamp' => $timestamp,
            'tanggal' => $tanggal,
            'data' => $dataUntukSheet
        ]);
        
        header("Location: index.php?status=sukses&records=$santriTidakHadir");
        exit;
    } else {
        throw new Exception('Gagal menulis data ke Google Sheets');
    }

} catch (Exception $e) {
    // ========== ERROR HANDLING ==========
    $errorMessage = $e->getMessage();
    $errorDetails = [
        'message' => $errorMessage,
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'timestamp' => date('Y-m-d H:i:s'),
        'post_data' => $_POST
    ];
    
    // Log error
    logError('Absensi Error: ' . json_encode($errorDetails));
    
    // Redirect dengan error
    $encodedMessage = urlencode($errorMessage);
    header("Location: index.php?status=error&message=$encodedMessage");
    exit;
}

/**
 * Function untuk logging aktivitas
 * @param array $data Data aktivitas
 */
function logActivity($data) {
    $logFile = 'absensi_log.txt';
    $logContent = "\n" . str_repeat("=", 60) . "\n";
    $logContent .= "ABSENSI LOG - " . strtoupper($data['type']) . "\n";
    $logContent .= str_repeat("=", 60) . "\n";
    $logContent .= "Timestamp: " . $data['timestamp'] . "\n";
    $logContent .= "Tanggal: " . $data['tanggal'] . "\n";
    $logContent .= "Kegiatan: " . $data['kegiatan'] . "\n";
    $logContent .= "Kelompok: " . ucfirst($data['kelompok']) . "\n";
    $logContent .= "Total Santri: " . $data['total_santri'] . "\n";
    
    if ($data['type'] === 'success') {
        $logContent .= "Santri Tidak Hadir: " . $data['santri_tidak_hadir'] . "\n";
        $logContent .= "Rows Updated: " . $data['rows_updated'] . "\n";
        $logContent .= "Status: BERHASIL DIKIRIM KE GOOGLE SHEETS\n\n";
        
        $logContent .= "DETAIL KETIDAKHADIRAN:\n";
        foreach ($data['data'] as $row) {
            $logContent .= "- ID: {$row[1]} | Nama: {$row[2]} | Status: {$row[4]}";
            if (!empty($row[5])) {
                $logContent .= " | Keterangan: {$row[5]}";
            }
            $logContent .= "\n";
        }
    } else {
        $logContent .= "Status: SEMUA SANTRI HADIR\n";
    }
    
    $logContent .= str_repeat("=", 60) . "\n";
    
    file_put_contents($logFile, $logContent, FILE_APPEND | LOCK_EX);
}
?>
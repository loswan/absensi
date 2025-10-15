<?php
/**
 * File: index.php
 * Sistem Absensi Santri YPMI Al-Firdaus
 * Dengan integrasi Google Sheets untuk data santri dinamis
 */

// ========== BAGIAN 1: LOGIKA PHP (GOOGLE SHEETS INTEGRATION) ==========

// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// KONFIGURASI - UBAH SESUAI KEBUTUHAN ANDA
$spreadsheetId = '1W6s0Osq_09tN3KKuXBFLj3noVlCNp7GRJT8f-MwIn04'; // ← MASUKKAN SPREADSHEET ID ANDA DI SINI
$credentialsPath = 'credentials.json';

// Inisialisasi variabel untuk data santri
$santriPutra = [];
$santriPutri = [];
$jsonSantriPutra = '[]';
$jsonSantriPutri = '[]';
$connectionError = '';

// Cek apakah file credentials ada
if (!file_exists($credentialsPath)) {
    $connectionError = 'File credentials.json tidak ditemukan. Silakan baca CREDENTIALS_SETUP_GUIDE.md untuk setup.';
} else {
    // Include Google Sheets class
    require_once 'google_sheets_fixed.php';
    
    // Fungsi untuk mengambil data santri dari Google Sheets
    function getSantriData($sheets, $sheetName) {
        try {
            // Buat URL untuk mengambil data dari range A2:C (skip header)
            $url = sprintf(
                'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s!A2:C',
                $sheets->spreadsheetId,
                rawurlencode($sheetName)
            );
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $sheets->accessToken,
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
    
    try {
        // Inisialisasi Google Sheets client
        $sheets = new SimpleGoogleSheets($credentialsPath, $spreadsheetId);
        
        // Ambil data santri putra dan putri
        $santriPutra = getSantriData($sheets, 'Santri Putra');
        $santriPutri = getSantriData($sheets, 'Santri Putri');
        
        // Konversi ke JSON untuk JavaScript
        $jsonSantriPutra = json_encode($santriPutra);
        $jsonSantriPutri = json_encode($santriPutri);
        
    } catch (Exception $e) {
        $connectionError = 'Error koneksi ke Google Sheets: ' . $e->getMessage();
        error_log($connectionError);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi Santri YPMI Al-Firdaus</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid px-3 px-md-4 py-3">
        <!-- Header -->
        <div class="row mb-3 mb-md-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h1 class="h3 mb-0 fw-bold">
                            <i class="bi bi-clipboard-check me-2"></i>
                            Sistem Absensi Santri
                        </h1>
                        <small class="opacity-75">YPMI Al-Firdaus</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <div class="row mb-3" id="alertContainer">
            <div class="col-12">
                <!-- Google Sheets Connection Status -->
                <?php if (!empty($connectionError)): ?>
                    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Google Sheets:</strong> <?php echo htmlspecialchars($connectionError); ?>
                        <br><small>Data santri akan dimuat dari data statis. Untuk data dinamis, perbaiki koneksi Google Sheets.</small>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-cloud-check me-2"></i>
                        <strong>Google Sheets Connected!</strong> 
                        Data santri dimuat: <?php echo count($santriPutra); ?> putra, <?php echo count($santriPutri); ?> putri
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Form Processing Status -->
                <?php if (isset($_GET['status'])): ?>
                    <?php if ($_GET['status'] == 'sukses'): ?>
                        <?php 
                        $records = isset($_GET['records']) ? intval($_GET['records']) : 0;
                        $message = isset($_GET['message']) && $_GET['message'] == 'semua_hadir' 
                            ? "Semua santri hadir. Tidak ada data yang disimpan ke log." 
                            : "Absensi berhasil disimpan! ($records ketidakhadiran tercatat)";
                        ?>
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Sukses!</strong> <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($_GET['status'] == 'error'): ?>
                        <?php $errorMsg = isset($_GET['message']) ? urldecode($_GET['message']) : 'Terjadi kesalahan saat menyimpan data.'; ?>
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Error!</strong> <?php echo htmlspecialchars($errorMsg); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Form Absensi -->
        <form id="absensiForm" method="POST" action="proses_absen_simple.php">
            <!-- Pilihan Kegiatan -->
            <div class="row mb-3 mb-md-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-calendar-event me-2"></i>Pilih Kegiatan
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <!-- Sholat Wajib -->
                                <div class="col-6 col-md-3 col-lg-2">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Sholat Shubuh" id="shubuh">
                                    <label class="btn btn-outline-primary w-100 py-2 py-md-3" for="shubuh">
                                        <i class="bi bi-sunrise d-block mb-1"></i>
                                        <small>Shubuh</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Sholat Dzuhur" id="dzuhur">
                                    <label class="btn btn-outline-primary w-100 py-2 py-md-3" for="dzuhur">
                                        <i class="bi bi-sun d-block mb-1"></i>
                                        <small>Dzuhur</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Sholat Ashar" id="ashar">
                                    <label class="btn btn-outline-primary w-100 py-2 py-md-3" for="ashar">
                                        <i class="bi bi-cloud-sun d-block mb-1"></i>
                                        <small>Ashar</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Sholat Maghrib" id="maghrib">
                                    <label class="btn btn-outline-primary w-100 py-2 py-md-3" for="maghrib">
                                        <i class="bi bi-sunset d-block mb-1"></i>
                                        <small>Maghrib</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Sholat Isya" id="isya">
                                    <label class="btn btn-outline-primary w-100 py-2 py-md-3" for="isya">
                                        <i class="bi bi-moon-stars d-block mb-1"></i>
                                        <small>Isya</small>
                                    </label>
                                </div>

                                <!-- Ngaji Semester 1-4 -->
                                <div class="col-6 col-md-3 col-lg-2">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Ngaji Senin (Tafsir Jalalain)" id="ngaji_senin">
                                    <label class="btn btn-outline-success w-100 py-2 py-md-3" for="ngaji_senin">
                                        <i class="bi bi-book d-block mb-1"></i>
                                        <small>Senin (1-4)</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Ngaji Selasa (Fiqh Wadih)" id="ngaji_selasa">
                                    <label class="btn btn-outline-success w-100 py-2 py-md-3" for="ngaji_selasa">
                                        <i class="bi bi-book d-block mb-1"></i>
                                        <small>Selasa (1-4)</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Ngaji Rabu (Hadits Arbain)" id="ngaji_rabu">
                                    <label class="btn btn-outline-success w-100 py-2 py-md-3" for="ngaji_rabu">
                                        <i class="bi bi-book d-block mb-1"></i>
                                        <small>Rabu (1-4)</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Ngaji Kamis (Sirah Nabawiyah)" id="ngaji_kamis">
                                    <label class="btn btn-outline-success w-100 py-2 py-md-3" for="ngaji_kamis">
                                        <i class="bi bi-book d-block mb-1"></i>
                                        <small>Kamis (1-4)</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Ngaji Jumat (Arbain an-Nawawi)" id="ngaji_jumat">
                                    <label class="btn btn-outline-success w-100 py-2 py-md-3" for="ngaji_jumat">
                                        <i class="bi bi-book d-block mb-1"></i>
                                        <small>Jumat (1-4)</small>
                                    </label>
                                </div>

                                <!-- Ngaji Semester 5-8 -->
                                <div class="col-6 col-md-3 col-lg-2">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Ngaji Senin (Tafsir Ibnu Katsir)" id="ngaji_senin_senior">
                                    <label class="btn btn-outline-warning w-100 py-2 py-md-3" for="ngaji_senin_senior">
                                        <i class="bi bi-journal-bookmark d-block mb-1"></i>
                                        <small>Senin (5-8)</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Ngaji Selasa (Fiqh Manhaji)" id="ngaji_selasa_senior">
                                    <label class="btn btn-outline-warning w-100 py-2 py-md-3" for="ngaji_selasa_senior">
                                        <i class="bi bi-journal-bookmark d-block mb-1"></i>
                                        <small>Selasa (5-8)</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Ngaji Rabu (Hadits Bulugh al-Maram)" id="ngaji_rabu_senior">
                                    <label class="btn btn-outline-warning w-100 py-2 py-md-3" for="ngaji_rabu_senior">
                                        <i class="bi bi-journal-bookmark d-block mb-1"></i>
                                        <small>Rabu (5-8)</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pilihan Kelompok Santri -->
            <div class="row mb-3 mb-md-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-people me-2"></i>Pilih Kelompok Santri
                            </h5>
                            <small class="text-muted">
                                <span id="santriCount">0 santri dimuat</span>
                            </small>
                        </div>
                        <div class="card-body">
                            <!-- Toggle Buttons untuk Putra/Putri -->
                            <div class="btn-group w-100 mb-3" role="group">
                                <input type="radio" class="btn-check" name="kelompok" value="putra" id="kelompokPutra" checked>
                                <label class="btn btn-outline-primary" for="kelompokPutra" id="btnPutra">
                                    <i class="bi bi-person me-2"></i>Santri Putra
                                </label>
                                
                                <input type="radio" class="btn-check" name="kelompok" value="putri" id="kelompokPutri">
                                <label class="btn btn-outline-primary" for="kelompokPutri" id="btnPutri">
                                    <i class="bi bi-person-dress me-2"></i>Santri Putri
                                </label>
                            </div>

                            <!-- Loading Indicator -->
                            <div id="loadingIndicator" class="text-center py-3 d-none">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                <small>Memuat data santri...</small>
                            </div>

                            <!-- Tabel Absensi -->
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 35%">Nama Santri</th>
                                            <th style="width: 15%">Kamar</th>
                                            <th style="width: 45%" class="text-center">Status Kehadiran</th>
                                        </tr>
                                    </thead>
                                    <tbody id="absensi-table-body">
                                        <!-- Data santri akan dimuat oleh JavaScript -->
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                <i class="bi bi-hourglass-split me-2"></i>
                                                Pilih kelompok santri untuk memulai absensi
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="row">
                <div class="col-12">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg py-3" id="submitBtn" disabled>
                            <i class="bi bi-cloud-upload me-2"></i>
                            Simpan Absensi
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- ========== BAGIAN 2: LOGIKA JAVASCRIPT (DYNAMIC DATA RENDERING) ========== -->
    <script>
        // Simpan data santri dari PHP ke JavaScript
        const dataSantriPutra = <?php echo $jsonSantriPutra; ?>;
        const dataSantriPutri = <?php echo $jsonSantriPutri; ?>;
        
        console.log('Data Santri Putra:', dataSantriPutra);
        console.log('Data Santri Putri:', dataSantriPutri);

        // Fungsi untuk render tabel santri
        function renderTable(dataSantri, kelompok) {
            const tbody = document.getElementById('absensi-table-body');
            const santriCount = document.getElementById('santriCount');
            const submitBtn = document.getElementById('submitBtn');
            
            // Kosongkan tabel terlebih dahulu
            tbody.innerHTML = '';
            
            // Update counter
            santriCount.textContent = `${dataSantri.length} santri dimuat`;
            
            // Jika tidak ada data
            if (dataSantri.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox me-2"></i>
                            Tidak ada data santri ${kelompok} ditemukan
                        </td>
                    </tr>
                `;
                submitBtn.disabled = true;
                return;
            }
            
            // Render setiap baris santri
            dataSantri.forEach((santri, index) => {
                const [idSantri, namaSantri, kamarSantri] = santri;
                const nomorUrut = index + 1;
                
                const rowHTML = `
                    <tr>
                        <td class="align-middle">${nomorUrut}</td>
                        <td class="align-middle">
                            <strong>${namaSantri || 'Nama tidak tersedia'}</strong>
                            <input type="hidden" name="santri[${index}][id]" value="${idSantri || ''}">
                            <input type="hidden" name="santri[${index}][nama]" value="${namaSantri || ''}">
                            <input type="hidden" name="santri[${index}][kamar]" value="${kamarSantri || ''}">
                        </td>
                        <td class="align-middle">
                            <span class="badge bg-secondary">${kamarSantri || 'N/A'}</span>
                        </td>
                        <td class="align-middle">
                            <div class="btn-group btn-group-sm w-100" role="group">
                                <input type="radio" class="btn-check" name="santri[${index}][status]" value="Hadir" id="hadir_${index}" checked>
                                <label class="btn btn-outline-success" for="hadir_${index}">
                                    <i class="bi bi-check-circle me-1"></i>Hadir
                                </label>
                                
                                <input type="radio" class="btn-check" name="santri[${index}][status]" value="Alpha" id="alpha_${index}">
                                <label class="btn btn-outline-danger" for="alpha_${index}">
                                    <i class="bi bi-x-circle me-1"></i>Alpha
                                </label>
                                
                                <input type="radio" class="btn-check" name="santri[${index}][status]" value="Izin" id="izin_${index}">
                                <label class="btn btn-outline-warning" for="izin_${index}">
                                    <i class="bi bi-exclamation-circle me-1"></i>Izin
                                </label>
                                
                                <input type="radio" class="btn-check" name="santri[${index}][status]" value="Sakit" id="sakit_${index}">
                                <label class="btn btn-outline-info" for="sakit_${index}">
                                    <i class="bi bi-thermometer me-1"></i>Sakit
                                </label>
                            </div>
                            
                            <!-- Input keterangan (tersembunyi secara default) -->
                            <div class="mt-2">
                                <input type="text" class="form-control form-control-sm" 
                                       name="santri[${index}][keterangan]" 
                                       placeholder="Keterangan (opsional)" 
                                       id="keterangan_${index}">
                            </div>
                        </td>
                    </tr>
                `;
                
                tbody.insertAdjacentHTML('beforeend', rowHTML);
            });
            
            // Enable submit button
            submitBtn.disabled = false;
        }

        // Event listener untuk toggle putra/putri
        document.getElementById('btnPutra').addEventListener('click', function() {
            renderTable(dataSantriPutra, 'putra');
        });

        document.getElementById('btnPutri').addEventListener('click', function() {
            renderTable(dataSantriPutri, 'putri');
        });

        // Form validation
        document.getElementById('absensiForm').addEventListener('submit', function(e) {
            const kegiatan = document.querySelector('input[name="kegiatan"]:checked');
            const kelompok = document.querySelector('input[name="kelompok"]:checked');
            
            if (!kegiatan) {
                e.preventDefault();
                alert('Silakan pilih kegiatan terlebih dahulu!');
                return;
            }
            
            if (!kelompok) {
                e.preventDefault();
                alert('Silakan pilih kelompok santri terlebih dahulu!');
                return;
            }
            
            // Show loading
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<div class="spinner-border spinner-border-sm me-2" role="status"></div>Menyimpan...';
            submitBtn.disabled = true;
            
            // Reset button after 5 seconds (fallback)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });

        // ========== BAGIAN 3: INISIALISASI AWAL ==========
        
        // Tampilan awal: load data santri putra
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing...');
            
            // Tampilkan data santri putra secara default
            renderTable(dataSantriPutra, 'putra');
            
            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert:not(.alert-warning)');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });

        // Debug info
        console.log('Total Santri Putra:', dataSantriPutra.length);
        console.log('Total Santri Putri:', dataSantriPutri.length);
    </script>
</body>
</html>
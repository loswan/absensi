<?php
/**
 * File: index.php
 * Sistem Absensi Santri YPMI Al-Firdaus
 * Clean & Modern UI dengan Bootstrap 5
 */

// ========== KONFIGURASI ==========
$spreadsheetId = '1W6s0Osq_09tN3KKuXBFLj3noVlCNp7GRJT8f-MwIn04';

// ========== GOOGLE SHEETS INTEGRATION ==========
require_once 'google_sheets_api.php';

// Inisialisasi Google Sheets API
$googleSheets = new GoogleSheetsAPI($spreadsheetId);

// Ambil data santri
$santriPutra = $googleSheets->getSantriData('Santri Putra');
$santriPutri = $googleSheets->getSantriData('Santri Putri');

// Konversi ke JSON untuk JavaScript
$jsonSantriPutra = json_encode($santriPutra);
$jsonSantriPutri = json_encode($santriPutri);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi Santri - YPMI Al-Firdaus</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="style.css" rel="stylesheet">
</head>
<body class="bg-gradient">
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-clipboard-check me-2"></i>
                Sistem Absensi Santri
            </a>
            <span class="navbar-text text-light">
                <small>YPMI Al-Firdaus</small>
            </span>
        </div>
    </nav>

    <div class="container py-4">
        
        <!-- Connection Status Alert -->
        <div class="row mb-4">
            <div class="col-12">
                <?php if (!$googleSheets->isConnected()): ?>
                    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Google Sheets:</strong> <?php echo htmlspecialchars($googleSheets->connectionError); ?>
                        <br><small class="mt-1 d-block">Data santri akan dimuat dari data statis. Untuk data dinamis, perbaiki koneksi Google Sheets.</small>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-cloud-check-fill me-2"></i>
                        <strong>Google Sheets Connected!</strong> 
                        Data santri dimuat: <span class="fw-bold"><?php echo count($santriPutra); ?> putra</span>, 
                        <span class="fw-bold"><?php echo count($santriPutri); ?> putri</span>
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
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <strong>Sukses!</strong> <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($_GET['status'] == 'error'): ?>
                        <?php $errorMsg = isset($_GET['message']) ? urldecode($_GET['message']) : 'Terjadi kesalahan saat menyimpan data.'; ?>
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Error!</strong> <?php echo htmlspecialchars($errorMsg); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card shadow-lg border-0">
            <div class="card-header bg-white border-bottom">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-0 text-primary fw-bold">
                            <i class="bi bi-calendar-check me-2"></i>
                            Form Absensi
                        </h4>
                        <small class="text-muted">Pilih kegiatan dan catat kehadiran santri</small>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-light text-dark fs-6">
                            <i class="bi bi-clock me-1"></i>
                            <?php echo date('d/m/Y H:i'); ?>
                        </span>
                    </div>
                </div>
            </div>

            <form id="absensiForm" method="POST" action="proses_absen_clean.php">
                <div class="card-body">
                    
                    <!-- Pilihan Kegiatan -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-primary mb-3">
                            <i class="bi bi-calendar-event me-2"></i>Pilih Kegiatan
                        </label>
                        
                        <!-- Sholat Wajib -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Sholat Wajib</h6>
                            <div class="row g-2">
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Sholat Shubuh" id="shubuh">
                                    <label class="btn btn-outline-primary w-100 py-3 h-100" for="shubuh">
                                        <i class="bi bi-sunrise d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Shubuh</div>
                                        <small class="text-muted">04:30 - 05:30</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Sholat Dzuhur" id="dzuhur">
                                    <label class="btn btn-outline-primary w-100 py-3 h-100" for="dzuhur">
                                        <i class="bi bi-sun d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Dzuhur</div>
                                        <small class="text-muted">12:00 - 13:00</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Sholat Ashar" id="ashar">
                                    <label class="btn btn-outline-primary w-100 py-3 h-100" for="ashar">
                                        <i class="bi bi-cloud-sun d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Ashar</div>
                                        <small class="text-muted">15:30 - 16:30</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Sholat Maghrib" id="maghrib">
                                    <label class="btn btn-outline-primary w-100 py-3 h-100" for="maghrib">
                                        <i class="bi bi-sunset d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Maghrib</div>
                                        <small class="text-muted">18:00 - 19:00</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Sholat Isya" id="isya">
                                    <label class="btn btn-outline-primary w-100 py-3 h-100" for="isya">
                                        <i class="bi bi-moon-stars d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Isya</div>
                                        <small class="text-muted">19:30 - 20:30</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Ngaji Semester 1-4 -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Ngaji Semester 1-4</h6>
                            <div class="row g-2">
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Ngaji Senin (Tafsir Jalalain)" id="ngaji_senin">
                                    <label class="btn btn-outline-success w-100 py-3 h-100" for="ngaji_senin">
                                        <i class="bi bi-book d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Senin</div>
                                        <small class="text-muted">Tafsir Jalalain</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Ngaji Selasa (Fiqh Wadih)" id="ngaji_selasa">
                                    <label class="btn btn-outline-success w-100 py-3 h-100" for="ngaji_selasa">
                                        <i class="bi bi-book d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Selasa</div>
                                        <small class="text-muted">Fiqh Wadih</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Ngaji Rabu (Hadits Arbain)" id="ngaji_rabu">
                                    <label class="btn btn-outline-success w-100 py-3 h-100" for="ngaji_rabu">
                                        <i class="bi bi-book d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Rabu</div>
                                        <small class="text-muted">Hadits Arbain</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Ngaji Kamis (Sirah Nabawiyah)" id="ngaji_kamis">
                                    <label class="btn btn-outline-success w-100 py-3 h-100" for="ngaji_kamis">
                                        <i class="bi bi-book d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Kamis</div>
                                        <small class="text-muted">Sirah Nabawiyah</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Ngaji Jumat (Arbain an-Nawawi)" id="ngaji_jumat">
                                    <label class="btn btn-outline-success w-100 py-3 h-100" for="ngaji_jumat">
                                        <i class="bi bi-book d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Jumat</div>
                                        <small class="text-muted">Arbain an-Nawawi</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Ngaji Semester 5-8 -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Ngaji Semester 5-8</h6>
                            <div class="row g-2">
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Ngaji Senin (Tafsir Ibnu Katsir)" id="ngaji_senin_senior">
                                    <label class="btn btn-outline-warning w-100 py-3 h-100" for="ngaji_senin_senior">
                                        <i class="bi bi-journal-bookmark d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Senin</div>
                                        <small class="text-muted">Tafsir Ibnu Katsir</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Ngaji Selasa (Fiqh Manhaji)" id="ngaji_selasa_senior">
                                    <label class="btn btn-outline-warning w-100 py-3 h-100" for="ngaji_selasa_senior">
                                        <i class="bi bi-journal-bookmark d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Selasa</div>
                                        <small class="text-muted">Fiqh Manhaji</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Ngaji Rabu (Hadits Bulugh al-Maram)" id="ngaji_rabu_senior">
                                    <label class="btn btn-outline-warning w-100 py-3 h-100" for="ngaji_rabu_senior">
                                        <i class="bi bi-journal-bookmark d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Rabu</div>
                                        <small class="text-muted">Bulugh al-Maram</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Pilihan Kelompok Santri -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-primary mb-3">
                            <i class="bi bi-people me-2"></i>Pilih Kelompok Santri
                        </label>
                        
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="kelompok" value="putra" id="kelompokPutra" checked>
                                    <label class="btn btn-outline-primary py-3" for="kelompokPutra" id="btnPutra">
                                        <i class="bi bi-person me-2"></i>Santri Putra
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="kelompok" value="putri" id="kelompokPutri">
                                    <label class="btn btn-outline-primary py-3" for="kelompokPutri" id="btnPutri">
                                        <i class="bi bi-person-dress me-2"></i>Santri Putri
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2 mt-md-0">
                                <div class="text-center text-md-end">
                                    <span class="badge bg-light text-dark fs-6 py-2 px-3">
                                        <i class="bi bi-people-fill me-1"></i>
                                        <span id="santriCount">0 santri dimuat</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Loading Indicator -->
                        <div id="loadingIndicator" class="text-center py-4 d-none">
                            <div class="spinner-border text-primary me-3" role="status"></div>
                            <span class="text-muted">Memuat data santri...</span>
                        </div>

                        <!-- Tabel Absensi -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%" class="text-center">#</th>
                                        <th style="width: 30%">Nama Santri</th>
                                        <th style="width: 15%" class="text-center">Kamar</th>
                                        <th style="width: 50%" class="text-center">Status Kehadiran</th>
                                    </tr>
                                </thead>
                                <tbody id="absensi-table-body">
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-hourglass-split fs-1 d-block mb-2 opacity-50"></i>
                                            <div class="fw-semibold">Pilih kelompok santri untuk memulai absensi</div>
                                            <small>Data santri akan dimuat secara otomatis</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="card-footer bg-white border-top">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg py-3 fw-semibold" id="submitBtn" disabled>
                            <i class="bi bi-cloud-upload me-2"></i>
                            Simpan Absensi ke Google Sheets
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <small class="text-muted">
                © <?php echo date('Y'); ?> YPMI Al-Firdaus - Sistem Absensi Santri
                <span class="mx-2">•</span>
                <i class="bi bi-shield-check me-1"></i>Secure & Modern
            </small>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Data santri dari PHP
        const dataSantriPutra = <?php echo $jsonSantriPutra; ?>;
        const dataSantriPutri = <?php echo $jsonSantriPutri; ?>;
        
        // Debug
        console.log('Santri Putra loaded:', dataSantriPutra.length);
        console.log('Santri Putri loaded:', dataSantriPutri.length);

        // Fungsi untuk render tabel santri
        function renderTable(dataSantri, kelompok) {
            const tbody = document.getElementById('absensi-table-body');
            const santriCount = document.getElementById('santriCount');
            const submitBtn = document.getElementById('submitBtn');
            const loadingIndicator = document.getElementById('loadingIndicator');
            
            // Show loading
            loadingIndicator.classList.remove('d-none');
            tbody.innerHTML = '';
            
            // Simulate loading delay untuk UX
            setTimeout(() => {
                // Hide loading
                loadingIndicator.classList.add('d-none');
                
                // Update counter
                santriCount.textContent = `${dataSantri.length} santri dimuat`;
                
                // Jika tidak ada data
                if (dataSantri.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                <div class="fw-semibold">Tidak ada data santri ${kelompok} ditemukan</div>
                                <small>Pastikan data sudah ditambahkan ke Google Sheets</small>
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
                        <tr class="fade-in">
                            <td class="text-center fw-bold text-muted">${nomorUrut}</td>
                            <td>
                                <div class="fw-semibold text-dark">${namaSantri || 'Nama tidak tersedia'}</div>
                                <small class="text-muted">ID: ${idSantri || 'N/A'}</small>
                                <input type="hidden" name="santri[${index}][id]" value="${idSantri || ''}">
                                <input type="hidden" name="santri[${index}][nama]" value="${namaSantri || ''}">
                                <input type="hidden" name="santri[${index}][kamar]" value="${kamarSantri || ''}">
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary fs-6 px-3 py-2">${kamarSantri || 'N/A'}</span>
                            </td>
                            <td>
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
                                
                                <!-- Input keterangan -->
                                <div class="mt-2">
                                    <input type="text" class="form-control form-control-sm" 
                                           name="santri[${index}][keterangan]" 
                                           placeholder="Keterangan tambahan (opsional)" 
                                           id="keterangan_${index}">
                                </div>
                            </td>
                        </tr>
                    `;
                    
                    tbody.insertAdjacentHTML('beforeend', rowHTML);
                });
                
                // Enable submit button
                submitBtn.disabled = false;
                
                // Add fade-in animation
                setTimeout(() => {
                    document.querySelectorAll('.fade-in').forEach(row => {
                        row.classList.add('show');
                    });
                }, 100);
                
            }, 500); // Loading delay
        }

        // Event listeners
        document.getElementById('btnPutra').addEventListener('click', () => {
            renderTable(dataSantriPutra, 'putra');
        });

        document.getElementById('btnPutri').addEventListener('click', () => {
            renderTable(dataSantriPutri, 'putri');
        });

        // Form validation
        document.getElementById('absensiForm').addEventListener('submit', function(e) {
            const kegiatan = document.querySelector('input[name="kegiatan"]:checked');
            const kelompok = document.querySelector('input[name="kelompok"]:checked');
            
            if (!kegiatan) {
                e.preventDefault();
                showAlert('Silakan pilih kegiatan terlebih dahulu!', 'warning');
                return;
            }
            
            if (!kelompok) {
                e.preventDefault();
                showAlert('Silakan pilih kelompok santri terlebih dahulu!', 'warning');
                return;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<div class="spinner-border spinner-border-sm me-2" role="status"></div>Menyimpan ke Google Sheets...';
            submitBtn.disabled = true;
            
            // Fallback reset
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 10000);
        });

        // Helper function untuk show alert
        function showAlert(message, type = 'info') {
            const alertContainer = document.querySelector('.container .row:first-child .col-12');
            const alertHTML = `
                <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            alertContainer.insertAdjacentHTML('beforeend', alertHTML);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Load santri putra by default
            renderTable(dataSantriPutra, 'putra');
            
            // Auto-hide success alerts
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert-success, .alert-warning');
                alerts.forEach(alert => {
                    if (alert.querySelector('.btn-close')) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);
        });
    </script>
</body>
</html>
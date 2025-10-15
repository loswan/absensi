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
    
    <!-- Responsive Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-clipboard-check me-2"></i>
                <span class="d-none d-sm-inline">Sistem Absensi Santri</span>
                <span class="d-sm-none">Absensi</span>
            </a>
            
            <!-- Collapsible button for mobile -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <span class="navbar-text text-light me-3">
                        <small><i class="bi bi-geo-alt me-1"></i>YPMI Al-Firdaus</small>
                    </span>
                    <span class="navbar-text text-light">
                        <small><i class="bi bi-clock me-1"></i><?php echo date('d/m/Y H:i'); ?></small>
                    </span>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-3 py-md-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-9">
        
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
                    <div class="col-12 col-md-8">
                        <h4 class="card-title mb-1 mb-md-0 text-primary fw-bold">
                            <i class="bi bi-calendar-check me-2"></i>
                            <span class="d-none d-sm-inline">Form Absensi</span>
                            <span class="d-sm-none">Absensi</span>
                        </h4>
                        <small class="text-muted d-block d-md-inline">Pilih kegiatan dan catat kehadiran santri</small>
                    </div>
                    <div class="col-12 col-md-4 mt-2 mt-md-0 text-center text-md-end">
                        <span class="badge bg-light text-dark fs-6 d-none d-sm-inline-block">
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
                        <div class="mb-4">
                            <h6 class="text-muted mb-3 fw-semibold">
                                <i class="bi bi-brightness-high me-1"></i>Sholat Wajib
                            </h6>
                            <div class="row g-2 g-sm-3">
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Sholat Maghrib" id="maghrib" autocomplete="off">
                                    <label class="btn btn-outline-primary w-100 py-2 py-sm-3 h-100 d-flex flex-column justify-content-center" for="maghrib">
                                        <i class="bi bi-sunset d-block mb-1 mb-sm-2 fs-5 fs-sm-4"></i>
                                        <div class="fw-semibold fs-6 fs-sm-5">Maghrib</div>
                                        <small class="text-muted d-none d-sm-block">18:00</small>
                                    </label>
                                </div>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Sholat Isya" id="isya" autocomplete="off">
                                    <label class="btn btn-outline-primary w-100 py-2 py-sm-3 h-100 d-flex flex-column justify-content-center" for="isya">
                                        <i class="bi bi-moon-stars d-block mb-1 mb-sm-2 fs-5 fs-sm-4"></i>
                                        <div class="fw-semibold fs-6 fs-sm-5">Isya</div>
                                        <small class="text-muted d-none d-sm-block">19:30</small>
                                    </label>
                                </div>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Sholat Shubuh" id="shubuh" autocomplete="off">
                                    <label class="btn btn-outline-primary w-100 py-2 py-sm-3 h-100 d-flex flex-column justify-content-center" for="shubuh">
                                        <i class="bi bi-sunrise d-block mb-1 mb-sm-2 fs-5 fs-sm-4"></i>
                                        <div class="fw-semibold fs-6 fs-sm-5">Shubuh</div>
                                        <small class="text-muted d-none d-sm-block">04:30</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Ngaji Ba'da Shubuh -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Ngaji Ba'da Shubuh</h6>
                            <div class="row g-2">
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Rabu (Smt 5-8): Lubbul Ushul" id="ngaji_shubuh_rabu_58" autocomplete="off">
                                    <label class="btn btn-outline-success w-100 py-3 h-100" for="ngaji_shubuh_rabu_58">
                                        <i class="bi bi-book d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Rabu (Smt 5-8)</div>
                                        <small class="text-muted">Lubbul Ushul</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Kamis (Smt 1-4): Fathul Mu'in" id="ngaji_shubuh_kamis_14" autocomplete="off">
                                    <label class="btn btn-outline-success w-100 py-3 h-100" for="ngaji_shubuh_kamis_14">
                                        <i class="bi bi-book d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Kamis (Smt 1-4)</div>
                                        <small class="text-muted">Fathul Mu'in</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Sabtu (Semua Smt): Irsyadul 'Ibad" id="ngaji_shubuh_sabtu" autocomplete="off">
                                    <label class="btn btn-outline-success w-100 py-3 h-100" for="ngaji_shubuh_sabtu">
                                        <i class="bi bi-book d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Sabtu (Semua Smt)</div>
                                        <small class="text-muted">Irsyadul 'Ibad</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Ahad (Semua Smt): Irsyadul 'Ibad" id="ngaji_shubuh_ahad" autocomplete="off">
                                    <label class="btn btn-outline-success w-100 py-3 h-100" for="ngaji_shubuh_ahad">
                                        <i class="bi bi-book d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Ahad (Semua Smt)</div>
                                        <small class="text-muted">Irsyadul 'Ibad</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Ngaji Ba'da Maghrib -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Ngaji Ba'da Maghrib</h6>
                            <div class="row g-2">
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Kamis (Semua Smt): Yasinan" id="ngaji_maghrib_kamis" autocomplete="off">
                                    <label class="btn btn-outline-warning w-100 py-3 h-100" for="ngaji_maghrib_kamis">
                                        <i class="bi bi-book-half d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Kamis (Semua Smt)</div>
                                        <small class="text-muted">Yasinan</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Ngaji Ba'da Isya -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Ngaji Ba'da Isya</h6>
                            <div class="row g-2">
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Senin (Smt 5-8): Kifayatul Atqiya'" id="ngaji_isya_senin_58" autocomplete="off">
                                    <label class="btn btn-outline-info w-100 py-3 h-100" for="ngaji_isya_senin_58">
                                        <i class="bi bi-journal-text d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Senin (Smt 5-8)</div>
                                        <small class="text-muted">Kifayatul Atqiya'</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Selasa (Smt 1-4): Bidayatul Hidayah" id="ngaji_isya_selasa_14" autocomplete="off">
                                    <label class="btn btn-outline-info w-100 py-3 h-100" for="ngaji_isya_selasa_14">
                                        <i class="bi bi-journal-text d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Selasa (Smt 1-4)</div>
                                        <small class="text-muted">Bidayatul Hidayah</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Rabu (Smt 1-4): Adabul 'Alim wal Muta'allim" id="ngaji_isya_rabu_14" autocomplete="off">
                                    <label class="btn btn-outline-info w-100 py-3 h-100" for="ngaji_isya_rabu_14">
                                        <i class="bi bi-journal-text d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Rabu (Smt 1-4)</div>
                                        <small class="text-muted">Adabul 'Alim wal Muta'allim</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Kamis (Semua Smt): Diba'an & Tematik" id="ngaji_isya_kamis" autocomplete="off">
                                    <label class="btn btn-outline-info w-100 py-3 h-100" for="ngaji_isya_kamis">
                                        <i class="bi bi-music-note-beamed d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Kamis (Semua Smt)</div>
                                        <small class="text-muted">Diba'an & Tematik</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Jum'at (Smt 1-4): Arba'in an-Nawawi" id="ngaji_isya_jumat_14" autocomplete="off">
                                    <label class="btn btn-outline-info w-100 py-3 h-100" for="ngaji_isya_jumat_14">
                                        <i class="bi bi-journal-text d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Jum'at (Smt 1-4)</div>
                                        <small class="text-muted">Arba'in an-Nawawi</small>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <input type="radio" class="btn-check" name="kegiatan" value="Jum'at (Smt 5-8): Metodologi Penelitian" id="ngaji_isya_jumat_58" autocomplete="off">
                                    <label class="btn btn-outline-info w-100 py-3 h-100" for="ngaji_isya_jumat_58">
                                        <i class="bi bi-search d-block mb-2 fs-4"></i>
                                        <div class="fw-semibold">Jum'at (Smt 5-8)</div>
                                        <small class="text-muted">Metodologi Penelitian</small>
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
                        
                        <div class="row mb-3 mb-md-4">
                            <div class="col-12 col-sm-8 col-lg-6">
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="kelompok" value="putra" id="kelompokPutra" checked>
                                    <label class="btn btn-outline-primary py-2 py-sm-3 flex-fill" for="kelompokPutra" id="btnPutra">
                                        <i class="bi bi-person me-1 me-sm-2"></i>
                                        <span class="d-none d-sm-inline">Santri </span>Putra
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="kelompok" value="putri" id="kelompokPutri">
                                    <label class="btn btn-outline-primary py-2 py-sm-3 flex-fill" for="kelompokPutri" id="btnPutri">
                                        <i class="bi bi-person-dress me-1 me-sm-2"></i>
                                        <span class="d-none d-sm-inline">Santri </span>Putri
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4 col-lg-6 mt-2 mt-sm-0">
                                <div class="text-center text-sm-end">
                                    <span class="badge bg-light text-dark fs-6 py-2 px-3 w-100 w-sm-auto">
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

                        <!-- Desktop: Tabel Absensi -->
                        <div class="d-none d-lg-block">
                            <div class="table-responsive rounded border">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th style="width: 8%" class="text-center">#</th>
                                            <th style="width: 35%" class="ps-3">
                                                <i class="bi bi-person me-1"></i>Nama Santri
                                            </th>
                                            <th style="width: 12%" class="text-center">
                                                <i class="bi bi-house me-1"></i>Kamar
                                            </th>
                                            <th style="width: 45%" class="text-center">
                                                <i class="bi bi-check-circle me-1"></i>Status Kehadiran
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="absensi-table-body-desktop">
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

                        <!-- Mobile & Tablet: Card Layout -->
                        <div class="d-lg-none" id="absensi-card-container">
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-hourglass-split fs-1 d-block mb-2 opacity-50"></i>
                                <div class="fw-semibold">Pilih kelompok santri untuk memulai absensi</div>
                                <small>Data santri akan dimuat secara otomatis</small>
                            </div>
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
            const tbodyDesktop = document.getElementById('absensi-table-body-desktop');
            const cardContainer = document.getElementById('absensi-card-container');
            const santriCount = document.getElementById('santriCount');
            const submitBtn = document.getElementById('submitBtn');
            const loadingIndicator = document.getElementById('loadingIndicator');
            
            // Show loading
            loadingIndicator.classList.remove('d-none');
            tbodyDesktop.innerHTML = '';
            cardContainer.innerHTML = '';
            
            // Simulate loading delay untuk UX
            setTimeout(() => {
                // Hide loading
                loadingIndicator.classList.add('d-none');
                
                // Update counter
                santriCount.textContent = `${dataSantri.length} santri dimuat`;
                
                // Jika tidak ada data
                if (dataSantri.length === 0) {
                    const emptyStateHTML = `
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                            <div class="fw-semibold">Tidak ada data santri ${kelompok} ditemukan</div>
                            <small>Pastikan data sudah ditambahkan ke Google Sheets</small>
                        </div>
                    `;
                    
                    tbodyDesktop.innerHTML = `<tr><td colspan="4">${emptyStateHTML}</td></tr>`;
                    cardContainer.innerHTML = emptyStateHTML;
                    submitBtn.disabled = true;
                    return;
                }
                
                // Render setiap baris santri
                dataSantri.forEach((santri, index) => {
                    const [idSantri, namaSantri, kamarSantri] = santri;
                    const nomorUrut = index + 1;
                    
                    // Buat ID unik menggunakan idSantri yang sudah dibersihkan dari karakter khusus
                    const cleanSantriId = (idSantri || `santri_${index}`).replace(/[^a-zA-Z0-9]/g, '_');
                    
                    // Hidden inputs untuk form submission
                    const hiddenInputs = `
                        <input type="hidden" name="santri[${cleanSantriId}][id]" value="${idSantri || ''}">
                        <input type="hidden" name="santri[${cleanSantriId}][nama]" value="${namaSantri || ''}">
                        <input type="hidden" name="santri[${cleanSantriId}][kamar]" value="${kamarSantri || ''}">
                        <input type="hidden" name="santri[${cleanSantriId}][index]" value="${index}">
                    `;
                    
                    // Desktop Table Row
                    const rowHTML = `
                        <tr class="fade-in">
                            <td class="text-center fw-bold text-muted">${nomorUrut}</td>
                            <td>
                                <div class="fw-semibold text-dark">${namaSantri || 'Nama tidak tersedia'}</div>
                                <small class="text-muted">ID: ${idSantri || 'N/A'}</small>
                                ${hiddenInputs}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary fs-6 px-3 py-2">${kamarSantri || 'N/A'}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm w-100" role="group">
                                    <input type="radio" class="btn-check" name="status[${cleanSantriId}]" value="Hadir" id="status-hadir-${cleanSantriId}" autocomplete="off" checked>
                                    <label class="btn btn-outline-success" for="status-hadir-${cleanSantriId}">
                                        <i class="bi bi-check-circle me-1"></i>Hadir
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="status[${cleanSantriId}]" value="Alpha" id="status-alpha-${cleanSantriId}" autocomplete="off">
                                    <label class="btn btn-outline-danger" for="status-alpha-${cleanSantriId}">
                                        <i class="bi bi-x-circle me-1"></i>Alpha
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="status[${cleanSantriId}]" value="Izin" id="status-izin-${cleanSantriId}" autocomplete="off">
                                    <label class="btn btn-outline-warning" for="status-izin-${cleanSantriId}">
                                        <i class="bi bi-exclamation-circle me-1"></i>Izin
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="status[${cleanSantriId}]" value="Sakit" id="status-sakit-${cleanSantriId}" autocomplete="off">
                                    <label class="btn btn-outline-info" for="status-sakit-${cleanSantriId}">
                                        <i class="bi bi-thermometer me-1"></i>Sakit
                                    </label>
                                </div>
                                
                                <div class="mt-2">
                                    <input type="text" class="form-control form-control-sm" 
                                           name="keterangan[${cleanSantriId}]" 
                                           placeholder="Keterangan tambahan (opsional)" 
                                           id="keterangan-${cleanSantriId}">
                                </div>
                            </td>
                        </tr>
                    `;
                    
                    // Mobile Card Layout
                    const cardHTML = `
                        <div class="card santri-card mb-3 fade-in shadow-sm border-0" style="background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);">
                            <div class="card-body p-3">
                                <div class="row align-items-start">
                                    <!-- Informasi Santri -->
                                    <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                <span class="fw-bold text-primary fs-6">${nomorUrut}</span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="fw-bold mb-1 text-dark">${namaSantri || 'Nama tidak tersedia'}</h6>
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <small class="text-muted">
                                                        <i class="bi bi-person-badge me-1"></i>
                                                        ID: ${idSantri || 'N/A'}
                                                    </small>
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                                        <i class="bi bi-house-door me-1"></i>
                                                        ${kamarSantri || 'N/A'}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Status Kehadiran -->
                                    <div class="col-12 col-sm-6">
                                        <h6 class="fw-semibold mb-2 text-muted">
                                            <i class="bi bi-check-circle me-1"></i>Status Kehadiran
                                        </h6>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <input type="radio" class="btn-check" name="status[${cleanSantriId}]" value="Hadir" id="status-hadir-mobile-${cleanSantriId}" autocomplete="off" checked>
                                                <label class="btn btn-outline-success w-100 mobile-status-btn" for="status-hadir-mobile-${cleanSantriId}">
                                                    <i class="bi bi-check-circle d-block mb-1 fs-5"></i>
                                                    <small class="fw-semibold">Hadir</small>
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <input type="radio" class="btn-check" name="status[${cleanSantriId}]" value="Alpha" id="status-alpha-mobile-${cleanSantriId}" autocomplete="off">
                                                <label class="btn btn-outline-danger w-100 mobile-status-btn" for="status-alpha-mobile-${cleanSantriId}">
                                                    <i class="bi bi-x-circle d-block mb-1 fs-5"></i>
                                                    <small class="fw-semibold">Alpha</small>
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <input type="radio" class="btn-check" name="status[${cleanSantriId}]" value="Izin" id="status-izin-mobile-${cleanSantriId}" autocomplete="off">
                                                <label class="btn btn-outline-warning w-100 mobile-status-btn" for="status-izin-mobile-${cleanSantriId}">
                                                    <i class="bi bi-exclamation-circle d-block mb-1 fs-5"></i>
                                                    <small class="fw-semibold">Izin</small>
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <input type="radio" class="btn-check" name="status[${cleanSantriId}]" value="Sakit" id="status-sakit-mobile-${cleanSantriId}" autocomplete="off">
                                                <label class="btn btn-outline-info w-100 mobile-status-btn" for="status-sakit-mobile-${cleanSantriId}">
                                                    <i class="bi bi-thermometer d-block mb-1 fs-5"></i>
                                                    <small class="fw-semibold">Sakit</small>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <!-- Keterangan -->
                                        <div class="mt-3">
                                            <input type="text" class="form-control form-control-sm" 
                                                   name="keterangan[${cleanSantriId}]" 
                                                   placeholder="Keterangan tambahan (opsional)" 
                                                   id="keterangan-mobile-${cleanSantriId}">
                                        </div>
                                    </div>
                                </div>
                                ${hiddenInputs}
                            </div>
                        </div>
                    `;
                    
                    tbodyDesktop.insertAdjacentHTML('beforeend', rowHTML);
                    cardContainer.insertAdjacentHTML('beforeend', cardHTML);
                });
                
                // Enable submit button
                submitBtn.disabled = false;
                
                // Add keterangan input sync for mobile/desktop
                addKeteranganSync();
                
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

        // Sync keterangan inputs between desktop and mobile views
        function addKeteranganSync() {
            // Sync keterangan inputs
            const desktopKeteranganInputs = document.querySelectorAll('input[id^="keterangan-"][id$=""]');
            const mobileKeteranganInputs = document.querySelectorAll('input[id^="keterangan-mobile-"]');
            
            desktopKeteranganInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const santriId = this.id.replace('keterangan-', '');
                    const mobileEquivalent = document.getElementById(`keterangan-mobile-${santriId}`);
                    if (mobileEquivalent) {
                        mobileEquivalent.value = this.value;
                    }
                });
            });
            
            mobileKeteranganInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const santriId = this.id.replace('keterangan-mobile-', '');
                    const desktopEquivalent = document.getElementById(`keterangan-${santriId}`);
                    if (desktopEquivalent) {
                        desktopEquivalent.value = this.value;
                    }
                });
            });
        }

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
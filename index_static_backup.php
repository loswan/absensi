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
        <form method="POST" action="proses_absen_simple.php" id="formAbsensi">
            <!-- Form Kontrol -->
            <div class="row mb-3 mb-md-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0 d-flex align-items-center">
                                <i class="bi bi-gear me-2"></i>
                                Pengaturan Absensi
                            </h5>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <div class="row g-3">
                                <!-- Pilih Kegiatan -->
                                <div class="col-12 col-md-6">
                                    <label for="kegiatan" class="form-label fw-semibold">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        Pilih Kegiatan
                                    </label>
                                    <select class="form-select form-select-lg" id="kegiatan" name="kegiatan" required>
                                        <option value="">-- Pilih Kegiatan --</option>
                                        <optgroup label="🕌 Sholat Wajib">
                                            <option value="Sholat Maghrib">Sholat Maghrib</option>
                                            <option value="Sholat Isya">Sholat Isya</option>
                                            <option value="Sholat Subuh">Sholat Subuh</option>
                                        </optgroup>
                                        <optgroup label="📖 Ngaji Semester 1-4">
                                            <option value="Ngaji Selasa (Bidayatul Hidayah)">Ngaji Selasa (Bidayatul Hidayah)</option>
                                            <option value="Ngaji Rabu (Adabul 'Alim)">Ngaji Rabu (Adabul 'Alim)</option>
                                            <option value="Ngaji Kamis (Fathul Mu'in)">Ngaji Kamis (Fathul Mu'in)</option>
                                            <option value="Ngaji Jumat (Arba'in an-Nawawi)">Ngaji Jumat (Arba'in an-Nawawi)</option>
                                        </optgroup>
                                        <optgroup label="📚 Ngaji Semester 5-8">
                                            <option value="Ngaji Senin (Kifayatul Atqiya')">Ngaji Senin (Kifayatul Atqiya')</option>
                                            <option value="Ngaji Rabu (Lubbul Ushul)">Ngaji Rabu (Lubbul Ushul)</option>
                                            <option value="Ngaji Jumat (Metodologi Penelitian)">Ngaji Jumat (Metodologi Penelitian)</option>
                                        </optgroup>
                                    </select>
                                </div>

                                <!-- Kelompok Santri -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-people me-1"></i>
                                        Kelompok Santri
                                    </label>
                                    <div class="btn-group w-100" role="group" aria-label="Pilihan Kelompok Santri">
                                        <input type="radio" class="btn-check" name="kelompok" id="putra" value="putra" checked>
                                        <label class="btn btn-outline-primary btn-lg" for="putra">
                                            <i class="bi bi-person me-1"></i>
                                            Santri Putra
                                        </label>

                                        <input type="radio" class="btn-check" name="kelompok" id="putri" value="putri">
                                        <label class="btn btn-outline-primary btn-lg" for="putri">
                                            <i class="bi bi-person-dress me-1"></i>
                                            Santri Putri
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Daftar Santri -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0 d-flex align-items-center">
                                <i class="bi bi-list-check me-2"></i>
                                Daftar Absensi Santri
                                <span class="badge bg-light text-dark ms-auto" id="jumlahSantri">0 Santri</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0" id="tabelSantri">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-center" width="8%">No.</th>
                                            <th width="28%">Nama Santri</th>
                                            <th class="d-none d-md-table-cell" width="14%">Kamar</th>
                                            <th class="text-center" width="35%">Status Kehadiran</th>
                                            <th class="d-none d-lg-table-cell" width="15%">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodySantri">
                                        <!-- Data santri akan dimuat di sini -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Submit -->
            <div class="row">
                <div class="col-12">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg py-3" id="btnSubmit">
                            <i class="bi bi-check-circle me-2"></i>
                            <span class="submit-text">Simpan Absensi</span>
                            <div class="spinner-border spinner-border-sm ms-2 d-none" role="status" id="loadingSpinner">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Data dummy santri
        const dataSantri = {
            putra: [
                {id: 'P001', nama: 'Bachtiar Akfa Jauhari', kamar: 'Kamar 1'},
                {id: 'P002', nama: 'Ahmad Fauzi Rahman', kamar: 'Kamar 1'},
                {id: 'P003', nama: 'Muhammad Ridho Pratama', kamar: 'Kamar 2'},
                {id: 'P004', nama: 'Abdul Aziz Hakim', kamar: 'Kamar 2'},
                {id: 'P005', nama: 'Fadhil Nurdin Santoso', kamar: 'Kamar 3'},
                {id: 'P006', nama: 'Ilham Maulana Yusuf', kamar: 'Kamar 3'},
                {id: 'P007', nama: 'Zaki Mubarak Ali', kamar: 'Kamar 4'},
                {id: 'P008', nama: 'Dani Setiawan Putra', kamar: 'Kamar 4'},
                {id: 'P009', nama: 'Rofiq Abdillah', kamar: 'Kamar 5'},
                {id: 'P010', nama: 'Hafidz Imam Syafi\'i', kamar: 'Kamar 5'}
            ],
            putri: [
                {id: 'PI001', nama: 'Siti Aisyah Rahmawati', kamar: 'Kamar A'},
                {id: 'PI002', nama: 'Fatimah Zahra Anisa', kamar: 'Kamar A'},
                {id: 'PI003', nama: 'Khadijah Nur Hidayah', kamar: 'Kamar B'},
                {id: 'PI004', nama: 'Maryam Salsabila', kamar: 'Kamar B'},
                {id: 'PI005', nama: 'Aminah Putri Maharani', kamar: 'Kamar C'},
                {id: 'PI006', nama: 'Hafsah Dwi Lestari', kamar: 'Kamar C'},
                {id: 'PI007', nama: 'Zainab Aulia Rahma', kamar: 'Kamar D'},
                {id: 'PI008', nama: 'Ruqayyah Indah Sari', kamar: 'Kamar D'},
                {id: 'PI009', nama: 'Ummu Salamah', kamar: 'Kamar E'},
                {id: 'PI010', nama: 'Juwairiyah Fitria', kamar: 'Kamar E'}
            ]
        };

        // Fungsi untuk memuat data santri
        function muatDataSantri(kelompok) {
            const tbody = document.getElementById('tbodySantri');
            const jumlahSantriEl = document.getElementById('jumlahSantri');
            tbody.innerHTML = '';
            
            const santriList = dataSantri[kelompok];
            jumlahSantriEl.textContent = `${santriList.length} Santri`;
            
            santriList.forEach((santri, index) => {
                const row = `
                    <tr>
                        <td class="text-center fw-bold">${index + 1}</td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold">${santri.nama}</span>
                                <small class="text-muted d-md-none">${santri.kamar}</small>
                            </div>
                            <input type="hidden" name="santri[${index}][id]" value="${santri.id}">
                            <input type="hidden" name="santri[${index}][nama]" value="${santri.nama}">
                            <input type="hidden" name="santri[${index}][kamar]" value="${santri.kamar}">
                        </td>
                        <td class="d-none d-md-table-cell">
                            <span class="badge bg-light text-dark">${santri.kamar}</span>
                        </td>
                        <td>
                            <div class="btn-group-vertical btn-group-sm w-100 d-block d-md-none mb-2" role="group">
                                <input type="radio" class="btn-check" name="santri[${index}][status]" id="hadir_${index}" value="Hadir" checked>
                                <label class="btn btn-outline-success btn-sm" for="hadir_${index}">
                                    <i class="bi bi-check-lg me-1"></i>Hadir
                                </label>
                                
                                <input type="radio" class="btn-check status-radio" name="santri[${index}][status]" id="sakit_${index}" value="Sakit" data-index="${index}">
                                <label class="btn btn-outline-warning btn-sm" for="sakit_${index}">
                                    <i class="bi bi-thermometer-half me-1"></i>Sakit
                                </label>
                                
                                <input type="radio" class="btn-check status-radio" name="santri[${index}][status]" id="izin_${index}" value="Izin" data-index="${index}">
                                <label class="btn btn-outline-info btn-sm" for="izin_${index}">
                                    <i class="bi bi-hand-index me-1"></i>Izin
                                </label>
                                
                                <input type="radio" class="btn-check status-radio" name="santri[${index}][status]" id="alfa_${index}" value="Alfa" data-index="${index}">
                                <label class="btn btn-outline-danger btn-sm" for="alfa_${index}">
                                    <i class="bi bi-x-lg me-1"></i>Alfa
                                </label>
                            </div>
                            
                            <div class="btn-group btn-group-sm d-none d-md-flex" role="group">
                                <input type="radio" class="btn-check" name="santri[${index}][status]" id="hadir_md_${index}" value="Hadir" checked>
                                <label class="btn btn-outline-success btn-sm" for="hadir_md_${index}">
                                    <i class="bi bi-check-lg"></i>
                                </label>
                                
                                <input type="radio" class="btn-check status-radio" name="santri[${index}][status]" id="sakit_md_${index}" value="Sakit" data-index="${index}">
                                <label class="btn btn-outline-warning btn-sm" for="sakit_md_${index}">
                                    <i class="bi bi-thermometer-half"></i>
                                </label>
                                
                                <input type="radio" class="btn-check status-radio" name="santri[${index}][status]" id="izin_md_${index}" value="Izin" data-index="${index}">
                                <label class="btn btn-outline-info btn-sm" for="izin_md_${index}">
                                    <i class="bi bi-hand-index"></i>
                                </label>
                                
                                <input type="radio" class="btn-check status-radio" name="santri[${index}][status]" id="alfa_md_${index}" value="Alfa" data-index="${index}">
                                <label class="btn btn-outline-danger btn-sm" for="alfa_md_${index}">
                                    <i class="bi bi-x-lg"></i>
                                </label>
                            </div>
                            
                            <!-- Input keterangan untuk mobile -->
                            <input type="text" class="form-control form-control-sm keterangan-input mt-2 d-block d-lg-none" 
                                   name="santri[${index}][keterangan]" id="keterangan_mobile_${index}" 
                                   placeholder="Alasan (jika sakit/izin)..." disabled>
                        </td>
                        <td class="d-none d-lg-table-cell">
                            <input type="text" class="form-control form-control-sm keterangan-input" 
                                   name="santri[${index}][keterangan]" id="keterangan_${index}" 
                                   placeholder="Alasan..." disabled>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            // Event listener untuk mengaktifkan/menonaktifkan keterangan
            setupKeteranganListeners();
        }

        // Fungsi untuk setup event listener keterangan
        function setupKeteranganListeners() {
            const statusRadios = document.querySelectorAll('.status-radio');
            const hadirRadios = document.querySelectorAll('input[value="Hadir"]');

            statusRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        const index = this.getAttribute('data-index');
                        const keteranganInput = document.getElementById(`keterangan_${index}`);
                        const keteranganMobileInput = document.getElementById(`keterangan_mobile_${index}`);
                        
                        if (keteranganInput) {
                            keteranganInput.disabled = false;
                            keteranganInput.focus();
                        }
                        if (keteranganMobileInput) {
                            keteranganMobileInput.disabled = false;
                        }
                    }
                });
            });

            hadirRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        const name = this.getAttribute('name');
                        const index = name.match(/\[(\d+)\]/)[1];
                        const keteranganInput = document.getElementById(`keterangan_${index}`);
                        const keteranganMobileInput = document.getElementById(`keterangan_mobile_${index}`);
                        
                        if (keteranganInput) {
                            keteranganInput.disabled = true;
                            keteranganInput.value = '';
                        }
                        if (keteranganMobileInput) {
                            keteranganMobileInput.disabled = true;
                            keteranganMobileInput.value = '';
                        }
                    }
                });
            });
        }

        // Event listener untuk perubahan kelompok santri
        document.querySelectorAll('input[name="kelompok"]').forEach(radio => {
            radio.addEventListener('change', function() {
                muatDataSantri(this.value);
            });
        });

        // Validasi form sebelum submit
        document.getElementById('formAbsensi').addEventListener('submit', function(e) {
            const kegiatan = document.getElementById('kegiatan').value;
            const btnSubmit = document.getElementById('btnSubmit');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const submitText = document.querySelector('.submit-text');
            
            if (!kegiatan) {
                e.preventDefault();
                showAlert('Silakan pilih kegiatan terlebih dahulu!', 'warning');
                return;
            }
            
            // Tampilkan loading state
            btnSubmit.disabled = true;
            loadingSpinner.classList.remove('d-none');
            submitText.textContent = 'Menyimpan...';
        });

        // Fungsi untuk menampilkan alert
        function showAlert(message, type = 'info') {
            const alertContainer = document.getElementById('alertContainer');
            const alertHTML = `
                <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            alertContainer.innerHTML = alertHTML;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                const alert = alertContainer.querySelector('.alert');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        }

        // Muat data awal (santri putra)
        document.addEventListener('DOMContentLoaded', function() {
            muatDataSantri('putra');
        });
    </script>
</body>
</html>
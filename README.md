# Sistem Absensi Santri YPMI Al-Firdaus

Aplikasi web sederhana untuk mencatat kehadiran santri dalam kegiatan sholat dan ngaji di pondok pesantren.

## 🚀 Fitur Utama

- ✅ **Mobile-First Responsive Design** - Optimal di smartphone, tablet, dan desktop
- ✅ Pencatatan absensi untuk kegiatan sholat (Maghrib, Isya, Subuh)  
- ✅ **Jadwal Ngaji Per Semester** - Terorganisir berdasarkan semester 1-4 dan 5-8
- ✅ Pemisahan data santri putra dan putri dengan interface yang intuitif
- ✅ Status kehadiran: Hadir, Sakit, Izin, Alfa dengan ikon yang jelas
- ✅ Integrasi dengan Google Sheets sebagai database cloud
- ✅ **Touch-Friendly Interface** - Tombol dan form yang mudah disentuh
- ✅ Efisiensi data (hanya menyimpan ketidakhadiran untuk menghemat storage)
- ✅ **Progressive Enhancement** - Berfungsi baik di perangkat lama maupun baru

## 📋 Teknologi yang Digunakan

- **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript/jQuery
- **Backend:** PHP 7.4+
- **Database:** Google Sheets API
- **Server:** Apache/Nginx + PHP

## 📁 Struktur File

```
absensi/
├── index.php           # Halaman utama absensi
├── proses_absen.php    # Logic backend & integrasi Google Sheets
├── style.css           # Styling tambahan
├── credentials.json    # File kredensial Google (tidak termasuk)
├── composer.json       # Dependencies PHP (akan dibuat)
├── vendor/             # Folder Composer (akan dibuat)
├── README.md           # Dokumentasi ini
└── absensi_log.txt     # Log sementara untuk testing
```

## ⚙️ Setup dan Instalasi

### 1. Persiapan Server

```bash
# Pastikan PHP 7.4+ terinstall
php --version

# Install Composer (jika belum ada)
# Download dari: https://getcomposer.org/download/
```

### 2. Setup Google Sheets API

1. **Google Cloud Console Setup:**
   - Buka [Google Cloud Console](https://console.cloud.google.com/)
   - Buat project baru atau pilih project existing
   - Aktifkan Google Sheets API di Library
   - Buat Service Account di IAM & Admin > Service Accounts
   - Download file JSON credentials dan simpan sebagai `credentials.json`

2. **Google Sheets Setup:**
   - Buat Google Sheets baru dengan 3 sheet:
     - `Santri Putra` (kolom: ID_Santri, Nama_Lengkap, Kamar, Status_Santri)
     - `Santri Putri` (kolom: ID_Santri, Nama_Lengkap, Kamar, Status_Santri)  
     - `Log Absensi` (kolom: Timestamp, ID_Santri, Nama_Santri, Jenis_Kegiatan, Status_Kehadiran, Keterangan, Tanggal)
   - Berikan akses edit kepada email service account
   - Salin ID dari URL Google Sheets

### 3. Install Dependencies

```bash
# Masuk ke direktori project
cd /path/to/absensi

# Install Google Client Library
composer require google/apiclient
```

### 4. Konfigurasi

Edit file `proses_absen.php`:
```php
// Ganti dengan path file credentials Anda
$credentialsPath = 'path/to/your/credentials.json';

// Ganti dengan ID Google Sheets Anda
$spreadsheetId = 'YOUR_GOOGLE_SHEETS_ID_HERE';
```

Uncomment kode Google Sheets API di `proses_absen.php` setelah setup selesai.

### 5. Testing

1. Akses `http://localhost/absensi/` di browser
2. Pilih kegiatan dan kelompok santri  
3. Isi status kehadiran dan keterangan
4. Klik "Simpan Absensi"
5. Cek file `absensi_log.txt` untuk melihat hasil (mode testing)

## 🔧 Konfigurasi Lanjutan

### Menambah Data Santri

Edit array `dataSantri` di `index.php`:
```javascript
const dataSantri = {
    putra: [
        {id: 'P001', nama: 'Nama Santri', kamar: 'Kamar 1'},
        // tambahkan santri lainnya...
    ],
    putri: [
        {id: 'PI001', nama: 'Nama Santriwati', kamar: 'Kamar A'},
        // tambahkan santriwati lainnya...
    ]
};
```

### Menambah Kegiatan Baru

Edit dropdown kegiatan di `index.php`:
```html
<option value="Kegiatan Baru">Kegiatan Baru</option>
```

### Custom Styling

Modifikasi `style.css` untuk menyesuaikan tampilan sesuai brand pesantren.

## 🐛 Troubleshooting

### Error: "Class 'Google_Client' not found"
- Pastikan Composer terinstall
- Jalankan `composer install` di direktori project
- Pastikan path `vendor/autoload.php` benar

### Error: "Permission denied" saat akses Google Sheets
- Pastikan service account memiliki akses edit ke Google Sheets
- Cek apakah Google Sheets API sudah diaktifkan
- Periksa file credentials.json

### Data tidak tersimpan
- Cek file `error_log.txt` untuk detail error
- Pastikan koneksi internet stabil
- Verifikasi ID Google Sheets benar

## 📊 Format Data Google Sheets

### Sheet "Santri Putra/Putri"
| ID_Santri | Nama_Lengkap | Kamar | Status_Santri |
|-----------|--------------|-------|---------------|
| P001      | Ahmad Fauzi  | Kamar 1| Aktif        |

### Sheet "Log Absensi"  
| Timestamp | ID_Santri | Nama_Santri | Jenis_Kegiatan | Status_Kehadiran | Keterangan | Tanggal |
|-----------|-----------|-------------|----------------|------------------|------------|---------|
| 2025-01-01 19:30:00 | P001 | Ahmad Fauzi | Sholat Maghrib | Sakit | Demam | 2025-01-01 |

## 🔒 Keamanan

- File `credentials.json` JANGAN di-commit ke Git
- Tambahkan `credentials.json` ke `.gitignore`
- Gunakan HTTPS di production
- Validasi input user untuk mencegah injection

## 📝 Changelog

### v2.0.0 (2025-10-14) - Responsive Overhaul
- 🔥 **Complete Mobile-First Redesign** 
- ✅ Pemisahan jadwal ngaji berdasarkan semester (1-4 dan 5-8)
- ✅ Bootstrap Grid System untuk responsivitas penuh
- ✅ Touch-friendly buttons dan form controls
- ✅ Conditional display untuk berbagai ukuran layar
- ✅ Progressive loading states dan better UX
- ✅ Icons dari Bootstrap Icons untuk UI yang lebih modern
- ✅ Media queries untuk tablet, desktop, dan large screens

### v1.0.0 (2025-01-14) - Initial Release
- ✅ Fitur dasar absensi santri
- ✅ Integrasi Google Sheets API
- ✅ Interface responsif Bootstrap 5
- ✅ Validasi form dan error handling

## 🤝 Kontribusi

Jika ada bug atau saran perbaikan, silakan buat issue atau pull request.

## 📞 Support

Untuk pertanyaan teknis, hubungi pengembang melalui:
- Email: [email pengembang]
- WhatsApp: [nomor WhatsApp]

---

**Developed with ❤️ for YPMI Al-Firdaus**
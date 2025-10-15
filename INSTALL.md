# 🚀 PETUNJUK INSTALASI CEPAT

## Langkah 1: Download dan Extract
1. Extract file aplikasi ke folder web server (misal: `C:\laragon\www\absensi\`)

## Langkah 2: Install Dependencies
```bash
# Buka terminal/command prompt di folder aplikasi
cd C:\laragon\www\absensi

# Install dependencies menggunakan Composer
composer install
```

## Langkah 3: Setup Google Sheets API

### A. Google Cloud Console
1. Buka https://console.cloud.google.com/
2. Buat project baru: "Absensi Santri"
3. Aktifkan Google Sheets API
4. Buat Service Account:
   - Nama: "absensi-service"
   - Role: Editor
   - Download JSON key → simpan sebagai `credentials.json`

### B. Google Sheets
1. Buat Google Sheets baru
2. Buat 3 sheet:
   - **Santri Putra**: ID_Santri | Nama_Lengkap | Kamar | Status_Santri
   - **Santri Putri**: ID_Santri | Nama_Lengkap | Kamar | Status_Santri
   - **Log Absensi**: Timestamp | ID_Santri | Nama_Santri | Jenis_Kegiatan | Status_Kehadiran | Keterangan | Tanggal
3. Share dengan email service account (berikan akses Edit)
4. Copy ID dari URL (contoh: `1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms`)

## Langkah 4: Konfigurasi Aplikasi
Edit file `proses_absen.php`:
```php
// Baris 30-32, ganti dengan data Anda:
$spreadsheetId = 'YOUR_SHEETS_ID_HERE'; // Ganti dengan ID Google Sheets Anda
$credentialsPath = 'credentials.json';   // Path ke file credentials
$sheetName = 'Log Absensi';             // Nama sheet target
```

**TIDAK PERLU UNCOMMENT LAGI** - Kode Google Sheets API sudah aktif!

## Langkah 5: Testing
### Test Koneksi Google Sheets:
```bash
# Buka di browser untuk test koneksi:
http://localhost/absensi/test_google_connection.php
```

### Test Aplikasi:
1. Buka browser: `http://localhost/absensi/`
2. Pilih kegiatan dan kelompok
3. Ubah beberapa status santri dari "Hadir" ke "Sakit"/"Izin"/"Alfa"
4. Isi keterangan untuk yang tidak hadir
5. Klik "Simpan Absensi"
6. Cek Google Sheets untuk memastikan data tersimpan

### Jika Ada Error:
- Lihat error detail di halaman (debug mode aktif)
- Cek file `error_log.txt` untuk detail error
- Ikuti panduan `TROUBLESHOOTING.md`

## 🛠️ Troubleshooting Cepat

### Error "Class 'Google_Client' not found"
```bash
composer install --no-dev
```

### Error "Permission denied" Google Sheets
- Pastikan service account punya akses Edit
- Cek ID Google Sheets sudah benar

### Data tidak muncul di Google Sheets  
- Cek koneksi internet
- Periksa file `error_log.txt` untuk detail error

## 📞 Butuh Bantuan?
Jika ada masalah, cek file `README.md` untuk panduan lengkap atau hubungi pengembang.
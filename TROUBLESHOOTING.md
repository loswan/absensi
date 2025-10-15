# 🛠️ Troubleshooting Google Sheets Integration

## 🚨 Masalah Umum & Solusinya

### 1. **Error: "Class 'Google_Client' not found"**
```
❌ Problem: Library Google Client tidak ter-load
✅ Solution: 
   - Pastikan sudah install: composer require google/apiclient
   - Cek file vendor/autoload.php ada
   - Pastikan require_once 'vendor/autoload.php' tidak ter-comment
```

### 2. **Error: "File credentials tidak ditemukan"**
```
❌ Problem: File credentials.json tidak ada atau path salah
✅ Solution:
   - Download credentials.json dari Google Cloud Console
   - Letakkan di folder yang sama dengan proses_absen.php
   - Pastikan nama file exact: credentials.json (case-sensitive)
```

### 3. **Error: "Tidak dapat mengakses spreadsheet dengan ID"**
```
❌ Problem: Spreadsheet ID salah atau tidak ada akses
✅ Solution:
   - Cek ID dari URL: docs.google.com/spreadsheets/d/[ID_DISINI]/edit
   - Pastikan service account punya akses Edit ke spreadsheet
   - Cek spreadsheet masih ada (tidak terhapus)
```

### 4. **Error: "Sheet 'Log Absensi' not found"**
```
❌ Problem: Nama sheet salah atau tidak ada
✅ Solution:
   - Pastikan ada sheet bernama exact: "Log Absensi"
   - Nama sheet case-sensitive (L besar, A besar)
   - Cek tidak ada spasi extra atau karakter tersembunyi
```

### 5. **Data tidak muncul di Google Sheets meski tidak ada error**
```
❌ Problem: Semua santri status "Hadir" 
✅ Solution:
   - Aplikasi hanya menyimpan yang TIDAK hadir
   - Ubah status beberapa santri ke Sakit/Izin/Alfa
   - Atau cek google_sheets_success.log untuk konfirmasi
```

## 🔍 Langkah Debugging

### Step 1: Jalankan Test Connection
```bash
# Buka di browser:
http://localhost/absensi/test_google_connection.php
```

### Step 2: Cek Error Log
```bash
# Cek file-file log ini:
- error_log.txt
- php_error.log  
- google_sheets_success.log
```

### Step 3: Verifikasi Setup Google Cloud

1. **Google Cloud Console Checklist:**
   - [ ] Project dibuat
   - [ ] Google Sheets API enabled
   - [ ] Service Account dibuat
   - [ ] JSON Key downloaded
   - [ ] Service Account punya role Editor

2. **Google Sheets Checklist:**
   - [ ] Spreadsheet dibuat
   - [ ] Sheet "Log Absensi" ada
   - [ ] Service account email diberi akses Edit
   - [ ] Kolom header sesuai: Timestamp, ID_Santri, Nama_Santri, Jenis_Kegiatan, Status_Kehadiran, Keterangan, Tanggal

### Step 4: Test Manual API Call

Gunakan file `test_google_connection.php` yang sudah disediakan untuk test step by step.

## 📋 Checklist Lengkap Setup

### A. Server Requirements
- [ ] PHP 7.4+ terinstall  
- [ ] Composer terinstall
- [ ] Extension php-curl enabled
- [ ] Extension php-json enabled

### B. Google Cloud Setup
- [ ] Project Google Cloud dibuat
- [ ] Google Sheets API diaktifkan di Library
- [ ] Service Account dibuat dengan role Editor
- [ ] File JSON credentials di-download
- [ ] File credentials.json diletakkan di folder project

### C. Google Sheets Setup  
- [ ] Google Sheets baru dibuat
- [ ] Sheet "Log Absensi" dibuat dengan kolom yang benar
- [ ] Service account email diberi akses Editor ke spreadsheet
- [ ] Spreadsheet ID disalin dari URL

### D. PHP Project Setup
- [ ] `composer require google/apiclient` dijalankan
- [ ] File proses_absen.php diupdate dengan ID spreadsheet yang benar
- [ ] Error reporting diaktifkan untuk debugging

## 🔧 Common Fix Commands

```bash
# Install/update dependencies
composer install
composer require google/apiclient:^2.0

# Check PHP extensions
php -m | grep curl
php -m | grep json

# Check file permissions (Linux/Mac)
ls -la credentials.json
chmod 644 credentials.json
```

## 📞 Support

### Jika Masih Bermasalah:
1. Jalankan `test_google_connection.php` 
2. Screenshot semua hasil test
3. Copy isi file `error_log.txt`
4. Berikan informasi:
   - OS dan versi PHP
   - Pesan error exact
   - Langkah yang sudah dicoba

### Quick Debug Commands:
```php
// Tambahkan di awal proses_absen.php untuk debug:
var_dump($_POST);
var_dump(file_exists('credentials.json'));
var_dump(file_exists('vendor/autoload.php'));
```

---

**🎯 Tujuan:** Setelah mengikuti panduan ini, integrasi Google Sheets harus berfungsi 100% dan data absensi akan tersimpan otomatis ke spreadsheet.
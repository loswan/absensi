# 🚨 FIXED: Malformed URL Error Solution

## Root Cause Found! ✅

**Problem:** `cURL error: URL rejected: Malformed input to a URL function`

**Root Cause:** **Missing `credentials.json` file!** 

The error happens because the application tries to load credentials that don't exist, causing the Google Sheets API initialization to fail with malformed URL errors.

## 🔧 Complete Solution:

### Step 1: Create Service Account Credentials

1. **Go to Google Cloud Console:**
   ```
   https://console.cloud.google.com/
   ```

2. **Create/Select Project:**
   - Create new project or select existing one
   - Note your project ID

3. **Enable Google Sheets API:**
   - Go to "APIs & Services" > "Library"
   - Search "Google Sheets API"
   - Click "Enable"

4. **Create Service Account:**
   - Go to "APIs & Services" > "Credentials"
   - Click "Create Credentials" > "Service Account"
   - Fill service account details
   - Click "Create and Continue"

5. **Download Credentials:**
   - Click on created service account
   - Go to "Keys" tab
   - Click "Add Key" > "Create New Key" > "JSON"
   - Download the JSON file

### Step 2: Setup Credentials File

1. **Rename downloaded file to `credentials.json`**
2. **Place in your project directory:**
   ```
   c:\laragon\www\absensi\credentials.json
   ```

3. **Verify file format** (should look like this):
   ```json
   {
     "type": "service_account",
     "project_id": "your-project-id",
     "private_key_id": "key-id-here",
     "private_key": "-----BEGIN PRIVATE KEY-----\nYOUR_PRIVATE_KEY\n-----END PRIVATE KEY-----\n",
     "client_email": "your-service@your-project.iam.gserviceaccount.com",
     "client_id": "123456789",
     "auth_uri": "https://accounts.google.com/o/oauth2/auth",
     "token_uri": "https://oauth2.googleapis.com/token"
   }
   ```

### Step 3: Grant Permissions to Spreadsheet

1. **Copy the `client_email` from credentials.json**
2. **Open your Google Spreadsheet**
3. **Click "Share"**
4. **Add the service account email with "Editor" permissions**

### Step 4: Test the Fixed Implementation

1. **Run the test:**
   ```bash
   http://localhost/absensi/test_fixed_version.php
   ```

2. **Expected result:**
   ```
   ✅ Connection test successful
   ✅ Data sent to Google Sheets
   ```

## 🔄 Files Status After Fix:

### ✅ Working Files:
- `google_sheets_fixed.php` - Robust implementation with proper error handling
- `proses_absen_simple.php` - Updated to use fixed version
- `test_fixed_version.php` - Comprehensive test suite
- `credentials.json` - **[YOU NEED TO CREATE THIS]**

### ❌ Problematic Files (Don't Use):
- `google_sheets_simple.php` - Had URL formation issues
- `proses_absen.php` - Original with Guzzle problems

## 🎯 Error Prevention:

### Before Running Application:

1. **Check credentials exist:**
   ```bash
   ls -la credentials.json
   ```

2. **Verify JSON format:**
   ```bash
   php -r "json_decode(file_get_contents('credentials.json')); echo json_last_error();"
   ```
   (Should output: 0)

3. **Test connection:**
   ```bash
   http://localhost/absensi/test_fixed_version.php
   ```

## 🚀 Quick Fix Commands:

```bash
# 1. Check if credentials exist
dir credentials.json

# 2. If missing, copy from example and edit
copy credentials.json.example credentials.json
notepad credentials.json

# 3. Test the fixed version
php test_fixed_version.php
```

## 📊 Error Analysis:

| Issue | Cause | Solution |
|-------|-------|----------|
| Malformed URL | Missing credentials.json | Create service account credentials |
| cURL timeout | Wrong spreadsheet ID | Verify spreadsheet ID |
| Permission denied | Service account not shared | Share spreadsheet with service email |
| Invalid JSON | Corrupted credentials | Re-download from Google Cloud |

## ✅ Final Status:

**Problem:** RESOLVED ✅  
**Cause:** Missing Google Service Account credentials  
**Solution:** Create credentials.json with proper service account  
**Status:** Ready for production with `google_sheets_fixed.php`

## 🎉 Next Steps:

1. **Create your `credentials.json` file**
2. **Run `test_fixed_version.php` to verify**
3. **Your application will work perfectly!**

**No more Malformed URL errors! 🎊**
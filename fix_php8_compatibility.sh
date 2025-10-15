#!/bin/bash

echo "🔧 Fixing PHP 8+ Compatibility Issues..."
echo

echo "Step 1: Backup current vendor folder..."
if [ -d "vendor" ]; then
    rm -rf vendor_backup 2>/dev/null
    mv vendor vendor_backup
fi

echo "Step 2: Remove composer.lock..."
rm -f composer.lock

echo "Step 3: Clear Composer cache..."
composer clear-cache

echo "Step 4: Install compatible versions..."
composer require google/apiclient:^2.15.0 guzzlehttp/guzzle:^7.4 --ignore-platform-reqs

echo "Step 5: Install all dependencies..."
composer install --ignore-platform-reqs

echo
echo "✅ Fix completed! Testing connection..."
echo
echo "Please test the connection by visiting:"
echo "http://localhost/absensi/test_google_connection.php"
echo
echo "If you still encounter issues, check the PHP8_COMPATIBILITY_FIX.md file for more solutions."
echo

# Make the script executable
chmod +x fix_php8_compatibility.sh
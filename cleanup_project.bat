@echo off
echo 🧹 CLEANING UP PROJECT FILES...
echo.

echo Deleting test files...
if exist debug_url_test.php del debug_url_test.php
if exist simple_test.php del simple_test.php  
if exist test_data_format.php del test_data_format.php
if exist test_dynamic_integration.php del test_dynamic_integration.php
if exist test_fixed_version.php del test_fixed_version.php
if exist test_google_connection.php del test_google_connection.php
if exist test_range_fix.php del test_range_fix.php
if exist test_simple_connection.php del test_simple_connection.php

echo Deleting backup files...
if exist index.php.backup del index.php.backup
if exist index_dynamic.php del index_dynamic.php
if exist index_static_backup.php del index_static_backup.php
if exist google_sheets_simple.php del google_sheets_simple.php
if exist proses_absen.php del proses_absen.php
if exist update_form_action.php del update_form_action.php

echo Deleting development scripts...
if exist fix_php8_compatibility.bat del fix_php8_compatibility.bat
if exist fix_php8_compatibility.sh del fix_php8_compatibility.sh

echo Deleting redundant documentation...
if exist COMPATIBILITY_SUMMARY.md del COMPATIBILITY_SUMMARY.md
if exist SIMPLE_SOLUTION_FINAL.md del SIMPLE_SOLUTION_FINAL.md
if exist PROBLEM_SOLVED_SUMMARY.md del PROBLEM_SOLVED_SUMMARY.md
if exist PHP8_COMPATIBILITY_FIX.md del PHP8_COMPATIBILITY_FIX.md

echo Deleting temporary files...
if exist config.example.php del config.example.php

echo.
echo ✅ PROJECT CLEANUP COMPLETED!
echo.
echo Your project now has a clean structure with only essential files.
echo Ready for production! 🚀
pause
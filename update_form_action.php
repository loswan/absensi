<?php
/**
 * File: update_form_action.php
 * Deskripsi: Auto-update form action untuk menggunakan simple version
 */

echo "<html><head><title>Update Form Action</title></head><body>";
echo "<h2>🔧 Auto-Update Form Action</h2>";

$indexFile = 'index.php';
$backupFile = 'index.php.backup';

if (file_exists($indexFile)) {
    // Backup original file
    if (!file_exists($backupFile)) {
        copy($indexFile, $backupFile);
        echo "✅ Backup created: $backupFile<br>";
    }
    
    // Read content
    $content = file_get_contents($indexFile);
    
    // Replace form action
    $oldAction = 'action="proses_absen.php"';
    $newAction = 'action="proses_absen_simple.php"';
    
    if (strpos($content, $oldAction) !== false) {
        $updatedContent = str_replace($oldAction, $newAction, $content);
        
        if (file_put_contents($indexFile, $updatedContent)) {
            echo "✅ Form action updated successfully!<br>";
            echo "📝 Changed: <code>$oldAction</code><br>";
            echo "📝 To: <code>$newAction</code><br>";
            
            echo "<div style='background: #d4edda; padding: 15px; margin: 15px 0; border: 1px solid #c3e6cb; border-radius: 5px;'>";
            echo "<h3>✅ Update Complete!</h3>";
            echo "<p>Your form now uses the simple cURL method that avoids all Guzzle/PHP 8+ compatibility issues.</p>";
            echo "<p><strong>Benefits:</strong></p>";
            echo "<ul>";
            echo "<li>No more Guzzle HTTP errors</li>";
            echo "<li>Native cURL implementation</li>";
            echo "<li>PHP 8+ compatible</li>";
            echo "<li>Faster and more stable</li>";
            echo "</ul>";
            echo "</div>";
            
        } else {
            echo "❌ Failed to write updated content<br>";
        }
    } elseif (strpos($content, $newAction) !== false) {
        echo "ℹ️ Form action is already updated to simple version<br>";
    } else {
        echo "⚠️ Form action not found in expected format<br>";
    }
    
} else {
    echo "❌ index.php file not found<br>";
}

echo "<hr>";
echo "<h3>🧪 Next Steps:</h3>";
echo "<ol>";
echo "<li><a href='test_simple_connection.php'>Test Simple Connection</a></li>";
echo "<li><a href='index.php'>Test Main Application</a></li>";
echo "<li>Fill out form and check if data saves to Google Sheets</li>";
echo "</ol>";

if (file_exists($backupFile)) {
    echo "<p><strong>Recovery:</strong> If something goes wrong, restore from <code>$backupFile</code></p>";
}

echo "</body></html>";
?>
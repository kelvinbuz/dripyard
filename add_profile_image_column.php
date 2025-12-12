<?php
// Add profile_image column to users table
require_once __DIR__ . '/backend/db.php';

$pdo = getPDO();

try {
    // Add profile_image column
    $stmt = $pdo->prepare("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) AFTER password");
    $stmt->execute();
    echo "✅ Profile image column added successfully to users table\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "ℹ️ Profile image column already exists\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

?>

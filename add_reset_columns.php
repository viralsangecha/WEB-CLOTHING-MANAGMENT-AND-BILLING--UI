<?php
$conn = new mysqli("localhost", "root", "", "admins");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if reset_token column exists
$check_columns = $conn->query("SHOW COLUMNS FROM admin LIKE 'reset_token'");
if ($check_columns->num_rows == 0) {
    // Add the columns if they don't exist
    $conn->query("ALTER TABLE admin ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL");
    $conn->query("ALTER TABLE admin ADD COLUMN reset_expires DATETIME DEFAULT NULL");
    echo "Reset token columns added successfully!";
} else {
    echo "Reset token columns already exist.";
}

$conn->close();
?> 
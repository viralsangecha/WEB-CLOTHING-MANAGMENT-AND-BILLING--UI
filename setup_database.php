<?php
$conn = new mysqli("localhost", "root", "", "admins");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if sales table exists
$table_exists = $conn->query("SHOW TABLES LIKE 'sales'");

if ($table_exists->num_rows > 0) {
    // Table exists, check if created_at column exists
    $column_exists = $conn->query("SHOW COLUMNS FROM sales LIKE 'created_at'");
    
    if ($column_exists->num_rows === 0) {
        // Add created_at column
        $sql = "ALTER TABLE sales ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        if ($conn->query($sql) === TRUE) {
            echo "Added created_at column to existing sales table<br>";
        } else {
            echo "Error adding created_at column: " . $conn->error . "<br>";
        }
        
        // Update existing records with current timestamp
        $sql = "UPDATE sales SET created_at = CURRENT_TIMESTAMP WHERE created_at IS NULL";
        if ($conn->query($sql) === TRUE) {
            echo "Updated existing records with timestamps<br>";
        } else {
            echo "Error updating timestamps: " . $conn->error . "<br>";
        }
    } else {
        echo "Sales table already has created_at column<br>";
    }
} else {
    // Create new sales table with all required columns
    $sql = "CREATE TABLE sales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_id INT NOT NULL,
        item_name VARCHAR(255) NOT NULL,
        customer_name VARCHAR(255) NOT NULL,
        quantity_sold INT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Sales table created successfully<br>";
    } else {
        echo "Error creating sales table: " . $conn->error . "<br>";
    }
}

$conn->close();
echo "Database setup complete!";
?> 
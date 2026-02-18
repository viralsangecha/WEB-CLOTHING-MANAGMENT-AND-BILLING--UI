<?php
session_start();

if (!isset($_SESSION['admin'])) {
    echo "error: Not authorized";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "error: Invalid request method";
    exit();
}

$conn = new mysqli("localhost", "root", "", "admins");
if ($conn->connect_error) {
    echo "error: Database connection failed";
    exit();
}

// Check if bill_number column exists, if not create it
$result = $conn->query("SHOW COLUMNS FROM sales LIKE 'bill_number'");
if ($result->num_rows === 0) {
    $conn->query("ALTER TABLE sales ADD COLUMN bill_number VARCHAR(50)");
}

// Check if created_at column exists, if not create it
$result = $conn->query("SHOW COLUMNS FROM sales LIKE 'created_at'");
if ($result->num_rows === 0) {
    $conn->query("ALTER TABLE sales ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}

$customer_name = $_POST['customer_name'];
$cart = json_decode($_POST['cart'], true);

if (empty($cart)) {
    echo "error: Cart is empty";
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Generate bill number (format: BILL-YYYYMMDD-XXXX)
    $date = date('Ymd');
    $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(bill_number, '-', -1) AS UNSIGNED)) as last_number 
            FROM sales 
            WHERE bill_number LIKE 'BILL-$date-%'";
    
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $last_number = $row['last_number'] ?? 0;
    $new_number = str_pad($last_number + 1, 4, '0', STR_PAD_LEFT);
    $bill_number = "BILL-$date-$new_number";

    // Process each item in cart
    foreach ($cart as $item) {
        // Check stock availability
        $stmt = $conn->prepare("SELECT quantity FROM inventory WHERE ID = ?");
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $inventory = $result->fetch_assoc();
        
        if (!$inventory || $inventory['quantity'] < $item['quantity']) {
            throw new Exception("Insufficient stock for " . htmlspecialchars($item['name']));
        }

        // Update inventory
        $new_quantity = $inventory['quantity'] - $item['quantity'];
        $stmt = $conn->prepare("UPDATE inventory SET quantity = ? WHERE ID = ?");
        $stmt->bind_param("ii", $new_quantity, $item['id']);
        $stmt->execute();

        // Insert sale record with bill_number and created_at
        $stmt = $conn->prepare("INSERT INTO sales (bill_number, item_id, item_name, customer_name, quantity_sold, total_price, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sisids", $bill_number, $item['id'], $item['name'], $customer_name, $item['quantity'], $item['total']);
        $stmt->execute();
    }

    // Store bill data in session
    $_SESSION['bill_data'] = [
        'bill_number' => $bill_number,
        'customer_name' => $customer_name,
        'cart' => $cart,
        'bill_date' => date('Y-m-d H:i:s')
    ];

    // Commit transaction
    $conn->commit();
    
    echo "success";
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "error: " . $e->getMessage();
}

$conn->close();
?> 
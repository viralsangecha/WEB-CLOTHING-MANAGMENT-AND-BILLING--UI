<?php
session_start();

if (!isset($_SESSION['admin'])) {
    echo "<script>alert('You are not logged in, please log in');</script>";
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: inventory.php");
    exit();
}

// Validate required fields
if (!isset($_POST['item_name']) || !isset($_POST['quantity']) || !isset($_POST['price'])) {
    echo "<script>alert('Missing required fields');</script>";
    echo "<script>window.location.href = 'inventory.php';</script>";
    exit();
}

$item_name = trim($_POST['item_name']);
$quantity = (int)$_POST['quantity'];
$price = (float)$_POST['price'];

// Validate input
if (empty($item_name)) {
    echo "<script>alert('Item name cannot be empty');</script>";
    echo "<script>window.location.href = 'inventory.php';</script>";
    exit();
}

if ($quantity < 0) {
    echo "<script>alert('Quantity cannot be negative');</script>";
    echo "<script>window.location.href = 'inventory.php';</script>";
    exit();
}

if ($price < 0) {
    echo "<script>alert('Price cannot be negative');</script>";
    echo "<script>window.location.href = 'inventory.php';</script>";
    exit();
}

// Connect to database
$conn = new mysqli("localhost", "root", "", "admins");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if item already exists
$check_stmt = $conn->prepare("SELECT ID FROM inventory WHERE item_name = ?");
$check_stmt->bind_param("s", $item_name);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo "<script>alert('An item with this name already exists');</script>";
    echo "<script>window.location.href = 'inventory.php';</script>";
    $check_stmt->close();
    $conn->close();
    exit();
}
$check_stmt->close();

// Insert new item
$stmt = $conn->prepare("INSERT INTO inventory (item_name, quantity, price) VALUES (?, ?, ?)");
$stmt->bind_param("sid", $item_name, $quantity, $price);

if ($stmt->execute()) {
    echo "<script>alert('Item added successfully');</script>";
} else {
    echo "<script>alert('Error adding item: " . $conn->error . "');</script>";
}

$stmt->close();
$conn->close();

echo "<script>window.location.href = 'inventory.php';</script>";
?> 
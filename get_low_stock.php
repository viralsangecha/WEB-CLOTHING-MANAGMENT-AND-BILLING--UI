<?php
session_start();
$conn = new mysqli("localhost", "root", "", "admins");

$sql = "SELECT COUNT(*) as count FROM inventory WHERE quantity <= 10";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
if ($row['count'] > 0) {
    echo '<div class="notification warning">
        <i class="fas fa-exclamation-triangle"></i>
        <span>' . $row['count'] . ' items are running low on stock</span>
        <a href="Inventory.php" class="btn btn-sm">View</a>
    </div>';
}

$conn->close();
?> 
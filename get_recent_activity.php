<?php
session_start();
$conn = new mysqli("localhost", "root", "", "admins");

// Get recent sales
$sql = "SELECT s.*, c.name as customer_name, i.item_name 
        FROM sales s 
        LEFT JOIN customer c ON s.customer_name = c.name 
        LEFT JOIN inventory i ON s.item_id = i.id 
        ORDER BY s.created_at DESC LIMIT 5";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $time_ago = time_elapsed_string($row['created_at']);
        echo '<li class="activity-item">
            <div class="activity-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="activity-content">
                <div class="activity-title">Sale: ' . htmlspecialchars($row['item_name']) . ' to ' . htmlspecialchars($row['customer_name']) . '</div>
                <div class="activity-time">' . $time_ago . ' - ₹' . number_format($row['total_price']) . '</div>
            </div>
        </li>';
    }
}

// Get low stock items
$sql = "SELECT item_name, quantity FROM inventory WHERE quantity <= 10 ORDER BY quantity ASC LIMIT 3";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo '<li class="activity-item">
            <div class="activity-icon" style="background: rgba(239,68,68,0.1); color: var(--danger);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="activity-content">
                <div class="activity-title">Low Stock Alert: ' . htmlspecialchars($row['item_name']) . '</div>
                <div class="activity-time">Only ' . $row['quantity'] . ' items remaining</div>
            </div>
        </li>';
    }
}

$conn->close();

function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d == 0) {
        if ($diff->h == 0) {
            if ($diff->i == 0) {
                return "Just now";
            }
            return $diff->i . " minutes ago";
        }
        return $diff->h . " hours ago";
    }
    return $diff->d . " days ago";
}
?> 
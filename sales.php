<?php
session_start();

if (!isset($_SESSION['admin'])) {
    echo "<script>alert('You are not logged in, please log in');</script>";
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales - Clothing Store Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --background: #f8fafc;
            --surface: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text);
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            padding: 2rem;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .logo i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .logo h1 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text);
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1rem;
            color: var(--text-light);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--primary);
            color: white;
        }

        .nav-link i {
            font-size: 1.25rem;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 1.875rem;
            font-weight: 600;
            color: var(--text);
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text);
        }

        .btn-outline:hover {
            background: var(--background);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--surface);
            border-radius: 1rem;
            border: 1px solid var(--border);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.primary {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }

        .stat-icon.success {
            background: rgba(34, 197, 94, 0.1);
            color: var(--success);
        }

        .stat-icon.warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .stat-icon.danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .stat-info h3 {
            font-size: 0.875rem;
            color: var(--text-light);
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text);
        }

        /* Sales Table */
        .table-container {
            background: var(--surface);
            border-radius: 1rem;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .table th {
            background: var(--background);
            font-weight: 500;
            color: var(--text);
        }

        .table tr:hover {
            background: var(--background);
        }

        /* Date Filter */
        .date-filter {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            align-items: center;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-size: 0.875rem;
            color: var(--text-light);
        }

        .form-control {
            padding: 0.5rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 1rem;
            }

            .logo h1, .nav-link span {
                display: none;
            }

            .main-content {
                margin-left: 70px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .date-filter {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="logo">
                <i class="fas fa-store"></i>
                <h1>Store Manager</h1>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="Inventory.php" class="nav-link">
                        <i class="fas fa-box"></i>
                        <span>Inventory</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="stock.php" class="nav-link">
                        <i class="fas fa-truck"></i>
                        <span>Stock Purchased</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="billing.php" class="nav-link">
                        <i class="fas fa-receipt"></i>
                        <span>Billing</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="sales.php" class="nav-link active">
                        <i class="fas fa-chart-line"></i>
                        <span>Sales</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="Customers.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Customers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header">
                <h1>Sales Overview</h1>
                <div class="header-actions">
                    <button class="btn btn-outline">
                        <i class="fas fa-download"></i>
                        Export Report
                    </button>
                </div>
            </header>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Sales</h3>
                        <p>₹0.00</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Orders</h3>
                        <p>0</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Items Sold</h3>
                        <p>0</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Low Stock Items</h3>
                        <p>0</p>
                    </div>
                </div>
            </div>

            <!-- Date Filter -->
            <div class="date-filter">
                <div class="form-group">
                    <label class="form-label" for="start_date">Start Date</label>
                    <input type="date" id="start_date" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label" for="end_date">End Date</label>
                    <input type="date" id="end_date" class="form-control">
                </div>
                <button class="btn btn-primary">
                    <i class="fas fa-filter"></i>
                    Apply Filter
                </button>
            </div>

            <!-- Sales Table -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $conn = new mysqli("localhost", "root", "", "admins");
                        if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                        }

                        // First, let's get the total sales, orders, and items sold for the stats
                        $stats_query = "SELECT 
                            COUNT(DISTINCT bill_number) as total_orders,
                            SUM(total_price) as total_sales,
                            SUM(quantity_sold) as total_items
                            FROM sales";
                        $stats_result = $conn->query($stats_query);
                        $stats = $stats_result->fetch_assoc();

                        // Update the stats in the HTML
                        echo "<script>
                            document.querySelector('.stat-card:nth-child(1) .stat-info p').textContent = '₹" . number_format($stats['total_sales'] ?? 0, 2) . "';
                            document.querySelector('.stat-card:nth-child(2) .stat-info p').textContent = '" . ($stats['total_orders'] ?? 0) . "';
                            document.querySelector('.stat-card:nth-child(3) .stat-info p').textContent = '" . ($stats['total_items'] ?? 0) . "';
                        </script>";

                        // Get low stock items count
                        $low_stock_query = "SELECT COUNT(*) as low_stock FROM inventory WHERE quantity <= 5";
                        $low_stock_result = $conn->query($low_stock_query);
                        $low_stock = $low_stock_result->fetch_assoc();
                        echo "<script>
                            document.querySelector('.stat-card:nth-child(4) .stat-info p').textContent = '" . ($low_stock['low_stock'] ?? 0) . "';
                        </script>";

                        // Get sales data grouped by bill number
                        $sql = "SELECT 
                            bill_number,
                            created_at,
                            customer_name,
                            GROUP_CONCAT(item_name SEPARATOR ', ') as items,
                            SUM(total_price) as total_amount
                            FROM sales 
                            GROUP BY bill_number, created_at, customer_name
                            ORDER BY created_at DESC";
                        
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . date('d M Y', strtotime($row['created_at'])) . "</td>";
                                echo "<td>" . htmlspecialchars($row['bill_number']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['items']) . "</td>";
                                echo "<td>₹" . number_format($row['total_amount'], 2) . "</td>";
                                echo "<td><span class='status completed'>Completed</span></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align: center;'>No sales data found</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Add date filtering functionality
        document.querySelector('.btn-primary').addEventListener('click', function() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (startDate && endDate) {
                window.location.href = `sales.php?start_date=${startDate}&end_date=${endDate}`;
            } else {
                alert('Please select both start and end dates');
            }
        });
    </script>
</body>
</html>


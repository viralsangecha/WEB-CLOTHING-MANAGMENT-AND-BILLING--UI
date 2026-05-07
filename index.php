<?php
	session_start();
	
	if (!isset($_SESSION['admin'])) 
	{
		echo "<script>alert('your are not logged in ,please log in');</script>";
		header("Location: login.html");
		exit();
	}

	// Add this code to handle note submission
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['notecontent'])) {
		$conn = new mysqli("localhost", "root", "", "admins");
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}
		
		$note = $conn->real_escape_string($_POST['notecontent']);
		$username = $_SESSION['admin'];
		
		$sql = "UPDATE admin SET Note = ? WHERE Username = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ss", $note, $username);
		
		if ($stmt->execute()) {
			echo "<script>alert('Notes saved successfully!');</script>";
		} else {
			echo "<script>alert('Error saving notes: " . $conn->error . "');</script>";
		}
		
		$stmt->close();
		$conn->close();
	}

	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	$conn = new mysqli("localhost", "root", "", "admins");
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	// Fetch data for charts
	$salesData = [];
	$categoryData = [];
	$metrics = [];
	$today_stats = [];

	// Fetch total sales
	$sql = "SELECT SUM(total_price) as total_sales FROM sales";
	$result = $conn->query($sql);
	$totalSales = $result->fetch_assoc()['total_sales'] ?? 0;

	// Fetch total orders
	$sql = "SELECT COUNT(*) as total_orders FROM sales";
	$result = $conn->query($sql);
	$totalOrders = $result->fetch_assoc()['total_orders'] ?? 0;

	// Fetch total customers
	$sql = "SELECT COUNT(DISTINCT name) as total_customers FROM customer";
	$result = $conn->query($sql);
	$totalCustomers = $result->fetch_assoc()['total_customers'] ?? 0;

	// Fetch today's sales
	$today = date('Y-m-d');
	$sql = "SELECT SUM(total_price) as todays_sales FROM sales WHERE DATE(created_at) = '$today'";
	$result = $conn->query($sql);
	$todaysSales = $result->fetch_assoc()['todays_sales'] ?? 0;

	$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clothing Store Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--surface);
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid var(--border);
			transition: all 0.3s ease;
		}

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-title {
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-value {
            font-size: 1.875rem;
            font-weight: 600;
            color: var(--text);
        }

        /* Recent Activity */
        .recent-activity {
            background: var(--surface);
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid var(--border);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text);
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--background);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: var(--primary);
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 500;
            color: var(--text);
            margin-bottom: 0.25rem;
        }

        .activity-time {
            font-size: 0.875rem;
            color: var(--text-light);
        }

        /* Modal Styles */
        .modal {
            display: none;
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            background: var(--surface);
            width: 90%;
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 1rem;
            position: relative;
        }

        .close {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            font-size: 1.5rem;
            color: var(--text-light);
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }

        /* Admin Tabs Styles */
        .admin-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 1rem;
        }

        .admin-tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            border-radius: 0.5rem;
            color: var(--text-light);
            transition: all 0.3s ease;
        }

        .admin-tab:hover {
            color: var(--primary);
            background: rgba(37,99,235,0.1);
        }

        .admin-tab.active {
            color: var(--primary);
            background: rgba(37,99,235,0.1);
            font-weight: 500;
        }

        .admin-form {
            display: none;
        }

        .admin-form:first-of-type {
            display: block;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .admin-table th,
        .admin-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .admin-table th {
            font-weight: 500;
            color: var(--text);
        }

        .login-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .login-link:hover {
            text-decoration: underline;
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
        }

        /* Add this to your existing CSS */
        .form-control[name="notecontent"] {
            width: 100%;
            min-height: 300px;
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            font-size: 1rem;
            line-height: 1.5;
            resize: vertical;
        }

        .form-control[name="notecontent"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }

        /* Notifications Panel */
        .notifications-panel {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 300px;
        }

        .notification {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--surface);
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 0.5rem;
            animation: slideIn 0.3s ease-out;
        }

        .notification.warning {
            border-left: 4px solid var(--warning);
        }

        .notification i {
            color: var(--warning);
            font-size: 1.25rem;
        }

        .notification span {
            flex: 1;
            font-size: 0.875rem;
            color: var(--text);
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Activity List Improvements */
        .activity-item {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
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
                    <a href="dashboard.php" class="nav-link active">
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
                    <a href="sales.php" class="nav-link">
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
                <h1>Welcome, <?php echo $_SESSION['admin']; ?></h1>
                <div class="header-actions">
                    <button class="btn btn-outline" onclick="showNotes()">
                        <i class="fas fa-sticky-note"></i>
                        Notes
                    </button>
                    <input type="hidden" id="v1" value="<?php echo $_SESSION['password']; ?>">
                    <button class="btn btn-primary" onclick="verifyadmin()">
                        <i class="fas fa-cog"></i>
                        Settings
                    </button>
                </div>
            </header>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card" onclick="showModal('salesModal')">
                    <div class="stat-header">
                        <span class="stat-title">Total Sales</span>
                        <div class="stat-icon" style="background: rgba(37,99,235,0.1); color: var(--primary);">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="stat-value">
                        ₹<?php echo number_format($totalSales); ?>
                    </div>
                    <div class="growth positive">+5% from last week</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Orders</span>
                        <div class="stat-icon" style="background: rgba(34,197,94,0.1); color: var(--success);">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="stat-value">
                        <?php echo number_format($totalOrders); ?>
                    </div>
                    <div class="growth negative">-2% from last week</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Customers</span>
                        <div class="stat-icon" style="background: rgba(245,158,11,0.1); color: var(--warning);">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-value">
                        <?php echo number_format($totalCustomers); ?>
                    </div>
                    <div class="growth positive">+10% from last month</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Today's Sales</span>
                        <div class="stat-icon" style="background: rgba(239,68,68,0.1); color: var(--danger);">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                    <div class="stat-value">
                        ₹<?php echo number_format($todaysSales); ?>
                    </div>
                </div>
            </div>

            <!-- Insights -->
            <div class="insights">
                <h2>Business Insights</h2>
                <p>Consider offering discounts on popular items to boost sales.</p>
                <p>Engage with new customers through personalized emails.</p>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <h2 class="section-title">Recent Activity</h2>
                <ul class="activity-list">
                    <?php
                    $conn = new mysqli("localhost", "root", "", "admins");
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

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

                    // Helper function for time ago
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
                </ul>
            </div>

            <!-- Low Stock Notifications -->
            <div class="notifications-panel">
                <?php
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
            </div>
        </main>
    </div>

    <!-- Admin Settings Modal -->
	<div id="admin" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAdminSettings()">&times;</span>
            <h2>Admin Settings</h2>
            
            <div class="admin-tabs">
                <div class="admin-tab active" data-form="updateForm">Update User</div>
                <div class="admin-tab" data-form="addUserForm">Add User</div>
                <div class="admin-tab" data-form="adminList">Admin List</div>
            </div>
			
            <div id="updateForm" class="admin-form">
                <form action="updateuser.php" method="post">
			<input type="hidden" name="id" value="<?php echo $_SESSION['id'];?>">
                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo $_SESSION['admin'];?>" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" name="password" value="<?php echo $_SESSION['password'];?>" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="q1">Security Question</label>
                        <select id="q1" name="qu" class="form-control" required>
                            <option value="First car number?" <?php echo $_SESSION['que'] == 'First car number?' ? 'selected' : ''; ?>>First car number?</option>
                            <option value="Best Friend Name" <?php echo $_SESSION['que'] == 'Best Friend Name' ? 'selected' : ''; ?>>Best Friend Name</option>
                            <option value="Most Likely School Teacher name" <?php echo $_SESSION['que'] == 'Most Likely School Teacher name' ? 'selected' : ''; ?>>Most Likely School Teacher name</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="ans">Answer</label>
                        <input type="text" id="ans" name="ans" value="<?php echo $_SESSION['ans']; ?>" class="form-control" required>
                    </div>
                    
                    <button type="submit" name="updateUser" class="btn btn-primary">Update</button>
        </form>
            </div>

            <div id="addUserForm" class="admin-form" style="display: none;">
                <form action="register.php" method="post">
                    <div class="form-group">
                        <label class="form-label" for="newUsername">New Username</label>
                        <input type="text" id="newUsername" name="newusername" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="newPassword">New Password</label>
                        <input type="password" id="newPassword" name="newpassword" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="newQ1">Security Question</label>
                        <select id="newQ1" name="qu" class="form-control" required>
                            <option value="First car number?">First car number?</option>
                            <option value="Best Friend Name">Best Friend Name</option>
                            <option value="Most Likely School Teacher name">Most Likely School Teacher name</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="newAns">Answer</label>
                        <input type="text" id="newAns" name="ans" class="form-control" required>
                    </div>

                    <button type="submit" name="addUser" class="btn btn-primary">Add User</button>
                </form>
            </div>

            <div id="adminList" class="admin-form" style="display: none;">
                <h3>Admins/User List</h3>
                <p style="color: var(--success)">🟢 - Current Login User</p>
                <?php 
                    $conn = new mysqli("localhost","root","","admins");
                    $sql = "select * from admin";
                    $result = $conn->query($sql);
                    
                    echo "<table class='admin-table'>
                        <tr>
                            <th>Username</th>
                            <th>Status</th>
					</tr>";
                    while($row = $result->fetch_assoc()): 
                        echo "<tr>";
                        echo "<td>" . $row['Username'] . "</td>";
                        if($_SESSION['admin'] == $row['Username']) {
                            echo "<td>🟢</td>";
                        } else {
                            echo "<td><a href='logout.php' class='login-link'>Login</a></td>";
                        }
                        echo "</tr>";
					 endwhile;
                    echo "</table>";
                ?>
		</div>
		</div>
	</div>

    <!-- Notes Modal -->
    <div id="noteshow" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeNotes()">&times;</span>
            <h2>Admin Notes</h2>
            <form action="dashboard.php" method="post">
                <div class="form-group">
                    <label class="form-label" for="notecontent">Your Notes</label>
                    <textarea name="notecontent" id="notecontent" class="form-control" style="height: 300px; resize: vertical;"><?php 
                        $conn = new mysqli("localhost", "root", "", "admins");
                        if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                        }
                        $username = $_SESSION['admin'];
                        $sql = "SELECT Note FROM admin WHERE Username = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $username);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($row = $result->fetch_assoc()) {
                            echo htmlspecialchars($row['Note']);
                        }
                        $stmt->close();
                        $conn->close();
                    ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Save Notes
                </button>
            </form>
        </div>
    </div>

    <!-- Sales Modal -->
    <div id="salesModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('salesModal')">&times;</span>
            <h2>Total Sales Analysis</h2>
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <script>
        // Admin verification
        function verifyadmin() {
            const p1 = document.getElementById('v1').value;
            var ans = prompt("Enter Your Password:");
            if(ans != null) {
                if(ans == p1) {
                    showAdminSettings();
                } else {
                    alert('Wrong password!');
					verifyadmin();
				}
			}
		}

        // Modal functions
        function showAdminSettings() {
            document.getElementById('admin').style.display = 'block';
            document.body.style.overflow = 'hidden';
            // Reset to first tab when opening modal
            document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.admin-form').forEach(form => form.style.display = 'none');
            document.querySelector('.admin-tab').classList.add('active');
            document.getElementById('updateForm').style.display = 'block';
        }

        function closeAdminSettings() {
            document.getElementById('admin').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function showNotes() {
            document.getElementById('noteshow').style.display = 'block';
            document.body.style.overflow = 'hidden';
            document.getElementById('notecontent').focus();
        }

        function closeNotes() {
		document.getElementById('noteshow').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.admin-tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    // Remove active class from all tabs
                    document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
                    // Add active class to clicked tab
                    tab.classList.add('active');
                    
                    // Hide all forms
                    document.querySelectorAll('.admin-form').forEach(form => {
                        form.style.display = 'none';
                    });
                    
                    // Show the selected form
                    const formId = tab.getAttribute('data-form');
                    document.getElementById(formId).style.display = 'block';
                });
			});
		});

        // Close modals when clicking outside
        window.onclick = function(event) {
            const adminModal = document.getElementById('admin');
            const notesModal = document.getElementById('noteshow');
            const salesModal = document.getElementById('salesModal');
            
            if (event.target == adminModal) {
                closeAdminSettings();
            }
            if (event.target == notesModal) {
                closeNotes();
            }
            if (event.target == salesModal) {
                closeModal('salesModal');
            }
        }

        // Add this to your existing JavaScript
        function saveNotes() {
            const noteContent = document.getElementById('notecontent').value;
            const form = document.querySelector('#noteshow form');
            
            form.addEventListener('submit', function(e) {
                const saveButton = this.querySelector('button[type="submit"]');
                saveButton.disabled = true;
                saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            });
        }

        // Add this to your existing JavaScript
        function updateRecentActivity() {
            fetch('get_recent_activity.php')
                .then(response => response.text())
                .then(html => {
                    document.querySelector('.activity-list').innerHTML = html;
                });
        }

        function updateLowStockNotifications() {
            fetch('get_low_stock.php')
                .then(response => response.text())
                .then(html => {
                    document.querySelector('.notifications-panel').innerHTML = html;
                });
        }

        // Update every 30 seconds
        setInterval(() => {
            updateRecentActivity();
            updateLowStockNotifications();
        }, 30000);

        // Initial load
        document.addEventListener('DOMContentLoaded', function() {
            updateRecentActivity();
            updateLowStockNotifications();
        });

        // Show modal
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Initialize Chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
                    datasets: [{
                        label: 'Sales',
                        data: [12000, 15000, 18000, 20000, 22000, 25000, 27000],
                        borderColor: 'rgb(37,99,235)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>

<?php
	session_start();

	if (!isset($_SESSION['admin']))
	{
		echo "<script>alert('You are not logged in, please log in');</script>";
		header("Location: login.html");
		exit();
	}

	
	$conn = new mysqli("localhost","root","","admins");

	if ($conn->connect_error)
	{
		die("Connection failed: " . $conn->connect_error);
	}

	// Handle search functionality
	$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
	$where_clause = $search ? "WHERE name LIKE '%$search%' OR number LIKE '%$search%'" : "";

	// Get customer statistics
	$stats_query = "SELECT 
		COUNT(*) as total_customers,
		SUM(CASE WHEN EXISTS (
			SELECT 1 FROM sales s 
			WHERE s.customer_name = customer.name 
			AND s.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
		) THEN 1 ELSE 0 END) as active_customers,
		SUM(CASE WHEN EXISTS (
			SELECT 1 FROM sales s 
			WHERE s.customer_name = customer.name 
			AND s.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
			AND s.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
		) THEN 1 ELSE 0 END) as new_customers
		FROM customer";

	$stats_result = $conn->query($stats_query);
	$stats = $stats_result->fetch_assoc();

	// Get top customers
	$top_customers_query = "SELECT 
		c.name,
		COUNT(DISTINCT s.bill_number) as total_orders,
		SUM(s.total_price) as total_spent
		FROM customer c
		LEFT JOIN sales s ON c.name = s.customer_name
		GROUP BY c.name
		ORDER BY total_spent DESC
		LIMIT 5";

	$top_customers = $conn->query($top_customers_query);

	// Get customers with pagination
	$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$items_per_page = 10;
	$offset = ($page - 1) * $items_per_page;

	$count_query = "SELECT COUNT(*) as total FROM customer $where_clause";
	$count_result = $conn->query($count_query);
	$total_items = $count_result->fetch_assoc()['total'];
	$total_pages = ceil($total_items / $items_per_page);

	$sql = "SELECT * FROM customer $where_clause ORDER BY ID DESC LIMIT $offset, $items_per_page";
	$result = $conn->query($sql);

	// Handle customer addition
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name']) && isset($_POST['number'])) {
		$name = $conn->real_escape_string($_POST['name']);
		$number = $conn->real_escape_string($_POST['number']);
		
		// Check if customer already exists
		$check_sql = "SELECT * FROM customer WHERE name = '$name' OR number = '$number'";
		$check_result = $conn->query($check_sql);
		
		if ($check_result->num_rows > 0) {
			echo "<script>alert('Customer with this name or number already exists!');</script>";
		} else {
			$sql = "INSERT INTO customer (name, number) VALUES ('$name', '$number')";
			if ($conn->query($sql)) {
				echo "<script>alert('Customer added successfully!'); window.location.href='Customers.php';</script>";
			} else {
				echo "<script>alert('Error adding customer: " . $conn->error . "');</script>";
			}
		}
	}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Customers - Clothing Store Management</title>
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

		/* Search Bar */
		.search-bar {
			margin: 2rem 0;
			background: var(--surface);
			padding: 1rem;
			border-radius: 0.5rem;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
		}

		.search-form {
			display: flex;
			gap: 1rem;
			align-items: center;
		}

		.search-input {
			flex: 1;
			padding: 0.75rem;
			border: 1px solid var(--border);
			border-radius: 0.5rem;
			font-size: 1rem;
		}

		.search-input:focus {
			outline: none;
			border-color: var(--primary);
			box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
		}

		.pagination {
			display: flex;
			justify-content: center;
			gap: 0.5rem;
			margin: 2rem 0;
		}

		.pagination .btn {
			min-width: 40px;
			height: 40px;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 0 1rem;
		}

		.table td {
			vertical-align: middle;
		}

		.action-buttons {
			display: flex;
			gap: 0.5rem;
		}

		.btn-icon {
			width: 36px;
			height: 36px;
			border-radius: 50%;
			border: none;
			display: flex;
			align-items: center;
			justify-content: center;
			cursor: pointer;
			transition: all 0.3s ease;
		}

		.btn-icon.edit {
			background: var(--primary);
			color: white;
		}

		.btn-icon.edit:hover {
			background: var(--primary-dark);
		}

		.btn-icon.delete {
			background: var(--danger);
			color: white;
		}

		.btn-icon.delete:hover {
			background: #dc2626;
		}

		.modal {
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.5);
			z-index: 1000;
		}

		.modal-content {
			background: var(--surface);
			padding: 2rem;
			border-radius: 1rem;
			width: 90%;
			max-width: 500px;
			position: relative;
			margin: 2rem auto;
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

			.search-form {
				flex-direction: column;
			}
			
			.search-input {
				width: 100%;
			}
			
			.pagination {
				flex-wrap: wrap;
			}
		}

		/* Enhanced Table Styles */
		.table-container {
			background: var(--surface);
			border-radius: 1rem;
			border: 1px solid var(--border);
			overflow: hidden;
			margin-top: 2rem;
			box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
		}

		.table {
			width: 100%;
			border-collapse: collapse;
		}

		.table th {
			background: var(--primary);
			color: white;
			font-weight: 500;
			text-transform: uppercase;
			font-size: 0.875rem;
			letter-spacing: 0.05em;
		}

		.table th,
		.table td {
			padding: 1rem;
			text-align: left;
			border-bottom: 1px solid var(--border);
		}

		.table tbody tr:hover {
			background: rgba(37, 99, 235, 0.05);
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
					<a href="sales.php" class="nav-link">
						<i class="fas fa-chart-line"></i>
						<span>Sales</span>
					</a>
				</li>
				<li class="nav-item">
					<a href="Customers.php" class="nav-link active">
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
				<h1>Customer Management</h1>
				<div class="header-actions">
					<button class="btn btn-primary" onclick="showAddCustomerModal()">
						<i class="fas fa-plus"></i>
						Add New Customer
					</button>
				</div>
			</header>

			<!-- Stats Cards -->
			<div class="stats-grid">
				<div class="stat-card">
					<div class="stat-icon primary">
						<i class="fas fa-users"></i>
					</div>
					<div class="stat-info">
						<h3>Total Customers</h3>
						<p>0</p>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon success">
						<i class="fas fa-shopping-bag"></i>
					</div>
					<div class="stat-info">
						<h3>Active Customers</h3>
						<p>0</p>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon warning">
						<i class="fas fa-clock"></i>
					</div>
					<div class="stat-info">
						<h3>New This Month</h3>
						<p>0</p>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon danger">
						<i class="fas fa-user-slash"></i>
					</div>
					<div class="stat-info">
						<h3>Inactive Customers</h3>
						<p>0</p>
					</div>
				</div>
			</div>

			<!-- Search Bar -->
			<div class="search-bar">
				<form method="GET" action="Customers.php" class="search-form">
					<input type="text" name="search" class="search-input" placeholder="Search customers..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
					<button type="submit" class="btn btn-outline">
						<i class="fas fa-search"></i>
						Search
					</button>
					<?php if(isset($_GET['search'])): ?>
						<a href="Customers.php" class="btn btn-outline">
							<i class="fas fa-times"></i>
							Clear
						</a>
					<?php endif; ?>
				</form>
			</div>

			<!-- Pagination -->
			<?php if ($total_pages > 1): ?>
			<div class="pagination">
				<?php if ($page > 1): ?>
					<a href="?page=<?php echo ($page-1); ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="btn btn-outline">&laquo; Previous</a>
				<?php endif; ?>
				
				<?php for ($i = 1; $i <= $total_pages; $i++): ?>
					<a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
					   class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?>">
						<?php echo $i; ?>
					</a>
				<?php endfor; ?>
				
				<?php if ($page < $total_pages): ?>
					<a href="?page=<?php echo ($page+1); ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="btn btn-outline">Next &raquo;</a>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<!-- Customers Table -->
			<div class="table-container">
				<table class="table">
					<thead>
						<tr>
							<th>Customer ID</th>
							<th>Name</th>
							<th>Contact Number</th>
							<th>Total Orders</th>
							<th>Total Spent</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php
						if ($result->num_rows > 0) {
							while($row = $result->fetch_assoc()) {
								echo "<tr>";
								echo "<td>#" . ($row['ID'] ?? $row['id'] ?? 'N/A') . "</td>";
								echo "<td>" . htmlspecialchars($row['name']) . "</td>";
								echo "<td>" . htmlspecialchars($row['number']) . "</td>";
								
								// Get total orders and amount for this customer
								$customer_name = $row['name'];
								$orders_query = "SELECT 
									COUNT(DISTINCT bill_number) as total_orders,
									SUM(total_price) as total_spent
									FROM sales 
									WHERE customer_name = '" . $conn->real_escape_string($customer_name) . "'";
								
								$orders_result = $conn->query($orders_query);
								$orders_data = $orders_result->fetch_assoc();
								
								echo "<td>" . ($orders_data['total_orders'] ?? '0') . "</td>";
								echo "<td>₹" . number_format($orders_data['total_spent'] ?? 0, 2) . "</td>";
								
								echo "<td class='action-buttons'>
									<button class='btn-icon edit' onclick='editCustomer(" . ($row['ID'] ?? $row['id'] ?? '0') . ")'><i class='fas fa-edit'></i></button>
									<button class='btn-icon delete' onclick='deleteCustomer(" . ($row['ID'] ?? $row['id'] ?? '0') . ")'><i class='fas fa-trash'></i></button>
								</td>";
								echo "</tr>";
							}
						} else {
							echo "<tr><td colspan='7' style='text-align: center;'>No customers found</td></tr>";
						}

						// Update customer stats
						$stats_query = "SELECT 
							COUNT(*) as total_customers,
							SUM(CASE WHEN EXISTS (
								SELECT 1 FROM sales s 
								WHERE s.customer_name = customer.name 
								AND s.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
							) THEN 1 ELSE 0 END) as active_customers,
							SUM(CASE WHEN EXISTS (
								SELECT 1 FROM sales s 
								WHERE s.customer_name = customer.name 
								AND s.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
							) THEN 0 ELSE 1 END) as inactive_customers,
							SUM(CASE WHEN EXISTS (
								SELECT 1 FROM sales s 
								WHERE s.customer_name = customer.name 
								AND s.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
								AND s.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
							) THEN 1 ELSE 0 END) as new_customers
							FROM customer";
						
						$stats_result = $conn->query($stats_query);
						$stats = $stats_result->fetch_assoc();

						echo "<script>
							document.querySelector('.stat-card:nth-child(1) .stat-info p').textContent = '" . ($stats['total_customers'] ?? '0') . "';
							document.querySelector('.stat-card:nth-child(2) .stat-info p').textContent = '" . ($stats['active_customers'] ?? '0') . "';
							document.querySelector('.stat-card:nth-child(3) .stat-info p').textContent = '" . ($stats['new_customers'] ?? '0') . "';
							document.querySelector('.stat-card:nth-child(4) .stat-info p').textContent = '" . ($stats['inactive_customers'] ?? '0') . "';
						</script>";

						$conn->close();
						?>
					</tbody>
				</table>
			</div>
		</main>
	</div>

	<!-- Add Customer Modal -->
	<div id="addCustomerModal" class="modal">
		<div class="modal-content">
			<span class="close" onclick="closeAddCustomerModal()">&times;</span>
			<h2>Add New Customer</h2>
			<form action="Customers.php" method="post">
				<div class="form-group">
					<label class="form-label" for="name">Customer Name</label>
					<input type="text" id="name" name="name" class="form-control" required>
				</div>

				<div class="form-group">
					<label class="form-label" for="number">Contact Number</label>
					<input type="tel" id="number" name="number" class="form-control" pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number" required>
				</div>

				<button type="submit" class="btn btn-primary">Add Customer</button>
			</form>
		</div>
	</div>

	<script>
		// Modal functions
		function showAddCustomerModal() {
			document.getElementById('addCustomerModal').style.display = 'block';
			document.body.style.overflow = 'hidden';
		}

		function closeAddCustomerModal() {
			document.getElementById('addCustomerModal').style.display = 'none';
			document.body.style.overflow = 'auto';
		}

		function editCustomer(customerId) {
			// Implement edit functionality
			alert('Edit customer ' + customerId);
		}

		function deleteCustomer(customerId) {
			if (confirm('Are you sure you want to delete this customer?')) {
				window.location.href = 'delete_customer.php?id=' + customerId;
			}
		}

		// Close modal when clicking outside
		window.onclick = function(event) {
			const modal = document.getElementById('addCustomerModal');
			if (event.target == modal) {
				closeAddCustomerModal();
			}
		}
	</script>
</body>
</html>

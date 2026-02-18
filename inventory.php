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

	if ($_SERVER['REQUEST_METHOD'] == 'POST') 
	{
		$item_name = $_POST['item_name'];
		$quantity = $_POST['quantity'];
		$price = $_POST['price'];

		// Check if item already exists
		$check_sql = "SELECT * FROM inventory WHERE item_name = '$item_name'";
		$check_result = $conn->query($check_sql);

		if ($check_result->num_rows > 0) {
			if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
				echo "duplicate_error";
				exit;
			}
		} else {
			$sql = "INSERT INTO inventory (item_name, quantity, price) VALUES ('$item_name', '$quantity', '$price')";
			
			if ($conn->query($sql)) {
				if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
					echo "success";
					exit;
				}
			} else {
				if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
					echo "db_error:" . $conn->error;
					exit;
				}
			}
		}
	}

	$sql = "SELECT * FROM inventory";
	$result = $conn->query($sql);
	
	$sql1 = "SELECT  quantity FROM inventory";
	$r = $conn->query($sql1);

	$row = $r->fetch_assoc();
	$quantity=$row['quantity'];
	if($quantity < 5)
	{
		echo "<script> alert('stocke is less')</script>";
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Inventory - Clothing Store Management</title>
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

		/* Table Styles */
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

		.action-buttons {
			display: flex;
			gap: 0.5rem;
		}

		.btn-icon {
			padding: 0.5rem;
			border-radius: 0.375rem;
			color: var(--text-light);
			transition: all 0.3s ease;
		}

		.btn-icon:hover {
			background: var(--background);
			color: var(--primary);
		}

		.btn-icon.edit {
			color: var(--warning);
		}

		.btn-icon.delete {
			color: var(--danger);
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

			.table-container {
				overflow-x: auto;
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
					<a href="Inventory.php" class="nav-link active">
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
				<h1>Inventory Management</h1>
				<div class="header-actions">
					<button class="btn btn-primary" onclick="showAddItemModal()">
						<i class="fas fa-plus"></i>
						Add New Item
					</button>
				</div>
			</header>

			<!-- Inventory Table -->
			<div class="table-container">
				<table class="table">
					<thead>
						<tr>
							<th>Item ID</th>
							<th>Name</th>
							<th>Price</th>
							<th>Stock</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php
						if ($result->num_rows > 0) {
							while($row = $result->fetch_assoc()) {
								echo "<tr>";
								echo "<td>" . htmlspecialchars($row['ID']) . "</td>";
								echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
								echo "<td>₹" . number_format($row['price'], 2) . "</td>";
								echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
								echo "<td class='action-buttons'>
									<button class='btn-icon edit' onclick='editItem(" . $row['ID'] . ")'><i class='fas fa-edit'></i></button>
									<button class='btn-icon delete' onclick='deleteItem(" . $row['ID'] . ")'><i class='fas fa-trash'></i></button>
								</td>";
								echo "</tr>";
							}
						} else {
							echo "<tr><td colspan='5' style='text-align: center;'>No items found</td></tr>";
						}
						$conn->close();
						?>
					</tbody>
				</table>
			</div>
		</main>
	</div>

	<!-- Add Item Modal -->
	<div id="addItemModal" class="modal">
		<div class="modal-content">
			<span class="close" onclick="closeAddItemModal()">&times;</span>
			<h2>Add New Item</h2>
			<form action="add_item.php" method="post" onsubmit="return validateAddItemForm()">
				<div class="form-group">
					<label class="form-label" for="item_name">Item Name</label>
					<input type="text" id="item_name" name="item_name" class="form-control" required>
				</div>

				<div class="form-group">
					<label class="form-label" for="price">Price</label>
					<input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
				</div>

				<div class="form-group">
					<label class="form-label" for="quantity">Quantity</label>
					<input type="number" id="quantity" name="quantity" class="form-control" min="0" required>
				</div>

				<button type="submit" class="btn btn-primary">Add Item</button>
			</form>
		</div>
	</div>

	<script>
		// Modal functions
		function showAddItemModal() {
			document.getElementById('addItemModal').style.display = 'block';
			document.body.style.overflow = 'hidden';
		}

		function closeAddItemModal() {
			document.getElementById('addItemModal').style.display = 'none';
			document.body.style.overflow = 'auto';
		}

		function editItem(itemId) {
			window.location.href = 'update.php?id=' + itemId;
		}

		function deleteItem(itemId) {
			if (confirm('Are you sure you want to delete this item?')) {
				window.location.href = 'delete.php?id=' + itemId;
			}
		}

		// Close modal when clicking outside
		window.onclick = function(event) {
			const modal = document.getElementById('addItemModal');
			if (event.target == modal) {
				closeAddItemModal();
			}
		}

		function validateAddItemForm() {
			const itemName = document.getElementById('item_name').value.trim();
			const quantity = document.getElementById('quantity').value;
			const price = document.getElementById('price').value;

			if (itemName === '') {
				alert('Item name cannot be empty');
				return false;
			}

			if (quantity < 0) {
				alert('Quantity cannot be negative');
				return false;
			}

			if (price < 0) {
				alert('Price cannot be negative');
				return false;
			}

			return true;
		}
	</script>
</body>
</html>

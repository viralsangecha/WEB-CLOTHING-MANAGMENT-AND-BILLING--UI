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
		$price_per_item = $_POST['price_per_item'];
		$total_price=$_POST['total_price'];

		$sql = "INSERT INTO stock_ordered(Item_name, quantity, price_per_item, total_price) VALUES (?, ?, ?, ?)";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("sidd", $item_name, $quantity, $price_per_item, $total_price);
		$stmt->execute();
		$stmt->close();
	}

	$sql = "SELECT * FROM stock_ordered";
	$result = $conn->query($sql);
	
	$sql1 = "SELECT  quantity FROM stock_ordered";
	$r = $conn->query($sql1);

	$row = $r->fetch_assoc();
	$quantity=$row['quantity'];
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Stock Purchased - Clothing Store Management</title>
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
			min-height: 100vh;
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
			margin-bottom: 2rem;
		}

		.header h1 {
			font-size: 1.875rem;
			font-weight: 600;
			color: var(--text);
			margin-bottom: 0.5rem;
		}

		.card {
			background: var(--surface);
			border-radius: 1rem;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
			padding: 1.5rem;
			margin-bottom: 2rem;
		}

		.card-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 1.5rem;
		}

		.card-title {
			font-size: 1.25rem;
			font-weight: 600;
			color: var(--text);
		}

		/* Form Styles */
		.form-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
			gap: 1rem;
			margin-bottom: 1.5rem;
		}

		.form-group {
			margin-bottom: 1rem;
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

		.btn {
			padding: 0.75rem 1.5rem;
			border-radius: 0.5rem;
			border: none;
			font-weight: 500;
			cursor: pointer;
			transition: all 0.3s ease;
			display: inline-flex;
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

		/* Table Styles */
		.table-container {
			overflow-x: auto;
		}

		.table {
			width: 100%;
			border-collapse: collapse;
			white-space: nowrap;
		}

		.table th,
		.table td {
			padding: 1rem;
			text-align: left;
			border-bottom: 1px solid var(--border);
		}

		.table th {
			background: var(--background);
			font-weight: 600;
			color: var(--text);
		}

		.table tr:hover {
			background: var(--background);
		}

		.table td {
			color: var(--text-light);
		}

		/* Responsive Design */
		@media (max-width: 768px) {
			.sidebar {
				width: 70px;
				padding: 1rem;
			}

			.logo h1,
			.nav-link span {
				display: none;
			}

			.main-content {
				margin-left: 70px;
			}

			.form-grid {
				grid-template-columns: 1fr;
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
					<a href="stock.php" class="nav-link active">
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
			<div class="header">
				<h1>Stock Purchase Management</h1>
			</div>

			<!-- Add New Stock Form -->
			<div class="card">
				<div class="card-header">
					<h2 class="card-title">Add New Stock Purchase</h2>
				</div>
				<form action="stock.php" method="POST" onsubmit="return validateForm()">
					<div class="form-grid">
						<div class="form-group">
							<label class="form-label" for="item_name">Item Name</label>
							<input type="text" id="item_name" name="item_name" class="form-control" pattern="[A-Za-z ]+" required>
						</div>
						
						<div class="form-group">
							<label class="form-label" for="quantity">Quantity</label>
							<input type="number" id="quantity" name="quantity" class="form-control" min="1" required onchange="calculateTotal()">
						</div>
						
						<div class="form-group">
							<label class="form-label" for="price_per_item">Price per Item</label>
							<input type="number" id="price_per_item" name="price_per_item" class="form-control" min="1" required onchange="calculateTotal()">
						</div>
						
						<div class="form-group">
							<label class="form-label" for="total_price">Total Price</label>
							<input type="number" id="total_price" name="total_price" class="form-control" readonly required>
						</div>
					</div>
					
					<button type="submit" class="btn btn-primary">
						<i class="fas fa-plus"></i>
						Add Stock
					</button>
				</form>
			</div>

			<!-- Stock List -->
			<div class="card">
				<div class="card-header">
					<h2 class="card-title">Stock Purchase History</h2>
				</div>
				<div class="table-container">
					<table class="table">
						<thead>
							<tr>
								<th>ID</th>
								<th>Item Name</th>
								<th>Quantity</th>
								<th>Price per Item</th>
								<th>Total Price</th>
								<th>Date</th>
							</tr>
						</thead>
						<tbody>
							<?php 
							$query = mysqli_query($conn, "SELECT * FROM stock_ordered ORDER BY Date DESC");
							if(mysqli_num_rows($query) > 0) {
								while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
									echo "<tr>";
									echo "<td>" . ($row['ID'] ?? $row['id'] ?? '') . "</td>";
									echo "<td>" . htmlspecialchars($row['Item_name']) . "</td>";
									echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
									echo "<td>₹" . htmlspecialchars($row['price_per_item']) . "</td>";
									echo "<td>₹" . htmlspecialchars($row['total_price']) . "</td>";
									echo "<td>" . htmlspecialchars($row['Date']) . "</td>";
									echo "</tr>";
								}
							} else {
								echo "<tr><td colspan='6' style='text-align: center;'>No records found</td></tr>";
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</main>
	</div>

	<script>
	function calculateTotal() {
		const quantity = document.getElementById('quantity').value;
		const pricePerItem = document.getElementById('price_per_item').value;
		
		if (quantity && pricePerItem) {
			const total = quantity * pricePerItem;
			document.getElementById('total_price').value = total;
		}
	}

	function validateForm() {
		const quantity = document.getElementById('quantity').value;
		const pricePerItem = document.getElementById('price_per_item').value;
		const total = document.getElementById('total_price').value;
		
		if (!quantity || !pricePerItem || !total) {
			alert('Please fill all required fields');
			return false;
		}
		
		if (quantity < 1) {
			alert('Quantity must be at least 1');
			return false;
		}
		
		if (pricePerItem < 1) {
			alert('Price per item must be at least 1');
			return false;
		}
		
		return true;
	}
	</script>
</body>
</html>

<?php
$conn->close();
?>

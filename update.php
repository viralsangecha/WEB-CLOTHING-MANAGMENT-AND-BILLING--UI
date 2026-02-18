<?php
session_start();
	
	if (!isset($_SESSION['admin'])) 
	{
		
		echo "<script>alert('You are not logged in, please log in');</script>";
		header("Location: login.html");
		exit();
		
	}

	if (!isset($_GET['id'])) {
		echo "<script>alert('No item ID provided');</script>";
		header("Location: inventory.php");
		exit();
	}

	$id = $_GET['id'];
	$conn = new mysqli("localhost", "root", "", "admins");

	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("SELECT * FROM inventory WHERE ID = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($row = $result->fetch_assoc()) {
		?>
		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Update Item</title>
			<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
			<style>
				:root {
					--primary: #2563eb;
					--primary-dark: #1d4ed8;
					--background: #f8fafc;
					--surface: #ffffff;
					--text: #1e293b;
					--border: #e2e8f0;
				}

				body {
					font-family: 'Inter', sans-serif;
					margin: 0;
					padding: 2rem;
					background-color: var(--background);
					color: var(--text);
				}

				.container {
					max-width: 600px;
					margin: 0 auto;
					background: var(--surface);
					padding: 2rem;
					border-radius: 1rem;
					box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
				}

				h1 {
					margin-bottom: 2rem;
					padding-bottom: 1rem;
					border-bottom: 1px solid var(--border);
				}

				.form-group {
					margin-bottom: 1.5rem;
				}

				label {
					display: block;
					margin-bottom: 0.5rem;
					font-weight: 500;
				}

				input {
					width: 100%;
					padding: 0.75rem;
					border: 1px solid var(--border);
					border-radius: 0.5rem;
					font-size: 1rem;
				}

				input:focus {
					outline: none;
					border-color: var(--primary);
					box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
				}

				.update-btn {
					background: var(--primary);
					color: white;
					padding: 0.75rem 1.5rem;
					border: none;
					border-radius: 0.5rem;
					font-size: 1rem;
					font-weight: 500;
					cursor: pointer;
					transition: background-color 0.3s ease;
				}

				.update-btn:hover {
					background: var(--primary-dark);
				}

				.cancel-btn {
					background: transparent;
					color: var(--text);
					padding: 0.75rem 1.5rem;
					border: 1px solid var(--border);
					border-radius: 0.5rem;
					font-size: 1rem;
					font-weight: 500;
					cursor: pointer;
					margin-right: 1rem;
					transition: background-color 0.3s ease;
				}

				.cancel-btn:hover {
					background: var(--background);
				}

				.button-group {
					margin-top: 2rem;
					display: flex;
					gap: 1rem;
				}
			</style>
		</head>
		<body>
			<div class="container">
				<h1>Update Item</h1>
				<form action="update2.php" method="GET" onsubmit="return validateForm()">
					<div class="form-group">
						<label for="ID">Item ID:</label>
						<input type="text" id="ID" name="ID" value="<?php echo htmlspecialchars($row['ID']); ?>" readonly>
					</div>
					
					<div class="form-group">
						<label for="name">Item Name:</label>
						<input type="text" id="name" name="name" value="<?php echo htmlspecialchars($row['item_name']); ?>" required>
					</div>

					<div class="form-group">
						<label for="quantity">Quantity:</label>
						<input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($row['quantity']); ?>" required min="0">
					</div>

					<div class="form-group">
						<label for="price">Price:</label>
						<input type="number" id="price" name="price" value="<?php echo htmlspecialchars($row['price']); ?>" required min="0" step="0.01">
					</div>

					<div class="button-group">
						<button type="button" class="cancel-btn" onclick="window.location.href='inventory.php'">Cancel</button>
						<button type="submit" class="update-btn">Update Item</button>
					</div>
				</form>
			</div>

			<script>
			function validateForm() {
				const quantity = document.getElementById('quantity').value;
				const price = document.getElementById('price').value;

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
		<?php
	} else {
		echo "<script>alert('Item not found');</script>";
		echo "<script>window.location.href = 'inventory.php';</script>";
	}

	$stmt->close();
	$conn->close();
?>

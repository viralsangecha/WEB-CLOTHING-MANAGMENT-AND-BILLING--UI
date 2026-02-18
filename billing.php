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
		$item_id = $_POST['item_id'];
		$item_name=$_POST['item_name'];
		$customer_name=$_POST['customer_name'];
		$quantity_sold = $_POST['quantity_sold'];
		$price=$_POST['price_per_item'];
		$total_price=$_POST['total_price'];

		$sql = "select * from inventory WHERE id='$item_id'";
		$result = $conn->query($sql);
		if ($result->num_rows > 0)
		{
			$item = $result->fetch_assoc();
			$new_quantity = $item['quantity'] - $quantity_sold;
			//$total_price = $item['price'] * $quantity_sold;

			if ($new_quantity >= 0) 
			{
				$sql = "UPDATE inventory SET quantity='$new_quantity' WHERE id='$item_id'";
				$conn->query($sql);

				$sql = "INSERT INTO sales (item_id, item_name,customer_name, quantity_sold, total_price) VALUES ('$item_id', '$item_name','$customer_name', '$quantity_sold', '$total_price')";
				$conn->query($sql);
				
				
			} 
			else
			{
				echo "<script>alert('Not enough items in stock');</script>";
			}
		} 
		else 
		{
			echo "<script>alert('Item not found');</script>";
		}
	}

	$sql = "SELECT * FROM inventory";
	$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing - Clothing Store Management</title>
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

        /* Billing Form Styles */
        .billing-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .billing-form {
            background: var(--surface);
            border-radius: 1rem;
            border: 1px solid var(--border);
            padding: 2rem;
        }

        .cart-summary {
            background: var(--surface);
            border-radius: 1rem;
            border: 1px solid var(--border);
            padding: 2rem;
            height: fit-content;
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

        /* Cart Items Styles */
        .cart-items {
            margin-bottom: 1.5rem;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 500;
            color: var(--text);
        }

        .item-price {
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .item-quantity {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            padding: 0.25rem 0.5rem;
            border: 1px solid var(--border);
            border-radius: 0.25rem;
            background: var(--background);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: var(--border);
        }

        .remove-item {
            color: var(--danger);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
        }

        .remove-item:hover {
            background: var(--background);
        }

        /* Summary Styles */
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            color: var(--text-light);
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
            font-weight: 600;
            color: var(--text);
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

            .billing-container {
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
                    <a href="stock.php" class="nav-link">
                        <i class="fas fa-truck"></i>
                        <span>Stock Purchased</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="billing.php" class="nav-link active">
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
                <h1>Billing</h1>
                <div class="header-actions">
                    <a href="recent_bills.php" class="btn btn-outline">
                        <i class="fas fa-history"></i>
                        Recent Bills
                    </a>
                </div>
            </header>

            <div class="billing-container">
                <!-- Billing Form -->
                <div class="billing-form">
                    <form id="billingForm" onsubmit="return addToCart(event)">
                        <div class="form-group">
                            <label class="form-label" for="customer_name">Customer Name</label>
                            <input type="text" id="customer_name" name="customer_name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="item">Select Item</label>
                            <select id="item" name="item_id" class="form-control" required>
                                <option value="">Select an item</option>
                                <?php
                                $conn = new mysqli("localhost", "root", "", "admins");
                                if ($conn->connect_error) {
                                    die("Connection failed: " . $conn->connect_error);
                                }
                                $sql = "SELECT * FROM inventory";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<option value='" . $row['ID'] . "' data-price='" . $row['price'] . "' data-name='" . htmlspecialchars($row['item_name']) . "'>" . $row['item_name'] . " - ₹" . number_format($row['price'], 2) . "</option>";
                                    }
                                }
                                $conn->close();
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="quantity">Quantity</label>
                            <input type="number" id="quantity" name="quantity" class="form-control" min="1" required>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Add to Cart
                        </button>
                    </form>
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h2>Cart Summary</h2>
                    <div class="cart-items">
                        <!-- Cart items will be dynamically added here -->
                    </div>
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span>₹0.00</span>
                    </div>
                    <div class="summary-item">
                        <span>Tax (18%)</span>
                        <span>₹0.00</span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span>₹0.00</span>
                    </div>
                    <button class="btn btn-primary" style="width: 100%; margin-top: 1rem;" onclick="completePurchase()">
                        <i class="fas fa-check"></i>
                        Complete Purchase
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        let cart = [];
        let subtotal = 0;
        const TAX_RATE = 0.18; // 18% tax rate

        function addToCart(event) {
            event.preventDefault();
            
            const select = document.getElementById('item');
            const option = select.options[select.selectedIndex];
            const quantity = parseInt(document.getElementById('quantity').value);
            
            if (!option.value || !quantity) {
                alert('Please select an item and quantity');
                return false;
            }

            if (quantity <= 0) {
                alert('Please enter a valid quantity');
                return false;
            }

            const item = {
                id: option.value,
                name: option.dataset.name,
                price: parseFloat(option.dataset.price),
                quantity: quantity,
                total: parseFloat(option.dataset.price) * quantity
            };

            cart.push(item);
            updateCartDisplay();
            document.getElementById('billingForm').reset();
            return false;
        }

        function updateCartDisplay() {
            const cartItems = document.querySelector('.cart-items');
            cartItems.innerHTML = '';
            subtotal = 0;

            cart.forEach((item, index) => {
                subtotal += item.total;
                cartItems.innerHTML += `
                    <div class="cart-item">
                        <div class="item-details">
                            <div class="item-name">${item.name}</div>
                            <div class="item-price">₹${item.price.toFixed(2)} × ${item.quantity}</div>
                        </div>
                        <div>₹${item.total.toFixed(2)}</div>
                        <div class="remove-item" onclick="removeItem(${index})">
                            <i class="fas fa-times"></i>
                        </div>
                    </div>
                `;
            });

            // Update summary
            try {
                const tax = subtotal * TAX_RATE;
                const total = subtotal + tax;

                const summaryItems = document.querySelectorAll('.summary-item span:last-child');
                if (summaryItems.length >= 2) {
                    summaryItems[0].textContent = '₹' + subtotal.toFixed(2);
                    summaryItems[1].textContent = '₹' + tax.toFixed(2);
                }

                const totalElement = document.querySelector('.summary-total span:last-child');
                if (totalElement) {
                    totalElement.textContent = '₹' + total.toFixed(2);
                }
            } catch (error) {
                console.error('Error updating summary:', error);
            }
        }

        function removeItem(index) {
            cart.splice(index, 1);
            updateCartDisplay();
        }

        function completePurchase() {
            if (cart.length === 0) {
                alert('Please add items to cart first');
                return;
            }

            const customerName = document.getElementById('customer_name').value;
            if (!customerName) {
                alert('Please enter customer name');
                return;
            }

            const formData = new FormData();
            formData.append('customer_name', customerName);
            formData.append('cart', JSON.stringify(cart));

            // Show loading state
            const purchaseButton = document.querySelector('.cart-summary .btn-primary');
            const originalText = purchaseButton.innerHTML;
            purchaseButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            purchaseButton.disabled = true;

            fetch('process_bill.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(result => {
                purchaseButton.innerHTML = originalText;
                purchaseButton.disabled = false;

                if (result.trim() === 'success') {
                    alert('Purchase completed successfully!');
                    cart = [];
                    updateCartDisplay();
                    document.getElementById('customer_name').value = '';
                    // Redirect to bill page
                    window.location.href = 'generate_bill.php';
                } else if (result.startsWith('error:')) {
                    alert('Error: ' + result.substring(6));
                } else {
                    alert('Error processing purchase. Please try again.');
                }
            })
            .catch(error => {
                purchaseButton.innerHTML = originalText;
                purchaseButton.disabled = false;
                alert('Error processing purchase: ' + error);
            });
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
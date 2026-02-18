<?php
session_start();
	
	if (!isset($_SESSION['admin'])) 
	{
		
		echo "<script>alert('You are not logged in, please log in');</script>";
		header("Location: login.html");
		exit();
		
	}

	if (!isset($_GET['ID']) || !isset($_GET['name']) || !isset($_GET['quantity']) || !isset($_GET['price'])) {
		echo "<script>alert('Missing required fields');</script>";
		header("Location: inventory.php");
		exit();
	}

	$id = $_GET['ID'];
	$name = $_GET['name'];
	$quantity = $_GET['quantity'];
	$price = $_GET['price'];
	
	$conn = new mysqli("localhost", "root", "", "admins");

	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("UPDATE inventory SET item_name = ?, quantity = ?, price = ? WHERE ID = ?");
	$stmt->bind_param("sidi", $name, $quantity, $price, $id);

	if ($stmt->execute()) {
		if ($stmt->affected_rows > 0) {
			echo "<script>alert('Item updated successfully');</script>";
		} else {
			echo "<script>alert('No changes made or item not found');</script>";
		}
	} else {
		echo "<script>alert('Error updating item: " . $conn->error . "');</script>";
	}

	$stmt->close();
	$conn->close();

	echo "<script>window.location.href = 'inventory.php';</script>";
?>
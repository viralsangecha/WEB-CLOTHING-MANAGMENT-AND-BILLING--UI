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

	$id=$_GET['id'];
	
	$conn = new mysqli("localhost", "root", "", "admins");

	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$stmt = $conn->prepare("DELETE FROM inventory WHERE ID = ?");
	$stmt->bind_param("i", $id);

	if ($stmt->execute()) {
		if ($stmt->affected_rows > 0) {
			echo "<script>alert('Item deleted successfully');</script>";
		} else {
			echo "<script>alert('Item not found');</script>";
		}
	} else {
		echo "<script>alert('Error deleting item: " . $conn->error . "');</script>";
	}

	$stmt->close();
	$conn->close();

	echo "<script>window.location.href = 'inventory.php';</script>";
?>
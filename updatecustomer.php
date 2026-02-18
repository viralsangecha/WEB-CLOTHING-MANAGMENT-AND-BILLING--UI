<?php
session_start();
	
	if (!isset($_SESSION['admin'])) 
	{
		
		echo "<script>alert('your are not logged in ,please log in');</script>";
		header("Location: login.html");
		exit();
		
	}
	$id = $_GET["ID"];
	$name = $_GET["name"];
	$number = $_GET["number"];
	
	$con = mysqli_connect("localhost","root","","admins");
	$query = mysqli_query($con,"update customer set name='$name', number=$number where ID=$id");
	
	header('location:Customers.php');
?>
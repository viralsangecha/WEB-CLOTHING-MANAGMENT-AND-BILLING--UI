<?php
session_start();
	
	if (!isset($_SESSION['admin'])) 
	{
		
		echo "<script>alert('your are not logged in ,please log in');</script>";
		header("Location: login.html");
		exit();
		
	}
	$id=$_GET['id'];
	
	$con=mysqli_connect("localhost","root","","admins");
	$query=mysqli_query($con,"delete  from customer where ID=$id");
	echo mysqli_affected_rows($con);
	header('Location:Customers.php');
?>
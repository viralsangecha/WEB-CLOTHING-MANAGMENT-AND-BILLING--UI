<?php
	session_start();
	
	$conn = new mysqli("localhost","root","","admins");

	if ($conn->connect_error) 
	{
		die("Connection failed: " . $conn->connect_error);
	}

	$user = $_POST['username'];
	$pass = $_POST['password'];
	
	$query = mysqli_query($conn,"SELECT Username FROM admin WHERE Username = '$user' AND Password ='$pass'");
	$query1 ="SELECT * FROM admin WHERE Username = '$user' AND Password ='$pass'";
	
	
	$r = $conn->query($query1);

	$row = $r->fetch_assoc();
	$id=$row['ID'];
	$que=$row['question'];
	$ans=$row['ans'];
	
	
	
	mysqli_num_rows($query);

	if (mysqli_num_rows($query)> 0) 
	{
		$_SESSION['admin'] = $user;
		$_SESSION['password']=$pass;
		$_SESSION['id']=$id;
		$_SESSION['que'] = $que;
		$_SESSION['ans'] = $ans;
		header("Location:dashboard.php");
		
	} 
	else 
	{
		echo "<script>alert('Invalid username or password');
		window.location.href='login.html';</script>";
	}

	$conn->close();
	?>

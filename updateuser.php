<?php 
	session_start();

	if (!isset($_SESSION['admin']))
	{
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
		$user = $_POST['username'];
		$pass = $_POST['password'];
		$que=$_POST['qu'];
		$ans=$_POST['ans'];
		$id=$_POST['id'];

		$query = mysqli_query($conn,"UPDATE admin SET Username='$user' , Password='$pass' ,question='$que',ans='$ans' where Id='$id'");
		
		
		$_SESSION['admin'] = $user;
		$_SESSION['password']=$pass;
		
		
			echo "<script>alert('Your Data updated ');
			window.location.href='logout.php';</script>";
		
		
		
	}





?>
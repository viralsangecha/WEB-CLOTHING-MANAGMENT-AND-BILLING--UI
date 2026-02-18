<?php
	session_start();
	
	if (!isset($_SESSION['admin'])) 
	{
		
		echo "<script>alert('your are not logged in ,please log in');</script>";
		header("Location: login.html");
		exit();
		
	}
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "admins";
	
	$conn = new mysqli($servername, $username, $password, $dbname);

	if ($conn->connect_error) 
	{
		die("Connection failed: " . $conn->connect_error);
	}

	$user = $_POST['newusername'];
	$pass = $_POST['newpassword'];
	$q=$_POST['q1'];
	$ans=$_POST['ans'];
	
	$sql="insert into admin (Username ,Password,question,ans) values('$user' , '$pass','$q','$ans')";
	$result = $conn->query($sql);

	if (mysqli_affected_rows($conn)> 0) 
	{
		header("Location:login.html");
		
	} 
	else 
	{
		echo "<script>alert('Please Register');
		window.location.href='dasboard.php';</script>";
	}

	$conn->close();
	?>

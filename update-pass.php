<?php
	
	session_start();
	
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		session_start();
		$conn = new mysqli("localhost","root","","admins");

		if ($conn->connect_error) 
		{
			die("Connection failed: " . $conn->connect_error);
		}
		$pass=$_POST['Password'];
		$sql =("UPDATE admin SET Password='$pass' WHERE Username='$_SESSION[user]'");
		$conn->query($sql);
		
		$u=mysqli_affected_rows($conn);
	
		if ($u> 0) 
		{
			echo "<script>alert('Your password is Updated successfully !')
			window.location.href='login.html';</script>";
		} 
		
		{
			//echo "<script>alert('username not found !');
			//window.location.href='login.html';</script>";
		}

		$conn->close();
	}
?>
<html>
<head>
    <title>Admin Forgot Password</title>
    <style>
		body 
		{
			font-family: 'Arial', sans-serif;
			background: url('tb.png') no-repeat center fixed;
			background-size: cover;
			display: flex;
			justify-content: center;
			align-items: center;
			height: 100vh;
			margin: 0;
			
		}

		.login-container
		{
			background-color: rgba(0, 0, 0, 0.6);
						
			padding: 40px;
			margin-bottom:40px;
			border-radius: 16px;
			box-shadow: 0 0 10px rgba(0, 0, 0, 0.9);
			text-align: center;
			width: 300px;
		}

		h2 
		{
			color: white;
			font-size: 24;
			margin-bottom: 40px;
		}

		label
		{
			display: block;
			margin-bottom: 5px;
			color:white;
			font-size:19;
		}
		a
		{
			text-decoration:none;
			color:white;
		}

		input 
		{
			width: 100%;
			padding: 10px;
			margin: 10px 0;
			border: none;
			border-radius: 4px;
			box-sizing: border-box;
		}

		input[type="submit"]
		{
			width: 50%;
			padding: 10px;
			border: none;
			border-radius: 4px;
			background:rgb(25, 10, 96);
			color: white;
			font-size: 16px;
			cursor: pointer;
			margin-top: 10px;
			transition:0.5s;
		}
		
		input[type="submit"]:hover 
		{
			box-shadow: 0 0 10px rgb(0,0,0);
			background:rgb(46, 18, 174);
			width:100%;
			transition:0.5s;
		}
	</style>
</head>
<body>
    <div class="login-container">
        <h2>Admin Forgot Password</h2>
        <form action="update-pass.php" method="post">
		
            <label for="password">Password:</label>			
            <input type="Password" id="Password" name="Password" placeholder="new Password" required>
			<input type="submit" value="UPDATE">
        </form>
    </div>
</body>
</html>
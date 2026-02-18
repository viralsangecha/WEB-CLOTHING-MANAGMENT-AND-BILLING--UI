<?php
	session_start();
	$conn = new mysqli("localhost","root","","admins");

	if ($conn->connect_error)
	{
		die("Connection failed: " . $conn->connect_error);
	}

		$query1 ="SELECT question FROM admin WHERE Username = '$_SESSION[user]'";
			$result = $conn->query($query1);
			$row = $result->fetch_assoc();



			if ($_SERVER['REQUEST_METHOD'] == 'POST')
			{
				$ans=$_POST['ans'];

				$query = mysqli_query($conn,"SELECT Username FROM admin WHERE question='$row[question]' AND ans='$ans'");

				if (mysqli_num_rows($query)> 0)
				{
					echo "<script>alert('Answer is verified got update password');
					window.location.href='update-pass.php';</script>";


				}
				else
				{
					echo "<script>alert('wrong Answer!')</script>";
				}
			}
?>
<head>
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
			<form action="que-ans.php" method="POST">
			<h2>Answer your Qustion That you set Whane You Registered</h2>
			<label>Username:<?php echo $_SESSION['user'];?> </label>
			<label for="q1">Question:-<?php
			$query1 ="SELECT question FROM admin WHERE Username = '$_SESSION[user]'";
			$result = $conn->query($query1);
			$row = $result->fetch_assoc();
			echo $row['question']?></label><br>

			<label for="ans">Answer:</label>
            <input type="text" id="ans" name="ans" required>

			<center><input type="submit" value="verify">
			</form>
	</div>
</body>
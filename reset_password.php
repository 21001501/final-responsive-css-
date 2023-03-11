<!DOCTYPE html>
<html>
<head>
  <title>Reset Password</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
 	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
  	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  
  <style>
	body {
		font-family: 'Poppins', sans-serif; 
		display: flex;
		justify-content: center;
		align-items: center;
		min-height: 100vh;
		background-color: #F0F8FF;
	}

	form {
			background-color: #DCDCDC;
			padding: 20px;
			border-radius: 10px;
			max-width: 900px;
			margin: 0 auto;
			display: block;
			font-size: 32px; 
	}

	h2 {
		position: absolute;
		top: 23%;
		left: 50%;
		transform: translate(-50%, -50%);
		text-align: center;
		justify-content: center; 
	}

	label {
	display: block;
	margin-bottom: 10px;
	font-size: 16px; 
	font-weight: bold;
	}

	input[type="password"] {
	padding: 10px;
	border-radius: 5px;
	margin-bottom: 20px;
	width: 95%;
	}

	input[type="submit"] {
		background-color: #000080;
				color: white;
				padding: 12px 20px;
				border: none;
				border-radius: 5px;
				cursor: pointer;
				font-size: 16px;
				margin-top: 10px;
				width: 100%;
				transition: 0.3s ease;
				font-size: 20px; 
	}

	input[type="submit"]:hover {	
		background-color: #4169E1;
				color: #000000; 
	}

  </style>
</head>
<body>

<?php
session_start();
require_once "db_init.php";

// Check if token is valid
if (isset($_GET["token"])) {
  $token = $_GET["token"];

  $stmt = $db->prepare("SELECT * FROM users WHERE token = ?");
  $stmt->execute([$token]);
  $user = $stmt->fetch();

  if (!$user) {
    $_SESSION["reset-password-error"] = "Invalid reset token.";
    header("Location: forgot_password.php");
    exit();
  }
} else {
  $_SESSION["reset-password-error"] = "Reset token not provided.";
  header("Location: forgot_password.php");
  exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $password = $_POST["password"];
  $confirm_password = $_POST["confirm_password"];

  // Validate input
  if ($password != $confirm_password) {
    $_SESSION["reset-password-error"] = "Passwords do not match.";
    header("Location: reset_password.php?token=$token");
    exit();
  }

  // Update the user's password in the database
  $password_hash = password_hash($password, PASSWORD_DEFAULT);
  $stmt = $db->prepare("UPDATE users SET password = ?, token = NULL WHERE email = ?");
  $stmt->execute([$password_hash, $user["email"]]);

  $_SESSION["reset-password-success"] = "Password reset successfully.";
  header("Location: login.php");
  exit();
}
?>

<h2>Reset Password</h2>

<form method="post" action="reset_password.php?token=<?php echo $token; ?>">
  <label for="password">New Password:</label>
  <input type="password" name="password" required>
  <label for="confirm_password">Confirm Password:</label>
  <input type="password" name="confirm_password" required>
  <input type="submit" value="Reset Password">
</form>


</body>
</html>
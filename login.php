<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
	<title>Login and Registration</title>
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

		input[type="text"],
		input[type="password"],
		input[type="email"],
		input[type="date"],
		input[type="firstname"],
		input[type="lastname"],
		input[type="number"] {
			padding: 10px;
			width: 100%;
			border: none;
			border-radius: 5px;
			margin-bottom: 10px;
			box-sizing: border-box;
			font-size: 16px;
		}

		label {
			display: block;
			margin-bottom: 10px;
			font-size: 16px;
			font-weight: bold;
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
			color: white;
		}

		.error {
			color: red;
			margin-bottom: 10px;
		}

		/*mini blue text */
		.login-link{
			text-align: center;
			margin-top: 20px;
			font-size: 14px;
			cursor: pointer;
			color: blue;
			text-decoration: underline;
		}
		.remember-link{
			text-align: center;
			margin-top: 20px;
			font-size: 14px;
			cursor: pointer;
			color: blue;
			text-decoration: underline;
		}
		.forgot-link {
			text-align: center;
			margin-top: 20px;
			font-size: 14px;
			cursor: pointer;
			color: blue;
			text-decoration: underline;
		}
		.register-link {
			text-align: center;
			margin-top: 20px;
			font-size: 14px;
			cursor: pointer;
			color: blue;
			text-decoration: underline;
		}

	
		#register-form {
			display: none;
		}
		
		#forgot-form{
			display: none;
		}

		.active {
			display: block;
		}
	</style>
</head>
<body>

<form id="login-form" class="active" method="post" action="login_handler.php">
	<label>Username:</label>
	<input type="text" name="username" required>
	<label>Password:</label>
	<input type="password" name="password" required>
	<input type="submit" value="Login">
	<?php if (isset($_SESSION["login-error"])): ?>
		<p class="error"><?php echo $_SESSION["login-error"]; ?></p>
		<?php unset($_SESSION["login-error"]); ?>
	<?php endif; ?>
	<p class="register-link" id="register-toggle">Don't have an account?</p>
	<p class="forgot-link" id="forgot-toggle">Forgot password?</p>
</form>

<form id="register-form" method="post" action="register_handler.php">
	<label>First Name:</label>
	<input type = "text" name = "firstname" required>
	<label>Last Name:</label>
	<input type = "text" name = "lastname" required>
	<label>Phone Number:</label>
	<input type = "text" name = "number" required>
	<label>Username:</label>
	<input type="text" name="username" required>
	<label>Email:</label>
	<input type="email" name="email" required>
	<label>Date of Birth:</label>
	<input type="date" name="dob" required>
	<label>Password:</label>
	<input type="password" name="password" required>
	<label>Confirm Password:</label>
	<input type="password" name="password-confirm" required>
	<input type="submit" value="Register">
	<?php if (isset($_SESSION["register-error"])): ?>
		<p class="error"><?php echo $_SESSION["register-error"]; ?></p>
		<?php unset($_SESSION["register-error"]); ?>
	<?php endif; ?>
	<p class="login-link" id="login-toggle">Already have an account?</p>
</form>

<form id = "forgot-form" method="post" action="forgot_password_handler.php">
  <label for="email">Email:</label>
  <input type="email" name="email" required>
  <input type="submit" value="Reset Password">
  <p class="remember-link" id="remember-toggle">Remembered your password?</p>
</form>


<script>
	var loginForm = document.getElementById("login-form");
	var registerForm = document.getElementById("register-form");
	var registerLink = document.getElementById("register-toggle");
	var rememberLink = document.getElementById("remember-toggle");
	var rememberForm = document.getElementById("remember-form");
	var loginLink = document.getElementById("login-toggle");
	var forgotForm = document.getElementById("forgot-form");
	var forgotLink = document.getElementById("forgot-toggle");

	loginLink.addEventListener("click", function() {
		toggleForm("login");
	});
	registerLink.addEventListener("click", function() {
		toggleForm("register");
	});
	forgotLink.addEventListener("click", function() {
		toggleForm("forgot");
	});
	rememberLink.addEventListener("click", function() {
		toggleForm("login");
	});

	function toggleForm(form) {
		if (form == "login") {
			loginForm.style.display = "block";
			registerForm.style.display = "none";
			forgotForm.style.display = "none";
		} else if (form == "register") {
			loginForm.style.display = "none";
			registerForm.style.display = "block";
			forgotForm.style.display = "none";
		}
		else if(form == "forgot") {
			loginForm.style.display = "none";
			registerForm.style.display = "none";
			forgotForm.style.display = "block";
		}
	}
</script>
</body>
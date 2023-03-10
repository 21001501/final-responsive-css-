<?php
session_start();
if (!isset($_SESSION["user_id"])) {
	header("Location: login.php");
	exit();
}

require_once "db_init.php";

// Retrieve the user's information from the database
$stmt = $db->prepare("SELECT username, email, dob FROM users WHERE id = :id");
$stmt->bindValue(":id", $_SESSION["user_id"]);
$stmt->execute();
$user = $stmt->fetch();

// Extract the first name from the username
$first_name = explode(" ", $user["username"])[0];

// Check if there are any upcoming birthdays
$stmt = $db->prepare("SELECT firstname, lastname, dob FROM users WHERE dob >= CURDATE() AND dob <= DATE_ADD(CURDATE(), INTERVAL 2 DAY) AND id != :id");
$stmt->bindValue(":id", $_SESSION["user_id"]);
$stmt->execute();
$upcoming_birthdays = $stmt->fetchAll();

// Display a notification message if there are any upcoming birthdays
if (!empty($upcoming_birthdays)) {
	$names = array_map(function ($u) { return $u["firstname"] . " " . $u["lastname"]; }, $upcoming_birthdays);
	$message = "Upcoming birthdays: " . implode(", ", $names);
	echo "<script>alert('$message');</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
 	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
  	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
	
	<title>profile</title>
	<style>
		.navbar {
			position: fixed;
			top: 0;
			left: 0;
			width: 100px;
			height: 100vh;
			background-color: #f0f0f0;
			padding: 20px;
		}

		.navbar a {
			display: block;
			margin-bottom: 10px;
		}
	</style>
</head>
<body>
	<div class="navbar">
		<a href="#">Home</a>
		<a href="#">Dashboard</a>
		<a href="#">Settings</a>
	</div>

	<h1>Welcome, <?php echo $first_name; ?>!</h1>
	<p>You are logged in as <?php echo $user["username"]; ?>.</p>
	<p>Your date of birth is <?php echo $user["dob"]; ?>.</p>
    <a href="add_debit.php?user_id=<?php echo $_SESSION['user_id']; ?>">Add Debit</a>

	<p><a href="logout.php">Log out</a></p>
</body>
</html>
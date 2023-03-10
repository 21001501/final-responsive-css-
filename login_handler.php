<?php
session_start();
require_once "db_init.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = $_POST["username"];
	$password = $_POST["password"];

	$stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
	$stmt->bindValue(":username", $username);
	$stmt->execute();
	$user = $stmt->fetch();

	// Verify password
	if ($user && password_verify($password, $user["password"])) {
		$_SESSION["user_id"] = $user["id"];
		header("Location: profile.php"); // Redirect to members-only page
		exit();
	} else {
		$_SESSION["login-error"] = "Invalid username or password";
		header("Location: login.php");
		exit();
	}
}
?>
<?php
session_start();
require_once "db_init.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST["email"];

  // Check if email exists in the database
  $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if (!$user) {
    $_SESSION["forgot-password-error"] = "This email address is not registered.";
    header("Location: forgot_password.php");
    exit();
  }

  // Generate a random token
  $token = bin2hex(random_bytes(32));

  // Update the user's token in the database
  $stmt = $db->prepare("UPDATE users SET token = ? WHERE email = ?");
  $stmt->execute([$token, $email]);

  // Redirect the user to a password reset page with the token in the URL
  header("Location: reset_password.php?token=$token");
  exit();
}
?>
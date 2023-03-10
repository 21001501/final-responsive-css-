<?php
session_start();
require_once "db_init.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $amount = $_POST["amount"];
  $description = $_POST["description"];
  $user_id = $_SESSION["user_id"];

  // Insert new debit into database
  $stmt = $db->prepare("INSERT INTO debits (user_id, amount, description) VALUES (:user_id, :amount, :description)");
  $stmt->bindValue(":user_id", $user_id);
  $stmt->bindValue(":amount", $amount);
  $stmt->bindValue(":description", $description);
  $stmt->execute();

  // Redirect back to profile page
  header("Location: profile.php");
  exit();
}
?>
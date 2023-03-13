<?php
session_start();
require_once "db_init.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];
    $number = $_POST["number"];
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $password_confirm = $_POST["password-confirm"];
    $dob = $_POST["dob"];

    // Check if passwords match
    if ($password != $password_confirm) {
        $_SESSION["register-error"] = "Passwords do not match";
        header("Location: login.php");
        exit();
    }

    // Check if username is taken
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindValue(":username", $username);
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION["register-error"] = "Username is already taken";
        header("Location: login.php");
        exit();
    }

    // Hash password and insert user into database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO users (username, firstname, lastname, number, email, password, dob) VALUES (:username, :firstname, :lastname, :number, :email, :password, :dob)");
    $stmt->bindValue(":firstname", $firstname);
    $stmt->bindValue(":lastname", $lastname);
    $stmt->bindValue(":number", $number);
    $stmt->bindValue(":username", $username);
    $stmt->bindValue(":email", $email);
    $stmt->bindValue(":password", $hashed_password);
    $stmt->bindValue(":dob", $dob);
    $stmt->execute();

    $_SESSION["user_id"] = $db->lastInsertId();
    header("Location: login.php");
}
?>

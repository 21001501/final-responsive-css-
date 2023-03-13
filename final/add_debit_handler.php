<?php
session_start();
require_once "db_init.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $amount = $_POST["amount"];
    $payment_installment = $_POST["payment_installment"];
    $final_date = $_POST["final_date"];
    $description = $_POST["description"];
    $user_id = $_POST["user_id"];

    // Validate input
    if (empty($title) || empty($amount) || empty($payment_installment) || empty($final_date) || empty($description)) {
        $_SESSION["debit-error"] = "Please fill in all fields";
        header("Location: add_debit.php");
        exit();
    }

    if (!is_numeric($amount) || !is_numeric($payment_installment)) {
        $_SESSION["debit-error"] = "Amount and payment installment must be numbers";
        header("Location: add_debit.php");
        exit();
    }

    // Insert debit into database
    $stmt = $db->prepare("INSERT INTO debits (user_id, title, amount, payment_installment, final_date, description) VALUES (:user_id, :title, :amount, :payment_installment, :final_date, :description)");
    $stmt->bindValue(":user_id", $user_id);
    $stmt->bindValue(":title", $title);
    $stmt->bindValue(":amount", $amount);
    $stmt->bindValue(":payment_installment", $payment_installment);
    $stmt->bindValue(":final_date", $final_date);
    $stmt->bindValue(":description", $description);
    $stmt->execute();

    $_SESSION["success-message"] = "Debit added successfully";
    header("Location: profile.php");
    exit();
}
?>
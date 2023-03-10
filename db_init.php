<?php
$db = new PDO('sqlite:users.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    firstname TEXT,
    lastname TEXT,
    number INTEGER,
    email TEXT UNIQUE,
    password TEXT,
    dob TEXT,
    token TEXT
)");
$db->exec("CREATE TABLE IF NOT EXISTS debits (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    amount FLOAT,
    title TEXT,
    description TEXT,
    payment_installment FLOAT,
    final_date TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");
?>

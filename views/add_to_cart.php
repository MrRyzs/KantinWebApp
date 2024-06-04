<?php
require_once "../function/connection.php";
session_start();

if (!isset($_SESSION["email_user"])) {
    echo "You need to log in first.";
    exit;
}

if (isset($_POST['menu_id']) && isset($_POST['quantity'])) {
    $email_user = $_SESSION["email_user"];
    $menu_id = $_POST['menu_id'];
    $quantity = $_POST['quantity'];
    $toppings = isset($_POST['toppings']) ? $_POST['toppings'] : [];

    // Fetch username
    $query = "SELECT username FROM info_user WHERE email_user = ?";
    $stmt = mysqli_prepare($host, $query);
    mysqli_stmt_bind_param($stmt, "s", $email_user);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $username = $row['username'];

    $date = date('Y-m-d');
    $transaction_status = 'pending';

    foreach ($toppings as $topping_id) {
        $query = "INSERT INTO transaction (email_usr, username, food_id, toppings_id, transaction_status, transaction_date, quantity) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($host, $query);
        mysqli_stmt_bind_param($stmt, "ssisssi", $email_user, $username, $menu_id, $topping_id, $transaction_status, $date, $quantity);
        mysqli_stmt_execute($stmt);
    }

    echo "Added to cart";
} else {
    echo "Invalid request.";
}
?>

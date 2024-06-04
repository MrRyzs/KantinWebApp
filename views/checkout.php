<?php
require_once "../function/connection.php";
session_start();

if (!isset($_SESSION["email_user"])) {
    echo "You need to log in first.";
    exit;
}

$email_user = $_SESSION["email_user"];

$query = "
    UPDATE transaction 
    SET transaction_status = 'diproses'
    WHERE email_usr = ? AND transaction_status = 'pending'
";
$stmt = mysqli_prepare($host, $query);
mysqli_stmt_bind_param($stmt, "s", $email_user);
mysqli_stmt_execute($stmt);

echo "Checkout successful";
?>

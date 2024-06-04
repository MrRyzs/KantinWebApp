<?php
require_once "../function/connection.php";

if (isset($_POST['penjual'])) {
    $penjual = $_POST['penjual'];

    $query = "SELECT * FROM menu WHERE penjual = ?";
    $stmt = mysqli_prepare($host, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $penjual);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='menu-item'>";
                echo "<h5>" . htmlspecialchars($row['nama_menu']) . "</h5>";
                echo "<p>Price: " . htmlspecialchars($row['harga']) . "</p>";
                echo "<p>Select Topping: <button class='btn-topping btn btn-warning' data-menuid='" . $row['food_id'] . "'>Select</button></p>";
                echo "<div class='topping-content'></div>"; // Container for toppings
                echo "<button class='btn btn-danger btn-menu btn-minus'>-</button> <span class='quantity'>0</span> <button class='btn btn-success btn-menu btn-plus'>+</button><br>";
                echo "</div><hr>";
            }
        } else {
            echo "No menu items found.";
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Failed to prepare the statement.";
    }
} else {
    echo "Invalid request.";
}

?>

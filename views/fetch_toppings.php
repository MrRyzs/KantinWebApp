<?php
require_once "../function/connection.php";

if (isset($_POST['food_id'])) {
    $food_id = $_POST['food_id'];

    $query = "SELECT * FROM toppings WHERE food_id = ?";
    $stmt = mysqli_prepare($host, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $food_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result) {
            echo "<form id='toppingForm'>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='topping-item'>";
                echo "<input type='checkbox' name='toppings[]' value='" . htmlspecialchars($row['toppings_id']) . "'> ";
                echo "<label>" . htmlspecialchars($row['toppings_name']) . " - Price: ";
                echo ($row['toppings_price'] != null || $row['toppings_price'] != 0) ? htmlspecialchars($row['toppings_price']) : "free"; // Perubahan di sini
                echo "</label>";
                echo "</div><hr>";
            }
            echo "</form>";
        } else {
            echo "No toppings found.";
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Failed to prepare the statement.";
    }
} else {
    echo "Invalid request.";
}
?>

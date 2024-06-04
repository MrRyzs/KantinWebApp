<?php
require_once "../function/connection.php";
session_start();

if (!isset($_SESSION["email_user"])) {
    header("Location: login.php");
    exit;
}

$email_user = $_SESSION["email_user"];

// Fetch cart items
$query = "
    SELECT 
        t.transaction_id, 
        m.nama_menu, 
        t.quantity, 
        GROUP_CONCAT(tp.toppings_name SEPARATOR ', ') as toppings, 
        (m.harga + COALESCE(SUM(tp.toppings_price), 0)) * t.quantity as price,
        m.harga
    FROM 
        transaction t
    LEFT JOIN menu m ON t.food_id = m.food_id
    LEFT JOIN toppings tp ON FIND_IN_SET(tp.toppings_id, t.toppings_id)
    WHERE 
        t.email_usr = ? AND t.transaction_status = 'pending'
    GROUP BY 
        t.transaction_id, m.nama_menu, t.quantity, m.harga
";
$stmt = mysqli_prepare($host, $query);
mysqli_stmt_bind_param($stmt, "s", $email_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row_count = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css"> <!-- Include the CSS file -->
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <div class="col-12 col-md-3 col-lg-2 p-0">
        <footer class="footer">
          <nav>
              <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="account.php">Account</a></li>
              </ul>
            </nav>
        </footer>
      </div>
      <div class="mb-3 title">
        <h1>Your Cart</h1>
      </div>
      <div class="col-12 col-md-9 col-lg-10 content">
        
        <?php if ($row_count > 0) { ?>
            <?php
            while ($row = mysqli_fetch_assoc($result)) {
                // Debugging: Check if 'harga' is set
                if (!isset($row['harga'])) {
                    echo "<p class='text-danger'>Error: 'harga' key not found in result.</p>";
                    continue;
                }
            ?>
              <div class="card mb-3">
                <div class="card-body">
                  <h5 class="card-title"><?php echo htmlspecialchars($row['nama_menu']); ?></h5>
                  <p class="card-text">Quantity: <?php echo htmlspecialchars($row['quantity']); ?></p>
                  <p class="card-text">Toppings: <?php echo htmlspecialchars($row['toppings']); ?></p>
                  <p class="card-text">Price: Rp. <?php echo htmlspecialchars($row['harga'] * $row['quantity']); ?></p>
                </div>
              </div>
            <?php
                }
            ?>
          <div class="d-flex justify-content-center">
            <button id="checkout" class="btn btn-warning">Checkout</button>
          </div>
        <?php } else { ?>
          <p class="text-center">Anda tidak memesan apapun</p>
        <?php } ?>
      </div>
    </div>

  </div>
  <!-- Modal for Menu -->
  <div class="modal fade" id="menuModal" tabindex="-1" aria-labelledby="menuModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="menuModalLabel">Menu</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="menu-content"></div>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal for Toppings -->
  <div class="modal fade" id="toppingModal" tabindex="-1" aria-labelledby="toppingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="toppingModalLabel">Select Toppings</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="topping-content"></div>
          <button type="button" id="addToppings" class="btn btn-primary">Add To Cart</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
  $(document).ready(function() {
    $('#checkout').on('click', function() {
      $.ajax({
        url: 'checkout.php',
        type: 'POST',
        success: function(response) {
          alert(response);
          location.reload();
        },
        error: function(xhr, status, error) {
          console.error("Checkout failed: ", status, error);
        }
      });
    });
  });
  </script>
</body>
</html>

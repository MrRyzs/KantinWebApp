<?php
require_once "../function/connection.php";
session_start();

if (!isset($_SESSION["email_user"])) {
    header("Location: login.php");
    exit;
}

$email_user = $_SESSION["email_user"];

// Fetch pending cart items
$query_pending = "
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
$stmt_pending = mysqli_prepare($host, $query_pending);
mysqli_stmt_bind_param($stmt_pending, "s", $email_user);
mysqli_stmt_execute($stmt_pending);
$result_pending = mysqli_stmt_get_result($stmt_pending);
$row_count_pending = mysqli_num_rows($result_pending);

// Fetch processed cart items
$query_processed = "
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
        t.email_usr = ? AND t.transaction_status = 'diproses'
    GROUP BY 
        t.transaction_id, m.nama_menu, t.quantity, m.harga
";
$stmt_processed = mysqli_prepare($host, $query_processed);
mysqli_stmt_bind_param($stmt_processed, "s", $email_user);
mysqli_stmt_execute($stmt_processed);
$result_processed = mysqli_stmt_get_result($stmt_processed);
$row_count_processed = mysqli_num_rows($result_processed);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css"> <!-- Include the CSS file -->
    <style>
      .content {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }
    </style>
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
        
        <?php if ($row_count_pending > 0) { ?>
            <?php
            $total_price = 0;
            while ($row = mysqli_fetch_assoc($result_pending)) {
                $total_price += $row['price'];
            ?>
              <div class="card mb-3">
                <div class="card-body">
                  <h5 class="card-title"><?php echo htmlspecialchars($row['nama_menu']); ?></h5>
                  <p class="card-text">Quantity: <?php echo htmlspecialchars($row['quantity']); ?></p>
                  <p class="card-text">Toppings: <?php echo htmlspecialchars($row['toppings']); ?></p>
                  <p class="card-text">Price: Rp. <?php echo htmlspecialchars($row['price']); ?></p>
                </div>
              </div>
            <?php } ?>
          <div class="d-flex justify-content-center">
            <button id="checkout" class="btn btn-warning">Checkout</button>
          </div>
        <?php } else { ?>
          <p class="text-center">Anda tidak memesan apapun</p>
        <?php } ?>

        <hr>
        
        <h2>Processed Orders</h2>
        <?php if ($row_count_processed > 0) { ?>
            <?php while ($row = mysqli_fetch_assoc($result_processed)) { ?>
              <div class="card mb-3">
                <div class="card-body">
                  <h5 class="card-title"><?php echo htmlspecialchars($row['nama_menu']); ?></h5>
                  <p class="card-text">Quantity: <?php echo htmlspecialchars($row['quantity']); ?></p>
                  <p class="card-text">Toppings: <?php echo htmlspecialchars($row['toppings']); ?></p>
                  <p class="card-text">Price: Rp. <?php echo htmlspecialchars($row['price']); ?></p>
                </div>
              </div>
            <?php } ?>
        <?php } else { ?>
          <p class="text-center">No processed orders</p>
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

  <!-- Modal for Checkout Total Price -->
  <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="checkoutModalLabel">Checkout Total</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Total Price: Rp. <span id="totalPrice"></span></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" id="confirmCheckout" class="btn btn-primary">Confirm Checkout</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  $(document).ready(function() {
    $('#checkout').on('click', function() {
      $('#totalPrice').text('<?php echo $total_price; ?>');
      $('#checkoutModal').modal('show');
    });

    $('#confirmCheckout').on('click', function() {
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

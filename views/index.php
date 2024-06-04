<?php
require_once "../function/connection.php";
session_start();

if (!isset($_SESSION["email_user"])) {
    header("Location: login.php");
}

$query = "SELECT username FROM info_user WHERE email_user = ?";
$stmt = mysqli_prepare($host, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $_SESSION["email_user"]);
    $success = mysqli_stmt_execute($stmt);

    if ($success) {
        $result = mysqli_stmt_get_result($stmt);

        if ($result) {
            $row = mysqli_fetch_assoc($result);

            if ($row) {
                $username = $row['username'];
            } else {
                $username = "User";
            }
        } else {
            $username = "User";
        }
    } else {
        $username = "User";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
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
        <h1 class="">Welcome, <?php echo htmlspecialchars($username); ?></h1>
      </div>

      <div class="col-12 col-md-9 col-lg-10 content">
        <?php
        $sql = "SELECT * FROM info_user WHERE role_user = 'penjual'";
        $result = mysqli_query($host, $sql);
        while ($row = mysqli_fetch_array($result)) {
        ?>
          <a href="#" class="card-link" data-bs-toggle="modal" data-bs-target="#menuModal" data-penjual="<?php echo htmlspecialchars($row['username']); ?>">
            <div class="card mb-3" style="max-width: 480px;">
              <div class="row g-0">
                <div class="col-md-4 img">
                  <img src="https://www.cnet.com/a/img/resize/36e8e8fe542ad9af413eb03f3fbd1d0e2322f0b2/hub/2023/02/03/afedd3ee-671d-4189-bf39-4f312248fb27/gettyimages-1042132904.jpg?auto=webp&fit=crop&height=1200&width=1200" class="img-fluid rounded-start" alt="...">
                </div>
                <div class="col-md-8 bdy">
                  <div class="card-body">
                    <h5 class="card-title"><?php echo "Kantin " . htmlspecialchars($row['username']); ?></h5>
                    <p class="card-text">Total yang dijual</p>
                    <p class="card-text">Rp. 0</p>
                  </div>
                </div>
              </div>
            </div>
          </a>
        <?php
        }
        ?>
      </div>
    </div>
  </div>

  <!-- Modal for Menu -->
  <div class="modal fade" id="menuModal" tabindex="-1" aria-labelledby="menuModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <h class="modal-header">
          <h5 class="modal-title" id="menuModalLabel">Menu</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          <hr>
        </h>
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
          <hr>
        </div>
        <div class="modal-body">
          <div id="topping-content"></div>
          <button type="button" id="addToppings" class="btn btn-warning">Add To Cart</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function(){
        // Function to fetch menu when modal is shown
        $('#menuModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var penjual = button.data('penjual');

            $.ajax({
                url: 'fetch_menu.php',
                type: 'POST',
                data: {penjual: penjual},
                success: function(response) {
                    $('#menu-content').html(response);
                }
            });
        });

        // Function to handle button clicks for + and -
        $(document).on('click', '.btn-plus, .btn-minus', function() {
            var $quantity = $(this).closest('.menu-item').find('.quantity');
            var currentVal = parseInt($quantity.text());

            if ($(this).hasClass('btn-plus')) {
                $quantity.text(currentVal + 1);
            } else {
                if (currentVal > 0) {
                    $quantity.text(currentVal - 1);
                }
            }

            var $menuItem = $(this).closest('.menu-item');
            var quantity = parseInt($quantity.text());
            var $addToCartButton = $menuItem.find('.btn-add-to-cart');

            // Enable or disable Add to Cart button based on quantity
            if (quantity > 0) {
                $addToCartButton.prop('disabled', false);
            } else {
                $addToCartButton.prop('disabled', true);
            }
        });

        // Function to handle topping selection
        $(document).on('click', '.btn-topping', function() {
            var foodId = $(this).data('menuid');
            var quantity = parseInt($(this).closest('.menu-item').find('.quantity').text());
            $('#addToppings').data('menuid', foodId);

            $.ajax({
                url: 'fetch_toppings.php',
                type: 'POST',
                data: {food_id: foodId},
                success: function(response) {
                    $('#topping-content').html(response);
                    $('#toppingModal').modal('show');
                    // Enable or disable the add toppings button based on quantity
                    if (quantity > 0) {
                        $('#addToppings').prop('disabled', false);
                    } else {
                        $('#addToppings').prop('disabled', true);
                    }
                }
            });
        });

        // Function to handle adding toppings to cart
        $('#addToppings').on('click', function() {
            var menuId = $(this).data('menuid');
            var toppings = [];
            $('#toppingForm input:checked').each(function() {
                toppings.push($(this).val());
            });

            var $menuItem = $(".btn-topping[data-menuid='" + menuId + "']").closest('.menu-item');
            var quantity = $menuItem.find('.quantity').text();

            $.ajax({
                url: 'add_to_cart.php',
                type: 'POST',
                data: {menu_id: menuId, quantity: quantity, toppings: toppings},
                success: function(response) {
                    alert('Added to cart');
                    $('#toppingModal').modal('hide');
                }
            });
        });

        // Function to handle Add to Cart without toppings
        $(document).on('click', '.btn-add-to-cart', function() {
            var menuId = $(this).data('menuid');
            var quantity = $(this).closest('.menu-item').find('.quantity').text();

            $.ajax({
                url: 'add_to_cart.php',
                type: 'POST',
                data: {menu_id: menuId, quantity: quantity},
                success: function(response) {
                    alert('Added to cart');
                }
            });
        });
    });
  </script>
</body>
</html>

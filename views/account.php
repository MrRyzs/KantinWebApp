<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
  <div class="mb-3 title">
    <h1>Account</h1>
  </div>

  <div class="container text-center">
    <!-- Tombol Logout -->
    <label for="logout">Logout Button: </label><br>
    <button type="button" class="btn btn-danger" onclick="location.href='../function/logout.php'">Logout</button>
  </div>

  <footer class="footer">
    <nav>
        <ul>
          <li><a href="index.php">Dashboard</a></li>
          <li><a href="cart.php">Cart</a></li>
          <li><a href="account.php">Account</a></li>
        </ul>
      </nav>
  </footer>
</body>
</html>
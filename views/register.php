<?php
include "../function/connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nis_user = $_POST["nis_user"];
    $email_user = $_POST["email_user"];
    $username = $_POST["username"];
    $password_user = $_POST["password_user"];
    $role_user = $_POST["role_user"];

    // Hash password before saving to the database
    $hashedPassword = password_hash($password_user, PASSWORD_DEFAULT);

    // Set nis_user to NULL if it's empty or if the role is admin
    if (empty($nis_user) || $role_user == "admin") {
        $nis_user = NULL;
    }

    // Save user data to the database
    $query = "INSERT INTO info_user (nis_user, email_user, username, password_user, role_user) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($host, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssss", $nis_user, $email_user, $username, $hashedPassword, $role_user);
        $success = mysqli_stmt_execute($stmt);

        if ($success) {
            header("Location: login.php");
            exit;
        } else {
            $error = "Failed to save user data.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $error = "Error in preparing SQL statement.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/form.css">
</head>
<body>
    <div class="container login-container">
        <div class="login-box">
            <h1 class="text-center mb-4">Register</h1>
            <?php if (isset($error)) echo "<p class='text-danger'>$error</p>"; ?>
            <form action="" method="post">
                <div class="mb-3">
                    <label for="role_user" class="form-label">Role User:</label>
                    <select name="role_user" id="role_user" class="form-select" required>
                        <option value="" disabled selected>---Select Role---</option>
                        <option value="admin">Admin</option>
                        <option value="siswa">Siswa</option>
                        <option value="guru">Guru</option>
                        <option value="penjual">Penjual</option>
                    </select>
                </div>

                <div class="mb-3" id="nis_section">
                    <label for="nis_user" class="form-label" id="nis_label">NIS User:</label>
                    <input type="text" class="form-control" id="nis_user" name="nis_user">
                </div>

                <div class="mb-3">
                    <label for="email_user" class="form-label">Email User:</label>
                    <input type="email" class="form-control" id="email_user" name="email_user" required>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>

                <div class="mb-3">
                    <label for="password_user" class="form-label">Password User:</label>
                    <input type="password" class="form-control" id="password_user" name="password_user" required>
                </div>

                <div class="mb-3">
                    <input type="checkbox" name="agreement" id="agreement" required>
                    <label for="agreement">I agree to the <a href="#" style="color: #fff;">terms and conditions</a></label>
                </div>

                <button type="submit" class="btn btn-warning w-100">Register</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('role_user').addEventListener('change', function() {
            var role = this.value;
            var nisSection = document.getElementById('nis_section');
            var nisLabel = document.getElementById('nis_label');
            var nisInput = document.getElementById('nis_user');

            if (role === 'admin') {
                nisSection.style.display = 'none';
                nisInput.removeAttribute('required');
                nisInput.value = '';
            } else if (role === 'siswa') {
                nisSection.style.display = 'block';
                nisLabel.textContent = 'NIS User:';
                nisInput.setAttribute('required', 'required');
            } else if (role === 'guru' || role === 'penjual') {
                nisSection.style.display = 'block';
                nisLabel.textContent = 'NPWP (Optional):';
                nisInput.removeAttribute('required');
                nisInput.value = '';
            }
        });
    </script>
</body>
</html>

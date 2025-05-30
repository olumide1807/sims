<?php
include 'config/config.php';
session_start();

if (isset($_SESSION['email']) && isset($_SESSION['password'])) {
    if ($_SESSION['role'] == 'manager') {
        header("Location: admin/dashboard/");
        exit;
    } elseif ($_SESSION['role'] == 'sales_rep') {
        header("sales_rep/index.php");
        exit;
    }
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sel_qry = "SELECT * FROM `users` WHERE `email` = ?";
    $stmt = mysqli_prepare($connect, $sel_qry);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if (!$user['password']) {
        die("No user found with that email");
    }

    $verify_result = password_verify($password, $user['password']);

    if ($verify_result) {
        $_SESSION = $user;
        if ($_SESSION['role'] == 'manager') {
            header("Location: admin/dashboard/");
            exit;
        } elseif ($_SESSION['role'] == 'sales_rep') {
            // die('okay');
            header("Location: sales_rep/index.php");
            exit;
        }
        
    } else {
        // echo "invalid Login!";
        $login_error = "Invalid Username or Password";
        // die();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Smart Inventory Management System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/sims/style/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="style/css/style.css">
    <style>
        a {
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col">
                <h2 class="text-center">Welcome back!</h2>
                <p class="text-center">Enter your email and password.</p>
                <br><br>
                <form method="post">
                    <?php if (isset($login_error)) { ?>
                        <div class="alert alert-danger border border-danger text-danger text-center px-4 py-3 rounded mb-4" role="alert">
                            <?php echo htmlspecialchars($login_error); ?>
                        </div>
                    <?php } ?>
                    <input type="email" name="email" id="" placeholder="Username" style="border-radius: 5px; width: 100%;">
                    <br /><br />
                    <input type="password" name="password" id="" placeholder="Password" style="border-radius: 5px; width: 100%;">
                    <br>
                    <small><input type="checkbox" checked> Remember me</small>
                    <small style="float: right;"><a href="" style="text-decoration: none;">Forgot password?</a></small>
                    <br><br>
                    <input name="login" type="submit" value="Login" class="btn btn-outline-primary" style="border-radius: 5px; width: 100%;">
                </form>
                <p>Don't have an account? <a href="signup/">Sign up</a></p>
            </div>
            <div class="col">
                <img src="images/austin-distel-744oGeqpxPQ-unsplash.jpg" alt="" srcset="" class="img-fluid rounded">
            </div>
        </div>
    </div>

    <script src="/sims/style/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>
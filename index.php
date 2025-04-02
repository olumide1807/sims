<?php
    include 'config/config.php';
    session_start();
    
    if (isset($_SESSION['email']) && isset($_SESSION['password'])) {
        header("Location: dashboard/");
        exit;
    }

    if (isset($_POST['login'])){
        $email = $_POST['email'];
        $password = $_POST['password'];

        $sel_qry = "SELECT * FROM `users` WHERE email = '$email' AND `password` = '$password'";
        $qry_result = mysqli_query($connect, $sel_qry);
        
        /* $rows = mysqli_fetch_assoc($qry_result);
        echo "<pre>";
        print_r($rows);
        echo "</pre>";
        die(); */
        
        if (mysqli_num_rows($qry_result) == 1){
            $rows = mysqli_fetch_assoc($qry_result);

            /* echo "<pre>";
            print_r($rows['role']);
            echo "</pre>";
            die(); */
            
            $_SESSION = $rows;
            header ("Location: dashboard/");
            exit();
        }else {
            $login_error = "Invalid Username or Password";
            // echo "<script> alert('Username or password incorrect'); </script>";
            // die('Invalid login...');
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
            a{
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
                        <?php if (isset($login_error)){ ?>
                            <div class="alert alert-danger border border-danger text-danger text-center px-4 py-3 rounded mb-4" role="alert">
                                <?php echo htmlspecialchars($login_error); ?>   
                            </div>
                        <?php } ?>
                        <input type="email" name="email" id="" placeholder="Username" style="border-radius: 5px; width: 100%;">
                        <br/><br/>
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
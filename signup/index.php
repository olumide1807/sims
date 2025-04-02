<?php
    include "../config/config.php";
    include "../phpmail.php";

    if (isset($_POST['signup'])) {
        // die('okay');
        $firstname  = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
        $lastname   = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
        $email      = isset($_POST['email']) ? trim($_POST['email']) : '';
        $address    = isset($_POST['home_address']) ? trim($_POST['home_address']) : '';
        $phone      = isset($_POST['phone']) ? trim($_POST['phone']) : '';

        if (empty($firstname) and empty($lastname) and empty($email) and empty($address) and empty($phone)) {
            die ('okay');
        } else {
            // phpinfo();
            // die();
            function generateStrongPassword($length = 8) {
                $upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $lower = "abcdefghijklmnopqrstuvwxyz";
                $numbers = "0123456789";
                $symbols = "!@#$%^&*()-_=+";
                
                $all = $upper . $lower . $numbers . $symbols;
                $password = $upper[rand(0, strlen($upper) - 1)] . 
                            $lower[rand(0, strlen($lower) - 1)] . 
                            $numbers[rand(0, strlen($numbers) - 1)] . 
                            $symbols[rand(0, strlen($symbols) - 1)];

                for ($i = 4; $i < $length; $i++) {
                    $password .= $all[rand(0, strlen($all) - 1)];
                }

                return str_shuffle($password);
            }

            $password = generateStrongPassword(8);

            $sql = "INSERT INTO users (`user_id`, `firstname`, `lastname`, `email`, `home_address`, `phone`, `password`, `date_registered`) 
                VALUES ('', '$firstname', '$lastname', '$email', '$address', '$phone', '$password', NOW())";
            // die ($sql);
            // Execute query
            if (mysqli_query($connect, $sql)) {
                $recipient = $email;
                $subject = "Login Details";
                $body = "You have successfully created an account. Kindly login with your mail and the password specified below\n
                        Password: {$password}\n
                        You are adviced to change your password after your first login,\n
                        Thank you.";
                $headers = "From: olumide@gmail.com\r\n";

                // send email
                $result = sendEmail($recipient,$subject,$body,$headers);
                echo $result;
                echo "<script> alert('An Email has been sent to you. Kindly check for your Login Details');
                                window.location.href = '../';
                        </script>";
                // die();
            } else {
                echo "Error: " . mysqli_error($connect);
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@24.7.0/build/css/intlTelInput.css">
    <style>
        body {
            background-color: #f5f5f5;
        }

        .card {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
        }

        .card-header {
            /* background-color: #007bff; */
            color: #000;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            text-align: center;
        }

        .form-control {
            border-radius: 6px;
            border-color: #ced4da;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 6px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="card w-100 max-w-md">
            <div class="card-header">
                <h2 class="mb-0">Sign Up</h2>
            </div>
            <div class="card-body">
                <form id="signupForm" method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="firstname">First Name</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lastname">Last Name</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="home_address">Home Address</label>
                        <input type="text" class="form-control" id="home_address" name="home_address" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label><br />
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    <!-- <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">Select a role</option>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div> 
                    <div class="form-group">
                        <label for="date_registered">Date Registered</label>
                        <input type="date" class="form-control" id="date_registered" name="date_registered" required>
                    </div>-->
                    <br />
                    <button type="submit" class="btn btn-primary w-100" name="signup">Sign Up</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24.7.0/build/js/intlTelInput.min.js"></script>
    <script>
        // const phoneInput = document.querySelector("#phone");

        const input = document.querySelector("#phone");
        /* window.intlTelInput(input, {
            loadUtilsOnInit: "https://cdn.jsdelivr.net/npm/intl-tel-input@24.7.0/build/js/utils.js",
        }); */

        intlTelInput(input, {
            initialCountry: "auto",
            geoIpLookup: function(success, failure) {
                fetch("https://ipapi.co/json")
                    .then(function(res) {
                        return res.json();
                    })
                    .then(function(data) {
                        success(data.country_code);
                    })
                    .catch(function() {
                        failure();
                    });
            }
        });
        // Initialize intl-tel-input
        /* const iti = window.intlTelInput(phoneInput, {
            initialCountry: "auto",
            geoIpLookup: function(callback) {
                // Use IPinfo API to detect location
                fetch("https://ipinfo.io/json?token=your_token") // Replace 'your_token' with your actual token
                    .then(response => response.json())
                    .then(data => {
                        callback(data.country); // Pass the country code (e.g., "US", "GH")
                    })
                    .catch(() => {
                        callback("us"); // Default to US if geolocation fails
                    });
            },
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js", // Formatting utils
        }); */
    </script>

</body>

</html>
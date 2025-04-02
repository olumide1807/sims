<?php
$dbhost = "localhost";
$username = "root";       
$password = "";           
$dbname = "sims";

$connect = mysqli_connect($dbhost, $username, $password, $dbname);

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
<?php
    if (!isset($_SESSION['username']) && !isset($_SESSION['password'])) {
        header("Location: ../");
    }
?>
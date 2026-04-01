<?php
session_start();
$_SESSION['db'] = $_GET['name'];
header('location: ../public/dashboard.php');
?>
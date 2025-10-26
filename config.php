<?php
session_start();

$host = "localhost";
$username = "root"; 
$password = "";
$database = "medical_store";
$port = 3306;

$conn = mysqli_connect($host, $username, $password, $database, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
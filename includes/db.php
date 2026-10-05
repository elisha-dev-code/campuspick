<?php
$servername = "localhost";
$name = "root";
$password = "";
$db = "campuspick";

$conn = new mysqli("localhost", "root", "", "campuspick");

if ($conn -> connect_error) {
    die ("Connnection failed: ". $conn->connect_error);
}

$conn-> set_charset("utf8mb4");


?>
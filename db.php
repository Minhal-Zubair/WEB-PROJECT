<?php
/* DATABASE CONNECTION */

$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "shopping_db";

/* CREATE CONNECTION */
$conn = new mysqli($servername, $username, $password, $database);

/* CHECK CONNECTION */ 
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* START SESSION */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
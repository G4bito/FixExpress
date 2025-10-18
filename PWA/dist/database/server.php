<?php
$server_data = "localhost";
$username = "root";
$password = "";
$dbname = "fixexpress"; 

$conn = new mysqli($server_data, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>

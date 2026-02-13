<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ais";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
   die("Connection failed: " . $conn->connect_error);
}


$sql = "SELECT id, menu FROM menubar WHERE id = 0";

$query = $conn->query($sql);

$menubar = mysqli_fetch_assoc($query);
?>
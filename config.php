<?php
$servername = "localhost";
$username = "artist_bci_users";
$password = "1TcqitE%fRi9";
$dbname = "artist_bci";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
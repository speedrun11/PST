<?php
$dbuser = "root";
$dbpass = "";
$host = "localhost";
$db = "rposystem";

// Create connection
$mysqli = new mysqli($host, $dbuser, $dbpass, $db);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>
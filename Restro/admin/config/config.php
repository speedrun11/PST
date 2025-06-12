<?php
$dbuser = "root";
$dbpass = "";
$host = "localhost";
$db = "pst";

$mysqli = new mysqli($host, $dbuser, $dbpass, $db);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>
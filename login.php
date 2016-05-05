<?php
$username = $_POST['username'];
$userpass = $_POST['password'];
$sessnsem = intval($_POST['asession'])*10+intval($_POST['semester']);
session_start();
$_SESSION['username'] = $username;
$_SESSION['userpass'] = $userpass;
$_SESSION['sessnsem'] = $sessnsem;
header('Location: check.php');
?>

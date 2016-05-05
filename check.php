<?php
try {
	session_start();
	if (!isset($_SESSION['username'])||!isset($_SESSION['userpass'])) {
	} else {
		require_once dirname(__FILE__).'/UniData.php';
		$data = new UniData($_SESSION['sessnsem']);
		$pass = $data->validate($_SESSION['username'],$_SESSION['userpass']);
		if ($pass===false) throw new Exception();
		header('Content-Type: text/html; charset=utf-8');
		echo "[CHECK] Validate: ".json_encode($pass).PHP_EOL;
	}
} catch (Exception $error) {
	session_destroy();
	header('Location: error.html');
}
exit();
?>

<?php
require_once dirname(__FILE__).'/config.php';
try {
	session_start();
	if (!isset($_SESSION['username'])||!isset($_SESSION['userpass'])||
			!isset($_SESSION['usertype'])) {
		header('Location: login.php');
		exit();
	}
	$type = "UniData";
	if (intval($_SESSION['usertype'])==MY1STAFF_LOGIN) {
		$type = $type."Staff";
	}
	require_once dirname(__FILE__).'/'.$type.'.php';
	$data = new $type();
	$pass = $data->validateUser($_SESSION['username'],$_SESSION['userpass']);
	if ($pass===false)
		throw new Exception('Invalid login!');
	$user = $data->getProfile();
	$staf = $data->listStaff();
	if ($staf['stat']==false) {
		throw new Exception('Something is WRONG!');
	}
	// header for CSV download
	header("Content-type: text/csv; charset=utf-8");
	header("Content-Disposition: attachment; filename=stafflist.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo HEADER_STAFF_UNID.",".HEADER_STAFF_NRIC.",".
		HEADER_STAFF_NAME.",FLAG\n";
	foreach ($staf['list'] as $item) {
		echo implode(',',$item);
	}
} catch (Exception $error) {
	session_destroy();
	if (DEBUG_MODE) {
		$message = $error->getMessage();
	} else {
		$message = "General Error!";
	}
	header('Content-Type: text/html; charset=utf-8');
	echo "<h1>Error</h1>".PHP_EOL.$message;
}
exit();
?>

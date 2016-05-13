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
	// check upload error... from php.net
	if (!isset($_FILES['dataFile']['error']) ||
			is_array($_FILES['dataFile']['error'])) {
		throw new RuntimeException('Invalid parameters!');
	}
	switch ($_FILES['dataFile']['error']) {
		case UPLOAD_ERR_OK:
			break;
		case UPLOAD_ERR_NO_FILE:
			throw new RuntimeException('No file sent.');
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			throw new RuntimeException('Exceeded filesize limit.');
		default:
			throw new RuntimeException('Unknown errors.');
	}
	// enforce these!
	if ($_FILES["dataFile"]["size"] == 0) {
		throw new RuntimeException('Empty file?!');
	}
	if (empty($_POST["staffId"])) {
		throw new RuntimeException('No Staff ID!');
	}
	if ($_POST["staffId"]!=$user['unid']) {
		throw new RuntimeException('Invalid ID!');
	}
	if ($_FILES["dataFile"]["size"] > 500000) {
		throw new RuntimeException('File too large!');
	}
	// simply load temporary file
	$filename = $_FILES["dataFile"]["tmp_name"];
	require_once dirname(__FILE__).'/FileText.php';
	$file = new FileText();
	$csvd = $file->loadCSV($filename);
	if ($csvd['error']===true) {
		throw new RuntimeException('Cannot load data!');
	} else if ($csvd['rows']==0) {
		throw new RuntimeException('No data found!');
	}
	// what's the command?
	switch ($_POST["aCommand"])
	{
		case TASK_STAFF_CREATE_STAFF:
			if ($csvd['cols']!=3||$csvd['headline'][0]!=HEADER_STAFF_UNID||
					$csvd['headline'][1]!=HEADER_STAFF_NRIC||
					$csvd['headline'][2]!=HEADER_STAFF_NAME)
			{
				throw new RuntimeException('Invalid format?!');
			}
			$data->checkStaff();
			foreach ($csvd['dataline'] as $line) {
				// fixed index
				$unid = strtoupper(trim($line[0]));
				$nric = strtoupper(trim($line[1]));
				$name = strtoupper(trim($line[2]));
				$staf = $data->findStaff($unid);
				if ($staf['stat']==false) {
					$data->createStaff($unid,$name,$nrid);
					$staf = $data->findStaff($unid);
					if ($staf['stat']==false) {
						throw new RuntimeException('Something is WRONG!');
					}
				}
			}
			break;
		case TASK_STAFF_VIEW_STAFF:
			$staf = $data->listStaff();
			if ($staf['stat']==false) {
				throw new RuntimeException('Something is WRONG!');
			}
			break;
		default:
			throw new RuntimeException('Unknown error?!');
	}
} catch (RuntimeException $error) {
	session_destroy();
	if (DEBUG_MODE) {
		$message = $error->getMessage();
	} else {
		$message = "General Error!";
	}
	header('Content-Type: text/html; charset=utf-8');
	echo "<h1>Error</h1>".PHP_EOL.$message;
	exit();
}
?>

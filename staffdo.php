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
		throw new Exception('Invalid parameters!');
	}
	switch ($_FILES['dataFile']['error']) {
		case UPLOAD_ERR_OK:
			break;
		case UPLOAD_ERR_NO_FILE:
			throw new Exception('No file sent.');
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			throw new Exception('Exceeded filesize limit.');
		default:
			throw new Exception('Unknown errors.');
	}
	// enforce these!
	if ($_FILES["dataFile"]["size"] == 0) {
		throw new Exception('Empty file?!');
	}
	if (empty($_POST["staffId"])) {
		throw new Exception('No Staff ID!');
	}
	if ($_POST["staffId"]!=$user['unid']) {
		throw new Exception('Invalid ID!');
	}
	if ($_FILES["dataFile"]["size"] > 500000) {
		throw new Exception('File too large!');
	}
	// simply load temporary file
	$filename = $_FILES["dataFile"]["tmp_name"];
	require_once dirname(__FILE__).'/FileText.php';
	$file = new FileText();
	$csvd = $file->loadCSV($filename);
	if ($csvd['error']===true) {
		throw new Exception('Cannot load data!');
	} else if ($csvd['rows']==0) {
		throw new Exception('No data found!');
	}
	// what's the command?
	switch ($_POST["aCommand"])
	{
		case TASK_STAFF_CREATE_STAFF:
			if ($csvd['cols']!=3||
					strtoupper($csvd['headline'][0])!=HEADER_STAFF_UNID||
					strtoupper($csvd['headline'][1])!=HEADER_STAFF_NRIC||
					strtoupper($csvd['headline'][2])!=HEADER_STAFF_NAME)
			{
				throw new Exception('Invalid format?!');
			}
			$data->checkStaff();
			foreach ($csvd['dataline'] as $line) {
				// fixed index
				$unid = strtoupper(trim($line[0]));
				$nrid = strtoupper(trim($line[1]));
				$name = strtoupper(trim($line[2]));
				if (empty($unid)||empty($nrid)||empty($name)) {
					throw new Exception('Empty fields!');
				}
				$staf = $data->findStaff($unid);
				if ($staf['stat']==false) {
					$data->createStaff($unid,$name,$nrid);
					$staf = $data->findStaff($unid);
					if ($staf['stat']==false) {
						throw new Exception('Something is WRONG!');
					}
				} else {
					throw new Exception('Staff already in list!');
				}
			}
			break;
		case TASK_STAFF_CREATE_COURSE:
			if ($csvd['cols']!=3||
					strtoupper($csvd['headline'][0])!=HEADER_COURSE_CODE||
					strtoupper($csvd['headline'][1])!=HEADER_COURSE_NAME||
					strtoupper($csvd['headline'][2])!=HEADER_COURSE_UNIT)
			{
				throw new Exception('Invalid format?!');
			}
			$data->checkCourses();
			foreach ($csvd['dataline'] as $line) {
				// fixed index
				$code = strtoupper(trim($line[0]));
				$name = strtoupper(trim($line[1]));
				$unit = strtoupper(trim($line[2]));
				if (empty($code)||empty($name)||empty($unit)) {
					throw new Exception('Empty fields!');
				}
				$cors = $data->findCourse($code);
				if ($cors['stat']==false) {
					$data->createCourse($code,$name,$unit);
					$cors = $data->findCourse($code);
					if ($cors['stat']==false) {
						throw new Exception('Something is WRONG!');
					}
				} else {
					throw new Exception('Course already in list!');
				}
			}
			break;
		default:
			throw new Exception('Unknown error?!');
	}
	// create HTML
	require_once dirname(__FILE__).'/HTMLDocument.php';
	// create doc generator
	$dohtml = new HTMLDocument(MY1APP_TITLE);
	// create page title
	$dotemp = new HTMLObject('h1');
	$dotemp->insert_inner(MY1APP_TITLE);
	$dotemp->do_1skipline();
	$dohtml->append_2body($dotemp);
	// create message
	$dotemp = new HTMLObject('p');
	$dotemp->insert_inner('Done.');
	$dotemp->do_1skipline();
	$dohtml->append_2body($dotemp);
	// create command links
	$dotemp = new HTMLObject('p');
	$dotemp->insert_inner('<a href="javascript:history.back()">Back</a>');
	$dotemp->do_1skipline();
	$dohtml->append_2body($dotemp);
	// generate HTML
	echo $dohtml->write_html();
	exit();
} catch (Exception $error) {
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

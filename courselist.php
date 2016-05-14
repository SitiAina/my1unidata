<?php
require_once dirname(__FILE__).'/config.php';
try {
	session_start();
	if (!isset($_SESSION['username'])||!isset($_SESSION['userpass'])||
			!isset($_SESSION['usertype'])) {
		header('Location: login.php');
		exit();
	}
	if (intval($_SESSION['usertype'])!=MY1STAFF_LOGIN) {
		throw new Exception('Invalid permission!');
	}
	require_once dirname(__FILE__).'/UniDataStaff.php';
	$data = new UniDataStaff();
	$pass = $data->validateUser($_SESSION['username'],$_SESSION['userpass']);
	if ($pass===false)
		throw new Exception('Invalid login!');
	$user = $data->getProfile();
	$cors = $data->listCourse();
	if ($cors['stat']==false) {
		throw new Exception('Something is WRONG!');
	}
	// create HTML
	require_once dirname(__FILE__).'/HTMLDocument.php';
	// create doc generator
	$dohtml = new HTMLDocument(MY1APP_TITLE);
	// create page title
	$dotemp = new HTMLObject('h1');
	$dotemp->insert_inner(MY1APP_TITLE." - Course List");
	$dotemp->insert_linebr(2);
	$dotemp->do_1skipline();
	$dohtml->append_2body($dotemp);
	$cksize = count($cors['list']);
	if ($cksize>0) {
		// create table
		$do_tab = new HTMLObject('table');
		$do_tab->insert_keyvalue('border','2',true);
		$do_tab->insert_keyvalue('width','100%',true);
		$do_tab->insert_keyvalue('cellpadding','10',true);
		$do_tab->insert_linebr(2);
		$do_tab->do_multiline();
		$dohtml->append_2body($do_tab);
		$do_row = new HTMLObject('tr');
		$do_row->do_1skipline();
		$do_tab->append_object($do_row);
		$do_col = new HTMLObject('th');
		$do_col->insert_inner('<b>'.HEADER_COURSE_CODE.'</b>');
		$do_row->append_object($do_col);
		$do_col = new HTMLObject('th');
		$do_col->insert_inner('<b>'.HEADER_COURSE_NAME.'</b>');
		$do_row->append_object($do_col);
		$do_col = new HTMLObject('th');
		$do_col->insert_inner('<b>'.HEADER_COURSE_UNIT.'</b>');
		$do_row->append_object($do_col);
		foreach ($cors['list'] as $item) {
			$do_row = new HTMLObject('tr');
			$do_row->do_1skipline();
			$do_tab->append_object($do_row);
			$do_col = new HTMLObject('td');
			$do_col->insert_inner($item['code']);
			$do_row->append_object($do_col);
			$do_col = new HTMLObject('td');
			$do_col->insert_inner($item['name']);
			$do_row->append_object($do_col);
			$do_col = new HTMLObject('td');
			$do_col->insert_inner($item['unit']);
			$do_row->append_object($do_col);
		}
	} else {
		// create message
		$dotemp = new HTMLObject('p');
		$dotemp->insert_inner('<b>No course info found in database.</b>');
		$dotemp->insert_linebr(2);
		$dotemp->do_1skipline();
		$dohtml->append_2body($dotemp);
	}
	// create command links
	$dotemp = new HTMLObject('p');
	$dotemp->insert_inner('<a href="javascript:history.back()">Back</a>');
	$dotemp->do_1skipline();
	$dohtml->append_2body($dotemp);
	// generate HTML
	echo $dohtml->write_html();
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

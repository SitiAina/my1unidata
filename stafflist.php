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
	// create HTML
	require_once dirname(__FILE__).'/HTMLDocument.php';
	// create doc generator
	$dohtml = new HTMLDocument(MY1APP_TITLE);
	// create page title
	$dotemp = new HTMLObject('h1');
	$dotemp->insert_inner(MY1APP_TITLE." - Staff List");
	$dotemp->insert_linebr(2);
	$dotemp->do_1skipline();
	$dohtml->append_2body($dotemp);
	$cksize = count($staf['list']);
	if ($cksize>1) {
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
		$do_col->insert_inner('<b>'.HEADER_STAFF_UNID.'</b>');
		$do_row->append_object($do_col);
		$do_col = new HTMLObject('th');
		$do_col->insert_inner('<b>'.HEADER_STAFF_NRIC.'</b>');
		$do_row->append_object($do_col);
		$do_col = new HTMLObject('th');
		$do_col->insert_inner('<b>'.HEADER_STAFF_NAME.'</b>');
		$do_row->append_object($do_col);
		$do_col = new HTMLObject('th');
		$do_col->insert_inner('<b>FLAG</b>');
		$do_row->append_object($do_col);
		foreach ($staf['list'] as $item) {
			if ($item['unid']=='0100000')
				continue;
			$do_row = new HTMLObject('tr');
			$do_row->do_1skipline();
			$do_tab->append_object($do_row);
			$do_col = new HTMLObject('td');
			$do_col->insert_inner($item['unid']);
			$do_row->append_object($do_col);
			$do_col = new HTMLObject('td');
			$do_col->insert_inner($item['nrid']);
			$do_row->append_object($do_col);
			$do_col = new HTMLObject('td');
			$do_col->insert_inner($item['name']);
			$do_row->append_object($do_col);
			$do_col = new HTMLObject('td');
			$do_col->insert_inner($item['flag']);
			$do_row->append_object($do_col);
		}
	} else {
		// create message
		$dotemp = new HTMLObject('p');
		$dotemp->insert_inner('<b>No staff profile found in database.</b>');
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

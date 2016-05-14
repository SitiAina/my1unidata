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
	// create HTML
	require_once dirname(__FILE__).'/HTMLDocument.php';
	// create doc generator
	$dohtml = new HTMLDocument(MY1APP_TITLE);
	// assign local styles
	//$dohtml->append_2head(get_style_local());
	// assign local scripts
	//$dohtml->append_2head(get_script_local());
	// assign external library scripts
	//$dohtml->append_2head(get_script_exlib());
	// assign main script
	//$dohtml->append_2body(get_script_main($server));
	// assign onload
	//$dohtml->insert_onload('main()');
	// create page title
	$dotemp = new HTMLObject('h1');
	$dotemp->insert_inner(MY1APP_TITLE);
	$dotemp->do_1skipline();
	$dohtml->append_2body($dotemp);
	// create hello message with a link
	$dotemp = new HTMLObject('p');
	$dotemp->insert_inner('Hello, '.$user['name'].
		'&nbsp;&nbsp;(<a href="logout.php">Logout</a>)');
	$dotemp->do_1skipline();
	$dohtml->append_2body($dotemp);
	if ($user['staf']===true) {
		// create form
		$doform = new HTMLObject('form');
		$doform->insert_id('form_login');
		$doform->insert_keyvalue('method','POST');
		$doform->insert_keyvalue('action','staffdo.php');
		$doform->insert_keyvalue('enctype','multipart/form-data');
		$doform->do_multiline();
		$dohtml->append_2body($doform);
		// create label staff id
		$dotemp = new HTMLObject('label');
		$dotemp->insert_inner('Staff ID');
		$dotemp->insert_linebr();
		$dotemp->do_1skipline();
		$doform->append_object($dotemp);
		// create input staff id
		$dotemp = new HTMLObject('input');
		$dotemp->insert_keyvalue('type','text');
		$dotemp->insert_keyvalue('name','staffID');
		$dotemp->insert_keyvalue('id','staffid');
		$dotemp->insert_keyvalue('placeholder',$user['unid']);
		$dotemp->insert_constant('disabled');
		$dotemp->remove_tail();
		$dotemp->insert_linebr(2);
		$dotemp->do_1skipline();
		$doform->append_object($dotemp);
		// create label data file
		$dotemp = new HTMLObject('label');
		$dotemp->insert_inner('Select Data File (CSV)');
		$dotemp->insert_linebr();
		$dotemp->do_1skipline();
		$doform->append_object($dotemp);
		// create input data file
		$dotemp = new HTMLObject('input');
		$dotemp->insert_keyvalue('type','file');
		$dotemp->insert_keyvalue('name','dataFile');
		$dotemp->insert_keyvalue('id','datafile');
		$dotemp->remove_tail();
		$dotemp->insert_linebr(2);
		$dotemp->do_1skipline();
		$doform->append_object($dotemp);
		// create label select command
		$dotemp = new HTMLObject('label');
		$dotemp->insert_inner('Select Command');
		$dotemp->insert_linebr();
		$dotemp->do_1skipline();
		$doform->append_object($dotemp);
		// create select select command
		$dotemp = new HTMLObject('select');
		$dotemp->insert_keyvalue('name','aCommand');
		$dotemp->insert_keyvalue('id','acommand');
		$dotemp->insert_linebr(2);
		$dotemp->do_multiline();
		$doform->append_object($dotemp);
		// create options for select
		$doopts = new HTMLObject('option');
		$doopts->insert_keyvalue('value',TASK_STAFF_CREATE_STAFF,true);
		$doopts->insert_inner('Create Staff');
		$doopts->do_1skipline();
		$dotemp->append_object($doopts);
		$doopts = new HTMLObject('option');
		$doopts->insert_keyvalue('value',TASK_STAFF_CREATE_COURSE,true);
		$doopts->insert_inner('Create Course');
		$doopts->do_1skipline();
		$dotemp->append_object($doopts);
		// create submit button
		$dotemp = new HTMLObject('input');
		$dotemp->insert_keyvalue('type','submit');
		$dotemp->insert_keyvalue('value','Process Command');
		$dotemp->insert_keyvalue('name','submit');
		$dotemp->remove_tail();
		$dotemp->insert_linebr(2);
		$dotemp->do_1skipline();
		$doform->append_object($dotemp);
	} else {
		// create label select asession
		$dotemp = new HTMLObject('label');
		$dotemp->insert_inner('Academic Session');
		$dotemp->insert_linebr();
		$dotemp->do_1skipline();
		$dohtml->append_object($dotemp);
		// create select select asession
		$dotemp = new HTMLObject('select');
		$dotemp->insert_keyvalue('id','sesspick');
		$dotemp->insert_keyvalue('name','asession');
		$dotemp->insert_linebr(2);
		$dotemp->do_1skipline();
		$dotemp->do_multiline();
		$dohtml->append_object($dotemp);
		// create options for select
		$doopts = new HTMLObject('option');
		$doopts->insert_keyvalue('value','20152016',true);
		$doopts->insert_inner('Academic Session 2015/2016');
		$doopts->do_1skipline();
		$dotemp->append_object($doopts);
		$doopts = new HTMLObject('option');
		$doopts->insert_keyvalue('value','20142015',true);
		$doopts->insert_inner('Academic Session 2014/2015');
		$doopts->do_1skipline();
		$dotemp->append_object($doopts);
		// create label select semester
		$dotemp = new HTMLObject('label');
		$dotemp->insert_inner('Semester');
		$dotemp->insert_linebr();
		$dotemp->do_1skipline();
		$dohtml->append_object($dotemp);
		// create select select semester
		$dotemp = new HTMLObject('select');
		$dotemp->insert_keyvalue('id','sem_pick');
		$dotemp->insert_keyvalue('name','semester');
		$dotemp->insert_linebr(2);
		$dotemp->do_1skipline();
		$dotemp->do_multiline();
		$dohtml->append_object($dotemp);
		// create options for select
		$doopts = new HTMLObject('option');
		$doopts->insert_keyvalue('value','1',true);
		$doopts->insert_inner('Semester 1');
		$doopts->do_1skipline();
		$dotemp->append_object($doopts);
		$doopts = new HTMLObject('option');
		$doopts->insert_keyvalue('value','2',true);
		$doopts->insert_inner('Semester 2');
		$doopts->do_1skipline();
		$dotemp->append_object($doopts);
	}
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

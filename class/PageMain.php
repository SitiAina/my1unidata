<?php
require_once dirname(__FILE__).'/PageBase.php';
class PageMain extends PageBase {
	function __construct() {
		parent::__construct();
	}
	function js_main() {
		$js_main = <<< JSMAIN
function mod_check() {
	var chk_form = document.getElementById('form_command');
	chk_form.userId.value = chk_form.userId.placeholder;
	chk_form.userId.disabled = false;
	return true;
}
JSMAIN;
		return $js_main;
	}
	function build_page() {
		$user = $this->_dodata->getProfile();
		// also use parent build
		parent::build_page();
		// create hello message with a link
		$temp = new HTMLObject('p');
		$temp->insert_inner('Hello, '.$user['name'].
			'&nbsp;&nbsp;[<a href="work.php?do=dopass">Change Password</a>]'.
			'&nbsp;&nbsp;[<a href="logout.php">Logout</a>]');
		$temp->do_1skipline();
		$this->append_2body($temp);
		// create form
		$form = new HTMLObject('form');
		$form->insert_id('form_command');
		$form->insert_keyvalue('method','POST');
		$form->insert_keyvalue('action','work.php?do=command');
		$form->insert_keyvalue('enctype','multipart/form-data');
		$form->insert_keyvalue('onsubmit','javascript:return mod_check();');
		$form->do_multiline();
		$this->append_2body($form);
		// create label user id
		$temp = new HTMLObject('label');
		$temp->insert_inner('User ID');
		$temp->insert_linebr();
		$temp->do_1skipline();
		$form->append_object($temp);
		// create input user id
		$temp = new HTMLObject('input');
		$temp->insert_keyvalue('type','text');
		$temp->insert_keyvalue('name','userId');
		$temp->insert_keyvalue('id','userid');
		$temp->insert_keyvalue('placeholder',$user['unid']);
		$temp->insert_constant('disabled');
		$temp->remove_tail();
		$temp->insert_linebr(2);
		$temp->do_1skipline();
		$form->append_object($temp);
		if ($user['staf']===true) {
			// create options for select
			if ($user['alvl']>0) {
				// create label data file
				$temp = new HTMLObject('label');
				$temp->insert_inner('Select Data File (CSV)');
				$temp->insert_linebr();
				$temp->do_1skipline();
				$form->append_object($temp);
				// create input data file
				$temp = new HTMLObject('input');
				$temp->insert_keyvalue('type','file');
				$temp->insert_keyvalue('name','dataFile');
				$temp->insert_keyvalue('id','datafile');
				$temp->remove_tail();
				$temp->insert_linebr(2);
				$temp->do_1skipline();
				$form->append_object($temp);
			}
		}
		// create label select asession
		$temp = new HTMLObject('label');
		$temp->insert_inner('Academic Session');
		$temp->insert_linebr();
		$temp->do_1skipline();
		$form->append_object($temp);
		// create select select asession
		$temp = new HTMLObject('select');
		$temp->insert_keyvalue('id','sesspick');
		$temp->insert_keyvalue('name','asession');
		$temp->insert_linebr(2);
		$temp->do_1skipline();
		$temp->do_multiline();
		$form->append_object($temp);
		// create options for select
		$opts = new HTMLObject('option');
		$opts->insert_keyvalue('value','20152016',true);
		$opts->insert_inner('Academic Session 2015/2016');
		$opts->do_1skipline();
		$temp->append_object($opts);
		$opts = new HTMLObject('option');
		$opts->insert_keyvalue('value','20142015',true);
		$opts->insert_inner('Academic Session 2014/2015');
		$opts->do_1skipline();
		$temp->append_object($opts);
		// create label select semester
		$temp = new HTMLObject('label');
		$temp->insert_inner('Semester');
		$temp->insert_linebr();
		$temp->do_1skipline();
		$form->append_object($temp);
		// create select select semester
		$temp = new HTMLObject('select');
		$temp->insert_keyvalue('id','sem_pick');
		$temp->insert_keyvalue('name','semester');
		$temp->insert_linebr(2);
		$temp->do_1skipline();
		$temp->do_multiline();
		$form->append_object($temp);
		// create options for select
		$opts = new HTMLObject('option');
		$opts->insert_keyvalue('value','1',true);
		$opts->insert_inner('Semester 1');
		$opts->do_1skipline();
		$temp->append_object($opts);
		$opts = new HTMLObject('option');
		$opts->insert_keyvalue('value','2',true);
		$opts->insert_inner('Semester 2');
		$opts->do_1skipline();
		$temp->append_object($opts);
		$opts = new HTMLObject('option');
		$opts->insert_keyvalue('value','3',true);
		$opts->insert_inner('Semester 3');
		$opts->do_1skipline();
		$temp->append_object($opts);
		// create label select command
		$temp = new HTMLObject('label');
		$temp->insert_inner('Select Command');
		$temp->insert_linebr();
		$temp->do_1skipline();
		$form->append_object($temp);
		// create select select command
		$temp = new HTMLObject('select');
		$temp->insert_keyvalue('name','aCommand');
		$temp->insert_keyvalue('id','acommand');
		$temp->do_multiline();
		$form->append_object($temp);
		if ($user['staf']===true) {
			// create options for select
			if ($user['alvl']>0) {
				$opts = new HTMLObject('option');
				$opts->insert_keyvalue('value',TASK_STAFF_CREATE_STAFF,true);
				$opts->insert_inner('Create Staff ');
				$opts->do_1skipline();
				$temp->append_object($opts);
				$opts = new HTMLObject('option');
				$opts->insert_keyvalue('value',TASK_STAFF_CREATE_COURSE,true);
				$opts->insert_inner('Create Course ');
				$opts->do_1skipline();
				$temp->append_object($opts);
			}
			$opts = new HTMLObject('option');
			$opts->insert_keyvalue('value',TASK_STAFF_EXECUTE_COURSE,true);
			$opts->insert_inner('Set Course Evaluation ');
			$opts->do_1skipline();
			$temp->append_object($opts);
			// create submit button
			$temp = new HTMLObject('input');
			$temp->insert_keyvalue('type','submit');
			$temp->insert_keyvalue('value','Process Command');
			$temp->insert_keyvalue('name','submit');
			$temp->remove_tail();
			$temp->insert_linebr(2);
			$temp->do_1skipline();
			$form->append_object($temp);
			// extra command links
			if ($user['alvl']>0) {
				// create command links
				$temp = new HTMLObject('p');
				$temp->insert_inner('<a href="work.php?do=viewstaff">'.
					'View Staff List</a>');
				$temp->do_1skipline();
				$this->append_2body($temp);
				// create command links
				$temp = new HTMLObject('p');
				$temp->insert_inner('<a href="work.php?do=viewcourse">'.
					'View Course List</a>');
				$temp->do_1skipline();
				$this->append_2body($temp);
			}
		} else {
		}
	}
}
?>

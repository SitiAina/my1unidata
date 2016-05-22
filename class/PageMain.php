<?php
require_once dirname(__FILE__).'/PageBase.php';
class PageMain extends PageBase {
	function __construct() {
		parent::__construct();
	}
	function js_main() {
		$js_main = <<< JSMAIN
function post_check() {
	var chk_form = document.getElementById('form_command');
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
		$this->insert_para('Hello, '.$user['name']."&nbsp;&nbsp;\n".
			"[<a href=\"work.php?do=dopass\">Change Password</a>]".
			"&nbsp;&nbsp;\n[<a href=\"logout.php\">Logout</a>]");
		//$this->insert_para("[DEBUG] ".json_encode($user));
		if ($user['staf']===true) {
			$this->_dodata->checkCoursesStaffs();
			$list = $this->_dodata->listCourseStaff($user['unid']);
			if ($list['stat']!=true) {
				$this->throw_debug('Cannot get course implementation list!');
			}
			foreach ($list['list'] as $item) {
				$this->insert_link("work.php?do=nothing",
					$item['ssem']." - ".$item['course']);
			}
		}
		// create form
		$form = new HTMLForm('form_command','work.php');
		$form->insert_keyvalue('enctype','multipart/form-data',
			'javascript:return post_check();');
		$this->append_2body($form);
		$form->create_input_hidden('userId',$user['unid'],true);
		if ($user['staf']===true) {
			// create options for select
			$form->create_input('Select Data File (CSV)','file','dataFile');
		}
		// selection for semester
		$opts = [
			[ 'Academic Session 2015/2016 Semester 1', '201520161', true ],
			[ 'Academic Session 2014/2015 Semester 2', '201420152', true ],
			[ 'Academic Session 2014/2015 Semester 1', '201420151', true ]
		];
		$temp = $form->create_select('Academic Session / Semester',
			'pickSem','picksem',$opts);
		// selection for course
		$opts = [];
		$cors = $this->_dodata->listCourse();
		foreach ($cors['list'] as $item) {
			array_push($opts,[$item['code']." - ".$item['name'],
				$item['code'],false]);
		}
		$temp = $form->create_select('Select Course',
			'pickCourse','pickcourse',$opts);
		// selection for command
		if ($user['staf']===true) {
			$opts = [
				[ 'Implement a Course', TASK_STAFF_EXECUTE_COURSE,true ]
			];
			if ($user['alvl']>0) {
				array_unshift($ops,['Create Staff',
					TASK_STAFF_CREATE_STAFF,true]);
				array_unshift($ops,['Create Course',
					TASK_STAFF_CREATE_COURSE,true]);
			}
			$temp = $form->create_select('Select Command',
				'aCommand','acommand',$opts);
			$temp->remove_linebr();
			$form->create_submit('Process Command','process');
			// extra command links
			if ($user['alvl']>0) {
				$this->insert_link("work.php?do=viewstaff",
					'View Staff List');
				$this->insert_link("work.php?do=viewcourse",
					'View Course List');
			}
		} else {
		}
	}
}
?>

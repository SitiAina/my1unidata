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
			if ($list['stat']===true) {
				$this->insert_para("<br><b>Course Implementations</b>");
				foreach ($list['list'] as $item) {
					$this->insert_link("work.php?do=impcourse&ssem=".
						$item['ssem']."&code=".$item['course'],
						$item['ssem']." - ".$item['course']." (".
						$item['coursename'].")");
				}
			}
		}
		$this->insert_para("<br><b>Course Administration</b>");
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
		$opts = array();
		date_default_timezone_set('UTC');
		$temp = explode(':',date("Y:m"));
		$year = intval(array_shift($temp));
		$mmon = intval(array_shift($temp)); // can be used to select default??
		for ($loop=0;$loop<2;$loop++) {
			$temp = 'Academic Session '.$year.'/'.($year+1).' Semester 2';
			array_push($opts,[$temp,$year.''.($year+1).'2',true]);
			$temp = 'Academic Session '.$year.'/'.($year+1).' Semester 1';
			array_push($opts,[$temp,$year.''.($year+1).'1',true]);
			$year--;
		}
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
				[ 'Implement a Course', TASK_STAFF_EXECUTE_COURSE,true ],
				[ 'Add Students', TASK_STAFF_ADD_STUDENTS,true ]
			];
			if ($user['alvl']>0) {
				array_unshift($opts,['Create Course',
					TASK_STAFF_CREATE_COURSE,true]);
				array_unshift($opts,['Create Staff',
					TASK_STAFF_CREATE_STAFF,true]);
			}
			$temp = $form->create_select('Select Command',
				'aCommand','acommand',$opts);
			$temp->remove_linebr();
			$form->create_submit('Process Command','process');
			// extra command links
			if ($user['alvl']>0) {
				$show = ' ['.HEADER_STAFF_UNID.','.
					HEADER_STAFF_NRIC.','.HEADER_STAFF_NAME.']';
				$this->insert_link("work.php?do=viewstaff",
					'View Staff List'.$show);
				$show = ' ['.HEADER_COURSE_CODE.','.
					HEADER_COURSE_NAME.','.HEADER_COURSE_UNIT.']';
				$this->insert_link("work.php?do=viewcourse",
					'View Course List'.$show);
			}
		} else {
		}
	}
}
?>

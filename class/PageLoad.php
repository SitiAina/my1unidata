<?php
require_once dirname(__FILE__).'/PageDone.php';
class PageLoad extends PageDone {
	function __construct() {
		parent::__construct();
		$user = $this->_dodata->getProfile();
		// check upload error... from php.net
		if (!isset($_FILES['dataFile']['error']) ||
				is_array($_FILES['dataFile']['error'])) {
			$this->throw_debug('Invalid parameters!');
		}
		switch ($_FILES['dataFile']['error']) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				$this->throw_debug('No file sent.');
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$this->throw_debug('Exceeded filesize limit.');
			default:
				$this->throw_debug('Unknown errors.');
		}
		// enforce these!
		if ($_FILES["dataFile"]["size"] == 0) {
			$this->throw_debug('Empty file?!');
		}
		if (empty($_POST["staffId"])) {
			$this->throw_debug('No Staff ID!');
		}
		if ($_POST["staffId"]!=$user['unid']) {
			$this->throw_debug('Invalid ID!');
		}
		if ($_FILES["dataFile"]["size"] > 500000) {
			$this->throw_debug('File too large!');
		}
		// simply load temporary file
		$filename = $_FILES["dataFile"]["tmp_name"];
		require_once dirname(__FILE__).'/FileText.php';
		$file = new FileText();
		$csvd = $file->loadCSV($filename);
		if ($csvd['stat']!==true) {
			$this->throw_debug('Cannot load data!');
		} else if ($csvd['rows']==0) {
			$this->throw_debug('No data found!');
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
					$this->throw_debug('Invalid format?!');
				}
				$this->_dodata->checkStaff();
				foreach ($csvd['dataline'] as $line) {
					if (empty($line[0])||empty($line[1])||empty($line[2])) {
						$this->throw_debug('Empty fields!');
					}
					$unid = strtoupper(trim($line[0]));
					$nrid = strtoupper(trim($line[1]));
					$name = strtoupper(trim($line[2]));
					$staf = $this->_dodata->findStaff($unid);
					if ($staf['stat']==false) {
						$this->_dodata->createStaff($unid,$name,$nrid);
						$staf = $this->_dodata->findStaff($unid);
						if ($staf['stat']==false) {
							$this->throw_debug('Something is WRONG!');
						}
					} else {
						$this->throw_debug('Staff already in list!');
					}
				}
				break;
			case TASK_STAFF_CREATE_COURSE:
				if ($csvd['cols']!=3||
						strtoupper($csvd['headline'][0])!=HEADER_COURSE_CODE||
						strtoupper($csvd['headline'][1])!=HEADER_COURSE_NAME||
						strtoupper($csvd['headline'][2])!=HEADER_COURSE_UNIT)
				{
					$this->throw_debug('Invalid format?!');
				}
				$this->_dodata->checkCourses();
				foreach ($csvd['dataline'] as $line) {
					if (empty($line[0])||empty($line[1])||empty($line[2])) {
						$this->throw_debug('Empty fields!');
					}
					$code = strtoupper(trim($line[0]));
					$name = strtoupper(trim($line[1]));
					$unit = strtoupper(trim($line[2]));
					$cors = $this->_dodata->findCourse($code);
					if ($cors['stat']==false) {
						$this->_dodata->createCourse($code,$name,$unit);
						$cors = $this->_dodata->findCourse($code);
						if ($cors['stat']==false) {
							$this->throw_debug('Something is WRONG!');
						}
					} else {
						$this->throw_debug('Course already in list!');
					}
				}
				break;
			case TASK_STAFF_EXECUTE_COURSE:
				if ($csvd['cols']!=5||
						strtoupper($csvd['headline'][0])!=HEADER_CCOMP_NAME||
						strtoupper($csvd['headline'][1])!=HEADER_CCOMP_RAW_||
						strtoupper($csvd['headline'][2])!=HEADER_CCOMP_PCT_||
						strtoupper($csvd['headline'][3])!=HEADER_CCOMP_GROUP||
						strtoupper($csvd['headline'][4])!=HEADER_CCOMP_SUBGRP)
				{
					$this->throw_debug('Invalid format?!');
				}
				if (!isset($_POST["pickSem"])||!isset($_POST["pickCourse"])) {
					$this->throw_debug('Incomplete post?!');
				}
				$staf = $this->_dodata->findStaff($_POST["staffId"]);
				$cors = $this->_dodata->findCourse($_POST["pickCourse"]);
				if ($cors['stat']==false) {
					$this->throw_debug('Course not found!');
				}
				$this->_dodata->selectSession($_POST["pickSem"]);
				// check if already implemented?
				$list = $this->_dodata->listCourseStaff(null,
					$_POST["pickCourse"]);
				if ($list['stat']==true) {
					$this->throw_debug('Already implemented?!');
				}
				$this->_dodata->createCourseStaff($cors['id'],$user['id']);
				$this->_dodata->checkCoursesComponents();
				foreach ($csvd['dataline'] as $line) {
					if (empty($line[0])||empty($line[1])||empty($line[2])
							||empty($line[3])||empty($line[4])) {
						$this->throw_debug('Empty fields!');
					}
					$name = strtoupper(trim($line[0]));
					$raw = floatval($line[1]);
					$pct = floatval($line[2]);
					$grp = intval($line[3]);
					$sub = intval($line[4]);
					$this->_dodata->createCourseComponent($cors['id'],
						$name,$raw,$pct,$grp,$sub);
				}
				break;
			case TASK_STAFF_ADD_STUDENTS:
				if ($csvd['cols']<4||$csvd['cols']>6||
						strtoupper($csvd['headline'][0])!=HEADER_STUDENT_UNID||
						strtoupper($csvd['headline'][1])!=HEADER_STUDENT_NAME||
						strtoupper($csvd['headline'][2])!=HEADER_STUDENT_NRIC||
						strtoupper($csvd['headline'][3])!=HEADER_STUDENT_PROG)
				{
					$this->throw_debug('Invalid format?!');
				}
				if (!isset($_POST["pickSem"])||!isset($_POST["pickCourse"])) {
					$this->throw_debug('Incomplete post?!');
				}
				$ssem = strtoupper($_POST["pickSem"]);
				$code = strtoupper($_POST["pickCourse"]);
				$cors = $this->_dodata->findCourse($code);
				$table = $code.'_'.$ssem;
				$this->_dodata->checkStudents();
				$this->_dodata->selectSession($_POST["pickSem"]);
				$this->_dodata->checkCourseStudent($table,$cors['id']);
				foreach ($csvd['dataline'] as $line) {
					if (empty($line[0])||empty($line[1])||
							empty($line[2])||empty($line[3])) {
						$this->throw_debug('Empty fields!');
					}
					$unid = strtoupper(trim($line[0]));
					$name = strtoupper(trim($line[1]));
					$nrid = strtoupper(trim($line[2]));
					$prog = strtoupper(trim($line[3]));
					$lgrp = ""; $mgrp = "";
					if ($csvd['cols']>4&&!empty($line[4])&&
							strtoupper($csvd['headline'][4])!=
							HEADER_STUDENT_LGRP) {
						$lgrp = strtoupper(trim($line[4]));
					}
					if ($csvd['cols']>5&&!empty($line[5])&&
							strtoupper($csvd['headline'][5])!=
							HEADER_STUDENT_MGRP) {
						$mgrp = strtoupper(trim($line[5]));
					}
					$stud = $this->_dodata->findStudent($unid);
					if ($stud['stat']==true) {
						if ($stud['name']!=$name||$stud['nrid']!=$nrid) {
							$this->throw_debug('Mistaken identity!');
						}
					} else {
						$this->_dodata->createStudent($unid,$name,$nrid,$prog);
						$stud = $this->_dodata->findStudent($unid);
						if ($stud['stat']==false) {
							$this->throw_debug('Something is WRONG!');
						}
					}
					$this->_dodata->createCourseStudent($table,
						$stud['id'],$lgrp,$mgrp);
				}
				break;
			default:
				$this->throw_debug('Unknown error?!');
		}
	}
	function build_page() {
		parent::build_page();
	}
}
?>

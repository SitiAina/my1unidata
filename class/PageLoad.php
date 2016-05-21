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
		if ($csvd['error']===true) {
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
					// fixed index
					$unid = strtoupper(trim($line[0]));
					$nrid = strtoupper(trim($line[1]));
					$name = strtoupper(trim($line[2]));
					if (empty($unid)||empty($nrid)||empty($name)) {
						$this->throw_debug('Empty fields!');
					}
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
					// fixed index
					$code = strtoupper(trim($line[0]));
					$name = strtoupper(trim($line[1]));
					$unit = strtoupper(trim($line[2]));
					if (empty($code)||empty($name)||empty($unit)) {
						$this->throw_debug('Empty fields!');
					}
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
			default:
				$this->throw_debug('Unknown error?!');
		}
	}
	function build_page() {
		parent::build_page();
	}
}
?>

<?php
require_once dirname(__FILE__).'/PageBase.php';
class PageCourse extends PageBase {
	function __construct() {
		parent::__construct('Course List');
	}
	function build_page() {
		$user = $this->_dodata->getProfile();
		$cors = $this->_dodata->listCourses();
		// also use parent build
		parent::build_page();
		$size = count($cors['list']);
		if ($size>0) {
			// create table
			$_tab = new HTMLObject('table');
			$_tab->insert_keyvalue('border','2',true);
			$_tab->insert_keyvalue('width','100%',true);
			$_tab->insert_keyvalue('cellpadding','10',true);
			$_tab->insert_linebr(2);
			$_tab->do_multiline();
			$this->append_2body($_tab);
			$_row = new HTMLObject('tr');
			$_row->do_1skipline();
			$_tab->append_object($_row);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>'.HEADER_COURSE_CODE.'</b>');
			$_row->append_object($_col);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>'.HEADER_COURSE_NAME.'</b>');
			$_row->append_object($_col);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>'.HEADER_COURSE_UNIT.'</b>');
			$_row->append_object($_col);
			foreach ($cors['list'] as $item) {
				$_row = new HTMLObject('tr');
				$_row->do_1skipline();
				$_tab->append_object($_row);
				$_col = new HTMLObject('td');
				$temp = $item['code'];
				if ($user['alvl']>0) {
					$temp = $temp.'&nbsp;&nbsp;[<a href="work.php?'.
					'do=editcourse&code='.$item['code'].'">Modify</a>]';
				}
				$_col->insert_inner($temp);
				$_row->append_object($_col);
				$_col = new HTMLObject('td');
				$_col->insert_inner($item['name']);
				$_row->append_object($_col);
				$_col = new HTMLObject('td');
				$_col->insert_inner($item['unit']);
				$_row->append_object($_col);
			}
		} else {
			// create message
			$temp = new HTMLObject('p');
			$temp->insert_inner('<b>No course info found in database.</b>');
			$temp->insert_linebr(2);
			$temp->do_1skipline();
			$this->append_2body($temp);
		}
		// create command links
		$temp = new HTMLObject('p');
		$temp->insert_inner('<a href="work.php?do=viewcourse&fmt=csv">'.
			'Download CSV</a>');
		$temp->do_1skipline();
		$this->append_2body($temp);
		$temp = new HTMLObject('p');
		$temp->insert_inner('<a href="javascript:history.back()">Back</a>');
		$temp->do_1skipline();
		$this->append_2body($temp);
	}
	function sendCSV() {
		$cors = $this->_dodata->listCourses();
		$head =  [ HEADER_COURSE_CODE,
			HEADER_COURSE_NAME, HEADER_COURSE_UNIT ];
		$data = [];
		foreach ($cors['list'] as $item) {
			array_push($data,[$item['code'],$item['name'],$item['unit']]);
		}
		require_once dirname(__FILE__).'/FileText.php';
		$fcsv = new FileText();
		$fcsv->sendCSV('listcourse.csv',$head,$data);
	}
}
?>

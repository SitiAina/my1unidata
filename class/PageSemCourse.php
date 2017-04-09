<?php
require_once dirname(__FILE__).'/PageBase.php';
class PageSemCourse extends PageBase {
	protected $_ssem;
	protected $_code;
	function __construct($ssem,$code) {
		$ssem = strtoupper(trim($ssem));
		$code = strtoupper(trim($code));
		parent::__construct($code."@".$ssem);
		$this->_ssem = $ssem;
		$this->_code = $code;
	}
	function build_page() {
		$user = $this->_dodata->getProfile();
		$core = $this->_dodata->findCourse($this->_code);
		$text = $this->_dodata->selectSession($this->_ssem);
		$cors = $this->_dodata->findCoursesComponents($core['id']);
		if ($cors['stat']==false) {
			$this->throw_debug('Cannot find components!');
		}
		// also use parent build
		parent::build_page();
		$this->insert_para("<b>".$this->_code." ".$core['name'].
			" (".$text.")</b>");
		if ($cors['count']>0) {
			// create table
			$_tab = new HTMLObject('table');
			$_tab->insert_keyvalue('border','2',true);
			$_tab->insert_keyvalue('width','100%',true);
			$_tab->insert_keyvalue('cellpadding','5',true);
			$_tab->do_multiline();
			$this->append_2body($_tab);
			$_row = new HTMLObject('tr');
			$_row->do_1skipline();
			$_tab->append_object($_row);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>'.HEADER_CCOMP_GROUP.'</b>');
			$_row->append_object($_col);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>'.HEADER_CCOMP_SUBGRP.'</b>');
			$_row->append_object($_col);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>'.HEADER_CCOMP_LABEL.'</b>');
			$_row->append_object($_col);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>'.HEADER_CCOMP_NAME.'</b>');
			$_row->append_object($_col);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>'.HEADER_CCOMP_RAW_.'</b>');
			$_row->append_object($_col);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>'.HEADER_CCOMP_PCT_.'</b>');
			$_row->append_object($_col);
			foreach ($cors['list'] as $item) {
				$_row = new HTMLObject('tr');
				$_row->do_1skipline();
				$_tab->append_object($_row);
				$_col = new HTMLObject('td');
				$_col->insert_inner($item['grp']);
				$_row->append_object($_col);
				$_col = new HTMLObject('td');
				$_col->insert_inner($item['sub']);
				$_row->append_object($_col);
				$temp = $item['lbl'];
				$temp = $temp.'&nbsp;&nbsp;[<a href="work.php?'.
				'do=editccmp&ssem='.$this->_ssem.'&code='.$this->_code.
				'&ccmp='.$item['id'].'">Modify</a>]';
				$_col = new HTMLObject('td');
				$_col->insert_inner($temp);
				$_row->append_object($_col);
				$_col = new HTMLObject('td');
				$_col->insert_inner($item['name']);
				$_row->append_object($_col);
				$_col = new HTMLObject('td');
				$_col->insert_inner($item['raw']);
				$_row->append_object($_col);
				$_col = new HTMLObject('td');
				$_col->insert_inner($item['pct']);
				$_row->append_object($_col);
			}
		} else {
			// create message
			$this->insert_para('<b>No course info found in database.</b>');
		}
		// create command links
		$this->insert_link('javascript:history.back()','Back');
		// checkif student db avaialable
		$this->_dodata->checkStudents();
		// list students as well
		$table = $this->_code.'_'.$this->_ssem;
		$this->_dodata->checkCourseStudent($table,$core['id']);
		$list = $this->_dodata->listCourseStudent($table);
		if ($list['stat']===true) {
			$this->insert_link('work.php?do=impcourse&fmt=csv'.
				'&ssem='.$this->_ssem.'&code='.$this->_code,
				'Download Student List (CSV)');
			$this->insert_para("<b>Student List</b>");
			// create table
			$_tab = new HTMLObject('table');
			$_tab->insert_keyvalue('border','2',true);
			$_tab->insert_keyvalue('width','100%',true);
			$_tab->insert_keyvalue('cellpadding','5',true);
			$_tab->do_multiline();
			$this->append_2body($_tab);
			$_row = new HTMLObject('tr');
			$_row->do_1skipline();
			$_tab->append_object($_row);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>#</b>');
			$_row->append_object($_col);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>'.HEADER_STUDENT_NAME.'</b>');
			$_row->append_object($_col);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>'.HEADER_STUDENT_UNID.'</b>');
			$_row->append_object($_col);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>'.HEADER_STUDENT_NRIC.'</b>');
			$_row->append_object($_col);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>'.HEADER_STUDENT_PROG.'</b>');
			$_row->append_object($_col);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>LGROUP</b>');
			$_row->append_object($_col);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>MGROUP</b>');
			$_row->append_object($_col);
			$step = 0;
			foreach ($list['list'] as $item) {
				$step++;
				$_row = new HTMLObject('tr');
				$_row->do_1skipline();
				$_tab->append_object($_row);
				$_col = new HTMLObject('td');
				$_col->insert_inner($step);
				$_row->append_object($_col);
				$_col = new HTMLObject('td');
				$_col->insert_inner($item['name']);
				$_row->append_object($_col);
				$_col = new HTMLObject('td');
				$_col->insert_inner($item['unid']);
				$_row->append_object($_col);
				$_col = new HTMLObject('td');
				$_col->insert_inner($item['nrid']);
				$_row->append_object($_col);
				$_col = new HTMLObject('td');
				$_col->insert_inner($item['prog']);
				$_row->append_object($_col);
				$_col = new HTMLObject('td');
				$_col->insert_inner($item['lgrp']);
				$_row->append_object($_col);
				$_col = new HTMLObject('td');
				$_col->insert_inner($item['mgrp']);
				$_row->append_object($_col);
			}
		}
	}
	function sendCSV() {
		$this->_dodata->selectSession($this->_ssem);
		$core = $this->_dodata->findCourse($this->_code);
		$table = $this->_code.'_'.$this->_ssem;
		$this->_dodata->checkCourseStudent($table,$core['id']);
		$list = $this->_dodata->listCourseStudent($table);
		if ($list['stat']==false) {
			$this->throw_debug('Something is WRONG!');
		}
		$head =  [ '#', HEADER_STUDENT_NAME,HEADER_STUDENT_UNID,
			HEADER_STUDENT_NRIC,HEADER_STUDENT_PROG,'LGROUP','MGROUP' ];
		$data = []; $step = 0;
		foreach ($list['list'] as $item) {
			$step++;
			array_push($data,[$step,$item['name'],$item['unid'],
				$item['nrid'],$item['prog'],$item['lgrp'],$item['mgrp']]);
		}
		$name = $this->_code."_".$this->_ssem."_"."studentlist.csv";
		require_once dirname(__FILE__).'/FileText.php';
		$fcsv = new FileText();
		$fcsv->sendCSV($name,$head,$data);
	}
}
?>

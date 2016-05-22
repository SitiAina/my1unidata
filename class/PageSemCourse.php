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
		$cors = $this->_dodata->findCourseComponents($core['id']);
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
			$_tab->insert_linebr(2);
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
	}
}
?>

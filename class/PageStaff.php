<?php
require_once dirname(__FILE__).'/PageBase.php';
class PageStaff extends PageBase {
	function __construct() {
		parent::__construct('Staff List');
	}
	function build_page() {
		$user = $this->_dodata->getProfile();
		$staf = $this->_dodata->listStaff();
		if ($staf['stat']==false) {
			throw new Exception('Something is WRONG!');
		}
		// also use parent build
		parent::build_page();
		$size = count($staf['list']);
		if ($size>1) {
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
			$_col->insert_inner('<b>'.HEADER_STAFF_UNID.'</b>');
			$_row->append_object($_col);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>'.HEADER_STAFF_NRIC.'</b>');
			$_row->append_object($_col);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>'.HEADER_STAFF_NAME.'</b>');
			$_row->append_object($_col);
			$_col = new HTMLObject('th');
			$_col->insert_inner('<b>FLAG</b>');
			$_row->append_object($_col);
			foreach ($staf['list'] as $item) {
				if ($item['unid']=='0100000')
					continue;
				$_row = new HTMLObject('tr');
				$_row->do_1skipline();
				$_tab->append_object($_row);
				$_col = new HTMLObject('td');
				$_col->insert_inner($item['unid']);
				$_row->append_object($_col);
				$_col = new HTMLObject('td');
				$_col->insert_inner($item['nrid']);
				$_row->append_object($_col);
				$_col = new HTMLObject('td');
				$_col->insert_inner($item['name']);
				$_row->append_object($_col);
				$_col = new HTMLObject('td');
				$_col->insert_inner($item['flag']);
				$_row->append_object($_col);
			}
		} else {
			// create message
			$temp = new HTMLObject('p');
			$temp->insert_inner('<b>No staff profile found in database.</b>');
			$temp->insert_linebr(2);
			$temp->do_1skipline();
			$this->append_2body($temp);
		}
		// create command links
		$temp = new HTMLObject('p');
		$temp->insert_inner('<a href="work.php?do=viewstaff&fmt=csv">'.
			'Download CSV</a>');
		$temp->do_1skipline();
		$this->append_2body($temp);
		$temp = new HTMLObject('p');
		$temp->insert_inner('<a href="javascript:history.back()">Back</a>');
		$temp->do_1skipline();
		$this->append_2body($temp);
	}
	function sendCSV() {
		$staf = $this->_dodata->listStaff();
		if ($staf['stat']==false) {
			throw new Exception('Something is WRONG!');
		}
		$head =  [ HEADER_STAFF_UNID,
			HEADER_STAFF_NRIC, HEADER_STAFF_NAME ];
		$data = [];
		foreach ($staf['list'] as $item) {
			if ($item['unid']=='0100000')
				continue;
			array_push($data,[$item['unid'],$item['nrid'],$item['name']]);
		}
		require_once dirname(__FILE__).'/FileText.php';
		$fcsv = new FileText();
		$fcsv->sendCSV('liststaff.csv',$head,$data);
	}
}
?>

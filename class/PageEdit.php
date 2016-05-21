<?php
require_once dirname(__FILE__).'/PageDone.php';
class PageEdit extends PageDone {
	function __construct($id,$code,$name,$unit) {
		parent::__construct();
		$this->_dodata->modifyCourse($id,$code,$name,$unit);
	}
	function build_page() {
		parent::build_page();
	}
}
?>

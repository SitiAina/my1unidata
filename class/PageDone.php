<?php
require_once dirname(__FILE__).'/PageBase.php';
class PageDone extends PageBase {
	function __construct() {
		parent::__construct();
	}
	function build_page() {
		// also use parent build
		parent::build_page();
		// create message
		$temp = new HTMLObject('p');
		$temp->insert_inner('Done.');
		$temp->do_1skipline();
		$this->append_2body($temp);
		// create command links
		$temp = new HTMLObject('p');
		$temp->insert_inner('<a href="javascript:history.back()">Back</a>');
		$temp->do_1skipline();
		$this->append_2body($temp);
	}
}
?>

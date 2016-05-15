<?php
require_once dirname(__FILE__).'/Page.php';
class PageInit extends Page {
	function __construct() {
		parent::__construct();
	}
	function build_page() {
		// also use parent build
		parent::build_page();
		// create command links
		$temp = new HTMLObject('p');
		$temp->insert_inner('<a href="work.php?do=login">Login</a>');
		$temp->do_1skipline();
		$this->append_2body($temp);
	}
}
?>

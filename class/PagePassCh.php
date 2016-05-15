<?php
require_once dirname(__FILE__).'/PageBase.php';
class PagePassCh extends PageBase {
	function __construct() {
		parent::__construct();
		if (!isset($_SESSION['pass_new'])) {
			$this->throw_debug('Invalid request!');
		}
		$pass = $this->_dodata->modifyPass($_SESSION['username'],
			$_SESSION['userpass'], $_SESSION['pass_new']);
		if ($pass!==true) {
			$this->throw_debug('Password change failed!');
		}
		// update session?
		$_SESSION['userpass'] = $_SESSION['pass_new'];
		unset($_SESSION['pass_new']);
	}
	function build_page() {
		$user = $this->_dodata->getProfile();
		// also use parent build
		parent::build_page();
		// create message
		$temp = new HTMLObject('p');
		$temp->insert_inner('Password changed for '.$user['name'].'.');
		$temp->do_1skipline();
		$this->append_2body($temp);
		// create command links
		$temp = new HTMLObject('p');
		$temp->insert_inner('<a href="index.php">Main Page</a>');
		$temp->do_1skipline();
		$this->append_2body($temp);
	}
}
?>

<?php
require_once dirname(__FILE__).'/Page.php';
class PageBase extends Page {
	protected $_dodata;
	function __construct($title=PAGE_TITLE_DEFAULT) {
		session_start();
		if (!isset($_SESSION['username'])||
				!isset($_SESSION['userpass'])||
				!isset($_SESSION['usertype'])) {
			$this->throw_debug('Invalid Session!');
		}
		// okay, we have login info!
		$type = "UniData";
		if (intval($_SESSION['usertype'])==MY1STAFF_LOGIN) {
			$type = $type."Staff";
		}
		require_once dirname(__FILE__).'/'.$type.'.php';
		$data = new $type();
		$pass = $data->validateUser($_SESSION['username'],
			$_SESSION['userpass']);
		if ($pass!==true) {
			session_destroy();
			$this->throw_debug('Invalid login!');
		}
		$this->_dodata = $data;
		parent::__construct($title);
	}
}
?>

<?php
require_once dirname(__FILE__).'/../config/config.php';
require_once dirname(__FILE__).'/HTMLDocument.php';
define('PAGE_TITLE_DEFAULT','');
class Page extends HTMLDocument {
	protected $_dosubs;
	protected $_doflag;
	function __construct($title=PAGE_TITLE_DEFAULT,$reset=false) {
		parent::__construct(MY1APP_TITLE,$reset);
		if ($title!=PAGE_TITLE_DEFAULT) $title = ' - '.$title;
		$this->_title_ = $title;
		// flag variable for general use
		$this->_doflag = null;
	}
	function js_main() {
		return null;
	}
	function build_page() {
		$test = $this->js_main();
		if ($test!=null) {
			// create main script
			$temp = new JSObject('js_main');
			$temp->insert_inner($test);
			$this->append_2body($temp);
		}
		// create page title
		$temp = new HTMLObject('h1');
		$temp->insert_inner(MY1APP_TITLE.$this->_title_);
		$temp->do_1skipline();
		$this->append_2body($temp);
	}
	function write_html() {
		$this->build_page();
		parent::write_html();
	}
	function Show() {
		$this->write_html();
		exit();
	}
	protected function throw_debug($error) {
		throw new Exception('['.get_class($this).'] => {'.$error.'}');
	}
}
?>

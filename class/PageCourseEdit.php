<?php
require_once dirname(__FILE__).'/PageBase.php';
class PageCourseEdit extends PageBase {
	protected $_code;
	function __construct($code) {
		parent::__construct('Edit Course Info');
		$this->_code = $code;
	}
	function js_main() {
		$js_main = <<< JSMAIN
function post_check() {
	var chk_form = document.getElementById('form_course');
	return true;
}
JSMAIN;
		return $js_main;
	}
	function build_page() {
		$user = $this->_dodata->getProfile();
		$item = $this->_dodata->findCourse($this->_code);
		if ($item['stat']==false) {
			$this->throw_debug('Something is WRONG!');
		}
		// also use parent build
		parent::build_page();
		// create form
		$form = new HTMLForm('form_course','work.php',
			'javascript:return post_check();');
		$this->append_2body($form);
		$form->create_input_hidden('cCoId',$item['id'],true);
		$form->create_input('Course Code','text','cCode','',$this->_code);
		$form->create_input('Course Name','text','cName','',$item['name']);
		$form->create_input('Course Unit','text','cUnit','',$item['unit'],true);
		$form->create_submit('Submit','editcourse');
		// create command links
		$temp = new HTMLObject('p');
		$temp->insert_inner('<a href="javascript:history.back()">Back</a>');
		$temp->do_1skipline();
		$this->append_2body($temp);
	}
}
?>

<?php
require_once dirname(__FILE__).'/PageBase.php';
class PageSemCourseEdit extends PageBase {
	protected $_ssem;
	protected $_code;
	protected $_ccmp;
	function __construct($ssem,$code,$ccmp) {
		parent::__construct('Edit Course Component');
		$ssem = strtoupper(trim($ssem));
		$code = strtoupper(trim($code));
		$ccmp = intval($ccmp);
		$this->_ssem = $ssem;
		$this->_code = $code;
		$this->_ccmp = $ccmp;
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
		$core = $this->_dodata->findCourse($this->_code);
		if ($core['stat']==false) {
			$this->throw_debug('Something is WRONG!');
		}
		$text = $this->_dodata->selectSession($this->_ssem);
		$cors = $this->_dodata->findCoursesComponents($core['id']);
		if ($cors['stat']==false) {
			$this->throw_debug('Something is WRONG!');
		}
		$item = null;
		foreach ($cors['list'] as $temp) {
			if ($temp['id']==$this->_ccmp) {
				$item = $temp;
			}
		}
		if ($item==null) {
			$this->throw_debug('Cannot find that component!');
		}
		// also use parent build
		parent::build_page();
		// create form
		$form = new HTMLForm('form_ccomp','work.php',
			'javascript:return post_check();');
		$this->append_2body($form);
		$form->create_input_hidden('cCCID',$this->_ccmp,true);
		$form->create_input('Name','text','cName','',$item['name']);
		$form->create_input('Label','text','cText','',$item['lbl']);
		$form->create_input('Raw','text','cRawM','',$item['raw'],true);
		$form->create_input('Pct','text','cPctM','',$item['pct'],true);
		$form->create_submit('Submit','editccmp');
		// create command links
		$temp = new HTMLObject('p');
		$temp->insert_inner('<a href="javascript:history.back()">Back</a>');
		$temp->do_1skipline();
		$this->append_2body($temp);
	}
}
?>

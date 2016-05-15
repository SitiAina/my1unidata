<?php
require_once dirname(__FILE__).'/PageLogin.php';
class PagePass extends PageLogin {
	function __construct() {
		session_start();
		if (!isset($_SESSION['username'])||
				!isset($_SESSION['userpass'])||
				!isset($_SESSION['usertype'])) {
			$this->throw_debug('Invalid Session!');
		}
		parent::__construct('Change Password');
	}
	function js_main() {
		$js_main = <<< JSMAIN
function mod_check() {
	var chk_form = document.getElementById('form_chpass');
	chk_form.user.value = chk_form.user.placeholder;
	chk_form.user.disabled = false;
	chk_form.pass.value = sha512(chk_form.pass.value);
	chk_form.pasX.value = sha512(chk_form.pasX.value);
	chk_form.pasY.value = sha512(chk_form.pasY.value);
	return true;
}
JSMAIN;
		return $js_main;
	}
	function build_page() {
		// add sha512 js lib
		$temp = new JSObject('js_sha512lib');
		$temp->insert_inner($this->js_sha512lib());
		$this->append_2head($temp);
		// create main script
		$temp = new JSObject('js_main');
		$temp->insert_inner($this->js_main());
		$this->append_2body($temp);
		// create page title
		$temp = new HTMLObject('h1');
		$temp->insert_inner(MY1APP_TITLE.$this->_title_);
		$temp->do_1skipline();
		$this->append_2body($temp);
		// create form
		$form = new HTMLObject('form');
		$form->insert_id('form_chpass');
		$form->insert_keyvalue('method','POST');
		$form->insert_keyvalue('action','work.php');
		$form->insert_keyvalue('onsubmit','javascript:return mod_check();');
		$form->do_multiline();
		$this->append_2body($form);
		// create label username
		$temp = new HTMLObject('label');
		$temp->insert_inner('Username');
		$temp->insert_linebr();
		$temp->do_1skipline();
		$form->append_object($temp);
		// create input username
		$temp = new HTMLObject('input');
		$temp->insert_keyvalue('type','text');
		$temp->insert_keyvalue('name','user');
		$temp->insert_keyvalue('placeholder',$_SESSION['username']);
		$temp->insert_constant('disabled');
		$temp->insert_linebr(2);
		$temp->do_1skipline();
		$temp->remove_tail();
		$form->append_object($temp);
		// create label old password
		$temp = new HTMLObject('label');
		$temp->insert_inner('Old Password');
		$temp->insert_linebr();
		$temp->do_1skipline();
		$form->append_object($temp);
		// create input old password
		$temp = new HTMLObject('input');
		$temp->insert_keyvalue('type','password');
		$temp->insert_keyvalue('name','pass');
		$temp->insert_keyvalue('placeholder','Old Password');
		$temp->remove_tail();
		$temp->insert_linebr(2);
		$temp->do_1skipline();
		$form->append_object($temp);
		// create label new password 1
		$temp = new HTMLObject('label');
		$temp->insert_inner('New Password');
		$temp->insert_linebr();
		$temp->do_1skipline();
		$form->append_object($temp);
		// create input new password 1
		$temp = new HTMLObject('input');
		$temp->insert_keyvalue('type','password');
		$temp->insert_keyvalue('name','pasX');
		$temp->insert_keyvalue('placeholder','New Password');
		$temp->remove_tail();
		$temp->insert_linebr(2);
		$temp->do_1skipline();
		$form->append_object($temp);
		// create label new password 2
		$temp = new HTMLObject('label');
		$temp->insert_inner('New Password (Again)');
		$temp->insert_linebr();
		$temp->do_1skipline();
		$form->append_object($temp);
		// create input new password 2
		$temp = new HTMLObject('input');
		$temp->insert_keyvalue('type','password');
		$temp->insert_keyvalue('name','pasY');
		$temp->insert_keyvalue('placeholder','New Password (Again)');
		$temp->remove_tail();
		$temp->insert_linebr(2);
		$temp->do_1skipline();
		$form->append_object($temp);
		// create submit button
		$temp = new HTMLObject('input');
		$temp->insert_keyvalue('type','submit');
		$temp->insert_keyvalue('value','Change');
		$temp->insert_keyvalue('name','chpass');
		$temp->remove_tail();
		$temp->do_1skipline();
		$form->append_object($temp);
	}
}
?>

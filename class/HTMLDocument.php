<?php

class HTMLObject {
	protected $_init;
	protected $_text;
	protected $_tail;
	protected $_subs;
	protected $_dobr;
	protected $_line;
	protected $_next;
	function __construct($tag) {
		$this->_init = "<".$tag.">";
		$this->_text = "";
		$this->_tail = "</".$tag.">";
		$this->_subs = array();
		$this->_dobr = "";
		$this->_line = "";
		$this->_next = "";
	}
	function remove_tail() {
		$this->_tail = "";
	}
	function do_multiline() {
		$this->_line = "\n";
	}
	// not needed when multilined
	function do_1skipline() {
		$this->_next = "\n";
	}
	function insert_linebr($count) {
		if (!isset($count)||empty($count)) {
			$count = 1;
		}
		for ($loop=0;$loop<$count;$loop++) {
			$this->_dobr = $this->_dobr."<br>";
		}
	}
	function insert_id($id) {
		$this->_init = substr($this->_init,0,strrpos($this->_init,">")).
			" id=\"".$id."\">";
	}
	function insert_style($style) {
		$this->_init = substr($this->_init,0,strrpos($this->_init,">")).
			" style=\"".$style."\">";
	}
	function insert_keyvalue($key,$value,$noquote=false) {
		$do_quote="\"";
		if (isset($noquote)&&$noquote==true) $do_quote="";
		$this->_init = substr($this->_init,0,strrpos($this->_init,">")).
			" ".$key."=".$do_quote.$value.$do_quote.">";
	}
	function insert_inner($text) {
		$this->_text = $text;
	}
	function insert_constant($value) {
		$this->_init = substr($this->_init,0,strrpos($this->_init,">")).
			" ".$value.">";
	}
	function insert_object($html) {
		if (is_a($html,'HTMLObject')) {
			array_unshift($this->_subs,$html);
			return true;
		} else {
			return false;
		}
	}
	function append_object($html) {
		if (is_a($html,'HTMLObject')) {
			array_push($this->_subs,$html);
			return true;
		} else {
			return false;
		}
	}
	function write_html() {
		// first put out start tag
		echo $this->_init.$this->_line;
		// inner html??
		if (!empty($this->_text))
			echo $this->_text.$this->_line;
		// iterate through child elements
		foreach ($this->_subs as $item) {
			$item->write_html();
		}
		// put out ending tag
		if (!empty($this->_tail)) {
			echo $this->_tail;
		}
		// add line break?
		if (!empty($this->_dobr))
			echo $this->_dobr;
		// if multiline
		echo $this->_line;
		// requested newline
		echo $this->_next;
	}
}

class CSSObject extends HTMLObject {
	function __construct($id) {
		parent::__construct('style');
		if (isset($id)&&!empty($id))
			$this->insert_id($id);
		$this->insert_keyvalue('type','text/css');
		$this->do_multiline();
	}
}

final class CSSReset extends CSSObject {
	function __construct($id) {
		parent::__construct($id);
		$this->_text = <<<CSSRESET
/***** CSS Reset (Eric Meyer) *****/
html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, font, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td {
	margin: 0; padding: 0; border: 0; outline: 0; vertical-align: baseline; }
:focus { outline: 0; }
body { line-height: 1; color: black; background: white; }
ol, ul { list-style: none; }
table { border-collapse: separate; border-spacing: 0; }
caption, th, td { text-align: left; font-weight: normal; }
blockquote:before, blockquote:after,
q:before, q:after { content: ""; }
blockquote, q { quotes: "" ""; }
/***** ====================== *****/
CSSRESET;
	}
	function insert_inner($text) {
		// override not to do anything!
	}
}

class JSObject extends HTMLObject {
	function __construct($id) {
		parent::__construct('script');
		if (isset($id)&&!empty($id))
			$this->insert_id($id);
		$this->insert_keyvalue('type','text/javascript');
		$this->do_multiline();
	}
}

class HTMLHead extends HTMLObject {
	function __construct($title) {
		parent::__construct('head');
		// create charset
		$cset = new HTMLObject('meta');
		$cset->insert_keyvalue('charset','UTF-8');
		$cset->remove_tail();
		$cset->do_1skipline();
		$this->append_object($cset);
		// create title
		$tell = new HTMLObject('title');
		$tell->insert_inner($title);
		$tell->do_1skipline();
		$this->append_object($tell);
		// head is multi-line
		$this->do_multiline();
	}
}

class HTMLDocument extends HTMLObject {
	protected $_head;
	protected $_body;
	function __construct($title,$reset=false) {
		parent::__construct('html');
		$this->_head = new HTMLHead($title);
		$this->append_object($this->_head);
		if ($reset===true) {
			// create reset css... if requested
			$crst = new CSSReset('css_reset');
			$this->_head->append_object($crst);
		}
		$this->_body = new HTMLObject('body');
		$this->_body->do_multiline();
		$this->append_object($this->_body);
		// HTML document ALWAYS multiline?
		$this->do_multiline();
	}
	function insert_2head($html) {
		return $this->_head->insert_object($html);
	}
	function insert_2body($html) {
		return $this->_body->insert_object($html);
	}
	function insert_onload($fnname) {
		return $this->_body->insert_keyvalue('onload',$fnname);
	}
	function append_2head($html) {
		return $this->_head->append_object($html);
	}
	function append_2body($html) {
		return $this->_body->append_object($html);
	}
	function write_html() {
		echo "<!DOCTYPE html>\n"; // html5!
		parent::write_html();
	}
}

?>

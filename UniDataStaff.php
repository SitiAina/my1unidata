<?php
require_once dirname(__FILE__).'/UniData.php';
class UniDataStaff extends UniData {
	protected $_alevel;
	function __construct($dbfile=UNIDATA_FILE) {
		parent::__construct($dbfile);
	}
	function validateUser($username, $userpass) {
		$prep = "SELECT id,unid,name,alvl FROM staffs WHERE unid=? AND pass=?";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$username,PDO::PARAM_STR)||
				!$stmt->bindValue(2,$userpass,PDO::PARAM_STR)) {
			$this->throw_debug('validateUser bind error!');
		}
		if (!$stmt->execute()) {
			$this->throw_debug('validateUser execute error!');
		}
		$item = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($item==false) return false;
		$this->_userid = intval($item['id']); // make sure an integer?
		$this->_dounid = $item['unid'];
		$this->_doname = $item['name'];
		$this->_alevel = intval($item['alvl']);
		return true;
	}
	function modifyPass($username, $pass_old, $pass_new) {
		if ($this->_dounid==null) {
			$this->throw_debug('modifyPass general error!');
		}
		$prep = "SELECT id FROM staffs WHERE unid=? AND pass=?";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$username,PDO::PARAM_STR)||
				!$stmt->bindValue(2,$pass_old,PDO::PARAM_STR)) {
			$this->throw_debug('modifyPass bind error!');
		}
		if (!$stmt->execute()) {
			$this->throw_debug('modifyPass execute error!');
		}
		$item = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($item==false) {
			$this->throw_debug('modifyPass validate error!');
		}
		$stmt->closeCursor();
		$prep = "UPDATE staffs SET pass=? WHERE id=?";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$pass_new,PDO::PARAM_STR)||
				!$stmt->bindValue(2,$item['id'],PDO::PARAM_INT)) {
			$this->throw_debug('modifyPass bind2 error!');
		}
		if (!$stmt->execute()) {
			$this->throw_debug('modifyPass execute2 error!');
		}
		$stmt->closeCursor();
		return true;
	}
	function getProfile() {
		$check = parent::getProfile();
		$check['staf'] = true;
		$check['alvl'] = $this->_alevel;
		return $check;
	}
	function checkStaff() {
		$table = "staffs";
		if (!$this->table_exists($table)) {
			$tdata = array();
			array_push($tdata,
				array("name"=>"id","type"=>"INTEGER PRIMARY KEY"),
				array("name"=>"unid","type"=>"TEXT UNIQUE NOT NULL"),
				array("name"=>"pass","type"=>"TEXT NOT NULL"),
				array("name"=>"name","type"=>"TEXT NOT NULL"),
				array("name"=>"nrid","type"=>"TEXT NOT NULL"),
				array("name"=>"alvl","type"=>"INTEGER"),
				array("name"=>"flag","type"=>"INTEGER"));
			$this->table_create($table,$tdata);
		}
	}
	function findStaff($unid) {
		$result = [];
		$prep = "SELECT id, name, nrid, flag FROM staffs WHERE unid=?";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$unid,PDO::PARAM_STR)) {
			$this->throw_debug('findStaff bind error!');
		}
		if (!$stmt->execute()) {
			$this->throw_debug('findStaff execute error!');
		}
		$item = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($item!=false) {
			$result = $item;
			$result['stat'] = true;
			$result['id'] = intval($item['id']);
			$result['flag'] = intval($item['flag']);
		} else {
			$result['stat'] = false;
		}
		return $result;
	}
	function createStaff($unid,$name,$nrid,$alvl=0) {
		$unid = strtoupper($unid);
		$name = strtoupper($name);
		$nrid = strtoupper($nrid);
		$alvl = intval($alvl);
		$hash = hash('sha512',$nrid,false);
		$prep = "INSERT INTO staffs (unid,pass,name,nrid,alvl,flag) ".
			"VALUES (:unid,:pass,:name,:nrid,:alvl,1)";
		$stmt = $this->prepare($prep);
		$stmt->bindValue(':unid',$unid,PDO::PARAM_STR);
		$stmt->bindValue(':pass',$hash,PDO::PARAM_STR);
		$stmt->bindValue(':name',$name,PDO::PARAM_STR);
		$stmt->bindValue(':nrid',$nrid,PDO::PARAM_STR);
		$stmt->bindValue(':alvl',$alvl,PDO::PARAM_INT);
		$temp = $stmt->execute();
		$stmt->closeCursor();
		if ($temp==false) {
			$this->throw_debug('createStaff Failed');
		}
	}
	function listStaff() {
		$result = [];
		$prep = "SELECT unid, nrid, name, flag FROM staffs";
		$stmt = $this->prepare($prep);
		if (!$stmt->execute()) {
			$this->throw_debug('listStaff execute error!');
		}
		$item = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($item!=false) {
			$result['list'] = $item;
			$result['stat'] = true;
		} else {
			$result['stat'] = false;
		}
		return $result;
	}
	function createCourse($code,$name,$unit) {
		$code = strtoupper($code);
		$name = strtoupper($name);
		$unit = intval($unit);
		$prep = "INSERT INTO courses (code,name,unit,flag) ".
			"VALUES (:code,:name,:unit,1)";
		$stmt = $this->prepare($prep);
		$stmt->bindValue(':code',$code,PDO::PARAM_STR);
		$stmt->bindValue(':name',$name,PDO::PARAM_STR);
		$stmt->bindValue(':unit',$unit,PDO::PARAM_INT);
		$temp = $stmt->execute();
		$stmt->closeCursor();
		if ($temp==false) {
			$this->throw_debug('createCourse Failed');
		}
	}
	function listCourse() {
		$result = [];
		$prep = "SELECT code, name, unit FROM courses";
		$stmt = $this->prepare($prep);
		if (!$stmt->execute()) {
			$this->throw_debug('listCourse execute error!');
		}
		$item = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($item!=false) {
			$result['list'] = $item;
			$result['stat'] = true;
		} else {
			$result['stat'] = false;
		}
		return $result;
	}
}
?>

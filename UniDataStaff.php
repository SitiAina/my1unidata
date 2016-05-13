<?php
require_once dirname(__FILE__).'/UniData.php';
class UniDataStaff extends UniData {
	function __construct($dbfile=UNIDATA_FILE) {
		parent::__construct($dbfile);
	}
	function validateUser($username, $userpass) {
		$prep = "SELECT id,unid,name FROM staffs WHERE unid=? AND pass=?";
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
		return true;
	}
	function getProfile() {
		$check = parent::getProfile();
		$check['staf'] = true;
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
	function listStaff() {
		$result = [];
		$prep = "SELECT unid, name, nrid, flag FROM staffs";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$unid,PDO::PARAM_STR)) {
			$this->throw_debug('listStaff bind error!');
		}
		if (!$stmt->execute()) {
			$this->throw_debug('listStaff execute error!');
		}
		$item = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($item!=false) {
			$result = $item;
			$result['stat'] = true;
			$result['flag'] = intval($item['flag']);
		} else {
			$result['stat'] = false;
		}
		return $result;
	}
	function createStaff($unid,$name,$nrid) {
		$unid = strtoupper($unid);
		$name = strtoupper($name);
		$nrid = strtoupper($nrid);
		$hash = hash('sha512',$nrid,false);
		$prep = "INSERT INTO staffs (unid,pass,name,nrid,flag) ".
			"VALUES (:unid,:pass,:name,:nrid,1)";
		$stmt = $this->prepare($prep);
		$stmt->bindValue(':unid',$unid,PDO::PARAM_STR);
		$stmt->bindValue(':pass',$hash,PDO::PARAM_STR);
		$stmt->bindValue(':name',$name,PDO::PARAM_STR);
		$stmt->bindValue(':nrid',$nrid,PDO::PARAM_STR);
		$temp = $stmt->execute();
		$stmt->closeCursor();
		if ($temp==false) {
			$this->throw_debug('createStaff Failed');
		}
	}
}
?>

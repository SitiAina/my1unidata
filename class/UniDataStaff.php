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
		$prep = "SELECT id, name, nrid, flag FROM staffs".
			" WHERE unid=? ORDER BY name";
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
		$prep = "SELECT code, name, unit FROM courses ORDER BY code";
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
	function createCourseComponent($coid,$name,$raw,$pct,$grp,$sub) {
		if ($this->_sessem==null) {
			$this->throw_debug('Session/Semester NOT selected!');
		}
		$coid = intval($coid);
		$name = strtolower($name);
		$grp = intval($grp);
		$sub = intval($sub);
		$raw = floatval($raw);
		$pct = floatval($pct);
		$prep = "INSERT INTO courses_components ";
		$prep = $prep."(ssem,coid,name,raw,pct,grp,sub,flag) ";
		$prep = $prep."VALUES (:ssem,:coid,:name,:raw,:pct,:grp,:sub,0)";
		$stmt = $this->prepare($prep);
		$stmt->bindValue(':ssem',$this->_sessem,PDO::PARAM_INT);
		$stmt->bindValue(':coid',$coid,PDO::PARAM_INT);
		$stmt->bindValue(':name',$name,PDO::PARAM_STR);
		$stmt->bindValue(':raw',$raw,PDO::PARAM_STR); // no PARAM_REAL!
		$stmt->bindValue(':pct',$pct,PDO::PARAM_STR);
		$stmt->bindValue(':grp',$grp,PDO::PARAM_INT);
		$stmt->bindValue(':sub',$sub,PDO::PARAM_INT);
		$temp = $stmt->execute();
		$stmt->closeCursor();
		if ($temp==false) {
			$this->throw_debug('createCourseComponent Failed');
		}
	}
	function flagCourseComponents($coid,$flag=false) {
		if ($this->_sessem==null) {
			$this->throw_debug('Session/Semester NOT selected!');
		}
		$prep = "UPDATE courses_components SET flag=";
		if ($flag===true) $prep = $prep."1 ";
		else $prep = $prep."0 ";
		$prep = $prep."WHERE ssem=? AND coid=?";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$this->_sessem,PDO::PARAM_INT)||
				!$stmt->bindValue(2,$coid,PDO::PARAM_INT)) {
			$this->throw_debug('flagCourseComponents bind error!');
		}
		if (!$stmt->execute()) {
			$this->throw_debug('flagCourseComponents execute error!');
		}
	}
	function modifyCourseComponent($id,$coid,$name,$raw,$pct,$grp,$sub) {
		$coid = intval($coid);
		$name = strtolower($name);
		$raw = floatval($raw);
		$pct = floatval($pct);
		$grp = intval($grp);
		$sub = intval($sub);
		$prep = "UPDATE courses_components SET name=? , raw=? , pct=? , ";
		$prep = $prep."grp=? , sub=? WHERE id=?";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$name,PDO::PARAM_STR)||
				!$stmt->bindValue(2,$raw,PDO::PARAM_STR)||
				!$stmt->bindValue(3,$pct,PDO::PARAM_STR)||
				!$stmt->bindValue(4,$grp,PDO::PARAM_INT)||
				!$stmt->bindValue(5,$sub,PDO::PARAM_INT)||
				!$stmt->bindValue(6,$id,PDO::PARAM_INT)) {
			$this->throw_debug('modifyCourseComponent bind error!');
		}
		if (!$stmt->execute()) {
			$this->throw_debug('modifyCourseComponent execute error!');
		}
	}
	function checkCourseComponents($coid) {
		if ($this->_sessem==null) {
			$this->throw_debug('Session/Semester NOT selected!');
		}
		$coid = intval($coid);
		$result = [];
		$prep = "SELECT id, pct FROM courses_components ";
		$prep = $prep."WHERE coid=? AND ssem=?";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$coid,PDO::PARAM_INT)||
				!$stmt->bindValue(2,$this->_sessem,PDO::PARAM_INT)) {
			$this->throw_debug('checkCourseComponents bind error!');
		}
		if (!$stmt->execute()) {
			$this->throw_debug('checkCourseComponents execute error!');
		}
		$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($items==false) {
			$this->throw_debug('checkCourseComponents unknown error!');
		}
		$total = 0.0;
		$check = false;
		foreach ($items as $item) {
			$total = $total + floatval($item['pct']);
		}
		if ($total===floatval(100)) {
			$check = true;
		}
		$this->flagCourseComponents($coid,$check);
		return $check;
	}
}
?>
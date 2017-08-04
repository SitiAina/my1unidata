<?php
require_once dirname(__FILE__).'/UniData.php';
class UniDataStaff extends UniData {
	protected $_alevel;
	function __construct($dbfile=UNIDATA_FILE) {
		parent::__construct($dbfile);
		$this->_usrtab = 'staffs';
		$this->_alevel = 0;
	}
	function validateUser($username, $userpass) {
		$item = parent::validateUser($username, $userpass);
		if ($item==false) return false;
		$this->_alevel = intval($this->_dofull['alvl']);
		return true;
	}
	function getProfile() {
		$check = parent::getProfile();
		$check['staf'] = true;
		$check['alvl'] = $this->_alevel;
		return $check;
	}
	function checkStaffs() {
		$table = $this->_usrtab;
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
		$prep = "SELECT id, name, nrid, flag FROM ".$this->_usrtab.
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
		$prep = "INSERT INTO ".$this->_usrtab.
			" (unid,pass,name,nrid,alvl,flag) ".
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
	function listStaffs() {
		$result = [];
		$prep = "SELECT unid, nrid, name, flag FROM ".$this->_usrtab;
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
	function listCourses() {
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
	function modifyCourse($id,$code,$name,$unit) {
		$code = strtoupper($code);
		$name = strtoupper($name);
		$unit = intval($unit);
		$id = intval($id);
		$prep = "UPDATE courses SET code=? , name=? , unit=? WHERE id=?";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$code,PDO::PARAM_STR)||
				!$stmt->bindValue(2,$name,PDO::PARAM_STR)||
				!$stmt->bindValue(3,$unit,PDO::PARAM_INT)||
				!$stmt->bindValue(4,$id,PDO::PARAM_INT)) {
			$this->throw_debug('modifyCourse bind error!');
		}
		if (!$stmt->execute()) {
			$this->throw_debug('modifyCourse execute error!');
		}
	}
	function checkCoursesStaffs() {
		$table = "courses_staffs";
		if (!$this->table_exists($table)) {
			$tdata = array();
			array_push($tdata,
				array("name"=>"id","type"=>"INTEGER PRIMARY KEY"),
				array("name"=>"ssem","type"=>"INTEGER"),
				array("name"=>"coid","type"=>"INTEGER"),
				array("name"=>"stid","type"=>"INTEGER"),
				array("name"=>"flag","type"=>"INTEGER")
			);
			$tmore = array();
			array_push($tmore,"UNIQUE (ssem,coid,stid)",
				"FOREIGN KEY(coid) REFERENCES courses(id)",
				"FOREIGN KEY(stid) REFERENCES staffs(id)");
			$this->table_create($table,$tdata,$tmore);
		}
		$this->checkCourses();
		$this->checkStaffs();
		$view = "courses_staffs_view";
		if (!$this->view_exists($view)) {
			$data = array();
			array_push($data,
				array("which"=>"T1.ssem","alias"=>"ssem"),
				array("which"=>"T2.code","alias"=>"course"),
				array("which"=>"T2.name","alias"=>"coursename"),
				array("which"=>"T3.unid","alias"=>"staff"),
				array("which"=>"T3.name","alias"=>"staffname")
			);
			$more = array();
			array_push($more," FROM courses_staffs T1, courses T2, ",
				"staffs T3 WHERE T1.coid=T2.id AND T1.stid=T3.id");
			$this->view_create($view,$data,$more);
		}
	}
	// findCoursesStaffs not needed coz we have listCoursesStaffs?
	function createCourseStaff($coid,$stid) {
		$ssem = intval($this->_sessem);
		$coid = intval($coid);
		$stid = intval($stid);
		$prep = "INSERT INTO courses_staffs (ssem,coid,stid,flag) ".
			"VALUES (:ssem,:coid,:stid,1)";
		$stmt = $this->prepare($prep);
		$stmt->bindValue(':ssem',$ssem,PDO::PARAM_INT);
		$stmt->bindValue(':coid',$coid,PDO::PARAM_INT);
		$stmt->bindValue(':stid',$stid,PDO::PARAM_INT);
		$temp = $stmt->execute();
		$stmt->closeCursor();
		if ($temp==false) {
			$this->throw_debug('createCourseStaff Failed');
		}
	}
	function listCoursesStaffs($user=null,$code=null) {
		if ($user!=null) $user = strtoupper($user);
		else if ($code!=null) $code = strtoupper($code);
		$result = [];
		$prep = "SELECT * FROM courses_staffs_view";
		if ($user!=null) $prep = $prep." WHERE staff=?";
		else if ($code!=null) $prep = $prep." WHERE course=?";
		$prep = $prep." ORDER BY ssem DESC, COURSE ASC";
		$stmt = $this->prepare($prep);
		if ($user!=null) {
			if (!$stmt->bindValue(1,$user,PDO::PARAM_STR)) {
				$this->throw_debug('listCoursesStaffs bind error!');
			}
		}
		else if ($code!=null) {
			if (!$stmt->bindValue(1,$code,PDO::PARAM_STR)) {
				$this->throw_debug('listCoursesStaffs bind error!');
			}
		}
		if (!$stmt->execute()) {
			$this->throw_debug('listCoursesStaffs execute error!');
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
	function createCourseComponent($coid,$name,$raw,$pct,$lab,$grp,$sub,$idx) {
		if ($this->_sessem==null) {
			$this->throw_debug('Session/Semester NOT selected!');
		}
		$coid = intval($coid);
		$name = strtoupper($name);
		$lab = strtoupper($lab);
		$grp = intval($grp);
		$sub = intval($sub);
		$idx = intval($idx);
		$raw = floatval($raw);
		$pct = floatval($pct);
		$prep = "INSERT INTO courses_components ";
		$prep = $prep."(ssem,coid,name,lbl,raw,pct,grp,sub,idx,flag) VALUES ";
		$prep = $prep."(:ssem,:coid,:name,:lab,:raw,:pct,:grp,:sub,:idx,1)";
		$stmt = $this->prepare($prep);
		$stmt->bindValue(':ssem',$this->_sessem,PDO::PARAM_INT);
		$stmt->bindValue(':coid',$coid,PDO::PARAM_INT);
		$stmt->bindValue(':name',$name,PDO::PARAM_STR);
		$stmt->bindValue(':lab',$lab,PDO::PARAM_STR);
		$stmt->bindValue(':raw',$raw,PDO::PARAM_STR); // no PARAM_REAL!
		$stmt->bindValue(':pct',$pct,PDO::PARAM_STR);
		$stmt->bindValue(':grp',$grp,PDO::PARAM_INT);
		$stmt->bindValue(':sub',$sub,PDO::PARAM_INT);
		$stmt->bindValue(':idx',$idx,PDO::PARAM_INT);
		$temp = $stmt->execute();
		$stmt->closeCursor();
		if ($temp==false) {
			$this->throw_debug('createCourseComponent Failed');
		}
	}
	function createStudent($unid,$name,$nrid,$prog) {
		$unid = strtoupper(trim($unid));
		$name = strtoupper(trim($name));
		$nrid = strtoupper(trim($nrid));
		$hash = hash('sha512',$nrid,false);
		$prog = strtoupper(preg_replace('/\s+/','',$prog));
		$prep = "INSERT INTO students (unid,pass,name,nrid,prog,flag)".
			" VALUES (:unid,:pass,:name,:nrid,:prog,1)";
		$stmt = $this->prepare($prep);
		$stmt->bindValue(':unid',$unid,PDO::PARAM_STR);
		$stmt->bindValue(':pass',$hash,PDO::PARAM_STR);
		$stmt->bindValue(':name',$name,PDO::PARAM_STR);
		$stmt->bindValue(':nrid',$nrid,PDO::PARAM_STR);
		$stmt->bindValue(':prog',$prog,PDO::PARAM_STR);
		$temp = $stmt->execute();
		$stmt->closeCursor();
		if ($temp==false) {
			$this->throw_debug('createStudent Failed');
		}
	}
	function listCourseStudent($table) {
		$result = [];
		$prep = "SELECT T2.name, T2.unid, T2.nrid, ".
			"T2.prog, T1.* FROM ".$table." T1, students T2 WHERE ".
			"T1.stid=T2.id";
		$stmt = $this->prepare($prep);
		if (!$stmt->execute()) {
			$this->throw_debug('listCourseStudent execute error!');
		}
		$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($list!=false) {
			foreach ($list as $item) {
				$item['stid'] = intval($item['stid']);
			}
			$result['list'] = $list;
			$result['stat'] = true;
		} else {
			$result['stat'] = false;
		}
		return $result;
	}
	function createCourseStudent($table,$stid,$lgrp,$mgrp) {
		$stid = intval($stid);
		$lgrp = strtoupper(trim($lgrp));
		$mgrp = strtoupper(trim($mgrp));
		$list = $this->findCourseStudent($table,$stid);
		if ($list['stat']===true&&count($list['list'])>0) {
			// modify!
			$prep = "UPDATE ".$table." SET lgrp=:lgrp, mgrp=:mgrp";
			$prep = $prep." WHERE stid=:stid";
		} else {
			$prep = "INSERT INTO ".$table;
			$prep = $prep." (stid,lgrp,mgrp,flag)";
			$prep = $prep." VALUES (:stid,:lgrp,:mgrp,1)";
		}
		$stmt = $this->prepare($prep);
		$stmt->bindValue(':stid',$stid,PDO::PARAM_INT);
		$stmt->bindValue(':lgrp',$lgrp,PDO::PARAM_STR);
		$stmt->bindValue(':mgrp',$mgrp,PDO::PARAM_STR);
		$temp = $stmt->execute();
		$stmt->closeCursor();
		if ($temp==false) {
			$this->throw_debug('createCourseStudent Failed');
		}
	}
	function updateCourseStudentMark($table,$stid,$col,$val) {
		$prep = "UPDATE ".$table." SET ".$col."=".$val." WHERE stid=?";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$stid,PDO::PARAM_INT)) {
			$this->throw_debug('updateCourseStudentMark bind error!');
		}
		if (!$stmt->execute()) {
			$this->throw_debug('updateCourseStudentMark execute error!');
		}
	}
	function updateCourseMarkTable($table,$rdata) {
		$test = array_shift($rdata);
		if (!isset($test['col'])||$test['col']!='stid') {
			$this->throw_debug('updateCourseMarkTable no stid!');
		}
		$stid = $test['val'];
		$test = array_shift($rdata);
		if (!isset($test['col'])||$test['col']!='name') {
			$this->throw_debug('updateCourseMarkTable no name!');
		}
		$name = $test['val'];
		$test = $this->findCourseMarkStudent($table,$stid);
		if ($test['stat'] == false) {
			// create new
			$prep = "INSERT INTO ".$table;
			$prep = $prep." (stid,name,flag) ";
			$prep = $prep."VALUES (:stid,:name,1)";
			$stmt = $this->prepare($prep);
			$stmt->bindValue(':stid',$stid,PDO::PARAM_INT);
			$stmt->bindValue(':name',$name,PDO::PARAM_STR);
			$temp = $stmt->execute();
			$stmt->closeCursor();
			if ($temp==false) {
				$this->throw_debug('createCourseMarkStudent Failed');
			}
		}
		// update
		foreach ($rdata as $item) {
			if (!isset($item['val'])||$item['val']=="")
				continue;
			if (isset($item['col'])) {
				$this->updateCourseMarkStudent($table,$stid,
					$item['col'],floatval($item['val']));
			} else if (isset($item['sys'])) {
				$this->updateCourseMarkStudentS($table,$stid,
					$item['sys'],strtoupper($item['val']));
			}
		}
	}
}
?>

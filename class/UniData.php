<?php
require_once dirname(__FILE__).'/Base.php';
define('UNIDATA_FILE','unidata.sqlite');
class UniData extends Base {
	protected $_userid;
	protected $_usrtab;
	protected $_dounid;
	protected $_doname;
	protected $_dofull; // validate results - temp?
	protected $_sessem; // session semester YYYYNNNNS format
	protected $_doskip; // skip column with these names
	function __construct($dbfile=UNIDATA_FILE) {
		parent::__construct($dbfile);
		$this->_userid = null;
		$this->_usrtab = 'students';
		$this->_dounid = null;
		$this->_doname = null;
		$this->_dofull = null;
		$this->_sessem = null;
		$this->_doskip = [ 'stid','matrik','nama','name','id','prog',
			'lgrp','flag','grp','lab' ];
	}
	function selectSession($sessem) {
		$year1 = intval($sessem/100000);
		$year2 = intval(($sessem%100000)/10);
		$dosem = intval($sessem%10);
		if ($year2!=($year1+1)||$dosem>3||$dosem<1) {
			$this->throw_debug('Invalid Session/Semester selection!');
		}
		$this->_sessem = intval($sessem);
		$check = "Academic Session ".$year1."/".$year2;
		$check = $check." Semester ".$dosem;
		return $check;
	}
	function validateUser($username, $userpass) {
		// hashing done by clients
		$prep = "SELECT * FROM ".$this->_usrtab." WHERE unid=? AND pass=?";
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
		$this->_dofull = $item;
		return true;
	}
	function getProfile() {
		return [ "id" => $this->_userid, "unid" => $this->_dounid,
			 "name" => $this->_doname, "staf" => false ];
	}
	function modifyPass($username, $pass_old, $pass_new) {
		if ($this->_dounid==null) {
			$this->throw_debug('modifyPass general error!');
		}
		$prep = "SELECT id FROM ".$this->_usrtab." WHERE unid=? AND pass=?";
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
		$prep = "UPDATE ".$this->_usrtab." SET pass=? WHERE id=?";
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
	function checkStudents() {
		$table = "students";
		if (!$this->table_exists($table)) {
			$tdata = array();
			array_push($tdata,
				array("name"=>"id","type"=>"INTEGER PRIMARY KEY"),
				array("name"=>"unid","type"=>"TEXT UNIQUE NOT NULL"),
				array("name"=>"pass","type"=>"TEXT NOT NULL"),
				array("name"=>"name","type"=>"TEXT NOT NULL"),
				array("name"=>"nrid","type"=>"TEXT NOT NULL"),
				array("name"=>"prog","type"=>"TEXT NOT NULL"),
				array("name"=>"flag","type"=>"INTEGER"));
			$this->table_create($table,$tdata);
		}
	}
	function findStudent($unid) {
		$result = [];
		$prep = "SELECT id, name, nrid, flag FROM students WHERE unid=?";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$unid,PDO::PARAM_STR)) {
			$this->throw_debug('findStudent bind error!');
		}
		if (!$stmt->execute()) {
			$this->throw_debug('findStudent execute error!');
		}
		$item = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($item!=false) {
			$result = $item;
			$result['stat'] = true;
			$result['id'] = intval($item['id']); // make sure an integer?
			$result['flag'] = intval($item['flag']);
		} else {
			$result['stat'] = false;
		}
		return $result;
	}
	function checkCourses() {
		$table = "courses";
		if (!$this->table_exists($table)) {
			$tdata = array();
			array_push($tdata,
				array("name"=>"id","type"=>"INTEGER PRIMARY KEY"),
				array("name"=>"code","type"=>"TEXT UNIQUE NOT NULL"),
				array("name"=>"name","type"=>"TEXT NOT NULL"),
				array("name"=>"unit","type"=>"INTEGER"),
				array("name"=>"flag","type"=>"INTEGER")
			);
			$this->table_create($table,$tdata);
		}
	}
	function findCourse($code) {
		$result = [];
		$prep = "SELECT id, name, unit, flag FROM courses WHERE code=?";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$code,PDO::PARAM_STR)) {
			$this->throw_debug('findCourse bind error!');
		}
		if (!$stmt->execute()) {
			$this->throw_debug('findCourse execute error!');
		}
		$item = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($item!=false) {
			$result = $item;
			$result['stat'] = true;
			$result['id'] = intval($item['id']); // make sure an integer?
			$result['unit'] = intval($item['unit']);
			$result['flag'] = intval($item['flag']);
		} else {
			$result['stat'] = false;
		}
		return $result;
	}
	function checkCoursesComponents() {
		$table = "courses_components";
		if (!$this->table_exists($table)) {
			$tdata = array();
			array_push($tdata,
				array("name"=>"id","type"=>"INTEGER PRIMARY KEY"),
				array("name"=>"ssem","type"=>"INTEGER"),
				array("name"=>"coid","type"=>"INTEGER"),
				array("name"=>"name","type"=>"TEXT"),
				array("name"=>"raw","type"=>"REAL"),
				array("name"=>"pct","type"=>"REAL"),
				array("name"=>"grp","type"=>"INTEGER"),
				array("name"=>"sub","type"=>"INTEGER"),
				array("name"=>"flag","type"=>"INTEGER")
			);
			$tmore = array();
			array_push($tmore,"UNIQUE (ssem,coid,name)",
				"FOREIGN KEY(coid) REFERENCES courses(id)");
			$this->table_create($table,$tdata,$tmore);
		}
	}
	function findCourseComponents($coid,$name=null) {
		if ($this->_sessem==null) {
			$this->throw_debug('Session/Semester NOT selected!');
		}
		$result = [];
		$prep = "SELECT id, name, raw, pct, grp, sub, flag ";
		$prep = $prep."FROM courses_components ";
		$prep = $prep."WHERE coid=? AND ssem=?";
		if ($name!=null) {
			$prep = $prep." AND name=?";
			$name = strtoupper($name);
		}
		$prep = $prep." ORDER BY grp ASC, sub ASC";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$coid,PDO::PARAM_INT)||
				!$stmt->bindValue(2,$this->_sessem,PDO::PARAM_INT)) {
			$this->throw_debug('findCourseComponent bind error!');
		}
		if ($name!=null) {
			if (!$stmt->bindValue(3,$name,PDO::PARAM_STR)) {
				$this->throw_debug('findCourseComponent bind error!');
			}
		}
		if (!$stmt->execute()) {
			$this->throw_debug('findCourseComponent execute error!');
		}
		$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($items!=false) {
			$count = 0;
			foreach ($items as $item) {
				$item['id'] = intval($item['id']); // make sure an integer?
				$item['flag'] = intval($item['flag']);
				$item['grp'] = intval($item['grp']);
				$item['sub'] = intval($item['sub']);
				$item['raw'] = floatval($item['raw']);
				$item['pct'] = floatval($item['pct']);
				$count++;
			}
			$result['list'] = $items;
			$result['count'] = $count;
			$result['stat'] = true;
		} else {
			$result['stat'] = false;
		}
		return $result;
	}
	function checkCourseStudent($table,$coid) {
		$check = $this->findCourseComponents($coid);
		if ($check['stat']==false) {
			$this->throw_debug('checkCourseStudent find error!');
		}
		if (!$this->table_exists($table)) {
			$tdata = array();
			array_push($tdata,
				array("name"=>"id","type"=>"INTEGER PRIMARY KEY"),
				array("name"=>"stid","type"=>"INTEGER UNIQUE"),
				array("name"=>"lgrp","type"=>"TEXT"),
				array("name"=>"mgrp","type"=>"TEXT"),
				array("name"=>"flag","type"=>"INTEGER"));
			foreach ($check['list'] as $item) {
				$name = preg_replace('/\s+/','_',$item['name']);
				$name = strtolower(preg_replace('/-/','_',$name));
				array_push($tdata,array("name"=>$name,"type"=>"REAL"));
			}
			$tmore = array();
			array_push($tmore,
				"FOREIGN KEY(stid) REFERENCES students(id)");
			$this->table_create($table,$tdata,$tmore);
		} else {
			// find columns
			$find = array();
			$prep = "pragma table_info(".$table.")";
			$stmt = $this->query($prep);
			$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if (!$list) {
				$this->throw_debug('checkCourseMarkTable findcol error');
			}
			$stmt->closeCursor();
			foreach ($list as $item) {
				array_push($find,$item['name']);
			}
			// check columns
			$tdata = array();
			foreach ($check['list'] as $item) {
				$name = preg_replace('/\s+/','_',$item['name']);
				$name = strtolower(preg_replace('/-/','_',$name));
				if (array_search($name,$find)===false) {
					array_push($tdata,array("name"=>$name,"type"=>"REAL"));
				}
			}
			if (!empty($tdata))
				$this->table_addcol($table,$tdata);
		}
	}
	function findCourseStudent($table,$stid=null) {
		$result = [];
		$prep = "SELECT id,stid,lgrp,mgrp,flag FROM ".$table;
		if ($stid!=null) {
			$prep = $prep." WHERE stid=?";
			$stid = intval($stid);
		}
		$stmt = $this->prepare($prep);
		if ($stid!=null) {
			if (!$stmt->bindValue(1,$stid,PDO::PARAM_INT)) {
				$this->throw_debug('findCourseStudent bind error!');
			}
		}
		if (!$stmt->execute()) {
			$this->throw_debug('findCourseStudent execute error!');
		}
		$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($list!=false) {
			foreach ($list as $item) {
				$item['id'] = intval($item['id']);
			}
			$result['list'] = $list;
			$result['stat'] = true;
		} else {
			$result['stat'] = false;
		}
		return $result;
	}
}
?>

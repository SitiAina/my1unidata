<?php
require_once dirname(__FILE__).'/Base.php';
define('UNIDATA_FILE','unidata.sqlite');
class UniData extends Base {
	protected $_sessem; // session semester YYYYNNNNS format
	protected $_doskip; // skip column with these names
	protected $_userid;
	protected $_doname;
	function __construct($sessem,$dbfile=UNIDATA_FILE) {
		parent::__construct($dbfile);
		$this->_sessem = intval($sessem); // should i check for format?
		$this->_doskip = [ 'stid','matrik','nama','name','id','prog',
			'lgrp','flag','grp','lab' ];
		$this->_userid = null;
		$this->_doname = null;
	}
	function validate($username, $userpass) {
		// hashing done by clients
		$prep = "SELECT id FROM students WHERE unid=? AND pass=?";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$username,PDO::PARAM_STR)||
				!$stmt->bindValue(2,$userpass,PDO::PARAM_STR)) {
			$this->throw_debug('Validate bind error!');
		}
		if (!$stmt->execute()) {
			$this->throw_debug('Validate execute error!');
		}
		$item = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($item==false) return false;
		$this->_userid = intval($item['id']);
		$this->_doname = $username;
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
			$result['ok'] = true;
			$result['id'] = intval($item['id']); // make sure an integer?
			$result['flag'] = intval($item['flag']);
		} else {
			$result['ok'] = false;
		}
		return $result;
	}
	function createStudent($unid,$name,$nrid) {
		$name = strtoupper($name);
		$nrid = strtoupper($nrid);
		$hash = hash('sha512',$nrid,false);
		$prep = "INSERT INTO students (unid,pass,name,nrid,flag) ".
			"VALUES (:unid,:pass,:name,:nrid,1)";
		$stmt = $this->prepare($prep);
		$stmt->bindValue(':unid',$unid,PDO::PARAM_STR);
		$stmt->bindValue(':pass',$hash,PDO::PARAM_STR);
		$stmt->bindValue(':name',$name,PDO::PARAM_STR);
		$stmt->bindValue(':nrid',$nrid,PDO::PARAM_STR);
		$temp = $stmt->execute();
		$stmt->closeCursor();
		if ($temp==false) {
			$this->throw_debug('createStudent Failed');
		}
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
			$result['ok'] = true;
			$result['id'] = intval($item['id']); // make sure an integer?
			$result['unit'] = intval($item['unit']);
			$result['flag'] = intval($item['flag']);
		} else {
			$result['ok'] = false;
		}
		return $result;
	}
	function createCourse($code,$name,$unit) {
		$code = strtoupper($code);
		$name = strtoupper($name);
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
	function checkCoursesStudents() {
		$table = "courses_students";
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
				"FOREIGN KEY(stid) REFERENCES students(id)");
			$this->table_create($table,$tdata,$tmore);
		}
	}
	function findCourseStudent($coid,$stid) {
		$result = [];
		$prep = "SELECT id, flag FROM courses_students WHERE coid=? ";
		$prep = $prep."AND stid=? AND ssem=?";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$coid,PDO::PARAM_INT)||
				!$stmt->bindValue(2,$stid,PDO::PARAM_INT)||
				!$stmt->bindValue(3,$this->_sessem,PDO::PARAM_INT)) {
			$this->throw_debug('findCourseStudent bind error!');
		}
		if (!$stmt->execute()) {
			$this->throw_debug('findCourseStudent execute error!');
		}
		$item = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($item!=false) {
			$result = $item;
			$result['ok'] = true;
			$result['id'] = intval($item['id']); // make sure an integer?
			$result['flag'] = intval($item['flag']);
		} else {
			$result['ok'] = false;
		}
		return $result;
	}
	function createCourseStudent($coid,$stid) {
		$coid = intval($coid);
		$stid = intval($stid);
		$prep = "INSERT INTO courses_students (ssem,coid,stid,flag) ".
			"VALUES (:ssem,:coid,:stid,1)";
		$stmt = $this->prepare($prep);
		$stmt->bindValue(':ssem',$this->_sessem,PDO::PARAM_INT);
		$stmt->bindValue(':coid',$coid,PDO::PARAM_INT);
		$stmt->bindValue(':stid',$stid,PDO::PARAM_INT);
		$temp = $stmt->execute();
		$stmt->closeCursor();
		if ($temp==false) {
			$this->throw_debug('createCourseStudent Failed');
		}
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
				array("name"=>"flag","type"=>"INTEGER")
			);
			$tmore = array();
			array_push($tmore,"UNIQUE (ssem,coid,name)",
				"FOREIGN KEY(coid) REFERENCES courses(id)");
			$this->table_create($table,$tdata,$tmore);
		}
	}
	function findCourseComponents($coid,$name=null) {
		$result = [];
		$prep = "SELECT id, name, raw, pct, grp, flag FROM courses_components ";
		$prep = $prep."WHERE coid=? AND ssem=?";
		if ($name!=null) {
			$prep = $prep." AND name=?";
			$name = strtolower($name);
		}
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
				$item['raw'] = floatval($item['raw']);
				$item['pct'] = floatval($item['pct']);
				$count++;
			}
			$result['data'] = $items;
			$result['count'] = $count;
			$result['ok'] = true;
		} else {
			$result['ok'] = false;
		}
		return $result;
	}
	function createCourseComponent($coid,$name,$raw,$pct,$grp) {
		$coid = intval($coid);
		$name = strtolower($name);
		$grp = intval($grp);
		$raw = floatval($raw);
		$pct = floatval($pct);
		$prep = "INSERT INTO courses_components ";
		$prep = $prep."(ssem,coid,name,raw,pct,grp,flag) ";
		$prep = $prep."VALUES (:ssem,:coid,:name,:raw,:pct,:grp,1)";
		$stmt = $this->prepare($prep);
		$stmt->bindValue(':ssem',$this->_sessem,PDO::PARAM_INT);
		$stmt->bindValue(':coid',$coid,PDO::PARAM_INT);
		$stmt->bindValue(':name',$name,PDO::PARAM_STR);
		$stmt->bindValue(':raw',$raw,PDO::PARAM_STR); // no PARAM_REAL!
		$stmt->bindValue(':pct',$pct,PDO::PARAM_STR);
		$stmt->bindValue(':grp',$grp,PDO::PARAM_INT);
		$temp = $stmt->execute();
		$stmt->closeCursor();
		if ($temp==false) {
			$this->throw_debug('createCourseComponent Failed');
		}
	}
	function checkCourseMarkTable($table,$coid) {
		$check = $this->findCourseComponents($coid);
		if ($check['ok']==false) {
			$this->throw_debug('checkCourseMarkTable find error!');
		}
		if (!$this->table_exists($table)) {
			$tdata = array();
			array_push($tdata,
				array("name"=>"id","type"=>"INTEGER PRIMARY KEY"),
				array("name"=>"stid","type"=>"INTEGER UNIQUE"),
				array("name"=>"name","type"=>"TEXT"),
				array("name"=>"prog","type"=>"TEXT"),
				array("name"=>"lgrp","type"=>"TEXT"),
				array("name"=>"flag","type"=>"INTEGER"));
			foreach ($check['data'] as $item) {
				array_push($tdata,
					array("name"=>$item['name'],"type"=>"REAL"));
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
			foreach ($check['data'] as $item) {
				if (array_search($item['name'],$find)===false) {
					array_push($tdata,
						array("name"=>$item['name'],"type"=>"REAL"));
				}
			}
			if (!empty($tdata))
				$this->table_addcol($table,$tdata);
		}
	}
	function findCourseMarkStudent($table,$stid) {
		$result = [];
		$prep = "SELECT id FROM ".$table." WHERE stid=?";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$stid,PDO::PARAM_STR)) {
			$this->throw_debug('findCourseMarkStudent bind error!');
		}
		if (!$stmt->execute()) {
			$this->throw_debug('findCourseMarkStudent execute error!');
		}
		$item = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($item!=false) {
			$result['ok'] = true;
			$result['id'] = intval($item['id']);
		} else {
			$result['ok'] = false;
		}
		return $result;
	}
	function updateCourseMarkStudent($table,$stid,$col,$val) {
		$prep = "UPDATE ".$table." SET ".$col."=".$val." WHERE stid=?";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$stid,PDO::PARAM_INT)) {
			$this->throw_debug('updateCourseMarkStudent bind error!');
		}
		if (!$stmt->execute()) {
			$this->throw_debug('updateCourseMarkStudent execute error!');
		}
	}
	function updateCourseMarkStudentS($table,$stid,$col,$val) {
		$prep = "UPDATE ".$table." SET ".$col."='".$val."' WHERE stid=?";
		$stmt = $this->prepare($prep);
		if (!$stmt->bindValue(1,$stid,PDO::PARAM_INT)) {
			$this->throw_debug('updateCourseMarkStudentS bind error!');
		}
		if (!$stmt->execute()) {
			$this->throw_debug('updateCourseMarkStudentS execute error!');
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
		if ($test['ok'] == false) {
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
	function importCSV_CourseInfo($fname,$lcoid,$lname,$lunit) {
		require_once dirname(__FILE__).'/FileText.php';
		$file = new FileText();
		$data = $file->loadCSV($fname);
		if ($data['error']===true) {
			$this->throw_debug('Cannot load course info list!');
		} else if ($data['rows']==0) {
			$this->throw_debug('No course info in list!');
		}
		// check loaded course list
		$icoid = -1; $iname = -1; $iunit = -1; $count = 0;
		$lcoid = strtoupper($lunid);
		$lname = strtoupper($lname);
		$lunit = strtoupper($lunit);
		foreach ($data['headline'] as $head) {
			if($head===$lcoid) {
				$icoid = $count;
			}
			if($head===$lname) {
				$iname = $count;
			}
			if($head===$lunit) {
				$iunit = $count;
			}
			$count++;
		}
		// make sure info headers found!
		if ($icoid<0||$iname<0||$iunit<0) {
			$this->throw_debug('Cannot find info to create course!');
		}
		// find course in database
		if ($this->checkCourses()==false) {
			$this->throw_debug('Cannot create table for courses!');
		}
		// do your thingy!
		foreach ($data['dataline'] as $line) {
			$code = trim($line[$icoid]);
			$name = trim($line[$iname]);
			$unit = intval($line[$iunit]);
			$info = $this->findCourse($code);
			if ($info['ok']==false) {
				$this->createCourse($code,$name,$unit);
				$info = $this->findCourse($code);
				if ($info['ok']==false) {
					$this->throw_debug('Something is WRONG!');
				}
			}
			// create course component?
			$count = 0; $cdata = array();
			foreach ($data['headline'] as $head) {
				if ($iunid!=$count&&$inrid!=$count&&$iname!=$count||
						array_search($head,$this->_doskip)==false) {
					// create component
					if ($line[$count]!="") {
						$item['col'] = $head;
						$item['val'] = floatval($line[$count]);
						array_push($cdata,$item);
					}
				}
				$count++;
			}
		}
	}
	function importCSV_CourseMark($fname,$lunid,$lnrid,$lname) {
		require_once dirname(__FILE__).'/FileText.php';
		$file = new FileText();
		$data = $file->loadCSV($fname);
		if ($data['error']===true) {
			$this->throw_debug('Cannot load student list!');
		} else if ($data['rows']==0) {
			$this->throw_debug('No student in list!');
		}
		// first column headline should be course id (@course code)!
		$name = strtoupper($data['headline'][0]);
		$name = explode(' - ',$name);
		$code = trim(array_shift($name));
		$name = trim(array_shift($name));
		// find course in database
		$this->checkCourses();
		$info = $this->findCourse($code);
		if ($info['ok']==false) {
			// create course with zero unit - modify later!
			$this->createCourse($code,$name,0);
			$info = $this->findCourse($code);
			if ($info['ok']==false) {
				$this->throw_debug('Something is WRONG!');
			}
		}
		// check loaded student list
		$iunid = -1; $inrid = -1; $iname = -1; $count = 0;
		$lunid = strtoupper($lunid);
		$lnrid = strtoupper($lnrid);
		$lname = strtoupper($lname);
		foreach ($data['headline'] as $head) {
			if($head===$lunid) {
				$iunid = $count;
			}
			if($head===$lnrid) {
				$inrid = $count;
			}
			if($head===$lname) {
				$iname = $count;
			}
			$count++;
		}
		if ($iunid<0||$iname<0) {
			$this->throw_debug('Cannot find label for student id/name!');
		}
		// check students tables in database
		$this->checkStudents();
		$this->checkCoursesStudents();
		// browse each student?
		foreach ($data['dataline'] as $line) {
			$line[$iunid] = trim($line[$iunid]);
			$line[$iname] = trim($line[$iname]);
			$stud = $this->findStudent($line[$iunid]);
			if ($stud['ok']==false) {
				if ($inrid<0) {
					$this->throw_debug('Not enough info to create student!');
				}
				$line[$inrid] = trim($line[$inrid]);
				$this->createStudent($line[$iunid],$line[$iname],$line[$inrid]);
				$stud = $this->findStudent($line[$iunid]);
				if ($stud['ok']==false) {
					$this->throw_debug('Something is WRONG!');
				}
			}
			$cost = $this->findCourseStudent($info['id'],$stud['id']);
			if ($cost['ok']==false) {
				$this->createCourseStudent($info['id'],$stud['id']);
				$cost = $this->findCourseStudent($info['id'],$stud['id']);
				if ($cost['ok']==false) {
					$this->throw_debug('Something is WRONG!');
				}
			}
		}
		// try to check valid course components
		$this->checkCoursesComponents();
		$list = array();
		$temp = array();
		$count = 0;
		foreach ($data['headline'] as $head) {
			$head = strtolower($head);
			if ($count>0&&$head!=""&&
					$iunid!=$count&&$inrid!=$count&&$iname!=$count&&
					array_search($head,$this->_doskip)==false) {
				$comp = $this->findCourseComponents($info['id'],$head);
				if ($comp['ok']==false) {
					$this->createCourseComponent($info['id'],$head,0.0,0.0,0);
					$comp = $this->findCourseComponents($info['id'],$head);
					if ($comp['ok']==false) {
						$this->throw_debug('Something is WRONG!');
					}
				}
				$item = array_shift($comp['data']);
				$item['idx'] = $count;
				array_push($list,$item);
			}
			else if ($head=='prog'||$head=='lgrp'||$head=='flag') {
				$item['col'] = $head;
				$item['idx'] = $count;
				array_push($temp,$item);
			}
			$count++;
		}
		// try to import all data
		$tname = $code.'_'.$this->_sessem;
		$this->checkCourseMarkTable($tname,$info['id']);
		// import in all data?
		foreach ($data['dataline'] as $line) {
			$rdat = array();
			array_push($rdat,
				array("col"=>"stid","val"=>$line[$iunid]),
				array("col"=>"name","val"=>$line[$iname]));
			foreach ($list as $item) {
				array_push($rdat,
					array("col"=>$item['name'],"val"=>$line[$item['idx']]));
			}
			foreach ($temp as $item) {
				array_push($rdat,
					array("sys"=>$item['col'],"val"=>$line[$item['idx']]));
			}
			$this->updateCourseMarkTable($tname,$rdat);
		}
		// cleanup?
		$file = null;
	}
}
?>

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
	function listStaff() {
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
				array("which"=>"T3.name","alias"=>"name"),
				array("which"=>"T3.unid","alias"=>"staff")
			);
			$more = array();
			array_push($more," FROM courses_staffs T1, courses T2, ",
				"staffs T3 WHERE T1.coid=T2.id AND T1.stid=T3.id");
			$this->view_create($view,$data,$more);
		}
	}
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
	function listCourseStaff($user=null,$code=null) {
		if ($user!=null) $user = strtoupper($user);
		else if ($code!=null) $code = strtoupper($code);
		$result = [];
		$prep = "SELECT * FROM  courses_staffs_view";
		if ($user!=null) $prep = $prep." WHERE staff=?";
		else if ($code!=null) $prep = $prep." WHERE course=?";
		$prep = $prep." ORDER BY ssem DESC, COURSE ASC";
		$stmt = $this->prepare($prep);
		if ($user!=null) {
			if (!$stmt->bindValue(1,$user,PDO::PARAM_STR)) {
				$this->throw_debug('listCourseStaff bind error!');
			}
		}
		else if ($code!=null) {
			if (!$stmt->bindValue(1,$code,PDO::PARAM_STR)) {
				$this->throw_debug('listCourseStaff bind error!');
			}
		}
		if (!$stmt->execute()) {
			$this->throw_debug('listCourseStaff execute error!');
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
	function validateCourseComponents($coid) {
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

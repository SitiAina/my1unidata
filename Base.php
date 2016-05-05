<?php
error_reporting(E_ALL);
define('DEFAULT_DBPATH',dirname(__FILE__).'/');
class Base {
	protected $_dbmain;
	protected $_dbfile;
	function __construct($dbfile,$dbpath=DEFAULT_DBPATH) {
		try {
			$dbfull = $dbpath.$dbfile;
			$this->_dbmain = new PDO("sqlite:".$dbfull);
			$this->_dbmain->setAttribute(PDO::ATTR_PERSISTENT,true);
			$this->_dbmain->setAttribute(PDO::ATTR_ERRMODE,
				PDO::ERRMODE_EXCEPTION);
		} catch ( Exception $error ) {
			$this->throw_debug($error->getMessage());
		}
		$this->_dbfile = $dbfull;
	}
	protected function throw_debug($error) {
		throw new Exception('['.get_class($this).'] => {'.$error.'}');
	}
	protected function prepare($query) {
		try { // returns PDOStatement or FALSE
			$result = $this->_dbmain->prepare($query);
		} catch ( PDOException $error ) {
			$this->throw_debug('Cannot prepare query! ('.
				$error->getMessage().')');
		}
		return $result;
	}
	protected function query($query) {
		try { // returns PDOStatement or FALSE
			$result = $this->_dbmain->query($query);
		} catch ( PDOException $error ) {
			$this->throw_debug('Cannot execute query! ('.
				$error->getMessage().')');
		}
		return $result;
	}
	protected function table_exists($table) {
		// check if table exists
		$prep = "SELECT name FROM sqlite_master WHERE type='table' ".
			"AND name='".$table."'";
		$stmt = $this->prepare($prep);
		if (!$stmt->execute()) {
			$this->throw_debug('CheckE error!');
		}
		$item = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($item==false) return false;
		return true;
	}
	protected function table_create($table,$tdata,$tmore=null) {
		// must exists
		if (empty($table)||empty($tdata)||!is_array($tdata)||
				(isset($tmore)&&!is_array($tmore))) {
			$this->throw_debug('Not enough data!');
		}
		// create table
		$prep = "CREATE TABLE IF NOT EXISTS ".$table." ( ";
		foreach ($tdata as $cols) {
			if (!array_key_exists('name',$cols)||
					!array_key_exists('type',$cols)) {
				$this->throw_debug('Invalid format!');
			}
			$prep = $prep.$cols['name']." ".strtoupper($cols['type']).", ";
		}
		if (isset($tmore)) {
			foreach ($tmore as $xtra) {
				$prep = $prep.$xtra.", ";
			}
		}
		$prep = substr($prep,0,-2);
		$prep = $prep." )";
		$stmt = $this->prepare($prep);
		if (!$stmt->execute()) {
			$this->throw_debug('Create error!');
		}
	}
	protected function table_delete($table) {
		// must exists
		if (empty($table)) {
			$this->throw_debug('Not enough data!');
		}
		// delete table
		$prep = "DROP TABLE IF EXISTS ".$table;
		$stmt = $this->prepare($prep);
		if (!$stmt->execute()) {
			$this->throw_debug('Delete error!');
		}
	}
	protected function table_addcol($table,$tdata) {
		// must exists
		if (empty($table)||empty($tdata)) {
			$this->throw_debug('Not enough data!');
		}
		// alter table
		$init = "ALTER TABLE ".$table." ADD COLUMN ";
		foreach ($tdata as $cols) {
			if (!array_key_exists('name',$cols)||
					!array_key_exists('type',$cols)) {
				$this->throw_debug('Invalid format!');
			}
			$prep = $init.$cols['name']." ".strtoupper($cols['type']).", ";
			$stmt = $this->prepare($prep);
			if (!$stmt->execute()) {
				$this->throw_debug('AddCol error!');
			}
			$stmt->closeCursor();
		}
	}
	protected function view_exists($view) {
		// check if view exists
		$prep = "SELECT name FROM sqlite_master WHERE type='view' ".
			"AND name='".$view."'";
		$stmt = $this->prepare($prep);
		if (!$stmt->execute()) {
			$this->throw_debug('CheckE error!');
		}
		$item = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($item==false) return false;
		return true;
	}
	protected function view_create($view,$data,$more) {
		// must exists
		if (empty($view)||empty($data)||!is_array($data)||
				(isset($more)&&!is_array($more))) {
			$this->throw_debug('Not enough data!');
		}
		// create view
		$prep = "CREATE VIEW IF NOT EXISTS ".$view." AS SELECT ";
		foreach ($data as $cols) {
			if (!array_key_exists('which',$cols)) {
				$this->throw_debug('Invalid format!');
			}
			$prep = $prep.$cols['which'];
			if (array_key_exists('alias',$cols)) {
				$prep = $prep." AS ".$cols['alias'];
			}
			$prep = $prep.", ";
		}
		$prep = substr($prep,0,-2);
		foreach ($more as $xtra) {
			$prep = $prep.$xtra;
		}
		$stmt = $this->prepare($prep);
		if (!$stmt->execute()) {
			$this->throw_debug('Create error!');
		}
	}
	protected function view_delete($view) {
		// must exists
		if (empty($view)) {
			$this->throw_debug('Not enough data!');
		}
		// delete view
		$prep = "DROP VIEW IF EXISTS ".$view;
		$stmt = $this->prepare($prep);
		if (!$stmt->execute()) {
			$this->throw_debug('Delete error!');
		}
	}
}
?>

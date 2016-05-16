<?php
define('MAX_LINESIZE',1000);
define('DEFAULT_TFPATH',dirname(__FILE__));
define('DEFAULT_TFNAME','data.txt');
define('CSV_WITH_HEADER',1);
define('CSV_WITHOUT_HEADER',0);
class FileText {
	protected $_pfname;
	protected $content;
	function __construct($tfname=DEFAULT_TFNAME,$tfpath=DEFAULT_TFPATH) {
		if (!isset($this->_pfname)) {
			$this->file_name($tfname,$tfpath);
		}
	}
	// basic text file read/write functions
	public function file_name($name,$path=DEFAULT_TFPATH) {
		$this->_pfname = $path."/".$name;
	}
	public function file_read() {
		if (!file_exists($this->_pfname)) {
			$this->throw_debug(get_class($this).': File not found!');
		}
		return file_get_contents($this->_pfname);
	}
	public function file_check() {
		if (!file_exists($this->_pfname)) {
			touch($this->_pfname);
			chmod($this->_pfname,0777);
			return false;
		} else {
			return true;
		}
	}
	public function file_append($content) {
		$this->file_check();
		file_put_contents($this->_pfname,$content,LOCK_EX|FILE_APPEND);
	}
	public function file_write($content) {
		$this->file_check();
		file_put_contents($this->_pfname,$content,LOCK_EX);
	}
	// load CSV format... optional header line
	public function loadCSV($fname,$start=CSV_WITH_HEADER) {
		$do_ext = true;
		if (!isset($fname)) {
			$fname = $this->_pfname;
			$do_ext = false;
		}
		$index = 0; $count = 0;
		$result = [];
		$result['dataline'] = []; // need this for first array_push
		if (file_exists($fname)&&($pfile = fopen($fname,'r'))!==false)
		{
			while (($cols = fgetcsv($pfile, MAX_LINESIZE))!== FALSE) {
				if ($index>=$start) {
					// get items
					$dataline = [];
					$loop = 0;
					foreach ($cols as $col) {
						array_push($dataline,$col);
						$loop++;
						if ($loop==$count) break;
					}
					while ($loop < $count) {
						array_push($dataline,null);
						$loop++;
					}
					array_push($result['dataline'],$dataline);
				} else {
					// get column names
					$headline = [];
					foreach ($cols as $col) {
						array_push($headline,$col);
						$count++;
					}
					$result['headline'] = $headline;
				}
				$index++;
			}
			fclose($pfile);
			$result['cols'] = $count;
			$result['rows'] = $index-$start;
			$result['stat'] = false;
		} else {
			$result['stat'] = true;
		}
		if ($do_ext==false) {
			$this->content = $result;
		} else {
			return $result;
		}
	}
	// send CSV format
	public function sendCSV($fname,$dhead,$ddata) {
		// header for CSV download
		header("Content-type: text/csv; charset=utf-8");
		header("Content-Disposition: attachment; filename=".$fname);
		header("Pragma: no-cache");
		header("Expires: 0");
		// row for headers
		if (isset($dhead)) {
			echo implode(',',$dhead)."\n";
		}
		// rows of data
		foreach ($ddata as $data) {
			echo implode(',',$data)."\n";
		}
		exit();
	}
}
?>

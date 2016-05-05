<?php
/**
 * upload.php
 * - simple php script to upload a single file
**/

define('UPLOAD_PATH',dirname(__FILE__).'/uploads');
define('STUDENT_LIST','list.txt');
define('MAX_LINESIZE',1000);
define('HDR_IDLABEL','MATRIK');
define('HDR_CHKNAME','NAMA');

function loadcsv($fname)
{
	$index = 0; $count = 0;
	$result = [];
	$result['dataline'] = [];
	if (($pfile = fopen($fname,'r')) !== false) {
		while (($cols = fgetcsv($pfile, MAX_LINESIZE))!== FALSE) {
			if ($index>0) {
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
	}
	$result['cols'] = $count;
	$result['rows'] = $index-1; // minus header
	return $result;
}

try {

	// just in case!?
	session_destroy();
	// from php.net
	if (!isset($_FILES['fileToUpload']['error']) ||
			is_array($_FILES['fileToUpload']['error'])) {
		throw new RuntimeException('Invalid parameters!');
	}
	switch ($_FILES['fileToUpload']['error']) {
		case UPLOAD_ERR_OK:
			break;
		case UPLOAD_ERR_NO_FILE:
			throw new RuntimeException('No file sent.');
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			throw new RuntimeException('Exceeded filesize limit.');
		default:
			throw new RuntimeException('Unknown errors.');
	}

	// enforce these!
	if ($_FILES["fileToUpload"]["size"] == 0) {
		throw new RuntimeException('Empty file?!');
	}
	if (empty($_POST["studentId"])) {
		throw new RuntimeException('Please enter ID!');
	}
	if (empty($_POST["nickName"])) {
		throw new RuntimeException('Please enter nick name!');
	}
	if(strpos($_POST["nickName"],' ')!==false) {
		throw new RuntimeException('Nick name must not contain spaces!');
	}

	// must have student list!
	$data = loadcsv(STUDENT_LIST);
	if ($data['rows']==0) {
		throw new RuntimeException('Student list NOT found!');
	}

	$check_id = null;
	$check_yo = null;
	// check submission
	if (isset($_POST["submit"])) {
		// check student list?
		$index = 0; $check = 0; $count = 0;
		foreach ($data['headline'] as $head) {
			if($head===HDR_IDLABEL) break;
			$index++;
		}
		foreach ($data['headline'] as $head) {
			if($head===HDR_CHKNAME) $check = $count;
			$count++;
		}
		if ($check==$count) $check = $index;
		foreach ($data['dataline'] as $line) {
			if ($line[$index]==$_POST["studentId"]) {
				$check_id = $line[$index];
				$check_yo = $line[$check];
				break;
			}
		}
	} else {
		throw new RuntimeException('No submission?');
	}

	// make sure there is a valid id!
	if ($check_id===null) {
		throw new RuntimeException('Cannot find ID! ('.$check_id.')');
	}

	$thatname = "_".$_SERVER['REMOTE_ADDR'];
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$thatname = $thatname."_".$_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	$thatname = $thatname."_".$_POST["nickName"];
	$thatname = $thatname."_".$_FILES["fileToUpload"]["name"];

	// create target filename
	$thisfile = UPLOAD_PATH."/".$check_id.$thatname;

	if (file_exists($thisfile)) {
		throw new RuntimeException('File exists!');
	}

	if ($_FILES["fileToUpload"]["size"] > 500000) {
		throw new RuntimeException('File too large!');
	}

	// make sure target path exists
	if (!file_exists(UPLOAD_PATH)) {
		if (!mkdir(UPLOAD_PATH)) {
			throw new RuntimeException("Cannot create upload path!");
		}
		chmod(UPLOAD_PATH,0777);
	}

	header('Content-Type: text/html; charset=utf-8');
	// greetings!
	echo "Hello, ".$_POST["nickName"]." @ ".$check_yo."!<br><br>";
	//echo "CHECK: '".$thisfile."'<br><br>";

	// do your thing!
	if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $thisfile)) {
		echo "The file ".basename($_FILES["fileToUpload"]["name"]).
			" successfully uploaded!";
		chmod($thisfile,0777);
	} else {
		echo "Sorry, there was an error uploading your file.";
	}
} catch (RuntimeException $e) {
	echo $e->getMessage();
}

?>

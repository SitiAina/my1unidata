<?php
require_once dirname(__FILE__).'/config.php';
try {
	session_start();
	if (!isset($_SESSION['username'])||!isset($_SESSION['userpass'])||
			!isset($_SESSION['usertype'])) {
		header('Location: login.php');
		exit();
	}
	$type = "UniData";
	if (intval($_SESSION['usertype'])==MY1STAFF_LOGIN) {
		$type = $type."Staff";
	}
	require_once dirname(__FILE__).'/'.$type.'.php';
	$data = new $type();
	$pass = $data->validateUser($_SESSION['username'],$_SESSION['userpass']);
	if ($pass===false)
		throw new Exception('Invalid login!');
	$user = $data->getProfile();
} catch (Exception $error) {
	session_destroy();
	if (DEBUG_MODE) {
		$message = $error->getMessage();
	} else {
		$message = "General Error!";
	}
	header('Content-Type: text/html; charset=utf-8');
	echo "<h1>Error</h1>".PHP_EOL.$message;
	exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<?php echo "<title>".MY1APP_TITLE."</title>\n"; ?>
</head>
<body>
<script id="js_main" type="text/javascript">
</script>
<?php echo "<h1>".MY1APP_TITLE."</h1>\n"; ?>
<p>
<?php echo "Hello, ".$user['name']; ?>
&nbsp;&nbsp;(<a href="logout.php">Logout</a>)</p>
<?php if ($user['staf']===true) : ?>
<form action="staffdo.php" method="post" enctype="multipart/form-data">
	<label>Staff ID</label><br>
<?php
	echo "\t<input type=\"text\" name=\"staffId\" id=\"staffid\"";
	echo "placeholder=\"".$user['unid']."\" disabled><br><br>\n";
?>
	<label>Select Data File (CSV)</label><br>
	<input type="file" name="dataFile" id="datafile"><br><br>
	<label>Select Command</label><br>
	<select id="aCommand" name="aCommand">
<?php
	echo "\t<option value=".TASK_STAFF_CREATE_STAFF.">Create Staff</option>\n";
	echo "\t<option value=".TASK_STAFF__STAFF.">View Staffs</option>\n";
?>
	</select><br><br>
	<input type="submit" value="Process Command" name="submit"><br><br>
</form>
<?php else : ?>
<label>Academic Session</label><br>
<select id="sesspick" name="asession">
<option value=20152016>Academic Session 2015/2016</option>
<option value=20142015>Academic Session 2014/2015</option>
</select><br><br>
<label>Semester</label><br>
<select id="sem_pick" name="semester">
<option value=1>Semester 1</option>
<option value=2>Semester 2</option>
</select><br><br>
<?php endif; ?>
</body>
</html>

<?php
/**
 * install.php
 * - installs a single root user to start things up
**/
try {
	header('Content-Type: text/html; charset=utf-8');
	$unid = "0100000"; /* ultimate user */
	$name = "root"; /* ultimate name :p */
	$nrid = "010101000101"; /* ultimate nrid? */
	require_once dirname(__FILE__).'/class/UniDataStaff.php';
	$test = new UniDataStaff();
	$test->checkStaff();
	$init = $test->findStaff($unid);
	if ($init['stat']===false) {
		$test->createStaff($unid,$name,$nrid,1);
	}
	echo "<h1>YAY!</h1>".PHP_EOL;
} catch( Exception $error ) {
	require_once dirname(__FILE__).'/config/config.php';
	if (DEBUG_MODE) {
		$message = $error->getMessage();
	} else {
		$message = "General Error!";
	}
	echo "<h1>NAY!</h1>".PHP_EOL.$message;
}
exit();
?>

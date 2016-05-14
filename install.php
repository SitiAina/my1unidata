<?php
/**
 * install.php
 * - install single root user to start things up
**/
try {
	$unid = "0100000"; /* ultimate user */
	$name = "root"; /* ultimate name :p */
	$nrid = "010101000101"; /* ultimate nrid? */
	require_once dirname(__FILE__).'/UniDataStaff.php';
	$test = new UniDataStaff();
	$test->checkStaff();
	$init = $test->findStaff($unid);
	if ($init['stat']===false) {
		$test->createStaff($unid,$name,$nrid);
	}
	header('Content-Type: text/html; charset=utf-8');
	echo "<h1>YAY!</h1>".PHP_EOL;
} catch( Exception $error ) {
	require_once dirname(__FILE__).'/config.php';
	if (DEBUG_MODE) {
		$message = $error->getMessage();
	} else {
		$message = "General Error!";
	}
	header('Content-Type: text/html; charset=utf-8');
	echo "<h1>NAY!</h1>".PHP_EOL.$message;
}
exit();
?>

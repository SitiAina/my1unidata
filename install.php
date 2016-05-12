<?php
/**
 * install.php
 * - install single root user to start things up
**/
try {
	$unid = "0100000"; /* ultimate user */
	$name = "root"; /* ultimate name :p */
	$nrid = "010101000101"; /* ultimate nrid? */
	header('Content-Type: text/html; charset=utf-8');
	require_once dirname(__FILE__).'/UniDataStaff.php';
	$test = new UniDataStaff(0);
	$test->checkStaff();
	$init = $test->findStaff($unid);
	if ($init['stat']===false) {
		$test->createStaff($unid,$name,$nrid);
	}
	echo "<h1>YAY!</h1>".PHP_EOL;
} catch( Exception $error ) {
	echo "<h1>NAY!</h1>".PHP_EOL;
/**
	echo "<h1>NAY!</h1> (".$error->getMessage().")".PHP_EOL;
**/
}
?>

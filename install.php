<?php
/**
 * install.php
 * - installs a single root user to start things up
**/
try {
	if (PHP_SAPI !== 'cli') { // or php_sapi_name()
		// returns html only if NOT on console?
		header('Content-Type: text/html; charset=utf-8');
		echo "<h1><p>Invalid access!</p></h1>".PHP_EOL;
		exit();
	}
	// create first user
	$unid = "0100000"; /* ultimate user */
	$name = "root"; /* ultimate name :p */
	$nrid = "010101000101"; /* ultimate nrid? */
	// check parameter
	for ($loop=1;$loop<$argc;$loop++) {
		if ($argv[$loop]==='--unid'&&$loop<$argc-1) {
			$unid = $argv[++$loop];
		} else if ($argv[$loop]==='--name') {
			$name = $argv[++$loop];
		} else if ($argv[$loop]==='--nrid') {
			$nrid = $argv[++$loop];
		}
	}
	require_once dirname(__FILE__).'/class/UniDataStaff.php';
	$test = new UniDataStaff();
	$test->checkStaffs();
	$init = $test->findStaff($unid);
	if ($init['stat']===false) {
		$test->createStaff($unid,$name,$nrid,1);
	}
	echo "Installation completed!".PHP_EOL;
} catch( Exception $error ) {
	echo "Installation error! [".$error->getMessage()."]".PHP_EOL;
}
exit();
?>

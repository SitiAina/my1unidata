<?php
/**
 * install.php
 * - simple script to create forlder for uploaded files
**/
define('UPLOAD_PATH',dirname(__FILE__).'/uploads');
try {
	if (!file_exists(UPLOAD_PATH)) {
		mkdir(UPLOAD_PATH);
	}
	chmod(UPLOAD_PATH,0777);
	header('Content-Type: text/html; charset=utf-8');
	echo "<h1>You can now remove this install.php file!</h1>\n";
} catch( Exception $error ) {
	die(basename(__FILE__).": ".$error->getMessage());
}
?>

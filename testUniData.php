<?php
try {
	header('Content-Type: text/html; charset=utf-8');
	require_once dirname(__FILE__).'/UniData.php';
	$test = new UniData(201520161);
	$test->importCSV_CourseMark('pgt302-201516s1_admin.csv','matrik','id','nama');
	echo "<h1>YAY!</h1>".PHP_EOL;
} catch( Exception $error ) {
	echo "<h1>NAY!</h1> (".$error->getMessage().")".PHP_EOL;
}
?>

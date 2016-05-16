<?php
require_once dirname(__FILE__).'/config/config.php';
try {
	if ($_SERVER['REQUEST_METHOD']=='POST') {
		if (array_key_exists('username',$_POST)&&
				array_key_exists('password',$_POST)&&
				array_key_exists('usertype',$_POST)) {
			$username = $_POST['username'];
			$userpass = $_POST['password'];
			$usertype = $_POST['usertype'];
			session_start();
			$_SESSION['username'] = $username;
			$_SESSION['userpass'] = $userpass;
			$_SESSION['usertype'] = $usertype;
			header('Location: work.php');
			exit();
		} else if (array_key_exists('user',$_POST)&&
				array_key_exists('pass',$_POST)&&
				array_key_exists('pasX',$_POST)&&
				array_key_exists('pasY',$_POST)) {
			if ($_POST['pasX']!=$_POST['pasY']) {
				throw new Exception('Mismatched password!');
			}
			$username = $_POST['user'];
			$pass_old = $_POST['pass'];
			$pass_new = $_POST['pasX'];
			session_start();
			if (!isset($_SESSION['username'])||
					!isset($_SESSION['userpass'])||
					!isset($_SESSION['usertype'])) {
				throw new Exception('Invalid session!');
			}
			if (($username!=$_SESSION['username'])||
					($pass_old!=$_SESSION['userpass'])) {
				throw new Exception('Verification failed!');
			}
			$_SESSION['pass_new'] = $pass_new;
			header('Location: work.php?do=chpass');
			exit();
		} else if (isset($_GET['do'])&&$_GET['do']=='command') {
			require_once dirname(__FILE__).'/class/PageLoad.php';
			$page = new PageLoad();
			$page->Show();
		} else {
			throw new Exception('Invalid Post!');
		}
	}
	session_start();
	if (!isset($_SESSION['username'])||!isset($_SESSION['userpass'])||
			!isset($_SESSION['usertype'])) {
		if (isset($_GET['do'])&&$_GET['do']=='login') {
			require_once dirname(__FILE__).'/class/PageLogin.php';
			$page = new PageLogin();
			$page->Show();
		} else {
			session_destroy();
			require_once dirname(__FILE__).'/class/PageInit.php';
			$page = new PageInit();
			$page->Show();
		}
	}
	if (isset($_GET['do'])) {
		if ($_GET['do']=='dopass') {
			require_once dirname(__FILE__).'/class/PagePass.php';
			$page = new PagePass();
			$page->Show();
		} else if ($_GET['do']=='chpass') {
			require_once dirname(__FILE__).'/class/PagePassCh.php';
			$page = new PagePassCh();
			$page->Show();
		} else if ($_GET['do']=='command') {


		} else if ($_GET['do']=='viewstaff') {
			require_once dirname(__FILE__).'/class/PageStaff.php';
			$page = new PageStaff();
			if (isset($_GET['fmt'])&&$_GET['fmt']=='csv')
				$page->sendCSV();
			else
				$page->Show();
		} else if ($_GET['do']=='viewcourse') {
			require_once dirname(__FILE__).'/class/PageCourse.php';
			$page = new PageCourse();
			if (isset($_GET['fmt'])&&$_GET['fmt']=='csv')
				$page->sendCSV();
			else
				$page->Show();
		} else {
			throw new Exception('Invalid Work!');
		}
	} else {
		// do main page?
		require_once dirname(__FILE__).'/class/PageMain.php';
		$page = new PageMain($data);
		$page->Show();
	}
} catch (Exception $error) {
	if (DEBUG_MODE) {
		$message = $error->getMessage();
	} else {
		$message = "General Error!";
		session_start();
		session_destroy();
	}
	header('Content-Type: text/html; charset=utf-8');
	echo "<h1>Error</h1>".PHP_EOL.$message;
}
exit();
?>

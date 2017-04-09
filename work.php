<?php
require_once dirname(__FILE__).'/config/config.php';
try {
	if ($_SERVER['REQUEST_METHOD']=='POST') {
		// determine what the post is about
		if (array_key_exists('username',$_POST)&&
				array_key_exists('password',$_POST)&&
				array_key_exists('usertype',$_POST)) {
			// login info - not authenticated here, just start a session
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
			// change password - not authenticated here, just update session
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
		} else if (array_key_exists('userId',$_POST)&&
				array_key_exists('aCommand',$_POST)) {
			// data upload
			$_POST["staffId"] = $_POST["userId"];
			require_once dirname(__FILE__).'/class/PageLoad.php';
			$page = new PageLoad();
			$page->Show();
		} else if (array_key_exists('cCode',$_POST)&&
				array_key_exists('cName',$_POST)&&
				array_key_exists('cUnit',$_POST)&&
				array_key_exists('cCoId',$_POST)) {
			if (empty($_POST['cCode'])||empty($_POST['cName'])||
					empty($_POST['cUnit'])||empty($_POST['cCoId'])) {
				throw new Exception('Invalid course info?!');
			}
			require_once dirname(__FILE__).'/class/PageEdit.php';
			$page = new PageEdit($_POST['cCoId'],$_POST['cCode'],
				$_POST['cName'],$_POST['cUnit']);
			$page->Show();
		} else {
			throw new Exception('Invalid Post!');
		}
	}
	session_start();
	if (!isset($_SESSION['username'])||!isset($_SESSION['userpass'])||
			!isset($_SESSION['usertype'])) {
		// if no login info in this session, it's init or login request
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
	// check if it is a command (do something?)
	if (isset($_GET['do'])) {
		if ($_GET['do']=='dopass') {
			// page to allow user to change passsword
			require_once dirname(__FILE__).'/class/PagePass.php';
			$page = new PagePass();
			$page->Show();
		} else if ($_GET['do']=='chpass') {
			// page to actually change the password
			require_once dirname(__FILE__).'/class/PagePassCh.php';
			$page = new PagePassCh();
			$page->Show();
		} else if ($_GET['do']=='viewstaff') {
			// page to view/download list of staffs
			require_once dirname(__FILE__).'/class/PageStaff.php';
			$page = new PageStaff();
			if (isset($_GET['fmt'])&&$_GET['fmt']=='csv')
				$page->sendCSV();
			else
				$page->Show();
		} else if ($_GET['do']=='viewcourse') {
			// page to view/download list of courses
			require_once dirname(__FILE__).'/class/PageCourse.php';
			$page = new PageCourse();
			if (isset($_GET['fmt'])&&$_GET['fmt']=='csv')
				$page->sendCSV();
			else
				$page->Show();
		} else if ($_GET['do']=='editcourse') {
			if (!isset($_GET['code'])) {
				throw new Exception('Invalid course edit!');
			}
			require_once dirname(__FILE__).'/class/PageCourseEdit.php';
			$page = new PageCourseEdit($_GET['code']);
			$page->Show();
		} else if ($_GET['do']=='impcourse') {
			if (!isset($_GET['ssem'])||!isset($_GET['code'])) {
				throw new Exception('Invalid course implementation!');
			}
			require_once dirname(__FILE__).'/class/PageSemCourse.php';
			$page = new PageSemCourse($_GET['ssem'],$_GET['code']);
			if (isset($_GET['fmt'])&&$_GET['fmt']=='csv')
				$page->sendCSV();
			else
				$page->Show();
		} else if ($_GET['do']=='editccmp') {
			if (!isset($_GET['ssem'])||!isset($_GET['code'])||
					!isset($_GET['ccmp'])) {
				throw new Exception('Invalid course component edit!');
			}
			require_once dirname(__FILE__).'/class/PageSemCourseEdit.php';
			$page = new PageSemCourseEdit($_GET['ssem'],
				$_GET['code'],$_GET['ccmp']);
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

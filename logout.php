<?php
session_start();
session_destroy();
header('Location: work.php');
exit();
?>

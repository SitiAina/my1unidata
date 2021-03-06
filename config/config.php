<?php
// allow local config file to override
include_once dirname(__FILE__).'/config/'.basename(__FILE__).'local';
// the config stuffs
define('DEBUG_MODE', true);
define('MY1APP_TITLE','MY1 UniData');
define('MY1STAFF_LOGIN',4);
define('HEADER_STAFF_UNID','STAFFID');
define('HEADER_STAFF_NRIC','NRIC');
define('HEADER_STAFF_NAME','NAME');
define('TASK_STAFF_CREATE_STAFF',1);
define('TASK_STAFF_CREATE_COURSE',2);
define('TASK_STAFF_EXECUTE_COURSE',3);
define('TASK_STAFF_ADD_STUDENTS',4);
define('HEADER_COURSE_CODE','CODE');
define('HEADER_COURSE_NAME','NAME');
define('HEADER_COURSE_UNIT','UNIT');
define('GROUP_COURSE_WORK',1);
define('GROUP_EXAMINATION',2);
define('HEADER_CCOMP_NAME','NAME');
define('HEADER_CCOMP_RAW_','RAWMARK');
define('HEADER_CCOMP_PCT_','PERCENTAGE');
define('HEADER_CCOMP_LABEL','LABEL');
define('HEADER_CCOMP_GROUP','GROUP');
define('HEADER_CCOMP_SUBGRP','SUBGROUP');
define('HEADER_CCOMP_INDEX','INDEX');
define('HEADER_STUDENT_UNID','STUDENTID');
define('HEADER_STUDENT_NAME','NAME');
define('HEADER_STUDENT_NRIC','NRIC');
define('HEADER_STUDENT_PROG','PROGRAM');
define('HEADER_STUDENT_LGRP','LGROUP');
define('HEADER_STUDENT_MGRP','MGROUP');
?>

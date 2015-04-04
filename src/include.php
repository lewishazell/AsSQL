<?php
// We want exceptions to be thrown in the queries (the system needs them for reporting errors to the user)
mysqli_report(MYSQLI_REPORT_ALL);
// Include all of the files in the library
require_once('AsSQL.php');
require_once('AsyncResult.php');
require_once('PollPool.php');
require_once('PollState.php');
?>

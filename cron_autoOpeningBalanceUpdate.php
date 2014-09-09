<?php

	include('common.php');
	
	include_once('includes/class.attendance.php');
	$objAttendance = new attendance();

	$openingLeaveBalanceUpdate = $objAttendance->fnSaveLeaveBalanceLog();

	$openingLeaveBalanceUpdate = $objAttendance->fnCopyOpeningBalance();

?>

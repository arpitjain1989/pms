<?php

	include('common.php');
	
	include_once('includes/class.leave.php');
	
	$objLeave = new leave();
	
	$leave = 0;
	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "" && isset($_REQUEST["dt"]) && trim($_REQUEST["dt"]) != "")
	{
		$leave = $objLeave->fnIsUserHalfDayApprovedByDate(trim($_REQUEST["id"]), trim($_REQUEST["dt"]));
	}
	
	echo $leave;
	exit;

?>

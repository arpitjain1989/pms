<?php 

	//include('common.php');
	
	$year = date('Y');
	$month = date('m');
	
	/*$start = '2013-01-27';
	$end = '2013-03-10';*/
	
	$start = $_REQUEST["start"];
	$end = $_REQUEST["end"];
	
	include_once('includes/class.attendance.php');

	$objAttendance = new attendance();
	
	$arrHighlights = $objAttendance->fetchAttendenceData($start, $end);
	
	echo json_encode($arrHighlights);
	
?>

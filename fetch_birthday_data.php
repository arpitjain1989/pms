<?php
	//include('common.php');
	session_start();
	//echo '<pre>'; print_r($_REQUEST);
	include_once('includes/class.employee.php');

	$objEmployee = new employee();

	$arrEmployee = array();

	$arrHighlights = $objEmployee->fetchEmployeeBitrhDayData($_REQUEST['start'],$_REQUEST['end']);

	//print_r($arrHighlights);die;

	echo json_encode($arrHighlights);

?>

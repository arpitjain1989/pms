<?php
	/* This file is using to automatically change the designation of bpo trainee to bpo executive when they complete 3 months */
	include('common.php');
	
	include_once('includes/class.employee.php');
	
	$objEmployee = new employee();

	$date = strtotime(Date('Y-m-d'));

	$date_three_month_before = date("Y-m-d", strtotime("-3 month", $date)); 
	
	/* Get all employee whose three months complete on current date */
	$getAllEmployees = $objEmployee->fnGetThreeMonthComplete($date_three_month_before);
	
	//echo '<pre>'; print_r($getAllEmployees);
	
	/* sending mail to reporting heads and hr */
	foreach($getAllEmployees as $employee)
	{
		$changeDesignation = $objEmployee->fnChangeEmployeeDesignation($employee['emp_id']);
	}
?>

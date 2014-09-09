<?php
	include_once("includes/class.employee.php");
	include_once("includes/class.attendance.php");

	$objEmployee = new employee();
	$objAttendance = new attendance();

	/* Get requested employee all details using id */
	$leave_bal = 0;
	if($_REQUEST['id'] != '')
	{
		$EmployeeInfo = $objEmployee->fnGetEmployeeById($_REQUEST['id']);
		
		$leave_bal = $objAttendance->fnGetLastLeaveBalance($_REQUEST['id']);
		
		if($leave_bal < 0)
			$leave_bal = 0;
	}
	//echo '<pre>';  print_r($EmployeeInfo);
	
	$arr = array('ad' => $EmployeeInfo['address'], 'ph' => $EmployeeInfo['contact'], 'leave_bal' => $leave_bal);

	$Jarray = json_encode($arr);
	
	echo $Jarray;

?>

<?php

	include_once('includes/class.shifts.php');
	include_once('includes/class.attendance.php');
	include_once('includes/class.employee.php');

	$objShifts = new shifts();
	$objAttendance = new attendance();
	$objEmployee = new employee();

	$Date = Date('Y-m-d');

	$starttime = "00:00";
	$endtime = "00:00";

	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		
		/* Get data from attendance */
		$ShiftId = $objAttendance->fnGetAttendanceShiftByUserAndDate($_REQUEST["id"], $Date);
		
		if($ShiftId == "" || $ShiftId == "0")
		{
			/* Get the default shift from the employee */
			$ShiftId = $objEmployee->fnGetEmployeeShiftById($_REQUEST["id"]);
		}
		
		
		$arrShift = $objShifts->fnGetShiftById($ShiftId);
		if(count($arrShift) > 0)
		{
			$starttime = $arrShift["starttime"];
			$endtime = $arrShift["endtime"];
		}
	}
	
	echo json_encode(array("start"=>$starttime, "end"=>$endtime));
	exit;

?>

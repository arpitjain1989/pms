<?php
	
	$employeeId = $_REQUEST["id"];
	$date = $_REQUEST["date"];

	include_once('includes/class.leave.php');
	include_once('includes/class.shift_movement.php');
	include_once('includes/class.holidays.php');
	
	$objLeave = new leave();
	$objShiftMovement = new shift_movement();
	$objHolidays = new holidays();
	
	/* Display Leaves -> Approved */
	$arrLeaves = $objLeave->fnGetEmployeeLeaveByDate($employeeId, $date);

	//print_r($arrLeaves);

	/* Display Shift Movements -> Approved */
	$arrShiftMovement = $objShiftMovement->getEmployeeShiftMovementByDate($employeeId, $date);

	/* Display holidays */
	$arrHoliday = $objHolidays->fnGetHolidayByDate($date);

	/* Display halfday leave */
	$arrHalfdayLeave = $objLeave->fnGetEmployeeHalfdayLeaveByDate($employeeId, $date);

	$DisplayString = "";
	if(count($arrHoliday) > 0 || (isset($arrLeaves["ph"]) && $arrLeaves["ph"] == "1"))
	{
		$DisplayString = "PH";
	}
	else if(count($arrLeaves) > 0)
	{
		$DisplayString = "PPL";
	}
	else if(count($arrHalfdayLeave) > 0)
	{
		$DisplayString = "PHL";
	}
	else if(count($arrShiftMovement) > 0)
	{
		$DisplayString = "SM";
	}

	echo $DisplayString;

?>

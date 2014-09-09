<?php

	include('common.php');
	set_time_limit(0);
	include_once('includes/class.roster.php');
	include_once('includes/class.employee.php');
	include_once('includes/class.attendance.php');
	include_once('includes/class.shift_movement.php');
	include_once('includes/class.leave.php');
	include_once('includes/class.holidays.php');

	$objRoster = new roster();
	$objEmployee = new employee();
	$objAttendance = new attendance();
	$objShiftMovement = new shift_movement();
	$objLeave = new leave();
	$objHolidays = new holidays();

	$arrRosterDates = $objRoster->fnGetRosterDays();

	$arrKeys = array_keys($arrRosterDates);

	$start_date = $arrKeys[0];
	$end_date = array_pop($arrKeys);

	$arrEmployee = $objRoster->fnGetRosteredEmployee($start_date, $end_date);

	$employeeids = 0;
	if(count($arrEmployee) > 0)
		$employeeids = implode(",", $arrEmployee);

	$arrEmployee = $objEmployee->fnGetUnrosteredEmployees($employeeids);

	if(count($arrEmployee) > 0)
	{
		foreach($arrEmployee as $curEmp)
		{
			//print_r($curEmp);

			$arrRoster["addedon"] = Date("Y-m-d H:i:s");
			$arrRoster["addedby"] = 0;
			$arrRoster["start_date"] = $start_date;
			$arrRoster["end_date"] = $end_date;
			$arrRoster["autoadded"] = 1;
			$arrRoster["userid"] = $curEmp["id"];
			$arrRoster["weekoffdate"] = $end_date;
			$arrRoster["weekoffday"] = "Sunday";
			$arrRoster["reportinghead"] = $curEmp["tlid"];

			$secondReportingHead = $objEmployee->fnGetReportingHeadId($arrRoster["reportinghead"]);
			$arrRoster["secondreportinghead"] = $secondReportingHead;

			//print_r($arrRoster);

			$arrRoster["isfinalized"] = "1";

			$RosterId = $objRoster->fnSaveRoster($arrRoster);

			$rosterDetail["rosterid"] = $RosterId;
			$shiftid = $objEmployee->fnGetEmployeeShiftById($curEmp["id"]);

			foreach($arrRosterDates as $k => $v)
			{
				$rosterDetail["rostereddate"] = $k;

				/* Display holidays */
				$arrHoliday = $objHolidays->fnGetHolidayByDate($k);

				if(count($arrHoliday) > 0)
				{
					$attendance = "PH";
					//$shiftid = 0;
				}
				else if($end_date == $k)
				{
					$attendance = "WO";
					//$shiftid = 0;
				}
				else
				{
					/* Check if leave added & approved */
					$leave = $objLeave->fnGetEmployeeLeaveByDate($curEmp["id"], $k);

					/* Check if shift movement added & approved */
					$shifts = $objShiftMovement->getEmployeeShiftMovementByDate($curEmp["id"], $k);

					/* Display halfday leave */
					$arrHalfdayLeave = $objLeave->fnGetEmployeeHalfdayLeaveByDate($curEmp["id"], $k);

					$attendance = "P";
					if(count($leave) > 0)
					{
						$attendance = "PPL";
						//$shiftid = 0;
					}
					else if(count($arrHalfdayLeave) > 0)
					{
						$attendance = "PHL";
					}
					else if(count($shifts) > 0)
					{
						$attendance = "SM";
					}
				}

				$rosterDetail["attendance"] = $attendance;
				$rosterDetail["shiftid"] = $shiftid;
				
				if($rosterDetail["attendance"] == "SC")
					$rosterDetail["attendance"] = "P";
							
				//print_r($rosterDetail);

				$objRoster->fnSaveRosterDetail($rosterDetail);

				//if($rosterDetail["attendance"] != "P")
				//{
					$arrInfo["user_id"] = $curEmp["id"];
					$arrInfo["date"] = $k;
					if($rosterDetail["attendance"] == "WO" || $rosterDetail["attendance"] == "PH")
					{
						/* Display Leaves -> Approved */
						$arrLeaves = $objLeave->fnGetEmployeeLeaveByDate($curEmp["id"], $arrInfo["date"]);
						
						/* Display halfday leave */
						$arrHalfdayLeave = $objLeave->fnGetEmployeeHalfdayLeaveByDate($curEmp["id"], $arrInfo["date"]);

						/* Display Shift Movements -> Approved */
						$arrShiftMovement = $objShiftMovement->getEmployeeShiftMovementByDate($curEmp["id"], $arrInfo["date"]);

						if(count($arrLeaves) > 0 || count($arrHalfdayLeave) > 0 || count($arrShiftMovement) > 0)
						{
							unset($arrInfo["leave_id"]);
						}
						else
							$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle($rosterDetail["attendance"]);
					}
					else
					{
						/*get leave id if on leave */
						//unset($arrInfo["leave_id"]);
						$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle($rosterDetail["attendance"]);
					}	
					$arrInfo["shift_id"] = $rosterDetail["shiftid"];

					//print_r($arrInfo);
					$objAttendance->fnInsertRosterAttendance($arrInfo);
				//}
			}
			
			/* Update leave status flag [not to allow updations after roster generation] */
			//$objLeave->fnDisableLeaveUpdation($curEmp["id"], $arrRoster["end_date"]);
		}
	}

	/* Add code to deactivate all leaves till date */
	$objLeave->fnDisableAllLeaveUpdationsByDate($end_date);

	/* Fetch all the employees in IT Support */
	include_once("includes/class.it_support_designations.php");
	include_once("includes/class.it_support_roster.php");
	
	$objItSupportDesignation = new it_support_designations();
	$objItSupportRoster = new it_support_roster();

	$arrDesignations = $objItSupportDesignation->fnGetSupportDesignations();
	$arrDesignations[] = 0;

	$arrSupportEmployee = $objEmployee->fnGetEmployeesByDesignation(implode(',', $arrDesignations));
	if(count($arrSupportEmployee) > 0)
	{
		foreach($arrSupportEmployee as $curEmployee)
		{
			foreach($arrRosterDates as $k => $v)
			{
				/* Check if roster added */
				if(!$objItSupportRoster->fnCheckIfRosterAlreadyEntered($curEmployee["id"], $k))
				{
					/* Roster is not added, so add it and reflect it in attendance */
					$objItSupportRoster->fnAutoInsertSupportRoster($curEmployee["id"], $curEmployee["shiftid"], $k);
				}
			}
		}
	}

	echo "done";

?>

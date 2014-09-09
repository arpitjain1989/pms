<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('print_roster.html','main');

	include_once('includes/class.employee.php');
	include_once('includes/class.roster.php');
	include_once('includes/class.leave.php');
	include_once('includes/class.shift_movement.php');
	include_once('includes/class.holidays.php');

	$objEmployee = new employee();
	$objRoster = new roster();
	$objLeave = new leave();
	$objShiftMovement = new shift_movement();
	$objHolidays = new holidays();

	$tpl->set_var("FillRosterDateBlock","");
	$tpl->set_var("FillRosterInformation","");
	$tpl->set_var("DisplayRoster","");
	$tpl->set_var("DisplayNoRoster","");

	if(isset($_REQUEST["start"]) && isset($_REQUEST["end"]) && isset($_REQUEST["id"]))
	{
		$arrRosterDates = $objRoster->fnGetRosterDays($_REQUEST["start"]);

		$arrKeys = array_keys($arrRosterDates);

		$start_date = $arrKeys[0];
		$end_date = array_pop($arrKeys);

		$tpl->set_var("startdate",$start_date);
		$tpl->set_var("enddate",$end_date);

		/*if(isset($_REQUEST["id"]) && $_REQUEST["id"] == "0")
		{
			$arrEmployees = $objEmployee->fnGetEmployeeIdsByDesignation('7, 13', $end_date);
			$tpl->set_var("rosterforstr","Team leaders");
		}
		else
		{
			$arrEmployees = $objEmployee->fnGetAllemployeesReleavingDateWise($end_date, $_REQUEST["id"]);
			$tpl->set_var("rosterforstr",$objEmployee->fnGetEmployeeNameById($_REQUEST["id"])."'s Team");
		}

		$arrEmployees = array_filter($arrEmployees,'strlen');*/

		$arrEmployees = $objEmployee->fnGetAllEmployeesForRoster($end_date, $_REQUEST["id"]);
		if($start_date == $_REQUEST["start"] && $end_date == $_REQUEST["end"])
		{

			$arrEmployees = array_filter($arrEmployees,'strlen');
			
			if(count($arrRosterDates) > 0)
			{
				foreach($arrRosterDates as $displayDate)
				{
					$tpl->set_var("DisplayDate",$displayDate);
					$tpl->parse("FillRosterDateBlock",true);
				}
			}

			if(count($arrEmployees) > 0)
			{
				foreach($arrEmployees as $employeeId)
				{
					$tpl->set_var("employee_id",$employeeId);
					$tpl->set_var("employeename",$objEmployee->fnGetEmployeeNameById($employeeId));

					$currentShift = $objEmployee->fnGetEmployeeShiftById($employeeId);

					$tpl->set_var("FillRosterDetails","");
					if(count($arrRosterDates) > 0)
					{
						foreach($arrRosterDates as $date => $displayDate)
						{
							/* Display Leaves -> Approved */
							$arrLeaves = $objLeave->fnGetEmployeeLeaveByDate($employeeId, $date);

							/* Display Shift Movements -> Approved */
							$arrShiftMovement = $objShiftMovement->getEmployeeShiftMovementByDate($employeeId, $date);

							/* Display holidays */
							$arrHoliday = $objHolidays->fnGetHolidayByDate($date);

							//print_r($arrShiftMovement);

							$arrRoster = $objRoster->fnGetRosterDetailsByUserid($start_date, $end_date, $employeeId, $date);

							$tpl->set_var("weekoffday",$arrRoster["weekoffday"]);

							/*$DisplayString = "P";
							if($date == $arrRoster["weekoffdate"])
							{
								$DisplayString = "WO";
							}
							else if(count($arrLeaves) > 0)
							{
								$DisplayString = "PPL";
							}
							else if(count($arrShiftMovement) > 0)
							{
								$DisplayString = "SM";
							}*/

							$shiftinfo = "";
							if(isset($arrRoster["attendance"]))
							{
								if($arrRoster["attendance"] == "PH")
								{
									if(isset($arrLeaves["ph"]) && $arrLeaves["ph"] == "1")
										$shiftinfo = "<br/>Alternative holiday for PH";
									else
										$shiftinfo = "<br/>".$arrHoliday["title"];
								}
								else if($arrRoster["attendance"] == "P")
								{
									/* else if($arrRoster["attendance"] == "SC" || $arrRoster["attendance"] == "P") */
									$arrRoster["attendance"] = "P";
									$shiftinfo = "<br/>".$arrRoster["shifttitle"] . " : " . $arrRoster["shiftstart"] . " - " . $arrRoster["shiftend"];
								}
								else if($arrRoster["attendance"] == "SM" && count($arrShiftMovement) > 0)
								{
									$shiftinfo = "<br/>"."Shift movement: ".$arrShiftMovement["movement_fromtime"] ." - " . $arrShiftMovement["movement_totime"]."<br>Reason: ".$arrShiftMovement["reason"];
								}

								$tpl->set_var("daystatus",$arrRoster["attendance"]);
							}
							else
							{
								$shiftinfo = "";
								$tpl->set_var("daystatus","");
							}

							$tpl->set_var("shiftinfo",$shiftinfo);

							$tpl->parse("FillRosterDetails",true);
						}
					}
					$tpl->parse("FillRosterInformation",true);
				}
				$tpl->parse("DisplayRoster",false);
			}
		}
		else
		{
			$tpl->parse("DisplayNoRoster",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoRoster",false);
	}

	$tpl->pparse('main',false);
?>


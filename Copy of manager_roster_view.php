<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('print_roster.html','main');

	include_once('includes/class.employee.php');
	include_once('includes/class.roster.php');
	include_once('includes/class.leave.php');
	include_once('includes/class.shift_movement.php');

	$objEmployee = new employee();
	$objRoster = new roster();
	$objLeave = new leave();
	$objShiftMovement = new shift_movement();

	$tpl->set_var("FillRosterDateBlock","");
	$tpl->set_var("FillRosterInformation","");

	if(isset($_REQUEST["start"]) && isset($_REQUEST["end"]) && isset($_REQUEST["id"]))
	{
		$arrRosterDates = $objRoster->fnGetRosterDays($_REQUEST["start"]);

		if(isset($_REQUEST["id"]) && $_REQUEST["id"] == "0")
		{
			$arrEmployees = $objEmployee->fnGetEmployeeIdsByDesignation('7, 13');
			$tpl->set_var("rosterforstr","Team leaders");
		}
		else
		{
			$arrEmployees = $objEmployee->fnGetAllemployees($_REQUEST["id"]);
			$tpl->set_var("rosterforstr",$objEmployee->fnGetEmployeeNameById($_REQUEST["id"])."'s Team");
		}

		$start_date = reset(array_keys($arrRosterDates));
		$end_date = end(array_keys($arrRosterDates));

		$tpl->set_var("startdate",$start_date);
		$tpl->set_var("enddate",$end_date);

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

							$arrRoster = $objRoster->fnGetRosterDetailsByUserid($start_date, $end_date, $employeeId, $date);

							$tpl->set_var("weekoffday",$arrRoster["weekoffday"]);

							$shiftinfo = "";
							if($arrRoster["attendance"] == "SC" || $arrRoster["attendance"] == "P")
							{
								$arrRoster["attendance"] = "P";
								$shiftinfo = "<br/>".$arrRoster["shifttitle"] . " : " . $arrRoster["shiftstart"] . " - " . $arrRoster["shiftend"];
							}
							else if($arrRoster["attendance"] == "SM" && count($arrShiftMovement) > 0)
							{
								$shiftinfo = "<br/>"."Shift movement: ".$arrShiftMovement["movement_fromtime"] ." - " . $arrShiftMovement["movement_totime"]."<br>Reason: ".$arrShiftMovement["reason"];
							}

							$tpl->set_var("daystatus",$arrRoster["attendance"]);
							$tpl->set_var("shiftinfo",$shiftinfo);

							$tpl->parse("FillRosterDetails",true);
						}
					}
					$tpl->parse("FillRosterInformation",true);
				}
			}
		}
		else
		{
			header("Location: roster_list.php?info=noroster");
			exit;
		}
	}
	else
	{
		header("Location: roster_list.php?info=noroster");
		exit;
	}

	$tpl->pparse('main',false);
?>

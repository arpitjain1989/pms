<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('manager_roster_view.html','main_container');

	$PageIdentifier = "ManagerRoster";
	include_once('userrights.php');

	/*if($_SESSION["usertype"] == "employee" && $_SESSION["designation"] != "6")
	{
		header("Location: dashboard.php");
		exit;
	}*/

	$tpl->set_var("mainheading","View Roster");
	$breadcrumb = '<li><a href="manager_roster_list.php">Manage Roster</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Roster</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

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

	if(isset($_REQUEST["start"]) && isset($_REQUEST["end"]) && isset($_REQUEST["id"]))
	{
		$tpl->set_var("viewrosterid",$_REQUEST["id"]);
		$arrRosterDates = $objRoster->fnGetRosterDays($_REQUEST["start"]);

		$arrKeys = array_keys($arrRosterDates);

		$start_date = $arrKeys[0];
		$end_date = array_pop($arrKeys);

		$tpl->set_var("startdate",$start_date);
		$tpl->set_var("enddate",$end_date);

		$tpl->set_var("rosterforstr",$objEmployee->fnGetEmployeeNameById($_REQUEST["id"])."'s Team");

		$arrEmployees = $objEmployee->fnGetAllEmployeesForRoster($end_date, $_REQUEST["id"]);
		if($start_date == $_REQUEST["start"] && $end_date == $_REQUEST["end"])
		{
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

							/* Display halfday leave */
							$arrHalfdayLeave = $objLeave->fnGetEmployeeHalfdayLeaveByDate($employeeId, $date);

							//print_r($arrShiftMovement);

							$arrRoster = $objRoster->fnGetRosterDetailsByUserid($start_date, $end_date, $employeeId, $date);

							$tpl->set_var("weekoffday",$arrRoster["weekoffday"]);

							$shiftinfo = "";
							if(isset($arrRoster["attendance"]))
							{
								if($arrRoster["attendance"] == "PH")
								{
									if(isset($arrLeaves["ph"]) && $arrLeaves["ph"] == "1")
										$shiftinfo = "Alternative holiday for PH";
									else
										$shiftinfo = $arrHoliday["title"];
								}
								else if($arrRoster["attendance"] == "SC" || $arrRoster["attendance"] == "P")
								{
									$arrRoster["attendance"] = "P";
									$shiftinfo = $arrRoster["shifttitle"] . " : " . $arrRoster["shiftstart"] . " - " . $arrRoster["shiftend"];
								}
								else if($arrRoster["attendance"] == "SM" && count($arrShiftMovement) > 0)
								{
									$shiftinfo = "Shift movement: ".$arrShiftMovement["movement_fromtime"] ." - " . $arrShiftMovement["movement_totime"]."<br>Reason: ".$arrShiftMovement["reason"];
								}
								else if($arrRoster["attendance"] == "PHL" && count($arrHalfdayLeave) > 0)
								{
									$halfdaystr = "";
									if($arrHalfdayLeave["halfdayfor"] == "1")
										$halfdaystr = "Halfday : First half";
									else if($arrHalfdayLeave["halfdayfor"] == "2")
										$halfdaystr = "Halfday : Second half";
									
									$shiftinfo = $arrRoster["shifttitle"] . " : " . $arrRoster["shiftstart"] . " - " . $arrRoster["shiftend"] . "<br/>".$halfdaystr;
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

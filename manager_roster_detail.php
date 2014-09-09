<?php
	include('common.php');

	$tpl = new Template($app_path);

	//$tpl->load_file('template.html','main');
	$tpl->load_file('manager_roster_detail.html','main');

	//$PageIdentifier = "Roster";
	//include_once('userrights.php');

	/*if(isset($_SESSION["usertype"]) && ($_SESSION["usertype"] == "admin" || ($_SESSION["usertype"] == "employee" && $_SESSION["designation"] != "6")))
	{
		header("Location: dashboard.php");
		exit;
	}*/

	$tpl->set_var("mainheading","Add Roster");
	$breadcrumb = '<li><a href="roster_list.php">Manage Roster</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add Roster</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.employee.php');
	include_once('includes/class.roster.php');
	include_once('includes/class.leave.php');
	include_once('includes/class.shift_movement.php');
	include_once('includes/class.attendance.php');
	include_once('includes/class.shifts.php');
	include_once('includes/class.holidays.php');

	$objEmployee = new employee();
	$objRoster = new roster();
	$objLeave = new leave();
	$objShiftMovement = new shift_movement();
	$objAttendance = new attendance();
	$objShifts = new shifts();
	$objHolidays = new holidays();

	/* Modified for roster update - 25May2013 */
	$start_date = date('Y-m-d', strtotime('next monday'));

	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["start"]) && trim($_REQUEST["start"]) != '')
	{
		$start_date = trim($_REQUEST["start"]);
	}
	else if(isset($_POST["action"]) && trim($_POST["action"]) == "SaveRoster")
	{
		$start_date = trim($_REQUEST["startdate"]);
	}

	$arrRosterDates = $objRoster->fnGetRosterDays($start_date);

	$arrKeys = array_keys($arrRosterDates);

	$start_date = $arrKeys[0];
	$end_date = array_pop($arrKeys);

	$tpl->set_var("startdate",$start_date);

	/*if(isset($_REQUEST["v"]) && $_REQUEST["v"] == "0")
	{
		$arrEmployees = $objEmployee->fnGetEmployeeIdsByDesignation('7, 13', $end_date, $start_date);
		$tpl->set_var("rosterforstr","Team leaders");
	}
	else
	{
		$arrEmployees = $objEmployee->fnGetAllemployeesReleavingDateWise($end_date, $_REQUEST["v"], $start_date);
		$tpl->set_var("rosterforstr",$objEmployee->fnGetEmployeeNameById($_REQUEST["v"])."'s Team");
	}

	$arrEmployees = array_filter($arrEmployees,'strlen');*/

	$arrEmployees = $objEmployee->fnGetAllEmployeesForRoster($end_date, $_REQUEST["v"]);
	$tpl->set_var("rosterforstr",$objEmployee->fnGetEmployeeNameById($_REQUEST["v"])."'s Team");

	if(isset($_POST["action"]) && trim($_POST["action"]) == "SaveRoster")
	{
		$arrRoster["addedon"] = Date("Y-m-d H:i:s");
		$arrRoster["addedby"] = $_SESSION["id"];
		$arrRoster["start_date"] = $start_date;
		$arrRoster["end_date"] = $end_date;
		$arrRoster["autoadded"] = 0;

		$arrWeekDays = array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
		$arrKeys = array_keys($arrRosterDates);

		if(count($arrEmployees) > 0)
		{
			foreach($arrEmployees as $employeeId)
			{
				if(isset($_POST["weekoff_".$employeeId]))
				{
					$val = $_POST["weekoff_".$employeeId];
					$arrRoster["userid"] = $employeeId;
					$arrRoster["weekoffdate"] = $arrKeys[$val];
					$arrRoster["weekoffday"] = $arrWeekDays[$val];

					$reportingHead = $objEmployee->fnGetReportingHeadId($employeeId);

					$arrRoster["reportinghead"] = $reportingHead;

					$secondReportingHead = $objEmployee->fnGetReportingHeadId($reportingHead);

					$arrRoster["secondreportinghead"] = $secondReportingHead;

					$RosterId = $objRoster->fnSaveRoster($arrRoster);

					$rosterDetail["rosterid"] = $RosterId;
					$shiftid = $objEmployee->fnGetEmployeeShiftById($employeeId);
					$allowedShifts = $objShifts->fnAllowedShiftsByHeadId($reportingHead);

					if(count($allowedShifts) == 1)
						$shiftid = $allowedShifts[0];

					foreach($arrWeekDays as $k => $v)
					{
						$rosterDetail["rostereddate"] = $_POST["date_$k"];
						$rosterDetail["attendance"] = $_POST["hdaystatus_".$k."_".$employeeId];

						if(isset($_POST["isworking_".$k."_".$employeeId]) && $rosterDetail["attendance"] == "PH")
						{
							$rosterDetail["attendance"] = "P";
						}

						if(isset($_POST["shiftchange_".$k."_".$employeeId]) && $rosterDetail["attendance"] == "P")
						{
							$rosterDetail["shiftid"] = $_POST["shiftchange_".$k."_".$employeeId];

							if($shiftid != $rosterDetail["shiftid"])
								$rosterDetail["attendance"] = "P";
						}
						else
							$rosterDetail["shiftid"] = $shiftid;

						if($rosterDetail["attendance"] == "SC")
							$rosterDetail["attendance"] = "P";

						$objRoster->fnSaveRosterDetail($rosterDetail);

						/*if($rosterDetail["attendance"] != "P")
						{*/
							$arrInfo["user_id"] = $employeeId;
							$arrInfo["date"] = $_POST["date_$k"];

							if($rosterDetail["attendance"] == "WO" || $rosterDetail["attendance"] == "PH")
							{
								/* Display Leaves -> Approved */
								$arrLeaves = $objLeave->fnGetEmployeeLeaveByDate($employeeId, $arrInfo["date"]);

								/* Display halfday leave */
								$arrHalfdayLeave = $objLeave->fnGetEmployeeHalfdayLeaveByDate($employeeId, $arrInfo["date"]);

								/* Display Shift Movements -> Approved */
								$arrShiftMovement = $objShiftMovement->getEmployeeShiftMovementByDate($employeeId, $arrInfo["date"]);

								if(count($arrLeaves) > 0 || count($arrHalfdayLeave) > 0 || count($arrShiftMovement) > 0)
								{
									unset($arrInfo["leave_id"]);
								}
								else
									$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle($rosterDetail["attendance"]);
							}
							else
								unset($arrInfo["leave_id"]);
								if(isset($_POST["enche_".$k."_".$employeeId]) &&  $_POST["enche_".$k."_".$employeeId]== 1)
							{
							 $arrInfo["enche"] = 1;
						
							}
							else
							{
							$arrInfo["enche"] = 0;
							
							}
							$arrInfo["shift_id"] = $rosterDetail["shiftid"];

							$objAttendance->fnInsertRosterAttendance($arrInfo);
						//}
					}
					/* Update leave status flag [not to allow updations after roster generation] */
					$objLeave->fnDisableLeaveUpdation($employeeId, $arrRoster["start_date"], $arrRoster["end_date"]);
				}
			}
		}

		header("Location: manager_roster_list.php?info=added");
		exit;
	}

	$tpl->set_var("ShowFormBlock","");
	$tpl->set_var("FormErrorBlock","");

	if(!$objRoster->fnCheckRosterEntered($start_date, $end_date, implode(",",$arrEmployees)))
	{
		//header("Location: manager_roster_list.php?info=err");
		//exit;

		$tpl->parse("FormErrorBlock",false);
	}
	else
	{
		$tpl->set_var("FillRosterDateBlock","");
		if(count($arrRosterDates) > 0)
		{
			$i = 0;
			foreach($arrRosterDates as $date => $displayDate)
			{
				$tpl->set_var("DisplayDate",$displayDate);
				$tpl->set_var("dt",$date);
				$tpl->set_var("curdt",$i);
				$tpl->parse("FillRosterDateBlock",true);
				$i++;
			}
		}

		$tpl->set_var("FillRosterInformation","");
		if(count($arrEmployees) > 0)
		{
			//$EmployeeShifts = $objEmployee->fnGetEmployeesShiftByReportingHead($_REQUEST["v"]);
			$arrShifts = $objShifts->fnAllowedShiftsDetailsByHeadId($_REQUEST["v"]);

			//$arrShifts = $objShifts->fnGetAllShifts();

			foreach($arrEmployees as $employeeId)
			{
				$tpl->set_var("employee_id",$employeeId);
				$tpl->set_var("employeename",$objEmployee->fnGetEmployeeNameById($employeeId));

				$empshiftid = $objEmployee->fnGetEmployeeShiftById($employeeId);

				if(count($arrShifts) == 1)
				{
					$empshiftid = $arrShifts[0]["id"];
				}

				$arrShiftInfo = $objShifts->fnGetShiftById($empshiftid);

				$empshifttext = "";
				if(count($arrShiftInfo) > 0)
				{
					$empshifttext = $arrShiftInfo["title"] . " : " . $arrShiftInfo["starttime"] . " - " . $arrShiftInfo["endtime"];
				}

				$tpl->set_var("shifttext",$empshifttext);

				$tpl->set_var("FillRosterDetails","");
				if(count($arrRosterDates) > 0)
				{
					$i = 0;

					foreach($arrRosterDates as $date => $displayDate)
					{
						$tpl->set_var("weekday",$i);

						/* Display Leaves -> Approved */
						$arrLeaves = $objLeave->fnGetEmployeeLeaveByDate($employeeId, $date);

						/* Display Shift Movements -> Approved */
						$arrShiftMovement = $objShiftMovement->getEmployeeShiftMovementByDate($employeeId, $date);

						/* Display holidays */
						$arrHoliday = $objHolidays->fnGetHolidayByDate($date);

						/* Display halfday leave */
						$arrHalfdayLeave = $objLeave->fnGetEmployeeHalfdayLeaveByDate($employeeId, $date);

						$arrRoster = $objRoster->fnGetRosterDetailsByUserid($start_date, $end_date, $employeeId, $date);

						if(isset($arrRoster["attendance"]) && trim($arrRoster["attendance"]) == "WO")
						{
							$DisplayString = "WO";
						}
						else if(isset($arrRoster["attendance"]) && trim($arrRoster["attendance"]) == "PPL" && count($arrLeaves) > 0)
						{
							$DisplayString = "PPL";
						}
						else if(isset($arrRoster["attendance"]) && trim($arrRoster["attendance"]) == "PH" && count($arrHoliday) > 0 || (isset($arrLeaves["ph"]) && $arrLeaves["ph"] == "1"))
						{
							$DisplayString = "PH";
						}
						else if(isset($arrRoster["attendance"]) && trim($arrRoster["attendance"]) == "PHL" && count($arrHalfdayLeave) > 0)
						{
							$DisplayString = "PHL";
						}
						else if(isset($arrRoster["attendance"]) && trim($arrRoster["attendance"]) == "SM" && count($arrShiftMovement) > 0)
						{
							$DisplayString = "SM";
						}
						else
						{
							$DisplayString = "P";
							if(count($arrHoliday) > 0 || (isset($arrLeaves["ph"]) && $arrLeaves["ph"] == "1"))
							{
								$DisplayString = "PH";
							}
							else if($date == $end_date && !isset($arrRoster["weekoffday"]))
							{
								$DisplayString = "WO";
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
						}

						$shiftinfo = "";

						if($DisplayString == "PH")
						{
							if(isset($arrLeaves["ph"]) && $arrLeaves["ph"] == "1")
								$shiftinfo = "Alternative holiday for PH";
						else
							$shiftinfo = $arrHoliday["title"];
						}
						else if($DisplayString == "P")
						{
							/* else if($DisplayString == "SC" || $DisplayString == "P") */
							$shiftinfo = $empshifttext;
						}
						else if($DisplayString == "SM" && count($arrShiftMovement) > 0)
						{
							$shiftinfo = "Shift movement: ".$arrShiftMovement["movement_fromtime"] ." - " . $arrShiftMovement["movement_totime"]."<br>Reason: ".$arrShiftMovement["reason"];
						}
						else if($DisplayString == "PHL")
						{
							$halfdaystr = "";
							if($arrHalfdayLeave["halfdayfor"] == "1")
								$halfdaystr = "Halfday : First half";
							else if($arrHalfdayLeave["halfdayfor"] == "2")
								$halfdaystr = "Halfday : Second half";

							$shiftinfo = $empshifttext."<br/>".$halfdaystr;
						}

						$tpl->set_var("shiftinfo",$shiftinfo);
						$tpl->set_var("daystatus",$DisplayString);

						$tpl->set_var("DisplayWorkdayOption","");
						$tpl->set_var("DisplayShiftChange","");
						$tpl->set_var("DisplaySingleShift","");
						$tpl->set_var("FillShiftChange","");

						$setdisabled = "";
						$displayChecked = "";
						//if($DisplayString == "PH" && isset($arrLeaves["ph"]) && $arrLeaves["ph"] == 0)
						if($DisplayString == "PH" || (isset($arrLeaves["ph"]) && $arrLeaves["ph"] == 1))
						{
							if($arrRoster["attendance"] == "P")
								$displayChecked = "checked='checked'";
							else
								$setdisabled = "disabled='disabled'";

							$tpl->set_var("displayChecked",$displayChecked);
							$tpl->parse("DisplayWorkdayOption",false);
						}

						/*(if(count($EmployeeShifts) > 1)
						{*/
							if($DisplayString == "P" || $DisplayString == "PH" || $DisplayString == "PHL")
							{
								if(count($arrShifts) > 0)
								{
									if(count($arrShifts) == "1")
									{
										$tpl->set_var("shift_id",$arrShifts[0]["id"]);
										$tpl->parse("DisplaySingleShift",true);
									}
									else
									{
										foreach($arrShifts as $curshift)
										{
											$tpl->set_var("shift_id",$curshift["id"]);
											$tpl->set_var("shift_name",$curshift["title"] . " : " . $curshift["starttime"] . " - " . $curshift["endtime"]);

											$tpl->parse("FillShiftChange",true);
										}


										/* if shift already selected, select that shift else take the shift from employee master */
										if(isset($arrRoster["roster_shiftid"]) && trim($arrRoster["roster_shiftid"]) != '')
											$tpl->set_var("employeeshift",$arrRoster["roster_shiftid"]);
										else
											$tpl->set_var("employeeshift",$objEmployee->fnGetEmployeeShiftById($employeeId));
										//$tpl->set_var("employeeshift",$objEmployee->fnGetEmployeeShiftById($employeeId));
										$tpl->set_var("setdisabled",$setdisabled);
										$tpl->parse("DisplayShiftChange",true);
									}
								}
							}
						/*}*/
						$tpl->parse("FillRosterDetails",true);
						$i++;
					}
				}
				$tpl->parse("FillRosterInformation",true);
			}
		}

		$tpl->parse("ShowFormBlock",false);
	}

	$tpl->pparse('main',false);
?>

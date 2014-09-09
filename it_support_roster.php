<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('it_support_roster.html','main_container');

	/* Rights management */
	$PageIdentifier = "ITSupportRoster";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Manage IT Support Roster");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage IT Support Roster</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	/* Include class */
	include_once("includes/class.it_support_designations.php");
	include_once("includes/class.employee.php");
	include_once("includes/class.shifts.php");
	include_once("includes/class.it_support_roster.php");
	include_once('includes/class.leave.php');
	include_once('includes/class.shift_movement.php');
	include_once('includes/class.attendance.php');
	include_once('includes/class.holidays.php');

	/* Create object */
	$objItSupportDesignation = new it_support_designations();
	$objEmployee = new employee();
	$objShifts = new shifts();
	$objItSupportRoster = new it_support_roster();
	$objLeave = new leave();
	$objShiftMovement = new shift_movement();
	$objAttendance = new attendance();
	$objHolidays = new holidays();

	$tpl->set_var("current_date", Date('Y-m-d'));

	/* if no date selected then set the current date */
	if(!isset($_SESSION["it_support_roster_date"]) || trim($_SESSION["it_support_roster_date"]) == "")
		$_SESSION["it_support_roster_date"] = Date('Y-m-d');

	$tpl->set_var("roster_start_date", $_SESSION["it_support_roster_date"]);

	/* Display message block */
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Support time saved";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Support timings same as previous. Cannot save the data.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	/* Search support roster information */
	if(isset($_POST["action"]) && trim($_POST["action"]) == 'SearchSupportRoster')
	{
		$_SESSION["it_support_roster_date"] = $_POST["roster_start_date"];

		header("Location: it_support_roster.php");
		exit;
	}
	
	/* Save support roster */
	if(isset($_POST["action"]) && trim($_POST["action"]) == 'SaveSupportRoster')
	{
		$objItSupportRoster->fnSaveSupportRoster($_POST);

		header("Location: it_support_roster.php?info=success");
		exit;
	}

	/* Fill shift information */
	$tpl->set_var("FillShiftInformation","");
	$arrShifts = $objShifts->fnGetAllShifts();
	if(count($arrShifts) > 0)
	{
		foreach($arrShifts as $curShift)
		{
			$tpl->set_var("shift_id", $curShift["id"]);
			$tpl->set_var("shift_title", $curShift["starttime"] . " - " . $curShift["endtime"]);

			$tpl->parse("FillShiftInformation",true);
		}
	}

	/* Fill existing values */
	$arrITDesignation = $objItSupportDesignation->fnGetSupportDesignations();

	/* Display shift information for support team */
	$tpl->set_var("DisplaySupportRoster","");
	if(isset($_SESSION["it_support_roster_date"]) && trim($_SESSION["it_support_roster_date"]) != "" && count($arrITDesignation) > 0)
	{
		$arrEmployee = $objEmployee->fnGetEmployeesByDesignation(implode(",", $arrITDesignation));

		if(count($arrEmployee) > 0)
		{
			$arrDates = array();

			$tpl->set_var("FillDateHeadings","");
			$tpl->set_var("FillEmployeeInformation","");
			$tpl->set_var("FillDateDetails","");

			$tpl->set_var("date_headings", date('d-m-Y',strtotime($_SESSION["it_support_roster_date"])));
			$tpl->set_var("date_value", date('Y-m-d',strtotime($_SESSION["it_support_roster_date"])));
			$arrDates[] = $_SESSION["it_support_roster_date"];

			$tpl->parse("FillDateHeadings", true);

			for($i=0; $i < 6; $i++)
			{
				$j = $i + 1;
				$strtime = strtotime('+'.$j.' day', strtotime($_SESSION["it_support_roster_date"]));
				$tpl->set_var("date_headings", date('d-m-Y',$strtime));
				$tpl->set_var("date_value", date('Y-m-d',$strtime));
				$arrDates[] = date('Y-m-d',$strtime);

				$tpl->parse("FillDateHeadings", true);
			}

			foreach($arrEmployee as $curEmployee)
			{
				$tpl->set_var("employee_name",$curEmployee["name"]);
				$tpl->set_var("employee_id",$curEmployee["id"]);

				$tpl->set_var("FillDateDetails","");
				foreach($arrDates as $curDate)
				{
					$tpl->set_var("cur_date", $curDate);

					/* Display Leaves -> Approved */
					$arrLeaves = $objLeave->fnGetEmployeeLeaveByDate($curEmployee["id"], $curDate);

					/* Display Shift Movements -> Approved */
					$arrShiftMovement = $objShiftMovement->getEmployeeShiftMovementByDate($curEmployee["id"], $curDate);

					/* Display holidays */
					$arrHoliday = $objHolidays->fnGetHolidayByDate($curDate);

					/* Display halfday leave */
					$arrHalfdayLeave = $objLeave->fnGetEmployeeHalfdayLeaveByDate($curEmployee["id"], $curDate);

					$DisplayString = "P";
					if(count($arrHoliday) > 0 || (isset($arrLeaves["ph"]) && $arrLeaves["ph"] == "1"))
					{
						$DisplayString = "PH";
					}
					else if(Date('l', strtotime($curDate)) == 'Sunday')
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

					$tpl->set_var("DisplayString",$DisplayString);

					$shiftid = $objItSupportRoster->fnGetSupportRoster($curDate, $curEmployee["id"]);

					$tpl->set_var("support_shift_id", $shiftid);

					$tpl->parse("FillDateDetails", true);
				}

				$tpl->parse("FillEmployeeInformation",true);
			}

			$tpl->parse("DisplaySupportRoster",true);
		}
	}

	$tpl->pparse('main',false);

?>

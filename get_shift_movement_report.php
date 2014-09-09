<?php

	include('common.php');

	$tpl = new Template($app_path);
	$tpl->load_file('get_shift_movement_report.html','main');
	
	include_once('includes/class.shift_movement.php');
	include_once('includes/class.leave.php');

	$objShiftMovement = new shift_movement();
	$objLeave = new leave();
		
	$Date = Date('Y-m-d');

	$txtShiftMovementCompensation = "<select name='shift_movement_id' id='shift_movement_id' onchange='javascript: fnMovementChange($(this));'>";
	$txtShiftMovementCompensation .= "<option value=''>Please Select</option>";

	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$_SESSION["AdminShiftMovementReport"]["search_from_date"] = $_REQUEST["search_from_date"];
		$_SESSION["AdminShiftMovementReport"]["search_to_date"] = $_REQUEST["search_to_date"];
		$_SESSION["AdminShiftMovementReport"]["movement_compensation"] = $_REQUEST["id"];
		
		$tpl->set_var("DisplayShiftMovement","");
		$tpl->set_var("NoDisplayShiftMovement","");
		
		$tpl->set_var("FillMovementInformation","");
/*		if(!isset($_SESSION["AdminShiftMovementReport"]["search_from_date"]))
		$_SESSION["AdminShiftMovementReport"]["search_from_date"] = $curMonth;

		if(!isset($_SESSION["AdminShiftMovementReport"]["search_to_date"]))
			$_SESSION["AdminShiftMovementReport"]["search_to_date"] = $curYear;
*/				
		if(!isset($_SESSION["AdminShiftMovementReport"]["movement_compensation"]))
			$_SESSION["AdminShiftMovementReport"]["movement_compensation"] = '1';
		
		$search_to_date = $_SESSION["AdminShiftMovementReport"]["search_to_date"];
		$search_from_date = $_SESSION["AdminShiftMovementReport"]["search_from_date"];
		$movement_compensation = $_SESSION["AdminShiftMovementReport"]["movement_compensation"];
		
		$tpl->set_var("search_to_date",$search_to_date);
		$tpl->set_var("search_from_date",$search_from_date);
		$tpl->set_var("movement_compensation",$movement_compensation);

		$tpl->set_var("FillShiftMovementBlock","");
		$tpl->set_var("FillShiftMovementCompensationBlock","");
		$tpl->set_var("FillAdminLeaveBlock","");
		$tpl->set_var("FillAdminHalfLeaveBlock","");
		$tpl->set_var("FillAdminSpecialLeaveBlock","");
		
		$tpl->set_var("PleaseSelectBlock","");
		
		if($_REQUEST["id"] == '1')
		{
			$tpl->set_var("smorcomp","Admin Added Shift Movements");
			$shiftMovementFromAttendance = $objShiftMovement->fnGetAllAdminShiftMovementsEmployee($search_from_date, $search_to_date);
			if(count($shiftMovementFromAttendance) > 0)
			{
				$tpl->set_var("count_sm",count($shiftMovementFromAttendance));
				foreach($shiftMovementFromAttendance as $shiftMovementAttendance)
				{
					//echo '<pre>'; echo 'key:'.$key; print_r($shiftMovementAttendance);
					if(isset($shiftMovementAttendance["e_name"]) && $shiftMovementAttendance["e_name"]!= '')
					{
						$tpl->set_var("emp_name",$shiftMovementAttendance["e_name"]);
					}
					if(isset($shiftMovementAttendance["m_date"]))
					{
						$tpl->set_var("mov_date",$shiftMovementAttendance["m_date"]);
					}
					if(isset($shiftMovementAttendance["com_date"]))
					{
						$tpl->set_var("comp_date",$shiftMovementAttendance["com_date"]);
					}
					if(isset($shiftMovementAttendance["name"]))
					{
						$tpl->set_var("employeename",$shiftMovementAttendance["name"]);
					}
					if(isset($shiftMovementAttendance["tll_name"]))
					{
						$tpl->set_var("teamLeader_name",$shiftMovementAttendance["tll_name"]);
					}
					
					if(isset($shiftMovementAttendance["smTime"]) && isset($shiftMovementAttendance["smToTime"]))
					{
						$tpl->set_var("MovementTime",$shiftMovementAttendance["smTime"].'-'.$shiftMovementAttendance["smToTime"]);
					}

					if(isset($shiftMovementAttendance['appr_tl']) && $shiftMovementAttendance['appr_tl'] == '1')
					{
						$tpl->set_var("status_tl","Approved");
					}
					else if(isset($shiftMovementAttendance['appr_tl']) && $shiftMovementAttendance['appr_tl'] == '2')
					{
						$tpl->set_var("status_tl","Un-approved");
					}
					else
					{
						$tpl->set_var("status_tl","pending");
					}
					
					$tpl->parse("FillMovementInformation",true);
				}
			}
			else
			{
				$tpl->parse("NoDisplayShiftMovement",true);
				$tpl->set_var("count_sm",'0');
			}
			$tpl->parse("FillShiftMovementBlock",true);
		}
		else if($_REQUEST["id"] == '2')
		{
			$tpl->set_var("smorcomp","Admin Added Shift Movement Compensations");
			$shiftMovementFromAttendance1 = $objShiftMovement->fnGetAllAdminShiftMovementsCompensationEmployee($search_from_date, $search_to_date);

			//echo '<pre>'; print_r($shiftMovementFromAttendance); die;
			$tpl->set_var("DisplayShiftMovement","");
			$tpl->set_var("NoDisplayShiftMovement","");
			
			$tpl->set_var("FillMovementInformation1","");

			if(count($shiftMovementFromAttendance1) > 0)
			{
				$tpl->set_var("count_sm",count($shiftMovementFromAttendance1));
				foreach($shiftMovementFromAttendance1 as $shiftMovementAttendance)
				{
					//echo '<pre>'; print_r($shiftMovementAttendance);
					if(isset($shiftMovementAttendance["e_name"]) && $shiftMovementAttendance["e_name"]!= '')
					{
						$tpl->set_var("emp_name",$shiftMovementAttendance["e_name"]);
					}
					if(isset($shiftMovementAttendance["tll_name"]) && $shiftMovementAttendance["tll_name"]!= '')
					{
						$tpl->set_var("temale_name",$shiftMovementAttendance["tll_name"]);
					}
					
					if(isset($shiftMovementAttendance["m_date"]))
					{
						$tpl->set_var("mov_date",$shiftMovementAttendance["m_date"]);
					}
					if(isset($shiftMovementAttendance["com_date"]))
					{
						$tpl->set_var("comp_date",$shiftMovementAttendance["com_date"]);
					}
					if(isset($shiftMovementAttendance["name"]))
					{
						$tpl->set_var("employeename",$shiftMovementAttendance["name"]);
					}

					if(isset($shiftMovementAttendance['appr_tl']) && $shiftMovementAttendance['appr_tl'] == '1')
					{
						$tpl->set_var("status_tl","Approved");
					}
					else if(isset($shiftMovementAttendance['appr_tl']) && $shiftMovementAttendance['appr_tl'] == '2')
					{
						$tpl->set_var("status_tl","Un-approved");
					}
					else
					{
						$tpl->set_var("status_tl","pending");
					}
					if(isset($shiftMovementAttendance["smTime"]) && isset($shiftMovementAttendance["smToTime"]))
					{
						$tpl->set_var("MovementTime",$shiftMovementAttendance["smTime"].'-'.$shiftMovementAttendance["smToTime"]);
					}
					if(isset($shiftMovementAttendance["smcomp_fromTime"]) && isset($shiftMovementAttendance["smcomp_ToTime"]))
					{
						$tpl->set_var("MovementCompTime",$shiftMovementAttendance["smcomp_fromTime"].'-'.$shiftMovementAttendance["smcomp_ToTime"]);
					}
					
					$tpl->parse("FillMovementInformation1",true);
				}
			}
			else
			{
				$tpl->parse("NoDisplayShiftMovement",true);
				$tpl->set_var("count_sm",'0');
			}
			$tpl->parse("FillShiftMovementCompensationBlock",true);
		}
		else if($_REQUEST["id"] == '3')
		{
			/* To display leaves added by admin */
			$tpl->set_var("smorcomp","Admin Added Leaves");
			
			/* time being added */
			/*$fromdate = $year . "-" . $month . "-01";
			$todate = Date('Y-m-t',strtotime($fromdate));*/

			$tpl->set_var("FillAdminLeaveInformation","");
			
			$arrStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");

			$arrAdminLeaves = $objLeave->fnGetAllAdminLeaveByDates($search_from_date, $search_to_date);
			if(count($arrAdminLeaves) > 0)
			{
				foreach($arrAdminLeaves as $curAdminLeaves)
				{
					$tpl->set_var("admin_leave_name", $curAdminLeaves["employee_name"]);
					$tpl->set_var("admin_leave_reporting_head", $curAdminLeaves["reporting_head_name"]);
					$tpl->set_var("admin_leave_startdate", $curAdminLeaves["startdate"]);
					$tpl->set_var("admin_leave_enddate", $curAdminLeaves["enddate"]);
					$tpl->set_var("admin_leave_reason", $curAdminLeaves["reason"]);
					$tpl->set_var("admin_leave_status", $arrStatus[$curAdminLeaves["status_manager"]]);
					$tpl->set_var("admin_leave_addeddate", $curAdminLeaves["addeddate"]);
					
					$tpl->parse("FillAdminLeaveInformation",true);
				}

				$tpl->parse("FillAdminLeaveBlock",true);
			}
		}
		else if($_REQUEST["id"] == '4')
		{
			/* To display leaves added by admin */
			$tpl->set_var("smorcomp","Admin Added Halfday Leaves");
			
			/* time being added */
			/*$fromdate = $year . "-" . $month . "-01";
			$todate = Date('Y-m-t',strtotime($fromdate));*/

			$tpl->set_var("FillAdminHalfLeaveInformation","");
			
			$arrStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");
			$arrHalfDayFor = array("1"=>"First Half", "2"=>"Second Half");

			$arrAdminHalfLeaves = $objLeave->fnGetAllAdminHalfLeaveByDates($search_from_date, $search_to_date);
			if(count($arrAdminHalfLeaves) > 0)
			{
				foreach($arrAdminHalfLeaves as $curAdminHalfLeaves)
				{
					$tpl->set_var("admin_halfleave_name", $curAdminHalfLeaves["employee_name"]);
					$tpl->set_var("admin_halfleave_reporting_head", $curAdminHalfLeaves["reporting_head_name"]);
					$tpl->set_var("admin_halfleave_startdate", $curAdminHalfLeaves["startdate"]);
					$tpl->set_var("admin_halfleave_halfdayfor", $arrHalfDayFor[$curAdminHalfLeaves["halfdayfor"]]);
					$tpl->set_var("admin_halfleave_reason", $curAdminHalfLeaves["reason"]);
					$tpl->set_var("admin_halfleave_status", $arrStatus[$curAdminHalfLeaves["status_manager"]]);
					$tpl->set_var("admin_halfleave_addeddate", $curAdminHalfLeaves["addeddate"]);
					
					$tpl->parse("FillAdminHalfLeaveInformation",true);
				}

				$tpl->parse("FillAdminHalfLeaveBlock",true);
			}
		}
		else if($_REQUEST["id"] == '5')
		{
			/* To display leaves added by admin */
			$tpl->set_var("smorcomp","Admin Added LWP / Special Leaves");
			
			/* time being added */
			/*$fromdate = $year . "-" . $month . "-01";
			$todate = Date('Y-m-t',strtotime($fromdate));*/

			$tpl->set_var("FillAdminSpecialLeaveInformation","");
			
			$arrStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");

			$arrAdminSpecialLeaves = $objLeave->fnGetAllAdminApprovedLWPByDate($search_from_date, $search_to_date);
			if(count($arrAdminSpecialLeaves) > 0)
			{
				foreach($arrAdminSpecialLeaves as $curAdminSpecialLeaves)
				{
					$tpl->set_var("admin_special_leave_name", $curAdminSpecialLeaves["employee_name"]);
					$tpl->set_var("admin_special_leave_reporting_head", $curAdminSpecialLeaves["reporting_head_name"]);
					$tpl->set_var("admin_special_leave_lwpdate", $curAdminSpecialLeaves["lwp_date"]);
					$tpl->set_var("admin_special_leave_title", $curAdminSpecialLeaves["leave_title"]);
					$tpl->set_var("admin_special_leave_reason", $curAdminSpecialLeaves["reason"]);
					$tpl->set_var("admin_special_leave_status", $arrStatus[$curAdminSpecialLeaves["approval_status"]]);
					$tpl->set_var("admin_special_leave_addeddate", $curAdminSpecialLeaves["addeddate"]);
					
					$tpl->parse("FillAdminSpecialLeaveInformation",true);
				}

				$tpl->parse("FillAdminSpecialLeaveBlock",true);
			}
		}
		else
		{
			$tpl->set_var("smorcomp",'No Data Found');
			$tpl->parse("PleaseSelectBlock",true);
		}
	}
	$tpl->pparse('main',false);
	//echo $txtShiftMovementCompensation .= "</select>";
	exit;

?>

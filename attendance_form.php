<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('attendance_form.html','main');
	
	include_once('includes/class.attendance.php');
	include_once('includes/class.holidays.php');
	include_once('includes/class.shift_movement.php');
	include_once('includes/class.shifts.php');
	
	set_time_limit(0);
	
	$objattendance = new attendance();
	$objShiftMovement = new shift_movement();
	$objShifts = new shifts();
	$objHolidays = new holidays();
		
	//$arrAttendanceData = $objattendance->fnGetAttendanceAll($_REQUEST['id']);
	//print_r($_REQUEST); die;
	//$arrEmployeeData = $objattendance->fnGetEmployeeDetails($_REQUEST['designation']);
	$holiday = $objHolidays->fnGetHolidayByDate($_REQUEST['date']);
	$tpl->set_var('EncheValue','');
	
	
	$arrEmployeeData1 = $objattendance->fnGetEmployeeDetails1($_REQUEST['designation'],$_REQUEST['date']);
	
	$arrLeaveType = $objattendance->fnGetLeaveType();
	
	$tpl->set_var('LeaveValue','');
	if(count($arrLeaveType)> 0)
	{
		foreach($arrLeaveType as $Leaves) 
		{
			$tpl->setAllValues($Leaves);
			$tpl->parse('LeaveValue',true);
		}
	}
	
	$arrShift = $objShifts->fnGetAllShifts();
	$tpl->set_var("FillShiftInformation","");
	if(count($arrShift) > 0)
	{
		foreach($arrShift as $curShift)
		{
			$tpl->set_var("shiftid",$curShift["id"]);
			$tpl->set_var("shifttitle",$curShift["title"]);
			
			$tpl->parse("FillShiftInformation",true);
		}
	}
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('attendanceid',"$_REQUEST[id]");
	}
	if(isset($_REQUEST['date']))
	{
		$tpl->set_var('attendance_date',"$_REQUEST[date]");
	}
	
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		$insertdata = $objattendance->fnInsertAttendance($_POST);
		if($insertdata)
		{
			header("Location: attendance.php?info=succ");
			exit;
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
		$updateAttendances = $objattendance->fnUpdateAttendances($_POST);
		if($updateAttendances)
		{
			header("Location: attendance.php?info=update");
			exit;
		}
	}
	//echo '<pre>';
	//print_r($arrEmployeeData1);
	$tpl->set_var('EmployeeValues','');
	if( count($arrEmployeeData1)> 0)
	{ //print_r($arrEmployeeData1);
		foreach($arrEmployeeData1 as $Employees) 
		{ 
		$tpl->set_var('EncheValue','');
		//print_r($arrEmployeeData1[$Employees["employee_id"]][in_time]); 
			//$arrAfdDetails[$recordData["recordid"]][$paraInfo['id']]
			$in_time = ($Employees['in_time'] =='00:00:00') ? '':$Employees['attendance_in_time'];
			$out_time = ($Employees['out_time'] =='00:00:00') ? '':$Employees['attendance_out_time'];
			$break1_in = ($Employees['break1_in'] =='00:00:00') ? '':$Employees['attendance_break1_in'];
			$break1_out = ($Employees['break1_out'] =='00:00:00') ? '':$Employees['attendance_break1_out'];
			$break2_in = ($Employees['break2_in'] =='00:00:00') ? '':$Employees['attendance_break2_in'];
			$break2_out = ($Employees['break2_out'] =='00:00:00') ? '':$Employees['attendance_break2_out'];
			$break3_in = ($Employees['break3_in'] =='00:00:00') ? '':$Employees['attendance_break3_in'];
			$break3_out = ($Employees['break3_out'] =='00:00:00') ? '':$Employees['attendance_break3_out'];
			$break4_in = ($Employees['break4_in'] =='00:00:00') ? '':$Employees['attendance_break4_in'];
			$break4_out = ($Employees['break4_out'] =='00:00:00') ? '':$Employees['attendance_break4_out'];
			$break5_in = ($Employees['break5_in'] =='00:00:00') ? '':$Employees['attendance_break5_in'];
			$break5_out = ($Employees['break5_out'] =='00:00:00') ? '':$Employees['attendance_break5_out'];			
			if(count($holiday)> 0)
			{
			if($in_time !="" && $out_time != "")
			{
			$tpl->set_var('disabled',"");
			}
			else
			{
			$tpl->set_var('disabled',"disabled=disabled");
			}
			$tpl->set_var('empid',$Employees['employee_id']);
			$tpl->set_var('enche1',$Employees['enche']);
			if($Employees['enche']==1)
			{
			//$tpl->set_var('display',"block");
			$tpl->set_var('displayChecked','checked=checked');
			}
			else
			{
			//$tpl->set_var('display',"block");
			$tpl->set_var('displayChecked',' ');
			}
			
			$tpl->parse('EncheValue',true);
		
			}
			
			$tpl->set_var('attendance1_in_time',$in_time);
			$tpl->set_var('attendance1_out_time',$out_time);
			$tpl->set_var('attendance1_break1_in',$break1_in);
			$tpl->set_var('attendance1_break1_out',$break1_out);
			$tpl->set_var('attendance1_break2_in',$break2_in);
			$tpl->set_var('attendance1_break2_out',$break2_out);
			$tpl->set_var('attendance1_break3_in',$break3_in);
			$tpl->set_var('attendance1_break3_out',$break3_out);
			$tpl->set_var('attendance1_break4_in',$break4_in);
			$tpl->set_var('attendance1_break4_out',$break4_out);
			$tpl->set_var('attendance1_break5_in',$break5_in);
			$tpl->set_var('attendance1_break5_out',$break5_out);

			$tpl->set_var('calculation_time',htmlspecialchars(json_encode(array("fullday_minimum_working_hour"=>$Employees['fullday_minimum_working_hour'], "fullday_break_minutes"=>$Employees['fullday_break_minutes'], "halfday_minimum_working_hour"=>$Employees['halfday_minimum_working_hour'], "halfday_break_minutes"=>$Employees['halfday_break_minutes'], "sm_minimum_working_hour"=>$Employees['sm_minimum_working_hour'], "sm_break_minutes"=>$Employees['sm_break_minutes'], "consider_break_exceed"=>$Employees['consider_break_exceed'], "consider_late_commings"=>$Employees['consider_late_commings'], "consider_inout_time"=>$Employees['consider_inout_time']))));
			
			$notes = "";
			
			$shiftTimings = $objShifts->fnGetShiftById($Employees["shift_id"]);
			if(count($shiftTimings) > 0)
			{
				$tpl->set_var("shift_start_time",$shiftTimings["starttime"]);
				$tpl->set_var("shift_end_time",$shiftTimings["endtime"]);
			}

			if($Employees["leave_id"] == "14")
			{
				$shiftInfo = $objShiftMovement->getEmployeeShiftMovementByDate($Employees["employee_id"], $_REQUEST['date']);
				if(count($shiftInfo) > 0)
				{
					$notes = "Movement Time: ".$shiftInfo["movement_fromtime"]." - ".$shiftInfo["movement_totime"]."<br/>Compensation: ".$shiftInfo["compensation_date"]." : ".$shiftInfo["compensation_fromtime"]." - ".$shiftInfo["compensation_totime"];
				}
			}
			else if($Employees["leave_id"] == "13")
			{
				if(count($shiftTimings) > 0)
				{
					$notes = $shiftTimings["title"]." : ".$shiftTimings["starttime"]." - ".$shiftTimings["endtime"];
				}
			}
			
			$tpl->set_var("notes",$notes);
			
			$tpl->setAllValues($Employees);
			$tpl->parse('EmployeeValues',true);
		}
	}
	
	
	/*$tpl->set_var('EmployeeValues1','');
	if(count($arrEmployeeData1)> 0)
	{
		foreach($arrEmployeeData1 as $Employees) 
		{
			$tpl->setAllValues($Employees);
			$tpl->parse('EmployeeValues',true);
		}
	}*/
	
	$tpl->pparse('main',false);
?>	

<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('attend_form.html','main_container');

	$PageIdentifier = "AttendanceView";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Attendance");
	$breadcrumb = '<li class="active">View Attendance</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.attendance.php');
	include_once('includes/class.shift_movement.php');
	include_once('includes/class.shifts.php');
	
	set_time_limit(0);
	
	$objattendance = new attendance();
	$objShiftMovement = new shift_movement();
	$objShifts = new shifts();
	
	//$arrAttendanceData = $objattendance->fnGetAttendanceAll($_REQUEST['id']);
	//print_r($_REQUEST); die;
	//$arrEmployeeData = $objattendance->fnGetEmployeeDetails($_REQUEST['designation']);
	$arrEmployeeData1 = $objattendance->fnGetEmployeeDetails2($_SESSION["id"],$_REQUEST['date']);
	
	$arrLeaveType = $objattendance->fnGetLeaveType();
	
	$arrShift = $objShifts->fnGetAllShifts();
	$tpl->set_var("FillShiftInformation","");
	
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('attendanceid',"$_REQUEST[id]");
	}
	if(isset($_REQUEST['date']))
	{
		$tpl->set_var('attendance_date',"$_REQUEST[date]");
	}
	
	//echo '<pre>';
	//print_r($arrEmployeeData1);
	$tpl->set_var('EmployeeValues','');
	if( count($arrEmployeeData1)> 0)
	{ //print_r($arrEmployeeData1);
		foreach($arrEmployeeData1 as $Employees) 
		{
			//echo '<pre>'; print_r($Employees);
			$getShiftTimingsById = $objShifts->fnGetShiftTimes($Employees['shiftid']);
			$getLeaveType = $objShifts->fnGetLeaveTypes($Employees['leave_id']);
		//print_r($arrEmployeeData1[$Employees["employee_id"]][in_time]); 
			//$arrAfdDetails[$recordData["recordid"]][$paraInfo['id']]
			$in_time = (isset($Employees['in_time']) && $Employees['in_time'] =='00:00:00') ? '':$Employees['attendance_in_time'];
			$out_time = (isset($Employees['out_time']) && $Employees['out_time'] =='00:00:00') ? '':$Employees['attendance_out_time'];
			$break1_in = (isset($Employees['break1_in']) && $Employees['break1_in'] =='00:00:00') ? '':$Employees['attendance_break1_in'];
			$break1_out = (isset($Employees['break1_out']) && $Employees['break1_out'] =='00:00:00') ? '':$Employees['attendance_break1_out'];
			$break2_in = (isset($Employees['break2_in']) && $Employees['break2_in'] =='00:00:00') ? '':$Employees['attendance_break2_in'];
			$break2_out = (isset($Employees['break2_out']) && $Employees['break2_out'] =='00:00:00') ? '':$Employees['attendance_break2_out'];
			$break3_in = (isset($Employees['break3_in']) && $Employees['break3_in'] =='00:00:00') ? '':$Employees['attendance_break3_in'];
			$break3_out = (isset($Employees['break3_out']) && $Employees['break3_out'] =='00:00:00') ? '':$Employees['attendance_break3_out'];
			$break4_in = (isset($Employees['break4_in']) && $Employees['break4_in'] =='00:00:00') ? '':$Employees['attendance_break4_in'];
			$break4_out = (isset($Employees['break4_out']) && $Employees['break4_out'] =='00:00:00') ? '':$Employees['attendance_break4_out'];
			$break5_in = (isset($Employees['break5_in']) && $Employees['break5_in'] =='00:00:00') ? '':$Employees['attendance_break5_in'];
			$break5_out = (isset($Employees['break5_out']) && $Employees['break5_out'] =='00:00:00') ? '':$Employees['attendance_break5_out'];
			
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
			
			$notes = "";
			
			$org_shift_tim = "";
			$shiftTimings = $objShifts->fnGetShiftById($Employees["shift_id"]);
			//echo 'hello'; print_r($shiftTimings);
			if(isset($shiftTimings['starttime']) && isset($shiftTimings['endtime']))
				$org_shift_tim = $shiftTimings['starttime'] .' To '. $shiftTimings['endtime'];

			if(count($shiftTimings) > 0)
			{
				$tpl->set_var("shift_start_time",$shiftTimings["starttime"]);
			}

			if(isset($Employees["leave_id"]) && $Employees["leave_id"] == "14")
			{
				$shiftInfo = $objShiftMovement->getEmployeeShiftMovementByDate($Employees["employee_id"], $_REQUEST['date']);
				if(count($shiftInfo) > 0)
				{
					$notes = "Movement Time: ".$shiftInfo["movement_fromtime"]." - ".$shiftInfo["movement_totime"]."<br/>Compensation: ".$shiftInfo["compensation_date"]." : ".$shiftInfo["compensation_fromtime"]." - ".$shiftInfo["compensation_totime"];
				}
			}
			else if(isset($Employees["leave_id"]) && $Employees["leave_id"] == "13")
			{
				if(count($shiftTimings) > 0)
				{
					$notes = $shiftTimings["title"]." : ".$shiftTimings["starttime"]." - ".$shiftTimings["endtime"];
				}
			}
			
			$tpl->set_var("notes",$notes);
			if(!isset($Employees['in_time']) && !isset($Employees['out_time']))
			{
				$tpl->set_var("LeaveType",'');
			}
			else
			{
				if((((isset($Employees['in_time']) && $Employees['in_time'] =='00:00:00') && (isset($Employees['out_time']) && $Employees['out_time'] =='00:00:00')) || ((isset($Employees['in_time']) && $Employees['in_time'] =='') && (isset($Employees['out_time']) && $Employees['out_time'] ==''))) && $getLeaveType == "")
				{
					$tpl->set_var("LeaveType",'');
				}
				else
				{
					if($getLeaveType != '')
					{
						$tpl->set_var("LeaveType",$getLeaveType);
					}
					else
					{
						$tpl->set_var("LeaveType",'P');
					}
				}
			}
			$tpl->set_var("shiftTimes",$org_shift_tim);
			
			$showred = "";
			if(isset($Employees["isExceededBreak"]) && $Employees["isExceededBreak"] == 1)
				$showred = "style='color:red;'";

			$tpl->set_var("showred",$showred);

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

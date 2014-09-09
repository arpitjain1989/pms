<?php 

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('attendance_view_all.html','main_container');

	$PageIdentifier = "AttendanceView";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Attendance");
	$breadcrumb = '<li class="active">View Attendance</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.attendance.php');
	include_once('includes/class.shifts.php');
	
	$objAttendance = new attendance();
	$objShifts = new shifts();

	$id = $_SESSION['id'];
	if(isset($_POST['month']) && $_POST['month'] != '')
	{
		$month = $_POST['month'];
	}
	else
	{
		$month = Date('m');
	}

	if(isset($_POST['year']) && $_POST['year'] != '')
	{
		$year = $_POST['year'];
	}
	else
	{
		$year = Date('Y');
	}

	$tpl->set_var("cur_month",$month);
	$tpl->set_var("cur_year",$year);
	//echo 'month'.$month.'year'.$year;
	
	$arrAttendances = $objAttendance->fnGetAttendanceByIdAndDate($id,$month,$year);
	//echo '<pre>'; print_r($arrAttendances);
	
	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");

	$curYear = Date('Y');
	$arrYear = array($curYear, $curYear-1);
	
	
	
	$tpl->set_var("DisplayYearBlock","");
	if(count($arrYear) > 0)
	{
		foreach($arrYear as $curYr)
		{
			$tpl->set_var("curyr",$curYr);
			$tpl->parse("DisplayYearBlock",true);
		}
	}
	$tpl->set_var("FillAttendanceInformation","");
	if(count($arrAttendances) > 0)
	{
		foreach($arrAttendances as $attendance)
		{
			//echo '<pre>';print_r($attendance);
			$getLeaveType = $objShifts->fnGetLeaveTypes($attendance['leave_id']);

			//echo '<br>getLeaveType---'.$getLeaveType.':::attendance-----'.$attendance['date'].'====attendance[in_time]!!!!:'.$attendance[in_time];
			
			if((($attendance["in_time"] =='00:00:00' && $attendance["out_time"] =='00:00:00') || ($attendance["in_time"] =='' && $attendance["out_time"] =='')) && $getLeaveType == "")
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

			$showred = "";
			if($attendance["isExceededBreak"] == 1)
				$showred = "style='color:red;'";

			$tpl->set_var("showred",$showred);

			
			$tpl->SetAllValues($attendance);
			$tpl->parse("FillAttendanceInformation",true);
		}
	}

	$tpl->pparse('main',false);
?>

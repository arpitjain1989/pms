<?php
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('leave_history.html','main_container');

	$PageIdentifier = "LeaveHistory";
	include_once('userrights.php');

	$curDate = Date('Y-m-d');
	
	$tpl->set_var("mainheading","Leave History");
	$breadcrumb = '<li class="active">Leave History</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.attendance.php');
	$objAttendance = new attendance();

	include_once('includes/class.leave.php');
	$objLeave = new leave();

	include_once('includes/class.employee.php');
	$objEmployee = new employee();
	
	include_once('includes/class.shifts.php');
	$objShifts = new shifts();

	$employeeId = $_REQUEST['id'];

	$month = time();
	$current_month = date('Y-m');
	$months[] = $current_month;
	for ($i = 1; $i < 12; $i++)
	{
		$month = strtotime('last month', $month);
		$months[] = date("Y-m", $month);
	}
	
	$monate = array(1=>"January",2=>"February",3=>"March",4=>"April",5=>"May",6=>"June",7=>"July",8=>"August",9=>"September",10=>"October",11=>"November",12=>"December");
	
	$tpl->set_var('TableValueFill','');

	foreach($months as $mon)
	{
		//$splitted = preg_split('/-/',$mon);
		$splitted = explode('-',$mon);
		$yearNumber = $splitted['0'];
		$monthNum = $splitted['1'];
		$monthName = date("F", mktime(0, 0, 0, $monthNum, 10));
		
		$arrLeaves = $objAttendance->fnGetEmployeeLeaveByMonthYr($employeeId,$monthNum,$yearNumber);
		
		$tpl->set_var('FillLeaveInformation','');
		$tpl->set_var('FillLeaveInformationNone','');
		
		if(count($arrLeaves) > 0)
		{
			foreach($arrLeaves as $currentLeave)
			{
				$tpl->set_var("leave_date",$currentLeave["date"]);
				$tpl->set_var("leave_status",$currentLeave["title"]);
				
				$tpl->parse('FillLeaveInformation',true);
			}
		}
		else
		{
			$tpl->parse('FillLeaveInformationNone',false);
		}

		/*
		$TotalLeaves = $objLeave->fnGetAllLeavesByEmployeeId($employeeId,$monthNum,$yearNumber);

		//print_r($TotalLeaves);
		$tpl->set_var('FillLeaveInformation','');
		$tpl->set_var('FillLeaveInformationNone','');
		//echo '<br>----'.count($TotalLeaves).'----<br>';
		if(count($TotalLeaves) > 0 )
		{
			foreach($TotalLeaves as $leaves)
			{
				//print_r($leaves);
				
				//$LeaveStatus = $objLeave->checkPresent($employeeId,$leaves['start_date'],$leaves['end_date']);
				//echo 'LeaveStatus'.$LeaveStatus;
				
				$tpl->set_var("nodays",$leaves['noOfDays']);
				$tpl->set_var("emp_name",$leaves['emp_name']);
				$tpl->set_var("start_date",$leaves['startdate']);
				$tpl->set_var("end_date",$leaves['enddate']);
				$tpl->set_var("leave_reason",$leaves['leave_reason']);

				if($leaves['leave_status'] == 1)
				{
					$tpl->set_var("status_final",'Approved');
				}
				else if($leaves['leave_status'] == 2)
				{
					$tpl->set_var("status_final",'Dis-approved');
				}
				else if($leaves['leave_status'] == 0 && $leaves['active_status'] == 1)
				{
					$tpl->set_var("status_final",'Cancel');
				}
				else if($leaves['leave_status'] == 0 && $leaves['active_status'] == 0)
				{
					$tpl->set_var("status_final",'Pending');
				}
				$tpl->parse('FillLeaveInformation',true);
			}
		}
		else
		{
			$tpl->set_var("NoRecords",'No records exists.');
			$tpl->parse('FillLeaveInformationNone',true);
		}*/

		$tpl->set_var("monthName",$monthName);
		$tpl->set_var("year",$yearNumber);
		$tpl->parse('TableValueFill',true);
	}
	
	$tpl->pparse('main',false);
?>

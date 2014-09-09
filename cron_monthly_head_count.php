<?php
	include('common.php');
	$tpl = new Template($app_path);

	//Initialize output buffer
	//ob_start();

	//$tpl->load_file('template.html','main');
	$tpl->load_file('cron_autoRctMailSend.html','main');
	
	$curDate = Date('Y-m-d');
	$curYear = Date('Y');
	$curMonth = Date('m');

	include_once('includes/class.attendance.php');
	include_once('includes/class.employee.php');
	
	$objAttendance = new attendance();
	$objEmployee = new employee();                                                             

	$curDate = date('Y-m-d');

	$getAllReporintHeads = $objAttendance->fnGetEmployees($curDate);
	
	//echo '<pre>'; print_r($getAllReporintHeads);
	//$getAllReporintHeads = $objEmployee->fnGetAllReportingHeads();

	//echo '<pre>'; print_r($getAllReporintHeads);
	$prev_date = date("Y-m-d", strtotime( '-1 days' ));
	
	//echo '<br>start_date:'.$start_date = date("Y-01-01"); 
	//echo '<br>end_date:'.$last_date = date("Y-03-31");
	echo '<br>end_date:'.$last_date = date("Y-m-d");
	//$headCount = $objEmployee->fnGetHeadCount('4');
	$date_month = date('Y-m-d');
	/* Get head count for each previous date and save */
	/*if(strtotime($start_date) <= strtotime($end_date))
	{
		foreach($getAllReporintHeads as $reporting_head)
		{
			//echo '<br>--------------'.$reporting_head['employee_id'].'<br>----------------';
			//print_r($reporting_head); die;
			$headCount = $objEmployee->fnGetHeadCount($reporting_head['employee_id']);
		}
	}*/
	/* Get head count for two dates set in start_date and end_date variables */
	//foreach($getAllReporintHeads as $reporting_head)
	//{
		//$start_date = date("Y-01-01");
		$start_date = date ("Y-m-d", strtotime("-1 day", strtotime(date('Y-m-d'))));
		echo 'here';
		
		while (strtotime($start_date) < strtotime($last_date))
		{
			echo "<br/>-------".$start_date;
			$getAllReporintHeads = $objAttendance->fnGetEmployees($start_date);
			echo '<br>--------------'.$reporting_head['employee_id'].'<br>----------------';	
			foreach($getAllReporintHeads as $reporting_head)
			{
				$headCount = $objEmployee->fnGetHeadCountById($reporting_head['employee_id'],$start_date);
			}
			$start_date = date ("Y-m-d", strtotime("+1 day", strtotime($start_date)));
		}
		
		//print_r($reporting_head); die;
		
	//}
	echo 'done';
	//die;
	
?>

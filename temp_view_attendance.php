<?php

	include('common.php');

	//error_reporting(E^ALL);
	set_time_limit(0);

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('temp_view_attendance.html','main_container');
	
		
	include_once('includes/class.calculation.php');
	
	$objCalculation = new calculation();
	$id = $_REQUEST['id'];
	$tpl->set_var("mainheading","Attendance Report");
	$breadcrumb = '<li class="active">Attendance Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	if($_REQUEST['month'] != '')
	{
		$month = $_REQUEST['month'];
	}
	else
	{
		$month = date('m');
	}

	if($_REQUEST['year'] != '')
	{
		$year = $_REQUEST['year'];
	}
	else
	{
		$year = date('Y');
	}
	
	$getAllCalculation = $objCalculation->fnGetTempMonthlyReport($id,$month,$year);

//print_r($getAllCalculation);

	$tpl->set_var("FillReport","");
	if(count($getAllCalculation) > 0 )
	{
		foreach($getAllCalculation as $calculations)
		{
			$tpl->setAllValues($calculations);
			$tpl->parse("FillReport",true);
		}
	}
	
	$tpl->pparse('main',false);
?>

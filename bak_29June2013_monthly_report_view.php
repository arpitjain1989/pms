<?php

	include('common.php');

	//error_reporting(E^ALL);
	set_time_limit(0);

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('monthly_report.html','main_container');
	
		
	include_once('includes/class.calculation.php');
	
	$objCalculation = new calculation();
	
	$tpl->set_var("mainheading","Attendance Report");
	$breadcrumb = '<li class="active">Attendance Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	$month = '05';
	$year = '2013';
	
	$getAllCalculation = $objCalculation->fnGetMonthlyReport($month,$year);

	//echo '<pre>'; print_r($getAllCalculation);

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

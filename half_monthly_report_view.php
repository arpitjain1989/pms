<?php
	include('common.php');

	error_reporting(E^ALL);
	set_time_limit(0);

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('half_monthly_report.html','main_container');
	
	include_once('includes/class.calculation.php');
	
	$objCalculation = new calculation();
	
	$tpl->set_var("mainheading","Attendance Report");
	$breadcrumb = '<li class="active">Attendance Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	$month = '05';
	$year = '2013';
	
	$getAllCalculation = $objCalculation->fnGetAllHalfCalculation($month,$year);
	$tpl->set_var("FillReport","");
	if(count($getAllCalculation) > 0 )
	{
		foreach($getAllCalculation as $calculations)
		{
		//echo '<pre>';
		//print_r($calculations);
			$tpl->setAllValues($calculations);
			$resultArr = json_decode($calculations["reason"]);

			if($resultArr)
			{
				$tpl->set_var("p",$resultArr->p);
				$tpl->set_var("total_plt",$resultArr->total_plt);
				$tpl->set_var("ppl",$resultArr->ppl);
				$tpl->set_var("uhl",$resultArr->uhl);
				$tpl->set_var("total_phl",$resultArr->total_phl);
				$tpl->set_var("wo",$resultArr->wo);
				$tpl->set_var("ph",$resultArr->ph);
				$tpl->set_var("ha",$resultArr->ha);
				$tpl->set_var("hlwp",$resultArr->hlwp);
				$tpl->set_var("a",$resultArr->a);
				$tpl->set_var("plwp",$resultArr->plwp);
				$tpl->set_var("ulwp",$resultArr->ulwp);
				$tpl->set_var("upl",$resultArr->upl);
				$tpl->set_var("total",$resultArr->total_present);
				$tpl->set_var("deducted_days",$resultArr->deducted_days);
				$tpl->set_var("pay_days",$resultArr->payDays);
				$tpl->set_var("OpeningLeavesBalance",$resultArr->OpeningLeavesBalance);
				$tpl->set_var("leave_earn",$resultArr->leave_earn);
				$tpl->set_var("pl_consume",$resultArr->pl_consume);
				$tpl->set_var("ClosingLeavesBalance",$resultArr->ClosingLeavesBalance);
			}
			
			//print_r($resultArr);
			$tpl->parse("FillReport",true);
		}
	}
	
	$tpl->pparse('main',false);
?>

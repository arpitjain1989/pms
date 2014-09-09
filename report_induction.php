<?php 

	include('common.php');
	$tpl = new Template($app_path);

	ini_set("max_execution_time",300);

	$tpl->load_file('template.html','main');
	$tpl->load_file('report_induction.html','main_container');

	$PageIdentifier = "InductionReport";
	include_once('userrights.php');

	
	$tpl->set_var("mainheading","Induction Report");
	$breadcrumb = '<li class="active">Induction Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.employee.php');
	
	$objEmployee = new employee();

	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'pending')
	{
		$arrInductionDetails = $objEmployee->fnGetAllInductionDetails('pending');
	}
	else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'attend')
	{
		$arrInductionDetails = $objEmployee->fnGetAllInductionDetails('attend');
	}
	else
	{
		$arrInductionDetails = $objEmployee->fnGetAllInductionDetails('all');
	}

	
	

	$tpl->set_var("FillInductionDetails","");
	foreach($arrInductionDetails as $arrInduction)
	{
		
		$tpl->SetAllValues($arrInduction);
		$tpl->parse("FillInductionDetails",true);
	}

	$tpl->pparse('main',false);
?>

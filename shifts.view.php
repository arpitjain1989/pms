<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('shifts.view.html','main_container');

	$PageIdentifier = "Shift";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Shift");
	$breadcrumb = '<li><a href="shifts.php">Manage Shifts</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Shift</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.shifts.php');
	
	$objShift = new shifts();
	
	$arrShifts = $objShift->fnGetShiftById($_REQUEST['id']);

	if(count($arrShifts) > 0)
	{
		if(isset($arrShifts['namaz_applicable']) && $arrShifts['namaz_applicable'] == '1')
		{
			$tpl->set_var('ApplicableDay','Friday');
		}
		else if(isset($arrShifts['namaz_applicable']) && $arrShifts['namaz_applicable'] == '2')
		{
			$tpl->set_var('ApplicableDay','Full week');
		}
		else
		{
			$tpl->set_var('ApplicableDay','-');
		}
		$tpl->SetAllValues($arrShifts);
	}

	$tpl->pparse('main',false);
?>

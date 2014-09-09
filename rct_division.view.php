<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('rct_division.view.html','main_container');

	$PageIdentifier = "RctDivision";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View RCT Division");
	$breadcrumb = '<li><a href="rct_division.php">Manage RCT Division</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View RCT Division</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.rct_division.php');
	
	$objRCTDivision = new rct_division();
	
	$arrRCTDivision = $objRCTDivision->fnGetRCTDivisionById($_REQUEST['id']);
	
	foreach($arrRCTDivision as $arrRCTDivisionvalue)
	{
		$tpl->SetAllValues($arrRCTDivisionvalue);
	}

	$tpl->pparse('main',false);
?>

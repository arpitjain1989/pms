<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('rct_source.view.html','main_container');

	$PageIdentifier = "RctSource";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View RCT Source");
	$breadcrumb = '<li><a href="rct_source.php">Manage RCT Source</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View RCT Source</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.rct_source.php');
	
	$objRCTSource = new rct_source();
	
	$arrRCTSource = $objRCTSource->fnGetRCTSourceById($_REQUEST['id']);
	
	foreach($arrRCTSource as $arrRCTSourcevalue)
	{
		$tpl->SetAllValues($arrRCTSourcevalue);
	}

	$tpl->pparse('main',false);
?>

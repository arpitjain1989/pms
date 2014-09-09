<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('rct_source.add.html','main_container');

	$PageIdentifier = "RctSource";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add / Edit RCTSource");
	$breadcrumb = '<li><a href="rct_source.php">Manage RCTSource</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit RCTSource</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.rct_source.php');
	
	$objRCTSource = new rct_source();
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('RCTSourceid',"$_REQUEST[id]");
	}
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		$insertdata = $objRCTSource->fnInsertRCTSource($_POST);
		if($insertdata)
		{
			header("Location: rct_source.php?info=succ");
			exit;
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
			$updateRCTSource = $objRCTSource->fnUpdateRCTSource($_POST);
			if($updateRCTSource)
		{
			header("Location: rct_source.php?info=update");
			exit;
		}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
	$arrRCTSource = $objRCTSource->fnGetRCTSourceById($_REQUEST['id']);
	foreach($arrRCTSource as $arrRCTSourcevalue)
	{
		$tpl->SetAllValues($arrRCTSourcevalue);
	}
		$tpl->set_var('action','update');
	}
	
	
	$tpl->pparse('main',false);
?>

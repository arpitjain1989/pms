<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('rct_division.add.html','main_container');

	$PageIdentifier = "RctDivision";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add / Edit RCT Division");
	$breadcrumb = '<li><a href="rct_division.php">Manage RCT Division</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit RCT Division</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.rct_division.php');
	
	$objRCTDivision = new rct_division();
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('RCTDivisionid',"$_REQUEST[id]");
	}
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		$insertdata = $objRCTDivision->fnInsertRCTDivision($_POST);
		if($insertdata)
		{
			header("Location: rct_division.php?info=succ");
			exit;
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
			$updateRCTDivision = $objRCTDivision->fnUpdateRCTDivision($_POST);
			if($updateRCTDivision)
		{
			header("Location: rct_division.php?info=update");
			exit;
		}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
		$arrRCTDivision = $objRCTDivision->fnGetRCTDivisionById($_REQUEST['id']);
		foreach($arrRCTDivision as $arrRCTDivisionvalue)
		{
			$tpl->SetAllValues($arrRCTDivisionvalue);
		}
		$tpl->set_var('action','update');
	}
	
	
	$tpl->pparse('main',false);
?>

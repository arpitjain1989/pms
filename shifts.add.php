<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('shifts.add.html','main_container');

	$PageIdentifier = "Shift";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add / Edit Shift");
	$breadcrumb = '<li><a href="shifts.php">Manage Shift</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Shift</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.shifts.php');
	
	$objshifts = new shifts();
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('shiftid',"$_REQUEST[id]");
	}
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		$insertdata = $objshifts->fnInsertShift($_POST);
		if($insertdata)
		{
			header("Location: shifts.php?info=succ");
			exit;
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
		$updateShifts = $objshifts->fnUpdateShifts($_POST);
		if($updateShifts)
		{
			header("Location: shifts.php?info=update");
			exit;
		}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
		$arrShifts = $objshifts->fnGetShiftById($_REQUEST['id']);
		$tpl->SetAllValues($arrShifts);
		$tpl->set_var('action','update');
	}
	
	
	$tpl->pparse('main',false);
?>

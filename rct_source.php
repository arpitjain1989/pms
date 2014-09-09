<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('rct_source.html','main_container');

	$PageIdentifier = "RctSource";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage RCT Source");
	$breadcrumb = '<li class="active">Manage RCT Source</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.rct_source.php');
	
	$objRCTSource = new rct_source();
	$arrRCTSource = $objRCTSource->fnGetAllRCTSource();

	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST['info']))
	{

		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "RCT Source inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "RCT Source updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "No records found.";
				break;
			case 'exist':
				$messageClass = "alert-success";
				$message = "RCT Source deleted successfully.";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] = 'delete')
	{
		$delteRCTSource = $objRCTSource->fnDeleteRCTSource($_POST);
		if($delteRCTSource)
		{
			header("Location: rct_source.php?info=delete");
		}
		else
		{
			
		}
	}
	
	$tpl->set_var("FillRctSheetValues","");
	foreach($arrRCTSource as $arrRCTSourcevalue)
	{
		$tpl->SetAllValues($arrRCTSourcevalue);
		$tpl->parse("FillRctSheetValues",true);
	}

	$tpl->pparse('main',false);
?>

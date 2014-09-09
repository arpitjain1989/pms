<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('rct_division.html','main_container');

	$PageIdentifier = "RctDivision";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage RCT Division");
	$breadcrumb = '<li class="active">Manage RCT Division</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.rct_division.php');
	
	$objRCTDivision = new rct_division();
	$arrRCTDivision = $objRCTDivision->fnGetAllRCTDivision();

	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST['info']))
	{

		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "RCT Division inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "RCT Division updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "No records found.";
				break;
			case 'exist':
				$messageClass = "alert-success";
				$message = "RCT Division deleted successfully.";
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
		$delteRCTDivision = $objRCTDivision->fnDeleteRCTDivision($_POST);
		if($delteRCTDivision)
		{
			header("Location: rct_division.php?info=delete");
		}
		else
		{
			
		}
	}
	
	$tpl->set_var("FillRctSheetValues","");
	foreach($arrRCTDivision as $arrRCTDivisionvalue)
	{
		$tpl->SetAllValues($arrRCTDivisionvalue);
		$tpl->parse("FillRctSheetValues",true);
	}

	$tpl->pparse('main',false);
?>

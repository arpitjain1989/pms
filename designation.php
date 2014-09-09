<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('designation.html','main_container');

	$PageIdentifier = "Designation";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Designation");
	$breadcrumb = '<li class="active">Manage Designation</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.designation.php');
	
	$objDesignation = new designations();
	$arrDesignations = $objDesignation->fnGetAllDesignations();

	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST['info']))
	{

		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Designation inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "Designation updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "Designation deleted successfully.";
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
		$deltedesignation = $objDesignation->fnDeleteDesignation($_POST);
		if($deltedesignation)
		{
			header("Location: designation.php?info=delete");
		}
		else
		{
			
		}
	}
	
	$tpl->set_var("FillDesignationValues","");
	foreach($arrDesignations as $arrDesignationvalue)
	{
		$tpl->SetAllValues($arrDesignationvalue);
		$tpl->parse("FillDesignationValues",true);
	}

	$tpl->pparse('main',false);
?>

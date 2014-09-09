<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('roles.html','main_container');

	$PageIdentifier = "Roles";
	include_once('userrights.php');
	
	$tpl->set_var("mainheading","Manage Roles");
	$breadcrumb = '<li class="active">Manage Roles</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	
	include_once('includes/class.roles.php');
	
	$objRoles = new roles();
	$arrRoles = $objRoles->fnGetAllRoles();
	
	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST['info']))
	{

		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Role inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "Role updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "Role deleted successfully.";
				break;
			case 'upload':
				$messageClass = "alert-error";
				$message = "Roles deleted successfully.";
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
		$deltedepartment = $objRoles->fnDeleteRoles($_POST);
		if($deltedepartment)
		{
			header("Location: roles.php?info=delete");
		}
	}
	
	$tpl->set_var("FillRolesValues","");

	if(count($arrRoles) >0)
	{
		foreach($arrRoles as $arrRolesvalue)
		{	
			$tpl->SetAllValues($arrRolesvalue);
			/*if(isset($arrRolesvalue['status']) && $arrRolesvalue['status'] == '0')
			{
				$tpl->set_var("state","Active");
			}
			else
			{
				$tpl->set_var("state","De-Active");
			}*/
			$tpl->parse("FillRolesValues",true);
		}
	}
	else
	{
		$tpl->set_var("FillHideDelete","");
		$tpl->set_var("norecord","No records found.");
	}
	
	$tpl->pparse('main',false);
?>

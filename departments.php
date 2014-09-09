<?php 

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('departments.html','main_container');

	$PageIdentifier = "Departments";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Departments");
	$breadcrumb = '<li class="active">Manage Departments</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.departments.php');
	
	$objDepartment = new departments();
	$arrDepartments = $objDepartment->fnGetAllDepartments();

	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST['info']))
	{

		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Department inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "Department updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "No records found.";
				break;
			case 'exist':
				$messageClass = "alert-success";
				$message = "Department deleted successfully.";
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
		$deltedepartment = $objDepartment->fnDeleteDepartment($_POST);
		if($deltedepartment)
		{
			header("Location: departments.php?info=delete");
		}
		else
		{
			
		}
	}
	
	$tpl->set_var("FillDepartmentValues","");
	foreach($arrDepartments as $arrDepartmentvalue)
	{
		$tpl->SetAllValues($arrDepartmentvalue);
		$tpl->parse("FillDepartmentValues",true);
	}

	$tpl->pparse('main',false);
?>

<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('project.html','main_container');

	$PageIdentifier = "Project";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Projects");
	$breadcrumb = '<li class="active">Manage Projects</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.project.php');
	
	$objProject = new project();
	$arrProject = $objProject->fnGetAllProject();
	
	
	if(isset($_REQUEST['info']))
	{
		if($_REQUEST['info'] == 'succ')
		{
			$tpl->set_var('message',"Project inserted successfully.");
		}
		else if($_REQUEST['info'] == 'update')
		{
			$tpl->set_var('message',"Project updated successfully.");
		}
		else if($_REQUEST['info'] == 'delete')
		{
			$tpl->set_var('message',"Project deleted successfully.");
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] = 'delete')
	{
		$deltedepartment = $objProject->fnDeleteProject($_POST);
		if($deltedepartment)
		{
			header("Location: project.php?info=delete");
		}
	}
	
	$tpl->set_var("FillProjectValues","");

	if(count($arrProject) >0)
	{
		foreach($arrProject as $arrProjectvalue)
		{	
			$tpl->SetAllValues($arrProjectvalue);
			if($arrProjectvalue['status'] == '0')
			{
				$tpl->set_var("state","Active");
				$tpl->set_var("active","");
			}
			else
			{
				$tpl->set_var("state","De-Active");
				$tpl->set_var("active","activeornot");
			}
			$tpl->parse("FillProjectValues",true);
		}
	}
	else
	{
		$tpl->set_var("FillHideDelete","");
		$tpl->set_var("norecord","No records found.");
	}
	
	
	
	
	$tpl->pparse('main',false);
?>

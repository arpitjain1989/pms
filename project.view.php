<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('project.view.html','main_container');
	
	$PageIdentifier = "Project";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Projects");
	$breadcrumb = '<li><a href="project.php">Manage Projects</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Projects</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.project.php');
	
	$objDepartment = new project();
	
	$arrProject = $objDepartment->fnGetProjectById($_REQUEST['id']);

	if($arrProject)
	{
		if($arrProject['status'] == '0')
		{
			$tpl->set_var("stat","Active");
		}
		else
		{
			$tpl->set_var("stat","Active");
		}
	}
	
	foreach($arrProject as $arrDepartmentvalue)
	{
		$tpl->SetAllValues($arrDepartmentvalue);
	}

	$tpl->pparse('main',false);
?>
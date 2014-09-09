<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('project.add.html','main_container');

	$PageIdentifier = "Project";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add / Edit Projects");
	$breadcrumb = '<li><a href="project.php">Manage Projects</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Projects</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.project.php');
	
	$objProject = new project();
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('projectid',"$_REQUEST[id]");
	}
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		$insertdata = $objProject->fnInsertProject($_POST);
		if($insertdata)
		{
			header("Location: project.php?info=succ");
			exit;
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
			$updateProject = $objProject->fnUpdateProject($_POST);
			if($updateProject)
		{
			header("Location: project.php?info=update");
			exit;
		}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
	$arrProject = $objProject->fnGetProjectById($_REQUEST['id']);
	foreach($arrProject as $arrProjectvalue)
	{
		$tpl->SetAllValues($arrProjectvalue);
	}
		$tpl->set_var('action','update');
	}
	
	$arrGetClients = $objProject->fnGetClients();
	$tpl->set_var("FillClientValues","");
	if(count($arrGetClients) > 0)
	{
		foreach($arrGetClients as $arrClients)
		{
			$tpl->SetAllValues($arrClients);
			$tpl->parse("FillClientValues",true);
		}
	}
	
	$tpl->pparse('main',false);
?>
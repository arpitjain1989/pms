<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('task.add.html','main_container');

	$PageIdentifier = "Task";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add / Edit Tasks");
	$breadcrumb = '<li><a href="task.php">Manage Tasks</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Tasks</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.task.php');
	
	$objTask = new task();
	$arrAllProjects = $objTask->fnGetAllProjects();
	$arrAllJobTypes =$objTask->fnGetAllJobTypes();
	
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('taskid',"$_REQUEST[id]");
	}
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		$insertdata = $objTask->fnInsertTask($_POST);
		if($insertdata)
		{
			header("Location: task.php?info=succ");
			exit;
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
			$updateTask = $objTask->fnUpdateTask($_POST);
			if($updateTask)
		{
			header("Location: task.php?info=update");
			exit;
		}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
	$arrTask = $objTask->fnGetTaskById($_REQUEST['id']);
	foreach($arrTask as $arrTaskvalue)
	{
		$tpl->SetAllValues($arrTaskvalue);
	}
		$tpl->set_var('action','update');
	}
	
	
	$tpl->set_var("FillProjectValues","");
	if(count($arrAllProjects) > 0)
	{
		foreach($arrAllProjects as $arrProjects)
		{
			$tpl->SetAllValues($arrProjects);
			$tpl->parse("FillProjectValues",true);
		} 
	}
	
	
	$tpl->set_var("FillJobTypeValues","");
	if(count($arrAllJobTypes) > 0)
	{
		foreach($arrAllJobTypes as $arrJobTypes)
		{
			$tpl->SetAllValues($arrJobTypes);
			$tpl->parse("FillJobTypeValues",true);
		} 
	}
	
	
	$tpl->pparse('main',false);
?>
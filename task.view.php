<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('task.view.html','main_container');

	$PageIdentifier = "Task";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Tasks");
	$breadcrumb = '<li><a href="task.php">Manage Tasks</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Tasks</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.task.php');
	
	$objDepartment = new task();
	
	$arrTask = $objDepartment->fnGetTaskById($_REQUEST['id']);
	if($arrTask)
	{
		if($arrTask[0][overtime] == '0')
		{
			$tpl->set_var("newovertime","Yes");
		}
		else
		{
			$tpl->set_var("newovertime","No");
		}
		if($arrTask[0]['rework'] == '0')
		{
			$tpl->set_var("newrework","Yes");
		}
		else
		{
			$tpl->set_var("newrework","No");
		}
	}
	
	foreach($arrTask as $arrDepartmentvalue)
	{
		$tpl->SetAllValues($arrDepartmentvalue);
	}

	$tpl->pparse('main',false);
?>
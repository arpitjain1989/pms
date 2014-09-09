<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('task.html','main_container');

	$PageIdentifier = "Task";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Tasks");
	$breadcrumb = '<li class="active">Manage Tasks</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.task.php');
	
	$objTask = new task();
	$arrAllTask = $objTask->fnGetAllTask();
	
	
	if(isset($_REQUEST['info']))
	{
		if($_REQUEST['info'] == 'succ')
		{
			$tpl->set_var('message',"Task inserted successfully.");
		}
		else if($_REQUEST['info'] == 'update')
		{
			$tpl->set_var('message',"Task updated successfully.");
		}
		else if($_REQUEST['info'] == 'delete')
		{
			$tpl->set_var('message',"Task deleted successfully.");
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] = 'delete')
	{
		$deltetask = $objTask->fnDeleteTask($_POST);
		if($deltetask)
		{
			header("Location: task.php?info=delete");
		}
		else
		{
			
		}
	}
	
	$tpl->set_var("FillTaskValues","");

	if(count($arrAllTask) >0)
	{
		foreach($arrAllTask as $arrAllTaskvalue)
		{	
			$tpl->SetAllValues($arrAllTaskvalue);
			if($arrAllTaskvalue['has_target'] == '0')
			{
				$tpl->set_var("state","Yes");
			}
			else
			{
				$tpl->set_var("state","No");
			}
			$tpl->parse("FillTaskValues",true);
		}
	}
	else
	{
		$tpl->set_var("FillHideDelete","");
		$tpl->set_var("norecord","No records found.");
	}
	
	
	
	
	$tpl->pparse('main',false);
?>
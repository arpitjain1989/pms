<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('form.html','main_container');

	$PageIdentifier = "Project";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Project Forms");
	$breadcrumb = '<li><a href="project.php">Manage Projects</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Manage Forms</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.forms.php');
	
	$objForm = new forms();
	$arrForm = $objForm->fnGetAllFormvalues($_REQUEST['id']);
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('projectid',"$_REQUEST[id]");
	}
	
	if(isset($_REQUEST['info']))
	{
		if($_REQUEST['info'] == 'succ')
		{
			$tpl->set_var('message',"Form inserted successfully.");
		}
		else if($_REQUEST['info'] == 'update')
		{
			$tpl->set_var('message',"Form updated successfully.");
		}
		else if($_REQUEST['info'] == 'delete')
		{
			$tpl->set_var('message',"Form deleted successfully.");
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] = 'delete')
	{
	
		$deltedepartment = $objForm->fnDeleteForm($_POST);
		if($deltedepartment)
		{
			header("Location: form.php?info=delete&id=".$_REQUEST['id']);
		}
		else
		{
			
		}
	}
	
	$tpl->set_var("FillFormValues","");

	if(count($arrForm) >0)
	{
		foreach($arrForm as $arrFormvalue)
		{
			$tpl->SetAllValues($arrFormvalue);
			if($arrFormvalue['status'] == '0')
			{
				$tpl->set_var("state","Active");
			}
			else
			{
				$tpl->set_var("state","De-Active");
			}
			$tpl->parse("FillFormValues",true);
		}
	}
	else
	{
		$tpl->set_var("FillHideDelete","");
		$tpl->set_var("norecord","No records found.");
	}
		
	$tpl->pparse('main',false);
?>
<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('form.add.html','main_container');

	$PageIdentifier = "Project";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Project Forms");
	$breadcrumb = '<li><a href="project.php">Manage Projects</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li><a href="form.php?id='.$_REQUEST["id"].'&action=form">Manage Forms</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Form</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.forms.php');
	
	$objForm = new forms();
	
	$formFieldValues = $objForm->fnFormFieldValues($_REQUEST['id']);
	
	
	$tpl->set_var('FormFieldValues','');
	if(count($formFieldValues)> 0)
	{
		foreach($formFieldValues as $formField) 
		{
			$tpl->SetAllValues($formField);
			$tpl->parse("FormFieldValues",true);
		}
	}
	
	if(isset($_POST['hdnprojectid']))
	{
		$tpl->set_var('projectid',"$_REQUEST[id]");
	}
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		$insertdata = $objForm->fnInsertForm($_REQUEST['id'],$_POST['formid']);
		if($insertdata)
		{
			header("Location: form.php?info=succ&id=".$_REQUEST['id']);
			exit;
		}
	}
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('projectid',"$_REQUEST[id]");
	}
	
	$tpl->pparse('main',false);
?>
<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('form_field.add.html','main_container');

	$PageIdentifier = "FormFields";
	include_once('userrights.php');
	
	$tpl->set_var("mainheading","Add / Edit Form Fields");
	$breadcrumb = '<li><a href="form_field.php">Manage Form Fields</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Form Fields</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.forms.php');
	
	$objForms = new forms();
	
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('formfieldid',"$_REQUEST[id]");
	}
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		if(!isset($_POST['iscompulsory']))
		{
			$_POST['iscompulsory'] = '0';
		}
		$insertdata = $objForms->fnInsertFormField($_POST);
		if($insertdata)
		{
			header("Location: form_field.php?info=succ");
			exit;
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
		if(!isset($_POST['iscompulsory']))
		{
			$_POST['iscompulsory'] = '0';
		}
		
		$updateForms = $objForms->fnUpdateForms($_POST);
			
		if($updateForms)
		{
			header("Location: form_field.php?info=update");
			exit;
		}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
		$arrClients = $objForms->fnGetFormFieldById($_REQUEST['id']);
		if(isset($arrClients))
		{
			if($arrClients[0]['iscompulsory'] == '1')
			{
				$tpl->set_var('check','checked="checked"');	
			}
			else
			{
				$tpl->set_var('check','');	
			}
		}
		foreach($arrClients as $arrClientsvalue)
		{
			$tpl->SetAllValues($arrClientsvalue);
		}
			$tpl->set_var('action','update');
	}
	
	
	
	
	
	$tpl->pparse('main',false);
?>
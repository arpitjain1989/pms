<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('form_field.html','main_container');

	$PageIdentifier = "FormFields";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Form Fields");
	$breadcrumb = '<li class="active">Manage Form Fields</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.forms.php');
	
	$objForms = new forms();
	$arrAllForms = $objForms->fnGetAllForms();
	//print_r($arrAllForms);
	
	if(isset($_REQUEST['info']))
	{
		if($_REQUEST['info'] == 'succ')
		{
			$tpl->set_var('message',"Form Field inserted successfully.");
		}
		else if($_REQUEST['info'] == 'update')
		{
			$tpl->set_var('message',"Form Field updated successfully.");
		}
		else if($_REQUEST['info'] == 'delete')
		{
			$tpl->set_var('message',"Form Field deleted successfully.");
		}
	}
	

if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] = 'delete')
	{
		$delteform_field = $objForms->fnDeleteFormField($_POST);
		if($delteform_field)
		{
			header("Location: form_field.php?info=delete");
		}
		else
		{
			
		}
	}
	
	$tpl->set_var("FillFormFieldsValues","");

	if(count($arrAllForms) >0)
	{
		foreach($arrAllForms as $arrAllFormFieldvalue)
		{	
			$tpl->SetAllValues($arrAllFormFieldvalue);
			switch($arrAllFormFieldvalue['type'])
			{
				case '1':
					$tpl->set_var("type","TextBox");
					break;
				case '2':
					$tpl->set_var("type","TextArea");
					break;	
				case '3':
					$tpl->set_var("type","DatePicker");
					break;
			}
			$tpl->parse("FillFormFieldsValues",true);
		}
	}
	else
	{
		$tpl->set_var("FillHideDelete","");
		$tpl->set_var("norecord","No records found.");
	}
	
	$tpl->pparse('main',false);
?>
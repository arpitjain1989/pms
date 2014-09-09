<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('form_field.view.html','main_container');

	$PageIdentifier = "FormFields";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Form Fields");
	$breadcrumb = '<li><a href="form_field.php">Manage Form Fields</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Form Fields</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.forms.php');
	
	$objForms = new forms();
	
	$arrFormField = $objForms->fnGetFormFieldById($_REQUEST['id']);
	if($arrFormField)
	{
		switch($arrFormField[0]['type'])
			{
				case '1':
					$tpl->set_var("type1","TextBox");
					break;
				case '2':
					$tpl->set_var("type1","TextArea");
					break;	
				case '3':
					$tpl->set_var("type1","DatePicker");
					break;
			}
			switch($arrFormField[0]['iscompulsory'])
			{
				case '1':
					$tpl->set_var("iscompulsory1","Yes");
					break;
				case '0':
					$tpl->set_var("iscompulsory1","No");
					break;	
			}
			switch($arrFormField[0]['validation_rule'])
			{
				case '1':
					$tpl->set_var("validation_rule1","Email");
					break;
				case '2':
					$tpl->set_var("validation_rule1","Number");
					break;	
			}
	}
	
	foreach($arrFormField as $arrFormFieldvalue)
	{
		$tpl->SetAllValues($arrFormFieldvalue);
	}

	$tpl->pparse('main',false);
?>
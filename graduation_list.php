<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('graduation_list.html','main_container');

	$PageIdentifier = "Graduation";
	include_once('userrights.php');
	
	$tpl->set_var("mainheading","Manage Graduation");
	$breadcrumb = '<li class="active">Manage Graduation</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.graduation.php');

	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Graduation added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Graduation already added. Cannot add again.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$objGraduation = new graduation();
	$arrGraduation = $objGraduation->fnGetAllGraduation();

	$tpl->set_var("FillGraduationList","");
	if(count($arrGraduation) >0)
	{
		foreach($arrGraduation as $curGraduation)
		{
			$tpl->SetAllValues($curGraduation);
			$tpl->parse("FillGraduationList",true);
		}
	}

	$tpl->pparse('main',false);

?>

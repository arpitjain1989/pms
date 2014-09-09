<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('post_graduation_list.html','main_container');

	$PageIdentifier = "PostGraduation";
	include_once('userrights.php');
	
	$tpl->set_var("mainheading","Manage Post Graduation");
	$breadcrumb = '<li class="active">Manage Post Graduation</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.post_graduation.php');

	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Post Graduation added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Post Graduation already added. Cannot add again.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$objPostGraduation = new post_graduation();
	$arrPostGraduation = $objPostGraduation->fnGetAllPostGraduation();

	$tpl->set_var("FillPostGraduationList","");
	if(count($arrPostGraduation) >0)
	{
		foreach($arrPostGraduation as $curPostGraduation)
		{
			$tpl->SetAllValues($curPostGraduation);
			$tpl->parse("FillPostGraduationList",true);
		}
	}

	$tpl->pparse('main',false);

?>

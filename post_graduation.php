<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('post_graduation.html','main_container');

	$PageIdentifier = "PostGraduation";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add Post Graduation");
	$breadcrumb = '<li><a href="post_graduation_list.php">Manage Post Graduation</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add Post Graduation</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.post_graduation.php');

	$objPostGraduation = new post_graduation();

	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$post_graduation = $objPostGraduation->fnGetPostGraduationById($_REQUEST["id"]);
		if(count($post_graduation) > 0)
		{
			$tpl->SetAllValues($post_graduation);
		}
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "SavePostGraduation")
	{
		//echo 'hello'; die;
		$post_graduation_status = $objPostGraduation->fnSavePostGraduation($_POST);

		if($post_graduation_status == 1)
		{
			header("Location: post_graduation_list.php?info=success");
			exit;
		}
		else if($post_graduation_status == 0)
		{
			header("Location: post_graduation_list.php?info=err");
			exit;
		}
	}

	$tpl->pparse('main',false);
?>

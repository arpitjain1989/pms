<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('post_graduation_view.html','main_container');

	$PageIdentifier = "PostGraduation";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Post Graduation");
	$breadcrumb = '<li><a href="graduation_list.php">Manage Post Graduation</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Post Graduation</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.post_graduation.php');
	
	$objPostGraduation = new post_graduation();
	
	$tpl->set_var("DisplayPostGraduationInformationBlock","");
	$tpl->set_var("DisplayNoPostGraduationBlock","");
	
	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$arrPostGraduation = $objPostGraduation->fnGetPostGraduationById($_REQUEST['id']);
		
		if(count($arrPostGraduation) > 0)
		{
			$tpl->SetAllValues($arrPostGraduation);
			$tpl->parse("DisplayPostGraduationInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoPostGraduationBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoPostGraduationddsddBlock",false);
	}
	
	$tpl->pparse('main',false);
?>


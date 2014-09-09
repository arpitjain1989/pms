<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('graduation_view.html','main_container');

	$PageIdentifier = "Graduation";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Graduation");
	$breadcrumb = '<li><a href="graduation_list.php">Manage Graduation</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Graduation</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.graduation.php');
	
	$objGraduation = new graduation();
	
	$tpl->set_var("DisplayGraduationInformationBlock","");
	$tpl->set_var("DisplayNoGraduationBlock","");
	
	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$arrGraduation = $objGraduation->fnGetGraduationById($_REQUEST['id']);
		
		if(count($arrGraduation) > 0)
		{
			$tpl->SetAllValues($arrGraduation);
			$tpl->parse("DisplayGraduationInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoGraduationBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoGraduationddsddBlock",false);
	}
	
	$tpl->pparse('main',false);
?>


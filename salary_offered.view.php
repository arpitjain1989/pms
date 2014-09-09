<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('salary_offered.view.html','main_container');

	$PageIdentifier = "SalaryOffered";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Salary Offered");
	$breadcrumb = '<li><a href="salary_offered.php">Manage Salary Offered</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Salary Offered</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.salary_offered.php');
	
	$objSalaryOffered = new salary_offered();
	
	$arrSalaryOffered = $objSalaryOffered->fnGetSalaryOfferedById($_REQUEST['id']);
	//echo '<pre>'; print_r($arrSalaryOffered);
	
	if(count($arrSalaryOffered) > 0 )
	{
		$tpl->SetAllValues($arrSalaryOffered);
	}

	$tpl->pparse('main',false);
?>

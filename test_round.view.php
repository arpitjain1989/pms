<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('test_round.view.html','main_container');

	$PageIdentifier = "TestRound";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Employee Test");
	$breadcrumb = '<li><a href="test_round.php">Manage Test Round Details</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Test Round Details</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.candidate_list.php');
	
	$objCandidateList = new candidate_list();
	
	$arrEmpTest = $objEmpTest->fnGetEmpTestById($_REQUEST['id']);
	
	foreach($arrEmpTest as $arrEmpTestvalue)
	{
		$tpl->SetAllValues($arrEmpTestvalue);
	}

	$tpl->pparse('main',false);
?>

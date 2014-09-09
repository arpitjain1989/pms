<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('salary_offered_detail.html','main');

	//$PageIdentifier = "Roster";
	//include_once('userrights.php');

	include_once('includes/class.salary_offered.php');
	
	$objSalaryOffered = new salary_offered();
	
	
	//echo $_REQUEST["v"]; die;
	if(isset($_REQUEST["v"]) && trim($_REQUEST["v"]))
	{
		$arrSalaryOffered = $objSalaryOffered->fnGetSalaryOfferedByDesId($_REQUEST["v"]);
		//echo '<pre>'; print_r($arrSalaryOffered); die;
		$tpl->set_var("hdnid",$arrSalaryOffered["id"]);
		$tpl->set_var("lowest",$arrSalaryOffered["lowest_amount"]);
		$tpl->set_var("highest",$arrSalaryOffered["highest_amount"]);
	}
	
	$tpl->pparse('main',false);
?>

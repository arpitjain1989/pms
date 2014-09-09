<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template1.html','main');
	$tpl->load_file('thank_you.html','main_container');

	include_once('includes/class.user_registration.php');
	include_once('includes/class.employee.php');

	$objUserRegistration = new user_registration();
	$objEmployee = new employee();

	$tpl->set_var("FillBestOfLuck","");

	if(isset($_REQUEST['info']) && $_REQUEST["info"] == 'succ')
	{
		$getUserName = $objUserRegistration->fnGetUserNameById($_REQUEST['id']);
		//$tpl->parse("FillBestOfLuck",false);
	}
	if(isset($_REQUEST['page']) && $_REQUEST['page'] == "per_history")
	{
		//echo 'helo'; die;
		$getUserName = $objEmployee->fnGetEmployeeNameById($_REQUEST['id']); 
	}
	else
	{
		$getUserName = $objUserRegistration->fnGetUserNameById($_REQUEST['id']);
		$tpl->parse("FillBestOfLuck",false);
	}

	if(isset($getUserName))
	{
		$tpl->set_var('candidate',$getUserName);
	}
	
	$tpl->pparse('main',false);
?>

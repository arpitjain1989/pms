<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('profile.html','main_container');

	$PageIdentifier = "Profile";
	include_once('userrights.php');
	
	$tpl->set_var("mainheading","User Profile");
	$breadcrumb = '<li class="active">User Profile</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.admin.php');
	
	$objAdmin = new admin();
	
	$message = "";
	$messageClass = "";
	
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Profile updated successfully";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}
	
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'update')
	{
		$updatevalues = $objAdmin->fnUpdateUser($_POST);
		if($updatevalues)
		{
			header("Location: profile.php?info=succ");
			exit;
		}
	}
	
	$UserDetail = $objAdmin->fnGetUserDetailByUsername($_SESSION['username']);
	foreach($UserDetail as $uDetails)
	{
		$tpl->SetAllValues($uDetails);
	}
	
	$tpl->pparse('main',false);
?>

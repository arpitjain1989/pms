<?php 
	include('common.php');
	
	/* if user already logged in redirect to dashboard */
	if(isset($_SESSION["id"]) && trim($_SESSION["id"]) != "" && $_SESSION["id"] != '0')
	{
		header("Location: dashboard.php");
		exit;
	}

	$tpl = new Template($app_path);
	
	$tpl->load_file('login.html','main');
	$tpl->load_file('index.html','main_container');
	
	include_once('includes/class.login.php');
	
	$objLogin = new clsLogin();
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'login')
	{
		$arrCheckLogin = $objLogin->fnCheckUser($_POST['username'],$_POST['password']);

		if($arrCheckLogin == "-1")
		{
			header("Location: index.php?erro=loggedin");
		}
		else if($arrCheckLogin === true)
		{
			header("Location: dashboard.php");
		}
		else
		{
			header("Location: index.php?erro=yes");
		}
		
		exit;
 	}

	if(isset($_REQUEST['erro']) && $_REQUEST['erro'] == 'yes')
	{
		$tpl->set_var("error","Your username and password not match");
	}	
	else if(isset($_REQUEST['erro']) && $_REQUEST['erro'] == 'loggedin' )
	{
		$tpl->set_var("error","User already logged in with another system, cannot login in again");
	}	
	
	$tpl->pparse('main',false);
?>

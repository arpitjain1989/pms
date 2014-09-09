<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('manager_roster_list.html','main_container');

	$PageIdentifier = "ManagerRoster";
	include_once('userrights.php');

	/*if($_SESSION["usertype"] == "employee" && $_SESSION["designation"] != "6")
	{
		header("Location: dashboard.php");
		exit;
	}*/
	
	if($_SESSION["usertype"] == "admin")
		$tpl->set_var("DisplayAddRosterBlock","");

	$tpl->set_var("mainheading","Manage Roster");
	$breadcrumb = '<li class="active">Manage Roster</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.roster.php');

	/*$tpl->set_var("DisplayAddRosterBlock","");
	if(isset($_SESSION["designation"]) && (trim($_SESSION["designation"]) == '7' || trim($_SESSION["designation"]) == '13'))
	{
		$tpl->parse("DisplayAddRosterBlock",false);
	}*/

	$objRoster = new roster();

	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'added':
				$messageClass = "alert-success";
				$message = "Roster added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Roster already added for the week cannot add again.";
				break;
			case 'noroster':
				$messageClass = "alert-error";
				$message = "No roster prepared for specified dates.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);
		}
	}

	$arrRoster = $objRoster->fnGetAllRosters();

	//print_r($arrRoster);

	$tpl->set_var("FillRosters","");
	if(count($arrRoster) > 0)
	{
		foreach($arrRoster as $RosterDays)
		{
			$tpl->setAllValues($RosterDays);
			$tpl->parse("FillRosters",true);
		}
	}

	$tpl->pparse('main',false);
?>

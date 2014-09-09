<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('manager_roster.html','main_container');

	$PageIdentifier = "ManagerRoster";
	include_once('userrights.php');

	if($_SESSION["usertype"] == "admin")
	{
		header("Location: dashboard.php");
		exit;
	}

	/*if($_SESSION["usertype"] == "admin" || ($_SESSION["usertype"] == "employee" && $_SESSION["designation"] != "6"))
	{
		header("Location: dashboard.php");
		exit;
	}*/

	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) != '')
		$tpl->set_var("action",trim($_REQUEST["action"]));

	$tpl->set_var("mainheading","Add Roster");
	$breadcrumb = '<li><a href="manager_roster_list.php">Manage Roster</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add Roster</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.employee.php');
	include_once('includes/class.roster.php');

	$objEmployee = new employee();
	$objRoster = new roster();

	/* Fetching all team leaders */
	/*$arrTeamLeads = $objEmployee->fnGetEmployeesByDesignation('7,13');*/

	/* Fetch managers of employees [direct reporting head of agents / developers etc] */
	/*$arrReportingHead = $objEmployee->fnGetDirectReportingManagers();

	if(count($arrReportingHead) > 0)
	{
		$arrTeamLeads = array_merge($arrReportingHead,$arrTeamLeads);
	}*/

	$arrTeamLeads = $objEmployee->fnGetReportingHeadForRoster();
	
	$start_date = date('Y-m-d', strtotime('next monday'));
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["start"]) && trim($_REQUEST["start"]) != '')
	{
		$start_date = trim($_REQUEST["start"]);
		if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
			$tpl->set_var("selectemployee",trim($_REQUEST["id"]));
	}
	
	$arrRosterDates = $objRoster->fnGetRosterDays($start_date);

	$arrKeys = array_keys($arrRosterDates);

	$start_date = $arrKeys[0];
	$end_date = array_pop($arrKeys);

	$tpl->set_var("startdate",$start_date);
	$tpl->set_var("enddate",$end_date);

	//print_r($arrTeamLeads);die;

	$tpl->set_var("FillTeams","");

	if(count($arrTeamLeads) > 0)
	{
		foreach($arrTeamLeads as $currTL)
		{
			$tpl->set_var("teamsid",$currTL["id"]);
			$tpl->set_var("teamsname",$currTL["name"]."'s Team");

			$tpl->parse("FillTeams",true);
		}
	}

	$tpl->pparse('main',false);
?>

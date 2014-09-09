<?php

	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('team_shift_time.html','main_container');

	$PageIdentifier = "TeamShifts";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Team shifts");
	$breadcrumb = '<li class="active">Manage Team shifts</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	$message = "";
	$messageClass = "";
	
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Shift for the team saved successfully.";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Error saving shift for the team. Please try again.";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	include_once('includes/class.employee.php');
	include_once('includes/class.attendance.php');
	
	$objEmployee = new employee();
	$objAttendance = new attendance();
	
	/* Fetching all team leaders */
	/*$arrTeamLeads = $objEmployee->fnGetEmployeesByDesignation('7,13');*/
	
	/* Fetch managers of employees [direct reporting head of agents / developers etc] */
	/*$arrReportingHead = $objEmployee->fnGetDirectReportingManagers();

	$arrTeamLeads = array_merge($arrTeamLeads,$arrReportingHead);*/

	$arrTeamLeads = $objAttendance->fnGetEmployees(date('Y-m-d'));

	$tpl->set_var("FillTeams","");
	if(count($arrTeamLeads) > 0)
	{
		foreach($arrTeamLeads as $curhead)
		{
			$tpl->set_var("teamid",$curhead["employee_id"]);
			$tpl->set_var("teamname",$curhead["employee_name"]);
			
			$tpl->parse("FillTeams",true);
		}
	}

	$tpl->pparse('main',false);
?>

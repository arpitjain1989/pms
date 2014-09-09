<?php

	include('common.php');
	
	include_once('includes/class.roster.php');
	$objRoster = new roster();

	include_once('includes/class.employee.php');
	$objEmployee = new employee();
	
	$Date = date('Y-m-d', strtotime('next monday'));
	
	$arrUnRosteredTeams = $objRoster->fnGetUnrosteredTeams($Date);
	
	$arrRosterDates = $objRoster->fnGetRosterDays();
	
	$arrKeys = array_keys($arrRosterDates);
	
	$start_date = $arrKeys[0];
	$end_date = array_pop($arrKeys);
	
	if(count($arrUnRosteredTeams) > 0)
	{
		foreach($arrUnRosteredTeams as $curTeam)
		{
			/* Pending leaves and shift movement */	
			$Subject = "Pending leave / shift movement request";

			/*$isManager = false;
			if($curTeam['designation'] == "6")
				$isManager = true;

			if($objRoster->fnGetUnrosteredLeavesAndShiftMovements($start_date, $end_date, $curTeam['id'], $isManager) == 1)
			{
				$content = "Dear ".$curTeam['name'].", <br/>You have pending leave / shift movement requests for the upcomming week.<br/>Please approve / unapprove the leaves / shift movements before the roster is generated or else updates will not be possible.<br><br>Regards,<br>".SITEADMINISTRATOR;

				echo "<br><hr>".$Subject;
				echo "<br>".$content;

				//sendmail($curTeam['email'],$Subject,$content);
			}*/

			if($objRoster->fnGetUnrosteredLeavesAndShiftMovements($start_date, $end_date, $curTeam['id']) == 1)
			{
				$content = "Dear ".$curTeam['name'].", <br/>You have pending leave / shift movement requests for the upcomming week.<br/>Please approve / unapprove the leaves / shift movements before the roster is generated or else updates will not be possible.<br><br>Regards,<br>".SITEADMINISTRATOR;

				//echo "<br><hr>".$Subject;
				//echo "<br>".$content;

				sendmail($curTeam['email'],$Subject,$content);
			}

			$Subject = "Reminder to add roster";
			$for = "your team";
			if($curTeam["for"] != "" and count($curTeam["for"]) > 0)
			{
				$for = implode(", ",$curTeam["for"]);
			}

			$content = "Dear ".$curTeam['name'].", <br/>Roster for ".$for." for the upcomming week starting from ".$Date." is not added.<br/>Please add the roster or else the roster will be auto generated on sunday and updates will not be possible.<br><br>Regards,<br>".SITEADMINISTRATOR;

			//echo "<br><hr>".$Subject;
			//echo "<br>".$content;

			sendmail($curTeam['email'],$Subject,$content);
		}
	}
	
	/* send mail to support teams and ceo */
	$arrEmployee = $objEmployee->fnGetSupportReportingHeads();
	if(count($arrEmployee) > 0)
	{
		foreach($arrEmployee as $curEmployee)
		{
			if($objRoster->fnGetUnrosteredLeavesAndShiftMovements($start_date, $end_date, $curEmployee['id'], true) == 1)
			{
				$content = "Dear ".$curEmployee['name'].", <br/><br/>You have pending leave / shift movement requests for the upcomming week.<br/>Please approve / unapprove the leaves / shift movements before the roster is generated or else updates will not be possible.<br><br>Regards,<br>".SITEADMINISTRATOR;

				//echo "<br><hr>".$Subject;
				//echo "<br>".$content;
				
				sendmail($curTeam['email'],$Subject,$content);
			}
		}
	}
	
	
	/* Send Reminder Mail for IT Support Roster */
	
	include_once("includes/class.it_support_designations.php");
	include_once("includes/class.it_support_roster.php");
	include_once("includes/class.employee.php");
	include_once('includes/class.roster.php');

	$objItSupportDesignation = new it_support_designations();
	$objItSupportRoster = new it_support_roster();
	$objEmployee = new employee();
	$objRoster = new roster();

	$arrRosterDates = $objRoster->fnGetRosterDays();

	$arrDesignations = $objItSupportDesignation->fnGetSupportDesignations();
	$arrDesignations[] = 0;
	
	/* Fetch all the employees in IT Support */
	$flag = false;
	$arrSupportEmployee = $objEmployee->fnGetEmployeesByDesignation(implode(',', $arrDesignations));
	if(count($arrSupportEmployee) > 0)
	{
		foreach($arrSupportEmployee as $curEmployee)
		{
			foreach($arrRosterDates as $k => $v)
			{
				//Check if roster added 
				if(!$objItSupportRoster->fnCheckIfRosterAlreadyEntered($curEmployee["id"], $k))
				{
					$flag = true;
					break;
				}
			}
		}

		if($flag)
		{
			//Roster pending so, send mail for reminder 
			$Subject = "Reminder to add roster";
			$content = "Dear IT Support Team, <br/><br/>Roster for your team for the upcomming week starting from ".$Date." is not added.<br/>Please add the roster or else the roster will be auto generated on sunday.<br><br>Regards,<br>".SITEADMINISTRATOR;

			sendmail("itsupport@transformsolution.net",$Subject,$content);
		}
	}
	
?>

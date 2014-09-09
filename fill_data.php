<?php

	include_once("includes/class.employee.php");

	$objEmployee = new employee();

	$displayVar = "No data found";

	if(session_id() == '') {
		session_start();
	}

	if(isset($_REQUEST["action"]))
	{
		if($_REQUEST["action"] == "fillteamleaders" && isset($_REQUEST["reporting_headid"]))
		{
			//$displayVar = "<select name='teamleader' id='teamleader' onchange='javascript: fnChangeTeamleader();'>";
			$displayVar = "<select name='team_member' id='team_member' class='nostyle' style='width:200px;'>";
			$displayVar .= "<option value=''>Please select</option>";
			if($_REQUEST["reporting_headid"] != "")
			{
				$dt = date("Y-m-t", strtotime($_REQUEST["year"]."-".$_REQUEST["month"]."-01"));
				//$arrEmployee = $objEmployee->fnGetTeamleadersByReportingHead($_REQUEST["reporting_headid"]);
				$arrEmployee = $objEmployee->fnGetAllEmployeesDetailsReleavingDateWise($dt, $_REQUEST["reporting_headid"]);

				if(count($arrEmployee) > 0)
				{
					foreach($arrEmployee as $curEmployeeId => $curEmployeeName)
					{
						$displayVar .= "<option value='".$curEmployeeId."'>".$curEmployeeName."</option>";
					}
				}
			}
			$displayVar .= "</select>";
		}
		/*else if($_REQUEST["action"] == "fillagents" && isset($_REQUEST["managerid"]))
		{
			$displayVar = "<select name='agents' id='agents'>";
			$displayVar .= "<option value=''>Please select</option>";
			if($_REQUEST["managerid"] != "")
			{
				$arrEmployee = $objEmployee->fnGetAgentsByReportingHead($_REQUEST["managerid"]);
				if(count($arrEmployee) > 0)
				{
					foreach($arrEmployee as $curEmployee)
					{
						$displayVar .= "<option value='".$curEmployee["id"]."'>".$curEmployee["name"]."</option>";
					}
				}
			}
			$displayVar .= "</select>";
		}
		else if($_REQUEST["action"] == "fillagents" && isset($_REQUEST["teamleaderid"]))
		{
			$displayVar = "<select name='agents' id='agents'>";
			$displayVar .= "<option value=''>Please select</option>";
			if($_REQUEST["teamleaderid"] != "")
			{
				$arrEmployee = $objEmployee->fnGetEmployeesByReportingHead($_REQUEST["teamleaderid"]);
				if(count($arrEmployee) > 0)
				{
					foreach($arrEmployee as $curEmployee)
					{
						$displayVar .= "<option value='".$curEmployee["id"]."'>".$curEmployee["name"]."</option>";
					}
				}
			}
			$displayVar .= "</select>";
		}*/
		else if($_REQUEST["action"] == "fillissues")
		{
			include_once("includes/class.issue.php");
			$objIssue = new issue();
			
			$displayVar = "<select name='issue_id' id='issue_id'>";
			$displayVar .= "<option value=''>Please select</option>";
			
			if(isset($_REQUEST["categoryid"]) && $_REQUEST["categoryid"] != "")
			{
				$arrIssue = $objIssue->fnGetIssueAccessByIssueCategoryAndDesignation($_REQUEST["categoryid"], $_SESSION["designation"]);
				if(count($arrIssue) > 0)
				{
					foreach($arrIssue as $curIssue)
					{
						$displayVar .= "<option value='".$curIssue["id"]."'>".$curIssue["issue"]."</option>";
					}
				}
			}
			$displayVar .= "</select>";
		}
		else if($_REQUEST["action"] == "fillcallhistoryissues")
		{
			include_once("includes/class.issue.php");
			$objIssue = new issue();
			
			$displayVar = "<select name='search_issue_id' id='search_issue_id'>";
			$displayVar .= "<option value=''>Please select</option>";
			
			if(isset($_REQUEST["categoryid"]) && $_REQUEST["categoryid"] != "")
			{
				$arrIssue = $objIssue->fnGetIssueByCategoryId($_REQUEST["categoryid"]);
				if(count($arrIssue) > 0)
				{
					foreach($arrIssue as $curIssue)
					{
						$displayVar .= "<option value='".$curIssue["id"]."'>".$curIssue["issue"]."</option>";
					}
				}
			}
			$displayVar .= "</select>";
		}
	}

	echo $displayVar;

?>

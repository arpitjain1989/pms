<?php
	include('common.php');
	$tpl = new Template($app_path);
	//print_r($_SESSION);
	$tpl->load_file('template.html','main');
	$tpl->load_file('emergency_leave_form.html','main_container');

	$PageIdentifier = "EmergencyLeaveForm";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Emergency Leave");
	$breadcrumb = '<li><a href="emergency_leave_list.php">Manage Emergency Leave</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Emergency Leave</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');
	include_once('includes/class.attendance.php');
	include_once('includes/class.phpmailer.php');
	include_once('includes/class.designation.php');

	$objLeave = new leave();
	$objEmployee = new employee();
	$objAttendance = new attendance();
	$objDesignation = new designations();

	$curDate = Date('Y-m-d');
	$tpl->set_var("curdate",$curDate);
	$tpl->set_var("curdesignation",$_SESSION['designation']);

	$arrEmployees = $objEmployee->fnGetAllEmployeesDetails($_SESSION["id"]);
	$arrtemp = array();
	
	$tpl->set_var("movement_date",Date('Y-m-d'));
	$tpl->set_var("previous_date",date('Y-m-d', strtotime('-1 day')));

	/* Get all the users whose task is delegated to current user */
	$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);	
	if(count($arrDelegatedTeamLeaderId) > 0 )
	{
		foreach($arrDelegatedTeamLeaderId as $delegatesIds)
		{
			$arrtemp = $objEmployee->fnGetAllEmployeesDetails($delegatesIds);
			$arrEmployees = $arrEmployees + $arrtemp;
		}
	}

	$tpl->set_var("FillTeamMembers","");
	if(count($arrEmployees) > 0)
	{
		foreach($arrEmployees as $curEmployee)
		{
			$tpl->set_var("teammember_id",$curEmployee["id"]);
			$tpl->set_var("teammember_name",$curEmployee["name"]);
			
			$tpl->parse("FillTeamMembers",true);
		}
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "AddEmergencyLeave")
	{
		$date = date('d-m-Y');

		/* Get employee info */
		$employeInfo = $objEmployee->fnGetEmployeeDetailById($_POST["employee_id"]);

		/* Fetch details for the user designation */
		$arrDesignationInfo = $objDesignation->fnGetDesignationById($employeInfo["designation"]);

		$teamleaderid = 0;
		$mangerid = 0;
		
		if(count($arrDesignationInfo) > 0)
		{
			/* Fetch reporting head hierarchy */
			$arrHeads = $objEmployee->fnGetReportHeadHierarchy($_POST["employee_id"]);
			
			if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && isset($arrDesignationInfo["second_reporting_head"]) && trim($arrDesignationInfo["second_reporting_head"]) != "" && trim($arrDesignationInfo["second_reporting_head"]) != "0")
			{
				if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
				{
					$teamleaderid = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
				}
				
				if(isset($arrHeads[$arrDesignationInfo["second_reporting_head"]]))
				{
					$mangerid = $arrHeads[$arrDesignationInfo["second_reporting_head"]]["id"];
				}
			}
			else if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && trim($arrDesignationInfo["second_reporting_head"]) == "0")
			{
				if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
				{
					$mangerid = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
				}
			}
		}

		$arrEmergencyLeave = array("nodays"=>"1", "start_date"=>$_POST['movement_date'], "end_date"=>$_POST['movement_date'], "reason"=>$_POST["reason"], "address"=>$_POST["address"], "contact"=>$_POST["contact"], "date"=>Date('Y-m-d H:i:s'), "employee_id" => $_POST["employee_id"], "isactive"=>0, "isemergency"=>"1", "emergencyaddedby"=>$_SESSION["id"], "teamleader_id"=>$teamleaderid, "manager_id"=>$mangerid, "tlapprovalcode"=>leaveform_uid(), "managerapprovalcode"=>leaveform_uid());

		$Subject = 'Emergency leave application';

		$checkDeligateId = $objLeave->fnCheckDeligate($teamleaderid);
		$checkDeligateManagerId = $objLeave->fnCheckDeligate($mangerid);
		
		$arrEmergencyLeave["deligateTeamLeaderId"] = 0;
		if(isset($checkDeligateId) && $checkDeligateId != '' && $checkDeligateId != '0')
		{
			$arrEmergencyLeave["deligateTeamLeaderId"] = $checkDeligateId;
			$arrEmergencyLeave["delegatedtlapprovalcode"] = leaveform_uid();
		}

		$arrEmergencyLeave["deligateManagerId"] = 0;
		if(isset($checkDeligateManagerId) && $checkDeligateManagerId != '' && $checkDeligateManagerId != '0')
		{
			$arrEmergencyLeave["deligateManagerId"] = $checkDeligateManagerId;
			$arrEmergencyLeave["delegatedmanagerapprovalcode"] = leaveform_uid();
		}
		
		/* If delegate is selected */
		if(isset($_POST["delegate"]) && trim($_POST["delegate"]) != "" && trim($_POST["delegate"]) != "0")
		{
			/* Add information for the delegated employee */
			$arrEmergencyLeave["delegate"] = $_POST["delegate"];
		}

		if($arrEmergencyLeave["teamleader_id"] == $_SESSION["id"])
		{
			/* Leave added by the first reporting head */
			$arrEmergencyLeave["status"] = "1";
			$arrEmergencyLeave["approved_date"] = Date('Y-m-d H:i:s');

			$objLeave->fnSaveEmergencyLeave($arrEmergencyLeave);

			/* Get second reporting head's data */
			$employeInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["employee_id"]);
			$TeamLeaderInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["teamleader_id"]);
			
			/* Main content */
			$main_content = $TeamLeaderInfo["name"]." has added an Emergency leave request for ".$employeInfo["name"]." for date ".$date.".";
			
			/* Send mail to user */
			$content = "Dear ".$employeInfo['name'].", <br /><br />".$TeamLeaderInfo["name"]." has added an Emergency leave request for you for date ".$date.".";
			$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
			sendmail($employeInfo['email'],$Subject,$content);
			
			/* Send mail to second reporting head */
			if($arrEmergencyLeave["manager_id"] != 0)
			{
				/* Get second reporting head's data */
				$ManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["manager_id"]);

				if(count($ManagerInfo) > 0)
				{
					$content = "Dear ".$ManagerInfo['name'].", <br /><br />".$main_content;
					
					if($arrEmergencyLeave["managerapprovalcode"] != "")
					{
						$content .= "<br/><br/>Please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["managerapprovalcode"]."_Approve_EL]'>Approve</a>  |  <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["managerapprovalcode"]."_Reject_EL]'>Reject</a> for letting us know your decision.";
					}
					
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($ManagerInfo['email'],$Subject,$content);
				}
			}

			if($arrEmergencyLeave["deligateTeamLeaderId"] != 0)
			{
				/* Get delegate team leaders data */
				$DelegateTeamLeaderInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["deligateTeamLeaderId"]);
				
				if(count($DelegateTeamLeaderInfo))
				{
					$content = "Dear ".$DelegateTeamLeaderInfo['name'].", <br /><br />".$main_content;

					if($arrEmergencyLeave["delegatedtlapprovalcode"] != "")
					{
						$content .= "<br/><br/>Please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["delegatedtlapprovalcode"]."_Approve_EL]'>Approve</a>  |  <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["delegatedtlapprovalcode"]."_Reject_EL]'>Reject</a> for letting us know your decision.";
					}

					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($DelegateTeamLeaderInfo['email'],$Subject,$content);
				}
			}
			
			if($arrEmergencyLeave["deligateManagerId"] != 0)
			{
				/* Get delegate manager data */
				$DelegateManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["deligateManagerId"]);

				if(count($DelegateManagerInfo))
				{
					$content = "Dear ".$DelegateManagerInfo['name'].", <br /><br />".$main_content;

					if($arrEmergencyLeave["delegatedmanagerapprovalcode"] != "")
					{
						$content .= "<br/><br/>Please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["delegatedmanagerapprovalcode"]."_Approve_EL]'>Approve</a>  |  <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["delegatedmanagerapprovalcode"]."_Reject_EL]'>Reject</a> for letting us know your decision.";
					}

					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($DelegateManagerInfo['email'],$Subject,$content);
				}
			}
		}
		else if($arrEmergencyLeave["manager_id"] == $_SESSION["id"])
		{
			/* Leave added by the first reporting head */
			$arrEmergencyLeave["status_manager"] = "1";
			$arrEmergencyLeave["approved_date_manager"] = Date('Y-m-d H:i:s');

			$objLeave->fnSaveEmergencyLeave($arrEmergencyLeave);

			/* Attendance data */
			$arrInfo["user_id"] = $employeInfo["id"];
			$arrInfo["date"] = $arrEmergencyLeave["start_date"];
			$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("UPL");

			$objAttendance->fnInsertRosterAttendance($arrInfo);
			
			/* Get second reporting head's data */
			$ManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["manager_id"]);
			
			/* Main content */
			$main_content = $ManagerInfo["name"]." has added an Emergency leave request for ".$employeInfo["name"]." for date ".$date.".";

			/* Send mail to user */
			$content = "Dear ".$employeInfo['name'].", <br /><br />".$ManagerInfo["name"]." has added an Emergency leave request for you for date ".$date.".";
			
			/* Send mail to the user to whom the responsibility is delegated */
			if(isset($_POST["delegate"]) && trim($_POST["delegate"]) != "" && trim($_POST["delegate"]) != "0")
			{
				$DelegatedUserInfo = $objEmployee->fnGetEmployeeDetailById($_POST["delegate"]);

				if(count($DelegatedUserInfo))
				{
					$content .= "<br/>Your responsibilities would be delegated to ".$DelegatedUserInfo['name']." while you are on leave/s, which please note.";
					
					$delegated_user_content = "Dear ".$DelegatedUserInfo['name'].", <br /><br />".$main_content." and his work responsibilities are delegated to you for while he is on leave/s for the above period.";

					$delegated_user_content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($DelegatedUserInfo['email'],$Subject,$delegated_user_content);
				}
			}
			
			$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
			sendmail($employeInfo['email'],$Subject,$content);
			
			if($arrEmergencyLeave["teamleader_id"] != 0)
			{
				/* Get second reporting head's data */
				$TeamLaderInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["teamleader_id"]);

				if(count($TeamLaderInfo) > 0)
				{
					$content = "Dear ".$TeamLaderInfo['name'].", <br /><br />".$main_content;
					
					if($arrEmergencyLeave["tlapprovalcode"] != "")
					{
						$content .= "<br/><br/>Please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["tlapprovalcode"]."_Approve_EL]'>Approve</a>  |  <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["tlapprovalcode"]."_Reject_EL]'>Reject</a> for letting us know your decision.";
					}
					
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($TeamLaderInfo['email'],$Subject,$content);
				}
			}
			
			if($arrEmergencyLeave["deligateTeamLeaderId"] != 0)
			{
				/* Get delegate team leaders data */
				$DelegateTeamLeaderInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["deligateTeamLeaderId"]);
				
				if(count($DelegateTeamLeaderInfo))
				{
					$content = "Dear ".$DelegateTeamLeaderInfo['name'].", <br /><br />".$main_content;
					
					if($arrEmergencyLeave["delegatedtlapprovalcode"] != "")
					{
						$content .= "<br/><br/>Please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["delegatedtlapprovalcode"]."_Approve_EL]'>Approve</a>  |  <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["delegatedtlapprovalcode"]."_Reject_EL]'>Reject</a> for letting us know your decision.";
					}
					
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($DelegateTeamLeaderInfo['email'],$Subject,$content);
				}
			}
			
			if($arrEmergencyLeave["deligateManagerId"] != 0)
			{
				/* Get delegate manager data */
				$DelegateManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["deligateManagerId"]);
				
				if(count($DelegateManagerInfo))
				{
					$content = "Dear ".$DelegateManagerInfo['name'].", <br /><br />".$main_content;
					
					if($arrEmergencyLeave["delegatedmanagerapprovalcode"] != "")
					{
						$content .= "<br/><br/>Please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["delegatedmanagerapprovalcode"]."_Approve_EL]'>Approve</a>  |  <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["delegatedmanagerapprovalcode"]."_Reject_EL]'>Reject</a> for letting us know your decision.";
					}
					
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($DelegateManagerInfo['email'],$Subject,$content);
				}
			}
		}
		else if($arrEmergencyLeave["deligateManagerId"] == $_SESSION["id"])
		{
			/* Leave added by the first reporting head */
			$arrEmergencyLeave["manager_delegate_status"] = "1";
			$arrEmergencyLeave["manager_delegate_date"] = Date('Y-m-d H:i:s');

			$objLeave->fnSaveEmergencyLeave($arrEmergencyLeave);

			/* Attendance data */
			$arrInfo["user_id"] = $employeInfo["id"];
			$arrInfo["date"] = $arrEmergencyLeave["start_date"];
			$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("UPL");

			$objAttendance->fnInsertRosterAttendance($arrInfo);
			
			/* Get second reporting head's data */
			$DelegateManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["deligateManagerId"]);
			
			/* Main content */
			$main_content = $DelegateManagerInfo["name"]." has added an Emergency leave request for ".$employeInfo["name"]." for date ".$date.".";
			
			/* Send mail to user */
			$content = "Dear ".$employeInfo['name'].", <br /><br />".$DelegateManagerInfo["name"]." has added an Emergency leave request for you for date ".$date.".";
			
			/* Send mail to the user to whom the responsibility is delegated */
			if(isset($_POST["delegate"]) && trim($_POST["delegate"]) != "" && trim($_POST["delegate"]) != "0")
			{
				$DelegatedUserInfo = $objEmployee->fnGetEmployeeDetailById($_POST["delegate"]);

				if(count($DelegatedUserInfo))
				{
					$content .= "<br/>Your responsibilities would be delegated to ".$DelegatedUserInfo['name']." while you are on leave/s, which please note.";

					$delegated_user_content = "Dear ".$DelegatedUserInfo['name'].", <br /><br />".$main_content." and his work responsibilities are delegated to you for while he is on leave/s for the above period.";

					$delegated_user_content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($DelegatedUserInfo['email'],$Subject,$delegated_user_content);
				}
			}
			
			$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
			sendmail($employeInfo['email'],$Subject,$content);
						
			if($arrEmergencyLeave["manager_id"] != 0)
			{
				/* Get second reporting head's data */
				$ManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["manager_id"]);

				if(count($ManagerInfo) > 0)
				{
					$content = "Dear ".$ManagerInfo['name'].", <br /><br />".$main_content;
					
					if($arrEmergencyLeave["managerapprovalcode"] != "")
					{
						$content .= "<br/><br/>Please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["managerapprovalcode"]."_Approve_EL]'>Approve</a>  |  <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["managerapprovalcode"]."_Reject_EL]'>Reject</a> for letting us know your decision.";
					}
					
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($ManagerInfo['email'],$Subject,$content);
				}
			}
			
			if($arrEmergencyLeave["teamleader_id"] != 0)
			{
				/* Get second reporting head's data */
				$TeamLaderInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["teamleader_id"]);

				if(count($TeamLaderInfo) > 0)
				{
					$content = "Dear ".$TeamLaderInfo['name'].", <br /><br />".$main_content;
					
					if($arrEmergencyLeave["tlapprovalcode"] != "")
					{
						$content .= "<br/><br/>Please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["tlapprovalcode"]."_Approve_EL]'>Approve</a>  |  <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["tlapprovalcode"]."_Reject_EL]'>Reject</a> for letting us know your decision.";
					}
					
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($TeamLaderInfo['email'],$Subject,$content);
				}
			}
			
			if($arrEmergencyLeave["deligateTeamLeaderId"] != 0)
			{
				/* Get delegate team leaders data */
				$DelegateTeamLeaderInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["deligateTeamLeaderId"]);
				
				if(count($DelegateTeamLeaderInfo))
				{
					$content = "Dear ".$DelegateTeamLeaderInfo['name'].", <br /><br />".$main_content;
					
					if($arrEmergencyLeave["delegatedtlapprovalcode"] != "")
					{
						$content .= "<br/><br/>Please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["delegatedtlapprovalcode"]."_Approve_EL]'>Approve</a>  |  <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["delegatedtlapprovalcode"]."_Reject_EL]'>Reject</a> for letting us know your decision.";
					}
					
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($DelegateTeamLeaderInfo['email'],$Subject,$content);
				}
			}
		}
		else if($arrEmergencyLeave["deligateTeamLeaderId"] == $_SESSION["id"])
		{
			/* Leave added by the first reporting head */
			$arrEmergencyLeave["delegate_status"] = "1";
			$arrEmergencyLeave["delegate_date"] = Date('Y-m-d H:i:s');

			$objLeave->fnSaveEmergencyLeave($arrEmergencyLeave);
			
			/* Get second reporting head's data */
			$DelegateTeamLeaderInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["deligateTeamLeaderId"]);
			
			/* Main content */
			$main_content = $DelegateTeamLeaderInfo["name"]." has added an Emergency leave request for ".$employeInfo["name"]." for date ".$date.".";

			/* Send mail to user */
			$content = "Dear ".$employeInfo['name'].", <br /><br />".$DelegateTeamLeaderInfo["name"]." has added an Emergency leave request for you for date ".$date.".";
			$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
			sendmail($employeInfo['email'],$Subject,$content);

			if($arrEmergencyLeave["manager_id"] != 0)
			{
				/* Get second reporting head's data */
				$ManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["manager_id"]);

				if(count($ManagerInfo) > 0)
				{
					$content = "Dear ".$ManagerInfo['name'].", <br /><br />".$main_content;

					if($arrEmergencyLeave["managerapprovalcode"] != "")
					{
						$content .= "<br/><br/>Please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["managerapprovalcode"]."_Approve_EL]'>Approve</a>  |  <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["managerapprovalcode"]."_Reject_EL]'>Reject</a> for letting us know your decision.";
					}

					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($ManagerInfo['email'],$Subject,$content);
				}
			}
			
			if($arrEmergencyLeave["teamleader_id"] != 0)
			{
				/* Get second reporting head's data */
				$TeamLaderInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["teamleader_id"]);

				if(count($TeamLaderInfo) > 0)
				{
					$content = "Dear ".$TeamLaderInfo['name'].", <br /><br />".$main_content;
					
					if($arrEmergencyLeave["tlapprovalcode"] != "")
					{
						$content .= "<br/><br/>Please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["tlapprovalcode"]."_Approve_EL]'>Approve</a>  |  <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["tlapprovalcode"]."_Reject_EL]'>Reject</a> for letting us know your decision.";
					}
					
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($TeamLaderInfo['email'],$Subject,$content);
				}
			}

			if($arrEmergencyLeave["deligateManagerId"] != 0)
			{
				/* Get delegate manager data */
				$DelegateManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["deligateManagerId"]);
				
				if(count($DelegateManagerInfo))
				{
					$content = "Dear ".$DelegateManagerInfo['name'].", <br /><br />".$main_content;
					
					if($arrEmergencyLeave["delegatedmanagerapprovalcode"] != "")
					{
						$content .= "<br/><br/>Please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["delegatedmanagerapprovalcode"]."_Approve_EL]'>Approve</a>  |  <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["delegatedmanagerapprovalcode"]."_Reject_EL]'>Reject</a> for letting us know your decision.";
					}
					
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($DelegateManagerInfo['email'],$Subject,$content);
				}
			}
		}
		else
		{
			$objLeave->fnSaveEmergencyLeave($arrEmergencyLeave);
			
			/* Get second reporting head's data */
			$LogedInUser = $objEmployee->fnGetEmployeeDetailById($_SESSION["id"]);
			
			/* Main content */
			$main_content = $LogedInUser["name"]." has added an Emergency leave request for ".$employeInfo["name"]." for date ".$date.".";
			
			/* Send mail to user */
			$content = "Dear ".$employeInfo['name'].", <br /><br />".$LogedInUser["name"]." has added an Emergency leave request for you for date ".$date.".";
			$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
			sendmail($employeInfo['email'],$Subject,$content);
			
			if($arrEmergencyLeave["manager_id"] != 0)
			{
				/* Get second reporting head's data */
				$ManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["manager_id"]);

				if(count($ManagerInfo) > 0)
				{
					$content = "Dear ".$ManagerInfo['name'].", <br /><br />".$main_content;
					
					if($arrEmergencyLeave["managerapprovalcode"] != "")
					{
						$content .= "<br/><br/>Please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["managerapprovalcode"]."_Approve_EL]'>Approve</a>  |  <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["managerapprovalcode"]."_Reject_EL]'>Reject</a> for letting us know your decision.";
					}
					
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($ManagerInfo['email'],$Subject,$content);
				}
			}
			
			if($arrEmergencyLeave["teamleader_id"] != 0)
			{
				/* Get second reporting head's data */
				$TeamLaderInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["teamleader_id"]);

				if(count($TeamLaderInfo) > 0)
				{
					$content = "Dear ".$TeamLaderInfo['name'].", <br /><br />".$main_content;
					
					if($arrEmergencyLeave["tlapprovalcode"] != "")
					{
						$content .= "<br/><br/>Please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["tlapprovalcode"]."_Approve_EL]'>Approve</a>  |  <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["tlapprovalcode"]."_Reject_EL]'>Reject</a> for letting us know your decision.";
					}
					
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($TeamLaderInfo['email'],$Subject,$content);
				}
			}

			if($arrEmergencyLeave["deligateTeamLeaderId"] != 0)
			{
				/* Get delegate team leaders data */
				$DelegateTeamLeaderInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["deligateTeamLeaderId"]);
				
				if(count($DelegateTeamLeaderInfo))
				{
					$content = "Dear ".$DelegateTeamLeaderInfo['name'].", <br /><br />".$main_content;
					
					if($arrEmergencyLeave["delegatedtlapprovalcode"] != "")
					{
						$content .= "<br/><br/>Please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["delegatedtlapprovalcode"]."_Approve_EL]'>Approve</a>  |  <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["delegatedtlapprovalcode"]."_Reject_EL]'>Reject</a> for letting us know your decision.";
					}
					
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($DelegateTeamLeaderInfo['email'],$Subject,$content);
				}
			}

			if($arrEmergencyLeave["deligateManagerId"] != 0)
			{
				/* Get delegate manager data */
				$DelegateManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrEmergencyLeave["deligateManagerId"]);
				
				if(count($DelegateManagerInfo))
				{
					$content = "Dear ".$DelegateManagerInfo['name'].", <br /><br />".$main_content;
					
					if($arrEmergencyLeave["delegatedmanagerapprovalcode"] != "")
					{
						$content .= "<br/><br/>Please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["delegatedmanagerapprovalcode"]."_Approve_EL]'>Approve</a>  |  <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrEmergencyLeave["delegatedmanagerapprovalcode"]."_Reject_EL]'>Reject</a> for letting us know your decision.";
					}
					
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					sendmail($DelegateManagerInfo['email'],$Subject,$content);
				}
			}
		}

		header("Location: emergency_leave_list.php?info=succ");
		exit;
	}

	$tpl->pparse('main',false);
?>

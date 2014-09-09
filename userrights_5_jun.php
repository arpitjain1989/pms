<?php

	/* Check if the user is logged in if user is not logged in redirect to the login page */
	if(!isset($_SESSION["id"]) || trim($_SESSION["id"]) == "" || $_SESSION["id"] == '0')
	{
		header("Location: index.php");
		exit;
	}

	include_once("includes/class.login.php");
	include_once("includes/class.designation.php");

	$objLogin = new clsLogin();
	$objDesignation = new designations();

	/* Check for last activity */
	if(!$objLogin->fnCheckLastAccess($_SESSION["usertype"], $_SESSION["id"]))
	{
		/* If idle more then defined time logout the user */
		$objLogin->fnLogout();

		/* Clear user session */
		unset($_SESSION);
		session_destroy();

		/* Redirect to login */
		header("Location: index.php");
		exit;
	}

	$tpl->set_var("AdminProfileModule","");
	$tpl->set_var("EmployeeProfileModule","");

	$tpl->set_var("DisplayOneCalendar","");
	$tpl->set_var("DisplayTwoCalendar","");

	$tpl->set_var("DisplayOneAttendanceView","");
	$tpl->set_var("DisplayTwoAttendanceView","");
	$tpl->set_var("DisplayAdminAttendanceView","");
	$tpl->set_var("ShiftMovementReportModule","");

	$tpl->set_var("DisplayHRBlock","");
	$tpl->set_var("DisplayHRNewBlock","");
	$tpl->set_var("DisplayHRNewestBlock","");

	$tpl->set_var("CandidatesModule","");
	$tpl->set_var("DisplayHRReports","");
	$tpl->set_var("OmRoundModule","");

	$tpl->set_var("BreakExceedApprovedReportModule","");

	if(isset($_SESSION["usertype"]) && trim($_SESSION["usertype"]) == "employee")
	{
		$tpl->parse("EmployeeProfileModule",false);

		include_once("includes/class.modules.php");

		$objModule = new clsModule();

		$arrModules = $objModule->fnGetAllModules();

		$key = array_search("LeaveHistory", $arrModules);
		if($key)
		{
			unset($arrModules[$key]);
		}

		//echo "<pre>";
		//print_r($arrModules);

		foreach($arrModules as $moduleName)
		{
			$tpl->set_var($moduleName."Module","");
		}

		/*if($_SESSION["id"] == "1" || $_SESSION["id"] == "2")
			$_SESSION["userrights"][] = "BreakExceedApprovedReport";*/

		if(isset($_SESSION["userrights"]) && count($_SESSION["userrights"]) > 0)
		{
			foreach($_SESSION["userrights"] as $currentModule)
			{
				if($currentModule != "LeaveHistory" && $currentModule != "ITDashboard" && $currentModule != "PenaltyDashboard" && $currentModule != "PendingRequestDashboard" && $currentModule != "LevelingDashboard")
				{
					if($objDesignation->fnCheckIfParentDesignation($_SESSION["designation"]))
						$tpl->parse("DisplayTwoCalendar",false);
					else
						$tpl->parse("DisplayOneCalendar",false);
							
					/*if($_SESSION['designation'] == "5" || $_SESSION['designation'] == "9" || $_SESSION['designation'] == "10" || $_SESSION['designation'] == "11" || $_SESSION['designation'] == "12" || $_SESSION['designation'] == "14" || $_SESSION['designation'] == "15" || $_SESSION['designation'] == "16" || $_SESSION['designation'] == "20" || $_SESSION['designation'] == "21" || $_SESSION['designation'] == "22" || $_SESSION['designation'] == "23" || $_SESSION['designation'] == "24" || $_SESSION['designation'] == "26" || $_SESSION['designation'] == "27" || $_SESSION['designation'] == "28" || $_SESSION['designation'] == '30' || $_SESSION['designation'] == '31' || $_SESSION['designation'] == '32' || $_SESSION['designation'] == '33' || $_SESSION['designation'] == '34' || $_SESSION['designation'] == '35' || $_SESSION['designation'] == '36' || $_SESSION['designation'] == '37' || $_SESSION['designation'] == '38' || $_SESSION['designation'] == '39' || $_SESSION['designation'] == '40' || $_SESSION['designation'] == '41' || $_SESSION['designation'] == '42' || $_SESSION['designation'] == '43' || $_SESSION['designation'] == '44' || $_SESSION['designation'] == '46')
					{
						$tpl->parse("DisplayOneCalendar",false);
					}
					else
					{
						$tpl->parse("DisplayTwoCalendar",false);
					}*/
					
					$tpl->parse($currentModule."Module",false);
				}
			}
		}

		/*$tpl->set_var("RosterModule","");
		$tpl->set_var("ManagerRosterModule","");
		if($_SESSION["designation"] == "6")
		{
			$tpl->parse("ManagerRosterModule",false);
		}
		else if($_SESSION["designation"] == "7" || $_SESSION["designation"] == "13" || $_SESSION["designation"] == "47")
		{
			$tpl->parse("RosterModule",false);
		}*/

		$tpl->set_var("AttendanceViewModule","");
		/* Conditioning for daily attendance report */
		if($objDesignation->fnCheckIfParentDesignation($_SESSION["designation"]))
		{
			$tpl->parse("DisplayTwoAttendanceView",false);
			$tpl->parse("AttendanceViewModule",false);
		}
		else
		{
			$tpl->parse("DisplayOneAttendanceView",false);
			$tpl->parse("AttendanceViewModule",false);
		}

		$tpl->set_var("ReportsBlock","");
		if(in_array("AttendanceReport",$_SESSION["userrights"]) || in_array("LeaveReport",$_SESSION["userrights"]) || in_array("BreakExceedReport",$_SESSION["userrights"]) || in_array('LateComingReport',$_SESSION["userrights"]) || in_array('BreakExceedApprovedReport',$_SESSION["userrights"]) || in_array('ShiftMovementReport',$_SESSION["userrights"]) || in_array('AllEmployeesAttendanceReport',$_SESSION["userrights"]) || in_array('ReportAttrition',$_SESSION["userrights"]) || in_array('HeadCountReportForReportingHeads',$_SESSION["userrights"]))
			$tpl->parse("ReportsBlock",false);

		$tpl->set_var("InventoryBlock","");
		if(in_array("InventoryType",$_SESSION["userrights"]) || in_array("InventoryLocation",$_SESSION["userrights"]) || in_array("StockRegister",$_SESSION["userrights"]))
			$tpl->parse("InventoryBlock",false);

		$tpl->set_var("InventoryReportBlock","");
		if(in_array("InventoryRepairReport",$_SESSION["userrights"]) || in_array("InventoryPurchaseReport",$_SESSION["userrights"]) || in_array("CurrentInventoryExport",$_SESSION["userrights"]))
			$tpl->parse("InventoryReportBlock",false);

		$arrItTicketArray = array("IssueCategory", "ITIssues", "IssueAccess", "PendingTicket", "Ticket", "ITSupportDesignation", "Requisition", "RequisitionRequest", "ITRequisition", "ITExpiredRequisition", "ScrapDisposal", "ITSupportRoster");

		$tpl->set_var("ITTicketingBlock","");
		$tpl->set_var("ITTicketingOneBlock","");
		if(count(array_intersect($_SESSION["userrights"], $arrItTicketArray)) > 1)
		{
			$tpl->parse("ITTicketingBlock",false);
		}
		else if(count(array_intersect($_SESSION["userrights"], $arrItTicketArray)) == 1)
		{
			$tpl->parse("ITTicketingOneBlock",false);
		}

		$arrLeaveMod = array("LeaveForm","HalfLeaveForm","EmergencyLeaveForm","LeaveRequest","HalfLeaveRequest","AdminLeaveForm","AdminHalfLeaveForm","AdminLeaveRequest","AdminHalfLeaveRequest");
		$tpl->set_var("ManageLeavesBlock","");
		if(count(array_intersect($_SESSION["userrights"], $arrLeaveMod)) > 0)
			$tpl->parse("ManageLeavesBlock",false);

		$arrItTicketReportArray = array("TicketCallHistory", "UserWiseIssueTrackingReport", "IssueCategoryWiseIssueTrackingReport", "LocationWiseIssueTrackingReport", "CallStatusWiseMonthlyReport", "DateWiseTicketGraph", "IssueCategoryWiseTicketGraph", "TicketResolutionReport", "TicketAttendingReport");

		$tpl->set_var("ITTicketingReportBlock","");
		if(count(array_intersect($_SESSION["userrights"], $arrItTicketReportArray)) > 0)
			$tpl->parse("ITTicketingReportBlock",false);

		if(in_array("RctSource",$_SESSION["userrights"]) || in_array("RctDivision",$_SESSION["userrights"]) || in_array("EmployeeTest",$_SESSION["userrights"]) || in_array("CurrentOpenings",$_SESSION["userrights"])  || in_array("DocumentDetails",$_SESSION["userrights"]) || in_array("Proof",$_SESSION["userrights"]) || in_array("Graduation",$_SESSION["userrights"]) || in_array("PostGraduation",$_SESSION["userrights"]) || in_array("SalaryOffered",$_SESSION["userrights"]))
			$tpl->parse("DisplayHRBlock",false);

		if(in_array("HrRound",$_SESSION["userrights"]) || in_array("IQRound",$_SESSION["userrights"]) || in_array("TestRound",$_SESSION["userrights"]) || in_array("FinalHr",$_SESSION["userrights"]) || in_array("OmRoundView",$_SESSION["userrights"]))
			$tpl->parse("DisplayHRNewBlock",false);

		if(in_array("Candidates",$_SESSION["userrights"]) || in_array("DocumentDetails",$_SESSION["userrights"]) || in_array("CandidateDocumentDetails",$_SESSION["userrights"]) || in_array("Joinees",$_SESSION["userrights"]))
			$tpl->parse("DisplayHRNewestBlock",false);

		if(in_array("RctSheet",$_SESSION["userrights"]) || in_array("MonthlyRctSheet",$_SESSION["userrights"]) || in_array("DocumentReport",$_SESSION["userrights"]) || in_array("PendingDocumentReport",$_SESSION["userrights"]) || in_array("MasterReport",$_SESSION["userrights"]) || in_array("BirthdayCalendar",$_SESSION["userrights"]) || in_array("InductionReport",$_SESSION["userrights"]) || in_array("MonthlyEmployeeStatusReport",$_SESSION["userrights"]) || in_array("DesignationWiseEmployeeSummary",$_SESSION["userrights"]) || in_array("DailyHeadCountReport",$_SESSION["userrights"]) || in_array("EmployeesNotIncludedInHeadCountList",$_SESSION["userrights"]) || in_array("DepartmentWiseHeadCountReport",$_SESSION["userrights"]))
			$tpl->parse("DisplayHRReports",false);

		if($PageIdentifier != "Dashboard" && $PageIdentifier != "Employee Profile" && $PageIdentifier != "LeaveHistory" && !in_array($PageIdentifier, $_SESSION["userrights"]))
		{
			header("Location: dashboard.php");
			exit;
		}

		$tpl->set_var("DisplayShiftMovementBlock","");
		if(in_array('ShiftMovement',$_SESSION["userrights"]) || in_array('ShiftMovementRequest',$_SESSION["userrights"]) || in_array('EmergencyShiftMovement',$_SESSION["userrights"]) || in_array('LateCommingCompensationRequest',$_SESSION["userrights"]))
		{
			$tpl->parse("DisplayShiftMovementBlock",false);
		}
	}
	else if(isset($_SESSION["usertype"]) && trim($_SESSION["usertype"]) == "admin")
	{
		/*$tpl->parse("AdminProfileModule",false);
		$tpl->set_var("RosterModule","");
		$tpl->set_var("ShiftMovementCompensationModule","");
		$tpl->set_var("ShiftMovementCompensationRequestModule","");
		$tpl->parse("ManagerRosterModule",false);

		$tpl->parse("DisplayOneCalendar",false);
		$tpl->parse("DisplayAdminAttendanceView",false);
		$tpl->parse("ShiftMovementReportModule",false);

		if($PageIdentifier == "Employee Profile")
		{
			header("Location: dashboard.php");
			exit;
		}*/
		
		$tpl->set_var("ManageLeavesBlock","");
		$tpl->set_var("HrReportAttritionModule","");
		$tpl->set_var("AllEmployeesAttendanceReportModule","");
		$tpl->set_var("EmployeeListModule","");
		$tpl->set_var("DailyHeadCountReportModule","");
		$tpl->set_var("HrReportAttritionModule","");
		$tpl->set_var("EmployeesNotIncludedInHeadCountListModule","");
		$tpl->set_var("DepartmentWiseHeadCountReportModule","");
		$tpl->set_var("DesignationWiseEmployeeSummaryModule","");
		$tpl->set_var("ReportingHeadHistoryModule","");
	
		if(isset($_SESSION["admin_type"]) && trim($_SESSION["admin_type"]) == "itadmin")
		{
			$ITAdmin = array("InventoryMake", "InventoryAttributes", "InventoryType", "InventoryLocation", "InventoryVendor", "StockRegister", "InventoryRepairReport", "InventoryPurchaseReport", "StockSummaryReport", "LocationWiseInventoryReport", "IssueCategory", "ITIssues", "IssueAccess","Dashboard","Profile","ScrapDisposalReport","TicketCallHistory","RequisitionInventory","ITRequisition","UserWiseIssueTrackingReport","IssueCategoryWiseIssueTrackingReport","LocationWiseIssueTrackingReport","DateWiseTicketGraph","IssueCategoryWiseTicketGraph","CallStatusWiseMonthlyReport","CurrentInventoryExport","ITSupportDesignation","ITSupportRoster","TicketResolutionReport","TicketAttendingReport");

			/* Hide all modules */
			include_once("includes/class.modules.php");
			$objModule = new clsModule();
			$arrModules = $objModule->fnGetAllModules();

			foreach($arrModules as $moduleName)
			{
				$tpl->set_var($moduleName."Module","");
			}

			/* Hide fixed dropdowns */
			$tpl->set_var("DisplayShiftMovementBlock","");
			/*$tpl->set_var("ManagerRosterModule","");*/
			$tpl->set_var("ReportsBlock","");
			$tpl->set_var("AdminShiftMovementModule","");

			/* Show admin profile */
			$tpl->parse("AdminProfileModule",false);

			/* Show IT related modules */
			$tpl->parse("InventoryMakeModule",false);
			$tpl->parse("InventoryAttributesModule",false);
			$tpl->parse("InventoryTypeModule",false);
			$tpl->parse("InventoryLocationModule",false);
			$tpl->parse("InventoryVendorModule",false);
			$tpl->parse("StockRegisterModule",false);
			$tpl->parse("RequisitionInventoryModule",false);

			$tpl->parse("InventoryRepairReportModule",false);
			$tpl->parse("InventoryPurchaseReportModule",false);
			$tpl->parse("StockSummaryReportModule",false);
			$tpl->parse("LocationWiseInventoryReportModule",false);
			$tpl->parse("ScrapDisposalReportModule",false);
			$tpl->parse("CurrentInventoryExportModule",false);

			$tpl->parse("IssueCategoryModule",false);
			$tpl->parse("ITIssuesModule",false);
			$tpl->parse("IssueAccessModule",false);
			$tpl->parse("TicketCallHistoryModule",false);
			$tpl->parse("ITRequisitionModule",false);
			$tpl->parse("UserWiseIssueTrackingReportModule",false);
			$tpl->parse("IssueCategoryWiseIssueTrackingReportModule",false);
			$tpl->parse("LocationWiseIssueTrackingReportModule",false);
			$tpl->parse("DateWiseTicketGraphModule",false);
			$tpl->parse("IssueCategoryWiseTicketGraphModule",false);
			$tpl->parse("CallStatusWiseMonthlyReportModule",false);
			$tpl->set_var("ITExpiredRequisitionModule","");
			//$tpl->set_var("ITSupportTimingsModule","");
			$tpl->set_var("RequisitionModule","");
			$tpl->set_var("RequisitionRequestModule","");
			$tpl->parse("ITSupportDesignationModule",false);
			
			$tpl->parse("ITSupportRosterModule",false);
			$tpl->parse("TicketResolutionReportModule",false);
			$tpl->parse("TicketAttendingReportModule",false);
			$tpl->set_var("ITExpiredRequisitionModule","");
			$tpl->set_var("ITTicketingOneBlock","");

			/* Disable quality modules */
			$tpl->set_var("QualityLevelingFormModule","");
			$tpl->set_var("QualityFormTypeModule","");
			$tpl->set_var("QualityParameterModule","");
			$tpl->set_var("QualityAFDModule","");
			$tpl->set_var("QualityLevelingReportModule","");
			$tpl->set_var("LevelingDataEditModule","");
			$tpl->set_var("QualitySettingsModule","");

			if($PageIdentifier == "Employee Profile" || !in_array($PageIdentifier, $ITAdmin))
			{
				header("Location: dashboard.php");
				exit;
			}
		}
		else if($_SESSION["admin_type"] == 'hradmin')
		{
			$HRAdmin = array("RctSource", "RctDivision", "EmployeeTest", "HrRound", "IQRound", "CurrentOpenings", "TestRound", "OmRound", "FinalHr", "Candidates", "DocumentDetails", "RctSheet","MonthlyRctSheet", "DocumentReport", "PendingDocumentReport","Dashboard","Profile","MasterReport","BirthdayCalendar","InductionReport","Proof","Graduation","PostGraduation","CandidateDocumentDetails","Joinees","CurrentEmployee","IncrementDetails","Employee","EmployeeTestMarks","SalaryOffered","Attrition","InterviewSettings","OmRoundView","MonthlyEmployeeStatusReport","HrReportAttrition","DesignationWiseEmployeeSummary","DailyHeadCountReport","EmployeesNotIncludedInHeadCountList","DepartmentWiseHeadCountReport");

			/* Hide inventory modules */
			$tpl->set_var("InventoryBlock","");
			$tpl->set_var("TicketCallHistoryModule","");
			$tpl->set_var("ITTicketingBlock","");
			$tpl->set_var("TicketModule","");
			$tpl->set_var("RequisitionInventory","");
			$tpl->set_var("UserWiseIssueTrackingReportModule","");
			$tpl->set_var("IssueCategoryWiseIssueTrackingReportModule","");
			$tpl->set_var("LocationWiseIssueTrackingReportModule","");
			$tpl->set_var("DateWiseTicketGraphModule","");
			$tpl->set_var("IssueCategoryWiseTicketGraphModule","");
			$tpl->set_var("CallStatusWiseMonthlyReportModule","");
			/*$tpl->set_var("TicketCallHistoryModule","");
			$tpl->set_var("TicketModule","");
			$tpl->set_var("RequisitionInventory","");*/

			$tpl->set_var("InventoryRepairReportModule","");
			$tpl->set_var("InventoryPurchaseReportModule","");
			$tpl->set_var("StockSummaryReportModule","");
			$tpl->set_var("LocationWiseInventoryReportModule","");
			$tpl->set_var("CurrentInventoryExportModule","");
			$tpl->set_var("ScrapDisposalReportModule","");
			$tpl->set_var("InventoryReportBlock","");
			//$tpl->set_var("ITSupportTimingsModule","");
			$tpl->set_var("ITSupportDesignationModule","");
			$tpl->parse("DisplayHRBlock",false);
			$tpl->parse("DisplayHRNewBlock",false);
			$tpl->parse("DisplayHRNewestBlock",false);
			$tpl->set_var("ITExpiredRequisitionModule","");
			$tpl->parse("HrReportAttritionModule",false);
			$tpl->parse("DesignationWiseEmployeeSummaryModule",false);
			$tpl->parse("DailyHeadCountReportModule",false);
			$tpl->parse("EmployeesNotIncludedInHeadCountListModule",false);
			$tpl->parse("DepartmentWiseHeadCountReportModule",false);
			$tpl->parse("DisplayHRReports",false);

			$tpl->set_var("ITRequisitionModule","");
			$tpl->set_var("RequisitionModule","");
			$tpl->set_var("RequisitionRequestModule","");
			$tpl->set_var("TicketResolutionReportModule","");
			$tpl->set_var("TicketAttendingReportModule","");
			$tpl->set_var("ITSupportRosterModule","");
			/* Hide all modules */
			include_once("includes/class.modules.php");
			$tpl->set_var("ITTicketingOneBlock","");
			$tpl->set_var("ITTicketingReportBlock","");

			$objModule = new clsModule();
			$arrModules = $objModule->fnGetAllModules();

			foreach($arrModules as $moduleName)
			{
				$tpl->set_var($moduleName."Module","");
			}

			$tpl->parse("EmployeeModule",false);
			$tpl->parse("AttritionModule",false);

			/* Hide fixed dropdowns */
			$tpl->set_var("DisplayShiftMovementBlock","");
			/*$tpl->set_var("ManagerRosterModule","");*/
			$tpl->set_var("ReportsBlock","");
			/*$tpl->set_var("Roster","");*/
			$tpl->set_var("AdminShiftMovementModule","");

			$tpl->parse("CandidatesModule",false);

			/* Show admin profile */
			$tpl->parse("AdminProfileModule",false);
			$tpl->parse("InterviewSettingsModule",false);
			
			/* Disable quality modules */
			$tpl->set_var("QualityLevelingFormModule","");
			$tpl->set_var("QualityFormTypeModule","");
			$tpl->set_var("QualityParameterModule","");
			$tpl->set_var("QualityAFDModule","");
			$tpl->set_var("QualityLevelingReportModule","");
			$tpl->set_var("LevelingDataEditModule","");
			$tpl->set_var("QualitySettingsModule","");

			if($PageIdentifier == "Employee Profile" || !in_array($PageIdentifier, $HRAdmin))
			{
				header("Location: dashboard.php");
				exit;
			}
		}
		else
		{
			$adm = array("InventoryMake", "InventoryAttributes", "InventoryType", "InventoryLocation", "InventoryVendor", "StockRegister", "InventoryRepairReport", "InventoryPurchaseReport", "StockSummaryReport", "LocationWiseInventoryReport", "IssueCategory", "ITIssues","IssueAccess","TicketCallHistory","RequisitionInventory","ITRequisition","BreakExceedApprovedReport","UserWiseIssueTrackingReport","IssueCategoryWiseIssueTrackingReport","LocationWiseIssueTrackingReport","DateWiseTicketGraph","IssueCategoryWiseTicketGraph","CallStatusWiseMonthlyReport","CurrentInventoryExport","ITExpiredRequisition","Requisition","RequisitionRequest","ITSupportRoster","Employee","Roster","LeaveForm","HalfLeaveForm","EmergencyLeaveForm","LeaveRequest","HalfLeaveRequest","ShiftMovement","EmergencyShiftMovement","ShiftMovementRequest","LateCommingCompensation","LateCommingCompensationRequest","ShiftMovementCompensation","ShiftMovementCompensationRequest","Roles","Designation","ManagerRosterModule","EmployeeList");

			$tpl->parse("AdminProfileModule",false);
			$tpl->set_var("RolesModule","");
			$tpl->set_var("DesignationModule","");
			$tpl->set_var("RosterModule","");
			$tpl->set_var("ManagerRosterModule","");
			$tpl->set_var("EmployeeModule","");
			$tpl->set_var("LeaveFormModule","");
			$tpl->set_var("HalfLeaveFormModule","");
			$tpl->set_var("EmergencyLeaveFormModule","");
			$tpl->set_var("LeaveRequestModule","");
			$tpl->set_var("HalfLeaveRequestModule","");
			$tpl->set_var("ShiftMovementModule","");
			$tpl->set_var("EmergencyShiftMovementModule","");
			$tpl->set_var("ShiftMovementRequestModule","");
			$tpl->set_var("LateCommingCompensationModule","");
			$tpl->set_var("LateCommingCompensationRequestModule","");
			$tpl->set_var("ShiftMovementCompensationModule","");
			$tpl->set_var("ShiftMovementCompensationRequestModule","");
			//$tpl->set_var("AdminLeaveFormModule","");
			//$tpl->set_var("AdminHalfLeaveFormModule","");
			//$tpl->set_var("AdminApprovedLWPFormModule","");
			//$tpl->parse("ManagerRosterModule",false);
			$tpl->parse("AdminShiftMovementModule",false);
			$tpl->parse("ManageLeavesBlock",false);

			$tpl->parse("DisplayOneCalendar",false);
			$tpl->parse("DisplayAdminAttendanceView",false);
			$tpl->parse("ShiftMovementReportModule",false);
			$tpl->parse("ScrapDisposalModule",false);

			/* Hide inventory modules */
			$tpl->set_var("InventoryBlock","");

			$tpl->set_var("TicketCallHistoryModule","");
			$tpl->set_var("ITTicketingBlock","");
			$tpl->set_var("TicketModule","");
			$tpl->set_var("RequisitionInventory","");
			$tpl->set_var("UserWiseIssueTrackingReportModule","");
			$tpl->set_var("IssueCategoryWiseIssueTrackingReportModule","");
			$tpl->set_var("LocationWiseIssueTrackingReportModule","");
			$tpl->set_var("DateWiseTicketGraphModule","");
			$tpl->set_var("IssueCategoryWiseTicketGraphModule","");
			$tpl->set_var("CallStatusWiseMonthlyReportModule","");
			/*$tpl->set_var("TicketCallHistoryModule","");
			$tpl->set_var("TicketModule","");
			$tpl->set_var("RequisitionInventory","");*/
			$tpl->set_var("ITExpiredRequisitionModule","");
			$tpl->set_var("InventoryRepairReportModule","");
			$tpl->set_var("InventoryPurchaseReportModule","");
			$tpl->set_var("StockSummaryReportModule","");
			$tpl->set_var("LocationWiseInventoryReportModule","");
			$tpl->set_var("CurrentInventoryExportModule","");
			$tpl->parse("ScrapDisposalReportModule",false);
			$tpl->parse("InventoryReportBlock",false);
			$tpl->set_var("ITSupportDesignationModule","");
			//$tpl->set_var("ITSupportTimingsModule","");
			$tpl->set_var("ITRequisitionModule","");
			$tpl->set_var("RequisitionModule","");
			$tpl->set_var("RequisitionRequestModule","");
			$tpl->set_var("TicketResolutionReportModule","");
			$tpl->set_var("TicketAttendingReportModule","");
			$tpl->set_var("ITSupportRosterModule","");
			$tpl->set_var("ITTicketingOneBlock","");
			$tpl->set_var("ITTicketingReportBlock","");
			$tpl->set_var("RctSheetModule","");
			$tpl->set_var("MonthlyRctSheetModule","");
			$tpl->set_var("DocumentReportModule","");
			$tpl->set_var("PendingDocumentReportModule","");
			$tpl->set_var("BirthdayCalendarModule","");
			$tpl->set_var("InductionReportModule","");
			$tpl->parse("DisplayHRReports",false);
			$tpl->set_var("InterviewSettingsModule","");
			
			/* Disable quality modules */
			$tpl->set_var("QualityLevelingFormModule","");
			$tpl->set_var("QualityFormTypeModule","");
			$tpl->set_var("QualityParameterModule","");
			$tpl->set_var("QualityAFDModule","");
			$tpl->set_var("QualityLevelingReportModule","");
			$tpl->set_var("LevelingDataEditModule","");
			$tpl->set_var("QualitySettingsModule","");
			
			if($PageIdentifier == "Employee Profile" || in_array($PageIdentifier, $adm))
			{
				header("Location: dashboard.php");
				exit;
			}
		}
	}

?>

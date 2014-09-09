<?php

	include('common.php');
	
	$tpl = new Template($app_path);
	
	$tpl->load_file('template.html','main');
	$tpl->load_file('dashboard.html','main_container');
	
	$PageIdentifier = "Dashboard";
	include_once('userrights.php');
	
	$tpl->set_var("mainheading","Dashboard");
	$breadcrumb = '<li class="active">Dashboard</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.admin.php');
	include_once('includes/class.designation.php');
	include_once('includes/class.attendance.php');
	include_once('includes/class.leave.php');
	include_once('includes/class.shift_movement.php');
	include_once('includes/class.requisition.php');
	include_once('includes/class.candidate_list.php');
	include_once('includes/class.quality_form.php');
	
	$objAdmin = new admin();
	$objDesignation = new designations();
	$objAttendance = new attendance();
	$objLeave = new leave();
	$objShiftMovement = new shift_movement();
	$objRequisition = new requisition();
	$objCandidateList = new candidate_list();
	$objQualityForm = new quality_form();
	
	$tpl->set_var("displayname",$_SESSION["displayname"]);

	/* Display To Do Block */
	$tpl->set_var("DisplayToDoListBlock","");
	if((isset($_SESSION["userrights"]) && in_array("ITDashboard",$_SESSION["userrights"])) || (isset($_SESSION["admin_type"]) && trim($_SESSION["admin_type"]) == "itadmin"))
	{
		include_once("includes/class.requisition.php");
		include_once("includes/class.ticket.php");
		
		$objRequisition = new requisition();
		$objTicket = new ticket();
		
		/* Fetch pending requisition counts */
		$pending_requisition_count = $objRequisition->fnCountPendingRequisition();
		$tpl->set_var("pending_requisition_count", $pending_requisition_count);
		
		/* Fetch all open tickets - not having status 'In Queue' and 'Resolved' */
		$open_tickets_count = $objTicket->fnCountOpenTickets();
		$tpl->set_var("open_tickets_count", $open_tickets_count);
		
		/* Fetch all pending tickets - having status 'In Queue' (0 => In Queue) */
		$pending_tickets_count = $objTicket->fnCountTicketsByStatus(0);
		$tpl->set_var("pending_tickets_count", $pending_tickets_count);

		/* Fetch all expired requisition count */
		$expired_requisition_count = $objRequisition->fnCountExpiredRequisition();
		$tpl->set_var("expired_requisition_count", $expired_requisition_count);

		$tpl->parse("DisplayToDoListBlock",false);
	}

	$tpl->set_var("DisplayPenaltyBlock","");
	$tpl->set_var("DisplayPenaltyBreakExceedBlock","");
	$tpl->set_var("DisplayPenaltyLateComingBlock","");
	
	if(isset($_SESSION["userrights"]) && in_array("PenaltyDashboard",$_SESSION["userrights"]))
	{
		$showPenalty = false;
		/* Display break exceed and late coming for login user */
		$arrDesignation = $objDesignation->fnGetDesignationById($_SESSION["designation"]);

		/* Fetch designation information for the user */
		if(isset($arrDesignation["consider_break_exceed"]) && trim($arrDesignation["consider_break_exceed"]) =='0')
		{
			/* If breaks considered */
			$break_exceed_days = $objAttendance->fnGetTotalPenaltyBreakExceedsByUser($_SESSION["id"]);
			$tpl->set_var("penalty_break_exceed_count", $break_exceed_days);
			$tpl->parse("DisplayPenaltyBreakExceedBlock",false);
			$showPenalty = true;
		}

		if(isset($arrDesignation["consider_late_commings"]) && trim($arrDesignation["consider_late_commings"]) =='0')
		{
			/* If late coming considered */
			$late_coming_days = $objAttendance->fnGetTotalPenaltyLateComingByUser($_SESSION["id"]);
			$tpl->set_var("penalty_late_coming_count", $late_coming_days);
			$tpl->parse("DisplayPenaltyLateComingBlock",false);
			$showPenalty = true;
		}
		
		if($showPenalty)
		{
			$tpl->parse("DisplayPenaltyBlock",false);
		}
	}
	
	/* To display pending information for pending requests */
	$tpl->set_var("DisplayPendingApprovalReminderBlock","");
	$tpl->set_var("DisplayPendingLeaveRequestBlock","");
	$tpl->set_var("DisplayPendingHalfdayLeaveRequestBlock","");
	$tpl->set_var("DisplayPendingShiftMovementRequestBlock","");
	$tpl->set_var("DisplayPendingShiftMovementCompensationRequestBlock","");
	$tpl->set_var("DisplayPendingLateComingCompensationRequestBlock","");
	$tpl->set_var("DisplayPendingRequisitionRequestBlock","");
	$tpl->set_var("DisplayPendingInterviewRequestBlock","");
	$tpl->set_var("DisplayPendingAdminLeaveRequestBlock","");
	$tpl->set_var("DisplayPendingAdminHalfLeaveRequestBlock","");
	$tpl->set_var("DisplayPendingAdminApprovedLwpRequestBlock","");
	
	if(isset($_SESSION["userrights"]) && in_array("PendingRequestDashboard",$_SESSION["userrights"]))
	{
		$displayPendingApprovalBlock = false;

		/* Check if, user has rights for leave request approval */
		if(isset($_SESSION["userrights"]) && in_array("LeaveRequest",$_SESSION["userrights"]))
		{
			/* Check for any leave request for the loged in user are pending for approval */
			$pending_leave_request_count = $objLeave->fnCheckPendingLeaveRequestByUserId($_SESSION["id"]);
			$tpl->set_var("pending_leave_request_count",$pending_leave_request_count);
			
			$tpl->parse("DisplayPendingLeaveRequestBlock",false);
			$displayPendingApprovalBlock = true;
		}

		/* Check if, user has rights for halfday leave approval */
		if(isset($_SESSION["userrights"]) && in_array("HalfLeaveRequest",$_SESSION["userrights"]))
		{
			$pending_halfday_leave_request_count = $objLeave->fnCheckPendingHalfdayLeaveRequestByUserId($_SESSION["id"]);
			$tpl->set_var("pending_halfday_leave_request_count",$pending_halfday_leave_request_count);

			$tpl->parse("DisplayPendingHalfdayLeaveRequestBlock",false);
			$displayPendingApprovalBlock = true;
		}

		/* Check if, user has rights for shift movement approval */
		if(isset($_SESSION["userrights"]) && in_array("ShiftMovementRequest",$_SESSION["userrights"]))
		{
			$pending_shift_movement_request_count = $objShiftMovement->fnCheckPendingShiftMovementRequestByUserId($_SESSION["id"]);
			$tpl->set_var("pending_shift_movement_request_count",$pending_shift_movement_request_count);

			$tpl->parse("DisplayPendingShiftMovementRequestBlock",false);
			$displayPendingApprovalBlock = true;
		}

		/* Check if, user has rights for shift movement compensation approval */
		if(isset($_SESSION["userrights"]) && in_array("ShiftMovementCompensationRequest",$_SESSION["userrights"]))
		{
			$pending_shift_movement_compensation_request_count = $objShiftMovement->fnCheckPenaltyShiftMovementRequestCountByUserId($_SESSION["id"]);
			$tpl->set_var("pending_shift_movement_compensation_request_count",$pending_shift_movement_compensation_request_count);

			$tpl->parse("DisplayPendingShiftMovementCompensationRequestBlock",false);
			$displayPendingApprovalBlock = true;
		}

		/* Check if, user has rights for late coming approval */
		if(isset($_SESSION["userrights"]) && in_array("LateCommingCompensationRequest",$_SESSION["userrights"]))
		{
			$pending_late_coming_compensation_request_count = $objAttendance->fnGetTotalPenaltyLateComingRequestByUser($_SESSION["id"]);
			$tpl->set_var("pending_late_coming_compensation_request_count",$pending_late_coming_compensation_request_count);

			$tpl->parse("DisplayPendingLateComingCompensationRequestBlock",false);
			$displayPendingApprovalBlock = true;
		}

		/* Check if, user has rights for requision approval */
		if(isset($_SESSION["userrights"]) && in_array("RequisitionRequest",$_SESSION["userrights"]))
		{
			$pending_requisition_request_count = $objRequisition->fnGetTotalPenaltyRequisitionRequestByUser($_SESSION["id"]);
			$tpl->set_var("pending_requisition_request_count",$pending_requisition_request_count);

			$tpl->parse("DisplayPendingRequisitionRequestBlock",false);
			$displayPendingApprovalBlock = true;
		}
		
		/* Check if, user has rights for om rount */
		if(isset($_SESSION["userrights"]) && in_array("OmRound",$_SESSION["userrights"]))
		{
			/* Fetch all interview count for manageral level */
			$pendInterviewCount = $objCandidateList->fnGetPendingInterviewCount();
			$tpl->set_var("Pending_interview_request_count", $pendInterviewCount);
			
			$tpl->parse("DisplayPendingInterviewRequestBlock",false);
			$displayPendingApprovalBlock = true;
		}

		/* Check if, admin leave added request pening */
		if(isset($_SESSION["userrights"]) && in_array("AdminLeaveRequest",$_SESSION["userrights"]))
		{
			/* Fetch all pending leave counts */
			$pending_admin_leave_count = $objLeave->fnCountAdminLeaveRequests();
			$tpl->set_var("pending_admin_leave_count", $pending_admin_leave_count);

			$tpl->parse("DisplayPendingAdminLeaveRequestBlock",false);
			$displayPendingApprovalBlock = true;
		}
		
		/* Check if, admin half leave added request pening */
		if(isset($_SESSION["userrights"]) && in_array("AdminHalfLeaveRequest",$_SESSION["userrights"]))
		{
			/* Fetch all pending leave counts */
			$pending_admin_half_leave_count = $objLeave->fnCountAdminHalfLeaveRequests();
			$tpl->set_var("pending_admin_half_leave_count", $pending_admin_half_leave_count);

			$tpl->parse("DisplayPendingAdminHalfLeaveRequestBlock",false);
			$displayPendingApprovalBlock = true;
		}
		
		/* Check if, admin approved lwp request pening */
		if(isset($_SESSION["userrights"]) && in_array("AdminApprovedLWPRequest",$_SESSION["userrights"]))
		{
			/* Fetch all pending leave counts */
			$pending_admin_approved_lwp_count = $objLeave->fnCountAdminApprovedLwpRequests();
			$tpl->set_var("pending_admin_approved_lwp_count", $pending_admin_approved_lwp_count);

			$tpl->parse("DisplayPendingAdminApprovedLwpRequestBlock",false);
			$displayPendingApprovalBlock = true;
		}

		if($displayPendingApprovalBlock)
		{
			/* If any of the inner blocks are parsed, then parse the main block */
			$tpl->parse("DisplayPendingApprovalReminderBlock",false);
		}
	}
	
	/* Check if, leveling taken */
	$tpl->set_var("DisplayLevelingDashboardBlock","");
	$tpl->set_var("DisplayLevelingPerformedBlock","");
	if(isset($_SESSION["userrights"]) && in_array("LevelingDashboard",$_SESSION["userrights"]))
	{
		$curDate = Date("Y-m-d");
		if($objQualityForm->fnCheckIfLevelingPerformedByDate($curDate))
		{
			$tpl->set_var("leveling_date", $curDate);
			$tpl->parse("DisplayLevelingPerformedBlock",false);
			$tpl->parse("DisplayLevelingDashboardBlock",false);
		}
	}
	
	$tpl->pparse('main',false);
?>

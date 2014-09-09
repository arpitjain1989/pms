<?php

	include('common.php');

	if(isset($_SESSION["usertype"]) && trim($_SESSION["usertype"]) != "" && isset($_SESSION["id"]) && trim($_SESSION["id"]) != "")
	{
		include_once("includes/class.login.php");
		
		$objLogin = new clsLogin();
		/* Check for last activity */
		if(!$objLogin->fnCheckLastAccess($_SESSION["usertype"], $_SESSION["id"]))
		{
			/* If idle more then defined time logout the user */
			$objLogin->fnLogout();

			/* Clear user session */
			unset($_SESSION);
			session_destroy();
		}
	}

	include_once("includes/class.requisition.php");
	include_once("includes/class.ticket.php");

	$objRequisition = new requisition();
	$objTicket = new ticket();

	/* Fetch pending requisition counts */
	$pending_requisition_count = $objRequisition->fnCountPendingRequisition();

	/* Fetch all open tickets - not having status 'In Queue' and 'Resolved' */
	$open_tickets_count = $objTicket->fnCountOpenTickets();

	/* Fetch all pending tickets - having status 'In Queue' (0 => In Queue) */
	$pending_tickets_count = $objTicket->fnCountTicketsByStatus(0);

	/* Fetch all expired requisition count */
	$expired_requisition_count = $objRequisition->fnCountExpiredRequisition();

	echo json_encode(array("pending_requisition_count"=>$pending_requisition_count, "open_tickets_count"=>$open_tickets_count, "pending_tickets_count"=>$pending_tickets_count, "expired_requisition_count"=>$expired_requisition_count));

?>

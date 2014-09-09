<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('ticket_list.html','main_container');

	/* Rights management */
	$PageIdentifier = "Ticket";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Manage Ticket");
	
	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage Ticket</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.ticket.php');

	/* Display message block */
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Ticket added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Ticket already added. Cannot add again.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$objTicket = new ticket();
	$arrTickets = $objTicket->fnGetTicketsByUserId($_SESSION["id"]);

	/* Display list */
	$tpl->set_var("FillTicketsList","");
	if(count($arrTickets) > 0)
	{
		foreach($arrTickets as $curTicket)
		{
			$tpl->SetAllValues($curTicket);
			$tpl->parse("FillTicketsList",true);
		}
	}

	$tpl->pparse('main',false);

?>

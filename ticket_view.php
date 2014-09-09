<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('ticket_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "Ticket";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","View Ticket");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="ticket_list.php">Manage Ticket</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Ticket</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.ticket.php');

	$objTicket = new ticket();

	$tpl->set_var("DisplayTicketInformationBlock","");
	$tpl->set_var("DisplayNoTicketInformationBlock","");
	
	$tpl->set_var("DisplayAdditionalRequirementBlock","");
	$tpl->set_var("DisplayAdditionalTimeBlock","");
	$tpl->set_var("DisplayAdditionalAssetBlock","");

	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$arrTicket = $objTicket->fnGetTicketsByUserAndTicketId($_SESSION['id'], $_REQUEST['id']);
		
		if(count($arrTicket) > 0)
		{
			$tpl->SetAllValues($arrTicket);
			if($arrTicket["resolution_status"] == 3)
			{
				if($arrTicket["requirement_for"] == 1)
					$tpl->parse("DisplayAdditionalTimeBlock",false);
				else if($arrTicket["requirement_for"] == 2)
					$tpl->parse("DisplayAdditionalAssetBlock",false);
				
				$tpl->parse("DisplayAdditionalRequirementBlock",false);
			}
			
			$tpl->parse("DisplayTicketInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoTicketInformationBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoTicketInformationBlock",false);
	}
	
	$tpl->pparse('main',false);
?>

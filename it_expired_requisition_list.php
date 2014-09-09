<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('it_expired_requisition_list.html','main_container');

	/* Rights management */
	$PageIdentifier = "ITExpiredRequisition";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Manage Expired Requistion");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage Expired Requistion</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.requisition.php');

	/* Display message block */
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Expired requisition closed";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Expired requisition already closed. Cannot close again";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$objRequisition = new requisition();
	$arrExpiredRequisition = $objRequisition->fnGetExpiredRequisition();

	/* Display list */
	$tpl->set_var("FillExpiredRequisitionList","");
	if(count($arrExpiredRequisition) >0)
	{
		foreach($arrExpiredRequisition as $curExpiredRequisition)
		{
			$tpl->SetAllValues($curExpiredRequisition);
			$tpl->parse("FillExpiredRequisitionList",true);
		}
	}

	$tpl->pparse('main',false);

?>

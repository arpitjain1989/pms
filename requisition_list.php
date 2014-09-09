<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('requisition_list.html','main_container');

	/* Rights management */
	$PageIdentifier = "Requisition";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Manage Requistion");
	
	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage Requistion</li>';
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
				$message = "Requisition added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Requisition already added. Cannot add again.";
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
	$arrRequisition = $objRequisition->fnGetRequisitionByEmployee($_SESSION["id"]);

	/* Display list */
	$tpl->set_var("FillRequisitionList","");
	if(count($arrRequisition) >0)
	{
		$arrStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");
		
		foreach($arrRequisition as $curRequisition)
		{
			$requisition_status = $arrStatus[$curRequisition["approval_status"]];
			if($curRequisition["approval_status"] == 0 && $curRequisition["delegated_reporting_head_status"] != "")
				$requisition_status = $arrStatus[$curRequisition["delegated_reporting_head_status"]];
			if(isset($curRequisition["is_auto_approved"]) && $curRequisition["is_auto_approved"] == "1")
			{
				$requisition_status = "Auto Approved";
			}
			
			$tpl->set_var("requisition_status",$requisition_status);
			$tpl->SetAllValues($curRequisition);
			$tpl->parse("FillRequisitionList",true);
		}
	}

	$tpl->pparse('main',false);

?>

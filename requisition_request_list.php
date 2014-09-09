<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('requisition_request_list.html','main_container');

	/* Rights management */
	$PageIdentifier = "RequisitionRequest";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Manage Requistion Requests");
	
	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage Requistion Requests</li>';
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
				$message = "Requisition updated successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Requisition status already updated. Cannot update again.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "SearchRequisitionRequest")
	{
		if(isset($_POST["chkViewAll"]))
			$_SESSION["RequisitionRequest"]["viewall"] = 1;
		else
			$_SESSION["RequisitionRequest"]["viewall"] = 0;
			
		header("Location: requisition_request_list.php");
		exit;
	}

	if(!isset($_SESSION["RequisitionRequest"]["viewall"]))
		$viewAll = 0;
	else
		$viewAll = $_SESSION["RequisitionRequest"]["viewall"];

	
	$checkedStr = ($viewAll == 1) ? "checked='checked'" : "";
	$tpl->set_var("checkedStr", $checkedStr);

	$objRequisition = new requisition();
	$arrRequisitionRequest = $objRequisition->fnGetRequisitionRequest($_SESSION["id"], $viewAll);

	/* Display list */
	$tpl->set_var("FillRequisitionRequestList","");
	if(count($arrRequisitionRequest) >0)
	{
		$arrStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");
		
		foreach($arrRequisitionRequest as $curRequisitionRequest)
		{
			$tpl->SetAllValues($curRequisitionRequest);
			
			$approval_status_text = $arrStatus[$curRequisitionRequest["approval_status"]];
			if($curRequisitionRequest["approval_status"] == 0 && $curRequisitionRequest["delegated_reporting_head_id"] != 0)
				$approval_status_text = $arrStatus[$curRequisitionRequest["delegated_reporting_head_status"]];
			$tpl->set_var("approval_status_text", $approval_status_text);

			$tpl->parse("FillRequisitionRequestList",true);
		}
	}

	$tpl->pparse('main',false);

?>

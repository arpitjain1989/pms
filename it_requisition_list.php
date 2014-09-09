<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('it_requisition_list.html','main_container');

	/* Rights management */
	$PageIdentifier = "ITRequisition";
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
				$message = "Requisition closed";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Requisition already closed.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "SearchITRequisition")
	{
		if(isset($_POST["chkViewAll"]))
			$_SESSION["ITRequisition"]["viewall"] = 1;
		else
			$_SESSION["ITRequisition"]["viewall"] = 0;
			
		header("Location: it_requisition_list.php");
		exit;
	}

	if(!isset($_SESSION["ITRequisition"]["viewall"]))
		$viewAll = 0;
	else
		$viewAll = $_SESSION["ITRequisition"]["viewall"];

	
	$checkedStr = ($viewAll == 1) ? "checked='checked'" : "";
	$tpl->set_var("checkedStr", $checkedStr);

	$objRequisition = new requisition();
	$arrRequisition = $objRequisition->fnGetApprovedRequisition($viewAll);

	/* Display list */
	$tpl->set_var("FillRequisitionList","");
	if(count($arrRequisition) >0)
	{
		foreach($arrRequisition as $curRequisition)
		{
			$tpl->SetAllValues($curRequisition);
			$tpl->parse("FillRequisitionList",true);
		}
	}

	$tpl->pparse('main',false);

?>

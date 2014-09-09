<?php 

	include('common.php');
	$tpl = new Template($app_path);
	$tpl->load_file('template.html','main');
	$tpl->load_file('shift_movement_compensation_requests.html','main_container');

	$PageIdentifier = "ShiftMovementCompensationRequest";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Compensation Request");
	$breadcrumb = '<li class="active">Manage Compensation Request</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	$message = "";
	$messageClass = "";
	
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$status = "";
				if($_REQUEST["s"] == "1")
					$status = "Approved";
				else if($_REQUEST["s"] == "2")
					$status = "Unapproved";
				$messageClass = "alert-success";
				$message = "Shift movement compensation request ".$status." successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Error approving / unapproving shift movement compensation request";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}
	
	include_once('includes/class.shift_movement.php');
	
	$objShiftMovement = new shift_movement();
	
	$arrCompensation = $objShiftMovement->fnGetAllShiftMovementCompensationRequest();
	
	$tpl->set_var("FillShiftMovementsCompensation","");
	if(count($arrCompensation))
	{
		foreach($arrCompensation as $CompensationInfo)
		{
			
			$final_status = "Pending";
			if($CompensationInfo["approvedby_tl"] == "1")
			{
				/* approved by manager */
				$final_status = "Approved";
			}
			else if($CompensationInfo["approvedby_tl"] == "2")
			{
				/* rejected by manager */
				$final_status = "Rejected";
			}
			else if($CompensationInfo["approvedby_tl"] == "0")
			{
				/* Kept pending by tl, check for the status of delegate tl */
				if($CompensationInfo["delegatedtl_id"] != "" && $CompensationInfo["delegatedtl_id"] != "0")
				{
					if($CompensationInfo["delegatedtl_status"] == "1")
					{
						/* if approved by delegate manager */
						$final_status = "Approved";
					}
					else if($CompensationInfo["delegatedtl_status"] == "2")
					{
						/* if rejected by delegate manager */
						$final_status = "Rejected";
					}
				}
			}
			
			$tpl->set_var("final_status", $final_status);
			
			$tpl->SetAllValues($CompensationInfo);
			$tpl->parse("FillShiftMovementsCompensation",true);
		}
	}

	$tpl->pparse('main',false);
?>

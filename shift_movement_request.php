<?php
	include('common.php');
	
	$tpl = new Template($app_path);
	
	$tpl->load_file('template.html','main');
	$tpl->load_file('shift_movement_request.html','main_container');
	
	$PageIdentifier = "ShiftMovementRequest";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Shift Movements Request");
	$breadcrumb = '<li class="active">Manage Shift Movements Request</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.employee.php');
	include_once('includes/class.shift_movement.php');
	
	$objEmployee = new employee();
	$objShiftMovement = new shift_movement();
	
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				
				$status = "";
				switch($_REQUEST["s"])
				{
					case '1':
						$status = "approved";
						break;
					case '2':
						$status = "unapproved";
						break;
				}
				
				$message = "Shift movement $status successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Do not have sufficient rights to approve / unapprove shift movement.";
				break;
			case 'errtime':
				$messageClass = "alert-error";
				$message = "Cannot approve / unapprove shift movement after the shift movement date and time.";
				break;
			case 'errdeactive':
				$messageClass = "alert-error";
				$message = "Cannot approve / unapprove shift movement, time for approval has passed.";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			$tpl->parse("DisplayMessageBlock",false);
		}
	}
	
	if($_SESSION['usertype'] == 'admin')
	{
		$arrEmployee = $objEmployee->fnGetAllemployees(0);
	}
	else
	{
		$arrEmployee = $objEmployee->fnGetAllemployees($_SESSION['id']);
	}
	
	
	
	/* Fetch employees who are delegated */
	$arrDelegatedEmployee = array();
	$arrtemp = array();
	
	/* Get Delegated Manager id */
	$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($_SESSION['id']);
	
	if(count($arrDelegatedManagerId) > 0 )
	{
		foreach($arrDelegatedManagerId as $delegatesManagerIds)
		{
			$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
			$arrDelegatedEmployee = $arrDelegatedEmployee + $arrtemp ;
		}
	}
	
	/* Get delegated teamleader id */
	$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);	
	
	if(count($arrDelegatedTeamLeaderId) > 0 )
	{
		foreach($arrDelegatedTeamLeaderId as $delegatesIds)
		{
			$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
			$arrDelegatedEmployee = $arrDelegatedEmployee + $arrtemp ;
		}
	}
	
	/*if($_SESSION["designation"] == "6" || $_SESSION["designation"] == "18" || $_SESSION["designation"] == "19")
	{
		// Get Delegated Manager id
		$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($_SESSION['id']);
		
		if(count($arrDelegatedManagerId) > 0 )
		{
			foreach($arrDelegatedManagerId as $delegatesManagerIds)
			{
				$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
				$arrDelegatedEmployee = $arrDelegatedEmployee + $arrtemp ;
			}
		}
	}
	else if ($_SESSION["designation"] == "7" || $_SESSION["designation"] == "13")
	{
		// Get delegated teamleader id 
		$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);	
		
		if(count($arrDelegatedTeamLeaderId) > 0 )
		{
			foreach($arrDelegatedTeamLeaderId as $delegatesIds)
			{
				$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
				$arrDelegatedEmployee = $arrDelegatedEmployee + $arrtemp ;
			}
		}
	}*/
	/* Merge current employees with delegated employees */
	$arrEmployee = $arrEmployee + $arrDelegatedEmployee;
	
	$arrEmployee[] = "";
	
	if(count($arrEmployee) > 0)
	{
		$arrEmployee = array_filter($arrEmployee,'strlen');
	}	
	
	$ids = "0";
	if(count($arrEmployee) > 0)
	{
		$ids = implode(',',$arrEmployee);
	}

	$arrMovements = $objShiftMovement->fnGetAllShiftMovementRequest($ids);

	//echo '<pre>'; print_r($arrMovements);
	
	$tpl->set_var("FillShiftMovements","");
	if(count($arrMovements) > 0)
	{
		foreach($arrMovements as $MovementInfo)
		{
			if($MovementInfo['isCancel'] == '1')
			{
				$trclass = "red";
				$final_status = "Cancelled";
				$tpl->set_var("trclass",$trclass);
			}
			else if($MovementInfo['isCancel'] == '0')
			{
				$tpl->set_var("trclass","");
				
				$final_status = "Pending";
				if($MovementInfo["approvedby_manager"] == "1")
				{
					/* approved by manager */
					$final_status = "Approved";
				}
				else if($MovementInfo["approvedby_manager"] == "2")
				{
					/* rejected by manager */
					$final_status = "Rejected";
				}
				else if($MovementInfo["approvedby_manager"] == "0")
				{
					/* Kept pending by manager, check for the status of delegate manager */
					if($MovementInfo["delegatedmanager_id"] != "" && $MovementInfo["delegatedmanager_id"] != "0")
					{
						if($MovementInfo["delegatedmanager_status"] == "1")
						{
							/* if approved by delegate manager */
							$final_status = "Approved";
						}
						else if($MovementInfo["delegatedmanager_status"] == "2")
						{
							/* if rejected by delegate manager */
							$final_status = "Rejected";
						}
					}
				}
			}
		
			$tpl->set_var("final_status", $final_status);
			
			$tpl->setAllValues($MovementInfo);
			$tpl->parse("FillShiftMovements",true);
		}
	}

	$tpl->pparse('main',false);
?>

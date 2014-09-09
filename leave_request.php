<?php
	include('common.php');
	
	$tpl = new Template($app_path);
	//print_r($_SESSION);
	$tpl->load_file('template.html','main');
	$tpl->load_file('leave_request.html','main_container');
	
	$PageIdentifier = "LeaveRequest";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Leave Request");
	$breadcrumb = '<li class="active">Manage Leave Request</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');
	
	$objLeave = new leave();
	$objEmployee = new employee();

	$message = "";
	$messageClass = "";
	
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST['info']))
	{
		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Leave inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "Leave Request updated successfully.";
				break;
			case 'upderr':
				$messageClass = "alert-error";
				$message = "Leave Request already updated. Cannot update again.";
				break;
			case 'norec':
				$messageClass = "alert-success";
				$message = "No records found.";
				break;
			case 'timepast':
				$messageClass = "alert-error";
				$message = "Cannot approved, time for approval has past.";
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
		//echo $_SESSION['id'];
		/* Get delegated teamleader id */
		$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);

		/* Get Delegated Manager id */
		$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($_SESSION['id']);
		
		/*echo 'hello';

		print_r($arrDelegatedManagerId);

		echo 'hello1';*/
		$arrDelegatedEmployee = array();
		$arrtemp = array();
		if(count($arrDelegatedTeamLeaderId) > 0 )
		{
			foreach($arrDelegatedTeamLeaderId as $delegatesIds)
			{
				//echo $delegatesIds;
				$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
				$arrDelegatedEmployee =$arrDelegatedEmployee + $arrtemp ;
			}
		}
		if(count($arrDelegatedManagerId) > 0 )
		{
			foreach($arrDelegatedManagerId as $delegatesManagerIds)
			{
				//echo $delegatesIds;
				$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
				$arrDelegatedEmployee =$arrDelegatedEmployee + $arrtemp ;
			}
		}
		//print_r($arrDelegatedEmployee);
		$temp1 = $objEmployee->fnGetAllemployees($_SESSION['id']);
		$arrEmployee = $temp1 + $arrDelegatedEmployee;
		//$arrEmployee = $objEmployee->fnGetAllemployees($_SESSION['id']);
	}
	//print_r($arrEmployee);
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
	
	$arrLeaveRequest = $objLeave->fnGetAllLeaveRequest($ids);
	//echo '<pre>';print_r($arrLeaveRequest);
	/*$tpl->set_var("FillLeaveRequestValues","");
	$tpl->set_var("FillTeamLeaderName","");
	if($_SESSION['designation'] == 6 || $_SESSION['designation'] == 0 || $_SESSION['designation'] == 17)
	{
		$tpl->parse("FillTeamLeaderName",false);
	}*/
	$tpl->set_var("FillLeaveRequestValues","");
	foreach($arrLeaveRequest as $LeaveRequest)
	{
		$final_status = "Pending";
		if($LeaveRequest["status_manager"] == "1")
		{
			/* approved by manager */
			$final_status = "Approved";
		}
		else if($LeaveRequest["status_manager"] == "2")
		{
			/* rejected by manager */
			$final_status = "Rejected";
		}
		else if($LeaveRequest["status_manager"] == "0")
		{
			/* Kept pending by manager, check for the status of delegate manager */
			if($LeaveRequest["deligateManagerId"] != "" && $LeaveRequest["deligateManagerId"] != "0")
			{
				if($LeaveRequest["manager_delegate_status"] == "1")
				{
					/* if approved by delegate manager */
					$final_status = "Approved";
				}
				else if($LeaveRequest["manager_delegate_status"] == "2")
				{
					/* if rejected by delegate manager */
					$final_status = "Rejected";
				}
			}
		}
		
		$tpl->set_var("final_status", $final_status);
		
		$tpl->setAllValues($LeaveRequest);
		if($LeaveRequest['headname'] == '' )
		{
			$tpl->set_var("headname","Admin");
		}
		$tpl->parse("FillLeaveRequestValues",true);
		
	}
	
	$tpl->pparse('main',false);
?>

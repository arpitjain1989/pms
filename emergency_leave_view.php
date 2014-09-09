<?php
	include('common.php');

	$tpl = new Template($app_path);
	//print_r($_SESSION);
	$tpl->load_file('template.html','main');
	$tpl->load_file('emergency_leave_view.html','main_container');

	$PageIdentifier = "EmergencyLeaveForm";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Emergency Leave");
	$breadcrumb = '<li><a href="leave_form.php">Manage Emergency Leave</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Emergency Leave</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');

	$objLeave = new leave();
	$objEmployee = new employee();

	if(isset($_POST["action"]) && trim($_POST["action"]) == "EmergencyLeaveSave")
	{
		//echo '<pre>'; print_r($_POST);
		if($objLeave->fnUpdateLeaveForm($_POST))
		{
			header("Location: emergency_leave_list.php?info=statsucc");
			exit;
		}
		else
		{
			header("Location: emergency_leave_list.php?info=staterr");
			exit;
		}
	}

	$arrLeaves = $objLeave->fnGetLeaveDetailsById($_REQUEST['id']);
	$arrLeaveForm = $objLeave->fnGetLeaveFormById($_REQUEST['id']);
	$tpl->set_var("eid",$arrLeaves['employee_id']);
	$tpl->set_var("tid",$arrLeaves['teamleader_id']);
	$tpl->set_var("mid",$arrLeaves['manager_id']);

	/* Get All Team Leader for Team Leader delegation dropdown */
	//echo '<pre>'; print_r($arrLeaves);
	//$getAllTeamLeaders = $objEmployee->fnGetAllTeamLeaders($arrLeaves['id'],$arrLeaves['designation'],$_SESSION['designation']);

	/* Get All managers for Manager delegation dropdown */
	/*$getAllManagers = $objEmployee->fnGetAllManagers($arrLeaves['id'],$arrLeaves['designation'],$_SESSION['designation']);*/

	$reporting_head = $objEmployee->fnGetReportingHeadById($_SESSION['id']);
	if($arrLeaves['delegate'] != '' && $arrLeaves['delegate'] != '0')
	{
		$delegated_TL_name_details = $objEmployee->fnGetEmployeeById($arrLeaves['delegate']);
		$delegated_teamLeader_name = $delegated_TL_name_details['name'];
		$tpl->set_var("delegated_tl",$delegated_teamLeader_name);
	}
	/*if($arrLeaves['manager_delegate'] != '' && $arrLeaves['manager_delegate'] != '0')
	{
		$delegated_Manager_detail = $objEmployee->fnGetEmployeeById($arrLeaves['manager_delegate']);
		$delegated_manager_name = $delegated_Manager_detail['name'];
		$tpl->set_var("delegated_manager",$delegated_manager_name);
	}*/

	if(isset($reporting_head))
	{
		$tpl->set_var("reportinghead",$reporting_head);
	}

	foreach($arrLeaveForm as $arrLeaveFormvalue)
	{
		$tpl->SetAllValues($arrLeaveFormvalue);
		$newStartDate = date("d-m-Y", strtotime($arrLeaveFormvalue['start_date']));
		$newsEndDate = date("d-m-Y", strtotime($arrLeaveFormvalue['end_date']));
		$tpl->set_var('startdate',$newStartDate);
		$tpl->set_var('enddate',$newsEndDate);
	}

	$tpl->set_var("DisplayManagerEntryBlock","");
	$tpl->set_var("DisplayManagerBlock","");
	$tpl->set_var("DisplayTLEntryBlock","");
	$tpl->set_var("DisplayTLBlock","");
	$tpl->set_var("DisplaySaveButton","");
	$tpl->set_var("DisplayDelegateTLBlock","");
	$tpl->set_var("DisplayDelegatedManagerEntryBlock","");
	$tpl->set_var("DisplayDelegateManagerBlock","");
	$tpl->set_var("DisplayDelegateTLEntryBlock","");
	$tpl->set_var("FillDeligation","");
	$tpl->set_var("FillManagerDeligation","");

	if(isset($arrLeaves) && $arrLeaves != '')
	{
		$arrStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");
		
		$tpl->set_var("comment_manager1",$arrLeaves['comment_manager']);
		$tpl->set_var("status_manager1",$arrStatus[$arrLeaves['status_manager']]);
		
		$tpl->set_var("comment_manager_delegate",$arrLeaves['manager_delegate_comment']);
		$tpl->set_var("status_manager_delegate",$arrStatus[$arrLeaves['manager_delegate_status']]);
		
		$tpl->set_var("comment1",$arrLeaves['comment']);
		$tpl->set_var("status1",$arrStatus[$arrLeaves['leave_status']]);
		
		$tpl->set_var("comment2",$arrLeaves['delegate_comment']);
		$tpl->set_var("status2",$arrStatus[$arrLeaves['delegate_status']]);

		$tpl->SetAllValues($arrLeaves);

		/* check with session if login user is manager then show manager editable block */
		if($arrLeaves['manager_id'] != "" && $arrLeaves['manager_id'] != "0")
		{
			$tpl->parse("DisplayManagerBlock",false);
		}

		/* check with session if login user is teamleader then show teamleader editable block */
		if($arrLeaves['teamleader_id'] != "" && $arrLeaves['teamleader_id'] != "0")
		{
			$tpl->parse("DisplayTLBlock",false);
		}

		/* check with session if login user is deligated manager then show deligated manager editable block */
		if($arrLeaves['deligateManagerId'] != "" && $arrLeaves['deligateManagerId'] != "0")
		{
			$tpl->parse("DisplayDelegateManagerBlock",false);
		}

		/* check with session if login user is deligated teamleader then show deligated teamleader editable block */
		if($arrLeaves['deligateTeamLeaderId'] != "" && $arrLeaves['deligateTeamLeaderId'] != "0")
		{
			$tpl->parse("DisplayDelegateTLBlock",false);
		}
	}

	$tpl->pparse('main',false);
?>

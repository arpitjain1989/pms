<?php
	include('common.php');
	$tpl = new Template($app_path);
	//print_r($_SESSION);
	$tpl->load_file('template.html','main');
	$tpl->load_file('halfleave_request.view.html','main_container');

	$PageIdentifier = "HalfLeaveRequest";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Half Leave Request");
	$breadcrumb = '<li><a href="employee.php">Manage Leave Request</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Half Leave Request</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');

	$objLeaves = new leave();
	$objEmployee = new employee();

	$arrLeaves = $objLeaves->fnGetHalfLeaveDetailsById($_REQUEST['id']);
	//echo '<pre>'; print_r($arrLeaves);

	$tpl->set_var("FillDeligationEditable","");
	/*if($arrLeaves['delegate'] != 0)
	{
		
	}*/
	
	$reportingHead = $objEmployee->fnGetReportingHead($arrLeaves['employee_id']);
	$lastThreeLeaves = $objLeaves->fnGetLastThreeLeaves($arrLeaves['employee_id'],$arrLeaves['leave_id']);
	$getAllTeamLeaders = $objEmployee->fnGetAllTeamLeaders($arrLeaves['id'],$arrLeaves['designation'],$_SESSION['designation']);

	$tpl->set_var("FillDeligation","");
	$tpl->set_var("FillTeamLeaders","");
	$tpl->set_var("session_designation",$_SESSION['designation']);

	//if(($_SESSION['designation'] == 6 || $_SESSION['designation'] == 8 || $_SESSION['designation'] == 17) &&  ($arrLeaves['designation'] == 7 || $arrLeaves['designation'] == 13 || $arrLeaves['designation'] == 6))
	if(($_SESSION['designation'] == 8 && $arrLeaves['designation'] == 6) || $_SESSION['designation'] == 6 && $arrLeaves['designation'] == 7 || $arrLeaves['designation'] == 13)
	{
	/*	//echo 'hello';
		if(count($getAllTeamLeaders)> 0)
		{
			foreach($getAllTeamLeaders as $AllTeamLeaders)
			{
				$tpl->setAllValues($AllTeamLeaders);
				$tpl->parse('FillTeamLeaders',true);
			}
		}
		$tpl->SetAllValues($arrLeaves);
		$tpl->parse("FillDeligation",false);
	*/}
		
	$tpl->set_var('id',$_REQUEST['id']);
	if($reportingHead != '')
	{
		if($reportingHead == '0')
		{
			$tpl->set_var('reportingHead',"Admin");
		}
		else
		{
			$tpl->set_var('reportingHead',$reportingHead);
		}
	}
	else
	{
		$tpl->set_var('reportingHead',"Admin");
	}


	if($_SESSION['usertype'] == 'admin')
	{
		$arrEmployee = $objEmployee->fnGetAllemployees(0);
	}
	else
	{
		$arrEmployee = $objEmployee->fnGetAllemployees($_SESSION['id']);
	}

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

	$arrLeaveRecords = $objLeaves->fnGetLeaveDetailByHead($arrLeaves['leave_id'],$ids,$arrLeaves['starting_date'],$arrLeaves['ending_date']);
	
	$tpl->set_var("DisplayActionButtons","");
	$tpl->set_var("DisplayBackButtons","");

	if($_SESSION['usertype'] == 'admin')
	{
		$tpl->parse("DisplayBackButtons",false);
	}
	else
	{
		$tpl->parse("DisplayActionButtons",false);
	}
	
	//echo '<pre>';	print_r($arrLeaveRecords);
	if(isset($arrLeaves))
		//echo '<pre>'; print_r($leaves);
	{

		if($arrLeaves['status_manager'] == '0')
		{
			$tpl->set_var("status_manager1","pending");
			$tpl->set_var("comment_manager1","pending");
			$tpl->set_var("comment_manager_readable",$arrLeaves['comment_manager']);
		}
		else if($arrLeaves['status_manager'] == '1')
		{
			$tpl->set_var("status_manager1",'Approved');
			$tpl->set_var("comment_manager1",$arrLeaves['comment_manager']);
			$tpl->set_var("comment_manager_readable",$arrLeaves['comment_manager']);
		}
		else if($arrLeaves['status_manager'] == '2')
		{
			$tpl->set_var("status_manager1","Reject");
			$tpl->set_var("comment_manager1",$arrLeaves['comment_manager']);
			$tpl->set_var("comment_manager_readable",$arrLeaves['comment_manager']);
		}

		if($arrLeaves['leave_status'] == '0')
		{
			$tpl->set_var("status1","pending");
			$tpl->set_var("comment1","pending");
		}
		else if($arrLeaves['leave_status'] == '1')
		{
			$tpl->set_var("status1",'Approved');
			$tpl->set_var("comment1",$arrLeaves['comment']);
		}
		else if($arrLeaves['leave_status'] == '2')
		{
			$tpl->set_var("status1","Reject");
			$tpl->set_var("comment1",$arrLeaves['comment']);
		}

		if($_SESSION['id'] == '1')
		{
			$tpl->set_var("FillTemaLeaderEditable","");
			$tpl->set_var("FillManagerEditable","");
			if($arrLeaves['designation'] == '7' || $arrLeaves['designation'] == '13')
			{
				$tpl->set_var("FillTemaLeaderReadable","");
			}
		}

		if($_SESSION['designation'] == '6' || $_SESSION['designation'] == '18' || $_SESSION['designation'] == '19')
		{
			/* Designation 6 - Manager */
			if($arrLeaves["team_leader_status_id"] == "2")
			{
				/* If rejected by the team leader cannot approved by the manager */
				$tpl->set_var("FillTemaLeaderEditable","");
				$tpl->set_var("FillManagerEditable","");
				$tpl->set_var("DisplayActionButtons","");
				$tpl->parse("DisplayBackButtons",false);
			}
			else
			{
				if($arrLeaves['designation'] == '7' || $arrLeaves['designation'] == '13')
				{
					$tpl->set_var("FillTemaLeaderReadable","");
				}
				$tpl->set_var("FillTemaLeaderEditable","");
				$tpl->set_var("FillManagerReadable","");
			}
		}
		else if($_SESSION['designation'] == '7' || $_SESSION['designation'] == '13')
		{
			$tpl->set_var("FillTemaLeaderReadable","");
			$tpl->set_var("FillManagerEditable","");
		}
		else if($_SESSION['designation'] == "5" || $_SESSION['designation'] == "9" || $_SESSION['designation'] == "10" || $_SESSION['designation'] == "11" || $_SESSION['designation'] == "12" || $_SESSION['designation'] == "14" || $_SESSION['designation'] == "15" || $_SESSION['designation'] == "16" || $_SESSION['designation'] == "20" || $_SESSION['designation'] == "21" || $_SESSION['designation'] == "22" || $_SESSION['designation'] == "23" || $_SESSION['designation'] == "24" || $_SESSION['designation'] == "25" || $_SESSION['designation'] == "26" || $_SESSION['designation'] == "27" || $_SESSION['designation'] == "28")
		{
			$tpl->set_var("FillManagerEditable","");
			$tpl->set_var("FillTemaLeaderEditable","");
		}
		else if($_SESSION['designation'] == "8" && $arrLeaves['designation'] == '6')
		{
			$tpl->set_var("FillTemaLeaderEditable","");
			$tpl->set_var("FillTemaLeaderReadable","");
			$tpl->set_var("FillManagerReadable","");
			$tpl->parse('FillManagerEditable',true);
			//$tpl->parse("FillDeligation",false);
		}

		if($arrLeaves['ph'] == '1')
		{
			$tpl->parse('PhLabel',true);
		}
		else
		{
			$tpl->set_var('PhLabel','');
		}

		//echo '<pre>';print_r($arrLeaves);
		$tpl->SetAllValues($arrLeaves);
		$tpl->set_var("leave_taker_designation",$arrLeaves['designation']);
		//$tpl->parse("FillDeligation",true);
	}


	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] == 'update')
	{
		$arrLeave = $objLeaves->fnUpdateHalfLeaveStatus($_POST);
		if($arrLeave)
		{
			header("Location: halfleave_request.php?info=update");
		}
		else
		{

		}
	}

	$tpl->set_var("FillLeaveHistory","");
	$tpl->set_var("FillNoLeaveHistory","");
	if(count($arrLeaveRecords) > 0)
	{
		foreach($arrLeaveRecords as $leavesRecord)
		{
			//print_r($leavesRecord);
			$tpl->setAllValues($leavesRecord);
			$tpl->parse("FillLeaveHistory","true");
		}
	}
	else
	{
		$tpl->parse("FillNoLeaveHistory","true");
	}

	$tpl->set_var("FillLastThreeLeaves","");
	$tpl->set_var("FillNoLastThreeLeaves","");
	if(count($lastThreeLeaves) > 0)
	{

		foreach($lastThreeLeaves as $threeLeaves)
		{
			if($threeLeaves['status_m'] == 1)
			{
				$status_manager = 'Approved';
			}
			else if($threeLeaves['status_m'] == 2)
			{
				$status_manager = 'Reject';
			}
			else
			{
				$status_manager = 'Pending';
			}

			if($threeLeaves['status_t'] == 1)
			{
				$status_tl = 'Approved';
			}
			else if($threeLeaves['status_t'] == 2)
			{
				$status_tl = 'Reject';
			}
			else
			{
				$status_tl = 'Pending';
			}

			$tpl->set_var("Teamleader_status",$status_tl);
			$tpl->set_var("manager_status",$status_manager);
			$tpl->setAllValues($threeLeaves);
			$tpl->parse("FillLastThreeLeaves","true");
		}
	}
	else
	{
		$tpl->parse("FillNoLastThreeLeaves","true");
	}


	$tpl->pparse('main',false);
?>

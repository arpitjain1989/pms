<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('halfleave_form.view.html','main_container');

	$PageIdentifier = "HalfLeaveForm";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Leave Form");
	$breadcrumb = '<li><a href="halfleave_form.php">Manage Leave Form</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Leave Form</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');

	$objLeave = new leave();
	$objEmployee = new employee();

	$arrLeaves = $objLeave->fnGetHalfLeaveDetailsById($_REQUEST['id']);

	$arrLeaveForm = $objLeave->fnGetHalfLeaveFormById($_REQUEST['id']);

	$reporting_head = $objEmployee->fnGetReportingHeadById($_SESSION['id']);
	if(isset($reporting_head))
	{
		$tpl->set_var("reportinghead",$reporting_head);
	}

	if(count($arrLeaveForm) > 0)
	{
		$tpl->set_var("address_leave",$arrLeaveForm['address']);
		$tpl->set_var("contact_no",$arrLeaveForm['contact']);

		$tpl->SetAllValues($arrLeaveForm);
		$newStartDate = date("d-m-Y", strtotime($arrLeaveForm['start_date']));
		$tpl->set_var('startdate',$newStartDate);
	}

	$tpl->set_var('FillTemaLeaderReadable','');
	$tpl->set_var('FillDelegateTemaLeaderReadable','');
	$tpl->set_var('FillManagerReadable','');
	$tpl->set_var('FillDelegateManagerReadable','');
	$tpl->set_var('DisplayAdminAddedBlock','');
	$tpl->set_var('PhLabel','');

	if(isset($arrLeaves) && $arrLeaves != '')
	{
		$arrApprovalStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");
		$arrHalfDayFor = array("1"=>"First half", "2"=>"Second half");
		
		$tpl->set_var("status_manager1",$arrApprovalStatus[$arrLeaves['status_manager']]);
		$tpl->set_var("comment_manager1",$arrLeaves['comment_manager']);

		$tpl->set_var("status1",$arrApprovalStatus[$arrLeaves['leave_status']]);
		$tpl->set_var("comment1",$arrLeaves['comment']);
		
		$tpl->set_var("status2",$arrApprovalStatus[$arrLeaves['delegate_status']]);
		$tpl->set_var("comment2",$arrLeaves['delegate_comment']);
		
		$tpl->set_var("status3",$arrApprovalStatus[$arrLeaves['manager_delegate_status']]);
		$tpl->set_var("comment3",$arrLeaves['manager_delegate_comment']);

		$tpl->set_var("halfdayfor_text",$arrHalfDayFor[$arrLeaves['halfdayfor']]);

		if($arrLeaves["isadminadded"] == "1")
		{
			$tpl->parse("DisplayAdminAddedBlock",false);
		}
		else
		{
			/* check with session if login user is manager then show manager editable block */
			if($arrLeaves['manager_id'] != "" && $arrLeaves['manager_id'] != "0")
			{
				$tpl->parse("FillManagerReadable",false);
			}

			/* check with session if login user is teamleader then show teamleader editable block */
			if($arrLeaves['teamleader_id'] != "" && $arrLeaves['teamleader_id'] != "0")
			{
				$tpl->parse("FillTemaLeaderReadable",false);
			}

			/* check with session if login user is deligated manager then show deligated manager editable block */
			if($arrLeaves['deligateManagerId'] != "" && $arrLeaves['deligateManagerId'] != "0")
			{
				$tpl->parse("FillDelegateManagerReadable",false);
			}

			/* check with session if login user is deligated teamleader then show deligated teamleader editable block */
			if($arrLeaves['deligateTeamLeaderId'] != "" && $arrLeaves['deligateTeamLeaderId'] != "0")
			{
				$tpl->parse("FillDelegateTemaLeaderReadable",false);
			}

			if($arrLeaves['ph'] == '1')
			{
				$tpl->parse('PhLabel',true);
			}
		}
		$tpl->SetAllValues($arrLeaves);
	}

	$tpl->pparse('main',false);
?>

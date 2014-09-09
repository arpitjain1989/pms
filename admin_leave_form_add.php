<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_leave_form_add.html','main_container');

	$PageIdentifier = "AdminLeaveForm";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add / Edit Leave Form");
	$breadcrumb = '<li><a href="admin_leave_form.php">Manage Admin Leave Form</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Leave Form</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');
	include_once('includes/class.attendance.php');

	$objLeave = new leave();
	$objEmployee = new employee();
	$objAttendance = new attendance();
	
	
			
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'AdminLeaveAdd')
	{
		$insertdata = $objLeave->fnInserAdminLeaveForm($_POST);

		if($insertdata == -1)
		{
			header("Location: admin_leave_form.php?info=earlyerr");
			exit;
		}
		else if($insertdata == -2)
		{
			header("Location: admin_leave_form.php?info=admexist");
			exit;
		}
		else if($insertdata == -3)
		{
			header("Location: admin_leave_form.php?info=aerr");
			exit;
		}
		else if($insertdata != '')
		{
			$leaveDetails = $objLeave->fnGetLeaveDetailById($insertdata);
			
			$curEmployee = $objEmployee->fnGetEmployeeDetailById($leaveDetails['employee_id']);
			$Subject = 'Admin Leave application';
			
			$addedby = "";
			if($_SESSION["usertype"] == "admin")
				$addedby = "LMS Admin";
			else if($_SESSION["usertype"] == "employee")
				$addedby = $_SESSION["displayname"];
			
			$userMailContent = "Dear Admin, <br /><br />".$addedby." has added a leave for ".$curEmployee["name"]." from ".$leaveDetails['startDate']." to ".$leaveDetails['endDate']." for ".$leaveDetails['nodays']." day(s).<br/>Reason for adding leave is : ".$leaveDetails['reason']."<br><br>Regards,<br>".SITEADMINISTRATOR;

			sendmail("admin@transformsolution.net",$Subject,$userMailContent);

			header("Location: admin_leave_form.php?info=succ");
			exit;
		}
		else
		{
			header("Location: admin_leave_form.php?info=shift");
			exit;
		}
	}
	
	$tpl->set_var("FillLeaveForBlock","");
	$arrEmployees = $objEmployee->fnGetAllEmployeesDetails(0);
	if(count($arrEmployees) > 0)
	{
		foreach($arrEmployees as $curEmployees)
		{
			$tpl->set_var("leave_form_id", $curEmployees["id"]);
			$tpl->set_var("leave_form_name", $curEmployees["name"]);
			
			$tpl->parse("FillLeaveForBlock", true);
		}
	}
	
/*
	$reporting_head = $objEmployee->fnGetReportingHeadById($_SESSION['id']);
	$userDetails = $objEmployee->fnGetEmployeeDetailById($_SESSION['id']);
	$arrGetAllPh = $objEmployee->fnGetAllPhDetails($_SESSION['id']);

	$tpl->set_var('addr',$userDetails['contact']);
	$tpl->set_var('cont',$userDetails['address']);

	$arrNotPhDates = array();
	$phcounts = 0;

	if(count($arrGetAllPh) > 0)
	{
		foreach($arrGetAllPh as $PhVal)
		{
			$phStatus = $objEmployee->fnCheckPh($_SESSION['id'],$PhVal);
			if($phStatus > 0)
			{
				$arrNotPhDates[] = $PhVal;
				$phcounts = $phcounts + 1;
			}
		}
	}
	$arrGetAllTakenPh = $objEmployee->fnCheckPhTakenOrLeave($_SESSION['id']);

	$final_count = $phcounts - $arrGetAllTakenPh;

	if(count($userDetails) > 0)
	{
		$pendingLeaves = $objAttendance->fnGetLastLeaveBalance($_SESSION['id']);

		$unpaid_leaves = $objAttendance->fnGetUserLeavesWithoutPayByMonthAndYear($_SESSION['id'], Date('m'), Date('Y'));

		$pendingLeaveBalance = 0;
		if($pendingLeaves > 0)
			$pendingLeaveBalance = $pendingLeaves;

		$tpl->set_var("pending_leave_balance", $pendingLeaveBalance);

		$eligible_leaves = ($pendingLeaves - $unpaid_leaves) + 7;
		$tpl->set_var('eligible_bal',$eligible_leaves);

		$tpl->setAllValues($userDetails);
	}
	//print_r($_SESSION);
	if($_SESSION['teamleader'] == 0)
	{
		$tpl->set_var("reportinghead",'Admin');
	}
	else
	{
		$tpl->set_var("reportinghead",$reporting_head);
	}
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('leaveformid',"$_REQUEST[id]");
	}
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		$pendingLeaves = $objAttendance->fnGetLastLeaveBalance($_SESSION['id']);

		$pendingLeaveBalance = 0;
		if($pendingLeaves > 0)
			$pendingLeaveBalance = $pendingLeaves;

		
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
		$arrLeaveForm = $objLeave->fnGetLeaveFormById($_REQUEST['id']);
		foreach($arrLeaveForm as $arrLeaveFormtvalue)
		{
			$tpl->SetAllValues($arrLeaveFormtvalue);
			$newStartDate = date("Y-m-d", strtotime($arrLeaveFormtvalue['start_date']));
			$newEndDate = date("Y-m-d", strtotime($arrLeaveFormtvalue['end_date']));
			$tpl->set_var('startdate',$newStartDate);
			$tpl->set_var('enddate',$newEndDate);
		}
		$tpl->set_var('action','update');
	}
*/

	$tpl->pparse('main',false);
?>

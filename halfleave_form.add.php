<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('halfleave_form.add.html','main_container');
	//print_r($_SESSION);
	$PageIdentifier = "HalfLeaveForm";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add / Edit Leave Form");
	$breadcrumb = '<li><a href="leave_form.php">Manage Leave Form</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Leave Form</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');
	include_once('includes/class.attendance.php');
	include_once('includes/class.phpmailer.php');
	
	$objLeave = new leave();
	$objEmployee = new employee();
	$objAttendance = new attendance();
	
	$reporting_head = $objEmployee->fnGetReportingHeadById($_SESSION['id']);
	$userDetails = $objEmployee->fnGetEmployeeDetailById($_SESSION['id']);
	$arrGetAllPh = $objEmployee->fnGetAllPhDetails($_SESSION['id']);

	$tpl->set_var("leave_date",Date('Y-m-d'));
	$tpl->set_var("previous_date",Date('Y-m-d', strtotime('-1 day')));

	$tpl->set_var("eid",$_SESSION['id']);

	$phcounts = 0;
	if(count($arrGetAllPh) > 0)
	{
		$arrNotPhDates = array();
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
	$tpl->set_var('PhCheckBox','');
	/*if(isset($final_count) && $final_count > 0)
	{
		$tpl->set_var('phcounts',$final_count);
		$tpl->parse('PhCheckBox',true);
	}*/

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
		
		$insertdata = $objLeave->fnInsertHalfLeaveForm($_SESSION['id'],$_POST);

		if($insertdata == -1)
		{
			header("Location: halfleave_form.php?info=earlyerr");
			exit;
		}
		else if($insertdata == -2)
		{
			header("Location: halfleave_form.php?info=admexist");
			exit;
		}
		else if($insertdata != '')
		{	
			$leaveDetails = $objLeave->fnGetHalfLeaveFormById($insertdata);
			
			$getLastLeave = $objLeave->fnGetLastLeave($leaveDetails['employee_id'],$insertdata);
			$getCountAllUnApprove = $objAttendance->fnGetAllUnApprove($leaveDetails['employee_id'],$insertdata,$leaveDetails['cur_year']);
			$totalLeavesAvailable = $objEmployee->fnGetAllLeaveAvail($leaveDetails['employee_id']);
			/*$getAllReportingHeads = $objEmployee->fnGetReportingHeads($leaveDetails['employee_id']);*/
			
			$curEmployee = $objEmployee->fnGetEmployeeDetailById($leaveDetails['employee_id']);
			
			if(count($getLastLeave) > 0)
			{
				$noOfLeavesAvail = $getLastLeave['number_d'];
				
				$arrApprovalStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");
				$arrHalfDayFor = array("1"=>"First Half", "2"=>"Second Half");

				$status_mn = $arrApprovalStatus[$getLastLeave['status_m']];
				$status_tl = $arrApprovalStatus[$getLastLeave['status_t']];
				
				/*if($getLastLeave['status_m'] == 0)
				{
					$status_mn = 'Pending';
				}
				else if($getLastLeave['status_m'] == 1)
				{
					$status_mn = 'Approved';
				}
				else if($getLastLeave['status_m'] == 2)
				{
					$status_mn = 'Unapproved';
				}
				
				if($getLastLeave['status_t'] == 0)
				{
					$status_tl = 'Pending';
				}
				else if($getLastLeave['status_t'] == 1)
				{
					$status_tl = 'Approved';
				}
				else if($getLastLeave['status_t'] == 2)
				{
					$status_tl = 'Unapproved';
				}*/
			}
			else
			{	
				$noOfLeavesAvail = 0;
			}
			//$table = "Dear "" , <br />A new leave request is added. The details for the leave are as follows:<br />";
			$table = "<table cellspacing='2' cellpadding='2' bgcolor='#E6E6E6'>";
			$table .= "<tr bgcolor='#FFFFFF'><td><b>No of days :</b></td><td>".$leaveDetails['nodays']."</td></tr>";
			$table .= "<tr bgcolor='#FFFFFF'><td><b>Halfday For :</b></td><td>".$arrHalfDayFor[$leaveDetails['halfdayfor']]."</td></tr>";
			$table .= "<tr bgcolor='#FFFFFF'><td><b>Dates :</b></td><td>".$leaveDetails['startDate'].' To '.$leaveDetails['startDate']."</td></tr>";
			$table .= "<tr bgcolor='#FFFFFF'><td><b>Reason :</b></td><td>".$leaveDetails['reason']."</td></tr>";
			$table .= "<tr bgcolor='#FFFFFF'><td colspan='2'>&nbsp;</td></tr>";
			$table .= "<tr bgcolor='#FFFFFF'><td colspan='2'><b>His/Her leave history is as below:</b></td></tr>";			
			$table .= "<tr bgcolor='#FFFFFF'><td><b>Total unapproved leaves in year :</b></td><th>".$noOfLeavesAvail."</th></tr>";
			$table .= "<tr bgcolor='#FFFFFF'><td><b>Total Available leaves :</b></td><th>".$pendingLeaveBalance ."</th></tr>";

			if(count($getLastLeave) > 0)
			{
				//echo 'hello1======='.count($getLastLeave);
				$table .= "<tr bgcolor='#FFFFFF'><td colspan='2'>&nbsp;</td></tr>";
				$table .= "<tr bgcolor='#FFFFFF'><td colspan='2'><b>Last Leave Taken :</b></td></tr>";
				$table .= "<tr bgcolor='#FFFFFF'><td><b>No of days :</b></td><td>".$getLastLeave['number_d']."</td></tr>";
				$table .= "<tr bgcolor='#FFFFFF'><td><b>Dates :</b></td><td>".$getLastLeave['start_d'].' To '.$getLastLeave['end_d']."</td></tr>";
				$table .= "<tr bgcolor='#FFFFFF'><td colspan='2'>&nbsp;</td></tr>";
				$table .= "<tr bgcolor='#FFFFFF'><td><b>Status by TeamLeader :</b></td><td>".$status_tl."</td></tr>";
				$table .= "<tr bgcolor='#FFFFFF'><td><b>Status by Manager :</b></td><td>".$status_mn."</td></tr>";
			}
			$table .= "<tr bgcolor='#FFFFFF'><td colspan='2'>&nbsp;</td></tr>";
			
			$table .= "</table><br>";
			//$table .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
			//echo $table;

			/*echo $table;
			die;*/
			//echo 'hello'; 
			
			$employeeDetailsById = $objEmployee->fnGetEmployeeById($leaveDetails['employee_id']);

			$Subject = 'Halfday Leave application';

			//echo '<pre>'; print_r($leaveDetails);

			/* First Reporting Head */
			if(isset($leaveDetails['teamleader_id']) && trim($leaveDetails['teamleader_id']) != ""  && trim($leaveDetails['teamleader_id']) != "0")
			{
				$arrTeamLeader = $objEmployee->fnGetEmployeeById($leaveDetails['teamleader_id']);

				$content = "Dear ".$arrTeamLeader['name'].", <br /><br />".$employeeDetailsById["name"]." has applied for leave/s. The details for his/her leave application are as follows:<br /><br />".$table;
				if($leaveDetails["tlapprovalcode"] != "")
				{
					$content .= "To approve / unapprove the request, please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$leaveDetails["tlapprovalcode"]."_Approve_HL]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$leaveDetails["tlapprovalcode"]."_Reject_HL]'>Reject</a>";
				}
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
				//echo '<br>content:'.$content;
				sendmail($arrTeamLeader['email'],$Subject,$content);
			}

			/* Second Reporting Head */
			if(isset($leaveDetails['manager_id']) && trim($leaveDetails['manager_id']) != ""  && trim($leaveDetails['manager_id']) != "0")
			{
				$arrManager = $objEmployee->fnGetEmployeeById($leaveDetails['manager_id']);

				$content = "Dear ".$arrManager['name'].", <br /><br />".$employeeDetailsById["name"]." has applied for leave/s. The details for his/her leave application are as follows:<br /><br />".$table;
				if($leaveDetails["managerapprovalcode"] != "")
				{
					$content .= "To approve / unapprove the request, please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$leaveDetails["managerapprovalcode"]."_Approve_HL]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$leaveDetails["managerapprovalcode"]."_Reject_HL]'>Reject</a>";
				}
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
				//echo '<br>content:'.$content;
				sendmail($arrManager['email'],$Subject,$content);
			}

			if(isset($leaveDetails['deligateTeamLeaderId']) && $leaveDetails['deligateTeamLeaderId'] != '' && $leaveDetails['deligateTeamLeaderId'] != '0')
			{
				$deligatedTeamleaderDetails = $objEmployee->fnGetEmployeeById($leaveDetails['deligateTeamLeaderId']);

				$content = "Dear ".$deligatedTeamleaderDetails['name'].", <br /><br />".$curEmployee["name"]." has applied for leave/s. The details for his/her leave application are as follows:<br /><br />".$table;
				if($leaveDetails["delegatedtlapprovalcode"] != "")
				{
					$content .= "To approve / unapprove the request, please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$leaveDetails["delegatedtlapprovalcode"]."_Approve_HL]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$leaveDetails["delegatedtlapprovalcode"]."_Reject_HL]'>Reject</a>";
				}
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
				//echo '<br>content:'.$content;
				sendmail($deligatedTeamleaderDetails['email'],$Subject,$content);
			}

			if(isset($leaveDetails['deligateManagerId']) && $leaveDetails['deligateManagerId'] != '' && $leaveDetails['deligateManagerId'] != '0')
			{
				$deligatedManagerDetails = $objEmployee->fnGetEmployeeById($leaveDetails['deligateManagerId']);

				$content = "Dear ".$deligatedManagerDetails['name'].", <br /><br />".$curEmployee["name"]." has applied for leave/s. The details for his/her leave application are as follows:<br /><br />".$table;
				if($leaveDetails["delegatedmanagerapprovalcode"] != "")
				{
					$content .= "To approve / unapprove the request, please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$leaveDetails["delegatedmanagerapprovalcode"]."_Approve_HL]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$leaveDetails["delegatedmanagerapprovalcode"]."_Reject_HL]'>Reject</a>";
				}
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
				sendmail($deligatedManagerDetails['email'],$Subject,$content);
			}

			/*
			foreach($getAllReportingHeads as $reportingheads)
			{
				//echo 'hello'; print_r($leaveDetails); die;
				$uniqueCode = "";
				$Subject = 'Halfday Leave application';
				if($reportingheads["designation"] == "6" || $reportingheads["designation"] == "18" || $reportingheads["designation"] == "19")
				{
					$uniqueCode = $leaveDetails["managerapprovalcode"];
				}
				else if($reportingheads["designation"] == "7" || $reportingheads["designation"] == "13")
				{
					$uniqueCode = $leaveDetails["tlapprovalcode"];
				}
				else if($reportingheads["designation"] == "17")
				{
					$uniqueCode = $leaveDetails["managerapprovalcode"];
				}
				//print_r($reportingheads);
				$content = "Dear ".$reportingheads['name'].", <br /><br />".$curEmployee["name"]." has applied for leave/s. The details for his/her leave application are as follows:<br /><br />".$table;

				if($uniqueCode != "")
				{
					
					//$content .= "To approve / unapprove the request, please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Approve_HL]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Reject_HL]'>Reject</a>";
					$content .= "Please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Approve_HL]'>Approve</a>  |  <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Reject_HL]'>Reject</a> for letting us know your decision.";
				}
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
				sendmail($reportingheads['email'],$Subject,$content);
				//sendmail1("chandni.patel@transformsolution.net",$Subject,$content);
			}*/
			
			header("Location: halfleave_form.php?info=succ");
			exit;
		}
		else
		{
			header("Location: halfleave_form.php?info=shift");
			exit;
		}
	}
	
	/*if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
		$updateLeaveForm = $objLeave->fnUpdateLeaveForm($_POST);
		if($updateLeaveForm)
		{
			header("Location: halfleave_form.php?info=update");
			exit;
		}
	}*/
	
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
	
	
	$tpl->pparse('main',false);
?>

<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('leave_form.add.html','main_container');

	$PageIdentifier = "LeaveForm";
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

	$tpl->set_var("DisplayDecemberMessageBlock","");
	if(Date("m") == "12")
		$tpl->parse("DisplayDecemberMessageBlock",false);
		
	$tpl->set_var('addr',$userDetails['contact']);
	$tpl->set_var('cont',$userDetails['address']);
	//echo count($arrGetAllPh);echo '<br>';
	$arrNotPhDates = array();
	$phcounts = 0;

	if(count($arrGetAllPh) > 0)
	{
		foreach($arrGetAllPh as $PhVal)
		{
			//	echo $PhVal;
			$phStatus = $objEmployee->fnCheckPh($_SESSION['id'],$PhVal);
			if($phStatus > 0)
			{
				$arrNotPhDates[] = $PhVal;
				$phcounts = $phcounts + 1;
			}
		}
//echo $phcounts;

	//	$tpl->set_var('phcount',$final_count);
	}
	$arrGetAllTakenPh = $objEmployee->fnCheckPhTakenOrLeave($_SESSION['id']);
	//echo "<br/>phcounts-----" . $phcounts;
	//echo "<br/>arrGetAllTakenPh--------" . $arrGetAllTakenPh;
	$final_count = $phcounts - $arrGetAllTakenPh;
//echo $final_count;
	//echo $phcounts;
	//echo 'final_count'.$final_count;
	$tpl->set_var('PhCheckBox','');
	if(isset($final_count) && $final_count > 0)
	{

		$tpl->set_var('phcounts',$final_count);
		$tpl->parse('PhCheckBox',true);
	}

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

		$insertdata = $objLeave->fnInsertLeaveForm($_SESSION['id'],$_POST);

		if($insertdata == -1)
		{
			header("Location: leave_form.php?info=earlyerr");
			exit;
		}
		else if($insertdata == -2)
		{
			header("Location: leave_form.php?info=admexist");
			exit;
		}
		else if($insertdata != '')
		{
			$leaveDetails = $objLeave->fnGetLeaveDetailById($insertdata);

			$getLastLeave = $objLeave->fnGetLastLeave($leaveDetails['employee_id'],$insertdata);
			$getCountAllUnApprove = $objAttendance->fnGetAllUnApprove($leaveDetails['employee_id'],$insertdata,$leaveDetails['cur_year']);
			$totalLeavesAvailable = $objEmployee->fnGetAllLeaveAvail($leaveDetails['employee_id']);
			/*$getAllReportingHeads = $objEmployee->fnGetReportingHeads($leaveDetails['employee_id']);*/
			
			$curEmployee = $objEmployee->fnGetEmployeeDetailById($leaveDetails['employee_id']);
			
			//print_r($getLastLeave);
			//echo 'hello'.count($getLastLeave);
			if(count($getLastLeave) > 0)
			{
				$noOfLeavesAvail = $getLastLeave['number_d'];
				
				$arrApprovalStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");

				$status_mn = $arrApprovalStatus[$getLastLeave['status_m']];
				$status_tl = $arrApprovalStatus[$getLastLeave['status_t']];
				$d_TeamLeaderStatus = $arrApprovalStatus[$getLastLeave['d_TeamLeaderStatus']];
				$d_ManagerStatus = $arrApprovalStatus[$getLastLeave['d_ManagerStatus']];
				
			}
			else
			{
				$noOfLeavesAvail = 0;
			}
				
			$table = "<table cellspacing='2' cellpadding='2' bgcolor='#E6E6E6'>";
			
			$table .= "<tr bgcolor='#FFFFFF'><td><b>No of days :</b></td><td>".$leaveDetails['nodays']."</td></tr>";
			$table .= "<tr bgcolor='#FFFFFF'><td><b>Dates :</b></td><td>From ".$leaveDetails['startDate'].' To '.$leaveDetails['endDate']."</td></tr>";
			$table .= "<tr bgcolor='#FFFFFF'><td><b>Reason :</b></td><td>".$leaveDetails['reason']."</td></tr>";
			$table .= "<tr bgcolor='#FFFFFF'><td colspan='2'>&nbsp;</td></tr>";
			$table .= "<tr bgcolor='#FFFFFF'><td colspan='2'><b>His/Her leave history is as below:</b></td></tr>";
			$table .= "<tr bgcolor='#FFFFFF'><td><b>Total unapproved leave/s in year :</b></td><th>".$noOfLeavesAvail."</th></tr>";
			$table .= "<tr bgcolor='#FFFFFF'><td><b>Total Available leaves :</b></td><th>".$pendingLeaveBalance."</th></tr>";
			
			if(count($getLastLeave) > 0)
			{
				//echo 'hello1======='.count($getLastLeave);
				$table .= "<tr bgcolor='#FFFFFF'><td colspan='2'>&nbsp;</td></tr>";
				$table .= "<tr bgcolor='#FFFFFF'><td colspan='2'><b>Last Leave Taken :</b></td></tr>";
				$table .= "<tr bgcolor='#FFFFFF'><td><b>No of days :</b></td><td>".$getLastLeave['number_d']."</td></tr>";
				$table .= "<tr bgcolor='#FFFFFF'><td><b>Dates :</b></td><td>From ".$getLastLeave['start_d'].' To '.$getLastLeave['end_d']."</td></tr>";
				$table .= "<tr bgcolor='#FFFFFF'><td colspan='2'>&nbsp;</td></tr>";
				$table .= "<tr bgcolor='#FFFFFF'><td><b>Status by TeamLeader :</b></td><td>".$status_tl."</td></tr>";
				$table .= "<tr bgcolor='#FFFFFF'><td><b>Status by Manager :</b></td><td>".$status_mn."</td></tr>";
				if($getLastLeave['d_teamleaderId'] != '0')
				{
					$table .= "<tr bgcolor='#FFFFFF'><td><b>Status by Delegate Team Leader :</b></td><td>".$d_TeamLeaderStatus."</td></tr>";
				}
				if($getLastLeave['d_managerId'] != '0')
				{
					$table .= "<tr bgcolor='#FFFFFF'><td><b>Status by Delegate Manager :</b></td><td>".$d_ManagerStatus."</td></tr>";
				}
			}
			
			
			$table .= "</table><br />";
			
			 /* when leave add by team leader only send mail to manager*/
			$employeeDetailsById = $objEmployee->fnGetEmployeeById($leaveDetails['employee_id']);

			$Subject = 'Leave application';

			/* First Reporting Head */
			if(isset($leaveDetails['teamleader_id']) && trim($leaveDetails['teamleader_id']) != ""  && trim($leaveDetails['teamleader_id']) != "0")
			{
				$arrTeamLeader = $objEmployee->fnGetEmployeeById($leaveDetails['teamleader_id']);

				$content = "Dear ".$arrTeamLeader['name'].", <br /><br />".$employeeDetailsById["name"]." has applied for leave/s. The details for his/her leave application are as follows:<br /><br />".$table;
				if($leaveDetails["tlapprovalcode"] != "")
				{
					$content .= "To approve / unapprove the request, please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$leaveDetails["tlapprovalcode"]."_Approve_L]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$leaveDetails["tlapprovalcode"]."_Reject_L]'>Reject</a>";
				}
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

				sendmail($arrTeamLeader['email'],$Subject,$content);
			}

			/* Second Reporting Head */
			if(isset($leaveDetails['manager_id']) && trim($leaveDetails['manager_id']) != ""  && trim($leaveDetails['manager_id']) != "0")
			{
				$arrManager = $objEmployee->fnGetEmployeeById($leaveDetails['manager_id']);

				$content = "Dear ".$arrManager['name'].", <br /><br />".$employeeDetailsById["name"]." has applied for leave/s. The details for his/her leave application are as follows:<br /><br />".$table;
				if($leaveDetails["managerapprovalcode"] != "")
				{
					$content .= "To approve / unapprove the request, please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$leaveDetails["managerapprovalcode"]."_Approve_L]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$leaveDetails["managerapprovalcode"]."_Reject_L]'>Reject</a>";
				}
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

				sendmail($arrManager['email'],$Subject,$content);
			}

			if(isset($leaveDetails['deligateTeamLeaderId']) && $leaveDetails['deligateTeamLeaderId'] != '' && $leaveDetails['deligateTeamLeaderId'] != '0')
			{
				$deligatedTeamleaderDetails = $objEmployee->fnGetEmployeeById($leaveDetails['deligateTeamLeaderId']);

				$content = "Dear ".$deligatedTeamleaderDetails['name'].", <br /><br />".$curEmployee["name"]." has applied for leave/s. The details for his/her leave application are as follows:<br /><br />".$table;
				if($leaveDetails["delegatedtlapprovalcode"] != "")
				{
					$content .= "To approve / unapprove the request, please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$leaveDetails["delegatedtlapprovalcode"]."_Approve_L]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$leaveDetails["delegatedtlapprovalcode"]."_Reject_L]'>Reject</a>";
				}
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

				sendmail($deligatedTeamleaderDetails['email'],$Subject,$content);
			}

			if(isset($leaveDetails['deligateManagerId']) && $leaveDetails['deligateManagerId'] != '' && $leaveDetails['deligateManagerId'] != '0')
			{
				$deligatedManagerDetails = $objEmployee->fnGetEmployeeById($leaveDetails['deligateManagerId']);

				$content = "Dear ".$deligatedManagerDetails['name'].", <br /><br />".$curEmployee["name"]." has applied for leave/s. The details for his/her leave application are as follows:<br /><br />".$table;
				if($leaveDetails["delegatedmanagerapprovalcode"] != "")
				{
					$content .= "To approve / unapprove the request, please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$leaveDetails["delegatedmanagerapprovalcode"]."_Approve_L]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$leaveDetails["delegatedmanagerapprovalcode"]."_Reject_L]'>Reject</a>";
				}
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

				sendmail($deligatedManagerDetails['email'],$Subject,$content);
			}
			
			/* when leave added by manager */
			/*if($employeeDetailsById['designation'] == '6' || $employeeDetailsById['designation'] == '18' || $employeeDetailsById['designation'] == '19' || $employeeDetailsById['designation'] == "25" || $employeeDetailsById['designation'] == "44")
			{
				$managerInfo = 	$objEmployee->fnGetEmployeeById($employeeDetailsById['teamleader_id']);
				$uniqueCode = $leaveDetails["managerapprovalcode"];

				$Subject = 'Leave application';

				$content = "Dear ".$managerInfo['name'].", <br /><br />".$curEmployee["name"]." has applied for leave/s. The details for his/her leave application are as follows:<br /><br />".$table;
				
				
				if($uniqueCode != "")
					{
						$content .= "To approve / unapprove the request, please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Approve_L]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Reject_L]'>Reject</a>";
					}
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
				//echo 'hello1'.$content;
				sendmail($managerInfo['email'],$Subject,$content);
			}*/
			
			/*if($employeeDetailsById['designation'] == '7' || $employeeDetailsById['designation'] == '13')
			{
				$managerInfo = 	$objEmployee->fnGetEmployeeById($employeeDetailsById['teamleader_id']);
				$uniqueCode = $leaveDetails["managerapprovalcode"];

				$Subject = 'Leave application';

				$content = "Dear ".$managerInfo['name'].", <br /><br />".$curEmployee["name"]." has applied for leave/s. The details for his/her leave application are as follows:<br /><br />".$table;
				
				
				if($uniqueCode != "")
					{
						$content .= "To approve / unapprove the request, please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Approve_L]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Reject_L]'>Reject</a>";
					}
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
				//echo 'hell2'.$content;
				sendmail($managerInfo['email'],$Subject,$content);
			}*/
			
			

			 /* When leave applied by agent send mail to all reporting heads */
			/*else if($employeeDetailsById['designation'] == "5" || $employeeDetailsById['designation'] == "9" || $employeeDetailsById['designation'] == "10" || $employeeDetailsById['designation'] == "11" ||  $employeeDetailsById['designation'] == "14" || $employeeDetailsById['designation'] == "12" || $employeeDetailsById['designation'] == "15" || $employeeDetailsById['designation'] == "16" || $employeeDetailsById['designation'] == "20" || $employeeDetailsById['designation'] == "21" || $employeeDetailsById['designation'] == "22" || $employeeDetailsById['designation'] == "23" || $employeeDetailsById['designation'] == "24" || $employeeDetailsById['designation'] == "26" || $employeeDetailsById['designation'] == "27" || $employeeDetailsById['designation'] == "28" || $employeeDetailsById['designation'] == '30' || $employeeDetailsById['designation'] == '31' || $employeeDetailsById['designation'] == '32' || $employeeDetailsById['designation'] == '33' || $employeeDetailsById['designation'] == '34' || $employeeDetailsById['designation'] == '35' || $employeeDetailsById['designation'] == '36' || $employeeDetailsById['designation'] == '37' || $employeeDetailsById['designation'] == '38' ||  $employeeDetailsById['designation'] == '39' || $employeeDetailsById['designation'] == '40' || $employeeDetailsById['designation'] == '41' || $employeeDetailsById['designation'] == '42' || $employeeDetailsById['designation'] == '43' )
			{
				foreach($getAllReportingHeads as $reportingheads)
				{
					$uniqueCode = "";
					$Subject = 'Leave application';
					if($reportingheads["designation"] == "6" || $reportingheads["designation"] == "18" || $reportingheads["designation"] == "19" || $reportingheads["designation"] == "25" || $reportingheads["designation"] == "44")
					{
						$uniqueCode = $leaveDetails["managerapprovalcode"];
					}
					else if($reportingheads["designation"] == "7" || $reportingheads["designation"] == "13")
					{
						$uniqueCode = $leaveDetails["tlapprovalcode"];
					}
					
					$content = "Dear ".$reportingheads['name'].", <br /><br />".$curEmployee["name"]." has applied for leave/s. The details for his/her leave application are as follows:<br /><br />".$table;
					
					if($uniqueCode != "")
					{
						$content .= "To approve / unapprove the request, please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Approve_L]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Reject_L]'>Reject</a>";
					}
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
					//echo '<br>hello3<br>'.$content;
					//sendmail($reportingheads['email'],$Subject,$content);

					sendmail($reportingheads['email'],$Subject,$content);
					//sendmail1("chandni.patel@transformsolution.net",$Subject,$content);
				}
			}
			//echo 'hello<pre>'; print_r($leaveDetails);
			if($leaveDetails['deligateTeamLeaderId'] != '' && $leaveDetails['deligateTeamLeaderId'] != '0')
			{
				$uniqueCode = $leaveDetails["delegatedtlapprovalcode"];
				$deligatedTeamleaderDetails = $objEmployee->fnGetEmployeeById($leaveDetails['deligateTeamLeaderId']);
				//echo '<pre>'; print_r($deligatedTeamleaderDetails); die;
				$Subject = 'Leave application';
				$content = "Dear ".$deligatedTeamleaderDetails['name'].", <br /><br />".$curEmployee["name"]." has applied for leave/s. The details for his/her leave application are as follows:<br /><br />".$table;
				if($uniqueCode != "")
					{
						$content .= "To approve / unapprove the request, please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Approve_L]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Reject_L]'>Reject</a>";
					}
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
				//echo '<br>hello4'.$content;
				sendmail($deligatedTeamleaderDetails['email'],$Subject,$content);
			}
			if($leaveDetails['deligateManagerId'] != '' && $leaveDetails['deligateManagerId'] != '0')
			{
				$uniqueCode = $leaveDetails["delegatedmanagerapprovalcode"];
				$deligatedManagerDetails = $objEmployee->fnGetEmployeeById($leaveDetails['deligateManagerId']);
				//echo '<pre>'; print_r($deligatedTeamleaderDetails); die;
				$Subject = 'Leave application';
				$content = "Dear ".$deligatedManagerDetails['name'].", <br /><br />".$curEmployee["name"]." has applied for leave/s. The details for his/her leave application are as follows:<br /><br />".$table;
				if($uniqueCode != "")
					{
						$content .= "To approve / unapprove the request, please click <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Approve_L]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Reject_L]'>Reject</a>";
					}
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
				//echo '<br>hello5'.$content;
				sendmail($deligatedManagerDetails['email'],$Subject,$content);
			}*/

			header("Location: leave_form.php?info=succ");
			exit;
		}
		else
		{
			header("Location: leave_form.php?info=shift");
			exit;
		}
	}
	
	/*if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
		$updateLeaveForm = $objLeave->fnUpdateLeaveForm($_POST);
		if($updateLeaveForm)
		{
			header("Location: leave_form.php?info=update");
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

<?php

	include_once("includes/db_mysql.php");
	include_once("common.php");

	//ini_set('display_errors','On');
	//error_reporting(E_ALL);

	set_time_limit(0);

	$db = new DB_Sql();

	include_once("includes/class.employee.php");

	$objEmployee = new employee();

	/* connect to gmail */
	$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
	$username = 'transform.pms@gmail.com';
	$password = 'Transform@123';

	/* try to connect */
	$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());

	/* grab emails */
	//$date = date("d-M-Y");
	//$date = "03-Apr-2013";
	$date = date('Y-m-d', strtotime('-1 day'));
	//$emails = imap_search($inbox,"ON '".$date."'");
	$emails = imap_search($inbox,'SINCE '.$date);
	//echo "--".$emails = imap_search($inbox,'ON '.$date);

	/* if emails are returned, cycle through each... */
	if($emails) {

		/* begin output var */
		$output = '';

		/* put the newest emails on top */
		rsort($emails);

		/* for every email... */
		foreach($emails as $email_number) {

			/* get information specific to this email */
			$overview = imap_fetch_overview($inbox,$email_number,0);
			/*$message = imap_fetchbody($inbox,$email_number,2);*/

			if(isset($overview[0]->subject) && trim($overview[0]->subject) != "")
			{
				$arrSubject = explode("-",str_replace("+","",$overview[0]->subject));

				if(isset($arrSubject[1]))
				{
					$curSubject = $arrSubject[1];

					$code = str_replace("]","",str_replace("[","",$curSubject));
					$arrCode = explode("_",$code);

					if(count($arrCode) == 3)
					{
						$sSQL = "";
						$uniqCode = trim($arrCode[0]);

						$approvalStatus = 0;
						if($arrCode[1] == "Approve" || $arrCode[1] == "Terminate" )
							$approvalStatus = 1;
						else if($arrCode[1] == "Reject" || $arrCode[1] == "Hold")
							$approvalStatus = 2;

						if($arrCode[2] == "L" || $arrCode[2] == "EL")
						{
							//echo 'hello'; 
							$status = array("0"=>"Pending", "1"=>"Approved", "2"=>"Unapproved");
							/* Leave approval / rejection by mail */
							$sSQL = "select *,DATE_FORMAT(`start_date`,'%d-%m-%Y') as startdate,DATE_FORMAT(`end_date`,'%d-%m-%Y') as enddate,DATE_FORMAT(`end_date`,'%d-%m-%Y') as approvedate from pms_leave_form where (tlapprovalcode='".mysql_real_escape_string($uniqCode)."' or managerapprovalcode='".mysql_real_escape_string($uniqCode)."' or delegatedtlapprovalcode='".mysql_real_escape_string($uniqCode)."' or delegatedmanagerapprovalcode='".mysql_real_escape_string($uniqCode)."') and isactive = '0'";
							
							$db->query($sSQL);
							if($db->num_rows() > 0)
							{
								if($db->next_record())
								{
									if($approvalStatus != 0)
									{
										/* Team leader click on approval/rejection link */
										if($db->f("tlapprovalcode") == $uniqCode)
										{
											//print_r($arrApprovalInfo); 
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["status"] = $approvalStatus;
											$arrApprovalInfo["approved_date"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["tlapprovalcode"] = $db->f("tlapprovalcode")."_used";

											$employeeId = $db->f("employee_id");
											$teamleaderId = $db->f("teamleader_id");
											$managerId = $db->f("manager_id");
											$startdate = $db->f("startdate");
											$enddate =  $db->f("enddate");
											$tlapprovalDate = $db->f("approvedate");
											$date = date('d-m-y');
											
											$deligateTeamLeaderId = $db->f("deligateTeamLeaderId");
											$deligateManagerId = $db->f("deligateManagerId");

											/* delegated team leader details */
											if($deligateTeamLeaderId != '' && $deligateTeamLeaderId != '0')
											{
												$deligatedTeamLeaderInfo = $objEmployee->fnGetEmployeeById($deligateTeamLeaderId);
											}

											/* delegated team manager details */
											if($deligateManagerId != '' && $deligateManagerId != '0')
											{
												$deligateManagerInfo = $objEmployee->fnGetEmployeeById($deligateManagerId);
											}

											
											/* Employee info that apply for leave */
											$EmployeeInfo = $objEmployee->fnGetEmployeeById($employeeId);

											/* Team Leader Info */
											$TeamleaderInfo = $objEmployee->fnGetEmployeeById($teamleaderId);

											/* Manager Info */
											$ManagerInfo = $objEmployee->fnGetEmployeeById($managerId);

											//echo '<pre>'; print_r($EmployeeInfo);

											if($EmployeeInfo['designation'] == '5' || $EmployeeInfo['designation'] == '9' || $EmployeeInfo['designation'] == '10' || $EmployeeInfo['designation'] == '11' || $EmployeeInfo['designation'] == '12' || $EmployeeInfo['designation'] == '14' || $EmployeeInfo['designation'] == '15' || $EmployeeInfo['designation'] == '16' || $EmployeeInfo['designation'] == '20' ||  $EmployeeInfo['designation'] == '21' || $EmployeeInfo['designation'] == '22' || $EmployeeInfo['designation'] == '23' || $EmployeeInfo['designation'] == '24' || $EmployeeInfo['designation'] == '25' || $EmployeeInfo['designation'] == '26' || $EmployeeInfo['designation'] == '27' || $EmployeeInfo['designation'] == '28')
											{
												/* send mail to applyer */
												$MailTo = $EmployeeInfo["email"];
												$Subject = "Leave request ".$status[$arrApprovalInfo["status"]];

												$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
												$content .= "Your application for Leave/s has been ".$status[$arrApprovalInfo["status"]]." by ".$TeamleaderInfo["name"]." on ".$date." . ";

												if($arrApprovalInfo["status"] == '1')
												{
													$content .= "As approved, you can avail these leaves from ".$startdate." to ".$enddate.".";
												}
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);

												/* send mail to manager*/
												$MailTo = $ManagerInfo["email"];
												$Subject = "Leave request ".$status[$arrApprovalInfo["status"]];

												$content = "Dear ".$ManagerInfo["name"].",<br><br>";
												$content .= $TeamleaderInfo['name']." has ".$status[$arrApprovalInfo["status"]]." leave request of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

												
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello1'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);

												/* send mail to delegated teamleader */
												if($deligateTeamLeaderId != '' && $deligateTeamLeaderId != '0')
												{
													$MailTo = $deligateTeamLeaderId["email"];
													$Subject = "Leave request ".$status[$arrApprovalInfo["status"]];

													$content = "Dear ".$deligateTeamLeaderId["name"].",<br><br>";
													$content .= $TeamleaderInfo['name']." has ".$status[$arrApprovalInfo["status"]]." leave request of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

													
													$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
													//echo 'hello1'.$content.'<br>';

													sendmail($MailTo, $Subject, $content);
												}

												/* send mail to delegated manager*/
												if($deligateManagerId != '' && $deligateManagerId != '0')
												{
													$MailTo = $deligateManagerInfo["email"];
													$Subject = "Leave request ".$status[$arrApprovalInfo["status"]];

													$content = "Dear ".$deligateManagerInfo["name"].",<br><br>";
													$content .= $TeamleaderInfo['name']." has ".$status[$arrApprovalInfo["status"]]." leave request of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

													
													$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
													//echo 'hello1'.$content.'<br>';

													sendmail($MailTo, $Subject, $content);
												}
												
											}
											//print_r($ManagerInfo);

											$db->updateArray("pms_leave_form",$arrApprovalInfo);
											//die;
										}
										/* Manager click on approval/rejection link  and teamleader status either approve or pending */
										else if($db->f("managerapprovalcode") == $uniqCode && $db->f("status") != 2)
										{
											//echo 'hello'; die;
											/* For manager approval if unapproved by team leader, do not consider the action  */
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["status_manager"] = $approvalStatus;
											$arrApprovalInfo["approved_date_manager"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["managerapprovalcode"] = $db->f("managerapprovalcode")."_used";

											//print_r($arrApprovalInfo);

											$employeeId = $db->f("employee_id");
											$teamleaderId = $db->f("teamleader_id");
											$managerId = $db->f("manager_id");
											$startdate = $db->f("startdate");
											$enddate =  $db->f("enddate");
											$tlapprovalDate = $db->f("approvedate");
											$date = date('d-m-y');
											
											$deligateTeamLeaderId = $db->f("deligateTeamLeaderId");
											$deligateManagerId = $db->f("deligateManagerId");

											/* delegated team leader details */
											if($deligateTeamLeaderId != '' && $deligateTeamLeaderId != '0')
											{
												$deligatedTeamLeaderInfo = $objEmployee->fnGetEmployeeById($deligateTeamLeaderId);
											}

											/* delegated team manager details */
											if($deligateManagerId != '' && $deligateManagerId != '0')
											{
												$deligateManagerInfo = $objEmployee->fnGetEmployeeById($deligateManagerId);
											}

											
											/* Employee info that apply for leave */
											$EmployeeInfo = $objEmployee->fnGetEmployeeById($employeeId);
											

											/* Team Leader Info */
											$TeamleaderInfo = $objEmployee->fnGetEmployeeById($teamleaderId);

											/* Manager Info */
											$ManagerInfo = $objEmployee->fnGetEmployeeById($managerId);

											/* Manager leave for approval */
											if($EmployeeInfo['designation'] == '6' || $EmployeeInfo['designation'] == '18' || $EmployeeInfo['designation'] == '19')
											{
												/* send mail to applyer */
												$MailTo = $EmployeeInfo["email"];
												$Subject = "Leave request ".$status[$arrApprovalInfo["status_manager"]];

												$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
												$content .= "Your application for Leave/s has been ".$status[$arrApprovalInfo["status_manager"]]." by ".$ManagerInfo["name"]." on ".$date." . ";

												if($arrApprovalInfo["status_manager"] == '1')
												{
													$content .= "As approved, you can avail these leaves from ".$startdate." to ".$enddate.".";
												}
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);
												
											}
											
											/* Teamleader leave for approval */
											if($EmployeeInfo['designation'] == '7' || $EmployeeInfo['designation'] == '13')
											{
												/* send mail to applyer */
												$MailTo = $EmployeeInfo["email"];
												$Subject = "Leave request ".$status[$arrApprovalInfo["status_manager"]];

												$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
												$content .= "Your application for Leave/s has been ".$status[$arrApprovalInfo["status_manager"]]." by ".$TeamleaderInfo["name"]." on ".$date." . ";

												if($arrApprovalInfo["status_manager"] == '1')
												{
													$content .= "As approved, you can avail these leaves from ".$startdate." to ".$enddate.".";
												}
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);

												
												/* send mail to delegated manager*/
												if($deligateManagerId != '' && $deligateManagerId != '0')
												{
													$MailTo = $deligateManagerInfo["email"];
													$Subject = "Leave request ".$status[$arrApprovalInfo["status_manager"]];

													$content = "Dear ".$deligateManagerInfo["name"].",<br><br>";
													$content .= $ManagerInfo['name']." has ".$status[$arrApprovalInfo["status_manager"]]." leave request of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

													
													$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
													//echo 'hello1'.$content.'<br>';

													sendmail($MailTo, $Subject, $content);
												}
												
											}
											//die;
											/* Agent leave for approval that have teamleader and manager both */
											if($EmployeeInfo['designation'] == '5' || $EmployeeInfo['designation'] == '9' || $EmployeeInfo['designation'] == '10' || $EmployeeInfo['designation'] == '14' || $EmployeeInfo['designation'] == '15' || $EmployeeInfo['designation'] == '16')
											{
												/* send mail to applyer */
												$MailTo = $EmployeeInfo["email"];
												$Subject = "Leave request ".$status[$arrApprovalInfo["status_manager"]];

												$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
												$content .= "Your application for Leave/s has been ".$status[$arrApprovalInfo["status_manager"]]." by ".$ManagerInfo["name"]." on ".$date." . ";

												if($arrApprovalInfo["status_manager"] == '1')
												{
													$content .= "As approved, you can avail these leaves from ".$startdate." to ".$enddate.".";
												}
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);

												/* send mail to Teamleader*/
												$MailTo = $TeamleaderInfo["email"];
												$Subject = "Leave request ".$status[$arrApprovalInfo["status_manager"]];

												$content = "Dear ".$TeamleaderInfo["name"].",<br><br>";
												$content .= $ManagerInfo['name']." has ".$status[$arrApprovalInfo["status_manager"]]." leave request of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

												
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello1'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);

												/* send mail to delegated teamleader */
												if($deligateTeamLeaderId != '' && $deligateTeamLeaderId != '0')
												{
													$MailTo = $deligateTeamLeaderId["email"];
													$Subject = "Leave request ".$status[$arrApprovalInfo["status_manager"]];

													$content = "Dear ".$deligateTeamLeaderId["name"].",<br><br>";
													$content .= $ManagerInfo['name']." has ".$status[$arrApprovalInfo["status_manager"]]." leave request of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

													
													$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
													//echo 'hello1'.$content.'<br>';

													sendmail($MailTo, $Subject, $content);
												}

												/* send mail to delegated manager*/
												if($deligateManagerId != '' && $deligateManagerId != '0')
												{
													$MailTo = $deligateManagerInfo["email"];
													$Subject = "Leave request ".$status[$arrApprovalInfo["status_manager"]];

													$content = "Dear ".$deligateManagerInfo["name"].",<br><br>";
													$content .= $ManagerInfo['name']." has ".$status[$arrApprovalInfo["status_manager"]]." leave request of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

													
													$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
													//echo 'hello1'.$content.'<br>';

													sendmail($MailTo, $Subject, $content);
												}
												
											}
											/* employee that have only managers */
											else if($EmployeeInfo['designation'] == '11' || $EmployeeInfo['designation'] == '12' || $EmployeeInfo['designation'] == '20' || $EmployeeInfo['designation'] == '21' || $EmployeeInfo['designation'] == '22' || $EmployeeInfo['designation'] == '23' || $EmployeeInfo['designation'] == '24' || $EmployeeInfo['designation'] == '25' || $EmployeeInfo['designation'] == '26' || $EmployeeInfo['designation'] == '27' || $EmployeeInfo['designation'] == '28')
											{
												//echo 'hello'; die;
												/* send mail to applyer */
												$MailTo = $EmployeeInfo["email"];
												$Subject = "Leave request ".$status[$arrApprovalInfo["status_manager"]];

												$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
												$content .= "Your application for Leave/s has been ".$status[$arrApprovalInfo["status_manager"]]." by ".$TeamleaderInfo["name"]." on ".$date." . ";

												if($arrApprovalInfo["status_manager"] == '1')
												{
													$content .= "As approved, you can avail these leaves from ".$startdate." to ".$enddate.".";
												}
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);

												
												/* send mail to delegated manager*/
												if($deligateManagerId != '' && $deligateManagerId != '0')
												{
													$MailTo = $deligateManagerInfo["email"];
													$Subject = "Leave request ".$status[$arrApprovalInfo["status_manager"]];

													$content = "Dear ".$deligateManagerInfo["name"].",<br><br>";
													$content .= $TeamleaderInfo['name']." has ".$status[$arrApprovalInfo["status_manager"]]." leave request of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

													
													$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
													//echo 'hello1'.$content.'<br>';

													sendmail($MailTo, $Subject, $content);
												}
												
											}

											include_once("includes/class.leave.php");
											include_once("includes/class.attendance.php");

											$objAttendance = new attendance();
											$objLeave = new leave();

											$LeaveInfo = $objLeave->fnLeaveInfoById($arrApprovalInfo["id"]);
											
											$next_monday_date = date('Y-m-d', strtotime('next monday'));
											
											
											//echo '<pre>'; print_r($LeaveInfo); print_r($arrApprovalInfo);
											if($LeaveInfo["isemergency"] == "1")
											{
												//echo 'hello<pre>'; print_r($LeaveInfo);
												if($arrApprovalInfo["status_manager"] == "1" || ($arrApprovalInfo["status_manager"] == "0" && $arrApprovalInfo["manager_delegate_status"] == "1"))
												{
													/* Approve */
													$arrInfo["user_id"] = $LeaveInfo["employee_id"];
													$arrInfo["date"] = $LeaveInfo["start_dt"];
													$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("UPL");
													$objAttendance->fnInsertRosterAttendance($arrInfo);
												}
												else
												{
													/* Un approve */
													$arrInfo["user_id"] = $LeaveInfo["employee_id"];
													$arrInfo["date"] = $LeaveInfo["start_dt"];
													$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("");
													
													$objAttendance->fnInsertRosterAttendance($arrInfo);
												}
											}
											else
											{
												//echo 'hello';
												if($arrApprovalInfo["status_manager"] == "1")
												{
													/* Approve */
													$arrInfo["user_id"] = $LeaveInfo["employee_id"];
													$arrInfo["start_dt"] = $LeaveInfo["start_dt"];
													if($arrInfo["start_dt"] >= $next_monday_date)
													{
														$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("PPL");
													}
													else
													{
														$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("UPL");
													}
													//echo $arrInfo["end_dt"] = $LeaveInfo["end_date"];
													//echo 'gagan<pre>'; print_r($arrInfo);
													$objAttendance->fnInsertRosterAttendance($arrInfo);
												}
												else
												{
													/* Un approve */
													$arrInfo["user_id"] = $LeaveInfo["employee_id"];
													$arrInfo["date"] = $LeaveInfo["start_dt"];
													$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("");
													$objAttendance->fnInsertRosterAttendance($arrInfo);
												}
											}
//die;
											$db->updateArray("pms_leave_form",$arrApprovalInfo);
										}
										else if($db->f("delegatedtlapprovalcode") == $uniqCode && $db->f("status") != 2)
										{
											/* For teamleader approval if unapproved by team leader, do not consider the action  */
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["delegate_status"] = $approvalStatus;
											$arrApprovalInfo["delegate_date"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["delegatedtlapprovalcode"] = $db->f("delegatedtlapprovalcode")."_used";

											$employeeId = $db->f("employee_id");
											$teamleaderId = $db->f("teamleader_id");
											$managerId = $db->f("manager_id");
											$startdate = $db->f("startdate");
											$enddate =  $db->f("enddate");
											$tlapprovalDate = $db->f("approvedate");
											$date = date('d-m-y');

											/* delegated team leader details */
											if($deligateTeamLeaderId != '' && $deligateTeamLeaderId != '0')
											{
												$deligatedTeamLeaderInfo = $objEmployee->fnGetEmployeeById($deligateTeamLeaderId);
											}
											

											/* delegated team manager details */
											if($deligateManagerId != '' && $deligateManagerId != '0')
											{
												$deligateManagerInfo = $objEmployee->fnGetEmployeeById($deligateManagerId);
											}

											
											/* Employee info that apply for leave */
											$EmployeeInfo = $objEmployee->fnGetEmployeeById($employeeId);

											/* Team Leader Info */
											$TeamleaderInfo = $objEmployee->fnGetEmployeeById($teamleaderId);

											/* Manager Info */
											$ManagerInfo = $objEmployee->fnGetEmployeeById($managerId);

											//echo '<pre>'; print_r($EmployeeInfo);

											if($EmployeeInfo['designation'] == '5' || $EmployeeInfo['designation'] == '9' || $EmployeeInfo['designation'] == '10' || $EmployeeInfo['designation'] == '11' || $EmployeeInfo['designation'] == '12' || $EmployeeInfo['designation'] == '14' || $EmployeeInfo['designation'] == '15' || $EmployeeInfo['designation'] == '16' || $EmployeeInfo['designation'] == '20' ||  $EmployeeInfo['designation'] == '21' || $EmployeeInfo['designation'] == '22' || $EmployeeInfo['designation'] == '23' || $EmployeeInfo['designation'] == '24' || $EmployeeInfo['designation'] == '25' || $EmployeeInfo['designation'] == '26' || $EmployeeInfo['designation'] == '27' || $EmployeeInfo['designation'] == '28')
											{
												/* send mail to applyer */
												$MailTo = $EmployeeInfo["email"];
												$Subject = "Leave request ".$status[$arrApprovalInfo["delegate_status"]];

												$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
												$content .= "Your application for Leave/s has been ".$status[$arrApprovalInfo["delegate_status"]]." by ".$deligatedTeamLeaderInfo["name"]." on ".$date." . ";

												if($arrApprovalInfo["status"] == '1')
												{
													$content .= "As approved, you can avail these leaves from ".$startdate." to ".$enddate.".";
												}
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);

												/* send mail to manager*/
												$MailTo = $ManagerInfo["email"];
												$Subject = "Leave request ".$status[$arrApprovalInfo["delegate_status"]];

												$content = "Dear ".$ManagerInfo["name"].",<br><br>";
												$content .= $deligatedTeamLeaderInfo['name']." has ".$status[$arrApprovalInfo["delegate_status"]]." leave request of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

												
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello1'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);

												/* send mail to teamleader */
												if($deligateTeamLeaderId != '' && $deligateTeamLeaderId != '0')
												{
													$MailTo = $TeamleaderInfo["email"];
													$Subject = "Leave request ".$status[$arrApprovalInfo["delegate_status"]];

													$content = "Dear ".$TeamleaderInfo["name"].",<br><br>";
													$content .= $deligatedTeamLeaderInfo['name']." has ".$status[$arrApprovalInfo["delegate_status"]]." leave request of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

													
													$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
													//echo 'hello1'.$content.'<br>';

													sendmail($MailTo, $Subject, $content);
												}

												/* send mail to delegated manager*/
												if($deligateManagerId != '' && $deligateManagerId != '0')
												{
													$MailTo = $deligateManagerInfo["email"];
													$Subject = "Leave request ".$status[$arrApprovalInfo["delegate_status"]];

													$content = "Dear ".$deligateManagerInfo["name"].",<br><br>";
													$content .= $deligatedTeamLeaderInfo['name']." has ".$status[$arrApprovalInfo["delegate_status"]]." leave request of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

													
													$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
													//echo 'hello1'.$content.'<br>';

													sendmail($MailTo, $Subject, $content);
												}
												
											}

											//print_r($arrApprovalInfo);

											$db->updateArray("pms_leave_form",$arrApprovalInfo);
										}
										else if($db->f("delegatedmanagerapprovalcode") == $uniqCode && $db->f("status") != 2)
										{
											/* For manager approval if unapproved by team leader, do not consider the action  */
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["manager_delegate_status"] = $approvalStatus;
											$arrApprovalInfo["manager_delegate_date"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["delegatedmanagerapprovalcode"] = $db->f("delegatedmanagerapprovalcode")."_used";

											//print_r($arrApprovalInfo);
											
											$employeeId = $db->f("employee_id");
											$teamleaderId = $db->f("teamleader_id");
											$managerId = $db->f("manager_id");
											$startdate = $db->f("startdate");
											$enddate =  $db->f("enddate");
											$tlapprovalDate = $db->f("approvedate");
											$date = date('d-m-y');
											
											$deligateTeamLeaderId = $db->f("deligateTeamLeaderId");
											$deligateManagerId = $db->f("deligateManagerId");

											/* delegated team leader details */
											if($deligateTeamLeaderId != '' && $deligateTeamLeaderId != '0')
											{
												$deligatedTeamLeaderInfo = $objEmployee->fnGetEmployeeById($deligateTeamLeaderId);
											}

											/* delegated team manager details */
											if($deligateManagerId != '' && $deligateManagerId != '0')
											{
												$deligateManagerInfo = $objEmployee->fnGetEmployeeById($deligateManagerId);
											}

											
											/* Employee info that apply for leave */
											$EmployeeInfo = $objEmployee->fnGetEmployeeById($employeeId);
											

											/* Team Leader Info */
											$TeamleaderInfo = $objEmployee->fnGetEmployeeById($teamleaderId);

											/* Manager Info */
											$ManagerInfo = $objEmployee->fnGetEmployeeById($managerId);

											/* Teamleader leave for approval */
											if($EmployeeInfo['designation'] == '7' || $EmployeeInfo['designation'] == '13')
											{
												/* send mail to applyer */
												$MailTo = $EmployeeInfo["email"];
												$Subject = "Leave request ".$status[$arrApprovalInfo["manager_delegate_status"]];

												$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
												$content .= "Your application for Leave/s has been ".$status[$arrApprovalInfo["manager_delegate_status"]]." by ".$TeamleaderInfo["name"]." on ".$date." . ";

												if($arrApprovalInfo["manager_delegate_status"] == '1')
												{
													$content .= "As approved, you can avail these leaves from ".$startdate." to ".$enddate.".";
												}
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);

												
												/* send mail to manager*/
												
												$MailTo = $ManagerInfo["email"];
												$Subject = "Leave request ".$status[$arrApprovalInfo["manager_delegate_status"]];

												$content = "Dear ".$ManagerInfo["name"].",<br><br>";
												$content .= $deligateManagerInfo['name']." has ".$status[$arrApprovalInfo["manager_delegate_status"]]." leave request of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

												
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello1'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);
												
												
											}
											//die;
											/* Agent leave for approval that have teamleader and manager both */
											if($EmployeeInfo['designation'] == '5' || $EmployeeInfo['designation'] == '9' || $EmployeeInfo['designation'] == '10' || $EmployeeInfo['designation'] == '14' || $EmployeeInfo['designation'] == '15' || $EmployeeInfo['designation'] == '16')
											{
												/* send mail to applyer */
												$MailTo = $EmployeeInfo["email"];
												$Subject = "Leave request ".$status[$arrApprovalInfo["manager_delegate_status"]];

												$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
												$content .= "Your application for Leave/s has been ".$status[$arrApprovalInfo["manager_delegate_status"]]." by ".$deligateManagerInfo["name"]." on ".$date." . ";

												if($arrApprovalInfo["manager_delegate_status"] == '1')
												{
													$content .= "As approved, you can avail these leaves from ".$startdate." to ".$enddate.".";
												}
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);

												/* send mail to Teamleader*/
												$MailTo = $TeamleaderInfo["email"];
												$Subject = "Leave request ".$status[$arrApprovalInfo["manager_delegate_status"]];

												$content = "Dear ".$TeamleaderInfo["name"].",<br><br>";
												$content .= $deligateManagerInfo['name']." has ".$status[$arrApprovalInfo["manager_delegate_status"]]." leave request of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

												
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello1'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);

												/* send mail to delegated teamleader */
												if($deligateTeamLeaderId != '' && $deligateTeamLeaderId != '0')
												{
													$MailTo = $deligateTeamLeaderId["email"];
													$Subject = "Leave request ".$status[$arrApprovalInfo["manager_delegate_status"]];

													$content = "Dear ".$deligateTeamLeaderId["name"].",<br><br>";
													$content .= $deligateManagerInfo['name']." has ".$status[$arrApprovalInfo["manager_delegate_status"]]." leave request of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

													
													$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
													//echo 'hello1'.$content.'<br>';

													sendmail($MailTo, $Subject, $content);
												}

												/* send mail manager*/
												$MailTo = $ManagerInfo["email"];
												$Subject = "Leave request ".$status[$arrApprovalInfo["manager_delegate_status"]];

												$content = "Dear ".$ManagerInfo["name"].",<br><br>";
												$content .= $deligateManagerInfo['name']." has ".$status[$arrApprovalInfo["manager_delegate_status"]]." leave request of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

												
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello1'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);
											}
											/* employee that have only managers */
											else if($EmployeeInfo['designation'] == '11' || $EmployeeInfo['designation'] == '12' || $EmployeeInfo['designation'] == '20' || $EmployeeInfo['designation'] == '21' || $EmployeeInfo['designation'] == '22' || $EmployeeInfo['designation'] == '23' || $EmployeeInfo['designation'] == '24' || $EmployeeInfo['designation'] == '25' || $EmployeeInfo['designation'] == '26' || $EmployeeInfo['designation'] == '27' || $EmployeeInfo['designation'] == '28')
											{
												//echo 'hello'; die;
												/* send mail to applyer */
												$MailTo = $EmployeeInfo["email"];
												$Subject = "Leave request ".$status[$arrApprovalInfo["manager_delegate_status"]];

												$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
												$content .= "Your application for Leave/s has been ".$status[$arrApprovalInfo["manager_delegate_status"]]." by ".$deligateTeamLeaderInfo["name"]." on ".$date." . ";

												if($arrApprovalInfo["manager_delegate_status"] == '1')
												{
													$content .= "As approved, you can avail these leaves from ".$startdate." to ".$enddate.".";
												}
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);

												
												/* send mail to manager*/
												$MailTo = $ManagerInfo["email"];
												$Subject = "Leave request ".$status[$arrApprovalInfo["manager_delegate_status"]];

												$content = "Dear ".$ManagerInfo["name"].",<br><br>";
												$content .= $deligateManagerInfo['name']." has ".$status[$arrApprovalInfo["manager_delegate_status"]]." leave request of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

												
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello1'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);
												
											}

											include_once("includes/class.leave.php");
											include_once("includes/class.attendance.php");

											$objAttendance = new attendance();
											$objLeave = new leave();

											$LeaveInfo = $objLeave->fnLeaveInfoById($arrApprovalInfo["id"]);
											
											$next_monday_date = date('Y-m-d', strtotime('next monday'));
											
											
											//echo '<pre>'; print_r($LeaveInfo); print_r($arrApprovalInfo);
											if($LeaveInfo["isemergency"] == "1")
											{
												//echo 'hello<pre>'; print_r($LeaveInfo);
												if($arrApprovalInfo["manager_delegate_status"] == "1" && $LeaveInfo["status_manager"] == "0")
												{
													/* Approve */
													$arrInfo["user_id"] = $LeaveInfo["employee_id"];
													$arrInfo["date"] = $LeaveInfo["start_dt"];
													$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("UPL");
													$objAttendance->fnInsertRosterAttendance($arrInfo);
												}
												else
												{
													/* Un approve */
													$arrInfo["user_id"] = $LeaveInfo["employee_id"];
													$arrInfo["date"] = $LeaveInfo["start_dt"];
													$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("");
													
													$objAttendance->fnInsertRosterAttendance($arrInfo);
												}
											}
											else
											{
												//echo 'hello';
												if($arrApprovalInfo["manager_delegate_status"] == "1" && $LeaveInfo["status_manager"] == "0")
												{
													/* Approve */
													$arrInfo["user_id"] = $LeaveInfo["employee_id"];
													$arrInfo["start_dt"] = $LeaveInfo["start_dt"];
													if($arrInfo["start_dt"] >= $next_monday_date)
													{
														$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("PPL");
													}
													else
													{
														$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("UPL");
													}
													//echo $arrInfo["end_dt"] = $LeaveInfo["end_date"];
													//echo 'gagan<pre>'; print_r($arrInfo);
													$objAttendance->fnInsertRosterAttendance($arrInfo);
												}
												else
												{
													/* Un approve */
													$arrInfo["user_id"] = $LeaveInfo["employee_id"];
													$arrInfo["date"] = $LeaveInfo["start_dt"];
													$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("");
													$objAttendance->fnInsertRosterAttendance($arrInfo);
												}
											}

											$db->updateArray("pms_leave_form",$arrApprovalInfo);
										}
									}
								}
							}
						}
						else if($arrCode[2] == "HL")
						{
							$date = date('%d-%m-%Y');
							$status = array("0"=>"Pending", "1"=>"Approved", "2"=>"Unapproved");
							/* Half day Leave approval / rejection by mail */
							$sSQL = "select *,DATE_FORMAT(`start_date`,'%d-%m-%Y') as startdate,DATE_FORMAT(`start_date`,'%d-%m-%Y') as enddate from pms_half_leave_form where (tlapprovalcode='".mysql_real_escape_string($uniqCode)."' or managerapprovalcode='".mysql_real_escape_string($uniqCode)."') and isactive = '0'";
							$db->query($sSQL);
							if($db->num_rows() > 0)
							{
								if($db->next_record())
								{
									if($approvalStatus != 0)
									{
										if($db->f("tlapprovalcode") == $uniqCode)
										{
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["status"] = $approvalStatus;
											$arrApprovalInfo["approved_date"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["tlapprovalcode"] = $db->f("tlapprovalcode")."_used";

											//print_r($arrApprovalInfo);


											$employeeId = $db->f("employee_id");
											$teamleaderId = $db->f("teamleader_id");
											$managerId = $db->f("manager_id");
											$startdate = $db->f("startdate");
											$enddate =  $db->f("startdate");
											$tlapprovalDate = $db->f("approvedate");
											$date = date('d-m-y');
									
												
											/* Employee info that apply for leave */
											$EmployeeInfo = $objEmployee->fnGetEmployeeById($employeeId);

											/* Team Leader Info */
											$TeamleaderInfo = $objEmployee->fnGetEmployeeById($teamleaderId);

											/* Manager Info */
											$ManagerInfo = $objEmployee->fnGetEmployeeById($managerId);

											//echo '<pre>'; print_r($EmployeeInfo);

											if($EmployeeInfo['designation'] == '5' || $EmployeeInfo['designation'] == '9' || $EmployeeInfo['designation'] == '10' || $EmployeeInfo['designation'] == '11' || $EmployeeInfo['designation'] == '12' || $EmployeeInfo['designation'] == '14' || $EmployeeInfo['designation'] == '15' || $EmployeeInfo['designation'] == '16' || $EmployeeInfo['designation'] == '20' ||  $EmployeeInfo['designation'] == '21' || $EmployeeInfo['designation'] == '22' || $EmployeeInfo['designation'] == '23' || $EmployeeInfo['designation'] == '24' || $EmployeeInfo['designation'] == '25' || $EmployeeInfo['designation'] == '26' || $EmployeeInfo['designation'] == '27' || $EmployeeInfo['designation'] == '28')
											{
												/* send mail to applyer */
												$MailTo = $EmployeeInfo["email"];
												$Subject = "Half Leave request ".$status[$arrApprovalInfo["status"]];

												$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
												$content .= "Your application for half Leave has been ".$status[$arrApprovalInfo["status"]]." by ".$TeamleaderInfo["name"]." on ".$date." . ";

												if($arrApprovalInfo["status"] == '1')
												{
													$content .= "As approved, you can avail these leaves from ".$startdate." to ".$enddate.".";
												}
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);

												/* send mail to manager*/
												$MailTo = $ManagerInfo["email"];
												$Subject = "Half Leave request ".$status[$arrApprovalInfo["status"]];

												$content = "Dear ".$ManagerInfo["name"].",<br><br>";
												$content .= $TeamleaderInfo['name']." has ".$status[$arrApprovalInfo["status"]]." half leave of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

												
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello1'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);
												
											}

											

											$db->updateArray("pms_half_leave_form",$arrApprovalInfo);
										}
										
										else if($db->f("managerapprovalcode") == $uniqCode && $db->f("status") != 2)
										{
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["status_manager"] = $approvalStatus;
											$arrApprovalInfo["approved_date_manager"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["managerapprovalcode"] = $db->f("managerapprovalcode")."_used";

											//print_r($arrApprovalInfo);

											$employeeId = $db->f("employee_id");
											$teamleaderId = $db->f("teamleader_id");
											$managerId = $db->f("manager_id");
											$startdate = $db->f("startdate");
											$enddate =  $db->f("startdate");
											$tlapprovalDate = $db->f("approvedate");
											$date = date('d-m-y');
											
											/* Employee info that apply for leave */
											$EmployeeInfo = $objEmployee->fnGetEmployeeById($employeeId);
											

											/* Team Leader Info */
											$TeamleaderInfo = $objEmployee->fnGetEmployeeById($EmployeeInfo['teamleader_id']);

											/* Manager Info */
											$ManagerInfo = $objEmployee->fnGetEmployeeById($TeamleaderInfo['teamleader_id']);

											/* Manager leave for approval */
											if($EmployeeInfo['designation'] == '6' || $EmployeeInfo['designation'] == '18' || $EmployeeInfo['designation'] == '19')
											{
												/* send mail to applyer */
												$MailTo = $EmployeeInfo["email"];
												$Subject = "Half Leave request ".$status[$arrApprovalInfo["status_manager"]];

												$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
												$content .= "Your application for half Leave has been ".$status[$arrApprovalInfo["status_manager"]]." by ".$TeamleaderInfo["name"]." on ".$date." . ";

												if($arrApprovalInfo["status_manager"] == '1')
												{
													$content .= "As approved, you can avail these leaves from ".$startdate." to ".$enddate.".";
												}
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);
												
											}
											
											/* Teamleader leave for approval */
											if($EmployeeInfo['designation'] == '7' || $EmployeeInfo['designation'] == '13')
											{
												/* send mail to applyer */
												$MailTo = $EmployeeInfo["email"];
												$Subject = "Half Leave request ".$status[$arrApprovalInfo["status_manager"]];

												$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
												$content .= "Your application for half Leave has been ".$status[$arrApprovalInfo["status_manager"]]." by ".$TeamleaderInfo["name"]." on ".$date." . ";

												if($arrApprovalInfo["status_manager"] == '1')
												{
													$content .= "As approved, you can avail these leaves from ".$startdate." to ".$enddate.".";
												}
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);
												
											}
											
											/* Agent leave for approval that have teamleader and manager both */
											if($EmployeeInfo['designation'] == '5' || $EmployeeInfo['designation'] == '9' || $EmployeeInfo['designation'] == '10' || $EmployeeInfo['designation'] == '14' || $EmployeeInfo['designation'] == '15' || $EmployeeInfo['designation'] == '16')
											{
												/* send mail to applyer */
												$MailTo = $EmployeeInfo["email"];
												$Subject = "Half Leave request ".$status[$arrApprovalInfo["status_manager"]];

												$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
												$content .= "Your application for half Leave has been ".$status[$arrApprovalInfo["status_manager"]]." by ".$ManagerInfo["name"]." on ".$date." . ";

												if($arrApprovalInfo["status_manager"] == '1')
												{
													$content .= "As approved, you can avail these leaves from ".$startdate." to ".$enddate.".";
												}
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);

												/* send mail to Teamleader*/
												$MailTo = $TeamleaderInfo["email"];
												$Subject = "Half Leave request ".$status[$arrApprovalInfo["status_manager"]];

												$content = "Dear ".$TeamleaderInfo["name"].",<br><br>";
												$content .= $ManagerInfo['name']." has ".$status[$arrApprovalInfo["status_manager"]]." half leave request of ".$EmployeeInfo['name']." on ".$date." from ".$startdate." to ".$enddate.".";

												
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello1'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);

											}
											/* employee that have only managers */
											else if($EmployeeInfo['designation'] == '11' || $EmployeeInfo['designation'] == '12' || $EmployeeInfo['designation'] == '20' || $EmployeeInfo['designation'] == '21' || $EmployeeInfo['designation'] == '22' || $EmployeeInfo['designation'] == '23' || $EmployeeInfo['designation'] == '24' || $EmployeeInfo['designation'] == '25' || $EmployeeInfo['designation'] == '26' || $EmployeeInfo['designation'] == '27' || $EmployeeInfo['designation'] == '28')
											{
												//echo 'hello'; die;
												/* send mail to applyer */
												$MailTo = $EmployeeInfo["email"];
												$Subject = "Half Leave request ".$status[$arrApprovalInfo["status_manager"]];

												$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
												$content .= "Your application for half Leave has been ".$status[$arrApprovalInfo["status_manager"]]." by ".$TeamleaderInfo["name"]." on ".$date." . ";

												if($arrApprovalInfo["status_manager"] == '1')
												{
													$content .= "As approved, you can avail these leaves from ".$startdate." to ".$enddate.".";
												}
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
												//echo 'hello'.$content.'<br>';

												sendmail($MailTo, $Subject, $content);
												
											}

												include_once('includes/class.attendance.php');
												include_once('includes/class.leave.php');

												$objAttendance = new attendance();
												$objLeave = new leave();
//echo '<pre>'; print_r($arrApprovalInfo);
												$LeaveInfo = $objLeave->fnHalfLeaveInfoById($arrApprovalInfo["id"]);
												$next_monday_date = date('Y-m-d', strtotime('next monday'));
												//echo '<pre>'; print_r($LeaveInfo); 
												if($arrApprovalInfo["status_manager"] == "1")
												{
													/* Approve */
													$arrInfo["user_id"] = $LeaveInfo["employee_id"];
													$arrInfo["date"] = $LeaveInfo["start_dt"];
													if($arrInfo["date"] >= $next_monday_date )
													{
														$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("PHL");
													}
													else
													{
														$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("UHL");
													}
													
													$objAttendance->fnInsertRosterHalfAttendance($arrInfo);
												}
												else
												{
													/* Un approve */
													$arrInfo["user_id"] = $LeaveInfo["employee_id"];
													$arrInfo["date"] = $LeaveInfo["start_dt"];
													$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("");
													
													$objAttendance->fnInsertRosterHalfAttendance($arrInfo);
												}
												//die;
											$db->updateArray("pms_half_leave_form",$arrApprovalInfo);
										}
									}
								}
							}
						}
						else if($arrCode[2] == "SM" || $arrCode[2] == "ESM")
						{
							include_once("includes/class.shift_movement.php");
							include_once("includes/class.leave.php");
							include_once("includes/class.attendance.php");

							$objShiftMovement = new shift_movement();
							$objLeave = new leave();
							$objAttendance = new attendance();

							
							/* Shift movement approval / rejection by mail */
							$sSQL = "select *, date_format(movement_date,'%Y-%m-%d') as movement_date, time_format(movement_fromtime,'%H:%i') as movement_fromtime, time_format(movement_totime,'%H:%i') as movement_totime from pms_shift_movement where (tlapprovalcode='".mysql_real_escape_string($uniqCode)."' or managerapprovalcode='".mysql_real_escape_string($uniqCode)."' or delegatedtlapprovalcode='".mysql_real_escape_string($uniqCode)."' or delegatedmanagerapprovalcode='".mysql_real_escape_string($uniqCode)."')";
							$db->query($sSQL);
							if($db->num_rows() > 0)
							{
								if($db->next_record())
								{
									if($approvalStatus != 0)
									{
										$status = array("0"=>"Pending", "1"=>"Approved", "2"=>"Unapproved");
//print_r($status);
//echo $approvalStatus."==".strtoupper($status[$approvalStatus]); die;
										$EmployeeInfo = $objEmployee->fnGetEmployeeById($db->f("userid"));

										$TeamleaderInfo = $objEmployee->fnGetEmployeeById($EmployeeInfo["teamleader_id"]);

										if($db->f("tlapprovalcode") == $uniqCode)
										{
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["approvedby_tl"] = $approvalStatus;
											$arrApprovalInfo["lt_approval_date"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["tlapprovalcode"] = $db->f("tlapprovalcode")."_used";

											//print_r($arrApprovalInfo);

											$db->updateArray("pms_shift_movement",$arrApprovalInfo);

											/* Mail to employee */
											$MailTo = $EmployeeInfo["email"];
											$Subject = "Shift Movement ".$status[$approvalStatus];

											$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
											$content .= $TeamleaderInfo["name"]." has ".strtoupper($status[$approvalStatus])." your shift movement request on ".$db->f("movement_date")." for the time ".$db->f("movement_fromtime")." to ".$db->f("movement_totime")." hours.";

											if($arrApprovalInfo["approvedby_tl"] == "1")
												$content .= "<br/><br/>Kindly ensure that you compensate the same as per your proposed timinings.";
											$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
											sendmail($MailTo, $Subject, $content);

											/* Common mail content */
											$tempContent = $TeamleaderInfo["name"]." has ".strtoupper($status[$approvalStatus])." shift movement request of ".$EmployeeInfo["name"]." on ".$db->f("movement_date")." for the time - ".$db->f("movement_fromtime")." to ".$db->f("movement_totime")." hours";
											$tempContent .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

											/* Mail to manager */
											if($TeamleaderInfo["teamleader_id"] != 0)
											{
												$ManagerInfo = $objEmployee->fnGetEmployeeById($TeamleaderInfo["teamleader_id"]);
												if($ManagerInfo["designation"] != "8" && $ManagerInfo["designation"] != "17")
												{
													$MailTo = $ManagerInfo["email"];
													$content = "Dear ".$ManagerInfo["name"].",<br><br>".$tempContent;
													sendmail($MailTo, $Subject, $content);
												}
											}

											/* Mail to delegated team leader */
											if($db->f("delegatedtl_id") != "" && $db->f("delegatedtl_id") != "0")
											{
												$TeamleaderInfo = $objEmployee->fnGetEmployeeById($db->f("delegatedtl_id"));
												$MailTo = $TeamleaderInfo["email"];
												$content = "Dear ".$TeamleaderInfo["name"].",<br><br>".$tempContent;
												sendmail($MailTo, $Subject, $content);
											}

											/* Mail to delegated manager */
											if($db->f("delegatedmanager_id") != "" && $db->f("delegatedmanager_id") != "0")
											{
												$ManagerInfo = $objEmployee->fnGetEmployeeById($db->f("delegatedmanager_id"));
												$MailTo = $ManagerInfo["email"];
												$content = "Dear ".$ManagerInfo["name"].",<br><br>".$tempContent;
												sendmail($MailTo, $Subject, $content);
											}
										}
										else if($db->f("managerapprovalcode") == $uniqCode && $db->f("approvedby_tl") != 2)
										{
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["approvedby_manager"] = $approvalStatus;
											$arrApprovalInfo["manager_approval_date"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["managerapprovalcode"] = $db->f("managerapprovalcode")."_used";

											$getDetailOfShiftMovement = $objShiftMovement->fnUserShiftMovementById1($arrApprovalInfo['id']);
											
											
											if($arrApprovalInfo["approvedby_manager"] == "1")
											{
												$arrInfo["user_id"] = $getDetailOfShiftMovement["user_id"];
												$arrInfo["date"] = $getDetailOfShiftMovement["movementdate"];
												$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("SM");
												$objAttendance->fnInsertRosterAttendance($arrInfo);
												//die;
											}
											else
											{
												$arrInfo["user_id"] = $getDetailOfShiftMovement["userid"];
												$arrInfo["date"] = $getDetailOfShiftMovement["movementdate"];
												$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("");
												$objAttendance->fnInsertRosterAttendance($arrInfo);
											}
				

											$db->updateArray("pms_shift_movement",$arrApprovalInfo);

											if($TeamleaderInfo["designation"] == "6" || $TeamleaderInfo["designation"] == "18" || $TeamleaderInfo["designation"] == "19")
												$ManagerInfo = $TeamleaderInfo;
											else
												$ManagerInfo = $objEmployee->fnGetEmployeeById($TeamleaderInfo["teamleader_id"]);

											/* Send mail to employee */
											$MailTo = $EmployeeInfo["email"];
											$Subject = "Shift Movement ".$status[$approvalStatus];
											$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
											$content .= $ManagerInfo["name"]." has ".strtoupper($status[$approvalStatus])." your shift movement request on ".$db->f("movement_date")." for the time ".$db->f("movement_fromtime")." to ".$db->f("movement_totime")." hours.";
											if($arrApprovalInfo["approvedby_manager"] == "1")
												$content .= "<br/><br/>Kindly ensure that you compensate the same as per your proposed timinings.";
											$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

											sendmail($MailTo, $Subject, $content);

											/* Common mail content for manager, delegated tl and delegated manager */
											$tempContent = $ManagerInfo["name"]." has ".strtoupper($status[$approvalStatus])." shift movement request of ".$EmployeeInfo["name"]." on ".$db->f("movement_date")." for the time - ".$db->f("movement_fromtime")." to ".$db->f("movement_totime")." hours.";
											$tempContent .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

											if($EmployeeInfo["teamleader_id"] != 0 && $EmployeeInfo["teamleader_id"] != "" && $ManagerInfo["id"] != $EmployeeInfo["teamleader_id"])
											{
												$TeamleaderInfo = $objEmployee->fnGetEmployeeById($EmployeeInfo["teamleader_id"]);
												if($TeamleaderInfo["designation"] != "6" && $TeamleaderInfo["designation"] != "18" && $TeamleaderInfo["designation"] != "19")
												{
													$MailTo = $TeamleaderInfo["email"];
													$content = "Dear ".$TeamleaderInfo["name"].",<br><br>".$tempContent;
													sendmail($MailTo, $Subject, $content);
												}
											}

											/* Mail to delegated team leader */
											if($db->f("delegatedtl_id") != "" && $db->f("delegatedtl_id") != "0")
											{
												$TeamleaderInfo = $objEmployee->fnGetEmployeeById($db->f("delegatedtl_id"));
												$MailTo = $TeamleaderInfo["email"];
												$content = "Dear ".$TeamleaderInfo["name"].",<br><br>".$tempContent;
												sendmail($MailTo, $Subject, $content);
											}

											/* Mail to delegated manager */
											if($db->f("delegatedmanager_id") != "" && $db->f("delegatedmanager_id") != "0")
											{
												$ManagerInfo = $objEmployee->fnGetEmployeeById($db->f("delegatedmanager_id"));
												$MailTo = $ManagerInfo["email"];
												$content = "Dear ".$ManagerInfo["name"].",<br><br>".$tempContent;
												sendmail($MailTo, $Subject, $content);
											}
										}
										else if($db->f("delegatedtlapprovalcode") == $uniqCode && $db->f("approvedby_tl") != 2)
										{
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["delegatedtl_status"] = $approvalStatus;
											$arrApprovalInfo["delegatedtl_date"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["delegatedtlapprovalcode"] = $db->f("delegatedtlapprovalcode")."_used";

											//print_r($arrApprovalInfo);

											$db->updateArray("pms_shift_movement",$arrApprovalInfo);

											$TeamleaderInfo = $objEmployee->fnGetEmployeeById($db->f("delegatedtl_id"));

											/* Mail to employee */
											$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
											$content .= $TeamleaderInfo["name"]." has ".strtoupper($status[$approvalStatus])." your shift movement request on ".$db->f("movement_date")." for the time ".$db->f("movement_fromtime")." to ".$db->f("movement_totime")." hours.";

											if($arrApprovalInfo["approvedby_tl"] == "1")
												$content .= "<br/><br/>Kindly ensure that you compensate the same as per your proposed timinings.";
											$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
											sendmail($MailTo, $Subject, $content);

											/* Common mail content */
											$tempContent = $TeamleaderInfo["name"]." has ".strtoupper($status[$approvalStatus])." shift movement request of ".$EmployeeInfo["name"]." on ".$db->f("movement_date")." for the time - ".$db->f("movement_fromtime")." to ".$db->f("movement_totime")." hours.";
											$tempContent .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

											/* Mail to team leader */
											if($EmployeeInfo["teamleader_id"] != 0)
											{
												$TeamleaderInfo = $objEmployee->fnGetEmployeeById($EmployeeInfo["teamleader_id"]);
												$MailTo = $TeamleaderInfo["email"];
												$content = "Dear ".$TeamleaderInfo["name"].",<br><br>".$tempContent;
												sendmail($MailTo, $Subject, $content);

												/* Mail to manager */
												if($TeamleaderInfo["teamleader_id"] != 0)
												{
													$ManagerInfo = $objEmployee->fnGetEmployeeById($TeamleaderInfo["teamleader_id"]);
													if($ManagerInfo["designation"] != 8 && $ManagerInfo["designation"] != 17)
													{
														$MailTo = $ManagerInfo["email"];
														$content = "Dear ".$ManagerInfo["name"].",<br><br>".$tempContent;
														sendmail($MailTo, $Subject, $content);
													}
												}
											}

											/* Mail to delegated manager */
											if($db->f("delegatedmanager_id") != "" && $db->f("delegatedmanager_id") != "0")
											{
												$TeamleaderInfo = $objEmployee->fnGetEmployeeById($db->f("delegatedmanager_id"));
												$MailTo = $TeamleaderInfo["email"];
												$content = "Dear ".$TeamleaderInfo["name"].",<br><br>".$tempContent;
												sendmail($MailTo, $Subject, $content);
											}
										}
										else if($db->f("delegatedmanagerapprovalcode") == $uniqCode && $db->f("approvedby_tl") != 2)
										{
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["delegatedmanager_status"] = $approvalStatus;
											$arrApprovalInfo["delegatedmanager_date"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["delegatedmanagerapprovalcode"] = $db->f("delegatedmanagerapprovalcode")."_used";

											//print_r($arrApprovalInfo);

											$db->updateArray("pms_shift_movement",$arrApprovalInfo);

											$ManagerInfo = $objEmployee->fnGetEmployeeById($db->f("delegatedmanager_id"));

											$MailTo = $EmployeeInfo["email"];
											$Subject = "Shift Movement ".$status[$approvalStatus];

											/* Mail to employee who has added a shift movement */
											$MailTo = $EmployeeInfo["email"];
											$Subject = "Shift Movement ".$status[$approvalStatus];
											$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
											$content .= $ManagerInfo["name"]." has ".strtoupper($status[$approvalStatus])." your shift movement request on ".$db->f("movement_date")." for the time ".$db->f("movement_fromtime")." to ".$db->f("movement_totime")." hours.";

											if($arrApprovalInfo["delegatedmanager_status"] == "1")
												$content .= "<br/><br/>Kindly ensure that you compensate the same as per your proposed timinings.";
											$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

											sendmail($MailTo, $Subject, $content);

											/* Common content to be sent to tl & manager */
											$tempContent = $ManagerInfo["name"]." has ".strtoupper($status[$approvalStatus])." shift movement request of ".$EmployeeInfo["name"]." on ".$db->f("movement_date")." for the time - ".$db->f("movement_fromtime")." to ".$db->f("movement_totime")." hours.";
											$tempContent .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

											/* Mail to team leader */
											if($EmployeeInfo["teamleader_id"] != 0)
											{
												$TeamleaderInfo = $objEmployee->fnGetEmployeeById($EmployeeInfo["teamleader_id"]);
												$MailTo = $TeamleaderInfo["email"];
												$content = "Dear ".$TeamleaderInfo["name"].",<br><br>".$tempContent;
												sendmail($MailTo, $Subject, $content);

												/* Mail to manager */
												if($TeamleaderInfo["teamleader_id"] != 0)
												{
													$ManagerInfo = $objEmployee->fnGetEmployeeById($TeamleaderInfo["teamleader_id"]);
													if($ManagerInfo["designation"] != 8 && $ManagerInfo["designation"] != 17)
													{
														$MailTo = $ManagerInfo["email"];
														$content = "Dear ".$ManagerInfo["name"].",<br><br>".$tempContent;
														sendmail($MailTo, $Subject, $content);
													}
												}
											}

											/* Mail to delegated team leader */
											if($db->f("delegatedtl_id") != "" && $db->f("delegatedtl_id") != "0")
											{
												$TeamleaderInfo = $objEmployee->fnGetEmployeeById($db->f("delegatedtl_id"));
												$MailTo = $TeamleaderInfo["email"];
												$content = "Dear ".$TeamleaderInfo["name"].",<br><br>".$tempContent;
												sendmail($MailTo, $Subject, $content);
											}
										}
									}
								}
							}
						}
						else if($arrCode[2] == "SMC")
						{
							/* Shift movement compensation approval / rejection by mail */
							$sSQL = "select c.*, date_format(sm.movement_date,'%Y-%m-%d') as movement_date, time_format(sm.movement_fromtime,'%H:%i') as movement_fromtime, time_format(sm.movement_totime,'%H:%i') as movement_totime, date_format(c.compensation_date,'%Y-%m-%d') as compensation_date, time_format(c.compensation_fromtime,'%H:%i') as compensation_fromtime, time_format(c.compensation_totime,'%H:%i') as compensation_totime from pms_shift_movement_compensation c INNER JOIN pms_shift_movement sm ON sm.id = c.shift_movement_id where (c.tlapprovalcode='".mysql_real_escape_string($uniqCode)."' or c.delegatedtlapprovalcode='".mysql_real_escape_string($uniqCode)."')";
							$db->query($sSQL);
							if($db->num_rows() > 0)
							{
								if($db->next_record())
								{
									if($approvalStatus != 0)
									{
										$EmployeeInfo = $objEmployee->fnGetEmployeeById($db->f("userid"));
										$status = array("0"=>"Pending", "1"=>"Approved", "2"=>"Unapproved");

										if($db->f("tlapprovalcode") == $uniqCode)
										{
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["approvedby_tl"] = $approvalStatus;
											$arrApprovalInfo["tl_approveddate"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["tlapprovalcode"] = $db->f("tlapprovalcode")."_used";

											//print_r($arrApprovalInfo);

											$db->updateArray("pms_shift_movement_compensation",$arrApprovalInfo);

											$TlInfo = $objEmployee->fnGetEmployeeById($EmployeeInfo["teamleader_id"]);

											/* Send mail to the employee */
											$MailTo = $EmployeeInfo["email"];
											$Subject = "Shift Movement Compensation ".$status[$approvalStatus];

											$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
											$content .= $TlInfo["name"]." has ".strtoupper($status[$approvalStatus])." your shift movement compensation request for shift movement on ".$db->f("movement_date")." for the time ".$db->f("movement_fromtime")." to ".$db->f("movement_totime")." compensated on ".$db->f("compensation_date")." from ".$db->f("compensation_fromtime")." to ".$db->f("compensation_totime");
											$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

											sendmail($MailTo, $Subject, $content);

											/* Send mail to delegated team leader */
											if($db->f("delegatedtl_id") != 0 && $db->f("delegatedtl_id") != "")
											{
												$DelegatedTlInfo = $objEmployee->fnGetEmployeeById($db->f("delegatedtl_id"));

												$MailTo = $DelegatedTlInfo["email"];
												$content = "Dear ".$DelegatedTlInfo["name"].",<br><br>";
												$content .= $TlInfo["name"]." has ".strtoupper($status[$approvalStatus])." shift movement compensation request of ".$EmployeeInfo["name"]." for shift movement on ".$db->f("movement_date")." for the time ".$db->f("movement_fromtime")." to ".$db->f("movement_totime")." compensated on ".$db->f("compensation_date")." from ".$db->f("compensation_fromtime")." to ".$db->f("compensation_fromtime");
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail($MailTo, $Subject, $content);
											}

										}
										else if($db->f("delegatedtlapprovalcode") == $uniqCode)
										{
											print_r($db->fetchrow());
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["delegatedtl_status"] = $approvalStatus;
											$arrApprovalInfo["delegatedtl_date"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["delegatedtlapprovalcode"] = $db->f("delegatedtlapprovalcode")."_used";

											//print_r($arrApprovalInfo);

											$db->updateArray("pms_shift_movement_compensation",$arrApprovalInfo);

											$DelegatedTlInfo = $objEmployee->fnGetEmployeeById($db->f("delegatedtl_id"));

											/* Send mail to employee who has added shift movement complensation */
											$MailTo = $EmployeeInfo["email"];
											$Subject = "Shift Movement Compensation ".$status[$ApprovalInfo["delegatedtl_status"]];

											$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
											$content .= $DelegatedTlInfo["name"]." has ".strtoupper($status[$approvalStatus])." your shift movement compensation request for shift movement on ".$db->f("movement_date")." for the time ".$db->f("movement_fromtime")." to ".$db->f("movement_totime")." compensated on ".$db->f("compensation_date")." from the time ".$db->f("compensation_fromtime")." to ".$db->f("compensation_fromtime")."<br/><br/>";
											$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

											sendmail($MailTo, $Subject, $content);

											/* Send mail to teamleader */
											if($EmployeeInfo["teamleader_id"] != "" && $EmployeeInfo["teamleader_id"] != "0")
											{
												$TlInfo = $objEmployee->fnGetEmployeeById($EmployeeInfo["teamleader_id"]);

												$MailTo = $TlInfo["email"];
												$content = "Dear ".$TlInfo["name"].",<br><br>";
												$content .= $DelegatedTlInfo["name"]." has ".strtoupper($status[$approvalStatus])." shift movement compensation request of ".$EmployeeInfo["name"]." for shift movement on ".$db->f("movement_date")." for the time ".$db->f("movement_fromtime")." to ".$db->f("movement_totime")." compensated on ".$db->f("compensation_date")." from ".$db->f("compensation_fromtime")." to ".$db->f("compensation_fromtime")."<br/><br/>";
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail($MailTo, $Subject, $content);
											}
										}
									}
								}
							}
						}
						else if($arrCode[2] == "C")
						{
							/* late comming compensation approval / rejection by mail */
							$sSQL = "select c.*, date_format(date,'%Y-%m-%d') as exceeddate from pms_exceed_compensation c INNER JOIN pms_attendance a ON a.id = c.attendance_id where (c.tlapprovalcode='".mysql_real_escape_string($uniqCode)."' or c.delegatedtlapprovalcode='".mysql_real_escape_string($uniqCode)."') ";
							$db->query($sSQL);
							if($db->num_rows() > 0)
							{
								if($db->next_record())
								{
									if($approvalStatus != 0)
									{
										$EmployeeInfo = $objEmployee->fnGetEmployeeById($db->f("userid"));
										$status = array("0"=>"Pending", "1"=>"Approved", "2"=>"Unapproved");

										if($db->f("tlapprovalcode") == $uniqCode)
										{
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["approvedby_tl"] = $approvalStatus;
											$arrApprovalInfo["tl_approveddate"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["tlapprovalcode"] = $db->f("tlapprovalcode")."_used";

											//print_r($arrApprovalInfo);

											$db->updateArray("pms_exceed_compensation",$arrApprovalInfo);

											$TlInfo = $objEmployee->fnGetEmployeeById($EmployeeInfo["teamleader_id"]);

											/* Send mail to the employee */
											$MailTo = $EmployeeInfo["email"];
											$Subject = "Late comming compensation ".$status[$approvalStatus];

											$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
											$content .= $TlInfo["name"]." has ".strtoupper($status[$approvalStatus])." your compensation request for late comming on ".$db->f("exceeddate");
											$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

											sendmail($MailTo, $Subject, $content);

											/* Send mail to delegated team leader */
											if($db->f("delegatedtl_id") != 0 && $db->f("delegatedtl_id") != "")
											{
												$DelegatedTlInfo = $objEmployee->fnGetEmployeeById($db->f("delegatedtl_id"));

												$MailTo = $DelegatedTlInfo["email"];
												$content = "Dear ".$DelegatedTlInfo["name"].",<br><br>";
												$content .= $TlInfo["name"]." has ".strtoupper($status[$approvalStatus])." compensation request of ".$EmployeeInfo["name"]." for late comming on ".$db->f("exceeddate");
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail($MailTo, $Subject, $content);
											}
										}
										else if($db->f("delegatedtlapprovalcode") == $uniqCode)
										{
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["delegatedtl_status"] = $approvalStatus;
											$arrApprovalInfo["delegatedtl_date"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["delegatedtlapprovalcode"] = $db->f("delegatedtlapprovalcode")."_used";

											//print_r($arrApprovalInfo);

											$db->updateArray("pms_exceed_compensation",$arrApprovalInfo);

											$DelegatedTlInfo = $objEmployee->fnGetEmployeeById($db->f("delegatedtl_id"));

											/* Send mail to employee who has added complensation for late comming */
											$MailTo = $EmployeeInfo["email"];
											$Subject = "Late comming compensation ".$status[$approvalStatus];

											$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
											$content .= $DelegatedTlInfo["name"]." has ".strtoupper($status[$approvalStatus])." your compensation request for late comming on ".$db->f("exceeddate");
											$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

											sendmail($MailTo, $Subject, $content);

											if($EmployeeInfo["teamleader_id"] != "" && $EmployeeInfo["teamleader_id"] != "0")
											{
												$TlInfo = $objEmployee->fnGetEmployeeById($EmployeeInfo["teamleader_id"]);

												/* Send mail to teamleader */
												$MailTo = $TlInfo["email"];
												$content = "Dear ".$TlInfo["name"].",<br><br>";
												$content .= $DelegatedTlInfo["name"]." has ".strtoupper($status[$approvalStatus])." compensation request of ".$EmployeeInfo["name"]." for late comming on ".$db->f("exceeddate");
												$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail($MailTo, $Subject, $content);
											}
										}
									}
								}
							}
						}
						else if($arrCode[2] == "AP")
						{
							/* Attrition process approval / rejection by mail */
							$sSQL = "select * from pms_attrition_process where tlapprovalcode='".mysql_real_escape_string($uniqCode)."' or managerapprovalcode='".mysql_real_escape_string($uniqCode)."' or hrapprovalcode='".mysql_real_escape_string($uniqCode)."' or adminapprovalcode='".mysql_real_escape_string($uniqCode)."'";
							$db->query($sSQL);
							if($db->num_rows() > 0)
							{
								if($db->next_record())
								{
									$arrEmployee = $objEmployee->fnGetEmployeeDetailById($db->f("userid"));
									$arrTL = $objEmployee->fnGetEmployeeDetailById($db->f("tlid"));
									$arrManager = $objEmployee->fnGetEmployeeDetailById($db->f("managerid"));

									if($approvalStatus != 0)
									{
										if($db->f("tlapprovalcode") == $uniqCode)
										{
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["tl_status"] = $approvalStatus;
											$arrApprovalInfo["tl_date"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["tlapprovalcode"] = $db->f("tlapprovalcode")."_used";

											//print_r($arrApprovalInfo);

											$db->updateArray("pms_attrition_process",$arrApprovalInfo);

											/* Send mail */
											if($approvalStatus == 1)
											{
												/* Mail to TL */
												$Subject = "Attrition process - Process termination";

												if(count($arrTL) > 0)
												{
													$content = "Dear ".$arrTL["name"].",<br/><br/>You have requested to send the <b>(Show Cause/Termination) Notice</b> to ".$arrEmployee["name"]." as he/she has been absent since last 3 consecutive days.<br><br>Regards,<br>".SITEADMINISTRATOR;

													sendmail($arrTL["email"], $Subject, $content);
												}

												/* Mail to manager */
												if(count($arrManager) > 0)
												{
													$content = "Dear ".$arrManager["name"].",<br/><br/>Kindly note that ".$arrTL["name"]."   has requested to send the <b>(Show Cause/Termination) Notice</b> to ".$arrEmployee["name"]." as he/she has been absent since last 3 consecutive days.<br><br>Regards,<br>".SITEADMINISTRATOR;

													sendmail($arrManager["email"], $Subject, $content);
												}

												/* Mail to HR */
												$content = "Dear HR,<br/><br/>Kindly note that ".$arrTL["name"]." has requested to send the <b>(Show Cause/Termination) Notice</b> to ".$arrEmployee["name"]." as he/she has been absent since last 3 consecutive days.<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail("hr@transformsolution.net", $Subject, $content);

												/* Mail to Admin */
												$content = "Dear Admin,<br/><br/>Kindly note that ".$arrTL["name"]." has requested to send the <b>(Show Cause/Termination) Notice</b> to ".$arrEmployee["name"]." as he/she has been absent since last 3 consecutive days.<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail("admin@transformsolution.net", $Subject, $content);
											}
											else if($approvalStatus == 2)
											{
												/* Mail to TL */
												$Subject = "Attrition process - Hold";

												if(count($arrTL) > 0)
												{
													$content = "Dear ".$arrTL["name"].",<br/><br/>You have requested to HOLD the sending of the <b> (Show Cause/Termination) </b> Notice to ".$arrEmployee["name"]." via email though he has been absent since past 3 days.<br/>Kindly login to the intranet and fill the form for the same, else your HOLD request would not be effected.<br><br>Regards,<br>".SITEADMINISTRATOR;

													sendmail($arrTL["email"], $Subject, $content);
												}

												/* Mail to manager */
												if(count($arrManager) > 0)
												{
													$content = "Dear ".$arrManager["name"].",<br/><br/>Kindly note that ".$arrTL["name"]." has requested to HOLD the sending the <b> (Show Cause/Termination) </b> Notice to ".$arrEmployee["name"]." though he has been absent since past 3 days.<br><br>Please approve the same.<br><br>Regards,<br>".SITEADMINISTRATOR;

													sendmail($arrManager["email"], $Subject, $content);
												}

												/* Mail to HR */
												$content = "Dear HR,<br/><br/>Kindly note that ".$arrTL["name"]." has requested to HOLD the sending the <b> (Show Cause/Termination) </b> Notice to ".$arrEmployee["name"]." though he has been absent since past 3 days.<br><br>Please approve the same.<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail("hr@transformsolution.net", $Subject, $content);

												/* Mail to Admin */
												$content = "Dear Admin,<br/><br/>Kindly note that ".$arrTL["name"]." has requested to HOLD the sending the <b> (Show Cause/Termination) </b> Notice to ".$arrEmployee["name"]." though he has been absent since past 3 days.<br><br>Please approve the same.<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail("admin@transformsolution.net", $Subject, $content);
											}
										}
										else if($db->f("managerapprovalcode") == $uniqCode)
										{
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["manager_status"] = $approvalStatus;
											$arrApprovalInfo["manager_date"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["managerapprovalcode"] = $db->f("managerapprovalcode")."_used";

											//print_r($arrApprovalInfo);

											$db->updateArray("pms_attrition_process",$arrApprovalInfo);

											/* Send mail */
											if($approvalStatus == 1)
											{
												/* Mail to manager */
												$Subject = "Attrition process - Process termination";

												if(count($arrManager) > 0)
												{
													$content = "Dear ".$arrManager["name"].",<br/><br/>You have requested to send the <b>(Show Cause/Termination) Notice</b> to ".$arrEmployee["name"]." as he/she has been absent since last 3 consecutive days.<br><br>Regards,<br>".SITEADMINISTRATOR;

													sendmail($arrManager["email"], $Subject, $content);
												}

												/* Mail to TL */
												if(count($arrTL) > 0)
												{
													$content = "Dear ".$arrTL["name"].",<br/><br/>Kindly note that ".$arrManager["name"]." has requested to send the <b>(Show Cause/Termination) Notice</b> to ".$arrEmployee["name"]." as he/she has been absent since last 3 consecutive days.<br><br>Regards,<br>".SITEADMINISTRATOR;

													sendmail($arrTL["email"], $Subject, $content);
												}

												/* Mail to HR */
												$content = "Dear HR,<br/><br/>Kindly note that ".$arrManager["name"]." has requested to send the <b>(Show Cause/Termination) Notice</b> to ".$arrEmployee["name"]." as he/she has been absent since last 3 consecutive days.<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail("hr@transformsolution.net", $Subject, $content);

												/* Mail to Admin */
												$content = "Dear Admin,<br/><br/>Kindly note that ".$arrManager["name"]." has requested to send the <b>(Show Cause/Termination) Notice</b> to ".$arrEmployee["name"]." as he/she has been absent since last 3 consecutive days.<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail("admin@transformsolution.net", $Subject, $content);
											}
											else if($approvalStatus == 2)
											{
												/* Mail to manager */
												$Subject = "Attrition process - Hold";

												if(count($arrManager) > 0)
												{
													$content = "Dear ".$arrManager["name"].",<br/><br/>You have requested to HOLD the sending of the <b> (Show Cause/Termination) </b> Notice to ".$arrEmployee["name"]." via email though he has been absent since past 3 days.<br/>Kindly login to the intranet and fill the form for the same, else your HOLD request would not be effected.<br><br>Regards,<br>".SITEADMINISTRATOR;

													sendmail($arrManager["email"], $Subject, $content);
												}

												/* Mail to TL */
												if(count($arrTL) > 0)
												{
													$content = "Dear ".$arrTL["name"].",<br/><br/>Kindly note that ".$arrManager["name"]." has requested to HOLD the sending the <b> (Show Cause/Termination) </b> Notice to ".$arrEmployee["name"]." though he has been absent since past 3 days.<br><br>Please approve the same.<br><br>Regards,<br>".SITEADMINISTRATOR;

													sendmail($arrTL["email"], $Subject, $content);
												}

												/* Mail to HR */
												$content = "Dear HR,<br/><br/>Kindly note that ".$arrManager["name"]." has requested to HOLD the sending the <b> (Show Cause/Termination) </b> Notice to ".$arrEmployee["name"]." though he has been absent since past 3 days.<br><br>Please approve the same.<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail("hr@transformsolution.net", $Subject, $content);

												/* Mail to Admin */
												$content = "Dear Admin,<br/><br/>Kindly note that ".$arrManager["name"]." has requested to HOLD the sending the <b> (Show Cause/Termination) </b> Notice to ".$arrEmployee["name"]." though he has been absent since past 3 days.<br><br>Please approve the same.<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail("admin@transformsolution.net", $Subject, $content);
											}
										}
										else if($db->f("hrapprovalcode") == $uniqCode)
										{
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["hr_status"] = $approvalStatus;
											$arrApprovalInfo["hr_date"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["hrapprovalcode"] = $db->f("hrapprovalcode")."_used";

											//print_r($arrApprovalInfo);

											$db->updateArray("pms_attrition_process",$arrApprovalInfo);

											/* Send mail */
											if($approvalStatus == 1)
											{
												/* Mail to HR */
												$Subject = "Attrition process - Process termination";

												$content = "Dear HR,<br/><br/>You have requested to send the <b>(Show Cause/Termination) Notice</b> to ".$arrEmployee["name"]." as he/she has been absent since last 3 consecutive days.<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail("hr@transformsolution.net", $Subject, $content);

												/* Mail to TL */
												if(count($arrTL) > 0)
												{
													$content = "Dear ".$arrTL["name"].",<br/><br/>HR have requested to send the <b>(Show Cause/Termination) Notice</b> to ".$arrEmployee["name"]." as he/she has been absent since last 3 consecutive days.<br><br>Regards,<br>".SITEADMINISTRATOR;

													sendmail($arrTL["email"], $Subject, $content);
												}

												/* Mail to Manager */
												if(count($arrManager) > 0)
												{
													$content = "Dear ".$arrManager["name"].",<br/><br/>Kindly note that HR has requested to send the <b>(Show Cause/Termination) Notice</b> to ".$arrEmployee["name"]." as he/she has been absent since last 3 consecutive days.<br><br>Regards,<br>".SITEADMINISTRATOR;

													sendmail($arrManager["email"], $Subject, $content);
												}

												/* Mail to Admin */
												$content = "Dear Admin,<br/><br/>Kindly note that HR has requested to send the <b>(Show Cause/Termination) Notice</b> to ".$arrEmployee["name"]." as he/she has been absent since last 3 consecutive days.<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail("admin@transformsolution.net", $Subject, $content);
											}
											else if($approvalStatus == 2)
											{
												/* Mail to HR */
												$Subject = "Attrition process - Hold";

												if(count($arrManager) > 0)
												{
													$content = "Dear ".$arrManager["name"].",<br/><br/>You have requested to HOLD the sending of the <b> (Show Cause/Termination) </b> Notice to ".$arrEmployee["name"]." via email though he has been absent since past 3 days.<br/>Kindly login to the intranet and fill the form for the same, else your HOLD request would not be effected.<br><br>Regards,<br>".SITEADMINISTRATOR;

													sendmail("hr@transformsolution.net", $Subject, $content);
												}

												/* Mail to TL */
												if(count($arrTL) > 0)
												{
													$content = "Dear ".$arrTL["name"].",<br/><br/>Kindly note that HR has requested to HOLD the sending the <b> (Show Cause/Termination) </b> Notice to ".$arrEmployee["name"]." though he has been absent since past 3 days.<br><br>Please approve the same.<br><br>Regards,<br>".SITEADMINISTRATOR;

													sendmail($arrTL["email"], $Subject, $content);
												}

												/* Mail to manager */
												$content = "Dear ".$arrManager["name"].",<br/><br/>Kindly note that HR has requested to HOLD the sending the <b> (Show Cause/Termination) </b> Notice to ".$arrEmployee["name"]." though he has been absent since past 3 days.<br><br>Please approve the same.<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail($arrManager["email"], $Subject, $content);

												/* Mail to Admin */
												$content = "Dear Admin,<br/><br/>Kindly note that HR has requested to HOLD the sending the <b> (Show Cause/Termination) </b> Notice to ".$arrEmployee["name"]." though he has been absent since past 3 days.<br><br>Please approve the same.<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail("admin@transformsolution.net", $Subject, $content);
											}
										}
										else if($db->f("adminapprovalcode") == $uniqCode)
										{
											$arrApprovalInfo["id"] = $db->f("id");
											$arrApprovalInfo["admin_status"] = $approvalStatus;
											$arrApprovalInfo["admin_date"] = Date("Y-m-d H:i:s");
											$arrApprovalInfo["adminapprovalcode"] = $db->f("adminapprovalcode")."_used";

											//print_r($arrApprovalInfo);

											$db->updateArray("pms_attrition_process",$arrApprovalInfo);

											/* Send mail */
											if($approvalStatus == 1)
											{
												/* Mail to Admin */
												$Subject = "Attrition process - Process termination";
												$content = "Dear Admin,<br/><br/>You have requested to send the <b>(Show Cause/Termination) Notice</b> to ".$arrEmployee["name"]." as he/she has been absent since last 3 consecutive days.<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail("admin@transformsolution.net", $Subject, $content);

												/* Mail to TL */
												if(count($arrTL) > 0)
												{
													$content = "Dear ".$arrTL["name"].",<br/><br/>Kindly note that Admin has requested to send the <b>(Show Cause/Termination) Notice</b> to ".$arrEmployee["name"]." as he/she has been absent since last 3 consecutive days.<br><br>Regards,<br>".SITEADMINISTRATOR;

													sendmail($arrTL["email"], $Subject, $content);
												}

												/* Mail to Manager */
												if(count($arrManager) > 0)
												{
													$content = "Dear ".$arrManager["name"].",<br/><br/>Kindly note that Admin has requested to send the <b>(Show Cause/Termination) Notice</b> to ".$arrEmployee["name"]." as he/she has been absent since last 3 consecutive days.<br><br>Regards,<br>".SITEADMINISTRATOR;

													sendmail($arrManager["email"], $Subject, $content);
												}

												/* Mail to HR */
												$content = "Dear HR,<br/><br/>Kindly note that Admin has requested to send the <b>(Show Cause/Termination) Notice</b> to ".$arrEmployee["name"]." as he/she has been absent since last 3 consecutive days.<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail("hr@transformsolution.net", $Subject, $content);
											}
											else if($approvalStatus == 2)
											{
												/* Mail to Admin */
												$Subject = "Attrition process - Hold";
												$content = "Dear Admin,<br/><br/>You have requested to HOLD the sending of the <b> (Show Cause/Termination) </b> Notice to ".$arrEmployee["name"]." via email though he has been absent since past 3 days.<br/>Kindly login to the intranet and fill the form for the same, else your HOLD request would not be effected.<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail("admin@transformsolution.net", $Subject, $content);

												/* Mail to TL */
												if(count($arrTL) > 0)
												{
													$content = "Dear ".$arrTL["name"].",<br/><br/>Kindly note that Admin has requested to HOLD the sending the <b> (Show Cause/Termination) </b> Notice to ".$arrEmployee["name"]." though he has been absent since past 3 days.<br><br>Please approve the same.<br><br>Regards,<br>".SITEADMINISTRATOR;

													sendmail($arrTL["email"], $Subject, $content);
												}

												/* Mail to manager */
												if(count($arrManager) > 0)
												{
													$content = "Dear ".$arrManager["name"].",<br/><br/>Kindly note that Admin has requested to HOLD the sending the <b> (Show Cause/Termination) </b> Notice to ".$arrEmployee["name"]." though he has been absent since past 3 days.<br><br>Please approve the same.<br><br>Regards,<br>".SITEADMINISTRATOR;

													sendmail($arrManager["email"], $Subject, $content);
												}

												/* Mail to HR */
												$content = "Dear HR,<br/><br/>Kindly note that Admin has requested to HOLD the sending the <b> (Show Cause/Termination) </b> Notice to ".$arrEmployee["name"]." though he has been absent since past 3 days.<br><br>Please approve the same.<br><br>Regards,<br>".SITEADMINISTRATOR;

												sendmail("hr@transformsolution.net", $Subject, $content);
											}
										}
									}
								}
							}
						}
					}
				}
			}

			/* output the email header information */
			/*$output.= '<div class="toggler '.($overview[0]->seen ? 'read' : 'unread').'">';
			$output.= '<span class="subject">'.$overview[0]->subject.'</span> ';
			$output.= '<span class="from">'.$overview[0]->from.'</span>';
			$output.= '<span class="date">on '.$overview[0]->date.'</span>';
			$output.= '</div>';*/

			/* output the email body */
			/*$output.= '<div class="body">'.$message.'</div>';*/
		}

		//echo $output;
	}

	/* close the connection */
	imap_close($inbox);

?>

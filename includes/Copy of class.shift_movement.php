<?php

	include_once('db_mysql.php');

	class shift_movement extends DB_Sql
	{
		function __construct()
		{
		}

		function fnCheckPending()
		{
			/* If shift movement already added and is pending cannot add another shift movement */
			//$sSQL = "select * from pms_shift_movement where userid='".mysql_real_escape_string($_SESSION["id"])."' and approvedby_manager='0' and isactive='0'";
			//$sSQL = "select * from pms_shift_movement where userid='".mysql_real_escape_string($_SESSION["id"])."' and approvedby_manager='0' and isactive='0' and approvedby_tl != 2";
			//$sSQL = "select * from pms_shift_movement where userid='".mysql_real_escape_string($_SESSION["id"])."' and approvedby_manager='0' and (delegatedmanager_id!='0' and delegatedmanager_status='0') and isactive='0' and approvedby_tl != 2 and delegatedtl_status!='2' and isCancel='0'";
			$sSQL = "select * from pms_shift_movement where userid='".mysql_real_escape_string($_SESSION["id"])."' and approvedby_manager='0' and delegatedmanager_status='0' and isactive='0' and approvedby_tl != 2 and delegatedtl_status!='2' and isCancel='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				return false;
			}
			else
				return true;
		}

		function fnSaveShiftMovement($arrMovementInfo)
		{
			$totalCount = $this->fnValidateShiftMovement($_SESSION["id"], $arrMovementInfo["movement_date"]);

			if(!$this->fnCheckShiftMovementEligable($_SESSION["id"], $arrMovementInfo["movement_date"]))
			{
				/* indicates that shift movement / leave is already taken for this date */
				return -1;
			}

			if(!$this->fnCheckPending())
				return -2;

			if($totalCount < 2)
			{
				if($arrMovementInfo["movement_fromtime_ampm"] == "am" && $arrMovementInfo["movement_fromtime_hour"] == 12)
				{
					//$arrMovementInfo["movement_fromtime"] = ($arrMovementInfo["movement_fromtime_hour"] + 12) . ":".$arrMovementInfo["movement_fromtime_minutes"];
					$arrMovementInfo["movement_fromtime"] = "00:".$arrMovementInfo["movement_fromtime_minutes"];
				}
				else if($arrMovementInfo["movement_fromtime_ampm"] == "pm" && $arrMovementInfo["movement_fromtime_hour"] != 12)
				{
					$arrMovementInfo["movement_fromtime"] = ($arrMovementInfo["movement_fromtime_hour"] + 12) . ":".$arrMovementInfo["movement_fromtime_minutes"];
				}
				else
				{
					$arrMovementInfo["movement_fromtime"] = $arrMovementInfo["movement_fromtime_hour"] . ":" . $arrMovementInfo["movement_fromtime_minutes"];
				}

				if($arrMovementInfo["movement_totime_ampm"] == "am" && $arrMovementInfo["movement_totime_hour"] == 12)
				{
					//$arrMovementInfo["movement_totime"] = ($arrMovementInfo["movement_totime_hour"] + 12) . ":".$arrMovementInfo["movement_totime_minutes"];
					$arrMovementInfo["movement_totime"] = "00:".$arrMovementInfo["movement_totime_minutes"];
				}
				else if($arrMovementInfo["movement_totime_ampm"] == "pm" && $arrMovementInfo["movement_totime_hour"] != 12)
				{
					$arrMovementInfo["movement_totime"] = ($arrMovementInfo["movement_totime_hour"] + 12) . ":".$arrMovementInfo["movement_totime_minutes"];
				}
				else
				{
					$arrMovementInfo["movement_totime"] = $arrMovementInfo["movement_totime_hour"] . ":" . $arrMovementInfo["movement_totime_minutes"];
				}

				if($arrMovementInfo["compensation_fromtime_ampm"] == "am" && $arrMovementInfo["compensation_fromtime_hour"] == 12)
				{
					//$arrMovementInfo["compensation_fromtime"] = ($arrMovementInfo["compensation_fromtime_hour"] + 12) . ":".$arrMovementInfo["compensation_fromtime_minutes"];
					$arrMovementInfo["compensation_fromtime"] = "00:".$arrMovementInfo["compensation_fromtime_minutes"];
				}
				else if($arrMovementInfo["compensation_fromtime_ampm"] == "pm" && $arrMovementInfo["compensation_fromtime_hour"] != 12)
				{
					$arrMovementInfo["compensation_fromtime"] = ($arrMovementInfo["compensation_fromtime_hour"] + 12) . ":".$arrMovementInfo["compensation_fromtime_minutes"];
				}
				else
				{
					$arrMovementInfo["compensation_fromtime"] = $arrMovementInfo["compensation_fromtime_hour"] . ":" . $arrMovementInfo["compensation_fromtime_minutes"];
				}

				if($arrMovementInfo["compensation_totime_ampm"] == "am" && $arrMovementInfo["compensation_totime_hour"] == 12)
				{
					//$arrMovementInfo["compensation_totime"] = ($arrMovementInfo["compensation_totime_hour"] + 12) . ":".$arrMovementInfo["compensation_totime_minutes"];
					$arrMovementInfo["compensation_totime"] = "00:".$arrMovementInfo["compensation_totime_minutes"];
				}
				else if($arrMovementInfo["compensation_totime_ampm"] == "pm" && $arrMovementInfo["compensation_totime_hour"] != 12)
				{
					$arrMovementInfo["compensation_totime"] = ($arrMovementInfo["compensation_totime_hour"] + 12) . ":".$arrMovementInfo["compensation_totime_minutes"];
				}
				else
				{
					$arrMovementInfo["compensation_totime"] = $arrMovementInfo["compensation_totime_hour"] . ":" . $arrMovementInfo["compensation_totime_minutes"];
				}

				//print_r($arrMovementInfo);die;

				$arrMovementInfo["userid"] = $_SESSION["id"];
				$arrMovementInfo["addedon"] = date('Y-m-d H:i:s');
				$arrMovementInfo["approvedby_tl"] = 0;
				$arrMovementInfo["approvedby_manager"] = 0;
				$arrMovementInfo["isemergency"] = 0;

				include_once('class.employee.php');

				$objEmployee = new employee();

				$reportingHead = $objEmployee->fnGetReportingHeadId($_SESSION["id"]);
				$arrMovementInfo["reportinghead1"] = $reportingHead;

				$reportinghead2 = $objEmployee->fnGetReportingHeadId($reportingHead);
				$arrMovementInfo["reportinghead2"] = $reportinghead2;

				/* BEGIN - Check if delegated to other tl, add the delegated TL */

				include_once("class.leave.php");

				$objLeave = new leave();

				/* get user designation */
				$head_designation = $objEmployee->fnGetEmployeeDesignation($reportingHead);
				if($head_designation == "6" || $head_designation == "18" || $head_designation == "19")
					$checkDeligateReportingHead1Id = $objLeave->fnCheckDeligateManager($reportingHead);
				else
					$checkDeligateReportingHead1Id = $objLeave->fnCheckDeligate($reportingHead);

				//$checkDeligateReportingHead1Id = $objLeave->fnCheckDeligate($reportingHead);
				$checkDeligateReportingHead2Id = $objLeave->fnCheckDeligateManager($reportinghead2);

				$delegateReportingHead1 = 0;
				if(isset($checkDeligateReportingHead1Id) && $checkDeligateReportingHead1Id != '')
				{
					$delegateReportingHead1 = $checkDeligateReportingHead1Id;
				}

				$delegateReportingHead2 = 0;
				if(isset($delegateReportingHead2) && $checkDeligateReportingHead2Id != '')
				{
					$delegateReportingHead2 = $checkDeligateReportingHead2Id;
				}

				$arrMovementInfo["delegatedtl_id"] = $delegateReportingHead1;
				$arrMovementInfo["delegatedtl_status"] = 0;
				$arrMovementInfo["delegatedmanager_id"] = $delegateReportingHead2;
				$arrMovementInfo["delegatedmanager_status"] = 0;

				if($arrMovementInfo["delegatedtl_id"] != "")
					$arrMovementInfo["delegatedtlapprovalcode"] = shiftmovementform_uid();

				if($arrMovementInfo["delegatedmanager_id"] != "")
					$arrMovementInfo["delegatedmanagerapprovalcode"] = shiftmovementform_uid();

				/* END - Check if delegated to other tl, add the delegated TL */
				
				$arrMovementInfo["tlapprovalcode"] = shiftmovementform_uid();
				$arrMovementInfo["managerapprovalcode"] = shiftmovementform_uid();

				/* Insert to shift movement table */
				$id = $this->insertArray('pms_shift_movement',$arrMovementInfo);

				$employeeInfo = $objEmployee->fnGetEmployeeDetailById($_SESSION["id"]);
				$HeadInfo = $objEmployee->fnGetEmployeeDetailById($reportingHead);

				$arrHeads = $objEmployee->fnGetReportingHeads($_SESSION["id"]);

				/* Mail */
				
				$tempContent = "A Shift Movement has been requested by <b>".$employeeInfo["name"]."</b>. The details for movement requested are as follows:<br/><br/>";
				$tempContent .= "<table cellspacing='2' cellpadding='2' bgcolor='#E6E6E6'>
					<tr bgcolor='#FFFFFF'>
						<td><b>Movement Date: </b></td>
						<td>".$arrMovementInfo["movement_date"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>Requested Movement time: </b></td>
						<td>".$arrMovementInfo["movement_fromtime"]." - ".$arrMovementInfo["movement_totime"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>He/She has proposed to Compensate on: </b></td>
						<td>".$arrMovementInfo["compensation_date"].", ".$arrMovementInfo["compensation_fromtime"]." - ".$arrMovementInfo["compensation_totime"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>Reason: </b></td>
						<td>".$arrMovementInfo["reason"]."</td>
					</tr>";
					$Subject = "Shift Movement Request";
				
				if(count($arrHeads) > 0)
				{
					foreach($arrHeads as $curHead)
					{
						if(($_SESSION["designation"] == "6" || $_SESSION["designation"] == "18" || $_SESSION["designation"] == "19" || $_SESSION["designation"] == "25" || $_SESSION["designation"] == "44" || $_SESSION["designation"] == "17"))
						//if($curHead["designation"] != "8" && $curHead["designation"] != "17")
						//{
							/* Do not send mail to admin and CEO */

							$uniqueCode = "";
							if($curHead["designation"] == "6" || $curHead["designation"] == "18" || $curHead["designation"] == "19" || $curHead["designation"] == "25" || $curHead["designation"] == "44")
							{
								$uniqueCode = $arrMovementInfo["managerapprovalcode"];
							}
							else if($curHead["designation"] == "7" || $curHead["designation"] == "13")
							{
								$uniqueCode = $arrMovementInfo["tlapprovalcode"];
							}

							$MailTo = $curHead["email"];
							
							$content = "Dear ".$curHead["name"].",<br><br>".$tempContent;
							
							if($uniqueCode != "")
							{
								$content .= "<tr bgcolor='#FFFFFF'>
												<td colspan='2'>
													Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Approve_SM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Reject_SM]'>Reject</a></b> for letting us know your decision.
												</td>
											</tr>";
							}

							$content .= "</table>";
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

							sendmail($MailTo, $Subject, $content);
							//sendmail1("chandni.patel@transformsolution.net",$Subject,$content);
						//}
					}

					if($arrMovementInfo["delegatedtl_id"] != 0)
					{
						$DelegatedTL = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedtl_id"]);
						$MailTo = $DelegatedTL["email"];
						
						$content = "Dear ".$DelegatedTL["name"].",<br><br>".$tempContent;
						
						if($uniqueCode != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Approve_SM]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Reject_SM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if($arrMovementInfo["delegatedmanager_id"] != 0)
					{
						$DelegatedTL = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedmanager_id"]);
						$MailTo = $DelegatedTL["email"];
						
						$content = "Dear ".$DelegatedTL["name"].",<br><br>".$tempContent;
						
						if($uniqueCode != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedmanagerapprovalcode"]."_Approve_SM]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedmanagerapprovalcode"]."_Reject_SM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}
				}
				return 1;
			}
			else
			{
				return 0;
			}
		}

		function fnSaveEmergencyShiftMovement($arrMovementInfo)
		{
			include_once('class.attendance.php');

			$objAttendance = new attendance();

			
			$totalCount = $this->fnValidateShiftMovement($arrMovementInfo["userid"], $arrMovementInfo["movement_date"]);

			if(!$this->fnCheckShiftMovementEligable($arrMovementInfo["userid"], $arrMovementInfo["movement_date"]))
			{
				/* indicates that shift movement / leave is already taken for this date */
				return -1;
			}

			if($totalCount < 2)
			{
				if($arrMovementInfo["movement_fromtime_ampm"] == "am" && $arrMovementInfo["movement_fromtime_hour"] == 12)
				{
					//$arrMovementInfo["movement_fromtime"] = ($arrMovementInfo["movement_fromtime_hour"] + 12) . ":".$arrMovementInfo["movement_fromtime_minutes"];
					$arrMovementInfo["movement_fromtime"] = "00:".$arrMovementInfo["movement_fromtime_minutes"];
				}
				else if($arrMovementInfo["movement_fromtime_ampm"] == "pm" && $arrMovementInfo["movement_fromtime_hour"] != 12)
				{
					$arrMovementInfo["movement_fromtime"] = ($arrMovementInfo["movement_fromtime_hour"] + 12) . ":".$arrMovementInfo["movement_fromtime_minutes"];
				}
				else
				{
					$arrMovementInfo["movement_fromtime"] = $arrMovementInfo["movement_fromtime_hour"] . ":" . $arrMovementInfo["movement_fromtime_minutes"];
				}

				if($arrMovementInfo["movement_totime_ampm"] == "am" && $arrMovementInfo["movement_totime_hour"] == 12)
				{
					//$arrMovementInfo["movement_totime"] = ($arrMovementInfo["movement_totime_hour"] + 12) . ":".$arrMovementInfo["movement_totime_minutes"];
					$arrMovementInfo["movement_totime"] = "00:".$arrMovementInfo["movement_totime_minutes"];
				}
				else if($arrMovementInfo["movement_totime_ampm"] == "pm" && $arrMovementInfo["movement_totime_hour"] != 12)
				{
					$arrMovementInfo["movement_totime"] = ($arrMovementInfo["movement_totime_hour"] + 12) . ":".$arrMovementInfo["movement_totime_minutes"];
				}
				else
				{
					$arrMovementInfo["movement_totime"] = $arrMovementInfo["movement_totime_hour"] . ":" . $arrMovementInfo["movement_totime_minutes"];
				}

				if($arrMovementInfo["compensation_fromtime_ampm"] == "am" && $arrMovementInfo["compensation_fromtime_hour"] == 12)
				{
					//$arrMovementInfo["compensation_fromtime"] = ($arrMovementInfo["compensation_fromtime_hour"] + 12) . ":".$arrMovementInfo["compensation_fromtime_minutes"];
					$arrMovementInfo["compensation_fromtime"] = "00:".$arrMovementInfo["compensation_fromtime_minutes"];
				}
				else if($arrMovementInfo["compensation_fromtime_ampm"] == "pm" && $arrMovementInfo["compensation_fromtime_hour"] != 12)
				{
					$arrMovementInfo["compensation_fromtime"] = ($arrMovementInfo["compensation_fromtime_hour"] + 12) . ":".$arrMovementInfo["compensation_fromtime_minutes"];
				}
				else
				{
					$arrMovementInfo["compensation_fromtime"] = $arrMovementInfo["compensation_fromtime_hour"] . ":" . $arrMovementInfo["compensation_fromtime_minutes"];
				}

				if($arrMovementInfo["compensation_totime_ampm"] == "am" && $arrMovementInfo["compensation_totime_hour"] == 12)
				{
					//$arrMovementInfo["compensation_totime"] = ($arrMovementInfo["compensation_totime_hour"] + 12) . ":".$arrMovementInfo["compensation_totime_minutes"];
					$arrMovementInfo["compensation_totime"] = "00:".$arrMovementInfo["compensation_totime_minutes"];
				}
				else if($arrMovementInfo["compensation_totime_ampm"] == "pm" && $arrMovementInfo["compensation_totime_hour"] != 12)
				{
					$arrMovementInfo["compensation_totime"] = ($arrMovementInfo["compensation_totime_hour"] + 12) . ":".$arrMovementInfo["compensation_totime_minutes"];
				}
				else
				{
					$arrMovementInfo["compensation_totime"] = $arrMovementInfo["compensation_totime_hour"] . ":" . $arrMovementInfo["compensation_totime_minutes"];
				}

				//print_r($arrMovementInfo);die;

				$arrMovementInfo["addedon"] = date('Y-m-d H:i:s');
				
				if($_SESSION["designation"] == "6" || $_SESSION["designation"] == "18" || $_SESSION["designation"] == "19" || $_SESSION["designation"] == "25" || $_SESSION["designation"] == "44" || $_SESSION["designation"] == "17")
				{
					$arrMovementInfo["approvedby_manager"] = 1;
					$arrMovementInfo["manager_approval_date"] = Date('Y-m-d H:i:s');
					$arrMovementInfo["approvedby_tl"] = 0;
				}
				else if($_SESSION["designation"] == "7" || $_SESSION["designation"] == "13")
				{
					$arrMovementInfo["approvedby_tl"] = 1;
					$arrMovementInfo["lt_approval_date"] = Date('Y-m-d H:i:s');
					$arrMovementInfo["approvedby_manager"] = 0;
				}
				
				$arrMovementInfo["isemergency"] = 1;
				$arrMovementInfo["emergencysmaddedby"] = $_SESSION["id"];

				include_once('class.employee.php');

				$objEmployee = new employee();

				$reportingHead = $objEmployee->fnGetReportingHeadId($arrMovementInfo["userid"]);

				$arrMovementInfo["reportinghead1"] = $reportingHead;

				$reportinghead2 = $objEmployee->fnGetReportingHeadId($reportingHead);

				$arrMovementInfo["reportinghead2"] = $reportinghead2;

				//print_r($arrMovementInfo);
				//die;
				
				$arrMovementInfo["tlapprovalcode"] = shiftmovementform_uid();
				$arrMovementInfo["managerapprovalcode"] = shiftmovementform_uid();

				/* BEGIN - Check if delegated to other tl, add the delegated TL */

				include_once("class.leave.php");

				$objLeave = new leave();

				/* get user designation */
				$head_designation = $objEmployee->fnGetEmployeeDesignation($reportingHead);
				if($head_designation == "6" || $head_designation == "18" || $head_designation == "19")
					$checkDeligateReportingHead1Id = $objLeave->fnCheckDeligateManager($reportingHead);
				else
					$checkDeligateReportingHead1Id = $objLeave->fnCheckDeligate($reportingHead);

				//$checkDeligateReportingHead1Id = $objLeave->fnCheckDeligate($reportingHead);
				$checkDeligateReportingHead2Id = $objLeave->fnCheckDeligateManager($reportinghead2);

				$delegateReportingHead1 = 0;
				if(isset($checkDeligateReportingHead1Id) && $checkDeligateReportingHead1Id != '')
				{
					$delegateReportingHead1 = $checkDeligateReportingHead1Id;
				}

				$delegateReportingHead2 = 0;
				if(isset($delegateReportingHead2) && $checkDeligateReportingHead2Id != '')
				{
					$delegateReportingHead2 = $checkDeligateReportingHead2Id;
				}

				$arrMovementInfo["delegatedtl_id"] = $delegateReportingHead1;
				$arrMovementInfo["delegatedtl_status"] = 0;
				$arrMovementInfo["delegatedmanager_id"] = $delegateReportingHead2;
				$arrMovementInfo["delegatedmanager_status"] = 0;

				if($arrMovementInfo["delegatedtl_id"] != "")
					$arrMovementInfo["delegatedtlapprovalcode"] = shiftmovementform_uid();

				if($arrMovementInfo["delegatedmanager_id"] != "")
					$arrMovementInfo["delegatedmanagerapprovalcode"] = shiftmovementform_uid();

				/* END - Check if delegated to other tl, add the delegated TL */


				/* Insert emergency shift movement */

				if($arrMovementInfo["approvedby_manager"] == "1" || ($arrMovementInfo["approvedby_manager"] == "0" &&  $arrMovementInfo["delegatedmanager_status"] == "1"))
				{
					$arrInfo["user_id"] = $arrMovementInfo["userid"];
					$arrInfo["date"] = $arrMovementInfo["movement_date"];
					$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("SM");
					$objAttendance->fnInsertRosterAttendance($arrInfo);
					//die;
				}
				else
				{
					$arrInfo["user_id"] = $arrMovementInfo["userid"];
					$arrInfo["date"] = $arrMovementInfo["movement_date"];
					$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("");
					$objAttendance->fnInsertRosterAttendance($arrInfo);
				}
				
				$id = $this->insertArray('pms_shift_movement',$arrMovementInfo);

				$employeeInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["userid"]);
				$HeadInfo = $objEmployee->fnGetEmployeeDetailById($reportingHead);
				
				//$arrHeads = $objEmployee->fnGetReportingHeads($arrMovementInfo["userid"]);

				/* Mail */

				$Subject = "Emergency Shift Movement Request";

				$tempContent = $employeeInfo["name"]. " has requested for shift movement. The details for his request are as follows:<br/><br/>";
				$tempContent .= "<table cellspacing='2' cellpadding='2' bgcolor='#E6E6E6'>
					<tr bgcolor='#FFFFFF'>
						<td><b>Employee Name: </b></td>
						<td>".$employeeInfo["name"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>Reporting Head: </b></td>
						<td>".$HeadInfo["name"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>Movement Date: </b></td>
						<td>".$arrMovementInfo["movement_date"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>Requested Movement time: </b></td>
						<td>".$arrMovementInfo["movement_fromtime"]." - ".$arrMovementInfo["movement_totime"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>He/She has proposed to Compensate on: </b></td>
						<td>".$arrMovementInfo["compensation_date"].", time: ".$arrMovementInfo["compensation_fromtime"]." - ".$arrMovementInfo["compensation_totime"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>Reason: </b></td>
						<td>".$arrMovementInfo["reason"]."</td>
					</tr>";

				//if($reportinghead2 != "")
				//{
				$uniqueCode = "";
				$HeadInfo = $objEmployee->fnGetEmployeeDetailById($reportingHead);
				$HeadInfo2 = $objEmployee->fnGetEmployeeDetailById($reportinghead2);
				
				if($_SESSION["designation"] == "6" || $_SESSION["designation"] == "18" || $_SESSION["designation"] == "19" || $_SESSION["designation"] == "25" || $_SESSION["designation"] == "44" || $_SESSION["designation"] == "17")
				{
					if($HeadInfo2["id"] == $_SESSION["id"])
					{
						/* If login emp is a manager */
						
						/* send mail to delegated manager */
						if($arrMovementInfo["delegatedmanager_id"] != 0)
						{
							$DelegatedTL = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedmanager_id"]);
							$MailTo = $DelegatedTL["email"];
							
							$content = "Dear ".$DelegatedTL["name"].",<br><br>".$tempContent;
							
							if($arrMovementInfo["delegatedmanagerapprovalcode"] != "")
							{
								$content .= "<tr bgcolor='#FFFFFF'>
												<td colspan='2'>
													Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedmanagerapprovalcode"]."_Approve_ESM]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedmanagerapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
												</td>
											</tr>";
							}

							$content .= "</table>";
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

							sendmail($MailTo, $Subject, $content);
						}
					}
					else
					{
						/* If login emp is a delegated manager */
						
						if($HeadInfo2["id"] != "0" && ($HeadInfo2["designation"] == "6" || $HeadInfo2["designation"] == "18" || $HeadInfo2["designation"] == "19"))
						{
							/* Send mail to manager */
							$MailTo = $HeadInfo2["email"];
							$content = "Dear ".$HeadInfo2["name"].",<br><br>".$tempContent;
							if($arrMovementInfo["managerapprovalcode"] != "")
							{
								$content .= "<tr bgcolor='#FFFFFF'>
												<td colspan='2'>
													Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["managerapprovalcode"]."_Approve_ESM]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["managerapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
												</td>
											</tr>";
							}
							$content .= "</table>";
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

							sendmail($MailTo, $Subject, $content);
						}
					}
					
					if($HeadInfo["id"] != $_SESSION["id"])
					{
						/* send mail to team leader */
						$MailTo = $HeadInfo["email"];
						$content = "Dear ".$HeadInfo["name"].",<br><br>".$tempContent;
						if($uniqueCode != "")
						{
							if($HeadInfo["designation"] == "6" || $HeadInfo["designation"] == "18" || $HeadInfo["designation"] == "19" || $HeadInfo["designation"] == "25" || $HeadInfo["designation"] == "44")
							{
								$uniqueCode = $arrMovementInfo["managerapprovalcode"];
							}
							else
							{
								$uniqueCode = $arrMovementInfo["tlapprovalcode"];
							}
							
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Approve_ESM]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}
						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
						
						sendmail($MailTo, $Subject, $content);
					}
					
					/* send mail to delegated team leader */
					
					if($arrMovementInfo["delegatedtl_id"] != 0)
					{
						$DelegatedTL = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedtl_id"]);
						$MailTo = $DelegatedTL["email"];
						
						$content = "Dear ".$DelegatedTL["name"].",<br><br>".$tempContent;
						
						if($arrMovementInfo["delegatedtlapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Approve_ESM]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

				}
				else if($_SESSION["designation"] == "7" || $_SESSION["designation"] == "13")
				{
					if($HeadInfo["id"] == $_SESSION["id"])
					{
						/* logged in user is team leader */
						
						/* send mail to delegated team leader */
						if($arrMovementInfo["delegatedtl_id"] != 0)
						{
							$DelegatedTL = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedtl_id"]);
							$MailTo = $DelegatedTL["email"];
							
							$content = "Dear ".$DelegatedTL["name"].",<br><br>".$tempContent;
							
							if($arrMovementInfo["delegatedtlapprovalcode"] != "")
							{
								$content .= "<tr bgcolor='#FFFFFF'>
												<td colspan='2'>
													Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Approve_ESM]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
												</td>
											</tr>";
							}

							$content .= "</table>";
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

							sendmail($MailTo, $Subject, $content);
						}

					}
					else
					{
						/* logged in user is delegated team leader */
						
						/* send mail to team leader */
						if($HeadInfo["id"] != "0")
						{
							/* send mail to team leader */
							$MailTo = $HeadInfo["email"];
							$content = "Dear ".$HeadInfo["name"].",<br><br>".$tempContent;
							if($uniqueCode != "")
							{
								if($HeadInfo["designation"] == "6" || $HeadInfo["designation"] == "18" || $HeadInfo["designation"] == "19" || $HeadInfo["designation"] == "25" || $HeadInfo["designation"] == "44")
								{
									$uniqueCode = $arrMovementInfo["managerapprovalcode"];
								}
								else
								{
									$uniqueCode = $arrMovementInfo["tlapprovalcode"];
								}

								$content .= "<tr bgcolor='#FFFFFF'>
												<td colspan='2'>
													Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Approve_ESM]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
												</td>
											</tr>";
							}
							$content .= "</table>";
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
							
							sendmail($MailTo, $Subject, $content);
						}
					}
					
					/* Send mail to manager */
					if($HeadInfo2["id"] != "0" && ($HeadInfo2["designation"] == "6" || $HeadInfo2["designation"] == "18" || $HeadInfo2["designation"] == "19" || $HeadInfo2["designation"] == "25" || $HeadInfo2["designation"] == "44"))
					{
						/* Send mail to manager */
						$MailTo = $HeadInfo2["email"];
						$content = "Dear ".$HeadInfo2["name"].",<br><br>".$tempContent;
						if($arrMovementInfo["managerapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["managerapprovalcode"]."_Approve_ESM]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["managerapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}
						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					/* Send mail to delegated manager */
					if($arrMovementInfo["delegatedmanager_id"] != 0)
					{
						$DelegatedTL = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedmanager_id"]);
						$MailTo = $DelegatedTL["email"];
						
						$content = "Dear ".$DelegatedTL["name"].",<br><br>".$tempContent;
						
						if($arrMovementInfo["delegatedmanagerapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedmanagerapprovalcode"]."_Approve_ESM]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedmanagerapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}
				}
				
				return 1;
			}
			else
			{
				return 0;
			}
		}

		function fnSaveAdminShiftMovement($arrMovementInfo)
		{
			include_once('class.attendance.php');

			$objAttendance = new attendance();
			
			$totalCount = $this->fnValidateShiftMovement($arrMovementInfo["userid"], $arrMovementInfo["movement_date"]);

			if(!$this->fnCheckAdminShiftMovementEligable($arrMovementInfo["userid"], $arrMovementInfo["movement_date"]))
			{
				/* indicates that shift movement / leave is already taken for this date */
				return -1;
			}

			if($totalCount < 2)
			{
				if($arrMovementInfo["movement_fromtime_ampm"] == "am" && $arrMovementInfo["movement_fromtime_hour"] == 12)
				{
					$arrMovementInfo["movement_fromtime"] = "00:".$arrMovementInfo["movement_fromtime_minutes"];
				}
				else if($arrMovementInfo["movement_fromtime_ampm"] == "pm" && $arrMovementInfo["movement_fromtime_hour"] != 12)
				{
					$arrMovementInfo["movement_fromtime"] = ($arrMovementInfo["movement_fromtime_hour"] + 12) . ":".$arrMovementInfo["movement_fromtime_minutes"];
				}
				else
				{
					$arrMovementInfo["movement_fromtime"] = $arrMovementInfo["movement_fromtime_hour"] . ":" . $arrMovementInfo["movement_fromtime_minutes"];
				}

				if($arrMovementInfo["movement_totime_ampm"] == "am" && $arrMovementInfo["movement_totime_hour"] == 12)
				{
					$arrMovementInfo["movement_totime"] = "00:".$arrMovementInfo["movement_totime_minutes"];
				}
				else if($arrMovementInfo["movement_totime_ampm"] == "pm" && $arrMovementInfo["movement_totime_hour"] != 12)
				{
					$arrMovementInfo["movement_totime"] = ($arrMovementInfo["movement_totime_hour"] + 12) . ":".$arrMovementInfo["movement_totime_minutes"];
				}
				else
				{
					$arrMovementInfo["movement_totime"] = $arrMovementInfo["movement_totime_hour"] . ":" . $arrMovementInfo["movement_totime_minutes"];
				}

				if($arrMovementInfo["compensation_fromtime_ampm"] == "am" && $arrMovementInfo["compensation_fromtime_hour"] == 12)
				{
					$arrMovementInfo["compensation_fromtime"] = "00:".$arrMovementInfo["compensation_fromtime_minutes"];
				}
				else if($arrMovementInfo["compensation_fromtime_ampm"] == "pm" && $arrMovementInfo["compensation_fromtime_hour"] != 12)
				{
					$arrMovementInfo["compensation_fromtime"] = ($arrMovementInfo["compensation_fromtime_hour"] + 12) . ":".$arrMovementInfo["compensation_fromtime_minutes"];
				}
				else
				{
					$arrMovementInfo["compensation_fromtime"] = $arrMovementInfo["compensation_fromtime_hour"] . ":" . $arrMovementInfo["compensation_fromtime_minutes"];
				}

				if($arrMovementInfo["compensation_totime_ampm"] == "am" && $arrMovementInfo["compensation_totime_hour"] == 12)
				{
					$arrMovementInfo["compensation_totime"] = "00:".$arrMovementInfo["compensation_totime_minutes"];
				}
				else if($arrMovementInfo["compensation_totime_ampm"] == "pm" && $arrMovementInfo["compensation_totime_hour"] != 12)
				{
					$arrMovementInfo["compensation_totime"] = ($arrMovementInfo["compensation_totime_hour"] + 12) . ":".$arrMovementInfo["compensation_totime_minutes"];
				}
				else
				{
					$arrMovementInfo["compensation_totime"] = $arrMovementInfo["compensation_totime_hour"] . ":" . $arrMovementInfo["compensation_totime_minutes"];
				}

				//print_r($arrMovementInfo);die;

				$arrMovementInfo["addedon"] = date('Y-m-d H:i:s');
				$arrMovementInfo["isemergency"] = 1;
				$arrMovementInfo["emergencysmaddedby"] = $_SESSION["id"];
				$arrMovementInfo["isadminadded"] = 1;
				$arrMovementInfo["approvedby_tl"] = 0;
				$arrMovementInfo["approvedby_manager"] = 1;

				include_once('class.employee.php');

				$objEmployee = new employee();

				$reportingHead = $objEmployee->fnGetReportingHeadId($arrMovementInfo["userid"]);

				$arrMovementInfo["reportinghead1"] = $reportingHead;

				$reportinghead2 = $objEmployee->fnGetReportingHeadId($reportingHead);

				$arrMovementInfo["reportinghead2"] = $reportinghead2;

				/* BEGIN - Check if delegated to other tl, add the delegated TL */

				include_once("class.leave.php");

				$objLeave = new leave();

				/* get user designation */
				$head_designation = $objEmployee->fnGetEmployeeDesignation($reportingHead);
				if($head_designation == "6" || $head_designation == "18" || $head_designation == "19")
					$checkDeligateReportingHead1Id = $objLeave->fnCheckDeligateManager($reportingHead);
				else
					$checkDeligateReportingHead1Id = $objLeave->fnCheckDeligate($reportingHead);

				$checkDeligateReportingHead2Id = $objLeave->fnCheckDeligateManager($reportinghead2);

				$delegateReportingHead1 = 0;
				if(isset($checkDeligateReportingHead1Id) && $checkDeligateReportingHead1Id != '')
				{
					$delegateReportingHead1 = $checkDeligateReportingHead1Id;
				}

				$delegateReportingHead2 = 0;
				if(isset($delegateReportingHead2) && $checkDeligateReportingHead2Id != '')
				{
					$delegateReportingHead2 = $checkDeligateReportingHead2Id;
				}

				$arrMovementInfo["delegatedtl_id"] = $delegateReportingHead1;
				$arrMovementInfo["delegatedtl_status"] = 0;
				$arrMovementInfo["delegatedmanager_id"] = $delegateReportingHead2;
				$arrMovementInfo["delegatedmanager_status"] = 0;

				/* END - Check if delegated to other tl, add the delegated TL */
				$id = $this->insertArray('pms_shift_movement',$arrMovementInfo);

				/* add data to attendance */

				$arrInfo["user_id"] = $arrMovementInfo["userid"];
				$arrInfo["date"] = $arrMovementInfo["movement_date"];
				$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("SM");
				$objAttendance->fnInsertRosterAttendance($arrInfo);

				$employeeInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["userid"]);
				$HeadInfo = $objEmployee->fnGetEmployeeDetailById($reportingHead);

				$arrHeads = $objEmployee->fnGetReportingHeads($employeeInfo["id"]);

				$Subject = "Admin Shift Movement Request";

				/* Send mail to the employee for whome the leave is added */
				$empContent  = "Dear ".$employeeInfo["name"].",<br><br>Admin has added a shift movement for you. The details for his request are as follows:<br/><br/><table cellspacing='2' cellpadding='2' bgcolor='#E6E6E6'>
					<tr bgcolor='#FFFFFF'>
						<td><b>Reporting Head: </b></td>
						<td>".$HeadInfo["name"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>Movement Date: </b></td>
						<td>".$arrMovementInfo["movement_date"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>Requested Movement time: </b></td>
						<td>".$arrMovementInfo["movement_fromtime"]." - ".$arrMovementInfo["movement_totime"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>He/She has proposed to Compensate on: </b></td>
						<td>".$arrMovementInfo["compensation_date"].", time: ".$arrMovementInfo["compensation_fromtime"]." - ".$arrMovementInfo["compensation_totime"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>Reason: </b></td>
						<td>".$arrMovementInfo["reason"]."</td>
					</tr>
				</table>
				<br><br>Regards,<br>".SITEADMINISTRATOR;
				
				sendmail($employeeInfo["email"], $Subject, $empContent);

				$tempContent = "Admin has added a shift movement for ".$employeeInfo["name"].". The details for his request are as follows:<br/><br/>";
				$tempContent .= "<table cellspacing='2' cellpadding='2' bgcolor='#E6E6E6'>
					<tr bgcolor='#FFFFFF'>
						<td><b>Employee Name: </b></td>
						<td>".$employeeInfo["name"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>Reporting Head: </b></td>
						<td>".$HeadInfo["name"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>Movement Date: </b></td>
						<td>".$arrMovementInfo["movement_date"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>Requested Movement time: </b></td>
						<td>".$arrMovementInfo["movement_fromtime"]." - ".$arrMovementInfo["movement_totime"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>He/She has proposed to Compensate on: </b></td>
						<td>".$arrMovementInfo["compensation_date"].", time: ".$arrMovementInfo["compensation_fromtime"]." - ".$arrMovementInfo["compensation_totime"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>Reason: </b></td>
						<td>".$arrMovementInfo["reason"]."</td>
					</tr>
				</table>";

				if(count($arrHeads) > 0)
				{
					foreach($arrHeads as $curHead)
					{
						//if(($employeeInfo["designation"] == "6" || $employeeInfo["designation"] == "18" || $employeeInfo["designation"] == "19" || $employeeInfo["designation"] == "25" || $employeeInfo["designation"] == "44" || $employeeInfo["designation"] == "17"))
						//{
							/* Do not send mail to admin and CEO */
							$MailTo = $curHead["email"];

							$content = "Dear ".$curHead["name"].",<br><br>".$tempContent;
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

							sendmail($MailTo, $Subject, $content);
						//}
					}

					if($arrMovementInfo["delegatedtl_id"] != 0)
					{
						$DelegatedTL = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedtl_id"]);
						$MailTo = $DelegatedTL["email"];

						$content = "Dear ".$DelegatedTL["name"].",<br><br>".$tempContent;
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if($arrMovementInfo["delegatedmanager_id"] != 0)
					{
						$DelegatedTL = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedmanager_id"]);
						$MailTo = $DelegatedTL["email"];

						$content = "Dear ".$DelegatedTL["name"].",<br><br>".$tempContent;
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}
				}

				return 1;
			}
			else
			{
				return 0;
			}
		}

		function fnSaveShiftMovementCompensation($arrMovementInfo)
		{			
			/* Cannot add shift movement compensation after 7 days */
			$movementDetails = $this->fnShiftMovementById($arrMovementInfo["shift_movement_id"]);

			if(strtotime('+7 day', strtotime($movementDetails["movementdate"]." 00:00:00")) < strtotime(Date('Y-m-d')." 00:00:00"))
			{
				return false;
			}

			if($arrMovementInfo["compensation_fromtime_ampm"] == "am" && $arrMovementInfo["compensation_fromtime_hour"] == 12)
			{
				//$arrMovementInfo["compensation_fromtime"] = ($arrMovementInfo["compensation_fromtime_hour"] + 12) . ":".$arrMovementInfo["compensation_fromtime_minutes"];
				$arrMovementInfo["compensation_fromtime"] = "00:".$arrMovementInfo["compensation_fromtime_minutes"];
			}
			else if($arrMovementInfo["compensation_fromtime_ampm"] == "pm" && $arrMovementInfo["compensation_fromtime_hour"] != 12)
			{
				$arrMovementInfo["compensation_fromtime"] = ($arrMovementInfo["compensation_fromtime_hour"] + 12) . ":".$arrMovementInfo["compensation_fromtime_minutes"];
			}
			else
			{
				$arrMovementInfo["compensation_fromtime"] = $arrMovementInfo["compensation_fromtime_hour"] . ":" . $arrMovementInfo["compensation_fromtime_minutes"];
			}

			if($arrMovementInfo["compensation_totime_ampm"] == "am" && $arrMovementInfo["compensation_totime_hour"] == 12)
			{
				//$arrMovementInfo["compensation_totime"] = ($arrMovementInfo["compensation_totime_hour"] + 12) . ":".$arrMovementInfo["compensation_totime_minutes"];
				$arrMovementInfo["compensation_totime"] = "00:".$arrMovementInfo["compensation_totime_minutes"];
			}
			else if($arrMovementInfo["compensation_totime_ampm"] == "pm" && $arrMovementInfo["compensation_totime_hour"] != 12)
			{
				$arrMovementInfo["compensation_totime"] = ($arrMovementInfo["compensation_totime_hour"] + 12) . ":".$arrMovementInfo["compensation_totime_minutes"];
			}
			else
			{
				$arrMovementInfo["compensation_totime"] = $arrMovementInfo["compensation_totime_hour"] . ":" . $arrMovementInfo["compensation_totime_minutes"];
			}

			$arrMovementInfo["userid"] = $_SESSION["id"];
			$arrMovementInfo["addedon"] = date('Y-m-d H:i:s');
			$arrMovementInfo["approvedby_tl"] = 0;

			include_once('class.employee.php');

			$objEmployee = new employee();

			$reportingHead = $objEmployee->fnGetReportingHeadId($_SESSION["id"]);

			$arrMovementInfo["firstreportingheadid"] = $reportingHead;

			$reportinghead2 = $objEmployee->fnGetReportingHeadId($reportingHead);

			$arrMovementInfo["secondreportingheadid"] = $reportinghead2;
			
			$arrMovementInfo["tlapprovalcode"] = shiftmovementcompensationform_uid();

			/* BEGIN - Check if delegated to other tl, add the delegated TL */

			include_once("class.leave.php");

			$objLeave = new leave();

			/* get user designation */
			$head_designation = $objEmployee->fnGetEmployeeDesignation($reportingHead);
			if($head_designation == "6" || $head_designation == "18" || $head_designation == "19")
				$checkDeligateReportingHead1Id = $objLeave->fnCheckDeligateManager($reportingHead);
			else
				$checkDeligateReportingHead1Id = $objLeave->fnCheckDeligate($reportingHead);

			$delegateReportingHead1 = 0;
			if(isset($checkDeligateReportingHead1Id) && $checkDeligateReportingHead1Id != '')
			{
				$delegateReportingHead1 = $checkDeligateReportingHead1Id;
			}

			$arrMovementInfo["delegatedtl_id"] = $delegateReportingHead1;
			$arrMovementInfo["delegatedtl_status"] = 0;

			if($arrMovementInfo["delegatedtl_id"] != "")
				$arrMovementInfo["delegatedtlapprovalcode"] = shiftmovementcompensationform_uid();

			/* END - Check if delegated to other tl, add the delegated TL */
			
			/* Insert shift movement compensation */
			$id = $this->insertArray('pms_shift_movement_compensation',$arrMovementInfo);

			$employeeInfo = $objEmployee->fnGetEmployeeDetailById($_SESSION["id"]);
			$tlInfo = $objEmployee->fnGetEmployeeDetailById($reportingHead);

			/* Mail */
			
			/* Common mail content */
			
			$tempContent = $employeeInfo["name"]." has added a compensation for his/her shift movement on ".$movementDetails["movementdate"].". The details for the shift movement compensation are as follows:<br/><br/>";
			$tempContent .= "<table cellspacing='2' cellpadding='2' bgcolor='#E6E6E6'>
				<tr bgcolor='#FFFFFF'>
					<td><b>Employee Name: </b></td>
					<td>".$employeeInfo["name"]."</td>
				</tr>
				<tr bgcolor='#FFFFFF'>
					<td><b>Reporting Head: </b></td>
					<td>".$tlInfo["name"]."</td>
				</tr>
				<tr bgcolor='#FFFFFF'>
					<td><b>Movement On: </b></td>
					<td>".$movementDetails["movementdate"].", ".$movementDetails["movementfrom"]." - ".$movementDetails["movementto"]."</td>
				</tr>
				<tr bgcolor='#FFFFFF'>
					<td><b>Compensation On: </b></td>
					<td>".$arrMovementInfo["compensation_date"].", ".$arrMovementInfo["compensation_fromtime"]." - ".$arrMovementInfo["compensation_totime"]."</td>
				</tr>";
			
			$Subject = "Shift Movement Compensation Request";
			
			/* Send mail to team leader */
			if(count($tlInfo) > 0)
			{
				$uniqueCode = $arrMovementInfo["tlapprovalcode"];
				
				$MailTo = $tlInfo["email"];
				
				$content = "Dear ".$tlInfo["name"].",<br><br>".$tempContent;

				if($uniqueCode != "")
				{
					$content .= "<tr bgcolor='#FFFFFF'>
									<td colspan='2'>
										Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Approve_SMC]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Reject_SMC]'>Reject</a></b> for letting us know your decision.
									</td>
								</tr>";
				}

				/*"<!--tr bgcolor='#FFFFFF'>
					<td colspan='2'>To approve / unapprove the shift movement please click <a href='".SERVERURL."shift_movement_request_view.php?id=".$id."'>here</a></td>
				</tr-->";*/

				$content .= "</table>";
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

				sendmail($MailTo, $Subject, $content);
				//sendmail1("chandni.patel@transformsolution.net",$Subject,$content);
			}

			/* Send mail to delegated team leader */
			if($arrMovementInfo["delegatedtl_id"] != 0)
			{
				$DelegatedTL = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedtl_id"]);
				$MailTo = $DelegatedTL["email"];
				
				$content = "Dear ".$DelegatedTL["name"].",<br><br>".$tempContent;
				
				if($uniqueCode != "")
				{
					$content .= "<tr bgcolor='#FFFFFF'>
									<td colspan='2'>
										Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Approve_SMC]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Reject_SMC]'>Reject</a></b> for letting us know your decision.
									</td>
								</tr>";
				}

				$content .= "</table>";
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

				sendmail($MailTo, $Subject, $content);
			}

			return true;
		}

		function fnCheckShiftMovementEligable($EmployeeId, $MovementDate)
		{
			/*$sSQL = "select * from pms_leave_form where '$MovementDate' BETWEEN date_format(start_date,'%Y-%m-%d') AND date_format(end_date,'%Y-%m-%d') and employee_id='$EmployeeId' and ((status='1' and status_manager='0') or status_manager='1' or (status='0' and status_manager='0'))";*/
			
			/* check if leaves for the user is added */
			
			//$sSQL = "select * from pms_leave_form where '$MovementDate' BETWEEN date_format(start_date,'%Y-%m-%d') AND date_format(end_date,'%Y-%m-%d') and employee_id='$EmployeeId' and (status_manager IN(0,1) or (status_manager='0' and deligateManagerId != 0 and manager_delegate_status in (0,1)))";
			
			//$sSQL = "select * from pms_leave_form where '$MovementDate' BETWEEN date_format(start_date,'%Y-%m-%d') AND date_format(end_date,'%Y-%m-%d') and employee_id='$EmployeeId' and (status_manager IN(0,1) or (status_manager='0' and deligateManagerId != 0 and manager_delegate_status in (0,1))) and ((status!=2 or (deligateTeamLeaderId != '' and manager_delegate_status=2 )) and (status_manager='0' or (deligateManagerId != 0 and manager_delegate_status ='0')))";
			
			$sSQL = "select * from pms_leave_form where '$MovementDate' BETWEEN date_format(start_date,'%Y-%m-%d') AND date_format(end_date,'%Y-%m-%d') and employee_id='$EmployeeId' and (status_manager IN(0,1) or (status_manager='0' and deligateManagerId != 0 and manager_delegate_status in (0,1))) and id not in (select id  from pms_leave_form where '$MovementDate' BETWEEN date_format(start_date,'%Y-%m-%d') AND date_format(end_date,'%Y-%m-%d') and employee_id='$EmployeeId' and ((status=2 or (status='0' and deligateTeamLeaderId != '0' and delegate_status=2 )) and (status_manager='0' and manager_delegate_status ='0')))";
			
			$this->query($sSQL);
			if($this->num_rows() > 0)
				return false;
			else
			{
				//$sSQL = "select * from pms_half_leave_form where date_format(start_date,'%Y-%m-%d') = '$MovementDate' and employee_id='$EmployeeId' and (status_manager IN(0,1) or (status_manager='0' and deligateManagerId != 0 and manager_delegate_status in (0,1))) and ((status!=2 or (deligateTeamLeaderId != '' and manager_delegate_status=2 )) and (status_manager='0' or (deligateManagerId != 0 and manager_delegate_status ='0')))";
				
				$sSQL = "select * from pms_half_leave_form where date_format(start_date,'%Y-%m-%d') = '$MovementDate' and employee_id='$EmployeeId' and (((status_manager IN(0,1) or (status_manager='0' and deligateManagerId != 0 and manager_delegate_status in (0,1))) and isactive=0) or (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1'))) and id not in (select id from pms_half_leave_form where date_format(start_date,'%Y-%m-%d') = '$MovementDate' and employee_id='$EmployeeId' and ((status=2 or (status='0' and deligateTeamLeaderId != '0' and delegate_status=2 )) and (status_manager='0' and manager_delegate_status ='0')))";
			
				$this->query($sSQL);
				if($this->num_rows() > 0)
					return false;
				else
				{
					/*$sSQL = "select * from pms_shift_movement where date_format(movement_date,'%Y-%m-%d') = '$MovementDate' and userid='$EmployeeId' and ((approvedby_tl='1' and approvedby_manager='0') or approvedby_manager='1' or (approvedby_tl='0' and approvedby_manager='0')) and isCancel='0'";*/
					
					/* check if shift movement for the user is added */
					
					//$sSQL = "select * from pms_shift_movement where date_format(movement_date,'%Y-%m-%d') = '$MovementDate' and userid='$EmployeeId' and (approvedby_manager in (0,1) or (approvedby_manager='0' and delegatedmanager_id!='0' and delegatedmanager_status in (0,1))) and isCancel='0'";
					//$sSQL = "select * from pms_shift_movement where date_format(movement_date,'%Y-%m-%d') = '$MovementDate' and userid='$EmployeeId' and (approvedby_manager in (0,1) or (approvedby_manager='0' and delegatedmanager_id!='0' and delegatedmanager_status in (0,1))) and isCancel='0'";
					
					//$sSQL = "select * from pms_shift_movement where date_format(movement_date,'%Y-%m-%d') = '$MovementDate' and userid='$EmployeeId' and (approvedby_manager in (0,1) or (approvedby_manager='0' and delegatedmanager_id!='0' and delegatedmanager_status in (0,1))) and ((approvedby_manager='0' or (delegatedmanager_id!='0' && delegatedmanager_status='0')) and (approvedby_tl!='2' and delegatedtl_status!='2')) and isCancel='0'";
					
					//$sSQL = "select * from pms_shift_movement where date_format(movement_date,'%Y-%m-%d') = '$MovementDate' and userid='$EmployeeId' and (approvedby_manager in (0,1) or (approvedby_manager='0' and delegatedmanager_id!='0' and delegatedmanager_status in (0,1))) and ((approvedby_manager='0' or (delegatedmanager_id!='0' && delegatedmanager_status='0'))) and id not in (select id from pms_shift_movement where date_format(movement_date,'%Y-%m-%d') = '$MovementDate' and userid='$EmployeeId' and (approvedby_tl='2' or delegatedtl_status='2') and approvedby_manager='0' and delegatedmanager_status='0') and isCancel='0'";

					$sSQL = "select * from pms_shift_movement where date_format(movement_date,'%Y-%m-%d') = '$MovementDate' and userid='$EmployeeId' and (approvedby_manager in (0,1) or (approvedby_manager='0' and delegatedmanager_id!='0' and delegatedmanager_status in (0,1))) and id not in (select id from pms_shift_movement where date_format(movement_date,'%Y-%m-%d') = '$MovementDate' and userid='$EmployeeId' and (approvedby_tl='2' or delegatedtl_status='2') and approvedby_manager='0' and delegatedmanager_status='0') and isCancel='0'";
					
					$this->query($sSQL);
					if($this->num_rows() > 0)
						return false;
					else
						return true;
				}
			}
		}

		function fnCheckAdminShiftMovementEligable($EmployeeId, $MovementDate)
		{
			//echo $sSQL = "select * from pms_shift_movement where date_format(movement_date,'%Y-%m-%d') = '$MovementDate' and userid='$EmployeeId' and (approvedby_manager in (0,1) or (approvedby_manager='0' and delegatedmanager_id!='0' and delegatedmanager_status in (0,1))) and ((approvedby_manager='0' or (delegatedmanager_id!='0' && delegatedmanager_status='0'))) and id not in (select id from pms_shift_movement where date_format(movement_date,'%Y-%m-%d') = '$MovementDate' and userid='$EmployeeId' and (approvedby_tl='2' or delegatedtl_status='2') and approvedby_manager='0' and delegatedmanager_status='0') and isCancel='0'";die;
			$sSQL = "select * from pms_shift_movement where date_format(movement_date,'%Y-%m-%d') = '$MovementDate' and userid='$EmployeeId' and (approvedby_manager in (0,1) or (approvedby_manager='0' and delegatedmanager_id!='0' and delegatedmanager_status in (0,1)))  and id not in (select id from pms_shift_movement where date_format(movement_date,'%Y-%m-%d') = '$MovementDate' and userid='$EmployeeId' and (approvedby_tl='2' or delegatedtl_status='2') and approvedby_manager='0' and delegatedmanager_status='0') and isCancel='0'";
			
			$this->query($sSQL);
			if($this->num_rows() > 0)
				return false;
			else
				return true;
		}

		function fnValidateShiftMovement($EmployeeId, $MovementDate)
		{
			$arrDt = explode("-",$MovementDate);

			$CheckingDt = $arrDt[0]."-".$arrDt[1];

			$movements = 0;
			$movementsPlt = 0;
			$movementPending = 0;
			$totalShiftMovement = 0;
			
			/*$sSQL = "select * from pms_shift_movement where userid='$EmployeeId' and date_format(movement_date,'%Y-%m') = '$CheckingDt' and (approvedby_manager = '1' or (approvedby_manager='0' && approvedby_tl='1'))";*/
			
			/* Fetch approved shift movements */
			$sSQL = "select * from pms_shift_movement where userid='$EmployeeId' and date_format(movement_date,'%Y-%m') = '$CheckingDt' and (approvedby_manager = '1' or (approvedby_manager = '0' and delegatedmanager_id!='0' and delegatedmanager_status='1')) and isCancel='0'";
			$this->query($sSQL);
			$movements = $this->num_rows();

			/* Fetch all shift movement + PLT's */
			$sSQL = "select * from pms_attendance where user_id='$EmployeeId' and date_format(date,'%Y-%m') = '$CheckingDt' and leave_id='11'";
			$this->query($sSQL);
			$movementsPlt = $this->num_rows();

			/* Fetch all the shift movements added and yet pending */
			$sSQL = "select * from pms_shift_movement where userid='".mysql_real_escape_string($EmployeeId)."' and date_format(movement_date,'%Y-%m')='".mysql_real_escape_string($CheckingDt)."' and approvedby_manager='0' and delegatedmanager_status='0' and isactive='0' and approvedby_tl != 2 and delegatedtl_status!='2' and isCancel='0'";
			$this->query($sSQL);
			$movementPending = $this->num_rows();

			$totalShiftMovement = $movements + $movementsPlt + $movementPending;

			return $totalShiftMovement;
		}

		function fnUserShiftMovement($UserId)
		{
			/* Fetch all shift movement for the user */
			$arrMovements = array();

			$sSQL = "select id, date_format(movement_date,'%d-%m-%Y') as movementdate, date_format(compensation_date,'%d-%m-%Y') as compensationdate, date_format(movement_fromtime,'%H:%i') as movementfrom, date_format(movement_totime,'%H:%i') as movementto, date_format(compensation_fromtime,'%H:%i') as compensationfrom, date_format(compensation_totime,'%H:%i') as compensationto, approvedby_tl, approvedby_manager,isCancel, isemergency, if(isemergency=1,'Yes','No') as emergencytext from pms_shift_movement where userid='$UserId'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$row = $this->fetchrow();

					switch($this->f("approvedby_tl"))
					{
						case '0':
							$row["tlapproval"] = 'Pending';
							break;
						case '1':
							$row["tlapproval"] = 'Approved';
							break;
						case '2':
							$row["tlapproval"] = 'Unapproved';
							break;
					}

					switch($this->f("approvedby_manager"))
					{
						case '0':
							$row["managerapproval"] = 'Pending';
							break;
						case '1':
							$row["managerapproval"] = 'Approved';
							break;
						case '2':
							$row["managerapproval"] = 'Unapproved';
							break;
					}

					$arrMovements[] = $row;
				}
			}

			return $arrMovements;
		}

		function fnUserEmergencyShiftMovement($HeadId)
		{
			$arrMovements = array();

			/* Get all employees under logged in user */

			include_once("class.employee.php");

			$objEmployee = new employee();
			$arrEmployees = $objEmployee->fnGetAllemployees($_SESSION["id"]);

			include_once("class.employee.php");
			
			$objEmployee = new employee();
			
			$arrtemp = array();
			
			if($_SESSION["designation"] == "6" || $_SESSION["designation"] == "18" || $_SESSION["designation"] == "19")
			{
				/* Get Delegated Manager id */
				$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($_SESSION['id']);
				
				if(count($arrDelegatedManagerId) > 0 )
				{
					foreach($arrDelegatedManagerId as $delegatesManagerIds)
					{
						$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
						$arrEmployees = $arrEmployees + $arrtemp;
					}
				}
			}
			else if ($_SESSION["designation"] == "7" || $_SESSION["designation"] == "13")
			{
				/* Get delegated teamleader id */
				$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);	
				
				if(count($arrDelegatedTeamLeaderId) > 0 )
				{
					foreach($arrDelegatedTeamLeaderId as $delegatesIds)
					{
						$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
						$arrEmployees = $arrEmployees + $arrtemp;
					}
				}
			}
			
			if(count($arrEmployees) > 0)
			{
				$arrEmployees = array_filter($arrEmployees,'strlen');
			}
			
			$ids = "0";
			if(count($arrEmployees) > 0)
			{
				$ids .= ",".implode(",",$arrEmployees);
			}

			/* Get all emergency shift movements added by the team leader */

			$sSQL = "select e.name as employeename, sm.id, date_format(sm.movement_date,'%d-%m-%Y') as movementdate, date_format(sm.compensation_date,'%d-%m-%Y') as compensationdate, date_format(sm.movement_fromtime,'%H:%i') as movementfrom, date_format(sm.movement_totime,'%H:%i') as movementto, date_format(sm.compensation_fromtime,'%H:%i') as compensationfrom, date_format(sm.compensation_totime,'%H:%i') as compensationto, sm.approvedby_tl, sm.approvedby_manager, sm.isCancel, sm.isemergency from pms_shift_movement sm INNER JOIN pms_employee e ON e.id = sm.userid where sm.userid in ($ids) and isemergency='1' and e.status='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$row = $this->fetchrow();

					switch($this->f("approvedby_tl"))
					{
						case '0':
							$row["tlapproval"] = 'Pending';
							break;
						case '1':
							$row["tlapproval"] = 'Approved';
							break;
						case '2':
							$row["tlapproval"] = 'Unapproved';
							break;
					}

					switch($this->f("approvedby_manager"))
					{
						case '0':
							$row["managerapproval"] = 'Pending';
							break;
						case '1':
							$row["managerapproval"] = 'Approved';
							break;
						case '2':
							$row["managerapproval"] = 'Unapproved';
							break;
					}

					$arrMovements[] = $row;
				}
			}

			return $arrMovements;
		}
		
		function fnAdminShiftMovement()
		{
			$arrMovements = array();

			/* Get all employees under logged in user */

			include_once("class.employee.php");
			$objEmployee = new employee();

			/* Get all emergency shift movements added by the team leader */
			$sSQL = "select e.name as employeename, sm.id, date_format(sm.movement_date,'%d-%m-%Y') as movementdate, date_format(sm.compensation_date,'%d-%m-%Y') as compensationdate, date_format(sm.movement_fromtime,'%H:%i') as movementfrom, date_format(sm.movement_totime,'%H:%i') as movementto, date_format(sm.compensation_fromtime,'%H:%i') as compensationfrom, date_format(sm.compensation_totime,'%H:%i') as compensationto, sm.approvedby_tl, sm.approvedby_manager, sm.isCancel, sm.isemergency from pms_shift_movement sm INNER JOIN pms_employee e ON e.id = sm.userid where isadminadded='1' and e.status='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$row = $this->fetchrow();

					switch($this->f("approvedby_tl"))
					{
						case '0':
							$row["tlapproval"] = 'Pending';
							break;
						case '1':
							$row["tlapproval"] = 'Approved';
							break;
						case '2':
							$row["tlapproval"] = 'Unapproved';
							break;
					}

					switch($this->f("approvedby_manager"))
					{
						case '0':
							$row["managerapproval"] = 'Pending';
							break;
						case '1':
							$row["managerapproval"] = 'Approved';
							break;
						case '2':
							$row["managerapproval"] = 'Unapproved';
							break;
					}

					$arrMovements[] = $row;
				}
			}

			return $arrMovements;
		}

		function fnUserShiftMovementCompensation()
		{
			/* Get shift movement compensation for the logged in employee */
			
			$arrMovements = array();

			$sSQL = "select smc.id as compensationid, date_format(sm.movement_date,'%d-%m-%Y') as movementdate, date_format(sm.movement_fromtime,'%H:%i') as movementfrom, date_format(sm.movement_totime,'%H:%i') as movementto, date_format(smc.compensation_date,'%d-%m-%Y') as compensationdate, date_format(smc.compensation_fromtime,'%H:%i') as compensationfrom, date_format(smc.compensation_totime,'%H:%i') as compensationto, smc.approvedby_tl from pms_shift_movement sm INNER JOIN pms_shift_movement_compensation smc ON sm.id = smc.shift_movement_id where smc.userid='".mysql_real_escape_string($_SESSION["id"])."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$row = $this->fetchrow();

					switch($this->f("approvedby_tl"))
					{
						case '0':
							$row["tlapproval"] = 'Pending';
							break;
						case '1':
							$row["tlapproval"] = 'Approved';
							break;
						case '2':
							$row["tlapproval"] = 'Unapproved';
							break;
					}

					$arrMovements[] = $row;
				}
			}

			return $arrMovements;
		}

		function fnUserShiftMovementCompensationById($CompensationId)
		{
			$arrMovements = array();
			
			/* Get shift movement compensation for the logged in employee by compensation id */
			
			$sSQL = "select smc.id as compensationid, date_format(sm.movement_date,'%Y-%m-%d') as movementdate, date_format(sm.movement_fromtime,'%H:%i') as movementfrom, date_format(sm.movement_totime,'%H:%i') as movementto, date_format(smc.compensation_date,'%Y-%m-%d') as compensationdate, date_format(smc.compensation_fromtime,'%H:%i') as compensationfrom, date_format(smc.compensation_totime,'%H:%i') as compensationto, smc.approvedby_tl, sm.reason, smc.tl_comment, smc.delegatedtl_id, smc.delegatedtl_status, smc.delegatedtl_comment from pms_shift_movement sm INNER JOIN pms_shift_movement_compensation smc ON sm.id = smc.shift_movement_id where smc.userid='".mysql_real_escape_string($_SESSION["id"])."' and smc.id='$CompensationId'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$row = $this->fetchrow();

					switch($this->f("approvedby_tl"))
					{
						case '0':
							$row["tlapproval"] = 'Pending';
							break;
						case '1':
							$row["tlapproval"] = 'Approved';
							break;
						case '2':
							$row["tlapproval"] = 'Unapproved';
							break;
					}
					
					switch($this->f("delegatedtl_status"))
					{
						case '0':
							$row["delegatedtlapproval"] = 'Pending';
							break;
						case '1':
							$row["delegatedtlapproval"] = 'Approved';
							break;
						case '2':
							$row["delegatedtlapproval"] = 'Unapproved';
							break;
					}

					$arrMovements = $row;
				}
			}

			return $arrMovements;
		}

		function fnShiftMovementCompensationById($CompensationId)
		{
			/* Get shift movement compensation for the employee by compensation id */
			
			$arrMovements = array();

			include_once("class.employee.php");
			
			$objEmployee = new employee();
			
			$arrtemp = array();
			$arrEmployee = array();

			if($_SESSION["designation"] == "6" || $_SESSION["designation"] == "18" || $_SESSION["designation"] == "19")
			{
				/* Get Delegated Manager id */
				$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($_SESSION['id']);
				
				if(count($arrDelegatedManagerId) > 0 )
				{
					foreach($arrDelegatedManagerId as $delegatesManagerIds)
					{
						$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
						$arrEmployee = $arrEmployee + $arrtemp;
					}
				}
			}
			else if ($_SESSION["designation"] == "7" || $_SESSION["designation"] == "13")
			{
				/* Get delegated teamleader id */
				$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);	
				
				if(count($arrDelegatedTeamLeaderId) > 0 )
				{
					foreach($arrDelegatedTeamLeaderId as $delegatesIds)
					{
						$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
						$arrEmployee = $arrEmployee + $arrtemp ;
					}
				}
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

			$sSQL = "select smc.id as compensationid, date_format(sm.movement_date,'%Y-%m-%d') as movementdate, date_format(sm.movement_fromtime,'%H:%i') as movementfrom, date_format(sm.movement_totime,'%H:%i') as movementto, date_format(smc.compensation_date,'%Y-%m-%d') as compensationdate, date_format(smc.compensation_fromtime,'%H:%i') as compensationfrom, date_format(smc.compensation_totime,'%H:%i') as compensationto, smc.approvedby_tl, sm.reason, e.name as employeename,e.id as employeeid, smc.tl_comment, smc.delegatedtl_status, smc.delegatedtl_comment, smc.delegatedtl_id from pms_shift_movement sm INNER JOIN pms_shift_movement_compensation smc ON sm.id = smc.shift_movement_id INNER JOIN pms_employee e ON e.id = smc.userid where (e.teamleader='".mysql_real_escape_string($_SESSION["id"])."' or smc.userid in ($ids)) and smc.id='$CompensationId'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$row = $this->fetchrow();

					switch($this->f("approvedby_tl"))
					{
						case '0':
							$row["tlapproval"] = 'Pending';
							break;
						case '1':
							$row["tlapproval"] = 'Approved';
							break;
						case '2':
							$row["tlapproval"] = 'Unapproved';
							break;
					}

					switch($this->f("delegatedtl_status"))
					{
						case '0':
							$row["delegatedtlapproval"] = 'Pending';
							break;
						case '1':
							$row["delegatedtlapproval"] = 'Approved';
							break;
						case '2':
							$row["delegatedtlapproval"] = 'Unapproved';
							break;
					}

					$arrMovements = $row;
				}
			}

			return $arrMovements;
		}

		function fnUserShiftMovementById($ShiftMovementId)
		{
			$MovementInfo = false;

			/* Get shift movement of the logged in user by id */

			$sSQL = "select date_format(m.movement_date,'%d-%m-%Y') as movementdate, date_format(m.compensation_date,'%d-%m-%Y') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, m.reason, m.lt_comments, m.manager_comments, m.delegatedmanager_id, m.delegatedtl_id, m.delegatedtl_status, m.delegatedmanager_status, m.delegatedmanager_comment, m.delegatedtl_comment, m.isadminadded from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid where m.id='".mysql_real_escape_string($ShiftMovementId)."' and m.userid='".mysql_real_escape_string($_SESSION["id"])."' and e.status='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$row = $this->fetchrow();

					switch($this->f("approvedby_tl"))
					{
						case '0':
							$row["tlapproval"] = 'Pending';
							break;
						case '1':
							$row["tlapproval"] = 'Approved';
							break;
						case '2':
							$row["tlapproval"] = 'Unapproved';
							break;
					}

					switch($this->f("approvedby_manager"))
					{
						case '0':
							$row["managerapproval"] = 'Pending';
							break;
						case '1':
							$row["managerapproval"] = 'Approved';
							break;
						case '2':
							$row["managerapproval"] = 'Unapproved';
							break;
					}

					switch($this->f("delegatedtl_status"))
					{
						case '0':
							$row["delegatedtlapproval"] = 'Pending';
							break;
						case '1':
							$row["delegatedtlapproval"] = 'Approved';
							break;
						case '2':
							$row["delegatedtlapproval"] = 'Unapproved';
							break;
					}

					switch($this->f("delegatedmanager_status"))
					{
						case '0':
							$row["delegatedmanagerapproval"] = 'Pending';
							break;
						case '1':
							$row["delegatedmanagerapproval"] = 'Approved';
							break;
						case '2':
							$row["delegatedmanagerapproval"] = 'Unapproved';
							break;
					}

					$MovementInfo = $row;
				}
			}

			return $MovementInfo;
		}

		function fnUserShiftMovementById1($ShiftMovementId)
		{
			$MovementInfo = false;

			/* Get shift movement of the logged in user by id */

			$sSQL = "select date_format(m.movement_date,'%Y-%m-%d') as movementdate, date_format(m.compensation_date,'%Y-%m-%d') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, m.reason, m.lt_comments, m.manager_comments, m.delegatedmanager_id, m.delegatedtl_id, m.delegatedtl_status, m.delegatedmanager_status, m.delegatedmanager_comment, m.delegatedtl_comment,m.userid as user_id from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid where m.id='".mysql_real_escape_string($ShiftMovementId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$row = $this->fetchrow();

					switch($this->f("approvedby_tl"))
					{
						case '0':
							$row["tlapproval"] = 'Pending';
							break;
						case '1':
							$row["tlapproval"] = 'Approved';
							break;
						case '2':
							$row["tlapproval"] = 'Unapproved';
							break;
					}

					switch($this->f("approvedby_manager"))
					{
						case '0':
							$row["managerapproval"] = 'Pending';
							break;
						case '1':
							$row["managerapproval"] = 'Approved';
							break;
						case '2':
							$row["managerapproval"] = 'Unapproved';
							break;
					}

					switch($this->f("delegatedtl_status"))
					{
						case '0':
							$row["delegatedtlapproval"] = 'Pending';
							break;
						case '1':
							$row["delegatedtlapproval"] = 'Approved';
							break;
						case '2':
							$row["delegatedtlapproval"] = 'Unapproved';
							break;
					}

					switch($this->f("delegatedmanager_status"))
					{
						case '0':
							$row["delegatedmanagerapproval"] = 'Pending';
							break;
						case '1':
							$row["delegatedmanagerapproval"] = 'Approved';
							break;
						case '2':
							$row["delegatedmanagerapproval"] = 'Unapproved';
							break;
					}

					$MovementInfo = $row;
				}
			}

			return $MovementInfo;
		}

		function fnUpdateShiftMovementCompensation($ApprovalInfo)
		{
			/* approval / unapproval of shift movement compensation */
			include_once('class.employee.php');
			$objEmployee = new employee();

			$status = array("0"=>"Pending", "1"=>"Approved", "2"=>"Unapproved");

			$compensationInfo = $this->fnShiftMovementCompensationById($ApprovalInfo["id"]);

			$EmployeeInfo = $objEmployee->fnGetEmployeeById($compensationInfo["employeeid"]);

			if($compensationInfo["delegatedtl_id"] == $_SESSION["id"])
			{
				/* If logged in user is delegated team leader */
				$DelegatedTlInfo = $objEmployee->fnGetEmployeeById($_SESSION["id"]);
				$ApprovalInfo["delegatedtl_date"] = Date("Y-m-d H:i:s");
				$this->updateArray('pms_shift_movement_compensation',$ApprovalInfo);
				
				if($EmployeeInfo["teamleader_id"] != "" && $EmployeeInfo["teamleader_id"] != "0")
				{
					$TlInfo = $objEmployee->fnGetEmployeeById($EmployeeInfo["teamleader_id"]);

					/* Send mail to employee who has added shift movement complensation */
					$MailTo = $EmployeeInfo["email"];
					$Subject = "Shift Movement Compensation ".$status[$ApprovalInfo["delegatedtl_status"]];

					$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
					$content .= $DelegatedTlInfo["name"]." has ".strtoupper($status[$ApprovalInfo["delegatedtl_status"]])." your shift movement compensation request for shift movement on ".$compensationInfo["movementdate"]." for the time ".$compensationInfo["movementfrom"]." to ".$compensationInfo["movementto"]." compensated on ".$compensationInfo["compensationdate"]." from the time ".$compensationInfo["compensationfrom"]." to ".$compensationInfo["compensationto"]."<br/><br/>";
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);

					/* Send mail to teamleader */
					$MailTo = $TlInfo["email"];
					$content = "Dear ".$TlInfo["name"].",<br><br>";
					$content .= $DelegatedTlInfo["name"]." has ".strtoupper($status[$ApprovalInfo["delegatedtl_status"]])." shift movement compensation request of ".$EmployeeInfo["name"]." for shift movement on ".$compensationInfo["movementdate"]." for the time ".$compensationInfo["movementfrom"]." to ".$compensationInfo["movementto"]." compensated on ".$compensationInfo["compensationdate"]." from ".$compensationInfo["compensationfrom"]." to ".$compensationInfo["compensationto"]."<br/><br/>";
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);

					return $ApprovalInfo["delegatedtl_status"];
				}
				else
					return -1;
			}
			else
			{
				/* If logged in user is team leader */
				if($EmployeeInfo["teamleader_id"] != "" && $EmployeeInfo["teamleader_id"] != "0")
				{
					$TlInfo = $objEmployee->fnGetEmployeeById($EmployeeInfo["teamleader_id"]);
					$ApprovalInfo["tl_approveddate"] = Date("Y-m-d H:i:s");
					$this->updateArray('pms_shift_movement_compensation',$ApprovalInfo);

					/* Send mail to the employee */
					$MailTo = $EmployeeInfo["email"];
					$Subject = "Shift Movement Compensation ".$status[$ApprovalInfo["approvedby_tl"]];

					$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
					$content .= $TlInfo["name"]." has ".strtoupper($status[$ApprovalInfo["approvedby_tl"]])." your shift movement compensation request for shift movement on ".$compensationInfo["movementdate"]." for the time ".$compensationInfo["movementfrom"]." to ".$compensationInfo["movementto"]." compensated on ".$compensationInfo["compensationdate"]." from ".$compensationInfo["compensationfrom"]." to ".$compensationInfo["compensationto"]."<br/><br/>";
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);

					/* Send mail to delegated team leader */
					if($compensationInfo["delegatedtl_id"] != 0 && $compensationInfo["delegatedtl_id"] != "")
					{
						$DelegatedTlInfo = $objEmployee->fnGetEmployeeById($compensationInfo["delegatedtl_id"]);

						$MailTo = $DelegatedTlInfo["email"];
						$content = "Dear ".$DelegatedTlInfo["name"].",<br><br>";
						$content .= $TlInfo["name"]." has ".strtoupper($status[$ApprovalInfo["approvedby_tl"]])." shift movement compensation request of ".$EmployeeInfo["name"]." for shift movement on ".$compensationInfo["movementdate"]." for the time ".$compensationInfo["movementfrom"]." to ".$compensationInfo["movementto"]." compensated on ".$compensationInfo["compensationdate"]." from ".$compensationInfo["compensationfrom"]." to ".$compensationInfo["compensationto"]."<br/><br/>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
						
						sendmail($MailTo, $Subject, $content);
					}

					return $ApprovalInfo["approvedby_tl"];
				}
				else
					return -1;
			}
		}

		function fnUserEmergencyShiftMovementById($ShiftMovementId)
		{
			$MovementInfo = false;
			/* Fetch emergency shift movement added by the logged in user and id */
			$sSQL = "select e.name as employeename, date_format(m.movement_date,'%Y-%m-%d') as movementdate, date_format(m.compensation_date,'%Y-%m-%d') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, m.reason, m.lt_comments, m.manager_comments from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid where m.id='".mysql_real_escape_string($ShiftMovementId)."' and e.teamleader='".mysql_real_escape_string($_SESSION["id"])."' and isemergency='1' and e.status='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$row = $this->fetchrow();

					switch($this->f("approvedby_tl"))
					{
						case '0':
							$row["tlapproval"] = 'Pending';
							break;
						case '1':
							$row["tlapproval"] = 'Approved';
							break;
						case '2':
							$row["tlapproval"] = 'Unapproved';
							break;
					}

					switch($this->f("approvedby_manager"))
					{
						case '0':
							$row["managerapproval"] = 'Pending';
							break;
						case '1':
							$row["managerapproval"] = 'Approved';
							break;
						case '2':
							$row["managerapproval"] = 'Unapproved';
							break;
					}

					$MovementInfo = $row;
				}
			}

			return $MovementInfo;
		}

		function fnAdminShiftMovementById($ShiftMovementId)
		{
			$MovementInfo = false;
			/* Fetch shift movement added by the admin */
			$sSQL = "select e.name as employeename, date_format(m.movement_date,'%Y-%m-%d') as movementdate, date_format(m.compensation_date,'%Y-%m-%d') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, m.reason, m.lt_comments, m.manager_comments from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid where m.id='".mysql_real_escape_string($ShiftMovementId)."' and e.status='0' and m.isadminadded='1'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$arrStatus = array('0'=>'Pending', '1'=>'Approved', '2'=>'Unapproved');
				if($this->next_record())
				{
					$row = $this->fetchrow();

					$row["tlapproval"] = $arrStatus[$this->f("approvedby_tl")];
					$row["managerapproval"] = $arrStatus[$this->f("approvedby_manager")];

					$MovementInfo = $row;
				}
			}

			return $MovementInfo;
		}

		function fnShiftMovementById($ShiftMovementId, $ids = "")
		{
			$MovementInfo = false;

			$cond = "";
			if(trim($ids) != "")
			{
				$cond = " and m.userid in ($ids)";
			}

			$sSQL = "select e.name as employeename, m.id,m.userid,date_format(m.movement_date,'%Y-%m-%d') as movementdate, date_format(m.compensation_date,'%Y-%m-%d') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, m.reason, m.approvedby_tl, m.lt_comments, m.approvedby_manager, m.manager_comments, concat(date_format(m.movement_date,'%Y-%m-%d'),' ',m.movement_fromtime) as movementdt,m.isCancel, m.isactive, m.delegatedtl_id, m.delegatedtl_status, m.delegatedtl_comment, m.delegatedmanager_id, m.delegatedmanager_status, m.delegatedmanager_comment, m.reportinghead1, m.reportinghead2, m.isadminadded from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid where m.id='".mysql_real_escape_string($ShiftMovementId)."' $cond";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$row = $this->fetchrow();

					switch($this->f("approvedby_tl"))
					{
						case '0':
							$row["tlapproval"] = 'Pending';
							break;
						case '1':
							$row["tlapproval"] = 'Approved';
							break;
						case '2':
							$row["tlapproval"] = 'Unapproved';
							break;
					}

					switch($this->f("approvedby_manager"))
					{
						case '0':
							$row["managerapproval"] = 'Pending';
							break;
						case '1':
							$row["managerapproval"] = 'Approved';
							break;
						case '2':
							$row["managerapproval"] = 'Unapproved';
							break;
					}

					switch($this->f("delegatedtl_status"))
					{
						case '0':
							$row["delegatedtlapproval"] = 'Pending';
							break;
						case '1':
							$row["delegatedtlapproval"] = 'Approved';
							break;
						case '2':
							$row["delegatedtlapproval"] = 'Unapproved';
							break;
					}

					switch($this->f("delegatedmanager_status"))
					{
						case '0':
							$row["delegatedmanagerapproval"] = 'Pending';
							break;
						case '1':
							$row["delegatedmanagerapproval"] = 'Approved';
							break;
						case '2':
							$row["delegatedmanagerapproval"] = 'Unapproved';
							break;
					}

					$MovementInfo = $row;
				}
			}

			return $MovementInfo;
		}

		function fnGetAllShiftMovementRequest($EmployeeIds)
		{
			$arrMovements = array();

			/*$sSQL = "select m.id, date_format(m.movement_date,'%Y-%m-%d') as movementdate, date_format(m.compensation_date,'%Y-%m-%d') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, m.reason, e1.name as teamleadername, m.lt_comments, m.manager_comments,m.isCancel from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid INNER JOIN pms_employee e1 ON e.teamleader = e1.id where m.userid in ($EmployeeIds)";*/

			$curdate = Date('Y-m-d');
			/*$sSQL = "select m.id,m.userid, date_format(m.movement_date,'%Y-%m-%d') as movementdate, date_format(m.compensation_date,'%Y-%m-%d') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, m.reason, e1.name as teamleadername, m.lt_comments, m.manager_comments,m.isCancel from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid INNER JOIN pms_employee e1 ON e.teamleader = e1.id where m.userid in ($EmployeeIds) and date_format(movement_date, '%Y-%m-%d') >= '".mysql_real_escape_string($curdate)."' and (approvedby_tl='0' or approvedby_manager='0' or (approvedby_tl='0' and delegatedtl_id!='0' and delegatedtl_status='0') or (approvedby_manager='0' and delegatedmanager_id!='0' and delegatedmanager_status='0'))";*/
			
			$prevdate = Date('Y-m-d', strtotime('-1 day'));
			
			//$sSQL = "select m.id,m.userid, date_format(m.movement_date,'%d-%m-%Y') as movementdate, date_format(m.compensation_date,'%d-%m-%Y') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, m.reason, e1.name as teamleadername, m.lt_comments, m.manager_comments,m.isCancel from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid INNER JOIN pms_employee e1 ON e.teamleader = e1.id where m.userid in ($EmployeeIds) and date_format(movement_date, '%Y-%m-%d') >= '".mysql_real_escape_string($curdate)."'";
			$sSQL = "select m.id,m.userid, date_format(m.movement_date,'%d-%m-%Y') as movementdate, date_format(m.movement_date,'%Y-%m-%d') as movement_date, date_format(m.compensation_date,'%d-%m-%Y') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, e.id as eid, m.reason, e1.name as teamleadername, m.lt_comments, m.manager_comments,m.isCancel from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid INNER JOIN pms_employee e1 ON e.teamleader = e1.id where m.userid in ($EmployeeIds) and date_format(movement_date, '%Y-%m-%d') >= '".mysql_real_escape_string($prevdate)."'";
			$this->query($sSQL);

			include_once('includes/class.shifts.php');
			include_once('includes/class.attendance.php');
			include_once('includes/class.employee.php');

			$objShifts = new shifts();
			$objAttendance = new attendance();
			$objEmployee = new employee();
			
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					if($this->f("movement_date") == $prevdate)
					{
						$starttime = "00:00";
						$endtime = "00:00";

						/* Get data from attendance */
						$ShiftId = $objAttendance->fnGetAttendanceShiftByUserAndDate($this->f("eid"), $curdate);

						if($ShiftId == "" || $ShiftId == "0")
						{
							/* Get the default shift from the employee */
							$ShiftId = $objEmployee->fnGetEmployeeShiftById($this->f("eid"));
						}

						$arrShift = $objShifts->fnGetShiftById($ShiftId);
						if(count($arrShift) > 0)
						{
							$starttime = $arrShift["starttime"];
							$endtime = $arrShift["endtime"];
						}
						
						if($endtime <= $starttime)
						{
							$arrMovements[] = $this->fetchrow();
						}
					}
					else
					{
						$arrMovements[] = $this->fetchrow();
					}
				}
			}

			return $arrMovements;
		}
		
		function fnGetAllShiftMovementToCancel($EmployeeIds,$month,$year)
		{
			$arrMovements = array();
			//$curdate = Date('Y-m');
			$curdate = $year.'-'.$month;
			
			//$sSQL = "select m.id,m.userid, date_format(m.movement_date,'%d-%m-%Y') as movementdate, date_format(m.compensation_date,'%d-%m-%Y') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, m.reason, e1.name as teamleadername, m.lt_comments, m.manager_comments,m.isCancel from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid INNER JOIN pms_employee e1 ON e.teamleader = e1.id where m.userid in ($EmployeeIds) and date_format(movement_date, '%Y-%m') = '".mysql_real_escape_string($curdate)."' and isCancel='0'";
			$sSQL = "select m.id,m.userid, date_format(m.movement_date,'%d-%m-%Y') as movementdate, date_format(m.compensation_date,'%d-%m-%Y') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, m.reason, e1.name as teamleadername, m.lt_comments, m.manager_comments,m.isCancel from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid INNER JOIN pms_employee e1 ON e.teamleader = e1.id where m.userid in ($EmployeeIds) and date_format(movement_date, '%Y-%m') = '$curdate'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrMovements[] = $this->fetchrow();
				}
			}

			return $arrMovements;
		}

		function fnGetAllShiftMovementCompensationRequest()
		{
			$arrMovements = array();
			
			include_once("class.employee.php");
			
			$objEmployee = new employee();
			
			$arrtemp = array();
			$arrEmployee = array();

			if($_SESSION["designation"] == "6" || $_SESSION["designation"] == "18" || $_SESSION["designation"] == "19")
			{
				/* Get Delegated Manager id */
				$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($_SESSION['id']);
				
				if(count($arrDelegatedManagerId) > 0 )
				{
					foreach($arrDelegatedManagerId as $delegatesManagerIds)
					{
						$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
						$arrEmployee = $arrEmployee + $arrtemp;
					}
				}
			}
			else if ($_SESSION["designation"] == "7" || $_SESSION["designation"] == "13")
			{
				/* Get delegated teamleader id */
				$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);	
				
				if(count($arrDelegatedTeamLeaderId) > 0 )
				{
					foreach($arrDelegatedTeamLeaderId as $delegatesIds)
					{
						$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
						$arrEmployee = $arrEmployee + $arrtemp;
					}
				}
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

			$sSQL = "select smc.id as compensationid, e.name as employeename, date_format(sm.movement_date,'%d-%m-%Y') as movementdate, date_format(sm.movement_fromtime,'%H:%i') as movementfrom, date_format(sm.movement_totime,'%H:%i') as movementto, date_format(smc.compensation_date,'%d-%m-%Y') as compensationdate, date_format(smc.compensation_fromtime,'%H:%i') as compensationfrom, date_format(smc.compensation_totime,'%H:%i') as compensationto, smc.approvedby_tl from pms_shift_movement sm INNER JOIN pms_shift_movement_compensation smc ON sm.id = smc.shift_movement_id INNER JOIN pms_employee e ON e.id = smc.userid where (e.teamleader='".mysql_real_escape_string($_SESSION["id"])."' or smc.userid in ($ids)) and (smc.approvedby_tl='0' or (smc.approvedby_tl='0' and smc.delegatedtl_id!='0' and smc.delegatedtl_status='0')) and e.status='0'";
			
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$row = $this->fetchrow();

					switch($this->f("approvedby_tl"))
					{
						case '0':
							$row["tlapproval"] = 'Pending';
							break;
						case '1':
							$row["tlapproval"] = 'Approved';
							break;
						case '2':
							$row["tlapproval"] = 'Unapproved';
							break;
					}

					$arrMovements[] = $row;
				}
			}

			return $arrMovements;
		}

		function fnUpdateShiftMovement($ApprovalInfo)
		{
			include_once('class.attendance.php');

			$objAttendance = new attendance();

			
			if($_SESSION["usertype"] == "employee")
			{
				include_once('class.employee.php');
				$objEmployee = new employee();

				include_once('class.leave.php');
				$objLeave = new leave();

				$status = array("0"=>"Pending", "1"=>"Approved", "2"=>"Unapproved");

				$MovementInfo = $this->fnShiftMovementById($ApprovalInfo["id"]);

				/*if($MovementInfo["isactive"] == "1")
				{
					return -2;
				}*/

				if($_SESSION["designation"] == '17')
				{
					$ApprovalInfo["manager_approval_date"] = Date("Y-m-d H:i:s");
					$this->updateArray('pms_shift_movement',$ApprovalInfo);

					/* If approved by manager update in attendance */
					if($ApprovalInfo["approvedby_manager"] == "1")
					{
						$arrInfo["user_id"] = $MovementInfo["userid"];
						$arrInfo["date"] = $MovementInfo["movementdate"];
						$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("SM");
						$objAttendance->fnInsertRosterAttendance($arrInfo);
						//die;
					}
					else
					{
						/* If unapproved by manager */
						$arrInfo["user_id"] = $MovementInfo["userid"];
						$arrInfo["date"] = $MovementInfo["movementdate"];
						$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("");
						$objAttendance->fnInsertRosterAttendance($arrInfo);
					}

					$EmployeeInfo = $objEmployee->fnGetEmployeeById($MovementInfo["userid"]);

					$ManagerInfo = $objEmployee->fnGetEmployeeById($_SESSION["id"]);

					$MailTo = $EmployeeInfo["email"];
					$Subject = "Shift Movement ".$status[$ApprovalInfo["approvedby_manager"]];
					$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
					$content .= $ManagerInfo["name"]." has ".strtoupper($status[$ApprovalInfo["approvedby_manager"]])." your shift movement request on ".$MovementInfo["movementdate"]." for the time ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." hours.";
					if($ApprovalInfo["approvedby_manager"] == "1")
						$content .= "<br/><br/>Kindly ensure that you compensate the same as per your proposed timinings.";
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);

					return $ApprovalInfo["approvedby_manager"];
				}
				else if($_SESSION["designation"] == '6' || $_SESSION["designation"] == '18' || $_SESSION["designation"] == '19' || $_SESSION["designation"] == '25' || $_SESSION["designation"] == '44')
				{
					/* Manager Login */
					
					if($MovementInfo["delegatedmanager_id"] == $_SESSION["id"])
					{
						/* If the delegated manager is currently logged in set approval for the same and send mail to others */
						
						$ApprovalInfo["delegatedmanager_date"] = Date("Y-m-d H:i:s");
						$this->updateArray('pms_shift_movement',$ApprovalInfo);

						/* If approved by manager update in attendance */
						if($ApprovalInfo["delegatedmanager_status"] == "1" && $MovementInfo["approvedby_manager"] == '0')
						{
							$arrInfo["user_id"] = $MovementInfo["userid"];
							$arrInfo["date"] = $MovementInfo["movementdate"];
							$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("SM");
							
							
							$objAttendance->fnInsertRosterAttendance($arrInfo);
						}

						$EmployeeInfo = $objEmployee->fnGetEmployeeById($MovementInfo["userid"]);

						$ManagerInfo = $objEmployee->fnGetEmployeeById($_SESSION["id"]);
						
						/* Mail to employee who has added a shift movement */
						$MailTo = $EmployeeInfo["email"];
						$Subject = "Shift Movement ".$status[$ApprovalInfo["delegatedmanager_status"]];
						$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
						$content .= $ManagerInfo["name"]." has ".strtoupper($status[$ApprovalInfo["delegatedmanager_status"]])." your shift movement request on ".$MovementInfo["movementdate"]." for the time ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." hours.";
						
						if($ApprovalInfo["delegatedmanager_status"] == "1")
							$content .= "<br/><br/>Kindly ensure that you compensate the same as per your proposed timinings.";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
						
						/* Common content to be sent to tl & manager */
						$tempContent = $ManagerInfo["name"]." has ".strtoupper($status[$ApprovalInfo["delegatedmanager_status"]])." shift movement request of ".$EmployeeInfo["name"]." on ".$MovementInfo["movementdate"]." for the time - ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." hours.";
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
								if($ManagerInfo["designation"] != "8" && $ManagerInfo["designation"] != "17")
								{
									/* do not send mail to admin or CEO if 2nd reporting head is admin / CEO */
									$MailTo = $ManagerInfo["email"];
									$content = "Dear ".$ManagerInfo["name"].",<br><br>".$tempContent;
									sendmail($MailTo, $Subject, $content);
								}
							}
						}
						
						/* Mail to delegated team leader */
						if($MovementInfo["delegatedtl_id"] != "" && $MovementInfo["delegatedtl_id"] != "0")
						{
							$TeamleaderInfo = $objEmployee->fnGetEmployeeById($MovementInfo["delegatedtl_id"]);
							$MailTo = $TeamleaderInfo["email"];
							$content = "Dear ".$TeamleaderInfo["name"].",<br><br>".$tempContent;
							sendmail($MailTo, $Subject, $content);
						}
						
						return $ApprovalInfo["delegatedmanager_status"];
					}
					else
					{
						$ApprovalInfo["manager_approval_date"] = Date("Y-m-d H:i:s");
						$this->updateArray('pms_shift_movement',$ApprovalInfo);

						/* If approved by manager update in attendance */
						if($ApprovalInfo["approvedby_manager"] == "1")
						{
							$arrInfo["user_id"] = $MovementInfo["userid"];
							$arrInfo["date"] = $MovementInfo["movementdate"];
							$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("SM");
							$objAttendance->fnInsertRosterAttendance($arrInfo);
							//die;
						}
						else
						{
							/* If unapproved by manager */
							$arrInfo["user_id"] = $MovementInfo["userid"];
							$arrInfo["date"] = $MovementInfo["movementdate"];
							$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("");
							$objAttendance->fnInsertRosterAttendance($arrInfo);
						}

						$EmployeeInfo = $objEmployee->fnGetEmployeeById($MovementInfo["userid"]);

						$ManagerInfo = $objEmployee->fnGetEmployeeById($_SESSION["id"]);

						$MailTo = $EmployeeInfo["email"];
						$Subject = "Shift Movement ".$status[$ApprovalInfo["approvedby_manager"]];
						$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
						$content .= $ManagerInfo["name"]." has ".strtoupper($status[$ApprovalInfo["approvedby_manager"]])." your shift movement request on ".$MovementInfo["movementdate"]." for the time ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." hours.";
						if($ApprovalInfo["approvedby_manager"] == "1")
							$content .= "<br/><br/>Kindly ensure that you compensate the same as per your proposed timinings.";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);

						/* Common mail content for manager, delegated tl and delegated manager */
						$tempContent = $ManagerInfo["name"]." has ".strtoupper($status[$ApprovalInfo["approvedby_manager"]])." shift movement request of ".$EmployeeInfo["name"]." on ".$MovementInfo["movementdate"]." for the time - ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." hours.";
						$tempContent .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						/* Mail to team leader */
						if($EmployeeInfo["teamleader_id"] != 0 && $EmployeeInfo["teamleader_id"] != $_SESSION["id"])
						{
							$TeamleaderInfo = $objEmployee->fnGetEmployeeById($EmployeeInfo["teamleader_id"]);
							$MailTo = $TeamleaderInfo["email"];
							$content = "Dear ".$TeamleaderInfo["name"].",<br><br>".$tempContent;
							sendmail($MailTo, $Subject, $content);
						}
						
						/* Mail to delegated team leader */
						if($MovementInfo["delegatedtl_id"] != "" && $MovementInfo["delegatedtl_id"] != "0")
						{
							$TeamleaderInfo = $objEmployee->fnGetEmployeeById($MovementInfo["delegatedtl_id"]);
							$MailTo = $TeamleaderInfo["email"];
							$content = "Dear ".$TeamleaderInfo["name"].",<br><br>".$tempContent;
							sendmail($MailTo, $Subject, $content);
						}

						/* Mail to delegated manager */
						if($MovementInfo["delegatedmanager_id"] != "" && $MovementInfo["delegatedmanager_id"] != "0")
						{
							$ManagerInfo = $objEmployee->fnGetEmployeeById($MovementInfo["delegatedmanager_id"]);
							$MailTo = $ManagerInfo["email"];
							$content = "Dear ".$ManagerInfo["name"].",<br><br>".$tempContent;
							sendmail($MailTo, $Subject, $content);
						}

						return $ApprovalInfo["approvedby_manager"];
					}
				}
				else if($_SESSION["designation"] == 7 || $_SESSION["designation"] == 13)
				{
					/* TL Login */

					//$ShiftInfo = $this->fnShiftMovementById($ApprovalInfo["id"]);

					$curdt = Date("Y-m-d H:i:s");

					if($MovementInfo["movementdt"] < $curdt)
						return -1;

					if($MovementInfo["delegatedtl_id"] == $_SESSION["id"])
					{
						$ApprovalInfo["delegatedtl_date"] = $curdt;
						$this->updateArray('pms_shift_movement',$ApprovalInfo);
						
						$EmployeeInfo = $objEmployee->fnGetEmployeeById($MovementInfo["userid"]);

						$TeamleaderInfo = $objEmployee->fnGetEmployeeById($_SESSION["id"]);
						
						/* Mail to employee */
						$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
						$content .= $TeamleaderInfo["name"]." has ".strtoupper($status[$ApprovalInfo["delegatedtl_status"]])." your shift movement request on ".$MovementInfo["movementdate"]." for the time ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." hours.";
						
						if($ApprovalInfo["delegatedtl_status"] == "1")
							$content .= "<br/><br/>Kindly ensure that you compensate the same as per your proposed timinings.";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
						sendmail($MailTo, $Subject, $content);

						/* Common mail content */
						$tempContent = $TeamleaderInfo["name"]." has ".strtoupper($status[$ApprovalInfo["delegatedtl_status"]])." shift movement request of ".$EmployeeInfo["name"]." on ".$MovementInfo["movementdate"]." for the time - ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." hours.";
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
								if($ManagerInfo["designation"] != "8" && $ManagerInfo["designation"] != "17")
								{
									$MailTo = $ManagerInfo["email"];
									$content = "Dear ".$ManagerInfo["name"].",<br><br>".$tempContent;
									sendmail($MailTo, $Subject, $content);
								}
							}
						}
						
						/* Mail to delegated manager */
						if($MovementInfo["delegatedmanager_id"] != "" && $MovementInfo["delegatedmanager_id"] != "0")
						{
							$TeamleaderInfo = $objEmployee->fnGetEmployeeById($MovementInfo["delegatedmanager_id"]);
							$MailTo = $TeamleaderInfo["email"];
							$content = "Dear ".$TeamleaderInfo["name"].",<br><br>".$tempContent;
							sendmail($MailTo, $Subject, $content);
						}
						
						return $ApprovalInfo["delegatedtl_status"];
					}
					else
					{
						$ApprovalInfo["lt_approval_date"] = $curdt;
						$this->updateArray('pms_shift_movement',$ApprovalInfo);

						$EmployeeInfo = $objEmployee->fnGetEmployeeById($MovementInfo["userid"]);

						$TeamleaderInfo = $objEmployee->fnGetEmployeeById($_SESSION["id"]);

						/* Mail to employee */
						$MailTo = $EmployeeInfo["email"];
						$Subject = "Shift Movement ".$status[$ApprovalInfo["approvedby_tl"]];

						$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
						$content .= $TeamleaderInfo["name"]." has ".strtoupper($status[$ApprovalInfo["approvedby_tl"]])." your shift movement request on ".$MovementInfo["movementdate"]." for the time ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." hours.";
						
						if($ApprovalInfo["approvedby_tl"] == "1")
							$content .= "<br/><br/>Kindly ensure that you compensate the same as per your proposed timinings.";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
						sendmail($MailTo, $Subject, $content);

						/* Common mail content */
						$tempContent = $TeamleaderInfo["name"]." has ".strtoupper($status[$ApprovalInfo["approvedby_tl"]])." shift movement request of ".$EmployeeInfo["name"]." on ".$MovementInfo["movementdate"]." for the time - ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." hours";
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
						if($MovementInfo["delegatedtl_id"] != "" && $MovementInfo["delegatedtl_id"] != "0")
						{
							$TeamleaderInfo = $objEmployee->fnGetEmployeeById($MovementInfo["delegatedtl_id"]);
							$MailTo = $TeamleaderInfo["email"];
							$content = "Dear ".$TeamleaderInfo["name"].",<br><br>".$tempContent;
							sendmail($MailTo, $Subject, $content);
						}

						/* Mail to delegated manager */
						if($MovementInfo["delegatedmanager_id"] != "" && $MovementInfo["delegatedmanager_id"] != "0")
						{
							$ManagerInfo = $objEmployee->fnGetEmployeeById($MovementInfo["delegatedmanager_id"]);
							$MailTo = $ManagerInfo["email"];
							$content = "Dear ".$ManagerInfo["name"].",<br><br>".$tempContent;
							sendmail($MailTo, $Subject, $content);
						}

						return $ApprovalInfo["approvedby_tl"];
					}
				}
				else
				{
					return 0;
				}
			}
			else
			{
				return 0;
			}
		}

		function getEmployeeShiftMovementByDate($EmployeeId, $Date)
		{
			$arrShiftMovement = array();
			$sSQL = "select *, time_format(movement_fromtime,'%H:%i') as movement_fromtime, time_format(movement_totime,'%H:%i') as movement_totime, time_format(compensation_fromtime,'%H:%i') as compensation_fromtime, time_format(compensation_totime,'%H:%i') as compensation_totime, date_format(compensation_date, '%Y-%m-%d') as compensation_date from pms_shift_movement where userid='$EmployeeId' and date_format(movement_date, '%Y-%m-%d') = '".mysql_real_escape_string($Date)."' and (approvedby_manager='1' or (approvedby_manager='0' and delegatedmanager_id!='0' and delegatedmanager_status='1')) and isCancel='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrShiftMovement = $this->fetchrow();
				}
			}

			return $arrShiftMovement;
		}

		function fnChangeStatus($id)
		{
			include_once('class.employee.php');
			$objEmployee = new employee();

			$MovementInfo = $this->fnShiftMovementById($id);

			$EmployeeInfo = $objEmployee->fnGetEmployeeById($MovementInfo["userid"]);

			$loginInfo = $objEmployee->fnGetEmployeeById($_SESSION['id']);

			$TeamleaderInfo = $objEmployee->fnGetEmployeeById($EmployeeInfo["teamleader"]);

			$ManagerInfo = $objEmployee->fnGetEmployeeById($TeamleaderInfo["teamleader"]);

			if($EmployeeInfo["email"] != '')
			{
				$MailTo = $EmployeeInfo["email"];

				$Subject = "Shift Movement Cancel";


				$content = "Dear ".$EmployeeInfo["name"].",<br><br>";

				if($_SESSION['id'] == 1 && $_SESSION['usertype'] == 'admin')
				{
					$content .=  "Admin  has cancel your shift movement on ".$MovementInfo["movementdate"]." from ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"].".<br/><br/>";
				}
				else
				{
					$content .= $loginInfo["name"]." has cancel your shift movement on ".$MovementInfo["movementdate"]." from ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." cancel successfully.<br/><br/>";
				}

				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
				sendmail($MailTo, $Subject, $content);
			}
			if($TeamleaderInfo["email"] != '')
			{
				$MailTo1 = $TeamleaderInfo["email"];

				$Subject1 = "Shift Movement Cancel";


				$content1 = "Dear ".$TeamleaderInfo["name"].",<br><br>";

				if($_SESSION['id'] == 1 && $_SESSION['usertype'] == 'admin')
				{
					$content1 .=  "Admin  has cancel shift movement of ".$EmployeeInfo["name"]." on ".$MovementInfo["movementdate"]." from ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"].".<br/><br/>";
				}
				else
				{
					$content1 .= $loginInfo["name"]." shift movement  of ".$EmployeeInfo["name"]." on ".$MovementInfo["movementdate"]." from ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." cancel successfully.<br/><br/>";
				}

				$content1 .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
				sendmail($MailTo1, $Subject1, $content1);
			}

			if($ManagerInfo["email"] != '')
			{
				$MailTo2 = $ManagerInfo["email"];

				$Subject2 = "Shift Movement Cancel";

				$content2 = "Dear ".$ManagerInfo["name"].",<br><br>";

				if($_SESSION['id'] == 1 && $_SESSION['usertype'] == 'admin')
				{
					$content2 .=  "Admin  has cancel shift movement  of ".$EmployeeInfo["name"]." on ".$MovementInfo["movementdate"]." from ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"].".<br/><br/>";
				}
				else
				{
					$content2 .= $loginInfo["name"]." shift movement of ".$EmployeeInfo["name"]." on ".$MovementInfo["movementdate"]." from ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." cancel successfully.<br/><br/>";
				}

				$content2 .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
				sendmail($MailTo2, $Subject2, $content2);
			}

			$status = array("id"=>$id, "isCancel"=>"1");
			$this->updateArray('pms_shift_movement',$status);
			return true;
		}

		function fnGetApprovedShiftMovementCompensationIdByUser($userId)
		{
			$arrApprovedCompensation = array();

			$sSQL = "select distinct shift_movement_id as shift_movement_id from pms_shift_movement_compensation where userid='$userId' and (approvedby_tl='1' or (approvedby_tl='0' and delegatedtl_id!='0' and delegatedtl_status='1'))";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrApprovedCompensation[] = $this->f("shift_movement_id");
				}
			}

			return $arrApprovedCompensation;
		}

		function fnGetApprovedAndPendinShiftMovementCompensationIdByUser($userId)
		{
			$arrApprovedCompensation = array();

			$sSQL = "select distinct shift_movement_id as shift_movement_id from pms_shift_movement_compensation where userid='$userId' and (approvedby_tl='1' or approvedby_tl='0' or (approvedby_tl='0' and delegatedtl_id!=0 and delegatedtl_status in (0,1)))";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrApprovedCompensation[] = $this->f("shift_movement_id");
				}
			}

			return $arrApprovedCompensation;
		}


		function fnGetPendingCompensationMovementByUser($userId)
		{
			$arrPendingShifts = array();

			$arrApprovedCompensation = $this->fnGetApprovedAndPendinShiftMovementCompensationIdByUser($userId);

			if(count($arrApprovedCompensation) > 0)
			{
				$arrApprovedCompensation = array_filter($arrApprovedCompensation,'strlen');
			}

			$ids = 0;
			if(count($arrApprovedCompensation) > 0)
			{
				$ids = implode(",",$arrApprovedCompensation);
			}

			/*$sSQL = "select id, date_format(movement_date,'%Y-%m-%d') as movement_date from pms_shift_movement where userid='$userId' and isCancel='0' and (approvedby_manager='1' or (approvedby_tl='1' and approvedby_manager='0')) and id not in ($ids)";*/
			//$sSQL = "select id, date_format(movement_date,'%Y-%m-%d') as movement_date from pms_shift_movement where userid='$userId' and isCancel='0' and (approvedby_manager='1' or (approvedby_manager='0' and delegatedtl_id!='0' and delegatedtl_status='0')) and id not in ($ids)";
			$sSQL = "select id, date_format(movement_date,'%Y-%m-%d') as movement_date from pms_shift_movement where userid='$userId' and isCancel='0' and (approvedby_manager='1' or (approvedby_manager='0' and delegatedmanager_id!='0' and delegatedmanager_status='1')) and id not in ($ids)";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrPendingShifts[] = $this->fetchrow();
				}
			}

			return $arrPendingShifts;
		}

		function fnGetUncompensatedShiftMovementsByUser($UserId)
		{
			$arrUncompensatedShiftMovement = array();

			/* Fetch all shift movement compensations that are approved */
			$arrApprovedCompensation = $this->fnGetApprovedAndPendinShiftMovementCompensationIdByUser($UserId);

			if(count($arrApprovedCompensation) > 0)
			{
				$arrApprovedCompensation = array_filter($arrApprovedCompensation,'strlen');
			}

			$ids = 0;
			if(count($arrApprovedCompensation) > 0)
			{
				$ids = implode(",",$arrApprovedCompensation);
			}

			/*$sSQL = "select id, date_format(movement_date,'%Y-%m-%d') as movement_date from pms_shift_movement where userid='$UserId' and isCancel='0' and (approvedby_manager='1' or (approvedby_tl='1' and approvedby_manager='0')) and id not in ($ids)";*/
			$curdt = Date('Y-m-d');
			$sSQL = "select id, date_format(movement_date,'%Y-%m-%d') as movement_date from pms_shift_movement where userid='$UserId' and isCancel='0' and (approvedby_manager='1' or (approvedby_manager='0' and delegatedmanager_id!=0 and delegatedmanager_status='1')) and id not in ($ids) and date_format(DATE_ADD(movement_date,INTERVAL 7 DAY),'%Y-%m-%d') >= '$curdt'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrUncompensatedShiftMovement[] = $this->fetchrow();
				}
			}

			return $arrUncompensatedShiftMovement;
		}
		
		function fnIsUserShiftMovementApprovedByDate($UserId, $ShiftMovementDate)
		{
			$sm = 0;
			
			$sSQL = "select * from pms_shift_movement where userid='".mysql_real_escape_string($UserId)."' and date_format(movement_date,'%Y-%m-%d') = '".mysql_real_escape_string($ShiftMovementDate)."' and (approvedby_manager='1' or (approvedby_manager='0' and delegatedmanager_id!='' and delegatedmanager_status='1')) and isCancel='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$sm = 1;
			}
			
			return $sm;
		}

		function fnGetAllShiftMovementsEmployee($year,$month)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = date("Y-m-t", strtotime($start_date));
			
			$arrShiftMovementInformation = array();
			$db = new DB_Sql();
			
			$query = "SELECT e.id as eid,e.name as ename, date_format(at.date,'%Y-%m-%d') as at_date FROM `pms_attendance` as at left join pms_employee as e on e.id = at.user_id WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' and at.`leave_id` in(14)";
			
			$this->query($query);
			
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					
					$arrShiftMovementInformation[$this->f("eid")]["name"] = $this->f("ename");
					$arrShiftMovementInformation[$this->f("eid")]["date"][] = $this->f("at_date");
				}
			}
			return $arrShiftMovementInformation;
		}
		function fnGetAllShiftMovementAndComponsations($uid,$date)
		{
			//echo '<br>uid'.$uid.'date'.$date;
			$movements = array();

			$query1 = "SELECT co . *,sm.movement_date as mov_date,co.compensation_date as comp_date FROM `pms_shift_movement` AS sm LEFT JOIN pms_shift_movement_compensation AS co ON sm.id = co.shift_movement_id WHERE sm.isCancel = '0' AND DATE_FORMAT( sm.movement_date, '%Y-%m-%d' ) = DATE_FORMAT( '$date', '%Y-%m-%d' ) AND ( sm.approvedby_manager =1 OR ( sm.approvedby_manager !=2 AND sm.delegatedmanager_id != '0' AND sm.delegatedmanager_status = '1' ) ) AND sm.userid = '$uid'";

			$this->query($query1);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$movements[] = $this->fetchrow();
				}
			}
			//echo '<pre>'; print_r($movements);
			return $movements;
			

		}
	}
?>

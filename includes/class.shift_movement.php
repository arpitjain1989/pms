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
			$curDt = Date('Y-m');
			$sSQL = "select * from pms_shift_movement where userid='".mysql_real_escape_string($_SESSION["id"])."' and approvedby_manager='0' and delegatedmanager_status='0' and isactive='0' and approvedby_tl != 2 and delegatedtl_status!='2' and isCancel='0' and date_format(movement_date,'%Y-%m') = '$curDt'";
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

			/* Check if approved LWP added by admin */
			$sSQL = "select * from pms_approved_lwp where date_format(lwp_date, '%Y-%m-%d') = '".mysql_real_escape_string($arrMovementInfo["movement_date"])."' and user_id='".mysql_real_escape_string($_SESSION["id"])."' and approval_status in (0,1)";
			$this->query($sSQL);
			if($this->num_rows() > 0)
				return -3;

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

				$arrMovementInfo["userid"] = $_SESSION["id"];
				$arrMovementInfo["addedon"] = date('Y-m-d H:i:s');
				$arrMovementInfo["approvedby_tl"] = 0;
				$arrMovementInfo["approvedby_manager"] = 0;
				$arrMovementInfo["isemergency"] = 0;

				include_once('class.employee.php');
				include_once('class.designation.php');
				include_once("class.leave.php");

				$objEmployee = new employee();
				$objDesignation = new designations();
				$objLeave = new leave();

				/* Fetch details for the user designation */
				$arrDesignationInfo = $objDesignation->fnGetDesignationById($_SESSION["designation"]);
				
				if(count($arrDesignationInfo) > 0)
				{
					/* Fetch reporting head hierarchy */
					$arrHeads = $objEmployee->fnGetReportHeadHierarchy($_SESSION["id"]);
					
					if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && isset($arrDesignationInfo["second_reporting_head"]) && trim($arrDesignationInfo["second_reporting_head"]) != "" && trim($arrDesignationInfo["second_reporting_head"]) != "0")
					{
						if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
						{
							$reportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];
							$arrMovementInfo["reportinghead1"] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
						}
						
						if(isset($arrHeads[$arrDesignationInfo["second_reporting_head"]]))
						{
							$reportinghead2 = $arrHeads[$arrDesignationInfo["second_reporting_head"]];
							$arrMovementInfo["reportinghead2"] = $arrHeads[$arrDesignationInfo["second_reporting_head"]]["id"];
						}
					}
					else if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && trim($arrDesignationInfo["second_reporting_head"]) == "0")
					{
						if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
						{
							$reportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];
							
							$arrMovementInfo["reportinghead2"] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
							$arrMovementInfo["reportinghead1"] = 0;
						}
					}
				}

				/* BEGIN - Check if delegated to other tl, add the delegated TL */

				$checkDeligateId = $objLeave->fnCheckDeligate($arrMovementInfo["reportinghead1"]);
				$checkDeligateManagerId = $objLeave->fnCheckDeligate($arrMovementInfo["reportinghead2"]);

				$delegateTeamleaderId = 0;
				if(isset($checkDeligateId) && $checkDeligateId != '')
				{
					$delegateTeamleaderId = $checkDeligateId;
				}

				$delegateManagerId = 0;
				if(isset($checkDeligateManagerId) && $checkDeligateManagerId != '')
				{
					$delegateManagerId = $checkDeligateManagerId;
				}

				$arrMovementInfo["delegatedtl_id"] = $delegateTeamleaderId;
				$arrMovementInfo["delegatedtl_status"] = 0;
				$arrMovementInfo["delegatedmanager_id"] = $delegateManagerId;
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

				if($arrMovementInfo["reportinghead1"] != "" && $arrMovementInfo["reportinghead1"] != "0")
				{
					$TeamLeaderInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["reportinghead1"]);

					$MailTo = $TeamLeaderInfo["email"];
					
					$content = "Dear ".$TeamLeaderInfo["name"].",<br><br>".$tempContent;
					
					if($arrMovementInfo["tlapprovalcode"] != "")
					{
						$content .= "<tr bgcolor='#FFFFFF'>
										<td colspan='2'>
											Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["tlapprovalcode"]."_Approve_SM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["tlapprovalcode"]."_Reject_SM]'>Reject</a></b> for letting us know your decision.
										</td>
									</tr>";
					}

					$content .= "</table>";
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);
				}

				if($arrMovementInfo["reportinghead2"] != "" && $arrMovementInfo["reportinghead2"] != "0")
				{
					$ManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["reportinghead2"]);

					$MailTo = $ManagerInfo["email"];
					
					$content = "Dear ".$ManagerInfo["name"].",<br><br>".$tempContent;
					
					if($arrMovementInfo["managerapprovalcode"] != "")
					{
						$content .= "<tr bgcolor='#FFFFFF'>
										<td colspan='2'>
											Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["managerapprovalcode"]."_Approve_SM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["managerapprovalcode"]."_Reject_SM]'>Reject</a></b> for letting us know your decision.
										</td>
									</tr>";
					}

					$content .= "</table>";
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);
				}

				if($arrMovementInfo["delegatedtl_id"] != "" && $arrMovementInfo["delegatedtl_id"] != "0")
				{
					$DelegateTeamLeaderInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedtl_id"]);

					$MailTo = $DelegateTeamLeaderInfo["email"];
					
					$content = "Dear ".$DelegateTeamLeaderInfo["name"].",<br><br>".$tempContent;
					
					if($arrMovementInfo["delegatedtlapprovalcode"] != "")
					{
						$content .= "<tr bgcolor='#FFFFFF'>
										<td colspan='2'>
											Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Approve_SM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Reject_SM]'>Reject</a></b> for letting us know your decision.
										</td>
									</tr>";
					}

					$content .= "</table>";
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);
				}

				if($arrMovementInfo["delegatedmanager_id"] != "" && $arrMovementInfo["delegatedmanager_id"] != "0")
				{
					$DelegateManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedmanager_id"]);

					$MailTo = $DelegateManagerInfo["email"];
					
					$content = "Dear ".$DelegateManagerInfo["name"].",<br><br>".$tempContent;
					
					if($arrMovementInfo["delegatedmanagerapprovalcode"] != "")
					{
						$content .= "<tr bgcolor='#FFFFFF'>
										<td colspan='2'>
											Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedmanagerapprovalcode"]."_Approve_SM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedmanagerapprovalcode"]."_Reject_SM]'>Reject</a></b> for letting us know your decision.
										</td>
									</tr>";
					}

					$content .= "</table>";
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);
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
			include_once('class.employee.php');
			include_once('class.designation.php');
			include_once('class.leave.php');

			$objEmployee = new employee();
			$objAttendance = new attendance();
			$objDesignation = new designations();
			$objLeave = new leave();
			
			$totalCount = $this->fnValidateShiftMovement($arrMovementInfo["userid"], $arrMovementInfo["movement_date"]);

			if(!$this->fnCheckShiftMovementEligable($arrMovementInfo["userid"], $arrMovementInfo["movement_date"]))
			{
				/* indicates that shift movement / leave is already taken for this date */
				return -1;
			}

			/* Check if approved LWP added by admin */
			$sSQL = "select * from pms_approved_lwp where date_format(lwp_date, '%Y-%m-%d') = '".mysql_real_escape_string($arrMovementInfo["movement_date"])."' and user_id='".mysql_real_escape_string($arrMovementInfo["userid"])."' and approval_status in (0,1)";
			$this->query($sSQL);
			if($this->num_rows() > 0)
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

				$arrMovementInfo["addedon"] = date('Y-m-d H:i:s');
				$arrMovementInfo["isemergency"] = 1;
				$arrMovementInfo["emergencysmaddedby"] = $_SESSION["id"];

				$employeeInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["userid"]);

				if(count($employeeInfo) > 0)
				{
					/* Fetch details for the user designation */
					$arrDesignationInfo = $objDesignation->fnGetDesignationById($employeeInfo["designation"]);

					/* Fetch reporting head hierarchy */
					$arrHeads = $objEmployee->fnGetReportHeadHierarchy($arrMovementInfo["userid"]);
					
					if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && isset($arrDesignationInfo["second_reporting_head"]) && trim($arrDesignationInfo["second_reporting_head"]) != "" && trim($arrDesignationInfo["second_reporting_head"]) != "0")
					{
						if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
						{
							$reportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];
							$arrMovementInfo["reportinghead1"] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
						}
						
						if(isset($arrHeads[$arrDesignationInfo["second_reporting_head"]]))
						{
							$reportinghead2 = $arrHeads[$arrDesignationInfo["second_reporting_head"]];
							$arrMovementInfo["reportinghead2"] = $arrHeads[$arrDesignationInfo["second_reporting_head"]]["id"];
						}
					}
					else if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && trim($arrDesignationInfo["second_reporting_head"]) == "0")
					{
						if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
						{
							$reportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];
							
							$arrMovementInfo["reportinghead2"] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
							$arrMovementInfo["reportinghead1"] = 0;
						}
					}
				}

				/* BEGIN - Check if delegated to other tl, add the delegated TL */

				$checkDeligateId = $objLeave->fnCheckDeligate($arrMovementInfo["reportinghead1"]);
				$checkDeligateManagerId = $objLeave->fnCheckDeligate($arrMovementInfo["reportinghead2"]);

				$delegateTeamleaderId = 0;
				if(isset($checkDeligateId) && $checkDeligateId != '')
				{
					$delegateTeamleaderId = $checkDeligateId;
				}

				$delegateManagerId = 0;
				if(isset($checkDeligateManagerId) && $checkDeligateManagerId != '')
				{
					$delegateManagerId = $checkDeligateManagerId;
				}

				$arrMovementInfo["approvedby_tl"] = 0;
				$arrMovementInfo["approvedby_manager"] = 0;

				$arrMovementInfo["delegatedtl_id"] = $delegateTeamleaderId;
				$arrMovementInfo["delegatedtl_status"] = 0;
				$arrMovementInfo["delegatedmanager_id"] = $delegateManagerId;
				$arrMovementInfo["delegatedmanager_status"] = 0;

				$arrMovementInfo["delegatedtlapprovalcode"] = "";
				if($arrMovementInfo["delegatedtl_id"] != "")
					$arrMovementInfo["delegatedtlapprovalcode"] = shiftmovementform_uid();

				$arrMovementInfo["delegatedmanagerapprovalcode"] = "";
				if($arrMovementInfo["delegatedmanager_id"] != "")
					$arrMovementInfo["delegatedmanagerapprovalcode"] = shiftmovementform_uid();

				/* END - Check if delegated to other tl, add the delegated TL */
				
				$arrMovementInfo["tlapprovalcode"] = shiftmovementform_uid();
				$arrMovementInfo["managerapprovalcode"] = shiftmovementform_uid();

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
				$Subject = "Emergency Shift Movement Request";

				$LoggedInUserInfo = $objEmployee->fnGetEmployeeDetailById($_SESSION["id"]);
				$user_content = "Dear ".$employeeInfo["name"].",<br><br>A shift movement had been added for you by ".$LoggedInUserInfo["name"]."<br/><br/>The details for the shift movement are as follows: <br><br>
				<table cellspacing='2' cellpadding='2' bgcolor='#E6E6E6'>
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
					</tr>
				</table>
				<br><br>Regards,<br>".SITEADMINISTRATOR;
				
				sendmail($employeeInfo["email"], $Subject, $user_content);

				if(isset($arrMovementInfo["reportinghead1"]) && $arrMovementInfo["reportinghead1"] == $_SESSION["id"])
				{
					/* Emergency leave added by team leader */
					$arrMovementInfo["approvedby_tl"] = 1;
					$arrMovementInfo["lt_approval_date"] = Date('Y-m-d H:i:s');

					
					/* Send mail to others */
					if($arrMovementInfo["reportinghead2"] != "" && $arrMovementInfo["reportinghead2"] != "0")
					{
						$ManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["reportinghead2"]);

						$MailTo = $ManagerInfo["email"];
						
						$content = "Dear ".$ManagerInfo["name"].",<br><br>".$tempContent;
						
						if(isset($arrMovementInfo["managerapprovalcode"]) && $arrMovementInfo["managerapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["managerapprovalcode"]."_Approve_ESM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["managerapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if($arrMovementInfo["delegatedtl_id"] != "" && $arrMovementInfo["delegatedtl_id"] != "0")
					{
						$DelegatedTeamleaderInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedtl_id"]);

						$MailTo = $DelegatedTeamleaderInfo["email"];

						$content = "Dear ".$DelegatedTeamleaderInfo["name"].",<br><br>".$tempContent;

						if(isset($arrMovementInfo["delegatedtlapprovalcode"]) && $arrMovementInfo["delegatedtlapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Approve_ESM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if($arrMovementInfo["delegatedmanager_id"] != "" && $arrMovementInfo["delegatedmanager_id"] != "0")
					{
						$DelegatedManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedmanager_id"]);

						$MailTo = $DelegatedManagerInfo["email"];

						$content = "Dear ".$DelegatedManagerInfo["name"].",<br><br>".$tempContent;

						if(isset($arrMovementInfo["delegatedmanagerapprovalcode"]) && $arrMovementInfo["delegatedmanagerapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedmanagerapprovalcode"]."_Approve_ESM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedmanagerapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}					
				}
				else if(isset($arrMovementInfo["reportinghead2"]) && $arrMovementInfo["reportinghead2"] == $_SESSION["id"])
				{
					/* Emergency leave added by team leader */
					$arrMovementInfo["approvedby_manager"] = 1;
					$arrMovementInfo["manager_approval_date"] = Date('Y-m-d H:i:s');

					/* Send mail to others */
					if($arrMovementInfo["reportinghead1"] != "" && $arrMovementInfo["reportinghead1"] != "0")
					{
						$TeamleaderInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["reportinghead1"]);

						$MailTo = $TeamleaderInfo["email"];
						
						$content = "Dear ".$TeamleaderInfo["name"].",<br><br>".$tempContent;
						
						if(isset($arrMovementInfo["tlapprovalcode"]) && $arrMovementInfo["tlapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["tlapprovalcode"]."_Approve_ESM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["tlapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if($arrMovementInfo["delegatedtl_id"] != "" && $arrMovementInfo["delegatedtl_id"] != "0")
					{
						$DelegatedTeamleaderInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedtl_id"]);

						$MailTo = $DelegatedTeamleaderInfo["email"];

						$content = "Dear ".$DelegatedTeamleaderInfo["name"].",<br><br>".$tempContent;

						if(isset($arrMovementInfo["delegatedtlapprovalcode"]) && $arrMovementInfo["delegatedtlapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Approve_ESM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if($arrMovementInfo["delegatedmanager_id"] != "" && $arrMovementInfo["delegatedmanager_id"] != "0")
					{
						$DelegatedManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedmanager_id"]);

						$MailTo = $DelegatedManagerInfo["email"];

						$content = "Dear ".$DelegatedManagerInfo["name"].",<br><br>".$tempContent;

						if(isset($arrMovementInfo["delegatedmanagerapprovalcode"]) && $arrMovementInfo["delegatedmanagerapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedmanagerapprovalcode"]."_Approve_ESM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedmanagerapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}
				}
				else if(isset($arrMovementInfo["delegatedtl_id"]) && $arrMovementInfo["delegatedtl_id"] == $_SESSION["id"])
				{
					/* Emergency leave added by team leader */
					$arrMovementInfo["delegatedtl_status"] = 1;
					$arrMovementInfo["delegatedtl_date"] = Date('Y-m-d H:i:s');

					/* Send mail to others */
					if($arrMovementInfo["reportinghead1"] != "" && $arrMovementInfo["reportinghead1"] != "0")
					{
						$TeamleaderInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["reportinghead1"]);

						$MailTo = $TeamleaderInfo["email"];
						
						$content = "Dear ".$TeamleaderInfo["name"].",<br><br>".$tempContent;
						
						if(isset($arrMovementInfo["tlapprovalcode"]) && $arrMovementInfo["tlapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["tlapprovalcode"]."_Approve_ESM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["tlapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if($arrMovementInfo["reportinghead2"] != "" && $arrMovementInfo["reportinghead2"] != "0")
					{
						$ManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["reportinghead2"]);

						$MailTo = $ManagerInfo["email"];
						
						$content = "Dear ".$ManagerInfo["name"].",<br><br>".$tempContent;
						
						if(isset($arrMovementInfo["managerapprovalcode"]) && $arrMovementInfo["managerapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["managerapprovalcode"]."_Approve_ESM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["managerapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if($arrMovementInfo["delegatedmanager_id"] != "" && $arrMovementInfo["delegatedmanager_id"] != "0")
					{
						$DelegatedManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedmanager_id"]);

						$MailTo = $DelegatedManagerInfo["email"];

						$content = "Dear ".$DelegatedManagerInfo["name"].",<br><br>".$tempContent;

						if(isset($arrMovementInfo["delegatedmanagerapprovalcode"]) && $arrMovementInfo["delegatedmanagerapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedmanagerapprovalcode"]."_Approve_ESM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedmanagerapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}
				}
				else if(isset($arrMovementInfo["delegatedmanager_id"]) && $arrMovementInfo["delegatedmanager_id"] == $_SESSION["id"])
				{
					/* Emergency leave added by team leader */
					$arrMovementInfo["delegatedmanager_status"] = 1;
					$arrMovementInfo["delegatedmanager_date"] = Date('Y-m-d H:i:s');

					/* Send mail to others */
					if($arrMovementInfo["reportinghead1"] != "" && $arrMovementInfo["reportinghead1"] != "0")
					{
						$TeamleaderInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["reportinghead1"]);

						$MailTo = $TeamleaderInfo["email"];
						
						$content = "Dear ".$TeamleaderInfo["name"].",<br><br>".$tempContent;
						
						if(isset($arrMovementInfo["tlapprovalcode"]) && $arrMovementInfo["tlapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["tlapprovalcode"]."_Approve_ESM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["tlapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if($arrMovementInfo["reportinghead2"] != "" && $arrMovementInfo["reportinghead2"] != "0")
					{
						$ManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["reportinghead2"]);

						$MailTo = $ManagerInfo["email"];
						
						$content = "Dear ".$ManagerInfo["name"].",<br><br>".$tempContent;
						
						if(isset($arrMovementInfo["managerapprovalcode"]) && $arrMovementInfo["managerapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["managerapprovalcode"]."_Approve_ESM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["managerapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if($arrMovementInfo["delegatedtl_id"] != "" && $arrMovementInfo["delegatedtl_id"] != "0")
					{
						$DelegatedTeamleaderInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedtl_id"]);

						$MailTo = $DelegatedTeamleaderInfo["email"];

						$content = "Dear ".$DelegatedTeamleaderInfo["name"].",<br><br>".$tempContent;

						if(isset($arrMovementInfo["delegatedtlapprovalcode"]) && $arrMovementInfo["delegatedtlapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Approve_ESM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
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
					/* If shift movement added by the head and is not a reporting head, send mail to all reporting heads */
					if($arrMovementInfo["reportinghead1"] != "" && $arrMovementInfo["reportinghead1"] != "0")
					{
						$TeamleaderInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["reportinghead1"]);

						$MailTo = $TeamleaderInfo["email"];
						
						$content = "Dear ".$TeamleaderInfo["name"].",<br><br>".$tempContent;
						
						if(isset($arrMovementInfo["tlapprovalcode"]) && $arrMovementInfo["tlapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["tlapprovalcode"]."_Approve_ESM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["tlapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if($arrMovementInfo["reportinghead2"] != "" && $arrMovementInfo["reportinghead2"] != "0")
					{
						$ManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["reportinghead2"]);

						$MailTo = $ManagerInfo["email"];
						
						$content = "Dear ".$ManagerInfo["name"].",<br><br>".$tempContent;
						
						if(isset($arrMovementInfo["managerapprovalcode"]) && $arrMovementInfo["managerapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["managerapprovalcode"]."_Approve_ESM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["managerapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}
					
					if($arrMovementInfo["delegatedtl_id"] != "" && $arrMovementInfo["delegatedtl_id"] != "0")
					{
						$DelegatedTeamleaderInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedtl_id"]);

						$MailTo = $DelegatedTeamleaderInfo["email"];

						$content = "Dear ".$DelegatedTeamleaderInfo["name"].",<br><br>".$tempContent;

						if(isset($arrMovementInfo["delegatedtlapprovalcode"]) && $arrMovementInfo["delegatedtlapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Approve_ESM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedtlapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if($arrMovementInfo["delegatedmanager_id"] != "" && $arrMovementInfo["delegatedmanager_id"] != "0")
					{
						$DelegatedManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedmanager_id"]);

						$MailTo = $DelegatedManagerInfo["email"];

						$content = "Dear ".$DelegatedManagerInfo["name"].",<br><br>".$tempContent;

						if(isset($arrMovementInfo["delegatedmanagerapprovalcode"]) && $arrMovementInfo["delegatedmanagerapprovalcode"] != "")
						{
							$content .= "<tr bgcolor='#FFFFFF'>
											<td colspan='2'>
												Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedmanagerapprovalcode"]."_Approve_ESM]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["delegatedmanagerapprovalcode"]."_Reject_ESM]'>Reject</a></b> for letting us know your decision.
											</td>
										</tr>";
						}

						$content .= "</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}
				}

				/* Insert emergency shift movement */

				if($arrMovementInfo["approvedby_manager"] == "1" || ($arrMovementInfo["approvedby_manager"] == "0" &&  $arrMovementInfo["delegatedmanager_status"] == "1"))
				{
					$arrInfo["user_id"] = $arrMovementInfo["userid"];
					$arrInfo["date"] = $arrMovementInfo["movement_date"];
					$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("SM");
					$objAttendance->fnInsertRosterAttendance($arrInfo);
				}
				else
				{
					$arrInfo["user_id"] = $arrMovementInfo["userid"];
					$arrInfo["date"] = $arrMovementInfo["movement_date"];
					$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("");
					$objAttendance->fnInsertRosterAttendance($arrInfo);
				}

				$id = $this->insertArray('pms_shift_movement',$arrMovementInfo);

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
				include_once("class.leave.php");
				include_once("class.designation.php");

				$objLeave = new leave();
				$objEmployee = new employee();
				$objDesignation = new designations();

				$employeeInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["userid"]);
				
				if(count($employeeInfo) > 0)
				{
					/* Fetch details for the user designation */
					$arrDesignationInfo = $objDesignation->fnGetDesignationById($employeeInfo["designation"]);

					/* Fetch reporting head hierarchy */
					$arrHeads = $objEmployee->fnGetReportHeadHierarchy($arrMovementInfo["userid"]);
					
					if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && isset($arrDesignationInfo["second_reporting_head"]) && trim($arrDesignationInfo["second_reporting_head"]) != "" && trim($arrDesignationInfo["second_reporting_head"]) != "0")
					{
						if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
						{
							$reportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];
							$arrMovementInfo["reportinghead1"] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
						}
						
						if(isset($arrHeads[$arrDesignationInfo["second_reporting_head"]]))
						{
							$reportinghead2 = $arrHeads[$arrDesignationInfo["second_reporting_head"]];
							$arrMovementInfo["reportinghead2"] = $arrHeads[$arrDesignationInfo["second_reporting_head"]]["id"];
						}
					}
					else if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && trim($arrDesignationInfo["second_reporting_head"]) == "0")
					{
						if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
						{
							$reportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];
							
							$arrMovementInfo["reportinghead2"] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
							$arrMovementInfo["reportinghead1"] = 0;
						}
					}
				}
				
				/* BEGIN - Check if delegated to other tl, add the delegated TL */

				$checkDeligateId = $objLeave->fnCheckDeligate($arrMovementInfo["reportinghead1"]);
				$checkDeligateManagerId = $objLeave->fnCheckDeligate($arrMovementInfo["reportinghead2"]);

				$delegateTeamleaderId = 0;
				if(isset($checkDeligateId) && $checkDeligateId != '')
				{
					$delegateTeamleaderId = $checkDeligateId;
				}

				$delegateManagerId = 0;
				if(isset($checkDeligateManagerId) && $checkDeligateManagerId != '')
				{
					$delegateManagerId = $checkDeligateManagerId;
				}

				$arrMovementInfo["delegatedtl_id"] = $delegateTeamleaderId;
				$arrMovementInfo["delegatedtl_status"] = 0;
				$arrMovementInfo["delegatedmanager_id"] = $delegateManagerId;
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

				if($arrMovementInfo["reportinghead1"] != 0)
				{
					$reportingHead1Info = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["reportinghead1"]);
					$MailTo = $reportingHead1Info["email"];

					$content = "Dear ".$reportingHead1Info["name"].",<br><br>".$tempContent;
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);
				}

				if($arrMovementInfo["reportinghead2"] != 0)
				{
					$reportingHead2Info = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["reportinghead2"]);
					$MailTo = $reportingHead2Info["email"];

					$content = "Dear ".$reportingHead2Info["name"].",<br><br>".$tempContent;
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);
				}

				if($arrMovementInfo["delegatedtl_id"] != 0)
				{
					$DelegatedTLInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedtl_id"]);
					$MailTo = $DelegatedTLInfo["email"];

					$content = "Dear ".$DelegatedTLInfo["name"].",<br><br>".$tempContent;
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);
				}
				
				if($arrMovementInfo["delegatedmanager_id"] != 0)
				{
					$DelegatedManagerInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedmanager_id"]);
					$MailTo = $DelegatedManagerInfo["email"];

					$content = "Dear ".$DelegatedManagerInfo["name"].",<br><br>".$tempContent;
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);
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
			include_once('class.designation.php');

			$objEmployee = new employee();
			$objDesignation = new designations();

			$employeeInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["userid"]);
			
			if(count($employeeInfo) > 0)
			{
				/* Fetch details for the user designation */
				$arrDesignationInfo = $objDesignation->fnGetDesignationById($employeeInfo["designation"]);

				/* Fetch reporting head hierarchy */
				$arrHeads = $objEmployee->fnGetReportHeadHierarchy($arrMovementInfo["userid"]);
				
				if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && isset($arrDesignationInfo["second_reporting_head"]) && trim($arrDesignationInfo["second_reporting_head"]) != "" && trim($arrDesignationInfo["second_reporting_head"]) != "0")
				{
					if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
					{
						$arrMovementInfo["firstreportingheadid"] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
					}
					
					if(isset($arrHeads[$arrDesignationInfo["second_reporting_head"]]))
					{
						$arrMovementInfo["secondreportingheadid"] = $arrHeads[$arrDesignationInfo["second_reporting_head"]]["id"];
					}
				}
				else if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && trim($arrDesignationInfo["second_reporting_head"]) == "0")
				{
					if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
					{
						$arrMovementInfo["firstreportingheadid"] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
						$arrMovementInfo["secondreportingheadid"] = 0;
					}
				}
			}

			$arrMovementInfo["tlapprovalcode"] = shiftmovementcompensationform_uid();

			/* BEGIN - Check if delegated to other tl, add the delegated TL */

			include_once("class.leave.php");

			$objLeave = new leave();

			$checkDeligateReportingHead1Id = $objLeave->fnCheckDeligate($arrMovementInfo["firstreportingheadid"]);

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

			$tlInfo = $objEmployee->fnGetEmployeeDetailById($employeeInfo["teamleader"]);

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
			
			$TeamLeaderInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["firstreportingheadid"]);

			if(count($TeamLeaderInfo) > 0)
			{
				$MailTo = $TeamLeaderInfo["email"];

				$content = "Dear ".$TeamLeaderInfo["name"].",<br><br>".$tempContent;
				
				if($arrMovementInfo["tlapprovalcode"] != "")
				{
					$content .= "<tr bgcolor='#FFFFFF'>
									<td colspan='2'>
										Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["tlapprovalcode"]."_Approve_SMC]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrMovementInfo["tlapprovalcode"]."_Reject_SMC]'>Reject</a></b> for letting us know your decision.
									</td>
								</tr>";
				}
				
				$content .= "</table>";
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

				sendmail($MailTo, $Subject, $content);
			}
			
			/* Send mail to delegated team leader */
			if($arrMovementInfo["delegatedtl_id"] != 0)
			{
				$DelegatedTL = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["delegatedtl_id"]);
				if(count($DelegatedTL) > 0)
				{
					$MailTo = $DelegatedTL["email"];
					
					$content = "Dear ".$DelegatedTL["name"].",<br><br>".$tempContent;
					
					if($arrMovementInfo["delegatedtlapprovalcode"] != "")
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
			}
			return true;
		}

		function fnCheckShiftMovementEligable($EmployeeId, $MovementDate)
		{
			/*$sSQL = "select * from pms_leave_form where '$MovementDate' BETWEEN date_format(start_date,'%Y-%m-%d') AND date_format(end_date,'%Y-%m-%d') and employee_id='$EmployeeId' and ((status='1' and status_manager='0') or status_manager='1' or (status='0' and status_manager='0'))";*/
			
			/* check if leaves for the user is added */
			
			//$sSQL = "select * from pms_leave_form where '$MovementDate' BETWEEN date_format(start_date,'%Y-%m-%d') AND date_format(end_date,'%Y-%m-%d') and employee_id='$EmployeeId' and (status_manager IN(0,1) or (status_manager='0' and deligateManagerId != 0 and manager_delegate_status in (0,1)))";
			
			//$sSQL = "select * from pms_leave_form where '$MovementDate' BETWEEN date_format(start_date,'%Y-%m-%d') AND date_format(end_date,'%Y-%m-%d') and employee_id='$EmployeeId' and (status_manager IN(0,1) or (status_manager='0' and deligateManagerId != 0 and manager_delegate_status in (0,1))) and ((status!=2 or (deligateTeamLeaderId != '' and manager_delegate_status=2 )) and (status_manager='0' or (deligateManagerId != 0 and manager_delegate_status ='0')))";
			
			$sSQL = "select * from pms_leave_form where '$MovementDate' BETWEEN date_format(start_date,'%Y-%m-%d') AND date_format(end_date,'%Y-%m-%d') and employee_id='$EmployeeId' and (((status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1))) and isactive='0') or (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1'))) and (status != 2 and delegate_status!=2 AND status_manager !=2 AND manager_delegate_status !=2)";
			
			$this->query($sSQL);
			if($this->num_rows() > 0)
				return false;
			else
			{
				//$sSQL = "select * from pms_half_leave_form where date_format(start_date,'%Y-%m-%d') = '$MovementDate' and employee_id='$EmployeeId' and (status_manager IN(0,1) or (status_manager='0' and deligateManagerId != 0 and manager_delegate_status in (0,1))) and ((status!=2 or (deligateTeamLeaderId != '' and manager_delegate_status=2 )) and (status_manager='0' or (deligateManagerId != 0 and manager_delegate_status ='0')))";
				
				$sSQL = "select * from pms_half_leave_form where date_format(start_date,'%Y-%m-%d') = '$MovementDate' and employee_id='$EmployeeId' and (((status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1))) and isactive='0') or (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1'))) and (status != 2 and delegate_status!=2 AND status_manager !=2 AND manager_delegate_status !=2)";
			
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

					$sSQL = "select * from pms_shift_movement where date_format(movement_date,'%Y-%m-%d') = '$MovementDate' and userid='$EmployeeId' and (((approvedby_manager IN(0,1) or (approvedby_manager ='0' and delegatedmanager_id!='0' and delegatedmanager_status IN(0,1))) and isactive='0') or (approvedby_manager ='1' or (approvedby_manager ='0' and delegatedmanager_id!='0' and delegatedmanager_status ='1'))) and (approvedby_tl != 2 and delegatedtl_status!=2 AND approvedby_manager !=2 AND delegatedmanager_status !=2) and isCancel='0'";
					
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
				$arrStatus = array('0' => 'Pending', '1' => 'Approved', '2' => 'Unapproved');
				
				while($this->next_record())
				{
					$row = $this->fetchrow();

					$row["tlapproval"] = 'Pending';
					if($this->f("approvedby_tl") != "")
						$row["tlapproval"] = $arrStatus[$this->f("approvedby_tl")];
					
					$row["managerapproval"] = 'Pending';
					if($this->f("approvedby_manager") != "")
						$row["managerapproval"] = $arrStatus[$this->f("approvedby_manager")];

					/*switch($this->f("approvedby_tl"))
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
					}*/

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
			
			/*if($_SESSION["designation"] == "6" || $_SESSION["designation"] == "18" || $_SESSION["designation"] == "19")
			{
				// Get Delegated Manager id
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
				// Get delegated teamleader id 
				$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);	
				
				if(count($arrDelegatedTeamLeaderId) > 0 )
				{
					foreach($arrDelegatedTeamLeaderId as $delegatesIds)
					{
						$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
						$arrEmployees = $arrEmployees + $arrtemp;
					}
				}
			}*/
			
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

			$sSQL = "select e.name as employeename, sm.id, date_format(sm.movement_date,'%d-%m-%Y') as movementdate, date_format(sm.compensation_date,'%d-%m-%Y') as compensationdate, date_format(sm.movement_fromtime,'%H:%i') as movementfrom, date_format(sm.movement_totime,'%H:%i') as movementto, date_format(sm.compensation_fromtime,'%H:%i') as compensationfrom, date_format(sm.compensation_totime,'%H:%i') as compensationto, sm.approvedby_tl, sm.approvedby_manager, sm.isCancel, sm.isemergency from pms_shift_movement sm INNER JOIN pms_employee e ON e.id = sm.userid where sm.userid in ($ids) and isemergency='1' and e.status='0' and (sm.reportinghead1='".mysql_real_escape_string($_SESSION["id"])."' or sm.reportinghead2='".mysql_real_escape_string($_SESSION["id"])."' or sm.delegatedtl_id='".mysql_real_escape_string($_SESSION["id"])."' or sm.delegatedmanager_id='".mysql_real_escape_string($_SESSION["id"])."')";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$arrStatus = array('0' => 'Pending', '1' => 'Approved', '2' => 'Unapproved');
				
				while($this->next_record())
				{
					$row = $this->fetchrow();

					$row["tlapproval"] = 'Pending';
					if($this->f("approvedby_tl") != "")
						$row["tlapproval"] = $arrStatus[$this->f("approvedby_tl")];
						
					$row["managerapproval"] = 'Pending';
					if($this->f("approvedby_manager") != "")
						$row["managerapproval"] = $arrStatus[$this->f("approvedby_manager")];
					
					/*switch($this->f("approvedby_tl"))
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
					}*/

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
				$arrStatus = array('0' => 'Pending', '1' => 'Approved', '2' => 'Unapproved');
				
				while($this->next_record())
				{
					$row = $this->fetchrow();

					$row["tlapproval"] = 'Pending';
					if($this->f("approvedby_tl") != "")
						$row["tlapproval"] = $arrStatus[$this->f("approvedby_tl")];

					/*switch($this->f("approvedby_tl"))
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
					}*/

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

		function fnUserShiftMovementCompensationById($CompensationId)
		{
			$arrMovements = array();
			
			/* Get shift movement compensation for the logged in employee by compensation id */
			
			$sSQL = "select smc.id as compensationid, date_format(sm.movement_date,'%Y-%m-%d') as movementdate, date_format(sm.movement_fromtime,'%H:%i') as movementfrom, date_format(sm.movement_totime,'%H:%i') as movementto, date_format(smc.compensation_date,'%Y-%m-%d') as compensationdate, date_format(smc.compensation_fromtime,'%H:%i') as compensationfrom, date_format(smc.compensation_totime,'%H:%i') as compensationto, smc.approvedby_tl, sm.reason, smc.tl_comment, smc.delegatedtl_id, smc.delegatedtl_status, smc.delegatedtl_comment, smc.isadminadded from pms_shift_movement sm INNER JOIN pms_shift_movement_compensation smc ON sm.id = smc.shift_movement_id where smc.userid='".mysql_real_escape_string($_SESSION["id"])."' and smc.id='$CompensationId'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$arrStatus = array('0' => 'Pending', '1' => 'Approved', '2' => 'Unapproved');
				
				while($this->next_record())
				{
					$row = $this->fetchrow();

					$row["tlapproval"] = 'Pending';
					if($this->f("approvedby_tl") != "")
						$row["tlapproval"] = $arrStatus[$this->f("approvedby_tl")];
						
					$row["delegatedtlapproval"] = 'Pending';
					if($this->f("delegatedtl_status") != "")
						$row["delegatedtlapproval"] = $arrStatus[$this->f("delegatedtl_status")];

					/*switch($this->f("approvedby_tl"))
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
					}*/

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
			
			/*if($_SESSION["designation"] == "6" || $_SESSION["designation"] == "18" || $_SESSION["designation"] == "19")
			{
				// Get Delegated Manager id
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
				// Get delegated teamleader id
				$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);	
				
				if(count($arrDelegatedTeamLeaderId) > 0 )
				{
					foreach($arrDelegatedTeamLeaderId as $delegatesIds)
					{
						$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
						$arrEmployee = $arrEmployee + $arrtemp ;
					}
				}
			}*/
			
			$temp1 = $objEmployee->fnGetAllemployees($_SESSION['id']);
			$arrEmployee = $temp1 + $arrEmployee;
			
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

			$sSQL = "select smc.id as compensationid, date_format(sm.movement_date,'%Y-%m-%d') as movementdate, date_format(sm.movement_fromtime,'%H:%i') as movementfrom, date_format(sm.movement_totime,'%H:%i') as movementto, date_format(smc.compensation_date,'%Y-%m-%d') as compensationdate, date_format(smc.compensation_fromtime,'%H:%i') as compensationfrom, date_format(smc.compensation_totime,'%H:%i') as compensationto, smc.approvedby_tl, sm.reason, e.name as employeename,e.id as employeeid, smc.tl_comment, smc.delegatedtl_status, smc.delegatedtl_comment, smc.delegatedtl_id, smc.firstreportingheadid, smc.isadminadded from pms_shift_movement sm INNER JOIN pms_shift_movement_compensation smc ON sm.id = smc.shift_movement_id INNER JOIN pms_employee e ON e.id = smc.userid where (smc.firstreportingheadid='".mysql_real_escape_string($_SESSION["id"])."' or smc.delegatedtl_id='".mysql_real_escape_string($_SESSION["id"])."' or smc.userid in ($ids)) and smc.id='$CompensationId'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$arrStatus = array('0' => 'Pending', '1' => 'Approved', '2' => 'Unapproved');
				
				while($this->next_record())
				{
					$row = $this->fetchrow();

					$row["tlapproval"] = 'Pending';
					if($this->f("approvedby_tl") != "")
						$row["tlapproval"] = $arrStatus[$this->f("approvedby_tl")];
					
					$row["delegatedtlapproval"] = 'Pending';
					if($this->f("delegatedtl_status") != "")
						$row["delegatedtlapproval"] = $arrStatus[$this->f("delegatedtl_status")];

					/*switch($this->f("approvedby_tl"))
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
					}*/

					$arrMovements = $row;
				}
			}

			return $arrMovements;
		}

		function fnUserShiftMovementById($ShiftMovementId)
		{
			$MovementInfo = false;

			/* Get shift movement of the logged in user by id */

			$sSQL = "select date_format(m.movement_date,'%d-%m-%Y') as movementdate, date_format(m.compensation_date,'%d-%m-%Y') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, m.reason, m.lt_comments, m.manager_comments, m.delegatedmanager_id, m.delegatedtl_id, m.delegatedtl_status, m.delegatedmanager_status, m.delegatedmanager_comment, m.delegatedtl_comment, reportinghead1, reportinghead2, m.isadminadded from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid where m.id='".mysql_real_escape_string($ShiftMovementId)."' and m.userid='".mysql_real_escape_string($_SESSION["id"])."' and e.status='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$arrStatus = array('0' => 'Pending', '1' => 'Approved', '2' => 'Unapproved');
				if($this->next_record())
				{
					$row = $this->fetchrow();

					$row["tlapproval"] = 'Pending';
					if($this->f("approvedby_tl") != "")
						$row["tlapproval"] = $arrStatus[$this->f("approvedby_tl")];

					$row["managerapproval"] = 'Pending';
					if($this->f("approvedby_manager") != "")
						$row["managerapproval"] = $arrStatus[$this->f("approvedby_manager")];

					$row["delegatedtlapproval"] = 'Pending';
					if($this->f("delegatedtl_status") != "")
						$row["delegatedtlapproval"] = $arrStatus[$this->f("delegatedtl_status")];

					$row["delegatedmanagerapproval"] = 'Pending';
					if($this->f("delegatedmanager_status") != "")
						$row["delegatedmanagerapproval"] = $arrStatus[$this->f("delegatedmanager_status")];

					/*switch($this->f("approvedby_tl"))
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
					}*/

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
				$arrStatus = array('0' => 'Pending', '1' => 'Approved', '2' => 'Unapproved');
				
				if($this->next_record())
				{
					$row = $this->fetchrow();

					$row["tlapproval"] = 'Pending';
					if($this->f("approvedby_tl") != "")
						$row["tlapproval"] = $arrStatus[$this->f("approvedby_tl")];
					
					$row["managerapproval"] = 'Pending';
					if($this->f("approvedby_manager") != "")
						$row["managerapproval"] = $arrStatus[$this->f("approvedby_manager")];
					
					$row["delegatedtlapproval"] = 'Pending';
					if($this->f("delegatedtl_status") != "")
						$row["delegatedtlapproval"] = $arrStatus[$this->f("delegatedtl_status")];
					
					$row["delegatedmanagerapproval"] = 'Pending';
					if($this->f("delegatedmanager_status") != "")
						$row["delegatedmanagerapproval"] = $arrStatus[$this->f("delegatedmanager_status")];

					/*switch($this->f("approvedby_tl"))
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
					}*/

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

			if($compensationInfo["delegatedtl_id"] == $_SESSION["id"] || (isset($ApprovalInfo["delegatedtl_id"]) && $ApprovalInfo["delegatedtl_id"] == $_SESSION["id"]))
			{
				/* If logged in user is delegated team leader */
				$DelegatedTlInfo = $objEmployee->fnGetEmployeeById($_SESSION["id"]);
				$ApprovalInfo["delegatedtl_date"] = Date("Y-m-d H:i:s");
				$this->updateArray('pms_shift_movement_compensation',$ApprovalInfo);
				
				if($compensationInfo["firstreportingheadid"] != "" && $compensationInfo["firstreportingheadid"] != "0")
				{
					$TlInfo = $objEmployee->fnGetEmployeeById($compensationInfo["firstreportingheadid"]);

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
			else if($compensationInfo["firstreportingheadid"] == $_SESSION["id"])
			{
				/* If logged in user is team leader */
				if($compensationInfo["firstreportingheadid"] != "" && $compensationInfo["firstreportingheadid"] != "0")
				{
					$TlInfo = $objEmployee->fnGetEmployeeById($compensationInfo["firstreportingheadid"]);
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
			else
			{
				return -1;
			}
		}

		function fnUserEmergencyShiftMovementById($ShiftMovementId)
		{
			$MovementInfo = false;
			/* Fetch emergency shift movement added by the logged in user and id */
			$sSQL = "select e.name as employeename, date_format(m.movement_date,'%Y-%m-%d') as movementdate, date_format(m.compensation_date,'%Y-%m-%d') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, m.reason, m.lt_comments, m.manager_comments, m.delegatedtl_status, m.delegatedmanager_status, m.delegatedmanager_id, m.delegatedtl_id from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid where m.id='".mysql_real_escape_string($ShiftMovementId)."' and (m.reportinghead1='".mysql_real_escape_string($_SESSION["id"])."' or m.reportinghead2='".mysql_real_escape_string($_SESSION["id"])."' or m.delegatedtl_id='".mysql_real_escape_string($_SESSION["id"])."' or m.delegatedmanager_id='".mysql_real_escape_string($_SESSION["id"])."') and isemergency='1' and e.status='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$arrStatus = array('0' => 'Pending', '1' => 'Approved', '2' => 'Unapproved');
				
				if($this->next_record())
				{
					$row = $this->fetchrow();

					$row["tlapproval"] = 'Pending';
					if($this->f("approvedby_tl") != "")
						$row["tlapproval"] = $arrStatus[$this->f("approvedby_tl")];
					
					$row["managerapproval"] = 'Pending';
					if($this->f("approvedby_manager") != "")
						$row["managerapproval"] = $arrStatus[$this->f("approvedby_manager")];

					$row["delegatedtlapproval"] = 'Pending';
					if($this->f("delegatedtl_status") != "")
						$row["delegatedtlapproval"] = $arrStatus[$this->f("delegatedtl_status")];

					$row["delegatedmanagerapproval"] = 'Pending';
					if($this->f("delegatedmanager_status") != "")
						$row["delegatedmanagerapproval"] = $arrStatus[$this->f("delegatedmanager_status")];

					/*switch($this->f("approvedby_tl"))
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
					}*/

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
		
		function fnAdminShiftMovementCompensationById($ShiftMovementCompId)
		{
			$MovementInfo = array();
			/* Fetch shift movement Compensation added by the admin */
			$sSQL = "select sm.reason as sm_rsn,smc.id as compensationid, e.name as employeename, date_format(sm.movement_date,'%d-%m-%Y') as movementdate, date_format(sm.movement_fromtime,'%H:%i') as movementfrom, date_format(sm.movement_totime,'%H:%i') as movementto, date_format(smc.compensation_date,'%d-%m-%Y') as compensationdate, date_format(smc.compensation_fromtime,'%H:%i') as compensationfrom, date_format(smc.compensation_totime,'%H:%i') as compensationto, smc.approvedby_tl from pms_shift_movement sm INNER JOIN pms_shift_movement_compensation smc ON sm.id = smc.shift_movement_id INNER JOIN pms_employee e ON e.id = smc.userid where smc.isadminadded='1' and shift_movement_id='$ShiftMovementCompId'";
			
			//$sSQL = "select e.name as employeename, date_format(m.movement_date,'%Y-%m-%d') as movementdate, date_format(m.compensation_date,'%Y-%m-%d') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, m.reason, m.lt_comments, m.manager_comments from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid where m.id='".mysql_real_escape_string($ShiftMovementId)."' and e.status='0' and m.isadminadded='1'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$MovementInfo = $this->fetchrow();
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
				$arrStatus = array('0' => 'Pending', '1' => 'Approved', '2' => 'Unapproved');
				
				if($this->next_record())
				{
					$row = $this->fetchrow();

					$row["tlapproval"] = 'Pending';
					if($this->f("approvedby_tl") != "")
						$row["tlapproval"] = $arrStatus[$this->f("approvedby_tl")];
					
					$row["managerapproval"] = 'Pending';
					if($this->f("approvedby_manager") != "")
						$row["managerapproval"] = $arrStatus[$this->f("approvedby_manager")];
					
					$row["delegatedtlapproval"] = 'Pending';
					if($this->f("delegatedtl_status") != "")
						$row["delegatedtlapproval"] = $arrStatus[$this->f("delegatedtl_status")];
					
					$row["delegatedmanagerapproval"] = 'Pending';
					if($this->f("delegatedmanager_status") != "")
						$row["delegatedmanagerapproval"] = $arrStatus[$this->f("delegatedmanager_status")];

					/*switch($this->f("approvedby_tl"))
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
					}*/

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
			$sSQL = "select m.id,m.userid, date_format(m.movement_date,'%d-%m-%Y') as movementdate, date_format(m.movement_date,'%Y-%m-%d') as movement_date, date_format(m.compensation_date,'%d-%m-%Y') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, e.id as eid, m.reason, e1.name as teamleadername, m.lt_comments, m.manager_comments,m.isCancel,m.delegatedmanager_id,m.delegatedmanager_status from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid INNER JOIN pms_employee e1 ON e.teamleader = e1.id where m.userid in ($EmployeeIds) and date_format(movement_date, '%Y-%m-%d') >= '".mysql_real_escape_string($prevdate)."'  and (((m.approvedby_manager IN(0,1) or (m.approvedby_manager ='0' and m.delegatedmanager_id!='0' and m.delegatedmanager_status IN(0,1))) and isactive='0') or (m.approvedby_manager ='1' or (m.approvedby_manager ='0' and m.delegatedmanager_id!='0' and m.delegatedmanager_status ='1'))) and (m.approvedby_tl != 2 and m.delegatedtl_status!=2 AND m.approvedby_manager !=2 AND m.delegatedmanager_status !=2)";
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

			/* Get delegated teamleader id */
			$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);

			/* Get Delegated Manager id */
			$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($_SESSION['id']);
			
			$arrDelegatedEmployee = array();
			$arrtemp = array();
			if(count($arrDelegatedTeamLeaderId) > 0 )
			{
				foreach($arrDelegatedTeamLeaderId as $delegatesIds)
				{
					$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
					$arrDelegatedEmployee =$arrDelegatedEmployee + $arrtemp ;
				}
			}
			if(count($arrDelegatedManagerId) > 0 )
			{
				foreach($arrDelegatedManagerId as $delegatesManagerIds)
				{
					$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
					$arrDelegatedEmployee =$arrDelegatedEmployee + $arrtemp ;
				}
			}

			$temp1 = $objEmployee->fnGetAllemployees($_SESSION['id']);
			$arrEmployee = $temp1 + $arrDelegatedEmployee;

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

			$sSQL = "select smc.id as compensationid, e.name as employeename, date_format(sm.movement_date,'%d-%m-%Y') as movementdate, date_format(sm.movement_fromtime,'%H:%i') as movementfrom, date_format(sm.movement_totime,'%H:%i') as movementto, date_format(smc.compensation_date,'%d-%m-%Y') as compensationdate, date_format(smc.compensation_fromtime,'%H:%i') as compensationfrom, date_format(smc.compensation_totime,'%H:%i') as compensationto, smc.approvedby_tl, smc.delegatedtl_id, smc.delegatedtl_status from pms_shift_movement sm INNER JOIN pms_shift_movement_compensation smc ON sm.id = smc.shift_movement_id INNER JOIN pms_employee e ON e.id = smc.userid where (smc.firstreportingheadid='".mysql_real_escape_string($_SESSION["id"])."' or smc.delegatedtl_id='".mysql_real_escape_string($_SESSION["id"])."' or e.id in ($ids)) and (smc.approvedby_tl='0' or (smc.approvedby_tl='0' and smc.delegatedtl_id!='0' and smc.delegatedtl_status='0')) and e.status='0'";
			
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$arrStatus = array('0' => 'Pending', '1' => 'Approved', '2' => 'Unapproved');
				
				while($this->next_record())
				{
					$row = $this->fetchrow();

					$row["tlapproval"] = 'Pending';
					if($this->f("approvedby_tl") != "")
						$row["tlapproval"] = $arrStatus[$this->f("approvedby_tl")];

					/*switch($this->f("approvedby_tl"))
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
					}*/

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

				/* Get designation of the employee for which the leave is to be added */
				$arrShiftMovementUser = $objEmployee->fnGetEmployeeById($MovementInfo["userid"]);

				if(isset($MovementInfo["reportinghead1"]) && trim($MovementInfo["reportinghead1"]) != "" && trim($MovementInfo["reportinghead1"]) != "0" && trim($MovementInfo["reportinghead1"]) == $_SESSION["id"])
				{
					$ApprovalInfo["lt_approval_date"] = Date("Y-m-d H:i:s");
					$this->updateArray('pms_shift_movement',$ApprovalInfo);

					/*$Subject = "Shift Movement ".$status[$ApprovalInfo["approvedby_tl"]];

					$arrReportingHead1User = $objEmployee->fnGetEmployeeById($MovementInfo["reportinghead1"]);

					$user_content = "Dear ".$arrShiftMovementUser["name"].",<br><br>".$arrReportingHead1User["name"]." has ".strtoupper($status[$ApprovalInfo["approvedby_tl"]])." your shift movement request on ".$MovementInfo["movementdate"]." for the time ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." hours.<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($arrShiftMovementUser["email"], $Subject, $user_content);

					$main_content = $arrReportingHead1User["name"]." has ".strtoupper($status[$ApprovalInfo["approvedby_tl"]])." shift movement request of ".$arrShiftMovementUser["name"]." on ".$MovementInfo["movementdate"]." for the time ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." hours.";

					if(isset($MovementInfo["reportinghead2"]) && trim($MovementInfo["reportinghead2"]) != "" && trim($MovementInfo["reportinghead2"]) != "0")
					{
						$arrReportingHead2User = $objEmployee->fnGetEmployeeById($MovementInfo["reportinghead2"]);

						$MailTo = $arrReportingHead2User["email"];

						$content = "Dear ".$arrReportingHead2User["name"].",<br><br>".$main_content;
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if(isset($MovementInfo["delegatedtl_id"]) && trim($MovementInfo["delegatedtl_id"]) != "" && trim($MovementInfo["delegatedtl_id"]) != "0")
					{
						$arrDelegateTLUser = $objEmployee->fnGetEmployeeById($MovementInfo["delegatedtl_id"]);

						$MailTo = $arrDelegateTLUser["email"];

						$content = "Dear ".$arrDelegateTLUser["name"].",<br><br>".$main_content;
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if(isset($MovementInfo["delegatedmanager_id"]) && trim($MovementInfo["delegatedmanager_id"]) != "" && trim($MovementInfo["delegatedmanager_id"]) != "0")
					{
						$arrDelegateManagerUser = $objEmployee->fnGetEmployeeById($MovementInfo["delegatedmanager_id"]);

						$MailTo = $arrDelegateManagerUser["email"];

						$content = "Dear ".$arrDelegateManagerUser["name"].",<br><br>".$main_content;
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}
					*/
					return $ApprovalInfo["approvedby_tl"];
				}
				else if(isset($MovementInfo["reportinghead2"]) && trim($MovementInfo["reportinghead2"]) != "" && trim($MovementInfo["reportinghead2"]) != "0" && trim($MovementInfo["reportinghead2"]) == $_SESSION["id"])
				{
					$ApprovalInfo["manager_approval_date"] = Date("Y-m-d H:i:s");
					$this->updateArray('pms_shift_movement',$ApprovalInfo);
					
					$Subject = "Shift Movement ".$status[$ApprovalInfo["approvedby_manager"]];

					$arrReportingHead2User = $objEmployee->fnGetEmployeeById($MovementInfo["reportinghead2"]);

					$user_content = "Dear ".$arrShiftMovementUser["name"].",<br><br>".$arrReportingHead2User["name"]." has ".strtoupper($status[$ApprovalInfo["approvedby_manager"]])." your shift movement request on ".$MovementInfo["movementdate"]." for the time ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." hours.<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($arrShiftMovementUser["email"], $Subject, $user_content);

					$main_content = $arrReportingHead2User["name"]." has ".strtoupper($status[$ApprovalInfo["approvedby_manager"]])." shift movement request of ".$arrShiftMovementUser["name"]." on ".$MovementInfo["movementdate"]." for the time ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." hours.";

					

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

					if(isset($MovementInfo["reportinghead1"]) && trim($MovementInfo["reportinghead1"]) != "" && trim($MovementInfo["reportinghead1"]) != "0")
					{
						$arrReportingHead1User = $objEmployee->fnGetEmployeeById($MovementInfo["reportinghead1"]);

						$MailTo = $arrReportingHead1User["email"];

						$content = "Dear ".$arrReportingHead1User["name"].",<br><br>".$main_content;
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if(isset($MovementInfo["delegatedtl_id"]) && trim($MovementInfo["delegatedtl_id"]) != "" && trim($MovementInfo["delegatedtl_id"]) != "0")
					{
						$arrDelegateTLUser = $objEmployee->fnGetEmployeeById($MovementInfo["delegatedtl_id"]);

						$MailTo = $arrDelegateTLUser["email"];

						$content = "Dear ".$arrDelegateTLUser["name"].",<br><br>".$main_content;
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if(isset($MovementInfo["delegatedmanager_id"]) && trim($MovementInfo["delegatedmanager_id"]) != "" && trim($MovementInfo["delegatedmanager_id"]) != "0")
					{
						$arrDelegateManagerUser = $objEmployee->fnGetEmployeeById($MovementInfo["delegatedmanager_id"]);

						$MailTo = $arrDelegateManagerUser["email"];

						$content = "Dear ".$arrDelegateManagerUser["name"].",<br><br>".$main_content;
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					return $ApprovalInfo["approvedby_manager"];
				}
				else if((isset($MovementInfo["delegatedtl_id"]) && trim($MovementInfo["delegatedtl_id"]) != "" && trim($MovementInfo["delegatedtl_id"]) != "0" && trim($MovementInfo["delegatedtl_id"]) == $_SESSION["id"]) || (isset($ApprovalInfo["delegatedtl_id"]) && $ApprovalInfo["delegatedtl_id"] == $_SESSION["id"]))
				{
					$ApprovalInfo["delegatedtl_date"] = Date("Y-m-d H:i:s");
					$this->updateArray('pms_shift_movement',$ApprovalInfo);
					
					/*$Subject = "Shift Movement ".$status[$ApprovalInfo["delegatedtl_status"]];

					$arrDelegatedTLUser = $objEmployee->fnGetEmployeeById($MovementInfo["delegatedtl_id"]);

					$user_content = "Dear ".$arrShiftMovementUser["name"].",<br><br>".$arrDelegatedTLUser["name"]." has ".strtoupper($status[$ApprovalInfo["delegatedtl_status"]])." your shift movement request on ".$MovementInfo["movementdate"]." for the time ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." hours.<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($arrShiftMovementUser["email"], $Subject, $user_content);

					$main_content = $arrDelegatedTLUser["name"]." has ".strtoupper($status[$ApprovalInfo["delegatedtl_status"]])." shift movement request of ".$arrShiftMovementUser["name"]." on ".$MovementInfo["movementdate"]." for the time ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." hours.";

					if(isset($MovementInfo["reportinghead1"]) && trim($MovementInfo["reportinghead1"]) != "" && trim($MovementInfo["reportinghead1"]) != "0")
					{
						$arrReportingHead1User = $objEmployee->fnGetEmployeeById($MovementInfo["reportinghead1"]);

						$MailTo = $arrReportingHead1User["email"];

						$content = "Dear ".$arrReportingHead1User["name"].",<br><br>".$main_content;
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if(isset($MovementInfo["reportinghead2"]) && trim($MovementInfo["reportinghead2"]) != "" && trim($MovementInfo["reportinghead2"]) != "0")
					{
						$arrReportingHead2User = $objEmployee->fnGetEmployeeById($MovementInfo["reportinghead2"]);

						$MailTo = $arrReportingHead2User["email"];

						$content = "Dear ".$arrReportingHead2User["name"].",<br><br>".$main_content;
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if(isset($MovementInfo["delegatedmanager_id"]) && trim($MovementInfo["delegatedmanager_id"]) != "" && trim($MovementInfo["delegatedmanager_id"]) != "0")
					{
						$arrDelegateManagerUser = $objEmployee->fnGetEmployeeById($MovementInfo["delegatedmanager_id"]);

						$MailTo = $arrDelegateManagerUser["email"];

						$content = "Dear ".$arrDelegateManagerUser["name"].",<br><br>".$main_content;
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}*/

					return $ApprovalInfo["delegatedtl_status"];
				}
				else if((isset($MovementInfo["delegatedmanager_id"]) && trim($MovementInfo["delegatedmanager_id"]) != "" && trim($MovementInfo["delegatedmanager_id"]) != "0" && trim($MovementInfo["delegatedmanager_id"]) == $_SESSION["id"]) || (isset($ApprovalInfo["delegatedmanager_id"]) && $ApprovalInfo["delegatedmanager_id"] == $_SESSION["id"]))
				{
					$ApprovalInfo["delegatedmanager_date"] = Date("Y-m-d H:i:s");
					$this->updateArray('pms_shift_movement',$ApprovalInfo);
					
					$MovementInfo = $this->fnShiftMovementById($ApprovalInfo["id"]);
					
					$Subject = "Shift Movement ".$status[$ApprovalInfo["delegatedmanager_status"]];

					$arrDelegatedManagerUser = $objEmployee->fnGetEmployeeById($MovementInfo["delegatedmanager_id"]);

					$user_content = "Dear ".$arrShiftMovementUser["name"].",<br><br>".$arrDelegatedManagerUser["name"]." has ".strtoupper($status[$ApprovalInfo["delegatedmanager_status"]])." your shift movement request on ".$MovementInfo["movementdate"]." for the time ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." hours.<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($arrShiftMovementUser["email"], $Subject, $user_content);

					$main_content = $arrDelegatedManagerUser["name"]." has ".strtoupper($status[$ApprovalInfo["delegatedmanager_status"]])." shift movement request of ".$arrShiftMovementUser["name"]." on ".$MovementInfo["movementdate"]." for the time ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]." hours.";

					/* If approved by manager update in attendance */
					if($ApprovalInfo["delegatedmanager_status"] == "1" && $MovementInfo["approvedby_manager"] == '0')
					{
						$arrInfo["user_id"] = $MovementInfo["userid"];
						$arrInfo["date"] = $MovementInfo["movementdate"];
						$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle("SM");

						$objAttendance->fnInsertRosterAttendance($arrInfo);
					}

					if(isset($MovementInfo["reportinghead1"]) && trim($MovementInfo["reportinghead1"]) != "" && trim($MovementInfo["reportinghead1"]) != "0")
					{
						$arrReportingHead1User = $objEmployee->fnGetEmployeeById($MovementInfo["reportinghead1"]);

						$MailTo = $arrReportingHead1User["email"];

						$content = "Dear ".$arrReportingHead1User["name"].",<br><br>".$main_content;
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if(isset($MovementInfo["reportinghead2"]) && trim($MovementInfo["reportinghead2"]) != "" && trim($MovementInfo["reportinghead2"]) != "0")
					{
						$arrReportingHead2User = $objEmployee->fnGetEmployeeById($MovementInfo["reportinghead2"]);

						$MailTo = $arrReportingHead2User["email"];

						$content = "Dear ".$arrReportingHead2User["name"].",<br><br>".$main_content;
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					if(isset($MovementInfo["delegatedtl_id"]) && trim($MovementInfo["delegatedtl_id"]) != "" && trim($MovementInfo["delegatedtl_id"]) != "0")
					{
						$arrDelegateTLUser = $objEmployee->fnGetEmployeeById($MovementInfo["delegatedtl_id"]);

						$MailTo = $arrDelegateTLUser["email"];

						$content = "Dear ".$arrDelegateTLUser["name"].",<br><br>".$main_content;
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					return $ApprovalInfo["delegatedmanager_status"];
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
		
		function fnCheckPendingShiftMovementRequestByUserId($UserId)
		{
			include_once("class.employee.php");
			$objEmployee = new employee();
			
			$current_date = date('Y-m-d');
			$prevdate = Date('Y-m-d', strtotime('-1 day'));
			$pending_shift_movement_count = 0;

			/* Get delegated teamleader id */
			$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($UserId);

			/* Get Delegated Manager id */
			$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($UserId);
			
			$arrEmployee = array();
			$arrtemp = array();
			if(count($arrDelegatedTeamLeaderId) > 0 )
			{
				foreach($arrDelegatedTeamLeaderId as $delegatesIds)
				{
					//echo $delegatesIds;
					$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
					$arrEmployee = $arrEmployee + $arrtemp;
				}
			}
			if(count($arrDelegatedManagerId) > 0 )
			{
				foreach($arrDelegatedManagerId as $delegatesManagerIds)
				{
					//echo $delegatesIds;
					$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
					$arrEmployee = $arrEmployee + $arrtemp;
				}
			}
			
			$arrEmployee[] = 0;
			
			if(count($arrEmployee) > 0)
			{
				$arrEmployee = array_filter($arrEmployee,'strlen');
			}
			
			$ids = "0";
			if(count($arrEmployee) > 0)
			{
				$ids = implode(',',$arrEmployee);
			}

			$sSQL = "select m.id, m.userid, date_format(movement_date,'%Y-%m-%d') as mdate, e.shiftid from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid INNER JOIN pms_employee e1 ON e.teamleader = e1.id where date_format(movement_date, '%Y-%m-%d') >= '".mysql_real_escape_string($prevdate)."' and ((m.reportinghead1='".mysql_real_escape_string($UserId)."' and (m.approvedby_tl='0' and m.delegatedtl_status in (0,null) and m.approvedby_manager = '0' and m.delegatedmanager_status in (0,null))) or (m.reportinghead2='".mysql_real_escape_string($UserId)."' and (m.approvedby_manager = '0' and m.approvedby_tl != '2' and m.delegatedtl_status != '2' and m.delegatedmanager_status in (0,null))) or ((m.delegatedtl_id='".mysql_real_escape_string($UserId)."' or (m.userid in ($ids) and '".mysql_real_escape_string($UserId)."' = (select delegate from pms_leave_form where employee_id = m.reportinghead1 and '$current_date' between DATE_FORMAT(`start_date`,'%Y-%m-%d') and DATE_FORMAT(`end_date`,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1')) ORDER BY `id` LIMIT 0,1))) and (m.delegatedtl_status in (0,null) and m.approvedby_tl ='0' and m.approvedby_manager = '0' and m.delegatedmanager_status in (0,null))) or ((m.delegatedmanager_id='".mysql_real_escape_string($UserId)."' or (m.userid in ($ids) and '".mysql_real_escape_string($UserId)."' = (select delegate from pms_leave_form where employee_id = m.reportinghead2 and '$current_date' between DATE_FORMAT(`start_date`,'%Y-%m-%d') and DATE_FORMAT(`end_date`,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1')) ORDER BY `id` LIMIT 0,1))) and m.delegatedmanager_status in (0,null) and m.approvedby_tl != '2' and m.delegatedtl_status != '2' and m.approvedby_manager = '0')) and m.isadminadded in (0,null)";
			$this->query($sSQL);

			include_once('includes/class.shifts.php');
			include_once('includes/class.attendance.php');

			$objShifts = new shifts();
			$objAttendance = new attendance();
			
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					if($this->f("mdate") == $prevdate)
					{
						$starttime = "00:00";
						$endtime = "00:00";

						/* Get data from attendance */
						$ShiftId = $objAttendance->fnGetAttendanceShiftByUserAndDate($this->f("userid"), $current_date);

						if($ShiftId == "" || $ShiftId == "0")
						{
							/* Get the default shift from the employee */
							$ShiftId = $this->f("shiftid");
						}

						$arrShift = $objShifts->fnGetShiftById($ShiftId);
						if(count($arrShift) > 0)
						{
							$starttime = $arrShift["starttime"];
							$endtime = $arrShift["endtime"];
						}

						if($endtime <= $starttime)
						{
							$pending_shift_movement_count++;
						}
					}
					else
					{
						$pending_shift_movement_count++;
					}
				}
			}

			return $pending_shift_movement_count;
		}
		
		function fnCheckPenaltyShiftMovementRequestCountByUserId($UserId)
		{
			include_once("class.employee.php");
			$objEmployee = new employee();

			$current_date = date('Y-m-d');
			$prevdate = Date('Y-m-d', strtotime('-1 day'));
			$shift_movement_compensation_count = 0;
			
			/* Get delegated teamleader id */
			$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($UserId);

			/* Get Delegated Manager id */
			$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($UserId);
			
			$arrEmployee = array();
			$arrtemp = array();
			if(count($arrDelegatedTeamLeaderId) > 0 )
			{
				foreach($arrDelegatedTeamLeaderId as $delegatesIds)
				{
					//echo $delegatesIds;
					$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
					$arrEmployee = $arrEmployee + $arrtemp;
				}
			}
			if(count($arrDelegatedManagerId) > 0 )
			{
				foreach($arrDelegatedManagerId as $delegatesManagerIds)
				{
					//echo $delegatesIds;
					$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
					$arrEmployee = $arrEmployee + $arrtemp;
				}
			}
			
			$arrEmployee[] = 0;
			
			if(count($arrEmployee) > 0)
			{
				$arrEmployee = array_filter($arrEmployee,'strlen');
			}
			
			$ids = "0";
			if(count($arrEmployee) > 0)
			{
				$ids = implode(',',$arrEmployee);
			}
			
			$sSQL = "select count(smc.id) as shift_movement_compensation_count from pms_shift_movement sm INNER JOIN pms_shift_movement_compensation smc ON sm.id = smc.shift_movement_id INNER JOIN pms_employee e ON e.id = smc.userid where (smc.firstreportingheadid='".mysql_real_escape_string($UserId)."' or smc.delegatedtl_id='".mysql_real_escape_string($UserId)."' or (smc.userid in ($ids) and '".mysql_real_escape_string($UserId)."' = (select delegate from pms_leave_form where employee_id = smc.firstreportingheadid and '$current_date' between DATE_FORMAT(`start_date`,'%Y-%m-%d') and DATE_FORMAT(`end_date`,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1')) ORDER BY `id` LIMIT 0,1))) and (smc.approvedby_tl='0' and  smc.delegatedtl_status in (0,null)) and e.status='0' and smc.isadminadded in (0,null)";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$shift_movement_compensation_count = $this->f("shift_movement_compensation_count");
				}
			}
			
			return $shift_movement_compensation_count;
		}
		function fnSaveAdminShiftMovementCompensation($arrMovementInfo)
		{
			
			/* Cannot add shift movement compensation after 7 days */
			$movementDetails = $this->fnShiftMovementById($arrMovementInfo["shift_movement_id"]);
			
			if(strtotime('+7 day', strtotime($movementDetails["movementdate"]." 00:00:00")) < strtotime($arrMovementInfo['compensation_date']." 00:00:00"))
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

			
			$arrMovementInfo["addedon"] = date('Y-m-d H:i:s');
			$arrMovementInfo["approvedby_tl"] = 0;

			include_once('class.employee.php');

			$objEmployee = new employee();

			$reportingHead = $objEmployee->fnGetReportingHeadId($arrMovementInfo["userid"]);

			$arrMovementInfo["firstreportingheadid"] = $reportingHead;

			$reportinghead2 = $objEmployee->fnGetReportingHeadId($reportingHead);

			$arrMovementInfo["secondreportingheadid"] = $reportinghead2;
			
			$arrMovementInfo["tlapprovalcode"] = shiftmovementcompensationform_uid();

			/* BEGIN - Check if delegated to other tl, add the delegated TL */

			include_once("class.leave.php");

			$objLeave = new leave();

			/* get user designation */
			/*$head_designation = $objEmployee->fnGetEmployeeDesignation($reportingHead);
			if($head_designation == "6" || $head_designation == "18" || $head_designation == "19")
				$checkDeligateReportingHead1Id = $objLeave->fnCheckDeligateManager($reportingHead);
			else*/
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
			
			$arrMovementInfo['approvedby_tl'] = '1';
			$arrMovementInfo['isadminadded'] = '1';
			//echo '<pre>';print_r($arrMovementInfo);
			 //die;
			$id = $this->insertArray('pms_shift_movement_compensation',$arrMovementInfo);

			$employeeInfo = $objEmployee->fnGetEmployeeDetailById($arrMovementInfo["userid"]);
			$tlInfo = $objEmployee->fnGetEmployeeDetailById($reportingHead);

			/* Mail */
			
			/* Common mail content */
			
			$tempContent = "Admin has added a compensation for ".$employeeInfo["name"]." shift movement on ".$movementDetails["movementdate"].". The details for the shift movement compensation are as follows:<br/><br/>";
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
				
				

				$content .= "</table>";
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

				sendmail($MailTo, $Subject, $content);
			}

			return true;
		}
		function fnAdminShiftMovementCompensation()
		{
			$arrMovements = array();

			/* Get all shift movements added by the admin */
			//$sSQL = "select e.name as employeename, sm.id, date_format(sm.movement_date,'%d-%m-%Y') as movementdate, date_format(sm.compensation_date,'%d-%m-%Y') as compensationdate, date_format(sm.movement_fromtime,'%H:%i') as movementfrom, date_format(sm.movement_totime,'%H:%i') as movementto, date_format(sm.compensation_fromtime,'%H:%i') as compensationfrom, date_format(sm.compensation_totime,'%H:%i') as compensationto, sm.approvedby_tl, sm.approvedby_manager, sm.isCancel, sm.isemergency from pms_shift_movement sm INNER JOIN pms_employee e ON e.id = sm.userid where isadminadded='1' and e.status='0'";
			
			$sSQL = "select e.name as employeename, sm.id, date_format(sm.movement_date,'%d-%m-%Y') as movementdate, date_format(sm.movement_fromtime,'%H:%i') as movementfrom, date_format(sm.movement_totime,'%H:%i') as movementto, date_format(smc.compensation_date,'%d-%m-%Y') as compensation_date, date_format(smc.compensation_fromtime,'%H:%i') as compensationfrom, date_format(smc.compensation_totime,'%H:%i') as compensationto, sm.isCancel from pms_shift_movement sm INNER JOIN pms_employee e ON e.id = sm.userid INNER JOIN pms_shift_movement_compensation smc ON sm.id = smc.shift_movement_id where smc.isadminadded='1' and e.status='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$row = $this->fetchrow();

					/*switch($this->f("approvedby_tl"))
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
					}*/

					$arrMovements[] = $row;
				}
			}

			return $arrMovements;
		}
		function fnGetAllAdminShiftMovementsEmployee($start_date,$end_date)
		{
			/*$start_date = $year.'-'.$month.'-'.'01';
			$end_date = date("Y-m-t", strtotime($start_date));*/
			
			$arrShiftMovementInformation = array();
			$db = new DB_Sql();
			
			$query = "SELECT m.id AS mid, m.userid AS eid,pe.name as tll_name, date_format( smcom.compensation_date, '%Y-%m-%d' ) AS com_date,TIME_FORMAT(m.movement_fromtime,'%H:%i') as smTime,TIME_FORMAT(m.movement_totime,'%H:%i') as smToTime, TIME_FORMAT(smcom.compensation_fromtime,'%H:%i') AS smcomp_fromTime, TIME_FORMAT(smcom.compensation_totime,'%H:%i') AS smcomp_ToTime, date_format( m.movement_date, '%Y-%m-%d' ) AS m_date,e.name as e_name,smcom.approvedby_tl as appr_tl FROM `pms_shift_movement` AS m LEFT JOIN pms_employee AS e ON e.id = m.userid left join pms_employee as pe on e.teamleader=pe.id LEFT JOIN pms_shift_movement_compensation AS smcom ON m.id = smcom.shift_movement_id WHERE DATE_FORMAT(m.movement_date,'%Y-%m-%d') between '".mysql_real_escape_string($start_date)."' AND '".mysql_real_escape_string($end_date)."' and m.isadminadded='1'";

			$this->query($query);
			
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					
					$arrShiftMovementInformation[] = $this->fetchrow();
				}
			}
			return $arrShiftMovementInformation;
		}
		function fnGetAllAdminShiftMovementsCompensationEmployee($start_date,$end_date)
		{
			/*$start_date = $year.'-'.$month.'-'.'01';
			$end_date = date("Y-m-t", strtotime($start_date));*/
			
			$arrShiftMovementInformation = array();
			$db = new DB_Sql();
			
			$query = "SELECT m.id AS mid, m.userid AS eid,pe.name as tll_name, date_format( smcom.compensation_date, '%Y-%m-%d' ) AS com_date, smcom.compensation_fromtime AS smcomp_fromTime, smcom.compensation_totime AS smcomp_ToTime, date_format( m.movement_date, '%Y-%m-%d' ) AS m_date,e.name as e_name,smcom.approvedby_tl as appr_tl,TIME_FORMAT(m.movement_fromtime,'%H:%i') as smTime,TIME_FORMAT(m.movement_totime,'%H:%i') as smToTime, TIME_FORMAT(smcom.compensation_fromtime,'%H:%i') AS smcomp_fromTime, TIME_FORMAT(smcom.compensation_totime,'%H:%i') AS smcomp_ToTime FROM `pms_shift_movement` AS m LEFT JOIN pms_employee AS e ON e.id = m.userid left join pms_employee as pe on e.teamleader=pe.id  LEFT JOIN pms_shift_movement_compensation AS smcom ON m.id = smcom.shift_movement_id WHERE DATE_FORMAT(m.movement_date,'%Y-%m-%d') between '".mysql_real_escape_string($start_date)."' AND '".mysql_real_escape_string($end_date)."' and smcom.isadminadded='1'";
			
			$this->query($query);
			
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					
					$arrShiftMovementInformation[] = $this->fetchrow();
				}
			}
			return $arrShiftMovementInformation;
		}
	}
?>

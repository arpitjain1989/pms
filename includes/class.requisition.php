<?php

	include_once('db_mysql.php');

	class requisition extends DB_Sql
	{
		function __construct()
		{
		}
		
		/*
		 * Save requisition
		 * */
		function fnSaveRequisition($arrRequisition)
		{
			include_once("class.requisition_inventory.php");
			$objRequisitionInventory = new requisition_inventory();

			$arrRequisitionInfo = $objRequisitionInventory->fnGetRequisitionInventoryById($arrRequisition["requisition_for"]);

			//$arrAllowedDesignation = array(7,13,6,18,19,25);
			$arrAllowedDesignation = array(0);
			if(isset($arrRequisitionInfo["allowed_designation"]) && trim($arrRequisitionInfo["allowed_designation"]) != "")
				$arrAllowedDesignation = explode(",", $arrRequisitionInfo["allowed_designation"]);
				
			if(in_array($_SESSION["designation"], $arrAllowedDesignation))
			{
				include_once("class.employee.php");
				include_once("class.designation.php");

				$objEmployee = new employee();
				$objDesignation = new designations();

				$type = array("1"=>"Permanent", "2"=>"Temporary");

				/* Fetch details for the user designation */
				$arrDesignationInfo = $objDesignation->fnGetDesignationById($_SESSION["designation"]);

				/* Fetch reporting head hierarchy */
				$arrHeads = $objEmployee->fnGetReportHeadHierarchy($_SESSION["id"]);
				
				$FirstReportingHead = 0;
				if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
				{
					$FirstReportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
				}

				$arrRequisition["user_id"] = $_SESSION["id"];
				$arrRequisition["reporting_head"] = $FirstReportingHead;
				$arrRequisition["approval_status"] = 0;
				$arrRequisition["addedon"] = Date('Y-m-d H:i:s');
				$arrRequisition["approvalcode"] = requisition_uid();
				$arrRequisition["isclosed"] = 0;
				$arrRequisition["is_auto_approved"] = 0;

				/* Check if requisition for requires approval or not */
				$isApprovalRequired = $objRequisitionInventory->fnGetApprovalRequiredForRequisitionInventory($arrRequisition["requisition_for"]);
				if($isApprovalRequired == 0)
				{
					/* Approval not required so approve this automatically */
					$arrRequisition["is_auto_approved"] = 1;
				}

				/* Begin Block for delegation */
				include_once("class.leave.php");

				$objLeave = new leave();

				$checkDeligateReportingHead1Id = $objLeave->fnCheckDeligate($arrRequisition["reporting_head"]);

				$delegateReportingHead1 = 0;
				if(isset($checkDeligateReportingHead1Id) && $checkDeligateReportingHead1Id != '')
				{
					$delegateReportingHead1 = $checkDeligateReportingHead1Id;
				}

				$arrRequisition["delegated_reporting_head_id"] = $delegateReportingHead1;
				$arrRequisition["delegated_reporting_head_status"] = 0;

				if($arrRequisition["delegated_reporting_head_id"] != "")
					$arrRequisition["delegated_reporting_head_approvalcode"] = requisition_uid();

				$id = $this->insertArray("pms_requisition",$arrRequisition);
				
				/* This was an un necessary change ask and then asked to be removed
				 * 
				 * if($arrRequisition["request_type"] == "2")
				{
					// If temporary request enter in log
					$arrRequisitionLog["requisition_id"] = $id;
					$arrRequisitionLog["added_by"] = $arrRequisition["user_id"];
					$arrRequisitionLog["requisition_till_date"] = $arrRequisition["till_date"];
					$arrRequisitionLog["addedon"] = Date('Y-m-d H:i:s');

					$this->fnSaveRequisitionLog($arrRequisitionLog);
				}*/

				$employeeInfo = $objEmployee->fnGetEmployeeDetailById($_SESSION["id"]);

				$Subject = "New Requisition";
				$tempContent = "You have added a requisition request. The details for request are as follows:<br/><br/>";
				$tableContent = "<table cellspacing='2' cellpadding='2' bgcolor='#E6E6E6'>
					<tr bgcolor='#FFFFFF'>
						<td><b>Requisition For: </b></td>
						<td>".$objRequisitionInventory->fnGetRequisitionInventoryTitleById($arrRequisition["requisition_for"])."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>Project: </b></td>
						<td>".$arrRequisition["project_name"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>Remarks: </b></td>
						<td>".$arrRequisition["remarks"]."</td>
					</tr>
					<tr bgcolor='#FFFFFF'>
						<td><b>Requirement Type: </b></td>
						<td>".$type[$arrRequisition["request_type"]]."</td>
					</tr>";
				if($arrRequisition["request_type"] == 2)
				{
					$tableContent .= "<tr bgcolor='#FFFFFF'>
						<td><b>Till Date: </b></td>
						<td>".$arrRequisition["till_date"]."</td>
					</tr>";
				}
				$tableContent .= "</table>";
				
				$footerContent = "<br><br>Regards,<br>".SITEADMINISTRATOR;
				
				/* send mail to user */
				if(isset($employeeInfo["email"]) && trim($employeeInfo["email"]) != "")
				{
					$content = "Dear ".$employeeInfo["name"].",<br><br>".$tempContent.$tableContent.$footerContent;
					sendmail($employeeInfo["email"], $Subject, $content);
					
					/* Send mail to reporting head */
					$reportingHeadInfo = $objEmployee->fnGetEmployeeDetailById($FirstReportingHead);
					if(isset($reportingHeadInfo["email"]) && trim($reportingHeadInfo["email"]) != "")
					{
						$tempContent = "Requisition request has been added by <b>".$employeeInfo["name"]."</b>. The details for request are as follows:<br/><br/>";

						$content = "Dear ".$reportingHeadInfo["name"].",<br><br>".$tempContent.$tableContent;
						
						if($isApprovalRequired == 1)
						{
							/* Approval required */
							$content .= "Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrRequisition["approvalcode"]."_Approve_R]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrRequisition["approvalcode"]."_Reject_R]'>Reject</a></b> for letting us know your decision.";
						}

						$content .= $footerContent;

						sendmail($reportingHeadInfo["email"], $Subject, $content);
						
						/* Send mail to delegated reporting head */
						if($arrRequisition["delegated_reporting_head_id"] != "" && $arrRequisition["delegated_reporting_head_id"] != "0")
						{
							$delegatedReportingHeadInfo = $objEmployee->fnGetEmployeeDetailById($arrRequisition["delegated_reporting_head_id"]);

							if(isset($delegatedReportingHeadInfo["email"]) && $delegatedReportingHeadInfo["email"] != "")
							{
								$tempContent = "Requisition request has been added by <b>".$employeeInfo["name"]."</b>. The details for request are as follows:<br/><br/>";

								$content = "Dear ".$delegatedReportingHeadInfo["name"].",<br><br>".$tempContent.$tableContent;

								if($isApprovalRequired == 1 && $arrRequisition["delegated_reporting_head_approvalcode"] != "")
								{
									/* Approval required */
									$content .= "Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrRequisition["delegated_reporting_head_approvalcode"]."_Approve_R]'>Approve </a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$arrRequisition["delegated_reporting_head_approvalcode"]."_Reject_R]'>Reject</a></b> for letting us know your decision.";
								}

								$content .= $footerContent;

								sendmail($delegatedReportingHeadInfo["email"], $Subject, $content);
							}
						}
						
						/* Send mail to IT Support */
						if($isApprovalRequired == 0)
						{
							$MailTo = "itsupport@transformsolution.net";
							$Subject = "New Requisition";
							$content = "Dear IT Support team,<br><br>";
							$content .= $tempContent.$tableContent;
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

							sendmail($MailTo, $Subject, $content);
						}
					}
				}
				
				return true;
			}
			else
			{
				return false;
			}
		}
		
		function fnGetRequisitionByEmployee($UserId)
		{
			$arrRequisition = array();
			
			$sSQL = "select r.*, r.id as requisitionid, rf.title as requisition_for_title, date_format(addedon,'%d-%m-%Y') as addeddate from pms_requisition r LEFT JOIN pms_requisition_for rf ON r.requisition_for = rf.id where r.user_id='".mysql_real_escape_string($UserId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$arrType = array("1"=>"Permanent", "2"=>"Temporary");
				
				while($this->next_record())
				{
					$tempRequisition = $this->fetchRow();
					
					$tempRequisition["request_type_text"] = $arrType[$tempRequisition["request_type"]];
					
					$arrRequisition[] = $tempRequisition;
				}
			}

			return $arrRequisition;
		}
		
		function fnGetUserRequisitionById($RequisitionId, $UserId)
		{
			$RequisitionInformation = array();

			$sSQL = "select *, rf.title as requisition_for_title, date_format(till_date,'%d-%m-%Y') as till_date, if(isclosed=0,'No','Yes') as isclosed_text from pms_requisition r LEFT JOIN pms_requisition_for rf ON r.requisition_for = rf.id where r.user_id='".mysql_real_escape_string($UserId)."' and r.id='".mysql_real_escape_string($RequisitionId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrType = array("1"=>"Permanent", "2"=>"Temporary");
					$arrStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");

					$RequisitionInformation = $this->fetchRow();

					$RequisitionInformation["request_type_text"] = $arrType[$RequisitionInformation["request_type"]];
					$RequisitionInformation["approval_status_text"] = $arrStatus[$RequisitionInformation["approval_status"]];
					if($RequisitionInformation["delegated_reporting_head_status"] != "")
						$RequisitionInformation["delegated_reporting_head_status_text"] = $arrStatus[$RequisitionInformation["delegated_reporting_head_status"]];
					else
						$RequisitionInformation["delegated_reporting_head_status_text"] = 'Pending';
					
					/* If auto approved mark as approved */
					if(isset($RequisitionInformation["is_auto_approved"]) && $RequisitionInformation["is_auto_approved"] == "1")
						$RequisitionInformation["approval_status_text"] = "Approved";
				}
			}

			return $RequisitionInformation;
		}
		
		function fnGetRequisitionRequest($TeamLeaderId, $viewAll = 0)
		{
			$RequisitionInformation = array();
			
			include_once("class.employee.php");
			$objEmployee = new employee();

			/* Fetch employees who are delegated */
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
					$arrEmployee = $arrEmployee + $arrtemp;
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
			
			if($viewAll == 1)
				$sSQL = "select r.*, r.id as requisitionid, rf.title as requisition_for_title, date_format(addedon,'%d-%m-%Y') as addeddate, e.name as name from pms_requisition r LEFT JOIN pms_requisition_for rf ON r.requisition_for = rf.id LEFT JOIN pms_employee e ON e.id = r.user_id where (r.reporting_head='".mysql_real_escape_string($TeamLeaderId)."' or r.delegated_reporting_head_id='".mysql_real_escape_string($TeamLeaderId)."' or r.user_id in ($ids))";
			else
				$sSQL = "select r.*, r.id as requisitionid, rf.title as requisition_for_title, date_format(addedon,'%d-%m-%Y') as addeddate, e.name as name from pms_requisition r LEFT JOIN pms_requisition_for rf ON r.requisition_for = rf.id LEFT JOIN pms_employee e ON e.id = r.user_id where (r.reporting_head='".mysql_real_escape_string($TeamLeaderId)."' or r.delegated_reporting_head_id='".mysql_real_escape_string($TeamLeaderId)."' or r.user_id in ($ids)) and approval_status='0' and delegated_reporting_head_status='0' and is_auto_approved='0'";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$arrType = array("1"=>"Permanent", "2"=>"Temporary");
				$arrStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");

				while($this->next_record())
				{
					$arrTemp = $this->fetchRow();
					
					$arrTemp["request_type_text"] = $arrType[$arrTemp["request_type"]];
					$arrTemp["approval_status_text"] = $arrStatus[$arrTemp["approval_status"]];

					/* If auto approved mark as approved */
					if(isset($arrTemp["is_auto_approved"]) && $arrTemp["is_auto_approved"] == "1")
						$arrTemp["approval_status_text"] = "Approved";

					$RequisitionInformation[] = $arrTemp;
				}
			}
			
			return $RequisitionInformation;
		}
		
		function fnGetRequisitionRequestById($RequisitionId, $TeamLeaderId)
		{
			$RequisitionInformation = array();
			
			include_once("class.employee.php");
			$objEmployee = new employee();

			/* Fetch employees who are delegated */
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
					$arrEmployee = $arrEmployee + $arrtemp;
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
			
			$sSQL = "select r.*, r.id as requisitionid, e.name as name, rf.title as requisition_for_title, date_format(addedon,'%d-%m-%Y') as addeddate, date_format(till_date,'%d-%m-%Y') as till_date, if(isclosed=0,'No','Yes') as isclosed_text from pms_requisition r LEFT JOIN pms_requisition_for rf ON r.requisition_for = rf.id LEFT JOIN pms_employee e ON e.id = r.user_id where (r.reporting_head='".mysql_real_escape_string($TeamLeaderId)."' or r.delegated_reporting_head_id='".mysql_real_escape_string($TeamLeaderId)."' or r.user_id in ($ids)) and r.id='".mysql_real_escape_string($RequisitionId)."'";
			$this->query($sSQL);
			
			if($this->num_rows() > 0)
			{
				$arrType = array("1"=>"Permanent", "2"=>"Temporary");
				$arrStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");

				if($this->next_record())
				{
					$RequisitionInformation = $this->fetchRow();

					$RequisitionInformation["request_type_text"] = $arrType[$RequisitionInformation["request_type"]];
					$RequisitionInformation["approval_status_text"] = $arrStatus[$RequisitionInformation["approval_status"]];
					$RequisitionInformation["delegated_reporting_head_status_text"] = $arrStatus[$RequisitionInformation["delegated_reporting_head_status"]];
					
					/* If auto approved mark as approved */
					if(isset($RequisitionInformation["is_auto_approved"]) && $RequisitionInformation["is_auto_approved"] == "1")
						$RequisitionInformation["approval_status_text"] = "Approved";
				}
			}

			return $RequisitionInformation;
		}
		
		function fnUpdateRequisitionRequest($RequisitionInformation)
		{
			/* Update approval status */
			if(isset($RequisitionInformation["delegated_reporting_head_status"]) && $RequisitionInformation["delegated_reporting_head_status"] != 0)
			{
				$arrApprovalInfo["id"] = $RequisitionInformation["id"];
				$arrApprovalInfo["delegated_reporting_head_status"] = $RequisitionInformation["delegated_reporting_head_status"];
				$arrApprovalInfo["delegated_reporting_head_remarks"] = $RequisitionInformation["delegated_reporting_head_remarks"];
				$arrApprovalInfo["delegated_approval_date"] = Date("Y-m-d H:i:s");

				if(isset($RequisitionInformation["delegated_reporting_head_id"]) && trim($RequisitionInformation["delegated_reporting_head_id"]) != "")
					$arrApprovalInfo["delegated_reporting_head_id"] = $RequisitionInformation["delegated_reporting_head_id"];

				$this->updateArray("pms_requisition",$arrApprovalInfo);

				$RequisitionInfo = $this->fnGetRequisitionRequestById($arrApprovalInfo["id"], $_SESSION["id"]);

				include_once("class.employee.php");
				include_once("includes/class.requisition_inventory.php");

				$objRequisitionInventory = new requisition_inventory();
				$objEmployee = new employee();

				$EmployeeInfo = $objEmployee->fnGetEmployeeById($RequisitionInfo["user_id"]);
				$DelegateReportingHeadInfo = $objEmployee->fnGetEmployeeById($RequisitionInfo["delegated_reporting_head_id"]);
				
				$status = array("0"=>"Pending", "1"=>"Approved", "2"=>"Unapproved");

				/* Send mail to user */
				$MailTo = $EmployeeInfo["email"];
				$Subject = "Requisition ".$status[$arrApprovalInfo["delegated_reporting_head_status"]];
				
				$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
				$content .= $DelegateReportingHeadInfo["name"]." has ".strtoupper($status[$arrApprovalInfo["delegated_reporting_head_status"]])." your request for requisition for ".$objRequisitionInventory->fnGetRequisitionInventoryTitleById($RequisitionInfo["requisition_for"]).".";
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

				sendmail($MailTo, $Subject, $content);

				/* send mail to delegated reporting head */
				$MailTo = $DelegateReportingHeadInfo["email"];

				$content = "Dear ".$DelegateReportingHeadInfo["name"].",<br><br>";
				$content .= "You have ".strtoupper($status[$arrApprovalInfo["delegated_reporting_head_status"]])." <b>".$EmployeeInfo["name"]."'s</b> requisition request of ".$objRequisitionInventory->fnGetRequisitionInventoryTitleById($RequisitionInfo["requisition_for"]).".";
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

				sendmail($MailTo, $Subject, $content);

				/* Send mail to reporting head */
				$ReportingHeadInfo = $objEmployee->fnGetEmployeeById($RequisitionInfo["reporting_head"]);
				if(count($ReportingHeadInfo) > 0)
				{
					$MailTo = $ReportingHeadInfo["email"];

					$content = "Dear ".$ReportingHeadInfo["name"].",<br><br>";
					$content .=$DelegateReportingHeadInfo["name"]." has ".strtoupper($status[$arrApprovalInfo["delegated_reporting_head_status"]])." <b>".$EmployeeInfo["name"]."'s</b> requisition request of ".$objRequisitionInventory->fnGetRequisitionInventoryTitleById($RequisitionInfo["requisition_for"]).".";
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);
				}

				/* Send mail to it support */
				if($RequisitionInformation["delegated_reporting_head_status"] == "1")
				{
					$MailTo = "itsupport@transformsolution.net";

					$content = "Dear IT Support team,<br><br>";
					$content .= $DelegateReportingHeadInfo["name"]." has ".strtoupper($status[$arrApprovalInfo["delegated_reporting_head_status"]])." <b>".$EmployeeInfo["name"]."'s</b> request for requisition for ".$objRequisitionInventory->fnGetRequisitionInventoryTitleById($RequisitionInfo["requisition_for"]).".";
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);
				}

				return true;
			}
			if(isset($RequisitionInformation["approval_status"]) && $RequisitionInformation["approval_status"] != 0)
			{
				$arrApprovalInfo["id"] = $RequisitionInformation["id"];
				$arrApprovalInfo["approval_status"] = $RequisitionInformation["approval_status"];
				$arrApprovalInfo["reporting_head_remarks"] = $RequisitionInformation["reporting_head_remarks"];
				$arrApprovalInfo["approval_date"] = Date("Y-m-d H:i:s");

				$this->updateArray("pms_requisition",$arrApprovalInfo);

				$RequisitionInfo = $this->fnGetRequisitionRequestById($arrApprovalInfo["id"], $_SESSION["id"]);

				include_once("class.employee.php");
				include_once("includes/class.requisition_inventory.php");

				$objRequisitionInventory = new requisition_inventory();
				$objEmployee = new employee();

				$EmployeeInfo = $objEmployee->fnGetEmployeeById($RequisitionInfo["user_id"]);
				$ReportingHeadInfo = $objEmployee->fnGetEmployeeById($RequisitionInfo["reporting_head"]);

				$status = array("0"=>"Pending", "1"=>"Approved", "2"=>"Unapproved");

				/* Send mail to user */
				$MailTo = $EmployeeInfo["email"];
				$Subject = "Requisition ".$status[$RequisitionInformation["approval_status"]];

				$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
				$content .= $ReportingHeadInfo["name"]." has ".strtoupper($status[$RequisitionInformation["approval_status"]])." your request for requisition for ".$objRequisitionInventory->fnGetRequisitionInventoryTitleById($RequisitionInfo["requisition_for"]).".";
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

				sendmail($MailTo, $Subject, $content);

				/* send mail to reporting head */
				$MailTo = $ReportingHeadInfo["email"];

				$content = "Dear ".$ReportingHeadInfo["name"].",<br><br>";
				$content .= "You have ".strtoupper($status[$RequisitionInformation["approval_status"]])."   <b>".$EmployeeInfo["name"]."'s</b> requisition request of ".$objRequisitionInventory->fnGetRequisitionInventoryTitleById($RequisitionInfo["requisition_for"]).".";
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

				sendmail($MailTo, $Subject, $content);

				/* If delegate reporting head exisits, send mail to delegated head */
				if($RequisitionInfo["delegated_reporting_head_id"] != "" && $RequisitionInfo["delegated_reporting_head_id"] != "0")
				{
					$DelegatedReportingHeadInfo = $objEmployee->fnGetEmployeeById($RequisitionInfo["delegated_reporting_head_id"]);
					
					$MailTo = $DelegatedReportingHeadInfo["email"];

					$content = "Dear ".$DelegatedReportingHeadInfo["name"].",<br><br>";
					$content .= $ReportingHeadInfo["name"]." has ".strtoupper($status[$RequisitionInformation["approval_status"]])."   <b>".$EmployeeInfo["name"]."'s</b> requisition request of ".$objRequisitionInventory->fnGetRequisitionInventoryTitleById($RequisitionInfo["requisition_for"]).".";
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);
				}

				/* Send mail to it support */
				if($RequisitionInformation["approval_status"] == "1")
				{
					$MailTo = "itsupport@transformsolution.net";

					$content = "Dear IT Support team,<br><br>";
					$content .= $ReportingHeadInfo["name"]." has ".strtoupper($status[$RequisitionInformation["approval_status"]])." <b>".$EmployeeInfo["name"]."'s</b> request for requisition for ".$objRequisitionInventory->fnGetRequisitionInventoryTitleById($RequisitionInfo["requisition_for"]).".";
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);
				}

				return true;
			}
			else
			{
				return false;
			}
		}
		
		function fnGetApprovedRequisition($viewAll = 0)
		{
			$arrRequisition = array();
			
			if($viewAll == 1)
				$sSQL = "select r.*, r.id as requisitionid, e.name as name, rf.title as requisition_for_title, date_format(addedon,'%d-%m-%Y') as addeddate, date_format(till_date,'%d-%m-%Y') as till_date, date_format(approval_date,'%d-%m-%Y') as approval_date, date_format(requisition_completed_date,'%d-%m-%Y') as requisition_completed_date, if(is_requisition_completed=0,'No','Yes') as is_requisition_completed_text, if(isclosed=0,'No','Yes') as isclosed_text, e1.name as approved_by, e2.name as requisition_completedby_name from pms_requisition r LEFT JOIN pms_requisition_for rf ON r.requisition_for = rf.id LEFT JOIN pms_employee e ON e.id = r.user_id LEFT JOIN pms_employee e1 ON e1.id = r.reporting_head LEFT JOIN pms_employee e2 ON e2.id = r.requisition_completedby where (r.approval_status='1' or r.is_auto_approved='1' or r.delegated_reporting_head_status='1')";
			else
				$sSQL = "select r.*, r.id as requisitionid, e.name as name, rf.title as requisition_for_title, date_format(addedon,'%d-%m-%Y') as addeddate, date_format(till_date,'%d-%m-%Y') as till_date, date_format(approval_date,'%d-%m-%Y') as approval_date, date_format(requisition_completed_date,'%d-%m-%Y') as requisition_completed_date, if(is_requisition_completed=0,'No','Yes') as is_requisition_completed_text, if(isclosed=0,'No','Yes') as isclosed_text, e1.name as approved_by, e2.name as requisition_completedby_name  from pms_requisition r LEFT JOIN pms_requisition_for rf ON r.requisition_for = rf.id LEFT JOIN pms_employee e ON e.id = r.user_id LEFT JOIN pms_employee e1 ON e1.id = r.reporting_head LEFT JOIN pms_employee e2 ON e2.id = r.requisition_completedby where (r.approval_status='1' or r.is_auto_approved='1' or r.delegated_reporting_head_status='1') and isclosed=0";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$arrType = array("1"=>"Permanent", "2"=>"Temporary");
				$arrStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");
				
				while($this->next_record())
				{
					$tempRequisition = $this->fetchRow();

					$tempRequisition["request_type_text"] = $arrType[$tempRequisition["request_type"]];
					$tempRequisition["approval_status_text"] = $arrStatus[$tempRequisition["approval_status"]];

					/* If auto approved mark as approved */
					if(isset($tempRequisition["is_auto_approved"]) && $tempRequisition["is_auto_approved"] == "1")
					{
						$tempRequisition["approval_status_text"] = "Approved";

						/* Make approved by as auto approved */
						$tempRequisition["approved_by"] = "Auto approved";

						$tempRequisition["approval_date"] = $tempRequisition["addeddate"];
					}

					$arrRequisition[] = $tempRequisition;
				}
			}

			return $arrRequisition;
		}

		function fnGetApprovedRequisitionById($RequisitionId)
		{
			$RequisitionInfo = array();
			
			$sSQL = "select r.*, r.id as requisitionid, e.name as name, rf.title as requisition_for_title, date_format(addedon,'%d-%m-%Y') as addeddate, date_format(till_date,'%d-%m-%Y') as till_date, if(isclosed=0,'No','Yes') as isclosed_text from pms_requisition r LEFT JOIN pms_requisition_for rf ON r.requisition_for = rf.id LEFT JOIN pms_employee e ON e.id = r.user_id where r.id='".mysql_real_escape_string($RequisitionId)."' and  (r.approval_status='1' or r.is_auto_approved='1' or r.delegated_reporting_head_status='1')";
			$this->query($sSQL);

			if($this->num_rows() > 0)
			{
				$arrType = array("1"=>"Permanent", "2"=>"Temporary");
				$arrStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");

				if($this->next_record())
				{
					$RequisitionInfo = $this->fetchRow();

					$RequisitionInfo["request_type_text"] = $arrType[$RequisitionInfo["request_type"]];
					$RequisitionInfo["approval_status_text"] = $arrStatus[$RequisitionInfo["approval_status"]];
					$RequisitionInfo["delegated_reporting_head_status_text"] = $arrStatus[$RequisitionInfo["delegated_reporting_head_status"]];

					/* If auto approved mark as approved */
					if(isset($RequisitionInfo["is_auto_approved"]) && $RequisitionInfo["is_auto_approved"] == "1")
						$RequisitionInfo["approval_status_text"] = "Approved";
				}
			}

			return $RequisitionInfo;
		}

		function fnCloseRequisition($RequisitionInfo)
		{
			$Requisition = $this->fnGetApprovedRequisitionById($RequisitionInfo["id"]);

			if($Requisition["isclosed"] == 0)
			{
				$arrRequisitionInfo["id"] = $RequisitionInfo["id"];
				$arrRequisitionInfo["isclosed"] = 1;
				$arrRequisitionInfo["it_support_remarks"] = $RequisitionInfo["it_support_remarks"];
				$arrRequisitionInfo["closed_datetime"] = Date('Y-m-d H:i:s');
				$arrRequisitionInfo["closedby"] = $_SESSION["id"];
				$arrRequisitionInfo["closedby_type"] = $_SESSION["usertype"];

				$this->updateArray("pms_requisition",$arrRequisitionInfo);

				include_once("class.employee.php");
				include_once("includes/class.requisition_inventory.php");

				$objRequisitionInventory = new requisition_inventory();
				$objEmployee = new employee();

				$EmployeeInfo = $objEmployee->fnGetEmployeeById($Requisition["user_id"]);
				$ReportingHeadInfo = $objEmployee->fnGetEmployeeById($Requisition["reporting_head"]);

				/* Send mail to user */
				$Subject = "Requisition Closed";

				if(isset($EmployeeInfo["email"]) && trim($EmployeeInfo["email"]) != "")
				{
					$MailTo = $EmployeeInfo["email"];
					
					$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
					$content .= "IT Support has closed your requisition request for ".$objRequisitionInventory->fnGetRequisitionInventoryTitleById($Requisition["requisition_for"]).", remarks by IT Support are as follows: <br/>Remarks: ".$arrRequisitionInfo["it_support_remarks"];
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);
				}
				
				/* send mail to reporting head */
				if(isset($ReportingHeadInfo["email"]) && trim($ReportingHeadInfo["email"]) != "")
				{
					$MailTo = $ReportingHeadInfo["email"];

					$content = "Dear ".$ReportingHeadInfo["name"].",<br><br>";
					$content .= "IT Support has closed the requisition request by ".$EmployeeInfo["name"]." for ".$objRequisitionInventory->fnGetRequisitionInventoryTitleById($Requisition["requisition_for"]).", remarks by IT Support are as follows: <br/>Remarks: ".$arrRequisitionInfo["it_support_remarks"];
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);
				}
				
				/* send mail to delegated reporting head */
				if(isset($Requisition["delegated_reporting_head_id"]) && $Requisition["delegated_reporting_head_id"]!=0)
				{
					$DelegatedReportingHeadInfo = $objEmployee->fnGetEmployeeById($Requisition["delegated_reporting_head_id"]);
					if(isset($DelegatedReportingHeadInfo["email"]) && trim($DelegatedReportingHeadInfo["email"]) != "")
					{
						$MailTo = $DelegatedReportingHeadInfo["email"];

						$content = "Dear ".$DelegatedReportingHeadInfo["name"].",<br><br>";
						$content .= "IT Support has closed the requisition request by ".$EmployeeInfo["name"]." for ".$objRequisitionInventory->fnGetRequisitionInventoryTitleById($Requisition["requisition_for"]).", remarks by IT Support are as follows: <br/>Remarks: ".$arrRequisitionInfo["it_support_remarks"];
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}
				}

				return true;
			}
			else
				return false;
		}
		
		function fnCountPendingRequisition()
		{
			$cntRequisition = 0;
			
			$sSQL = "select count(id) as requisition_cnt from pms_requisition where (approval_status='1' or is_auto_approved='1' or delegated_reporting_head_status='1') and isclosed=0";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$cntRequisition = $this->f("requisition_cnt");
				}
			}
			
			return $cntRequisition;
		}

		/* This was an un necessary change ask and then asked to be removed
		function fnSaveRequisitionLog($arrRequisitionLog)
		{
			$this->insertArray("pms_open_requisition_log",$arrRequisitionLog);
		}
		
		function fnGetOpenRequisition()
		{
			$arrOpenRequisition  = array();

			echo $sSQL = "select r.*, r.id as requisitionid, e.name as name, rf.title as requisition_for_title, date_format(addedon,'%d-%m-%Y') as addeddate, date_format(till_date,'%d-%m-%Y') as till_date, if(isclosed=0,'No','Yes') as isclosed_text, e1.name as approved_by from pms_requisition r LEFT JOIN pms_requisition_for rf ON r.requisition_for = rf.id LEFT JOIN pms_employee e ON e.id = r.user_id LEFT JOIN pms_employee e1 ON e1.id = r.reporting_head where (r.approval_status='1' or r.is_auto_approved='1') and requisition_for='2' and isclosed=1 and is_requisition_completed='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$arrType = array("1"=>"Permanent", "2"=>"Temporary");
				$arrStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");
				
				while($this->next_record())
				{
					$tempRequisition = $this->fetchRow();

					$tempRequisition["request_type_text"] = $arrType[$tempRequisition["request_type"]];
					$tempRequisition["approval_status_text"] = $arrStatus[$tempRequisition["approval_status"]];

					// If auto approved mark as approved
					if(isset($tempRequisition["is_auto_approved"]) && $tempRequisition["is_auto_approved"] == "1")
					{
						$tempRequisition["approval_status_text"] = "Approved";

						// Make approved by as auto approved
						$tempRequisition["approved_by"] = "Auto approved";

						$tempRequisition["approval_date"] = $tempRequisition["addeddate"];
					}

					$arrOpenRequisition[] = $tempRequisition;
				}
			}

			return $arrOpenRequisition;
		}*/
		
		function fnGetRequisitionExpiredByDate($expiry_date)
		{
			$arrExpiredRequisition = array();

			$sSQL = "select r.*, e.name as user_name, e.email, rf.title as requisition_for_title from pms_requisition r INNER JOIN pms_employee e ON r.user_id = e.id INNER JOIN pms_requisition_for rf ON rf.id = r.requisition_for where (r.approval_status='1' or (r.approval_status='0' && r.delegated_reporting_head_status='1') or r.is_auto_approved='1') and request_type='2' and isclosed=1 and (is_requisition_completed='0' or is_requisition_completed is null) and date_format(till_date,'%Y-%m-%d') = '".mysql_real_escape_string($expiry_date)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrExpiredRequisition[] = $this->fetchRow();
				}
			}
			
			return $arrExpiredRequisition;
		}
		
		function fnGetExpiredRequisition()
		{
			$arrOpenRequisition  = array();

			$curDate = Date('Y-m-d');

			$sSQL = "select r.*, r.id as requisitionid, e.name as name, rf.title as requisition_for_title, date_format(addedon,'%d-%m-%Y') as addeddate, date_format(till_date,'%d-%m-%Y') as till_date, if(isclosed=0,'No','Yes') as isclosed_text, e1.name as approved_by from pms_requisition r LEFT JOIN pms_requisition_for rf ON r.requisition_for = rf.id LEFT JOIN pms_employee e ON e.id = r.user_id LEFT JOIN pms_employee e1 ON e1.id = r.reporting_head where (r.approval_status='1' or (r.approval_status='0' && r.delegated_reporting_head_status='1') or r.is_auto_approved='1') and request_type='2' and isclosed=1 and (is_requisition_completed='0' or is_requisition_completed is null) and date_format(till_Date, '%Y-%m-%d') <= '".$curDate."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$arrType = array("1"=>"Permanent", "2"=>"Temporary");
				$arrStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");
				
				while($this->next_record())
				{
					$tempRequisition = $this->fetchRow();

					$tempRequisition["request_type_text"] = $arrType[$tempRequisition["request_type"]];
					$tempRequisition["approval_status_text"] = $arrStatus[$tempRequisition["approval_status"]];

					// If auto approved mark as approved
					if(isset($tempRequisition["is_auto_approved"]) && $tempRequisition["is_auto_approved"] == "1")
					{
						$tempRequisition["approval_status_text"] = "Approved";

						// Make approved by as auto approved
						$tempRequisition["approved_by"] = "Auto approved";

						$tempRequisition["approval_date"] = $tempRequisition["addeddate"];
					}

					$arrOpenRequisition[] = $tempRequisition;
				}
			}

			return $arrOpenRequisition;
		}
		
		function fnGetExpiredRequisitionByRequisitionId($id)
		{
			$arrExpiredRequisition  = array();

			$curDate = Date('Y-m-d');

			$sSQL = "select r.*, r.id as requisitionid, e.name as name, rf.title as requisition_for_title, date_format(addedon,'%d-%m-%Y') as addeddate, date_format(till_date,'%d-%m-%Y') as till_date, if(isclosed=0,'No','Yes') as isclosed_text, e1.name as approved_by from pms_requisition r LEFT JOIN pms_requisition_for rf ON r.requisition_for = rf.id LEFT JOIN pms_employee e ON e.id = r.user_id LEFT JOIN pms_employee e1 ON e1.id = r.reporting_head where (r.approval_status='1' or (r.approval_status='0' && r.delegated_reporting_head_status='1') or r.is_auto_approved='1') and request_type='2' and isclosed=1 and (is_requisition_completed='0' or is_requisition_completed is null) and r.id = '".mysql_real_escape_string($id)."' and date_format(till_Date, '%Y-%m-%d') <= '".$curDate."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$arrType = array("1"=>"Permanent", "2"=>"Temporary");
				$arrStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");
				
				if($this->next_record())
				{
					$arrExpiredRequisition = $this->fetchRow();

					$arrExpiredRequisition["request_type_text"] = $arrType[$arrExpiredRequisition["request_type"]];
					$arrExpiredRequisition["approval_status_text"] = $arrStatus[$arrExpiredRequisition["approval_status"]];

					// If auto approved mark as approved
					if(isset($arrExpiredRequisition["is_auto_approved"]) && $arrExpiredRequisition["is_auto_approved"] == "1")
					{
						$arrExpiredRequisition["approval_status_text"] = "Approved";

						// Make approved by as auto approved
						$arrExpiredRequisition["approved_by"] = "Auto approved";

						$arrExpiredRequisition["approval_date"] = $arrExpiredRequisition["addeddate"];
					}
				}
			}

			return $arrExpiredRequisition;
		}
		
		function fnCloseExpiredRequisition($RequisitionInfo)
		{
			$arrRequisition = $this->fnGetExpiredRequisitionByRequisitionId($RequisitionInfo["id"]);
			
			if(count($arrRequisition) > 0)
			{
				//if(isset($arrRequisition["is_requisition_completed"]) && (trim($arrRequisition["is_requisition_completed"]) == '0' || trim($arrRequisition["is_requisition_completed"]) == ''))
				if((trim($arrRequisition["is_requisition_completed"]) == '0' || trim($arrRequisition["is_requisition_completed"]) == ''))
				{
					$arrRequisitionInfo["id"] = $RequisitionInfo["id"];
					$arrRequisitionInfo["is_requisition_completed"] = 1;
					$arrRequisitionInfo["requisition_completed_date"] = Date('Y-m-d H:i:s');
					$arrRequisitionInfo["requisition_completedby"] = $_SESSION["id"];

					$this->updateArray("pms_requisition",$arrRequisitionInfo);

					include_once("class.employee.php");
					include_once("includes/class.requisition_inventory.php");

					$objRequisitionInventory = new requisition_inventory();
					$objEmployee = new employee();

					$EmployeeInfo = $objEmployee->fnGetEmployeeById($arrRequisition["user_id"]);

					/* Send mail to user */
					$Subject = "Expired Requisition Closed";

					if(isset($EmployeeInfo["email"]) && trim($EmployeeInfo["email"]) != "")
					{
						$MailTo = $EmployeeInfo["email"];
						
						$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
						$content .= "IT Support has closed your expired requisition request for ".$objRequisitionInventory->fnGetRequisitionInventoryTitleById($arrRequisition["requisition_for"]);
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}
					
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		
		function fnCountExpiredRequisition()
		{
			$cntExpiredRequisition  = array();

			$curDate = Date('Y-m-d');

			$sSQL = "select count(id) cnt_requisition from pms_requisition r where (approval_status='1' or (approval_status='0' && delegated_reporting_head_status='1') or is_auto_approved='1') and request_type='2' and isclosed=1 and (is_requisition_completed='0' or is_requisition_completed is null) and date_format(till_Date, '%Y-%m-%d') <= '".$curDate."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$cntExpiredRequisition  = $this->f("cnt_requisition");
				}
			}

			return $cntExpiredRequisition;
		}
		
		function fnGetTotalPenaltyRequisitionRequestByUser($UserId)
		{
			include_once("class.employee.php");
			$objEmployee = new employee();

			$current_date = date('Y-m-d');
			$requisition_request_count = 0;

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

			$sSQL = "select count(r.id) as requisition_request_count from pms_requisition r LEFT JOIN pms_requisition_for rf ON r.requisition_for = rf.id LEFT JOIN pms_employee e ON e.id = r.user_id where (r.reporting_head='".mysql_real_escape_string($UserId)."' or r.delegated_reporting_head_id='".mysql_real_escape_string($UserId)."' or (r.user_id in ($ids) and '".mysql_real_escape_string($UserId)."' = (select delegate from pms_leave_form where employee_id = r.reporting_head and '$current_date' between DATE_FORMAT(`start_date`,'%Y-%m-%d') and DATE_FORMAT(`end_date`,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1')) ORDER BY `id` LIMIT 0,1))) and approval_status='0' and delegated_reporting_head_status='0' and is_auto_approved='0'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				if($this->next_record())
				{
					$requisition_request_count = $this->f("requisition_request_count");
				}
			}
			
			return $requisition_request_count;
		}
	}
?>

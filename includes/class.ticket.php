<?php

	include_once('db_mysql.php');

	class ticket extends DB_Sql
	{
		function __construct()
		{
		}
		
		/*
		 * Save ticket
		 * */
		function fnSaveTicket($arrTicket)
		{
			include_once("class.issue.php");
			include_once("class.issue_category.php");
			include_once("class.employee.php");
			include_once("class.inventory_location.php");
			include_once("class.stock_register.php");
			include_once("class.it_support_time.php");
			include_once("class.it_support_designations.php");
			
			$objIssue = new issue();
			$objIssueCategory = new issue_category();
			$objEmployee = new employee();
			$objInventoryLocation = new inventory_location();
			$objStockRegister = new stock_register();
			$objItSupportTime = new it_support_time();
			$objItSupportDesignation = new it_support_designations();

			$arrIssue = $objIssue->fnGetIssueById($arrTicket["issue_id"]);

			if(count($arrIssue) > 0)
			{
				$curdate = Date('Y-m-d H:i:s');
				$curdt = Date('Y-m-d H');
				$curd = Date('Y-m-d');
				$curdtime = Date('Y-m-d H:i');
				$curTime = Date('H:i');
				
				$arrticketInfo["user_id"] = $_SESSION["id"];
				$arrticketInfo["issue_category_id"] = $arrTicket["issue_category_id"];
				$arrticketInfo["issue_id"] = $arrTicket["issue_id"];
				$arrticketInfo["description"] = $arrTicket["description"];
				$arrticketInfo["ticket_raised_date"] = $curdate;
				$arrticketInfo["priority"] = $arrIssue["priority"];
				$arrticketInfo["estimated_resolution_time"] = $arrIssue["estimated_resolution_time"];
				$arrticketInfo["location_id"] = $arrTicket["location_id"];
				
				$first_reporting_head_id = $objEmployee->fnGetReportingHeadId($_SESSION["id"]);
				$second_reporting_head_id = $objEmployee->fnGetReportingHeadId($first_reporting_head_id);
				
				$arrSupportDesignations = $objItSupportDesignation->fnGetSupportDesignations();
				$arrSupportDesignations[] = 0;
				$arrticketInfo["support_designations"] = implode(",", $arrSupportDesignations);
				
				$arrticketInfo["first_reporting_head_id"] = $first_reporting_head_id;
				$arrticketInfo["second_reporting_head_id"] = $second_reporting_head_id;
				$arrticketInfo["resolution_status"] = 0;
				$arrticketInfo["isdeleted"] = 0;
				$arrticketInfo["addedon"] = $curdate;

				/*if(count($arrSupportTime) > 0)
				{
					if(isset($arrSupportTime["support_start_time"]) && trim($arrSupportTime["support_start_time"]) != "")
						$arrticketInfo["support_start_time"] = trim($arrSupportTime["support_start_time"]);

					if(isset($arrSupportTime["support_end_time"]) && trim($arrSupportTime["support_end_time"]) != "")
						$arrticketInfo["support_end_time"] = trim($arrSupportTime["support_end_time"]);

					if(isset($arrSupportTime["limited_support_start_time"]) && trim($arrSupportTime["limited_support_start_time"]) != "")
						$arrticketInfo["limited_support_start_time"] = trim($arrSupportTime["limited_support_start_time"]);

					if(isset($arrSupportTime["limited_support_end_time"]) && trim($arrSupportTime["limited_support_end_time"]) != "")
						$arrticketInfo["limited_support_end_time"] = trim($arrSupportTime["limited_support_end_time"]);

					if(isset($arrSupportTime["support_designations"]) && trim($arrSupportTime["support_designations"]) != "")
						$arrticketInfo["support_designations"] = trim($arrSupportTime["support_designations"]);
				}*/

				$arrResolutionInformation["resolution_status"] = 0;
				$arrResolutionInformation["resolution_datetime"] = $curdate;
				$arrResolutionInformation["isdeleted"] = 0;
				$arrResolutionInformation["addedon"] = $curdate;
				$arrResolutionInformation["isclosed"] = 0;

				/* Check if the ticket already exists */
				$sSQL = "select * from pms_ticket where user_id='".mysql_real_escape_string($arrticketInfo["user_id"])."' and issue_category_id='".mysql_real_escape_string($arrticketInfo["issue_category_id"])."' and issue_id='".mysql_real_escape_string($arrticketInfo["issue_id"])."' and description='".mysql_real_escape_string($arrticketInfo["description"])."' and date_format(ticket_raised_date,'%Y-%m-%d %H')='".mysql_real_escape_string($curdt)."' and location_id='".mysql_real_escape_string($arrticketInfo["location_id"])."' and first_reporting_head_id='".mysql_real_escape_string($arrticketInfo["first_reporting_head_id"])."' and second_reporting_head_id='".mysql_real_escape_string($arrticketInfo["second_reporting_head_id"])."' and isdeleted='0'";
				$this->query($sSQL);
				if($this->num_rows() == 0)
				{
					$arrAttachment = array();
					
					$arrticketInfo["has_attachment"] = 0;
					if(isset($arrTicket["ticket_reference_image"]["name"]) && trim($arrTicket["ticket_reference_image"]["name"])!="")
					{
						$arrAttachment[] = array("file_string"=>file_get_contents($arrTicket["ticket_reference_image"]["tmp_name"]), "file_name"=>$arrTicket["ticket_reference_image"]["name"], "file_encoding"=>'base64', "file_mime_type"=>$arrTicket["ticket_reference_image"]["type"]);
						$arrticketInfo["has_attachment"] = 1;
					}
					
					$TicketId = $this->insertArray("pms_ticket",$arrticketInfo);
					
					$arrResolutionInformation["ticket_id"] = $TicketId;

					$this->insertArray("pms_ticket_resolution_status",$arrResolutionInformation);
					
					/* Send mail to IT Support */
					$employeeInfo = $objEmployee->fnGetEmployeeDetailById($arrticketInfo["user_id"]);
					$InventoryLocation = $objInventoryLocation->fnGetLocationNameById($arrticketInfo["location_id"]);
					
					$arrTime = explode(":",$arrticketInfo["estimated_resolution_time"]);
					$strTime = "";
					if($arrTime[0] != "00" && $arrTime[1] != "00")
					{
						$strTime = $arrTime[0] . " Hour(s) and ".$arrTime[1]." Minute(s)";
					}
					else if($arrTime[0] == "00")
					{
						$strTime = $arrTime[1]." Minute(s)";
					}
					else if($arrTime[1] == "00")
					{
						$strTime = $arrTime[0] . " Hour(s)";
					}
					
					$Subject = "New IT Ticket raised";
					$mailContent = "A ticket has been raised by <b>".$employeeInfo["name"]."</b>. Call will be attended within <b>".$strTime."</b> .The details of the ticket are as follows:<br/><br/>";
					
					$tableContent = "<table cellspacing='2' cellpadding='3' bgcolor='#e6e6e6'>
						<tr bgcolor='#FFFFFF'>
							<td><b>Ticket Id.: </b></td>
							<td>".$TicketId."</td>
						</tr>
						<tr bgcolor='#FFFFFF'>
							<td><b>Issue Category: </b></td>
							<td>".$objIssueCategory->fnGetIssueCategoryNameById($arrticketInfo["issue_category_id"])."</td>
						</tr>
						<tr bgcolor='#FFFFFF'>
							<td><b>Issue: </b></td>
							<td>".$objIssue->fnGetIssueNameById($arrticketInfo["issue_id"])."</td>
						</tr>
						<tr bgcolor='#FFFFFF'>
							<td><b>Location: </b></td>
							<td>".$InventoryLocation."</td>
						</tr>
						<tr bgcolor='#FFFFFF'>
							<td><b>Issue Description: </b></td>
							<td>".$arrticketInfo["description"]."</td>
						</tr>
					</table>";
					
					
					/* 
					 * Check leave 
					 * Create a function and send it to leave for clear code
					 * */
					$db = new DB_Sql();
					
					/*$arrItSupportTime = $objItSupportTime->fnGetSupportTime();
					
					$start_time = $arrItSupportTime["support_start_time"];
					$end_time = $arrItSupportTime["support_end_time"];
					
					$sSQL = "select l.* from pms_leave_form l INNER JOIN pms_employee e ON e.id = l.employee_id where '".mysql_real_escape_string($curd)."' between date_format(l.start_date,'%Y-%m-%d') and date_format(l.end_date,'%Y-%m-%d') and (l.status_manager='1' or (l.status_manager='0' and l.deligateManagerId !=0 and l.manager_delegate_status='1')) and e.designation in (".$arrItSupportTime["support_designations"].")";
					$db->query($sSQL);
					if($db->num_rows() > 0)
					{
						$start_time = $arrItSupportTime["limited_support_start_time"];
						$end_time = $arrItSupportTime["limited_support_end_time"];
					}*/
					
					$mailFooter = "";
					
					$sSQL = "select sr.*, time_format(st.starttime,'%H:%i') as starttime, time_format(st.endtime,'%H:%i') as endtime from pms_employee e INNER JOIN pms_support_roster sr ON e.id = sr.user_id INNER JOIN pms_shift_times st ON st.id = sr.shift_id where e.designation in (".mysql_real_escape_string($arrticketInfo["support_designations"]).") and e.id not in (select distinct e.id from pms_leave_form l INNER JOIN pms_employee e ON e.id = l.employee_id where '".mysql_real_escape_string($curd)."' between date_format(l.start_date,'%Y-%m-%d') and date_format(l.end_date,'%Y-%m-%d') and (l.status_manager='1' or (l.status_manager='0' and l.deligateManagerId !=0 and l.manager_delegate_status='1')) and e.designation in (".mysql_real_escape_string($arrticketInfo["support_designations"]).")) and date_format(shift_date,'%Y-%m-%d') = '".mysql_real_escape_string($curd)."' and IF(time_format(st.starttime,'%H:%i') > time_format(st.endtime,'%H:%i') , (('".mysql_real_escape_string($curTime)."' between time_format(st.starttime,'%H:%i') and '24:00' ) or ('".mysql_real_escape_string($curTime)."' between '00:00' and time_format(st.endtime,'%H:%i') )), ('".mysql_real_escape_string($curTime)."' between time_format(st.starttime,'%H:%i') and time_format(st.endtime,'%H:%i')))";
					$db->query($sSQL);
					if($db->num_rows() == 0)
					{
						$mailFooter = "<br/>IT Support is currently not available, your call will be attended on next availibility.<br/>";
					}
					
					$mailFooter .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
					
					/* Send mail to reporting head */
					$arrReportingHeadInfo = $objEmployee->fnGetEmployeeDetailById($first_reporting_head_id);
					if(count($arrReportingHeadInfo) > 0)
					{
						$content = "Dear ".$arrReportingHeadInfo["name"].",<br/><br/>".$mailContent.$tableContent.$mailFooter;
						sendmail($arrReportingHeadInfo["email"], $Subject, $content, $arrAttachment);
					}

					/* Send mail to the user */
					$content = "Dear ".$employeeInfo["name"].",<br/><br/>You have raised a new IT Ticket. Call will be attended within <b>".$strTime."</b>. The details of the ticket are as follows : <br/><br/>".$tableContent.$mailFooter;
					sendmail($employeeInfo["email"], $Subject, $content, $arrAttachment);

					/* Fetch location wise inventory */
					$arrInventoryList = $objStockRegister->fnGetLocationWiseInventoryList($arrticketInfo["location_id"]);
					$LocationInformation = "";
					if(count($arrInventoryList) > 0)
					{
						$LocationInformation .= "<br/>Assets on the location ".$InventoryLocation." are as follows: <br/><br/>";
						$LocationInformation .= "<table cellspacing='2' cellpadding='3' bgcolor='#e6e6e6'>
							<tr bgcolor='#FFFFFF'>
								<th>Inventory Type</th>
								<th>Inventory Make</th>
								<th>Serial No.</th>
								<th>Unique Id.</th>
								<th>Purchase Date</th>
								<th>Warranty</th>
								<th>Warranty expiry</th>
								<th>In warranty</th>
								<!--th>Status</th-->
							</tr>";
						
						foreach($arrInventoryList as $curInventory)
						{
							$LocationInformation .= "<tr bgcolor='#FFFFFF'>
								<td>".$curInventory["type"]."</td>
								<td>".$curInventory["make"]."</td>
								<td>".$curInventory["serialno"]."</td>
								<td>".$curInventory["uniqueid"]."</td>
								<td>".$curInventory["purchasedate"]."</td>
								<td>".$curInventory["warranty_text"]."</td>
								<td>".$curInventory["warrenty_expiry"]."</td>
								<td>".$curInventory["warranty_status_text"]."</td>
								<!--td>".$curInventory["status_text"]."</td-->
							</tr>";
						}
						
						$LocationInformation .= "</table>";
					}
					else
					{
						$LocationInformation .= "<br/>No assets found on the location ".$InventoryLocation;
					}
					
					/* Mail to IT Support */
					$mailContent = "A ticket has been raised by <b>".$employeeInfo["name"]."</b>. Call has to be attented in <b>".$strTime."</b> .The details of the ticket are as follows:<br/><br/>";
					$mailFooter = "<br><br>Regards,<br>".SITEADMINISTRATOR;
					$content = "Dear IT Support team,<br/><br/>".$mailContent.$tableContent.$LocationInformation.$mailFooter;
					sendmail("itsupport@transformsolution.net", $Subject, $content, $arrAttachment);
					
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		
		/* Get all the ticktets */
		function fnGetAllTickets($issue_category_id = 0, $issue_id = 0, $priority = 0, $ticket_raised_date_from='', $ticket_raised_date_to = '')
		{
			$arrTickets = array();

			$cond = '';

			if(trim($issue_category_id) != "" && trim($issue_category_id) != "0")
			{
				$cond .= " and t.issue_category_id = '".mysql_real_escape_string($issue_category_id)."'";
			}

			if(trim($issue_id) != "" && trim($issue_id) != "0")
			{
				$cond .= " and t.issue_id = '".mysql_real_escape_string($issue_id)."'";
			}
			
			if(trim($priority) != "" && trim($priority) != "0")
			{
				$cond .= " and t.priority = '".mysql_real_escape_string($priority)."'";
			}
			
			if(trim($ticket_raised_date_from) != "" && trim($ticket_raised_date_to) != "")
			{
				$cond .= " and date_format(t.ticket_raised_date,'%Y-%m-%d') between '".mysql_real_escape_string($ticket_raised_date_from)."' and '".mysql_real_escape_string($ticket_raised_date_to)."'";
			}

			$sSQL = "select t.*, tr.resolution_status, ic.issue_category, i.issue, l.location_name, e.name as user_name, e1.name as reporting_head_name, date_format(t.ticket_raised_date,'%d-%m-%Y') as ticket_raise_dt, date_format(t.ticket_raised_date,'%H:%i') as ticket_raise_time from pms_ticket t LEFT JOIN pms_ticket_resolution_status tr ON (t.id = tr.ticket_id and tr.isdeleted='0') LEFT JOIN pms_issue i ON i.id = t.issue_id LEFT JOIN pms_issue_category ic ON ic.id = t.issue_category_id LEFT JOIN pms_inventory_location l ON l.id = t.location_id LEFT JOIN pms_employee e ON e.id = t.user_id LEFT JOIN pms_employee e1 ON e1.id = t.first_reporting_head_id where 1=1".$cond;
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$status = array("0"=>"In queue", "1"=>"Being attended", "2"=>"Resolved", "3"=>"Work in progress", "4"=>"Under observation");

				while($this->next_record())
				{
					$tempTicket = $this->fetchrow(); 
					
					if($tempTicket["resolution_status"] == "")
						$tempTicket["resolution_status_text"] = "In queue";
					else
						$tempTicket["resolution_status_text"] = $status[$tempTicket["resolution_status"]];
					
					$arrTickets[] = $tempTicket;
				}
			}

			return $arrTickets;
		}

		function fnGetTicketById($TicketId)
		{
			$arrTicketInfo = array();
			
			$sSQL = "select * from pms_ticket t LEFT JOIN pms_ticket_resolution_status tr ON (t.id = tr.ticket_id and tr.isdeleted='0') where t.id='".mysql_real_escape_string($TicketId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrTicketInfo = $this->fetchrow();
				}
			}
			
			return $arrTicketInfo;
		}
		
		function fnGetTicketLocationById($TicketId)
		{
			$LocationId = 0;
			
			$sSQL = "select location_id from pms_ticket where id='".mysql_real_escape_string($TicketId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$LocationId = $this->f("location_id");
				}
			}
			
			return $LocationId;
		}
		
		/* Get tickets by user id */
		function fnGetTicketsByUserId($UserId)
		{
			$arrTickets = array();
			$sSQL = "select t.*, tr.resolution_status, ic.issue_category, i.issue, l.location_name from pms_ticket t LEFT JOIN pms_ticket_resolution_status tr ON (t.id = tr.ticket_id and tr.isdeleted='0') LEFT JOIN pms_issue i ON i.id = t.issue_id LEFT JOIN pms_issue_category ic ON ic.id = t.issue_category_id LEFT JOIN pms_inventory_location l ON l.id = t.location_id where t.user_id='".mysql_real_escape_string($UserId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				/*$status = array("0"=>"Not attended", "1"=>"Attending", "2"=>"Additional Requirements", "3"=>"Pending", "4"=>"Under observation", "5"=>"Closed");*/
			
				$status = array("0"=>"In queue", "1"=>"Being attended", "2"=>"Resolved", "3"=>"Work in progress", "4"=>"Under observation");
				
				while($this->next_record())
				{
					$tempTicket = $this->fetchrow(); 

					if($tempTicket["resolution_status"] != "")
						$tempTicket["resolution_status_text"] = $status[$tempTicket["resolution_status"]];
					else
						$tempTicket["resolution_status_text"] = "In queue";
					
					$arrTickets[] = $tempTicket;
				}
			}

			return $arrTickets;
		}
		
		/* Get tickets by user id */
		function fnGetTicketsByUserAndTicketId($UserId, $TicketId)
		{
			$arrTicket = array();
			$sSQL = "select t.*, tr.resolution_status, tr.requirement_for, time_format(tr.additional_time,'%H:%i') as additional_time, tr.additional_asset, tr.resolution_description, ic.issue_category, i.issue, l.location_name, it.type as additional_asset_name from pms_ticket t LEFT JOIN pms_ticket_resolution_status tr ON (t.id = tr.ticket_id and tr.isdeleted='0') LEFT JOIN pms_issue i ON i.id = t.issue_id LEFT JOIN pms_issue_category ic ON ic.id = t.issue_category_id LEFT JOIN pms_inventory_location l ON l.id = t.location_id LEFT JOIN pms_inventory_type it ON it.id = tr.additional_asset where t.user_id='".mysql_real_escape_string($UserId)."' and t.id='".mysql_real_escape_string($TicketId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				/*$status = array("0"=>"Not attended", "1"=>"Attending", "2"=>"Additional Requirements", "3"=>"Pending", "4"=>"Under observation", "5"=>"Closed");*/
			
				$status = array("0"=>"In queue", "1"=>"Being attended", "2"=>"Resolved", "3"=>"Work in progress", "4"=>"Under observation");
			
				$arrRequirementFor = array("1"=>"Time", "2"=>"Asset");
				if($this->next_record())
				{
					$arrTicket = $this->fetchrow(); 
					
					/*if($arrTicket["requirement_for"] == "")
						$arrTicket["requirement_for_text"] = "In queue";
					else
						$arrTicket["requirement_for_text"] = $arrRequirementFor[$arrTicket["requirement_for"]];*/
					
					if($arrTicket["resolution_status"] == "")
						$arrTicket["resolution_status_text"] = "In queue";
					else
						$arrTicket["resolution_status_text"] = $status[$arrTicket["resolution_status"]];
				}
			}

			return $arrTicket;
		}
		
		function fnGetPendingTickets()
		{
			$arrPendingTickets = array();
			
			/* Display tickets that are not closed or closed by admin */
			//$sSQL = "select t.*, ic.issue_category, i.issue, l.location_name, time_format(t.estimated_resolution_time,'%H:%i') as estimated_resolution_time, date_format(t.ticket_raised_date, '%Y-%m-%d %H:%i') as ticket_raised_date from pms_ticket t LEFT JOIN pms_issue i ON i.id = t.issue_id LEFT JOIN pms_issue_category ic ON ic.id = t.issue_category_id LEFT JOIN pms_inventory_location l ON l.id = t.location_id where resolution_status not in (5,6) order by priority, estimated_resolution_time, addedon";
			/*$sSQL = "select t.*, tr.resolution_status, ic.issue_category, i.issue, l.location_name, time_format(t.estimated_resolution_time,'%H:%i') as estimated_resolution_time, date_format(t.ticket_raised_date, '%Y-%m-%d %H:%i') as ticket_raised_date, e.name as user_name, e1.name as reporting_head_name, ADDTIME( t.ticket_raised_date, t.estimated_resolution_time ) AS ticket_resolution_time from pms_ticket t LEFT JOIN pms_ticket_resolution_status tr ON (t.id = tr.ticket_id and tr.isdeleted='0') LEFT JOIN pms_issue i ON i.id = t.issue_id LEFT JOIN pms_issue_category ic ON ic.id = t.issue_category_id LEFT JOIN pms_inventory_location l ON l.id = t.location_id LEFT JOIN pms_employee e ON e.id = t.user_id LEFT JOIN pms_employee e1 ON e1.id = t.first_reporting_head_id where resolution_status != 2 and isclosed='0' order by ticket_resolution_time, priority, estimated_resolution_time, addedon";*/
			
			$curdt = Date('Y-m-d H:m:s');
			$sSQL = "(select t.*, tr.resolution_status, ic.issue_category, i.issue, l.location_name, time_format(t.estimated_resolution_time,'%H:%i') as estimated_resolution_time, date_format(t.ticket_raised_date, '%Y-%m-%d %H:%i') as ticket_raised_date, e.name as user_name, e1.name as reporting_head_name, ADDTIME( t.ticket_raised_date, t.estimated_resolution_time ) AS ticket_resolution_time from pms_ticket t LEFT JOIN pms_ticket_resolution_status tr ON (t.id = tr.ticket_id and tr.isdeleted='0') LEFT JOIN pms_issue i ON i.id = t.issue_id LEFT JOIN pms_issue_category ic ON ic.id = t.issue_category_id LEFT JOIN pms_inventory_location l ON l.id = t.location_id LEFT JOIN pms_employee e ON e.id = t.user_id LEFT JOIN pms_employee e1 ON e1.id = t.first_reporting_head_id where resolution_status != 2 and isclosed='0' and ADDTIME( t.ticket_raised_date, t.estimated_resolution_time ) < '".$curdt."' order by ADDTIME( t.ticket_raised_date, t.estimated_resolution_time ) limit 10000)
			UNION 
			(select t.*, tr.resolution_status, ic.issue_category, i.issue, l.location_name, time_format(t.estimated_resolution_time,'%H:%i') as estimated_resolution_time, date_format(t.ticket_raised_date, '%Y-%m-%d %H:%i') as ticket_raised_date, e.name as user_name, e1.name as reporting_head_name, ADDTIME( t.ticket_raised_date, t.estimated_resolution_time ) AS ticket_resolution_time from pms_ticket t LEFT JOIN pms_ticket_resolution_status tr ON (t.id = tr.ticket_id and tr.isdeleted='0') LEFT JOIN pms_issue i ON i.id = t.issue_id LEFT JOIN pms_issue_category ic ON ic.id = t.issue_category_id LEFT JOIN pms_inventory_location l ON l.id = t.location_id LEFT JOIN pms_employee e ON e.id = t.user_id LEFT JOIN pms_employee e1 ON e1.id = t.first_reporting_head_id where resolution_status != 2 and isclosed='0' and ADDTIME( t.ticket_raised_date, t.estimated_resolution_time ) >= '".$curdt."' order by priority, estimated_resolution_time, addedon limit 10000)";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				/*$status = array("0"=>"Not attended", "1"=>"Attending", "2"=>"Additional Requirements", "3"=>"Pending", "4"=>"Under observation", "5"=>"Closed");*/
			
				$status = array("0"=>"In queue", "1"=>"Being attended", "2"=>"Resolved", "3"=>"Work in progress", "4"=>"Under observation");
			
				while($this->next_record())
				{
					$tempTicket = $this->fetchrow(); 
					
					if($tempTicket["resolution_status"] == "")
						$tempTicket["resolution_status_text"] = "In queue";
					else
						$tempTicket["resolution_status_text"] = $status[$tempTicket["resolution_status"]];
					
					$arrPendingTickets[] = $tempTicket;
				}
			}
			
			return $arrPendingTickets;
		}
		
		function fnGetPendingTicketById($TicketId)
		{
			$PendingTicketInformation = array();
			
			/* Display tickets that are not closed or closed by admin */
			$sSQL = "select t.*, tr.resolution_status, tr.requirement_for, time_format(tr.additional_time,'%H:%i') as additional_time, tr.additional_asset, tr.resolution_description, ic.issue_category, i.issue, l.location_name, time_format(t.estimated_resolution_time,'%H:%i') as estimated_resolution_time, date_format(t.ticket_raised_date, '%Y-%m-%d %H:%i') as ticket_raised_date, e.name as user_name, e1.name as reporting_head_name from pms_ticket t LEFT JOIN pms_ticket_resolution_status tr ON (t.id = tr.ticket_id and tr.isdeleted='0') LEFT JOIN pms_issue i ON i.id = t.issue_id LEFT JOIN pms_issue_category ic ON ic.id = t.issue_category_id LEFT JOIN pms_inventory_location l ON l.id = t.location_id LEFT JOIN pms_employee e ON e.id = t.user_id LEFT JOIN pms_employee e1 ON e1.id = t.first_reporting_head_id where resolution_status != 2 and isclosed='0' and t.id='".mysql_real_escape_string($TicketId)."' order by priority, estimated_resolution_time, addedon";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				/*$status = array("0"=>"Not attended", "1"=>"Attending", "2"=>"Additional Requirements", "3"=>"Pending", "4"=>"Under observation", "5"=>"Closed");*/
			
				$status = array("0"=>"In queue", "1"=>"Being attended", "2"=>"Resolved", "3"=>"Work in progress", "4"=>"Under observation");
			
				while($this->next_record())
				{
					$PendingTicketInformation = $this->fetchrow();

					if($PendingTicketInformation["resolution_status"] == "")
						$PendingTicketInformation["resolution_status_text"] = "In queue";
					else
						$PendingTicketInformation["resolution_status_text"] = $status[$PendingTicketInformation["resolution_status"]];
				}
			}

			return $PendingTicketInformation;
		}

		function fnUpdateResolutionStatus($TicketInfo)
		{
			$TicketInfo["resolved_by"] = $_SESSION["id"];
			$TicketInfo["isclosed"] = 0;
			$TicketInfo["isdeleted"] = 0;
			$TicketInfo["ticket_id"] = $TicketInfo["ticket_id"];
			$TicketInfo["resolution_status"] = $TicketInfo["status"];
			$TicketInfo["resolution_description"] = $TicketInfo["remarks"];
			$TicketInfo["resolution_datetime"] = Date('Y-m-d H:i:s');
			$TicketInfo["addedon"] = Date('Y-m-d H:i:s');

			$sSQL = "select * from pms_ticket_resolution_status where resolved_by='".mysql_real_escape_string($TicketInfo["resolved_by"])."' and resolution_status='".mysql_real_escape_string($TicketInfo["resolution_status"])."' and resolution_description='".mysql_real_escape_string($TicketInfo["resolution_description"])."' and ticket_id='".mysql_real_escape_string($TicketInfo["ticket_id"])."' and isdeleted='0'";
			$this->query($sSQL);
			if($this->num_rows() == 0)
			{
				/* update all the resolution status of previous ticket */
				$sSQL = "update pms_ticket_resolution_status set isdeleted='1', deleted_datetime='".Date('Y-m-d H:i:s')."' where ticket_id='".mysql_real_escape_string($TicketInfo["ticket_id"])."' and isdeleted='0'";
				$this->query($sSQL);
				
				/* Insert the updated status */
				$this->insertArray("pms_ticket_resolution_status",$TicketInfo);
				
				/* if inventory replaced, update the inventory status */
				if(trim($TicketInfo["resolution_status"]) == '2' && trim($TicketInfo["replace_item_id"]) != "" && trim($TicketInfo["replace_with"]) != "")
				{
					/* Mark previous inventory as scrap */
					$sSQL = "update pms_stock_register set location_id='', status='2' where id='".mysql_real_escape_string($TicketInfo["replace_item_id"])."'";
					$this->query($sSQL);
					
					include_once("class.stock_register.php");
					$objStockRegister = new stock_register();
					
					/* Insert in log */
					$objStockRegister->fnStockRegisterLog($TicketInfo["replace_item_id"]);
					
					/* Fetch ticket by Id */
					
					$LocationId = $this->fnGetTicketLocationById($TicketInfo["ticket_id"]);
					
					/* Mark the current inventory as used */
					$sSQL = "update pms_stock_register set location_id='".mysql_real_escape_string($LocationId)."', status='1' where id='".mysql_real_escape_string($TicketInfo["replace_with"])."'";
					$this->query($sSQL);
					
					/* Insert in log */
					$objStockRegister->fnStockRegisterLog($TicketInfo["replace_with"]);
				}
				
				/* Send mail to employee and team leader if the status of the ticket changed */
				$Subject = "IT Ticket updated";

				include_once("class.employee.php");
				include_once("class.issue_category.php");
				include_once("class.issue.php");
				
				$objEmployee = new employee();
				$objIssueCategory = new issue_category();
				$objIssue = new issue();

				$status = array("0"=>"In queue", "1"=>"Being attended", "2"=>"Resolved", "3"=>"Work in progress", "4"=>"Under observation");
				
				$arrTicket = $this->fnGetTicketById($TicketInfo["ticket_id"]);

				if($arrTicket["resolution_status"] != "1")
				{
					$employeeInfo = $objEmployee->fnGetEmployeeDetailById($arrTicket["user_id"]);
					if(count($employeeInfo) > 0)
					{
						$mailHeader = "Dear ".$employeeInfo["name"].",<br/><br/>Status for the ticket raised by you has been updated. The details for ticket are as follows:<br/><br/>";

						$mailContent = "<table cellspacing='2' cellpadding='3' bgcolor='#e6e6e6'>
							<tr bgcolor='#FFFFFF'>
								<td><b>Ticket Id: </b></td>
								<td>".$TicketInfo["ticket_id"]."</td>
							</tr>
							<tr bgcolor='#FFFFFF'>
								<td><b>Issue Category: </b></td>
								<td>".$objIssueCategory->fnGetIssueCategoryNameById($arrTicket["issue_category_id"])."</td>
							</tr>
							<tr bgcolor='#FFFFFF'>
								<td><b>Issue: </b></td>
								<td>".$objIssue->fnGetIssueNameById($arrTicket["issue_id"])."</td>
							</tr>
							<tr bgcolor='#FFFFFF'>
								<td><b>Issue Description: </b></td>
								<td>".$arrTicket["description"]."</td>
							</tr>
							<tr bgcolor='#FFFFFF'>
								<td><b>Status: </b></td>
								<td>".$status[$arrTicket["resolution_status"]]."</td>
							</tr>
							<tr bgcolor='#FFFFFF'>
								<td><b>IT Support Comments: </b></td>
								<td>".$arrTicket["resolution_description"]."</td>
							</tr>
						</table>";

						$mailFooter = "<br><br>Regards,<br>".SITEADMINISTRATOR;

						$content = $mailHeader.$mailContent.$mailFooter;
						sendmail($employeeInfo["email"], $Subject, $content);

						/* Send mail to reporting head */
						$arrReportingHeadInfo = $objEmployee->fnGetEmployeeDetailById($employeeInfo["teamleader"]);
						if(count($arrReportingHeadInfo) > 0)
						{
							$mailHeader = "Dear ".$arrReportingHeadInfo["name"].",<br/><br/>Status for the ticket raised by your team member <b>".$employeeInfo["name"]."</b> has been updated. The details for ticket are as follows:<br/><br/>";
							
							$content = $mailHeader.$mailContent.$mailFooter;
							sendmail($arrReportingHeadInfo["email"], $Subject, $content);
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
		
		function fnGetTicketHistoryById($TicketId)
		{
			$arrTicketInfo = array();
			$db = new DB_Sql();
			
			$sSQL = "select t.*, ic.issue_category, i.issue, l.location_name, e.name as user_name, e1.name as reporting_head_name, date_format(t.ticket_raised_date,'%d-%m-%Y %H:%i') as ticket_raised_date, time_format(t.estimated_resolution_time, '%H:%i') as estimated_resolution_time from pms_ticket t LEFT JOIN pms_issue i ON i.id = t.issue_id LEFT JOIN pms_issue_category ic ON ic.id = t.issue_category_id LEFT JOIN pms_inventory_location l ON l.id = t.location_id LEFT JOIN pms_employee e ON e.id = t.user_id LEFT JOIN pms_employee e1 ON e1.id = t.first_reporting_head_id where t.id='".mysql_real_escape_string($TicketId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$status = array("0"=>"In queue", "1"=>"Being attended", "2"=>"Resolved", "3"=>"Work in progress", "4"=>"Under observation");

				if($this->next_record())
				{
					$tempTicket = $this->fetchrow(); 
					
					/*if($tempTicket["resolution_status"] == "")
						$tempTicket["resolution_status_text"] = "In queue";
					else
						$tempTicket["resolution_status_text"] = $status[$tempTicket["resolution_status"]];*/
					
					/* Get the details for all the status changes for the ticket */
					$tempTicket["call_status_history"] = array();
					$sSQL = "select e.name as resolved_by_name, rs.resolution_status, rs.resolution_description, date_format(rs.resolution_datetime,'%d-%m-%Y %H:%i') as resolution_date from pms_ticket_resolution_status rs LEFT JOIN pms_employee e ON rs.resolved_by = e.id where rs.ticket_id='".mysql_real_escape_string($TicketId)."' order by rs.id";
					$db->query($sSQL);
					if($db->num_rows())
					{
						while($db->next_record())
						{
							$tempTicket["call_status_history"][] = $db->fetchRow();
						}
					}

					$arrTicketInfo = $tempTicket;
				}
			}

			return $arrTicketInfo;
		}
		
		function fnGetUserWiseIssueTracking($ticket_date_from, $ticket_date_to)
		{
			$arrIssueInformation = array();
			
			$sSQL = "select count(t.id) as cnt_user, t.user_id, e.name as username from pms_ticket t LEFT JOIN pms_employee e ON e.id = t.user_id where date_format(t.ticket_raised_date,'%Y-%m-%d') between '".mysql_real_escape_string($ticket_date_from)."' and '".mysql_real_escape_string($ticket_date_to)."' group by t.user_id";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrIssueInformation[] = $this->fetchrow();
				}
			}

			return $arrIssueInformation;
		}
		
		function fnGetUserWiseTicketInformation($userid, $start_date, $end_date)
		{
			$arrInformation = array();
			
			$sSQL = "select t.*, t.id as ticket_id, i.issue, l.location_name, date_format(t.ticket_raised_date, '%d-%m-%Y') as ticket_date, time_format(t.estimated_resolution_time,'%H:%i') as resolution_time, tr.resolution_status, ic.issue_category from pms_ticket t LEFT JOIN pms_issue i ON i.id = t.issue_id LEFT JOIN pms_inventory_location l ON l.id = t.location_id LEFT JOIN pms_ticket_resolution_status tr ON (tr.ticket_id = t.id and tr.isdeleted='0') LEFT JOIN pms_issue_category ic ON ic.id = t.issue_category_id where t.user_id='".mysql_real_escape_string($userid)."' and date_format(t.ticket_raised_date,'%Y-%m-%d') between '".mysql_real_escape_string($start_date)."' and '".mysql_real_escape_string($end_date)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$status = array("0"=>"In queue", "1"=>"Being attended", "2"=>"Resolved", "3"=>"Work in progress", "4"=>"Under observation");
				
				while($this->next_record())
				{
					$tempTicket = $this->fetchrow();

					if($tempTicket["resolution_status"] == "")
						$tempTicket["resolution_status_text"] = "In queue";
					else
						$tempTicket["resolution_status_text"] = $status[$tempTicket["resolution_status"]];
					
					$arrInformation[] = $tempTicket;
				}
			}
			
			return $arrInformation;
		}
		
		function fnGetIssueCategoryWiseIssueTracking($ticket_date_from, $ticket_date_to)
		{
			$arrIssueInformation = array();
			
			$sSQL = "select count(t.id) as cnt_issue_category, ic.id as issue_category_id, ic.issue_category from pms_ticket t LEFT JOIN pms_issue_category ic ON ic.id = t.issue_category_id where date_format(t.ticket_raised_date,'%Y-%m-%d') between '".mysql_real_escape_string($ticket_date_from)."' and '".mysql_real_escape_string($ticket_date_to)."' group by t.issue_category_id";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrIssueInformation[] = $this->fetchrow();
				}
			}

			return $arrIssueInformation;
		}
		
		function fnGetIssueCategoryWiseTicketInformation($issue_category_id, $start_date, $end_date)
		{
			$arrInformation = array();
			
			$sSQL = "select t.*, t.id as ticket_id, i.issue, l.location_name, date_format(t.ticket_raised_date, '%d-%m-%Y') as ticket_date, time_format(t.estimated_resolution_time,'%H:%i') as resolution_time, tr.resolution_status, e.name as username from pms_ticket t LEFT JOIN pms_issue i ON i.id = t.issue_id LEFT JOIN pms_inventory_location l ON l.id = t.location_id LEFT JOIN pms_ticket_resolution_status tr ON (tr.ticket_id = t.id and tr.isdeleted='0') LEFT JOIN pms_employee e ON e.id = t.user_id where t.issue_category_id='".mysql_real_escape_string($issue_category_id)."' and date_format(t.ticket_raised_date,'%Y-%m-%d') between '".mysql_real_escape_string($start_date)."' and '".mysql_real_escape_string($end_date)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$status = array("0"=>"In queue", "1"=>"Being attended", "2"=>"Resolved", "3"=>"Work in progress", "4"=>"Under observation");
				
				while($this->next_record())
				{
					$tempTicket = $this->fetchrow();

					if($tempTicket["resolution_status"] == "")
						$tempTicket["resolution_status_text"] = "In queue";
					else
						$tempTicket["resolution_status_text"] = $status[$tempTicket["resolution_status"]];
					
					$arrInformation[] = $tempTicket;
				}
			}
			
			return $arrInformation;
		}
		
		function fnGetLocationWiseIssueTracking($ticket_date_from, $ticket_date_to)
		{
			$arrIssueInformation = array();
			
			$sSQL = "select count(t.id) as cnt_location, l.id as location_id, l.location_name from pms_ticket t LEFT JOIN pms_inventory_location l ON l.id = t.location_id where date_format(t.ticket_raised_date,'%Y-%m-%d') between '".mysql_real_escape_string($ticket_date_from)."' and '".mysql_real_escape_string($ticket_date_to)."' group by t.location_id";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrIssueInformation[] = $this->fetchrow();
				}
			}

			return $arrIssueInformation;
		}
		
		function fnGetLocationWiseTicketInformation($location_id, $start_date, $end_date)
		{
			$arrInformation = array();
			
			$sSQL = "select t.*, t.id as ticket_id, i.issue, date_format(t.ticket_raised_date, '%d-%m-%Y') as ticket_date, time_format(t.estimated_resolution_time,'%H:%i') as resolution_time, tr.resolution_status, e.name as username, ic.issue_category from pms_ticket t LEFT JOIN pms_issue i ON i.id = t.issue_id LEFT JOIN pms_ticket_resolution_status tr ON (tr.ticket_id = t.id and tr.isdeleted='0') LEFT JOIN pms_employee e ON e.id = t.user_id LEFT JOIN pms_issue_category ic ON ic.id = t.issue_category_id where t.location_id='".mysql_real_escape_string($location_id)."' and date_format(t.ticket_raised_date,'%Y-%m-%d') between '".mysql_real_escape_string($start_date)."' and '".mysql_real_escape_string($end_date)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$status = array("0"=>"In queue", "1"=>"Being attended", "2"=>"Resolved", "3"=>"Work in progress", "4"=>"Under observation");
				
				while($this->next_record())
				{
					$tempTicket = $this->fetchrow();

					if($tempTicket["resolution_status"] == "")
						$tempTicket["resolution_status_text"] = "In queue";
					else
						$tempTicket["resolution_status_text"] = $status[$tempTicket["resolution_status"]];
					
					$arrInformation[] = $tempTicket;
				}
			}
			
			return $arrInformation;
		}
		
		function fnGetDatewiseTicketInformation($start_date, $end_date)
		{
			$arrTicketInformation = array();
			
			$sSQL = "select count(id) as ticket_cnt, date_format(ticket_raised_date,'%Y-%m-%d') as ticket_date from pms_ticket where date_format(ticket_raised_date,'%Y-%m-%d') between '".mysql_real_escape_string($start_date)."' and '".mysql_real_escape_string($end_date)."' group by ticket_date";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrTicketInformation[$this->f("ticket_date")] = $this->f("ticket_cnt");
				}
			}

			return $arrTicketInformation;
		}
		
		function fnGetIssueCategoryTicketInformation($start_date, $end_date)
		{
			$arrTicketInformation = array();
			$db = new DB_Sql();
			
			/* Get the tickets raised */
			$sSQL = "select count(t.id) as issue_category_cnt, ic.issue_category, t.issue_category_id from pms_ticket t LEFT JOIN pms_issue_category ic ON ic.id = t.issue_category_id where date_format(t.ticket_raised_date,'%Y-%m-%d') between '".mysql_real_escape_string($start_date)."' and '".mysql_real_escape_string($end_date)."' group by issue_category_id";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrTicketInformation[$this->f("issue_category")]["total"] = $this->f("issue_category_cnt");
					
					/* Get the number of tickets In Queue (Pending) between the given dates */
					$cnt_inqueue = 0;
					$sSQL = "select count(t.id) as cnt_inqueue from pms_ticket t INNER JOIN pms_ticket_resolution_status ts ON (t.id = ts.ticket_id and date_format(ts.resolution_datetime,'%Y-%m-%d') between '".mysql_real_escape_string($start_date)."' and '".mysql_real_escape_string($end_date)."' and ts.id IN (select max(ts.id) from pms_ticket t INNER JOIN pms_ticket_resolution_status ts ON (t.id = ts.ticket_id and date_format(ts.resolution_datetime,'%Y-%m-%d') between '".mysql_real_escape_string($start_date)."' and '".mysql_real_escape_string($end_date)."') where t.issue_category_id ='".mysql_real_escape_string($this->f("issue_category_id"))."' group by ts.ticket_id)) where t.issue_category_id ='".mysql_real_escape_string($this->f("issue_category_id"))."' and ts.resolution_status='0' group by ts.resolution_status";
					$db->query($sSQL);
					if($db->num_rows() > 0)
					{
						if($db->next_record())
						{
							$cnt_inqueue = $db->f("cnt_inqueue");
						}
					}

					$arrTicketInformation[$this->f("issue_category")]["pending"] = $cnt_inqueue;
					
					/* Tickets resolved in given time */
					$resolved_in_time = 0;
					$sSQL = "select count(t.id) as cnt_resolved_in_time from pms_ticket t INNER JOIN pms_ticket_resolution_status ts ON (t.id = ts.ticket_id and date_format(ts.resolution_datetime,'%Y-%m-%d') between '".mysql_real_escape_string($start_date)."' and '".mysql_real_escape_string($end_date)."' and ts.id IN (select max(ts.id) from pms_ticket t INNER JOIN pms_ticket_resolution_status ts ON (t.id = ts.ticket_id and date_format(ts.resolution_datetime,'%Y-%m-%d') between '".mysql_real_escape_string($start_date)."' and '".mysql_real_escape_string($end_date)."') where t.issue_category_id ='".mysql_real_escape_string($this->f("issue_category_id"))."' and ts.resolution_status='2' and date_format(ts.resolution_datetime,'%Y-%m-%d %H:%i') between date_format(ts.resolution_datetime,'%Y-%m-%d %H:%i') and date_format(ADDTIME( t.ticket_raised_date, t.estimated_resolution_time),'%Y-%m-%d %H:%i') group by ts.ticket_id)) where t.issue_category_id ='".mysql_real_escape_string($this->f("issue_category_id"))."' and ts.resolution_status='2' group by ts.resolution_status";
					$db->query($sSQL);
					if($db->num_rows() > 0)
					{
						if($db->next_record())
						{
							$resolved_in_time = $db->f("cnt_resolved_in_time");
						}
					}
					
					$arrTicketInformation[$this->f("issue_category")]["resolved_intime"] = $resolved_in_time;
				}
			}
			
			return $arrTicketInformation;
		}
		
		function fnGetCallStatusWiseIssueTracking($month, $year)
		{
			$arrTicketInformation = array();

			//echo $sSQL = "select count(t.id) as cnt_issue_status, ts.resolution_status from pms_ticket t LEFT JOIN pms_ticket_resolution_status ts ON (t.id = ts.ticket_id and date_format(ts.resolution_datetime,'%Y-%m') = '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."') where date_format(t.ticket_raised_date,'%Y-%m') = '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."' group by ts.resolution_status";
			
			//echo $sSQL = "select * from pms_ticket t INNER JOIN pms_ticket_resolution_status ts ON (t.id = ts.ticket_id and date_format(ts.resolution_datetime,'%Y-%m') = '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."' and ts.id IN (select max(ts.id) from pms_ticket t INNER JOIN pms_ticket_resolution_status ts ON (t.id = ts.ticket_id and date_format(ts.resolution_datetime,'%Y-%m') = '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."') group by ts.ticket_id)";
			
			$sSQL = "select count(t.id) as cnt_issue_status, ts.resolution_status from pms_ticket t INNER JOIN pms_ticket_resolution_status ts ON (t.id = ts.ticket_id and date_format(ts.resolution_datetime,'%Y-%m') = '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."' and ts.id IN (select max(ts.id) from pms_ticket t INNER JOIN pms_ticket_resolution_status ts ON (t.id = ts.ticket_id and date_format(ts.resolution_datetime,'%Y-%m') = '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."') group by ts.ticket_id)) group by ts.resolution_status";

			//select count(t.id) as cnt_issue_status, ts.resolution_status from pms_ticket t LEFT JOIN pms_ticket_resolution_status ts ON (t.id = ts.ticket_id and ts.id = (select max(id) from pms_ticket_resolution_status where date_format(ts.resolution_datetime,'%Y-%m') = '2013-07' and ticket_id=t.id group by ticket_id)) where date_format(t.ticket_raised_date,'%Y-%m') = '2013-07' group by ts.resolution_status

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$status = array("0"=>"In queue", "1"=>"Being attended", "2"=>"Resolved", "3"=>"Work in progress", "4"=>"Under observation");

				while($this->next_record())
				{
					$arrTemp = $this->fetchRow();
					
					if($this->f("resolution_status") == "")
						$arrTemp["resolution_status_text"] = "In queue";
					else
						$arrTemp["resolution_status_text"] = $status[$this->f("resolution_status")];
					
					$arrTicketInformation[] = $arrTemp;
				}
			}

			return $arrTicketInformation;
		}
		
		function fnGetCallStatusWiseTicketInformation($IssueStatus, $Month, $Year)
		{
			$arrTicketInformation = array();

			//$sSQL = "select t.id as ticket_id, ts.resolution_status, date_format(t.ticket_raised_date,'%d-%m-%Y') as ticket_date, t.priority, e.name as username, e1.name as reporting_head, ic.issue_category, i.issue, l.location_name, time_format(t.estimated_resolution_time,'%H:%i') as resolution_time from pms_ticket t LEFT JOIN pms_ticket_resolution_status ts ON (t.id = ts.ticket_id and ts.isdeleted='0') LEFT JOIN pms_employee e ON e.id = t.user_id LEFT JOIN pms_employee e1 ON e1.id = t.first_reporting_head_id LEFT JOIN pms_issue_category ic ON ic.id = t.issue_category_id LEFT JOIN pms_issue i ON i.id = t.issue_id LEFT JOIN pms_inventory_location l ON l.id = t.location_id where resolution_status = '".mysql_real_escape_string($IssueStatus)."' and date_format(t.ticket_raised_date,'%Y-%m') = '".mysql_real_escape_string($Year)."-".mysql_real_escape_string($Month)."'";

			$sSQL = "select t.id as ticket_id, ts.resolution_status, date_format(t.ticket_raised_date,'%d-%m-%Y') as ticket_date, t.priority, e.name as username, e1.name as reporting_head, ic.issue_category, i.issue, l.location_name, time_format(t.estimated_resolution_time,'%H:%i') as resolution_time from pms_ticket t INNER JOIN pms_ticket_resolution_status ts ON (t.id = ts.ticket_id and date_format(ts.resolution_datetime,'%Y-%m') = '".mysql_real_escape_string($Year)."-".mysql_real_escape_string($Month)."' and ts.id IN (select max(ts.id) from pms_ticket t INNER JOIN pms_ticket_resolution_status ts ON (t.id = ts.ticket_id and date_format(ts.resolution_datetime,'%Y-%m') = '".mysql_real_escape_string($Year)."-".mysql_real_escape_string($Month)."') group by ts.ticket_id)) LEFT JOIN pms_employee e ON e.id = t.user_id LEFT JOIN pms_employee e1 ON e1.id = t.first_reporting_head_id LEFT JOIN pms_issue_category ic ON ic.id = t.issue_category_id LEFT JOIN pms_issue i ON i.id = t.issue_id LEFT JOIN pms_inventory_location l ON l.id = t.location_id where resolution_status = '".mysql_real_escape_string($IssueStatus)."'";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$status = array("0"=>"In queue", "1"=>"Being attended", "2"=>"Resolved", "3"=>"Work in progress", "4"=>"Under observation");

				while($this->next_record())
				{
					$arrTemp = $this->fetchRow();

					if($this->f("resolution_status") == "")
						$arrTemp["resolution_status_text"] = "In queue";
					else
						$arrTemp["resolution_status_text"] = $status[$this->f("resolution_status")];

					$arrTicketInformation[] = $arrTemp;
				}
			}

			return $arrTicketInformation;
		}
		
		function fnCountTicketsByStatus($StatusId)
		{
			$ticket_cnt = 0;
			
			$sSQL = "select count(t.id) as ticket_cnt from pms_ticket t INNER JOIN pms_ticket_resolution_status tr ON (tr.ticket_id = t.id and tr.isdeleted='0') where tr.resolution_status='".mysql_real_escape_string($StatusId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$ticket_cnt = $this->f("ticket_cnt");
				}
			}
			
			return $ticket_cnt;
		}
		
		function fnCountOpenTickets()
		{
			$ticket_cnt = 0;
			
			$sSQL = "select count(t.id) as ticket_cnt from pms_ticket t INNER JOIN pms_ticket_resolution_status tr ON (tr.ticket_id = t.id and tr.isdeleted='0') where tr.resolution_status not in (0,2)";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$ticket_cnt = $this->f("ticket_cnt");
				}
			}
			
			return $ticket_cnt;
		}
		
		function fnTicketResolutionWiseReport($from_date, $to_date)
		{
			$arrTickets = array();
			$db = new DB_Sql();
			$mb = new DB_Sql();

			include_once("class.holidays.php");

			$objHolidays = new holidays();

			$sSQL = "select t.*, t.id as ticket_id, date_format(ticket_raised_date,'%Y-%m-%d') as ticket_date, date_format(ticket_raised_date,'%H:%i') as ticket_time, date_format(support_start_time,'%H:%i') as support_start_time, date_format(support_end_time,'%H:%i') as support_end_time, date_format(limited_support_start_time,'%H:%i') as limited_support_start_time, date_format(limited_support_end_time,'%H:%i') as limited_support_end_time, date_format(tr.resolution_datetime,'%Y-%m-%d %H:%i') as ticket_resolution_end, e.name as user_name, ic.issue_category as issue_category_title, i.issue as issue_title, e1.name as resolved_by_user from pms_ticket t INNER JOIN pms_ticket_resolution_status tr ON (t.id = tr.ticket_id and tr.isdeleted='0' and resolution_status='2') LEFT JOIN pms_employee e ON e.id = t.user_id LEFT JOIN pms_issue_category ic ON ic.id = t.issue_category_id LEFT JOIN pms_issue i ON i.id = t.issue_id LEFT JOIN pms_employee e1 ON e1.id = tr.resolved_by where date_format(ticket_raised_date,'%Y-%m-%d') between '".mysql_real_escape_string($from_date)."' and '".mysql_real_escape_string($to_date)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrTemp = $this->fetchRow();

					$support_designation_ids = "0";
					if(isset($arrTemp["support_designations"]) && trim($arrTemp["support_designations"]) != "")
					{
						$support_designation_ids = trim($arrTemp["support_designations"]);
					}

					/* 
					 * Check leave 
					 * Create a function and send it to leave for clear code
					 * */

					$newDate = $ticket_counter_start_time = $arrTemp["ticket_date"] . " " . $arrTemp["ticket_time"];

					if(date("l", strtotime($arrTemp["ticket_date"])) == "Sunday" || count($objHolidays->fnGetHolidayByDate($arrTemp["ticket_date"])) > 0)
					{
						/* Get to the next working day */
						/* If ticket raised after IT support end time change the date and start time of the ticket raised */
						$next_date = $arrTemp["ticket_date"];

						/* Check if not sunday and does not fall in public holidays defined in holiday table */
						/* Also check that both the employees are not on leave on the same day */
						do{
							/* Fetch the next day */
							$sSQL = "select date_format(DATE_ADD('".mysql_real_escape_string($next_date)."', INTERVAL 1 DAY),'%Y-%m-%d') as next_date";
							$mb->query($sSQL);
							if($mb->num_rows() > 0)
							{
								if($mb->next_record())
								{
									$next_date = $mb->f("next_date");
								}
							}

							/* check if all the employees are not on leave on the same day i.e. atlease one employee is present if not take up the next date */
							$sSQL = "select sr.*, time_format(st.starttime,'%H:%i') as starttime, time_format(st.endtime,'%H:%i') as endtime from pms_employee e INNER JOIN pms_support_roster sr ON e.id = sr.user_id INNER JOIN pms_shift_times st ON st.id = sr.shift_id where e.designation in ($support_designation_ids) and e.id not in (select distinct e.id from pms_leave_form l INNER JOIN pms_employee e ON e.id = l.employee_id where '".mysql_real_escape_string($next_date)."' between date_format(l.start_date,'%Y-%m-%d') and date_format(l.end_date,'%Y-%m-%d') and (l.status_manager='1' or (l.status_manager='0' and l.deligateManagerId !=0 and l.manager_delegate_status='1')) and e.designation in ($support_designation_ids)) and date_format(shift_date,'%Y-%m-%d') = '".mysql_real_escape_string($next_date)."' order by st.starttime";
							$mb->query($sSQL);
							$records_fetched = $mb->num_rows();
							
						} while(date("l", strtotime($next_date)) == "Sunday" || count($objHolidays->fnGetHolidayByDate($next_date)) > 0 || $records_fetched == 0);

						$sSQL = "select sr.*, time_format(st.starttime,'%H:%i') as starttime, time_format(st.endtime,'%H:%i') as endtime from pms_employee e INNER JOIN pms_support_roster sr ON e.id = sr.user_id INNER JOIN pms_shift_times st ON st.id = sr.shift_id where e.designation in ($support_designation_ids) and e.id not in (select distinct e.id from pms_leave_form l INNER JOIN pms_employee e ON e.id = l.employee_id where '".mysql_real_escape_string($next_date)."' between date_format(l.start_date,'%Y-%m-%d') and date_format(l.end_date,'%Y-%m-%d') and (l.status_manager='1' or (l.status_manager='0' and l.deligateManagerId !=0 and l.manager_delegate_status='1')) and e.designation in ($support_designation_ids)) and date_format(shift_date,'%Y-%m-%d') = '".mysql_real_escape_string($next_date)."' order by st.starttime";
						$mb->query($sSQL);
						$num_rows = $mb->num_rows();
						if($num_rows > 0)
						{
							if($mb->next_record())
							{
								$newDate = $next_date . " " . $mb->f("starttime");
							}
						}
					}
					else
					{
						/* Fetch timing from when the ticket needs to be started */
						$sSQL = "select sr.*, time_format(st.starttime,'%H:%i') as starttime, time_format(st.endtime,'%H:%i') as endtime from pms_employee e INNER JOIN pms_support_roster sr ON e.id = sr.user_id INNER JOIN pms_shift_times st ON st.id = sr.shift_id where e.designation in ($support_designation_ids) and e.id not in (select distinct e.id from pms_leave_form l INNER JOIN pms_employee e ON e.id = l.employee_id where '".mysql_real_escape_string($arrTemp["ticket_date"])."' between date_format(l.start_date,'%Y-%m-%d') and date_format(l.end_date,'%Y-%m-%d') and (l.status_manager='1' or (l.status_manager='0' and l.deligateManagerId !=0 and l.manager_delegate_status='1')) and e.designation in ($support_designation_ids)) and date_format(shift_date,'%Y-%m-%d') = '".mysql_real_escape_string($arrTemp["ticket_date"])."' order by st.starttime";
						$db->query($sSQL);
						$num_rows = $db->num_rows();
						if($num_rows > 0)
						{
							$i = 0;
							while($db->next_record())
							{
								$i++;
								/* Ticket is raised before / after IT support time, then start the time as per the support time */
								if(strtotime($ticket_counter_start_time) <= strtotime($arrTemp["ticket_date"] . " " . $db->f("starttime")) || strtotime($ticket_counter_start_time) >= strtotime($arrTemp["ticket_date"] . " " . $db->f("endtime")))
								{
									if(strtotime($ticket_counter_start_time) <= strtotime($arrTemp["ticket_date"] . " " . $db->f("starttime")))
									{
										/* If ticket raised before IT support start time change the start time of the ticket raised */
										$newDate = $arrTemp["ticket_date"] . " " . $db->f("starttime");
										break;
									}
									
									if(strtotime($arrTemp["ticket_date"] . " " . $db->f("endtime")) < strtotime($arrTemp["ticket_date"] . " " . $db->f("starttime")))
									{
										if(strtotime($ticket_counter_start_time) >= strtotime($arrTemp["ticket_date"] . " " . $db->f("starttime")) && strtotime($ticket_counter_start_time) <= strtotime($arrTemp["ticket_date"] . " 24:00"))
										{
											break;
										}
										else if(strtotime($ticket_counter_start_time) <= strtotime($arrTemp["ticket_date"] . " 00:00" && strtotime($ticket_counter_start_time) >= strtotime($arrTemp["ticket_date"] . " " . $db->f("endtime"))))
										{
											break;
										}
									}

									if(strtotime($ticket_counter_start_time) > strtotime($arrTemp["ticket_date"] . " " . $db->f("endtime")) && $i == $num_rows)
									{
										/* Get to the next working day */
										/* If ticket raised after IT support end time change the date and start time of the ticket raised */
										$next_date = $arrTemp["ticket_date"];

										/* Check if not sunday and does not fall in public holidays defined in holiday table */
										/* Also check that both the employees are not on leave on the same day */
										do{
											/* Fetch the next day */
											$sSQL = "select date_format(DATE_ADD('".mysql_real_escape_string($next_date)."', INTERVAL 1 DAY),'%Y-%m-%d') as next_date";
											$mb->query($sSQL);
											if($mb->num_rows() > 0)
											{
												if($mb->next_record())
												{
													$next_date = $mb->f("next_date");
												}
											}
											
											/* check if all the employees are not on leave on the same day i.e. atlease one employee is present if not take up the next date */
											$sSQL = "select sr.*, time_format(st.starttime,'%H:%i') as starttime, time_format(st.endtime,'%H:%i') as endtime from pms_employee e INNER JOIN pms_support_roster sr ON e.id = sr.user_id INNER JOIN pms_shift_times st ON st.id = sr.shift_id where e.designation in ($support_designation_ids) and e.id not in (select distinct e.id from pms_leave_form l INNER JOIN pms_employee e ON e.id = l.employee_id where '".mysql_real_escape_string($next_date)."' between date_format(l.start_date,'%Y-%m-%d') and date_format(l.end_date,'%Y-%m-%d') and (l.status_manager='1' or (l.status_manager='0' and l.deligateManagerId !=0 and l.manager_delegate_status='1')) and e.designation in ($support_designation_ids)) and date_format(shift_date,'%Y-%m-%d') = '".mysql_real_escape_string($next_date)."' order by st.starttime";
											$mb->query($sSQL);
											$records_fetched = $mb->num_rows();
											
										} while(date("l", strtotime($next_date)) == "Sunday" || count($objHolidays->fnGetHolidayByDate($next_date)) > 0 || $records_fetched == 0);

										$sSQL = "select sr.*, time_format(st.starttime,'%H:%i') as starttime, time_format(st.endtime,'%H:%i') as endtime from pms_employee e INNER JOIN pms_support_roster sr ON e.id = sr.user_id INNER JOIN pms_shift_times st ON st.id = sr.shift_id where e.designation in ($support_designation_ids) and e.id not in (select distinct e.id from pms_leave_form l INNER JOIN pms_employee e ON e.id = l.employee_id where '".mysql_real_escape_string($next_date)."' between date_format(l.start_date,'%Y-%m-%d') and date_format(l.end_date,'%Y-%m-%d') and (l.status_manager='1' or (l.status_manager='0' and l.deligateManagerId !=0 and l.manager_delegate_status='1')) and e.designation in ($support_designation_ids)) and date_format(shift_date,'%Y-%m-%d') = '".mysql_real_escape_string($next_date)."' order by st.starttime";
										$mb->query($sSQL);
										$num_rows = $mb->num_rows();
										if($num_rows > 0)
										{
											if($mb->next_record())
											{
												$newDate = $next_date . " " . $mb->f("starttime");
											}
										}
									}
								}
								else if(strtotime($ticket_counter_start_time) >= strtotime($arrTemp["ticket_date"] . " " . $db->f("starttime")) && strtotime($ticket_counter_start_time) <= strtotime($arrTemp["ticket_date"] . " " . $db->f("endtime")))
								{
									break;
								}
								else
								{
									continue;
								}
							}
						}
					}
					

					$arrTemp["ticket_resolution_start"] = $newDate;

					$is_before_time = 0;
					if($arrTemp["ticket_resolution_end"] < $arrTemp["ticket_resolution_start"])
					{
						$is_before_time = 1;
					}

					$arrTemp["is_before_time"] = $is_before_time;

					/* Calculate time difference */
					$time_difference = "";
					$diff =  abs(strtotime($arrTemp["ticket_resolution_end"]) - strtotime($arrTemp["ticket_resolution_start"]));

					//echo "<br/>".$years   = floor($diff / (365*60*60*24)); 
					//echo "<br/>".$months  = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

					$years = $months = 0;
					
					/* Check difference by days hours and minutes */
					$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
					$hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));
					$minuts = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ 60);

					$time_difference = $days . " " . str_pad($hours,2,'0',STR_PAD_LEFT) . ":" . str_pad($minuts,2,'0',STR_PAD_LEFT);

					//echo "<br/>".$seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minuts*60));

					$arrTemp["time_difference"] = $time_difference;

					$arrTickets[] = $arrTemp;
				}
			}

			return $arrTickets;
		}
		
		function fnTicketAttendingReport($from_date, $to_date)
		{
			$arrTickets = array();
			$db = new DB_Sql();
			$mb = new DB_Sql();

			include_once("class.holidays.php");

			$objHolidays = new holidays();

			$sSQL = "select t.*, t.id as ticket_id, date_format(ticket_raised_date,'%Y-%m-%d') as ticket_date, date_format(ticket_raised_date,'%H:%i') as ticket_time, date_format(support_start_time,'%H:%i') as support_start_time, date_format(support_end_time,'%H:%i') as support_end_time, date_format(limited_support_start_time,'%H:%i') as limited_support_start_time, date_format(limited_support_end_time,'%H:%i') as limited_support_end_time, date_format(tr.resolution_datetime,'%Y-%m-%d %H:%i') as ticket_resolution_end, e.name as user_name, ic.issue_category as issue_category_title, i.issue as issue_title, e1.name as resolved_by_user, TIME_TO_SEC(t.estimated_resolution_time) as estimated_resolution_time_sec from pms_ticket t INNER JOIN pms_ticket_resolution_status tr ON (t.id = tr.ticket_id and resolution_status!='0' and tr.id in (select min(id) from pms_ticket_resolution_status where resolution_status!='0' group by ticket_id)) LEFT JOIN pms_employee e ON e.id = t.user_id LEFT JOIN pms_issue_category ic ON ic.id = t.issue_category_id LEFT JOIN pms_issue i ON i.id = t.issue_id LEFT JOIN pms_employee e1 ON e1.id = tr.resolved_by where date_format(ticket_raised_date,'%Y-%m-%d') between '".mysql_real_escape_string($from_date)."' and '".mysql_real_escape_string($to_date)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrTemp = $this->fetchRow();

					$support_designation_ids = "0";
					if(isset($arrTemp["support_designations"]) && trim($arrTemp["support_designations"]) != "")
					{
						$support_designation_ids = trim($arrTemp["support_designations"]);
					}

					/* 
					 * Check leave 
					 * Create a function and send it to leave for clear code
					 * */

					$newDate = $ticket_counter_start_time = $arrTemp["ticket_date"] . " " . $arrTemp["ticket_time"];

					if(date("l", strtotime($arrTemp["ticket_date"])) == "Sunday" || count($objHolidays->fnGetHolidayByDate($arrTemp["ticket_date"])) > 0)
					{
						/* Get to the next working day */
						/* If ticket raised after IT support end time change the date and start time of the ticket raised */
						$next_date = $arrTemp["ticket_date"];

						/* Check if not sunday and does not fall in public holidays defined in holiday table */
						/* Also check that both the employees are not on leave on the same day */
						do{
							/* Fetch the next day */
							$sSQL = "select date_format(DATE_ADD('".mysql_real_escape_string($next_date)."', INTERVAL 1 DAY),'%Y-%m-%d') as next_date";
							$mb->query($sSQL);
							if($mb->num_rows() > 0)
							{
								if($mb->next_record())
								{
									$next_date = $mb->f("next_date");
								}
							}

							/* check if all the employees are not on leave on the same day i.e. atlease one employee is present if not take up the next date */
							$sSQL = "select sr.*, time_format(st.starttime,'%H:%i') as starttime, time_format(st.endtime,'%H:%i') as endtime from pms_employee e INNER JOIN pms_support_roster sr ON e.id = sr.user_id INNER JOIN pms_shift_times st ON st.id = sr.shift_id where e.designation in ($support_designation_ids) and e.id not in (select distinct e.id from pms_leave_form l INNER JOIN pms_employee e ON e.id = l.employee_id where '".mysql_real_escape_string($next_date)."' between date_format(l.start_date,'%Y-%m-%d') and date_format(l.end_date,'%Y-%m-%d') and (l.status_manager='1' or (l.status_manager='0' and l.deligateManagerId !=0 and l.manager_delegate_status='1')) and e.designation in ($support_designation_ids)) and date_format(shift_date,'%Y-%m-%d') = '".mysql_real_escape_string($next_date)."' order by st.starttime";
							$mb->query($sSQL);
							$records_fetched = $mb->num_rows();
							
						} while(date("l", strtotime($next_date)) == "Sunday" || count($objHolidays->fnGetHolidayByDate($next_date)) > 0 || $records_fetched == 0);

						$sSQL = "select sr.*, time_format(st.starttime,'%H:%i') as starttime, time_format(st.endtime,'%H:%i') as endtime from pms_employee e INNER JOIN pms_support_roster sr ON e.id = sr.user_id INNER JOIN pms_shift_times st ON st.id = sr.shift_id where e.designation in ($support_designation_ids) and e.id not in (select distinct e.id from pms_leave_form l INNER JOIN pms_employee e ON e.id = l.employee_id where '".mysql_real_escape_string($next_date)."' between date_format(l.start_date,'%Y-%m-%d') and date_format(l.end_date,'%Y-%m-%d') and (l.status_manager='1' or (l.status_manager='0' and l.deligateManagerId !=0 and l.manager_delegate_status='1')) and e.designation in ($support_designation_ids)) and date_format(shift_date,'%Y-%m-%d') = '".mysql_real_escape_string($next_date)."' order by st.starttime";
						$mb->query($sSQL);
						$num_rows = $mb->num_rows();
						if($num_rows > 0)
						{
							if($mb->next_record())
							{
								$newDate = $next_date . " " . $mb->f("starttime");
							}
						}
					}
					else
					{
						/* Fetch timing from when the ticket needs to be started */
						$sSQL = "select sr.*, time_format(st.starttime,'%H:%i') as starttime, time_format(st.endtime,'%H:%i') as endtime from pms_employee e INNER JOIN pms_support_roster sr ON e.id = sr.user_id INNER JOIN pms_shift_times st ON st.id = sr.shift_id where e.designation in ($support_designation_ids) and e.id not in (select distinct e.id from pms_leave_form l INNER JOIN pms_employee e ON e.id = l.employee_id where '".mysql_real_escape_string($arrTemp["ticket_date"])."' between date_format(l.start_date,'%Y-%m-%d') and date_format(l.end_date,'%Y-%m-%d') and (l.status_manager='1' or (l.status_manager='0' and l.deligateManagerId !=0 and l.manager_delegate_status='1')) and e.designation in ($support_designation_ids)) and date_format(shift_date,'%Y-%m-%d') = '".mysql_real_escape_string($arrTemp["ticket_date"])."' order by st.starttime";
						$db->query($sSQL);
						$num_rows = $db->num_rows();
						if($num_rows > 0)
						{
							$i = 0;
							while($db->next_record())
							{
								$i++;
								/* Ticket is raised before / after IT support time, then start the time as per the support time */
								if(strtotime($ticket_counter_start_time) <= strtotime($arrTemp["ticket_date"] . " " . $db->f("starttime")) || strtotime($ticket_counter_start_time) >= strtotime($arrTemp["ticket_date"] . " " . $db->f("endtime")))
								{
									if(strtotime($ticket_counter_start_time) <= strtotime($arrTemp["ticket_date"] . " " . $db->f("starttime")))
									{
										/* If ticket raised before IT support start time change the start time of the ticket raised */
										$newDate = $arrTemp["ticket_date"] . " " . $db->f("starttime");
										break;
									}
									
									if(strtotime($arrTemp["ticket_date"] . " " . $db->f("endtime")) < strtotime($arrTemp["ticket_date"] . " " . $db->f("starttime")))
									{
										if(strtotime($ticket_counter_start_time) >= strtotime($arrTemp["ticket_date"] . " " . $db->f("starttime")) && strtotime($ticket_counter_start_time) <= strtotime($arrTemp["ticket_date"] . " 24:00"))
										{
											break;
										}
										else if(strtotime($ticket_counter_start_time) <= strtotime($arrTemp["ticket_date"] . " 00:00" && strtotime($ticket_counter_start_time) >= strtotime($arrTemp["ticket_date"] . " " . $db->f("endtime"))))
										{
											break;
										}
									}

									if(strtotime($ticket_counter_start_time) > strtotime($arrTemp["ticket_date"] . " " . $db->f("endtime")) && $i == $num_rows)
									{
										/* Get to the next working day */
										/* If ticket raised after IT support end time change the date and start time of the ticket raised */
										$next_date = $arrTemp["ticket_date"];

										/* Check if not sunday and does not fall in public holidays defined in holiday table */
										/* Also check that both the employees are not on leave on the same day */
										do{
											/* Fetch the next day */
											$sSQL = "select date_format(DATE_ADD('".mysql_real_escape_string($next_date)."', INTERVAL 1 DAY),'%Y-%m-%d') as next_date";
											$mb->query($sSQL);
											if($mb->num_rows() > 0)
											{
												if($mb->next_record())
												{
													$next_date = $mb->f("next_date");
												}
											}
											
											/* check if all the employees are not on leave on the same day i.e. atlease one employee is present if not take up the next date */
											$sSQL = "select sr.*, time_format(st.starttime,'%H:%i') as starttime, time_format(st.endtime,'%H:%i') as endtime from pms_employee e INNER JOIN pms_support_roster sr ON e.id = sr.user_id INNER JOIN pms_shift_times st ON st.id = sr.shift_id where e.designation in ($support_designation_ids) and e.id not in (select distinct e.id from pms_leave_form l INNER JOIN pms_employee e ON e.id = l.employee_id where '".mysql_real_escape_string($next_date)."' between date_format(l.start_date,'%Y-%m-%d') and date_format(l.end_date,'%Y-%m-%d') and (l.status_manager='1' or (l.status_manager='0' and l.deligateManagerId !=0 and l.manager_delegate_status='1')) and e.designation in ($support_designation_ids)) and date_format(shift_date,'%Y-%m-%d') = '".mysql_real_escape_string($next_date)."' order by st.starttime";
											$mb->query($sSQL);
											$records_fetched = $mb->num_rows();
											
										} while(date("l", strtotime($next_date)) == "Sunday" || count($objHolidays->fnGetHolidayByDate($next_date)) > 0 || $records_fetched == 0);

										$sSQL = "select sr.*, time_format(st.starttime,'%H:%i') as starttime, time_format(st.endtime,'%H:%i') as endtime from pms_employee e INNER JOIN pms_support_roster sr ON e.id = sr.user_id INNER JOIN pms_shift_times st ON st.id = sr.shift_id where e.designation in ($support_designation_ids) and e.id not in (select distinct e.id from pms_leave_form l INNER JOIN pms_employee e ON e.id = l.employee_id where '".mysql_real_escape_string($next_date)."' between date_format(l.start_date,'%Y-%m-%d') and date_format(l.end_date,'%Y-%m-%d') and (l.status_manager='1' or (l.status_manager='0' and l.deligateManagerId !=0 and l.manager_delegate_status='1')) and e.designation in ($support_designation_ids)) and date_format(shift_date,'%Y-%m-%d') = '".mysql_real_escape_string($next_date)."' order by st.starttime";
										$mb->query($sSQL);
										$num_rows = $mb->num_rows();
										if($num_rows > 0)
										{
											if($mb->next_record())
											{
												$newDate = $next_date . " " . $mb->f("starttime");
											}
										}
									}
								}
								else if(strtotime($ticket_counter_start_time) >= strtotime($arrTemp["ticket_date"] . " " . $db->f("starttime")) && strtotime($ticket_counter_start_time) <= strtotime($arrTemp["ticket_date"] . " " . $db->f("endtime")))
								{
									break;
								}
								else
								{
									continue;
								}
							}
						}
					}
					

					$arrTemp["ticket_resolution_start"] = Date('Y-m-d H:i',strtotime($newDate) + $arrTemp["estimated_resolution_time_sec"]);

					$is_before_time = 0;
					if(strtotime($arrTemp["ticket_resolution_end"]) < strtotime($arrTemp["ticket_resolution_start"]))
					{
						$is_before_time = 1;
					}

					$arrTemp["is_before_time"] = $is_before_time;

					/* Calculate time difference */
					$time_difference = "";
					$diff = abs(strtotime($arrTemp["ticket_resolution_end"]) - strtotime($arrTemp["ticket_resolution_start"]));

					//echo "<br/>".$years   = floor($diff / (365*60*60*24)); 
					//echo "<br/>".$months  = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

					$years = $months = 0;

					/* Check difference by days hours and minutes */
					$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
					$hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));
					$minuts = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ 60);

					$time_difference = $days . " " . str_pad($hours,2,'0',STR_PAD_LEFT) . ":" . str_pad($minuts,2,'0',STR_PAD_LEFT);

					//echo "<br/>".$seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minuts*60));

					$arrTemp["time_difference"] = $time_difference;

					$arrTickets[] = $arrTemp;
				}
			}

			return $arrTickets;			
		}
	}
?>

<?php

	include_once('db_mysql.php');

	class shift_movement extends DB_Sql
	{
		function __construct()
		{
		}

		function fnSaveShiftMovement($arrMovementInfo)
		{
			$totalCount = $this->fnValidateShiftMovement($_SESSION["id"], $arrMovementInfo["movement_date"]);

			if($totalCount < 2)
			{
				$arrMovementInfo["userid"] = $_SESSION["id"];
				$arrMovementInfo["addedon"] = date('Y-m-d H:i:s');
				$arrMovementInfo["approvedby_tl"] = 0;
				$arrMovementInfo["approvedby_manager"] = 0;

				$id = $this->insertArray('pms_shift_movement',$arrMovementInfo);

				include_once('class.employee.php');

				$objEmployee = new employee();

				$reportingHead = $objEmployee->fnGetReportingHead($_SESSION["id"]);

				$employeeInfo = $objEmployee->fnGetEmployeeDetailById($_SESSION["id"]);

				$arrHeads = $objEmployee->fnGetReportingHeads($_SESSION["id"]);

				/* Mail */
				if(count($arrHeads) > 0)
				{
					foreach($arrHeads as $curHead)
					{
						$MailTo = $curHead["email"];
						$Subject = "Shift Movement Request";
						$content = "Dear ".$curHead["name"].",<br><br>";
						$content .= "A new shift movement request is added. The details for the leave are as follows:<br/><br/>";
						$content .= "<table cellspacing='2' cellpadding='2' bgcolor='#E6E6E6'>
							<tr bgcolor='#FFFFFF'>
								<td><b>Employee Name: </b></td>
								<td>".$employeeInfo["name"]."</td>
							</tr>
							<tr bgcolor='#FFFFFF'>
								<td><b>Reporting Head: </b></td>
								<td>".$reportingHead."</td>
							</tr>
							<tr bgcolor='#FFFFFF'>
								<td><b>Movement On: </b></td>
								<td>".$arrMovementInfo["movement_date"].", ".$arrMovementInfo["movement_fromtime"]." - ".$arrMovementInfo["movement_totime"]."</td>
							</tr>
							<tr bgcolor='#FFFFFF'>
								<td><b>Compensation On: </b></td>
								<td>".$arrMovementInfo["compensation_date"].", ".$arrMovementInfo["compensation_fromtime"]." - ".$arrMovementInfo["compensation_totime"]."</td>
							</tr>
							<tr bgcolor='#FFFFFF'>
								<td><b>Reason: </b></td>
								<td>".$arrMovementInfo["reason"]."</td>
							</tr>
							<tr bgcolor='#FFFFFF'>
								<td colspan='2'>To approve / unapprove the shift movement please click <a href='".SERVERURL."shift_movement_request_view.php?id=".$id."'>here</a></td>
							</tr>
						</table>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}
				}
				return true;
			}
			else
			{
				return false;
			}
		}

		function fnValidateShiftMovement($EmployeeId, $MovementDate)
		{
			$arrDt = explode("-",$MovementDate);

			$CheckingDt = $arrDt[0]."-".$arrDt[1];

			$movements = 0;
			$movementsPlt = 0;
			$totalShiftMovement = 0;

			$sSQL = "select * from pms_shift_movement where userid='$EmployeeId' and date_format(movement_date,'%Y-%m') = '$CheckingDt' and (approvedby_manager = '1' or (approvedby_manager='0' && approvedby_tl='1'))";
			$this->query($sSQL);
			$movements = $this->num_rows();

			$sSQL = "select * from pms_attendance where user_id='$EmployeeId' and date_format(date,'%Y-%m') = '$CheckingDt' and leave_id='11'";
			$this->query($sSQL);
			$movementsPlt = $this->num_rows();

			$totalShiftMovement = $movements + $movementsPlt;

			return $totalShiftMovement;
		}

		function fnUserShiftMovement($UserId)
		{
			$arrMovements = array();

			$sSQL = "select id, date_format(movement_date,'%Y-%m-%d') as movementdate, date_format(compensation_date,'%Y-%m-%d') as compensationdate, date_format(movement_fromtime,'%H:%i') as movementfrom, date_format(movement_totime,'%H:%i') as movementto, date_format(compensation_fromtime,'%H:%i') as compensationfrom, date_format(compensation_totime,'%H:%i') as compensationto, approvedby_tl, approvedby_manager from pms_shift_movement where userid='$UserId'";
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

		function fnUserShiftMovementById($ShiftMovementId)
		{
			$MovementInfo = false;

			$sSQL = "select date_format(m.movement_date,'%Y-%m-%d') as movementdate, date_format(m.compensation_date,'%Y-%m-%d') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, m.reason, m.lt_comments, m.manager_comments from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid where m.id='".mysql_escape_string($ShiftMovementId)."' and m.userid='".mysql_escape_string($_SESSION["id"])."'";
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

		function fnShiftMovementById($ShiftMovementId, $ids = "")
		{
			$MovementInfo = false;

			$cond = "";
			if(trim($ids) != "")
			{
				$cond = " and m.userid in ($ids)";
			}

			$sSQL = "select m.id,m.userid,date_format(m.movement_date,'%Y-%m-%d') as movementdate, date_format(m.compensation_date,'%Y-%m-%d') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, m.reason, m.approvedby_tl, m.lt_comments, m.approvedby_manager, m.manager_comments, concat(date_format(m.movement_date,'%Y-%m-%d'),' ',m.movement_fromtime) as movementdt from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid where m.id='".mysql_escape_string($ShiftMovementId)."' $cond";
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

		function fnGetAllShiftMovementRequest($EmployeeIds)
		{
			$arrMovements = array();

			$sSQL = "select m.id, date_format(m.movement_date,'%Y-%m-%d') as movementdate, date_format(m.compensation_date,'%Y-%m-%d') as compensationdate, date_format(m.movement_fromtime,'%H:%i') as movementfrom, date_format(m.movement_totime,'%H:%i') as movementto, date_format(m.compensation_fromtime,'%H:%i') as compensationfrom, date_format(m.compensation_totime,'%H:%i') as compensationto, m.approvedby_tl, m.approvedby_manager, e.name as employeename, m.reason, e1.name as teamleadername, m.lt_comments, m.manager_comments from pms_shift_movement m INNER JOIN pms_employee e ON e.id = m.userid INNER JOIN pms_employee e1 ON e.teamleader = e1.id where m.userid in ($EmployeeIds)";
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

		function fnUpdateShiftMovement($ApprovalInfo)
		{
			if($_SESSION["usertype"] == "employee")
			{
				include_once('class.employee.php');
				$objEmployee = new employee();

				$status = array("0"=>"Pending", "1"=>"Approved", "2"=>"Unapproved");

				$MovementInfo = $this->fnShiftMovementById($ApprovalInfo["id"]);

				if($_SESSION["designation"] == 6)
				{
					/* Manager Login */
					$ApprovalInfo["manager_approval_date"] = Date("Y-m-d H:i:s");
					$this->updateArray('pms_shift_movement',$ApprovalInfo);

					$EmployeeInfo = $objEmployee->fnGetEmployeeById($MovementInfo["userid"]);

					$ManagerInfo = $objEmployee->fnGetEmployeeById($_SESSION["id"]);

					$MailTo = $EmployeeInfo["email"];
					$Subject = "Shift Movement ".$status[$ApprovalInfo["approvedby_manager"]];
					$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
					$content .= $ManagerInfo["name"]." has ".$status[$ApprovalInfo["approvedby_manager"]]." your shift movement request on ".$MovementInfo["movementdate"]." from ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]."<br/><br/>";
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);

					if($EmployeeInfo["teamleader"] != 0)
					{
						$TeamleaderInfo = $objEmployee->fnGetEmployeeById($EmployeeInfo["teamleader"]);

						$MailTo = $TeamleaderInfo["email"];

						$content = "Dear ".$TeamleaderInfo["name"].",<br><br>";
						$content .= $ManagerInfo["name"]." has ".$status[$ApprovalInfo["approvedby_manager"]]." shift movement request of ".$EmployeeInfo["name"]." on ".$MovementInfo["movementdate"]." from ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]."<br/><br/>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					return $ApprovalInfo["approvedby_manager"];
				}
				else if($_SESSION["designation"] == 7 || $_SESSION["designation"] == 13)
				{
					/* TL Login */

					$ShiftInfo = $this->fnShiftMovementById($ApprovalInfo["id"]);

					$curdt = Date("Y-m-d H:i:s");

					if($ShiftInfo["movementdt"] < $curdt)
						return -1;

					$ApprovalInfo["lt_approval_date"] = $curdt;
					$this->updateArray('pms_shift_movement',$ApprovalInfo);

					$EmployeeInfo = $objEmployee->fnGetEmployeeById($MovementInfo["userid"]);

					$TeamleaderInfo = $objEmployee->fnGetEmployeeById($_SESSION["id"]);

					$MailTo = $EmployeeInfo["email"];
					$Subject = "Shift Movement ".$status[$ApprovalInfo["approvedby_tl"]];

					$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
					$content .= $TeamleaderInfo["name"]." has ".$status[$ApprovalInfo["approvedby_tl"]]." your shift movement request on ".$MovementInfo["movementdate"]." from ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]."<br/><br/>";
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
					sendmail($MailTo, $Subject, $content);

					if($TeamleaderInfo["teamleader"] != 0)
					{
						$ManagerInfo = $objEmployee->fnGetEmployeeById($TeamleaderInfo["teamleader"]);

						$MailTo = $ManagerInfo["email"];

						$content = "Dear ".$ManagerInfo["name"].",<br><br>";
						$content .= $TeamleaderInfo["name"]." has ".$status[$ApprovalInfo["approvedby_tl"]]." shift movement request of ".$EmployeeInfo["name"]." on ".$MovementInfo["movementdate"]." from ".$MovementInfo["movementfrom"]." to ".$MovementInfo["movementto"]."<br/><br/>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}

					return $ApprovalInfo["approvedby_tl"];
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
	}
?>

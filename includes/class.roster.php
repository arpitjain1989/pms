<?php

	include_once('db_mysql.php');

	class roster extends DB_Sql
	{
		function __construct()
		{
		}

		function fnGetRosterDays($date = "")
		{
			$RosterDays = array();

			if(trim($date) == "")
			{
				$Date = date('Y-m-d', strtotime('next monday'));
				$DisplayDate = date('d-M-Y, l', strtotime('next monday'));
			}
			else
			{
				$Date = date('Y-m-d', strtotime($date));
				$DisplayDate = date('d-M-Y, l', strtotime($date));
			}

			$RosterDays[$Date] =  $DisplayDate;

			for($i=0; $i < 6; $i++)
			{
				$curDate = $Date;
				$Date = date('Y-m-d', strtotime('+1 day', strtotime($curDate)));
				$DisplayDate = date('d-M-Y, l', strtotime('+1 day', strtotime($curDate)));

				$RosterDays[$Date] =  $DisplayDate;
			}

			return $RosterDays;
		}

		function fnSaveRoster($arrRoster)
		{
			$arrRosterInfo = $this->fnGetUserRosterByDates($arrRoster["start_date"], $arrRoster["end_date"], $arrRoster["userid"]);
			if(count($arrRosterInfo) == 0)
			{
				$id = $this->insertArray('pms_roster',$arrRoster);
				return $id;
			}
			else
			{
				if(isset($arrRosterInfo["id"]) && trim($arrRosterInfo["id"]) != '')
				{
					$arrRoster["id"] = $arrRosterInfo["id"];
					$id = $this->updateArray('pms_roster',$arrRoster);
				}
				return $arrRoster["id"];
			}
		}

		function fnSaveRosterDetail($rosterDetail)
		{
			$sSQL = "select * from pms_roster_detail where rosterid='".mysql_real_escape_string($rosterDetail["rosterid"])."' and date_format(rostereddate,'%Y-%m-%d')='".mysql_real_escape_string($rosterDetail["rostereddate"])."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$rosterDetail["id"] = $this->f("id");
					$this->updateArray('pms_roster_detail',$rosterDetail);
				}
			}
			else
			{
				$this->insertArray('pms_roster_detail',$rosterDetail);
			}
		}

		function fnGetUserRosterByDates($start, $end, $employeeid)
		{
			$arrRosterInfo = array();

			$sSQL = "select * from pms_roster where date_format(start_date,'%Y-%m-%d')='$start' and date_format(end_date,'%Y-%m-%d')='$end' and userid='$employeeid'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrRosterInfo = $this->fetchrow();
				}
			}

			return $arrRosterInfo;
		}

		function fnCheckRosterEntered($start, $end, $employeeids)
		{
			if(trim($employeeids) == "")
				$employeeids = 0;

			$sSQL = "select count(id) as totalcnt from pms_roster where date_format(start_date,'%Y-%m-%d')='$start' and date_format(end_date,'%Y-%m-%d')='$end' and userid in ($employeeids) and isfinalized='1'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					if($this->f("totalcnt") == 0)
						return true;
					else
						return false;
				}
				else
					return false;
			}
			else
				return false;
		}

		function fnGetUserRosters()
		{
			$arrRoster = array();
			$sSQL = "select distinct date_format(start_date,'%Y-%m-%d') as start_date, date_format(end_date,'%Y-%m-%d') as end_date from pms_roster where reportinghead='".mysql_real_escape_string($_SESSION["id"])."' order by start_date desc";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRoster[] = array("start"=>$this->f("start_date"), "end"=>$this->f("end_date"));
				}
			}
			return $arrRoster;
		}

		function fnGetAllRosters()
		{
			$arrRoster = array();
			/*echo $sSQL = "(select distinct date_format(r.start_date,'%Y-%m-%d') as start_date, date_format(r.end_date,'%Y-%m-%d') as end_date, r.addedby, if(r.autoadded=1,'Auto added',e1.name) as addedbyname, if(e2.designation='6','0',r.reportinghead) as reportinghead, if(e2.designation='6','Team Leaders',CONCAT(e2.name,'\'s Team')) as reportingheadname from pms_roster r LEFT JOIN pms_employee e1 ON r.addedby = e1.id INNER JOIN pms_employee e2 ON r.reportinghead = e2.id) UNION (select distinct date_format(r.start_date,'%Y-%m-%d') as start_date, date_format(r.end_date,'%Y-%m-%d') as end_date, r.addedby, if(r.autoadded=1,'Auto added',e1.name) as addedbyname, r.reportinghead as reportinghead, CONCAT(e2.name,'\'s Team') as reportingheadname from pms_roster r LEFT JOIN pms_employee e1 ON r.addedby = e1.id INNER JOIN pms_employee e2 ON r.reportinghead = e2.id and r.reportinghead in (select distinct teamleader from pms_employee where designation in (5,9,10,11,12,15,16)) and e2.designation = '6')";*/
			/*$sSQL = "select distinct date_format(r.start_date,'%Y-%m-%d') as start_date, date_format(r.end_date,'%Y-%m-%d') as end_date, r.addedby, if(r.autoadded=1,'Auto added',e1.name) as addedbyname, if(emp.designation='7' || emp.designation='13','0',r.reportinghead) as reportinghead, if(emp.designation='7' || emp.designation='13','Team Leaders',CONCAT(e2.name,'\'s Team')) as reportingheadname from pms_roster r LEFT JOIN pms_employee e1 ON r.addedby = e1.id INNER JOIN pms_employee e2 ON r.reportinghead = e2.id INNER JOIN pms_employee emp ON r.userid = emp.id and emp.status='0' GROUP BY start_date, reportinghead";*/
			$sSQL = "select distinct date_format(r.start_date,'%Y-%m-%d') as start_date, date_format(r.end_date,'%Y-%m-%d') as end_date, r.addedby, if(r.autoadded=1,'Auto added',e1.name) as addedbyname, r.reportinghead, CONCAT(e2.name,'\'s Team') as reportingheadname from pms_roster r LEFT JOIN pms_employee e1 ON r.addedby = e1.id INNER JOIN pms_employee e2 ON r.reportinghead = e2.id INNER JOIN pms_employee emp ON r.userid = emp.id and emp.status='0' GROUP BY start_date, reportinghead";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRoster[] = $this->fetchrow();
				}
			}
			return $arrRoster;
		}

		function fnGetRosterDetailsByUserid($start, $end, $userid, $curdate)
		{
			$rosterDetail = array();
			$sSQL = "select r.*, rd.rostereddate, date_format(r.start_date,'%Y-%m-%d') as start_date, date_format(r.end_date,'%Y-%m-%d') as end_date, date_format(r.weekoffdate,'%Y-%m-%d') as weekoffdate, rd.attendance, rd.shiftid as roster_shiftid, st.title as shifttitle, time_format(st.starttime,'%H:%i') as shiftstart, time_format(st.endtime,'%H:%i') as shiftend from pms_roster r INNER JOIN pms_roster_detail rd ON r.id = rd.rosterid LEFT JOIN pms_shift_times st ON st.id = rd.shiftid where date_format(r.start_date,'%Y-%m-%d') = '".mysql_real_escape_string($start)."' and date_format(r.end_date,'%Y-%m-%d') = '".mysql_real_escape_string($end)."' and r.userid='".mysql_real_escape_string($userid)."' and date_format(rd.rostereddate,'%Y-%m-%d') = '$curdate'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$rosterDetail = $this->fetchrow();
				}
			}

			return $rosterDetail;
		}

		function fnGetRosteredEmployee($start, $end)
		{
			$arrEmployee = array();

			$sSQL = "select distinct userid as userid from pms_roster where date_format(start_date,'%Y-%m-%d') = '".mysql_real_escape_string($start)."' and date_format(end_date,'%Y-%m-%d') = '".mysql_real_escape_string($end)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->f("userid");
				}
			}

			return $arrEmployee;
		}

		function fnGetRosteredShiftByUserAndDate($userid, $curdate)
		{
			$shiftid = "";
			$sSQL = "select rd.shiftid as shiftid from pms_roster r INNER JOIN pms_roster_detail rd ON r.id = rd.rosterid where r.userid='".mysql_real_escape_string($userid)."' and date_format(rd.rostereddate,'%Y-%m-%d') = '$curdate'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$shiftid = $this->f("shiftid");
				}
			}

			return $shiftid;
		}

		function fnGetUnrosteredTeams($startdate)
		{
			$arrRosteredTeams = array();
			$arrUnRosteredTeams = array();

			include_once("class.employee.php");
			
			$objEmployee = new employee();

			$arrEmployee = $objEmployee->fnGetReportingHeadForRoster();
			$arrEmployee[0] = 0;

			$empIds = implode(",",array_keys($arrEmployee));

			$sSQL = "select distinct reportinghead from pms_roster where date_format(start_date,'%Y-%m-%d') = '".mysql_real_escape_string($startdate)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRosteredTeams[] = $this->f("reportinghead");
				}
			}

			$arrRosteredTeams[] = 0;
			$arrRosteredTeams = array_filter($arrRosteredTeams,'strlen');
			$ids = implode(",",$arrRosteredTeams);

			/*$sSQL = "select e.id, e.name, e.email, e.teamleader, e1.id as managerid, e1.name as managername, e1.email as manageremail, e1.teamleader as managerteamleader from pms_employee e LEFT JOIN pms_employee e1 ON (e.teamleader = e1.id and e1.designation='6') where (e.designation in (7,13) or (e.designation = '6' and e.id in (select distinct teamleader from pms_employee where designation in (5,9,10,11,12,15,16,14,30,31,32,33,34,35,36,37,38,39,40,41,42,43,46)))) and e.id not in ($ids)";*/
			
			$sSQL = "select e.id, e.name, e.email, e.teamleader, e1.id as managerid, e1.name as managername, e1.email as manageremail, e1.teamleader as managerteamleader from pms_employee e LEFT JOIN pms_employee e1 ON e.teamleader = e1.id where e.id in ($empIds) and e.id not in ($ids) and e.status='0'";
			
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrUnRosteredTeams[$this->f("id")] = array("id"=>$this->f("id"), "name"=>$this->f("name"), "email"=>$this->f("email"), "reportinghead"=>$this->f("teamleader"), "for" => "");
				}
			}

			return $arrUnRosteredTeams;
		}

		function fnGetUnrosteredLeavesAndShiftMovements($start_date, $end_date, $reportingHeadId, $isManager = false)
		{
			/* Patch as tl and manager id is considered first reporting head and 2nd reporting head resp */
			if($isManager)
				$sSQL = "select * from pms_leave_form where ('".mysql_real_escape_string($start_date)."' between date_format(start_date,'%Y-%m-%d') and date_format(end_date,'%Y-%m-%d') or '".mysql_real_escape_string($end_date)."' between date_format(start_date,'%Y-%m-%d') and date_format(end_date,'%Y-%m-%d') or   date_format(start_date,'%Y-%m-%d') between '".mysql_real_escape_string($start_date)."' and '".mysql_real_escape_string($end_date)."' or date_format(end_date,'%Y-%m-%d') between '".mysql_real_escape_string($start_date)."' and '".mysql_real_escape_string($end_date)."') and ((teamleader_id='".mysql_real_escape_string($reportingHeadId)."' and status_manager='0') or (manager_id='".mysql_real_escape_string($reportingHeadId)."' and status_manager='0') or (deligateTeamLeaderId='".mysql_real_escape_string($reportingHeadId)."' and delegate_status='0') or (deligateManagerId='".mysql_real_escape_string($reportingHeadId)."' and manager_delegate_status='0'))";
			else
				$sSQL = "select * from pms_leave_form where ('".mysql_real_escape_string($start_date)."' between date_format(start_date,'%Y-%m-%d') and date_format(end_date,'%Y-%m-%d') or '".mysql_real_escape_string($end_date)."' between date_format(start_date,'%Y-%m-%d') and date_format(end_date,'%Y-%m-%d') or   date_format(start_date,'%Y-%m-%d') between '".mysql_real_escape_string($start_date)."' and '".mysql_real_escape_string($end_date)."' or date_format(end_date,'%Y-%m-%d') between '".mysql_real_escape_string($start_date)."' and '".mysql_real_escape_string($end_date)."') and ((teamleader_id='".mysql_real_escape_string($reportingHeadId)."' and status='0') or (manager_id='".mysql_real_escape_string($reportingHeadId)."' and status_manager='0') or (deligateTeamLeaderId='".mysql_real_escape_string($reportingHeadId)."' and delegate_status='0') or (deligateManagerId='".mysql_real_escape_string($reportingHeadId)."' and manager_delegate_status='0'))";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				return 1;
			}
			else
			{
				/* Check for half leaves */
				if($isManager)
					$sSQL = "select * from pms_half_leave_form where date_format(start_date,'%Y-%m-%d') between date_format('".mysql_real_escape_string($start_date)."','%Y-%m-%d') and date_format('".mysql_real_escape_string($end_date)."','%Y-%m-%d') and ((teamleader_id='".mysql_real_escape_string($reportingHeadId)."' and status_manager='0') or (manager_id='".mysql_real_escape_string($reportingHeadId)."' and status_manager='0') or (deligateTeamLeaderId='".mysql_real_escape_string($reportingHeadId)."' and delegate_status='0') or (deligateManagerId='".mysql_real_escape_string($reportingHeadId)."' and manager_delegate_status='0'))";
				else
					$sSQL = "select * from pms_half_leave_form where date_format(start_date,'%Y-%m-%d') between date_format('".mysql_real_escape_string($start_date)."','%Y-%m-%d') and date_format('".mysql_real_escape_string($end_date)."','%Y-%m-%d') and ((teamleader_id='".mysql_real_escape_string($reportingHeadId)."' and status='0') or (manager_id='".mysql_real_escape_string($reportingHeadId)."' and status_manager='0') or (deligateTeamLeaderId='".mysql_real_escape_string($reportingHeadId)."' and delegate_status='0') or (deligateManagerId='".mysql_real_escape_string($reportingHeadId)."' and manager_delegate_status='0'))";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					return 1;
				}
				else
				{
					/* Check for shift movement */

					if($isManager)
						$sSQL = "select * from pms_shift_movement where date_format(movement_date,'%Y-%m-%d') between date_format('".mysql_real_escape_string($start_date)."','%Y-%m-%d') and date_format('".mysql_real_escape_string($end_date)."','%Y-%m-%d') and ((reportinghead1='".mysql_real_escape_string($reportingHeadId)."' and approvedby_manager='0') or (reportinghead2='".mysql_real_escape_string($reportingHeadId)."' and approvedby_manager='0') or (delegatedtl_id='".mysql_real_escape_string($reportingHeadId)."' and delegatedtl_status='0') or (delegatedmanager_id='".mysql_real_escape_string($reportingHeadId)."' and delegatedmanager_status='0'))";
					else
						$sSQL = "select * from pms_shift_movement where date_format(movement_date,'%Y-%m-%d') between date_format('".mysql_real_escape_string($start_date)."','%Y-%m-%d') and date_format('".mysql_real_escape_string($end_date)."','%Y-%m-%d') and ((reportinghead1='".mysql_real_escape_string($reportingHeadId)."' and approvedby_tl='0') or (reportinghead2='".mysql_real_escape_string($reportingHeadId)."' and approvedby_tl='0') or (delegatedtl_id='".mysql_real_escape_string($reportingHeadId)."' and delegatedtl_status='0') or (delegatedmanager_id='".mysql_real_escape_string($reportingHeadId)."' and delegatedmanager_status='0'))";
					$this->query($sSQL);
					if($this->num_rows() > 0)
					{
						return 1;
					}
					else
					{
						return 0;
					}
				}
			}
		}

		function fnFinalizeRoster()
		{
			$arrRosterDates = $this->fnGetRosterDays();
			$arrKeys = array_keys($arrRosterDates);

			$start_date = $arrKeys[0];

			$sSQL = "update pms_roster set isfinalized='1' where date_format(start_date,'%Y-%m-%d') <= '".mysql_real_escape_string($start_date)."'";
			$this->query($sSQL);
		}

	}
?>

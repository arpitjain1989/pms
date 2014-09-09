<?php

	include_once('db_mysql.php');

	class it_support_roster extends DB_Sql
	{
		function __construct()
		{
		}
		
		function fnSaveSupportRoster($SupportRosterInfo)
		{
			if(count($SupportRosterInfo["shift_id"]) > 0)
			{
				foreach($SupportRosterInfo["shift_id"] as $user_id => $arrDates)
				{
					$arrRosterInfo["user_id"] = $user_id;
					if(count($arrDates) > 0)
					{
						foreach($arrDates as $curDate => $shiftId)
						{
							$sSQL = "select * from pms_support_roster where user_id='".mysql_real_escape_string($user_id)."' and date_format(shift_date,'%Y-%m-%d')='".mysql_real_escape_string($curDate)."' and shift_id='".mysql_real_escape_string($shiftId)."'";
							$this->query($sSQL);
							if($this->num_rows() == 0)
							{
								/* Update previous records if any */
								$sSQL = "update pms_support_roster set isdeleted='1', deleted_datetime='".Date('Y-m-d H:i:s')."' where user_id='".mysql_real_escape_string($user_id)."' and date_format(shift_date,'%Y-%m-%d')='".mysql_real_escape_string($curDate)."'";
								$this->query($sSQL);
								
								/* Insert new shift data */
								$arrRosterInfo["shift_date"] = $curDate;
								$arrRosterInfo["shift_id"] = $shiftId;
								$arrRosterInfo["isdeleted"] = 0;
								$arrRosterInfo["is_autoadded"] = 0;
								$arrRosterInfo["addedon"] = Date('Y-m-d H:i:s');
								
								$this->insertArray("pms_support_roster",$arrRosterInfo);
								
								/* Set the data in attendance module */
								/* Check if attendance already added */
								$sSQL = "select id from pms_attendance where date_format(date,'%Y-%m-%d')='".mysql_real_escape_string($curDate)."' and user_id='".mysql_real_escape_string($user_id)."'";
								$this->query($sSQL);
								if($this->num_rows() > 0)
								{
									/* Data found so update the shift time */
									$sSQL = "update pms_attendance set shift_id='".mysql_real_escape_string($shiftId)."' where date_format(date,'%Y-%m-%d')='".mysql_real_escape_string($curDate)."' and user_id='".mysql_real_escape_string($user_id)."'";
									$this->query($sSQL);
								}
								else
								{
									/* No attendance data found, so add the attendance data */
									$attendanceInfo["user_id"] = $user_id;
									$attendanceInfo["date"] = $curDate;
									$attendanceInfo["shift_id"] = $shiftId;
									$attendanceInfo["last_modified"] = Date('Y-m-d H:i:s');

									$this->insertArray("pms_attendance",$attendanceInfo);
								}
							}
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

		function fnGetSupportRoster($RosterDate, $UserId)
		{
			$ShiftId = 0;

			$sSQL = "select shift_id from pms_support_roster where date_format(shift_date, '%Y-%m-%d') = '".mysql_real_escape_string($RosterDate)."' and user_id='".mysql_real_escape_string($UserId)."' and isdeleted='0'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				if($this->next_record())
				{
					$ShiftId = $this->f("shift_id");
				}
			}
			else
			{
				/* If roster not added check the attendance data */
				$sSQL = "select shift_id from pms_attendance where date_format(date, '%Y-%m-%d') = '".mysql_real_escape_string($RosterDate)."' and user_id='".mysql_real_escape_string($UserId)."'";
				$this->query($sSQL);
				if($this->num_rows())
				{
					if($this->next_record())
					{
						$ShiftId = $this->f("shift_id");
					}
				}
				else
				{
					/* If attendance not marked, get the default shift from the employee master */
					$sSQL = "select shiftid from pms_employee where id='".mysql_real_escape_string($UserId)."'";
					$this->query($sSQL);
					if($this->num_rows())
					{
						if($this->next_record())
						{
							$ShiftId = $this->f("shiftid");
						}
					}
				}
			}

			return $ShiftId;
		}
		
		function fnCheckIfRosterAlreadyEntered($UserId, $RosterDate)
		{
			/* Check if the roster for the support team is already added or not */
			$sSQL = "select * from pms_support_roster where user_id='".mysql_real_escape_string($UserId)."' and date_format(shift_date,'%Y-%m-%d')='".mysql_real_escape_string($RosterDate)."'";
			$this->query($sSQL);
			if($this->num_rows() == 0)
				return false; /* Not entered */
			else
				return true; /* Already entered */
		}
		
		function fnAutoInsertSupportRoster($UserId, $ShiftId, $RosterDate)
		{
			/* Insert values in roster and update the shift time in attendance */
			$arrRosterInfo["user_id"] = $UserId;
			$arrRosterInfo["shift_date"] = $RosterDate;
			$arrRosterInfo["shift_id"] = $ShiftId;
			$arrRosterInfo["isdeleted"] = 0;
			$arrRosterInfo["is_autoadded"] = 1;
			$arrRosterInfo["addedon"] = Date('Y-m-d H:i:s');

			$this->insertArray("pms_support_roster",$arrRosterInfo);
			
			/* Set the data in attendance module */
			/* Check if attendance already added */
			$sSQL = "select id from pms_attendance where date_format(date,'%Y-%m-%d')='".mysql_real_escape_string($RosterDate)."' and user_id='".mysql_real_escape_string($UserId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				/* Data found so update the shift time */
				$sSQL = "update pms_attendance set shift_id='".mysql_real_escape_string($ShiftId)."' where date_format(date,'%Y-%m-%d')='".mysql_real_escape_string($RosterDate)."' and user_id='".mysql_real_escape_string($UserId)."'";
				$this->query($sSQL);
			}
			else
			{
				/* No attendance data found, so add the attendance data */
				$attendanceInfo["user_id"] = $UserId;
				$attendanceInfo["date"] = $RosterDate;
				$attendanceInfo["shift_id"] = $ShiftId;
				$attendanceInfo["last_modified"] = Date('Y-m-d H:i:s');

				$this->insertArray("pms_attendance",$attendanceInfo);
			}
		}
	}
?>

<?php

	include_once('db_mysql.php');
	class shifts extends DB_Sql
	{
		function __construct()
		{
		}
		function fnInsertShift($arrshift)
		{
		//	print_r($arrshift); die;
			$arrNewRecords = array("title"=>$arrshift['title'],"description"=>$arrshift['description'],"starttime"=>trim($arrshift['starttime']),"endtime"=>trim($arrshift['endtime']));

			$this->insertArray('pms_shift_times',$arrNewRecords);
			return true;
		}
		function fnGetAllShifts($ExcludeInduction = true)
		{
			$arrShiftValues = array();
			
			$cond = " where 1=1 ";
			if(!$ExcludeInduction)
				$cond .= " and id != '22'";
			
			$query = "SELECT *, TIME_FORMAT(starttime, '%H:%i') as starttime, TIME_FORMAT(endtime, '%H:%i') as endtime FROM `pms_shift_times` $cond";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrShiftValues[] = $this->fetchrow();
				}
			}
			return $arrShiftValues;
		}

		function fnGetShiftById($id)
		{
			$arrShiftValues = array();
			$query = "SELECT *, TIME_FORMAT(starttime, '%H:%i') as starttime, TIME_FORMAT(endtime, '%H:%i') as endtime FROM `pms_shift_times` WHERE `id` = '".mysql_real_escape_string($id)."' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrShiftValues = $this->fetchrow();
				}
			}
			return $arrShiftValues;
		}

		function fnUpdateShifts($arrPost)
		{
			$this->updateArray('pms_shift_times',$arrPost);
			return true;
		}

		function fnDeleteShift($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_shift_times` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}
		
		function fnGetShiftIdByName($ShiftName)
		{
			$ShiftId = 0;
			$sSQL = "select id from pms_shift_times where title='".mysql_real_escape_string($ShiftName)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$ShiftId = $this->f("id");
				}
			}
			
			return $ShiftId;
		}
		
		function fnAddAllowedShiftTime($arrShiftTime)
		{
			if($arrShiftTime["headid"] != "" && isset($arrShiftTime["chkshifttime"]) && count($arrShiftTime["chkshifttime"]) > 0)
			{
				$arrShift = array("headid" => $arrShiftTime["headid"],"addeddate" => Date('Y-m-d H:i:s'));
				$AllowedId = $this->insertArray("pms_allowed_shift_time",$arrShift);
				
				foreach($arrShiftTime["chkshifttime"] as $shiftid)
				{
					$arrShiftDetail = array("allowedshifttimeid" => $AllowedId,"shiftid" => $shiftid);
					$this->insertArray("pms_allowedshifts_detail",$arrShiftDetail);
				}
				
				return true;
			}
			else
				return false;
		}
		
		function fnAllowedShiftsByHeadId($headid)
		{
			$arrShifts = array();
			
			$sSQL = "select id from pms_allowed_shift_time where headid='".mysql_real_escape_string($headid)."' order by id desc limit 0,1";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$id = $this->f("id");
					
					$sSQL = "select shiftid from pms_allowedshifts_detail where allowedshifttimeid='".mysql_real_escape_string($id)."'";
					$this->query($sSQL);
					if($this->num_rows() > 0)
					{
						while($this->next_record())
						{
							$arrShifts[] = $this->f("shiftid");
						}
					}
				}
			}
			
			return $arrShifts;
		}
		
		function fnAllowedShiftsDetailsByHeadId($headid)
		{
			$arrShifts = array();
			
			$sSQL = "select id from pms_allowed_shift_time where headid='".mysql_real_escape_string($headid)."' order by id desc limit 0,1";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$id = $this->f("id");
					
					$sSQL = "select s.* from pms_allowedshifts_detail a INNER JOIN pms_shift_times s ON a.shiftid = s.id where a.allowedshifttimeid='".mysql_real_escape_string($id)."'";
					$this->query($sSQL);
					if($this->num_rows() > 0)
					{
						while($this->next_record())
						{
							$arrShifts[] = $this->fetchrow();
						}
					}
				}
			}
			
			return $arrShifts;
		}

		function fnGetShiftTimes($id)
		{
			$arrShifts = array();
			
			$sSQL = "select starttime,endtime from `pms_shift_times` where id='".mysql_real_escape_string($id)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$startTime = $this->f("starttime");
					$endTime = $this->f("endtime");
				}
			}
			$actual_timings = $startTime . ' To ' . $endTime;
			return $actual_timings;
		}


		function fnGetLeaveTypes($id)
		{
			$title = "";
			
			$sSQL = "select title from `pms_leave_type` where id='".mysql_real_escape_string($id)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$title = $this->f("title");
				}
			}
			return $title;
		}
	}
?>

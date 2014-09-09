<?php

	include_once('db_mysql.php');

	class it_support_time extends DB_Sql
	{
		function __construct()
		{
		}
		
		function fnSaveSupportTime($SupportTimeInfo)
		{
			$support_designation_ids = "";
			if(isset($SupportTimeInfo["support_designations"]) and count($SupportTimeInfo["support_designations"]) > 0)
			{
				$support_designation_ids = implode(",",$SupportTimeInfo["support_designations"]);
			}
			
			$SupportTimeInfo["support_designations"] = $support_designation_ids;
			
			/* Check if value same as previous value */
			$sSQL = "select * from pms_support_timings where isdeleted='0' and time_format(support_start_time,'%H:%i') = '".mysql_real_escape_string($SupportTimeInfo["support_start_time"])."' and time_format(support_end_time,'%H:%i') = '".mysql_real_escape_string($SupportTimeInfo["support_end_time"])."' and time_format(limited_support_start_time,'%H:%i') = '".mysql_real_escape_string($SupportTimeInfo["limited_support_start_time"])."' and time_format(limited_support_end_time,'%H:%i') = '".mysql_real_escape_string($SupportTimeInfo["limited_support_end_time"])."' and support_designations = '".mysql_real_escape_string($SupportTimeInfo["support_designations"])."'";
			$this->query($sSQL);
			if($this->num_rows() == 0)
			{
				/* If not as the previos record, then save the data */
				
				/* Mark previous record as deleted */
				$sSQL = "update pms_support_timings set isdeleted='1', deleted_datetime='".Date('Y-m-d H:i:s')."' where isdeleted='0'";
				$this->query($sSQL);
				
				/* Insert new Support timings */
				$SupportTimeInfo["addedon"] = Date('Y-m-d H:i:s');
				$SupportTimeInfo["isdeleted"] = 0;
				
				$this->insertArray("pms_support_timings",$SupportTimeInfo);
				
				return true;
			}
			else
			{
				return false;
			}
		}
		
		function fnGetSupportTime()
		{
			$arrShiftTiming = array();

			$sSQL = "select time_format(support_start_time,'%H:%i') as support_start_time, time_format(support_end_time,'%H:%i') as support_end_time, time_format(limited_support_start_time,'%H:%i') as limited_support_start_time, time_format(limited_support_end_time,'%H:%i') as limited_support_end_time,support_designations from pms_support_timings where isdeleted='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrShiftTiming = $this->fetchRow();
				}
			}
			
			return $arrShiftTiming;
		}
	}
?>


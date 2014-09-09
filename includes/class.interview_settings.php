<?php
include_once('db_mysql.php');
	class interview_settings extends DB_Sql
	{
		function __construct()
		{
		}
		
		function fnSaveInterviewSettings($arrInterviewSettings)
		{
			$interviewer_designations = '0';
			if(isset($arrInterviewSettings["interviewer_designations"]) && count($arrInterviewSettings["interviewer_designations"]) > 0)
			{
				$arrInterviewSettings["interviewer_designations"][] = 0;
				$interviewer_designations = implode(",",$arrInterviewSettings["interviewer_designations"]);
			}
			$arrInterviewSettings["interviewer_designations"] = $interviewer_designations;
			
			$managers_designations = '0';
			if(isset($arrInterviewSettings["managers_designations"]) && count($arrInterviewSettings["managers_designations"]) > 0)
			{
				$arrInterviewSettings["managers_designations"][] = 0;
				$managers_designations = implode(",",$arrInterviewSettings["managers_designations"]);
			}

			$arrInterviewSettings["managers_designations"] = $managers_designations;
			$arrInterviewSettings["last_modified"] = Date('Y-m-d H:i:s');

			if(isset($arrInterviewSettings["id"]) && trim($arrInterviewSettings["id"]) != "")
			{
				$this->updateArray('pms_interview_designations',$arrInterviewSettings);
				return true;
			}
			else
			{
				if(isset($arrInterviewSettings["id"]))
					unset($arrInterviewSettings["id"]);

				$this->insertArray('pms_interview_designations',$arrInterviewSettings);
				return true;
			}
		}
		
		function fnGetInterviewSettings()
		{
			$arrInterviewSettings = array();
			
			$sSQL = "select * from pms_interview_designations order by id desc limit 0,1";
			$this->query($sSQL);
			if($this->num_rows())
			{
				if($this->next_record())
				{
					$arrInterviewSettings = $this->fetchRow();
				}
			}
			
			return $arrInterviewSettings;
		}
	}
?>

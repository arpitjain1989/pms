<?php
include_once('db_mysql.php');
	class job_type extends DB_Sql
	{
		function __construct()
		{
		}
		function fnInsertJobType($arrJobType)
		{
			$this->insertArray('pms_job_types',$arrJobType);
			return true;
		}
		function fnGetAllJobType()
		{
			$arrJobTypeValues = array();
			//$query = "SELECT * ,employee.id AS emp_id,designation.id as des_id, designation.title as des_title,departments.title as dep_title FROM `pms_job_types` AS employee INNER JOIN pms_departments AS departments ON employee.department =departments.id INNER JOIN pms_designation AS designation ON employee.designation =designation.id ";
			$query = "SELECT * FROM `pms_job_types`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrJobTypeValues[] = $this->fetchrow();
				}
			}
			return $arrJobTypeValues;
		}

		function fnGetJobTypeById($id)
		{
			$arrJobTypeValues = array();
			//$query = "SELECT * ,employee.id AS emp_id,designation.id as des_id, designation.title as des_title,departments.title as dep_title FROM `pms_job_types` AS employee INNER JOIN pms_departments AS departments ON employee.department =departments.id INNER JOIN pms_designation AS designation ON employee.designation =designation.id WHERE employee.id ='".mysql_real_escape_string($id)."'";
			$query = "SELECT * FROM `pms_job_types` WHERE `id` = '".mysql_real_escape_string($id)."' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrJobTypeValues[] = $this->fetchrow();
				}
			}
			return $arrJobTypeValues;
		}


		function fnUpdateJobType($arrPost)
		{
			$this->updateArray('pms_job_types',$arrPost);
		return true;
		}

		function fnDeleteJobType($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_job_types` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}

		function fnGetJobTypes($id)
		{
			$arrJobTypeValues = array();
			$query = "SELECT id as job_id,title as job_title FROM `pms_job_types`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrJobTypeValues[] = $this->fetchrow();
				}
			}
			return $arrJobTypeValues;
		}

	}
?>
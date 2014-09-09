<?php
include_once('db_mysql.php');
	class project extends DB_Sql
	{
		function __construct()
		{
		}
		function fnInsertProject($arrProject)
		{
			$this->insertArray('pms_project',$arrProject);
			return true;
		}
		function fnGetAllProject()
		{
			$arrProjectValues = array();
			//$query = "SELECT * ,project.id AS emp_id,designation.id as des_id, designation.title as des_title,departments.title as dep_title FROM `pms_project` AS project INNER JOIN pms_departments AS departments ON project.department =departments.id INNER JOIN pms_designation AS designation ON project.designation =designation.id ";
			$query = "SELECT * , project.id AS project_id,clients.id as client_id FROM `pms_project` AS project LEFT JOIN pms_clients AS clients ON project.client = clients.id";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrProjectValues[] = $this->fetchrow();
				}
			}
			return $arrProjectValues;
		}

		function fnGetProjectById($id)
		{
			$arrProjectValues = array();
			//$query = "SELECT * ,project.id AS emp_id,designation.id as des_id, designation.title as des_title,departments.title as dep_title FROM `pms_project` AS project INNER JOIN pms_departments AS departments ON project.department =departments.id INNER JOIN pms_designation AS designation ON project.designation =designation.id WHERE project.id ='".mysql_real_escape_string($id)."'";
			echo $query = "SELECT * , project.id AS project_id,clients.id as client_uid FROM `pms_project` AS project LEFT JOIN pms_clients AS clients ON project.client = clients.id WHERE project.id ='".mysql_real_escape_string($id)."'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrProjectValues[] = $this->fetchrow();
				}
			}
			return $arrProjectValues;
		}


		function fnUpdateProject($arrPost)
		{
			$this->updateArray('pms_project',$arrPost);
		return true;
		}

		function fnDeleteProject($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_project` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}
		function fnGetClients()
		{
			$arrClientValues = array();
			$query = "SELECT *,id as client_id FROM  `pms_clients`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrClientValues[] = $this->fetchrow();
				}
			}
			return $arrClientValues;
		}

	}
?>
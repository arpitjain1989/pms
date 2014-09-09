<?php
include_once('db_mysql.php');
	class clients extends DB_Sql
	{
		function __construct()
		{
		}
		function fnInsertClients($arrClients)
		{
			$this->insertArray('pms_clients',$arrClients);
			return true;
		}
		function fnGetAllClients()
		{
			$arrClientsValues = array();
			//$query = "SELECT * ,clients.id as clients_id,clients.title as clients_title,clients.description as clients_desc,jobtype.id AS job_id,projects.id as proj_id, jobtype.title as job_title,projects.title as proj_title FROM `pms_clients` AS clients INNER JOIN pms_job_types AS jobtype ON clients.job_type =jobtype.id INNER JOIN pms_project AS projects ON projects.id = clients.project ";
			$query = "SELECT * FROM `pms_clients`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrClientsValues[] = $this->fetchrow();
				}
			}
			return $arrClientsValues;
		}

		function fnGetClientsById($id)
		{
			$arrClientsValues = array();
			//$query = "SELECT * ,clients.id as clients_id,clients.title as clients_title,clients.description as clients_desc,jobtype.id AS job_id,projects.id as proj_id, jobtype.title as job_title,projects.title as proj_title FROM `pms_clients` AS clients INNER JOIN pms_job_types AS jobtype ON clients.job_type =jobtype.id INNER JOIN pms_project AS projects ON projects.id = clients.project WHERE clients.id ='".mysql_real_escape_string($id)."'";
			$query = "SELECT * FROM `pms_clients` WHERE `id` = '".mysql_real_escape_string($id)."' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrClientsValues[] = $this->fetchrow();
				}
			}
			return $arrClientsValues;
		}


		function fnUpdateClients($arrPost)
		{
			$this->updateArray('pms_clients',$arrPost);
		return true;
		}

		function fnDeleteClients($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_clients` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}

		function fnGetAllProjects()
		{
			$arrClientsValues = array();
			$query = "SELECT id as project_id,title as project_title FROM  `pms_project`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrClientsValues[] = $this->fetchrow();
				}
			}
			return $arrClientsValues;
		}

		function fnGetAllJobTypes()
		{
			$arrJobTypeValues = array();
			$query = "SELECT id as job_id,title as job_title FROM  `pms_job_types`";
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
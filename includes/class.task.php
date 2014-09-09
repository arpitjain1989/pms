<?php
include_once('db_mysql.php');
	class task extends DB_Sql
	{
		function __construct()
		{
		}
		function fnInsertTask($arrTask)
		{
		$startTime = $arrTask['startdatepicker'] .' '. $arrTask['starttime'].':00';
		$endTime = $arrTask['enddatepicker'] .' '. $arrTask['endtime'].':00';
		$arrRecords = array("title"=>$arrTask['title'],"description"=>$arrTask['description'],"project"=>$arrTask['project'],"job_type"=>$arrTask['job_type'],"team_leader"=>$arrTask['team_leader'],"start_date"=>$startTime,"end_date"=>$endTime,"overtime"=>$arrTask['overtime'],"rework"=>$arrTask['rework']);

			$this->insertArray('pms_task',$arrRecords);
			return true;
		}
		function fnGetAllTask()
		{
			$arrTaskValues = array();
			$query = "SELECT * ,task.id as task_id,task.title as task_title,task.description as task_desc,jobtype.id AS job_id,projects.id as proj_id, jobtype.title as job_title,projects.title as proj_title FROM `pms_task` AS task INNER JOIN pms_job_types AS jobtype ON task.job_type =jobtype.id INNER JOIN pms_project AS projects ON projects.id = task.project ";
			//$query = "SELECT * FROM `pms_task`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrTaskValues[] = $this->fetchrow();
				}
			}
			return $arrTaskValues;
		}

		function fnGetTaskById($id)
		{
			$arrTaskValues = array();
			$query = "SELECT * ,task.id as task_id,task.title as task_title,task.description as task_desc,jobtype.id AS job_id,projects.id as proj_id, jobtype.title as job_title,projects.title as proj_title,DATE_FORMAT(start_date, '%Y-%m-%d') as starting_date,DATE_FORMAT(start_date, '%H:%i') as starting_time, DATE_FORMAT(end_date, '%Y-%m-%d') as ending_date,DATE_FORMAT(end_date, '%H:%i') as ending_time FROM `pms_task` AS task INNER JOIN pms_job_types AS jobtype ON task.job_type =jobtype.id INNER JOIN pms_project AS projects ON projects.id = task.project WHERE task.id ='".mysql_real_escape_string($id)."'";
			//$query = "SELECT * FROM `pms_task` WHERE `id` = '".mysql_real_escape_string($id)."' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrTaskValues[] = $this->fetchrow();
				}
			}
			return $arrTaskValues;
		}


		function fnUpdateTask($arrTask)
		{
		//print_r($arrPost); die;
		$startTime = $arrTask['startdatepicker'] .' '. $arrTask['starttime'].':00';
		$endTime = $arrTask['enddatepicker'] .' '. $arrTask['endtime'].':00';
		$arrRecords = array("id"=>$arrTask['id'],"title"=>$arrTask['title'],"description"=>$arrTask['description'],"project"=>$arrTask['project'],"job_type"=>$arrTask['job_type'],"team_leader"=>$arrTask['team_leader'],"start_date"=>$startTime,"end_date"=>$endTime,"overtime"=>$arrTask['overtime'],"rework"=>$arrTask['rework'],"description"=>$arrTask['description'],"project"=>$arrTask['project'],"rework"=>$arrTask['job_type'],"team_leader"=>$arrTask['team_leader']);

		//print_r($arrRecords); die;
			$this->updateArray('pms_task',$arrRecords);
		return true;
		}

		function fnDeleteTask($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_task` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
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
			$arrTaskValues = array();
			$query = "SELECT id as project_id,title as project_title FROM  `pms_project`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrTaskValues[] = $this->fetchrow();
				}
			}
			return $arrTaskValues;
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
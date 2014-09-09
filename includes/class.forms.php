<?php
include_once('db_mysql.php');
	class forms extends DB_Sql
	{
		function __construct()
		{
		}
		function fnInsertFormField($arrForms)
		{
			$this->insertArray('pms_form_field',$arrForms);
			return true;
		}
		function fnGetAllForms()
		{
			$arrFormsValues = array();
			//$query = "SELECT * ,forms.id as forms_id,forms.title as forms_title,forms.description as forms_desc,jobtype.id AS job_id,projects.id as proj_id, jobtype.title as job_title,projects.title as proj_title FROM `pms_form_field` AS forms INNER JOIN pms_job_types AS jobtype ON forms.job_type =jobtype.id INNER JOIN pms_project AS projects ON projects.id = forms.project ";
			$query = "SELECT * FROM `pms_form_field`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrFormsValues[] = $this->fetchrow();
				}
			}
			return $arrFormsValues;
		}

		function fnGetFormFieldById($id)
		{
			$arrFormsValues = array();
			//$query = "SELECT * ,forms.id as forms_id,forms.title as forms_title,forms.description as forms_desc,jobtype.id AS job_id,projects.id as proj_id, jobtype.title as job_title,projects.title as proj_title FROM `pms_form_field` AS forms INNER JOIN pms_job_types AS jobtype ON forms.job_type =jobtype.id INNER JOIN pms_project AS projects ON projects.id = forms.project WHERE forms.id ='".mysql_real_escape_string($id)."'";
			$query = "SELECT * FROM `pms_form_field` WHERE `id` = '".mysql_real_escape_string($id)."'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrFormsValues[] = $this->fetchrow();
				}
			}
			return $arrFormsValues;
		}


		function fnUpdateForms($arrPost)
		{
			$this->updateArray('pms_form_field',$arrPost);
			return true;
		}

		function fnDeleteFormField($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_form_field` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
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
			$arrFormsValues = array();
			$query = "SELECT id as project_id,title as project_title FROM  `pms_project`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrFormsValues[] = $this->fetchrow();
				}
			}
			return $arrFormsValues;
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
		function fnFormFieldValues($id)
		{
			$arrFormFields = array();
			$query = "SELECT * , form_field.id AS fieldid FROM `pms_form_field` AS form_field LEFT JOIN `pms_project_fields` AS projects ON ( form_field.id = projects.formid AND projects.project_name = '$id' ) WHERE   projects.id IS NULL";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrFormFields[] = $this->fetchrow();
				}
			}
			return $arrFormFields;
		}
		function fnInsertForm($projectid,$arrPost)
		{
		foreach($arrPost as $arrayDatavalue)
			{
				$arrInsertForm = array("project_name"=>$projectid,"formid"=>$arrayDatavalue);
				$lastinsertid = $this->insertArray('pms_project_fields',$arrInsertForm);
			}
			return true;
		}
		function fnGetAllFormvalues($id)
		{
			$arrAllFormvalues = array();
			$query = "SELECT *,project_field.id as project_field_id,form_field.id as form_field_id FROM `pms_project_fields` as project_field INNER JOIN `pms_form_field` as form_field ON project_field.formid = form_field.id WHERE `project_field`.`project_name` = '".mysql_real_escape_string($id)."'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllFormvalues[] = $this->fetchrow();
				}
			}
			return $arrAllFormvalues;
		}
		function fnDeleteForm($arrData)
		{
			if(isset($arrData[chk]))
			{
				foreach($arrData[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_project_fields` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}
		function fnCheckExistanceUsingId($formid,$projectid)
		{

		}

}
?>
<?php
include_once('db_mysql.php');
	class departments extends DB_Sql
	{
		function __construct()
		{
		}
		function fnInsertDepartment($arrEmployee)
		{
			$arrNewRecords = array("title"=>$arrEmployee['title'],"description"=>$arrEmployee['description']);
			$this->insertArray('pms_departments',$arrNewRecords);
			return true;
		}
		function fnGetAllDepartments()
		{
			$arrDepartmentValues = array();
			$query = "SELECT * FROM `pms_departments`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrDepartmentValues[] = $this->fetchrow();
				}
			}
			return $arrDepartmentValues;
		}

		function fnGetDepartmentById($id)
		{
			$arrDepartmentValues = array();
			$query = "SELECT * FROM `pms_departments` WHERE `id` = '".mysql_real_escape_string($id)."' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrDepartmentValues[] = $this->fetchrow();
				}
			}
			return $arrDepartmentValues;
		}

		function fnGetDepartmentIdByName($title)
		{
			$DepartmentId = 0;
			$query = "SELECT id FROM `pms_departments` WHERE `title` = '".mysql_real_escape_string(trim($title))."'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$DepartmentId = $this->f("id");
				}
			}
			return $DepartmentId;
		}
		
		function fnGetDepartmentNameById($id)
		{
			$DepartmentName = 0;
			$query = "SELECT title FROM `pms_departments` WHERE `id` = '".mysql_real_escape_string(trim($id))."'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$DepartmentName= $this->f("title");
				}
			}
			return $DepartmentName;
		}

		function fnUpdateDepartments($arrPost)
		{
			$this->updateArray('pms_departments',$arrPost);
			return true;
		}

		function fnDeleteDepartment($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_departments` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}
	}
?>

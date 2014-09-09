<?php
	include_once('db_mysql.php');
	class graduation extends DB_Sql
	{
		function __construct()
		{
		}
		/* Insert graduation details */
		function fnSaveGraduation($arrGraduation)
		{
			if(isset($arrGraduation["id"]) && trim($arrGraduation["id"]) == "")
			{
					$this->insertArray("pms_graduation",$arrGraduation);
			}
			else
			{
					$this->updateArray("pms_graduation",$arrGraduation);
			}
			return true;
		}
		/* Select all graduation details using graduation title and graduation type */
		function fnValidateGraduation($title, $type, $id = 0)
		{
			$cond = "";
			if($id != 0)
				$cond = " and id!='$id'";

			$sSQL = "select * from pms_graduation where title='".mysql_real_escape_string($title)."' and type='".mysql_real_escape_string($type)."' $cond";
			$this->query($sSQL);
			if($this->num_rows() == 0)
				return true;
			else
				return false;
		}
		/* Get all Graduations details */
		function fnGetAllGraduation()
		{
			$arrGraduation = array();
			
			$sSQL = "select * from pms_graduation";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrGraduation[] = $this->fetchrow();
				}
			}
			
			return $arrGraduation;
		}
		/* Get graduation details by Id */
		function fnGetGraduationById($id)
		{
			$arrGraduation = array();
			$sSQL = "select * from pms_graduation where id='$id'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrGraduation = $this->fetchrow();
				}
			}

			return $arrGraduation;
		}
	}
?>

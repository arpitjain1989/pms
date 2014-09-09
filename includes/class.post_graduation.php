<?php
	include_once('db_mysql.php');
	class post_graduation extends DB_Sql
	{
		function __construct()
		{
		}
		/* Insert Post graduation data */
		function fnSavePostGraduation($arrPostGraduation)
		{
			if(isset($arrPostGraduation["id"]) && trim($arrPostGraduation["id"]) == "")
			{
					$this->insertArray("pms_post_graduation",$arrPostGraduation);
			}
			else
			{
					$this->updateArray("pms_post_graduation",$arrPostGraduation);
			}
			return true;
		}
		/* Get all post graduation data */
		function fnGetAllPostGraduation()
		{
			$arrPostGraduation = array();
			
			$sSQL = "select * from pms_post_graduation";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrPostGraduation[] = $this->fetchrow();
				}
			}
			
			return $arrPostGraduation;
		}
		/* Get Post graduation data by using id of the post graduation */
		function fnGetPostGraduationById($id)
		{
			$arrPostGraduation = array();
			$sSQL = "select * from pms_post_graduation where id='$id'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrPostGraduation = $this->fetchrow();
				}
			}

			return $arrPostGraduation;
		}
	}
?>

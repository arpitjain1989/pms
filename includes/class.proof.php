<?php
	include_once('db_mysql.php');
	class proof extends DB_Sql
	{
		function __construct()
		{
		}
		
		function fnSaveProof($arrProof)
		{
			if(isset($arrProof["id"]) && trim($arrProof["id"]) == "")
			{
				if($this->fnValidateProof($_POST["title"], $_POST["type"]))
					$this->insertArray("pms_proof",$arrProof);
				else
					return false;
			}
			else
			{
				if($this->fnValidateProof($_POST["title"], $_POST["type"], $_POST["id"]))
					$this->updateArray("pms_proof",$arrProof);
				else
					return false;
			}
			return true;
		}
		
		function fnValidateProof($title, $type, $id = 0)
		{
			$cond = "";
			if($id != 0)
				$cond = " and id!='$id'";

			$sSQL = "select * from pms_proof where title='".mysql_real_escape_string($title)."' and type='".mysql_real_escape_string($type)."' $cond";
			$this->query($sSQL);
			if($this->num_rows() == 0)
				return true;
			else
				return false;
		}
		
		function fnGetAllProof()
		{
			$arrProof = array();
			
			$sSQL = "select * from pms_proof";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrProof[] = $this->fetchrow();
				}
			}
			
			return $arrProof;
		}
		
		function fnGetProofById($id)
		{
			$arrProof = array();
			$sSQL = "select * from pms_proof where id='$id'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrProof = $this->fetchrow();
				}
			}

			return $arrProof;
		}
	}
?>

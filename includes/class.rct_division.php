<?php
include_once('db_mysql.php');
	class rct_division extends DB_Sql
	{
		function __construct()
		{
		}
		/* Insert rct division details */
		function fnInsertRCTDivision($arrEmployee)
		{
			$arrNewRecords = array("title"=>$arrEmployee['title'],"description"=>$arrEmployee['description']);
			$this->insertArray('pms_rct_division',$arrNewRecords);
			return true;
		}
		/* Get all rct divisions details */
		function fnGetAllRCTDivision()
		{
			$arrRCTDivisionValues = array();
			$query = "SELECT * FROM `pms_rct_division`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}
		/* Get rct division details by division id */
		function fnGetRCTDivisionById($id)
		{
			$arrRCTDivisionValues = array();
			$query = "SELECT * FROM `pms_rct_division` WHERE `id` = '".mysql_real_escape_string($id)."' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}
		/* Update rct division details */
		function fnUpdateRCTDivision($arrPost)
		{
			$this->updateArray('pms_rct_division',$arrPost);
			return true;
		}

		/* Delete rct division details using all id's that passed */
		function fnDeleteRCTDivision($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_rct_division` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}

		/* Get division id by name */
		function fnGetDivisionIdByName($name)
		{
			$id = '0';
			$sql = "select id from pms_rct_division where title = '$name'";
			$this->query($sql);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$id = $this->f('id');
				}
			}
			return $id;
		}

		/* Get division name by id */
		function fnGetDivisionNameById($id)
		{
			$name = '';
			$sql = "select title from pms_rct_division where id = '$id'";
			$this->query($sql);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$id = $this->f('title');
				}
			}
			return $id;
		}
	}
?>

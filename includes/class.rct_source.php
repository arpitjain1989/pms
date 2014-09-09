<?php
include_once('db_mysql.php');
	class rct_source extends DB_Sql
	{
		function __construct()
		{
		}
		/* Insert rct source */
		function fnInsertRCTSource($arrEmployee)
		{
			$arrNewRecords = array("title"=>$arrEmployee['title'],"description"=>$arrEmployee['description']);
			$this->insertArray('pms_rct_source',$arrNewRecords);
			return true;
		}
		/* Get all rct sources details */
		function fnGetAllRCTSource()
		{
			$arrRCTSourceValues = array();
			$query = "SELECT * FROM `pms_rct_source`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTSourceValues[] = $this->fetchrow();
				}
			}
			return $arrRCTSourceValues;
		}
		/* Get rct source details using rct source id */
		function fnGetRCTSourceById($id)
		{
			$arrRCTSourceValues = array();
			$query = "SELECT * FROM `pms_rct_source` WHERE `id` = '".mysql_real_escape_string($id)."' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTSourceValues[] = $this->fetchrow();
				}
			}
			return $arrRCTSourceValues;
		}
		/* update rct source details */
		function fnUpdateRCTSource($arrPost)
		{
			$this->updateArray('pms_rct_source',$arrPost);
			return true;
		}

		/* delete all rct source thet's checkbox cehecked and id provided */
		function fnDeleteRCTSource($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_rct_source` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}
		/* Get rct source id using rct source name */
		function fnGetSourceIdByName($name)
		{
			$id = '';
			$sql = "select id from pms_rct_source where title = '$name'";
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
	}
?>

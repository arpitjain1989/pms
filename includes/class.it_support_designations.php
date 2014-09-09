<?php

	include_once('db_mysql.php');

	class it_support_designations extends DB_Sql
	{
		function __construct()
		{
		}
		
		function fnSaveSupportDesignation($SupportDesignationInfo)
		{
			$support_designation_ids = "";
			if(isset($SupportDesignationInfo["support_designations"]) and count($SupportDesignationInfo["support_designations"]) > 0)
			{
				/* Mark existing support designations as deleted */
				$sSQL = "update pms_support_designations set isdeleted='1', deleted_datetime='".Date('Y-m-d H:i:s')."' where isdeleted='0'";
				$this->query($sSQL);
				
				foreach($SupportDesignationInfo["support_designations"] as $curSupportDesignations)
				{
					$arrInfo["support_designation_id"] = $curSupportDesignations;
					$arrInfo["addedon"] = Date('Y-m-d H:i:s');
					$arrInfo["isdeleted"] = 0;
					
					$this->insertArray("pms_support_designations",$arrInfo);
				}
				
				return true;
			}
			else
			{
				return false;
			}
		}

		function fnGetSupportDesignations()
		{
			$arrDesignation = array();

			$sSQL = "select support_designation_id from pms_support_designations where isdeleted='0'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$arrDesignation[] = $this->f("support_designation_id");
				}
			}

			return $arrDesignation;
		}
	}
?>


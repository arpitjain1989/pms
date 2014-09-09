<?php

	include_once('db_mysql.php');

	class inventory_make extends DB_Sql
	{
		function __construct()
		{
		}
		
		/*
		 * Save inventory make
		 * */
		function fnSaveInventoryMake($arrInventoryMake)
		{
			/* Check if the id already exists */
			if(isset($arrInventoryMake["id"]) && trim($arrInventoryMake["id"]) != "")
			{
				/* If the id already exists, check if the make is already added in any other record, then update */
				if($this->fnValidateInventoryMake($arrInventoryMake["make"], $arrInventoryMake["id"]))
					$this->updateArray("pms_inventory_make",$arrInventoryMake);
				else
					return false;				
			}
			else
			{
				/* If id does not exists, check if the make is already exists, if not insert the make */
				if($this->fnValidateInventoryMake($arrInventoryMake["make"]))
				{
					$arrInventoryMake["addedon"] = Date('Y-m-d H:i:s');
					$this->insertArray("pms_inventory_make",$arrInventoryMake);
				}
				else
					return false;

			}
			return true;
		}
		
		/* Check if the inventory make already exists */
		function fnValidateInventoryMake($inventory_make, $id = 0)
		{
			$cond = "";
			if($id != 0)
				$cond = " and id!='".mysql_real_escape_string($id)."'";

			$sSQL = "select * from pms_inventory_make where make='".mysql_real_escape_string($inventory_make)."' $cond";
			$this->query($sSQL);
			if($this->num_rows() == 0)
				return true;
			else
				return false;
		}
		
		/* Get all the inventory make */
		function fnGetAllInventoryMake()
		{
			$arrInventoryMake = array();
			
			$sSQL = "select * from pms_inventory_make";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrInventoryMake[] = $this->fetchrow();
				}
			}
			
			return $arrInventoryMake;
		}
		
		/* Get all the inventory make by id */
		function fnGetInventoryMakeById($id)
		{
			$arrInventoryMake = array();
			$sSQL = "select * from pms_inventory_make where id='".mysql_real_escape_string($id)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrInventoryMake = $this->fetchrow();
				}
			}

			return $arrInventoryMake;
		}
		
		function fnGetInventoryMakeIdByInventoryMake($InventoryMake)
		{
			$InventoryMakeId = 0;
			
			$sSQL = "select id from pms_inventory_make where make='".mysql_real_escape_string($InventoryMake)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$InventoryMakeId = $this->f("id");
				}
			}
			
			return $InventoryMakeId;
		}
	}
?>

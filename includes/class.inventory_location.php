<?php

	include_once('db_mysql.php');

	class inventory_location extends DB_Sql
	{
		function __construct()
		{
		}

		/*
		 * Save inventory location
		 * */
		function fnSaveInventoryLocation($arrInventoryLocation)
		{
			if(!isset($arrInventoryLocation["hide_location"]))
				$arrInventoryLocation["hide_location"] = 0;

			/* Check if the id already exists */
			if(isset($arrInventoryLocation["id"]) && trim($arrInventoryLocation["id"]) != "")
			{
				/* If the id already exists, check if the inventory location is already added in any other record, then update */
				if($this->fnValidateInventoryLocation($arrInventoryLocation["location_name"], $arrInventoryLocation["id"]))
					$this->updateArray("pms_inventory_location",$arrInventoryLocation);
				else
					return false;
			}
			else
			{
				/* If id does not exists, check if the inventory location is already exists, if not insert the inventory location */
				if($this->fnValidateInventoryLocation($arrInventoryLocation["location_name"]))
				{
					$arrInventoryLocation["addedon"] = Date('Y-m-d H:i:s');
					$this->insertArray("pms_inventory_location",$arrInventoryLocation);
				}
				else
					return false;
			}
			return true;
		}

		/* Check if the inventory location already exists */
		function fnValidateInventoryLocation($inventory_location, $id = 0)
		{
			$cond = "";
			if($id != 0)
				$cond = " and id!='".mysql_real_escape_string($id)."'";

			$sSQL = "select * from pms_inventory_location where location_name='".mysql_real_escape_string($inventory_location)."' $cond";
			$this->query($sSQL);
			if($this->num_rows() == 0)
				return true;
			else
				return false;
		}

		/* Get all the inventory location */
		function fnGetAllInventoryLocation()
		{
			$arrInventoryLocation = array();

			$sSQL = "select *, if(hide_location=1,'Yes','No') as hidden_status from pms_inventory_location order by location_name";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrInventoryLocation[] = $this->fetchrow();
				}
			}

			return $arrInventoryLocation;
		}
		
		/* Get all the inventory location */
		function fnGetAllVisibleLocations()
		{
			$arrInventoryLocation = array();

			$sSQL = "select * from pms_inventory_location where hide_location=0 order by location_name";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrInventoryLocation[] = $this->fetchrow();
				}
			}

			return $arrInventoryLocation;
		}
		
		/* Get all the inventory location by id */
		function fnGetInventoryLocationById($id)
		{
			$arrInventoryLocation = array();
			$sSQL = "select *, if(hide_location=1,'Yes','No') as hidden_status from pms_inventory_location where id='".mysql_real_escape_string($id)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrInventoryLocation = $this->fetchrow();
				}
			}

			return $arrInventoryLocation;
		}
		
		function fnGetLocationNameById($LocationId)
		{
			$LocationName = "";
			
			$sSQL = "select location_name from pms_inventory_location where id='".mysql_real_escape_string($LocationId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$LocationName = $this->f("location_name");
				}
			}

			return $LocationName;

		}
		
		function fnGetInventoryLocationIdByInventoryLocation($InventoryLocation)
		{
			$InventoryLocationId = 0;
			
			$sSQL = "select id from pms_inventory_location where location_name='".mysql_real_escape_string($InventoryLocation)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$InventoryLocationId = $this->f("id");
				}
			}
			return $InventoryLocationId;
		}
	}

?>

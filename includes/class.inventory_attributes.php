<?php

	include_once('db_mysql.php');

	class inventory_attributes extends DB_Sql
	{
		function __construct()
		{
		}
		
		/*
		 * Save inventory attributes
		 * */
		function fnSaveInventoryAttributes($arrInventoryAttributes)
		{
			/* Check if the id already exists */
			if(isset($arrInventoryAttributes["id"]) && trim($arrInventoryAttributes["id"]) != "")
			{
				/* If the id already exists, check if the attribute is already added in any other record, then update */
				if($this->fnValidateInventoryAttributes($arrInventoryAttributes["attribute_name"], $arrInventoryAttributes["parentid"], $arrInventoryAttributes["id"]))
					$this->updateArray("pms_inventory_attributes",$arrInventoryAttributes);
				else
					return false;
			}
			else
			{
				/* If id does not exists, check if the attribute is already exists, if not insert the attribute */
				if($this->fnValidateInventoryAttributes($arrInventoryAttributes["attribute_name"], $arrInventoryAttributes["parentid"]))
				{
					$arrInventoryAttributes["addedon"] = Date('Y-m-d H:i:s');
					$this->insertArray("pms_inventory_attributes",$arrInventoryAttributes);
				}
				else
					return false;

			}
			return true;
		}
		
		/* Check if the inventory attribute already exists */
		function fnValidateInventoryAttributes($inventory_attribute, $parent_id, $id = 0)
		{
			$cond = "";
			if($id != 0)
				$cond = " and id!='".mysql_real_escape_string($id)."'";

			$sSQL = "select * from pms_inventory_attributes where attribute_name='".mysql_real_escape_string($inventory_attribute)."' and parentid='".mysql_real_escape_string($parent_id)."' $cond";
			$this->query($sSQL);
			if($this->num_rows() == 0)
				return true;
			else
				return false;
		}
		
		/* Get all the inventory attribute */
		function fnGetAllInventoryAttributes()
		{
			$arrInventoryAttributes = array();
			
			$sSQL = "select a.*, a1.attribute_name as parent_name from pms_inventory_attributes a LEFT JOIN pms_inventory_attributes a1 ON a.parentid = a1.id";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrInventoryAttributes[] = $this->fetchrow();
				}
			}
			
			return $arrInventoryAttributes;
		}
		
		/* Get all the inventory attribute by id */
		function fnGetInventoryAttributesById($id)
		{
			$arrInventoryAttributes = array();
			$sSQL = "select a.*, a1.attribute_name as parent_name from pms_inventory_attributes a LEFT JOIN pms_inventory_attributes a1 ON a.parentid = a1.id where a.id='".mysql_real_escape_string($id)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrInventoryAttributes = $this->fetchrow();
				}
			}

			return $arrInventoryAttributes;
		}

		/* Fetch main attributes */
		function fnMainAttributes()
		{
			$arrMainAttributes = array();

			$sSQL = "select * from pms_inventory_attributes where parentid='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrMainAttributes[] = $this->fetchrow();
				}
			}

			return $arrMainAttributes;
		}
		
		/* Get sub attributes by parent attribute */
		function fnGetSubAttributesByParentAttribute($id)
		{
			$arrInventoryAttributes = array();
			$sSQL = "select a.*, a1.attribute_name as parent_name from pms_inventory_attributes a LEFT JOIN pms_inventory_attributes a1 ON a.parentid = a1.id where a.parentid='".mysql_real_escape_string($id)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrInventoryAttributes[] = $this->fetchrow();
				}
			}

			return $arrInventoryAttributes;
		}
	}
?>

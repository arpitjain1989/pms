<?php

	include_once('db_mysql.php');

	class inventory_type extends DB_Sql
	{
		function __construct()
		{
		}
		
		/*
		 * Save inventory type
		 * */
		function fnSaveInventoryType($arrInventoryType)
		{
			/* Check if the id already exists */
			if(isset($arrInventoryType["id"]) && trim($arrInventoryType["id"]) != "")
			{
				/* If the id already exists, check if the type is already added in any other record, then update */
				if($this->fnValidateInventoryType($arrInventoryType["type"], $arrInventoryType["id"]))
				{
					$this->updateArray("pms_inventory_type",$arrInventoryType);
					$this->fnSaveInventoryTypeAttributes($arrInventoryType["id"], $arrInventoryType["inventoryattributes"]);
				}
				else
					return false;				
			}
			else
			{
				/* If id does not exists, check if the type is already exists, if not insert the type */
				if($this->fnValidateInventoryType($arrInventoryType["type"]))
				{
					$arrInventoryType["addedon"] = Date('Y-m-d H:i:s');
					$id = $this->insertArray("pms_inventory_type",$arrInventoryType);
					$this->fnSaveInventoryTypeAttributes($id, $arrInventoryType["inventoryattributes"]);
				}
				else
					return false;

			}
			return true;
		}
		
		/* Check if the inventory type already exists */
		function fnValidateInventoryType($inventory_type, $id = 0)
		{
			$cond = "";
			if($id != 0)
				$cond = " and id!='".mysql_real_escape_string($id)."'";

			$sSQL = "select * from pms_inventory_type where type='".mysql_real_escape_string($inventory_type)."' $cond";
			$this->query($sSQL);
			if($this->num_rows() == 0)
				return true;
			else
				return false;
		}
		
		/* Get all the inventory types */
		function fnGetAllInventoryType()
		{
			$arrInventoryType = array();
			
			$sSQL = "select * from pms_inventory_type";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrInventoryType[] = $this->fetchrow();
				}
			}
			
			return $arrInventoryType;
		}
		
		/* Get all the inventory types by id */
		function fnGetInventoryTypeById($id)
		{
			$arrInventoryType = array();
			$sSQL = "select * from pms_inventory_type where id='".mysql_real_escape_string($id)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrInventoryType = $this->fetchrow();
				}
			}

			return $arrInventoryType;
		}
		
		/* Saves inventory type attribute */
		function fnSaveInventoryTypeAttributes($InventoryTypeId, $InventoryAttributes)
		{
			/* Delete previously set values */
			$sSQL = "delete from pms_inventory_type_detail where inventory_type_id='".mysql_real_escape_string($InventoryTypeId)."'";
			$this->query($sSQL);
			
			/* Insert all the attributes */
			if(is_array($InventoryAttributes) && count($InventoryAttributes) > 0)
			{
				foreach($InventoryAttributes as $curAttribute)
				{
					$arrAttribute["inventory_type_id"] = $InventoryTypeId;
					$arrAttribute["inventory_attribute_id"] = $curAttribute;
					
					$this->insertArray("pms_inventory_type_detail",$arrAttribute);
				}
			}
		}
		
		function fnGetInventoryAttributeIdByType($InventoryTypeId)
		{
			$arrAttributes = array();
			$sSQL = "select inventory_attribute_id from pms_inventory_type_detail where inventory_type_id='".mysql_real_escape_string($InventoryTypeId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAttributes[] = $this->f("inventory_attribute_id");
				}
			}
			
			return $arrAttributes;
		}
		
		function fnGetInventoryAttributeByType($InventoryTypeId)
		{
			$arrAttributes = array();
			$sSQL = "select td.inventory_attribute_id as id, attribute_name from pms_inventory_type_detail td INNER JOIN pms_inventory_attributes a ON a.id = td.inventory_attribute_id where td.inventory_type_id='".mysql_real_escape_string($InventoryTypeId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAttributes[] = $this->fetchRow();
				}
			}
			
			return $arrAttributes;
		}
		
		function fnGetInventoryTypeIdByInventoryType($InventoryType)
		{
			$InventoryTypeId = 0;

			$sSQL = "select id from pms_inventory_type where type='".mysql_real_escape_string($InventoryType)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$InventoryTypeId = $this->f("id");
				}
			}
			
			return $InventoryTypeId;
		}
	}
?>

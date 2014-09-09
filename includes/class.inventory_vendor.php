<?php

	include_once('db_mysql.php');

	class inventory_vendor extends DB_Sql
	{
		function __construct()
		{
		}
		
		/*
		 * Save vendor
		 * */
		function fnSaveInventoryVendor($arrInventoryVendor)
		{
			/* Check if the id already exists */
			if(isset($arrInventoryVendor["id"]) && trim($arrInventoryVendor["id"]) != "")
			{
				/* If the id already exists, check if the vendor is already added in any other record, then update */
				if($this->fnValidateInventoryVendor($arrInventoryVendor["vendor_name"], $arrInventoryVendor["id"]))
				{
					$this->updateArray("pms_inventory_vendor",$arrInventoryVendor);
					
					/* Mark previous entries as deleted */
					$sSQL = "update pms_inventory_vendor_contact set isdeleted='1', deleteddatetime='".Date('Y-m-d H:i:s')."' where vendor_id='".mysql_real_escape_string($arrInventoryVendor["id"])."'";
					$this->query($sSQL);
					
					/* Insert newly saved entries */
					if(isset($arrInventoryVendor["contact_person"]) && isset($arrInventoryVendor["contactno"]) && isset($arrInventoryVendor["address"]))
					{
						foreach($arrInventoryVendor["contact_person"] as $k => $v)
						{
							$arrVendorContact["vendor_id"] = $arrInventoryVendor["id"];
							$arrVendorContact["contact_person"] = $v;
							$arrVendorContact["contact_no"] = $arrInventoryVendor["contactno"][$k];
							$arrVendorContact["address"] = $arrInventoryVendor["address"][$k];
							$arrVendorContact["addedon"] = Date('Y-m-d H:i:s');
							$arrVendorContact["isdeleted"] = 0;
							
							$this->insertArray("pms_inventory_vendor_contact",$arrVendorContact);
						}
					}
				}
				else
					return false;				
			}
			else
			{
				/* If id does not exists, check if the vendor is already exists, if not insert the vendor */
				if($this->fnValidateInventoryVendor($arrInventoryVendor["vendor_name"]))
				{
					$arrInventoryVendor["addedon"] = Date('Y-m-d H:i:s');
					$id = $this->insertArray("pms_inventory_vendor",$arrInventoryVendor);

					/* Insert vendor contact information */
					if(isset($arrInventoryVendor["contact_person"]) && isset($arrInventoryVendor["contactno"]) && isset($arrInventoryVendor["address"]))
					{
						foreach($arrInventoryVendor["contact_person"] as $k => $v)
						{
							$arrVendorContact["vendor_id"] = $id;
							$arrVendorContact["contact_person"] = $v;
							$arrVendorContact["contact_no"] = $arrInventoryVendor["contactno"][$k];
							$arrVendorContact["address"] = $arrInventoryVendor["address"][$k];
							$arrVendorContact["addedon"] = Date('Y-m-d H:i:s');
							$arrVendorContact["isdeleted"] = 0;
							
							$this->insertArray("pms_inventory_vendor_contact",$arrVendorContact);
						}
					}
				}
				else
					return false;

			}
			return true;
		}
		
		/* Check if the vendor already exists */
		function fnValidateInventoryVendor($VendorName, $id = 0)
		{
			$cond = "";
			if($id != 0)
				$cond = " and id!='".mysql_real_escape_string($id)."'";

			$sSQL = "select * from pms_inventory_vendor where vendor_name='".mysql_real_escape_string($VendorName)."' $cond";
			$this->query($sSQL);
			if($this->num_rows() == 0)
				return true;
			else
				return false;
		}
		
		/* Get all the vendor */
		function fnGetAllInventoryVendor()
		{
			$arrInventoryVendor = array();
			$db = new DB_Sql();
			
			$sSQL = "select * from pms_inventory_vendor";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$tempInformation = $this->fetchrow();
					
					$tempInformation["contact_information"] = array();
					
					$sSQL = "select * from pms_inventory_vendor_contact where vendor_id='".mysql_real_escape_string($tempInformation["id"])."' and isdeleted='0'";
					$db->query($sSQL);
					if($db->num_rows() > 0)
					{
						while($db->next_record())
						{
							$tempInformation["contact_information"][] = $db->fetchRow();
						}
					}
					
					$arrInventoryVendor[] = $tempInformation;
				}
			}
			
			return $arrInventoryVendor;
		}
		
		/* Get all the vendor by id */
		function fnGetInventoryVendorById($id)
		{
			$arrInventoryVendor = array();
			$sSQL = "select * from pms_inventory_vendor where id='".mysql_real_escape_string($id)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrInventoryVendor = $this->fetchrow();
					$arrInventoryVendor["contact_information"] = array();
					
					$sSQL = "select * from pms_inventory_vendor_contact where vendor_id='".mysql_real_escape_string($id)."' and isdeleted='0'";
					$this->query($sSQL);
					if($this->num_rows() > 0)
					{
						while($this->next_record())
						{
							$arrInventoryVendor["contact_information"][] = $this->fetchRow();
						}
					}
				}
			}

			return $arrInventoryVendor;
		}
		
		function fnGetInventoryVendorIdByInventoryVendor($InventoryVendor)
		{
			$InventoryVendorId = 0;
			
			$sSQL = "select id from pms_inventory_vendor where vendor_name='".mysql_real_escape_string($InventoryVendor)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$InventoryVendorId = $this->f("id");
				}
			}
			
			return $InventoryVendorId;
		}
	}
?>

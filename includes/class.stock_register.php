<?php

	include_once('db_mysql.php');

	class stock_register extends DB_Sql
	{
		function __construct()
		{
		}

		/*
		 * Save inventory location
		 * */
		function fnSaveStockRegister($arrStockRegister)
		{
			/* Check if purchase date and warranty added. Then calculate  */
			if(isset($arrStockRegister["purchasedate"]) && trim($arrStockRegister["purchasedate"]) != "")
			{
				$warrenty_expiry = $arrStockRegister["purchasedate"];
				if(isset($arrStockRegister["warranty_year"]) && trim($arrStockRegister["warranty_year"]) != "")
				{
					$warrenty_expiry = date('Y-m-d', strtotime('+'.$arrStockRegister["warranty_year"].' year', strtotime($warrenty_expiry)));
					$arrStockRegister["warrenty_expiry"] = $warrenty_expiry;
				}
				
				if(isset($arrStockRegister["warranty_month"]) && trim($arrStockRegister["warranty_month"]) != "")
				{
					$warrenty_expiry = date('Y-m-d', strtotime('+'.$arrStockRegister["warranty_month"].' month', strtotime($warrenty_expiry)));
					$arrStockRegister["warrenty_expiry"] = $warrenty_expiry;
				}
			}
			
			if(!isset($arrStockRegister["isreturnable"]))
				$arrStockRegister["isreturnable"] = 0;
			
			$returnFlag = true;
			
			/* Check if the id already exists */
			if(isset($arrStockRegister["id"]) && trim($arrStockRegister["id"]) != "")
			{
				/* If the id already exists, check if the stock entry is already added in any other record, then update */
				
				$previousStatus = $this->fnGetCurrentStockStatusById($StockId);
				
				if($this->fnValidateStockRegister($arrStockRegister["uniqueid"], $arrStockRegister["id"]))
				{
					$this->updateArray("pms_stock_register",$arrStockRegister);
					$this->fnDeleteStockArrtibutes($arrStockRegister["id"]);
					if(isset($arrStockRegister["inventory_type_attributes"]) && count($arrStockRegister["inventory_type_attributes"]) > 0)
					{
						$this->fnSaveStockAttribute($arrStockRegister["inventory_type_attributes"], $arrStockRegister["id"]);
					}
					
					/* Insert in log */
					$this->fnStockRegisterLog($arrStockRegister["id"]);
					
					if($previousStatus != $arrStockRegister["status"] && $arrStockRegister["status"] == "3")
					{
						/* Log for gate pass */
						
						$arrGatePassInfo["stock_register_id"] = $arrStockRegister["id"];
						$arrGatePassInfo["ticket_id"] = 0;
						$arrGatePassInfo["vendor_id"] = $arrStockRegister["sentto_vendor_id"];
						$arrGatePassInfo["expected_return_date"] = $arrStockRegister["expected_return_date"];
						$arrGatePassInfo["isdeleted"] = 0;
						$arrGatePassInfo["addedon"] = Date('Y-m-d H:i:s');

						$this->fnGenerateGatePass($arrGatePassInfo);

						$returnFlag = $arrStockRegister["id"];
					}
				}
				else
					return false;				
			}
			else
			{
				/* If id does not exists, check if the stock entry is already exists, if not insert the stock entry */
				if($this->fnValidateStockRegister($arrStockRegister["uniqueid"]))
				{
					$arrStockRegister["addedon"] = Date('Y-m-d H:i:s');
					$StockId = $this->insertArray("pms_stock_register",$arrStockRegister);
					$this->fnDeleteStockArrtibutes($StockId);
					if(isset($arrStockRegister["inventory_type_attributes"]) && count($arrStockRegister["inventory_type_attributes"]) > 0)
					{
						$this->fnSaveStockAttribute($arrStockRegister["inventory_type_attributes"], $StockId);
					}
					
					/* Insert in log */
					$this->fnStockRegisterLog($StockId);
					
					if($previousStatus != $arrStockRegister["status"] && $arrStockRegister["status"] == "3")
					{
						/* Log for gate pass */
						
						$arrGatePassInfo["stock_register_id"] = $StockId;
						$arrGatePassInfo["ticket_id"] = 0;
						$arrGatePassInfo["vendor_id"] = $arrStockRegister["sentto_vendor_id"];
						$arrGatePassInfo["expected_return_date"] = $arrStockRegister["expected_return_date"];
						$arrGatePassInfo["isdeleted"] = 0;
						$arrGatePassInfo["addedon"] = Date('Y-m-d H:i:s');
						
						$this->fnGenerateGatePass($arrGatePassInfo);
						
						$returnFlag = $StockId;
					}
				}
				else
					return false;
			}
			return $returnFlag;
		}
		
		function fnGenerateGatePass($GatePassInfo)
		{
			if(isset($GatePassInfo["stock_register_id"]) && trim($GatePassInfo["stock_register_id"]) != '')
			{
				$curDate = Date('Y-m-d H:i:s');
				
				/* Update all the previous records, of the gatepass printing */
				$sSQL = "update pms_stock_gatepass_log set isdeleted='1', deleted_datetime='".mysql_real_escape_string($curDate)."' where stock_register_id='".mysql_real_escape_string($GatePassInfo["stock_register_id"])."' and isdeleted='0'";
				$this->query($sSQL);
				
				/* Add a new record for gatepass printing */
				$this->insertArray("pms_stock_gatepass_log",$GatePassInfo);

				return true;
			}
			else
				return false;
		}
		
		function fnGetGatePassInformationByStockId($StockId)
		{
			$arrGatepassInformation = array();
			
			$sSQL = "select gp.id as gatepassid, date_format(gp.expected_return_date, '%d-%m-%Y') as return_date, vendor_name, sr.uniqueid, sr.serialno, sr.isreturnable, t.type from pms_stock_gatepass_log gp LEFT JOIN pms_inventory_vendor v ON v.id = gp.vendor_id LEFT JOIN pms_stock_register sr ON sr.id = gp.stock_register_id LEFT JOIN pms_inventory_type t ON t.id = sr.inventory_type_id where gp.stock_register_id='".mysql_real_escape_string($StockId)."' and gp.isdeleted='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrGatepassInformation = $this->fetchRow();
				}
			}
			
			return $arrGatepassInformation;
		}
		
		/* Get current ticket status */
		function fnGetCurrentStockStatusById($StockId)
		{
			$status = 0;
			$sSQL = "select status from pms_stock_register where id='".mysql_real_escape_string($StockId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$status = $this->f("status");
				}
			}
			
			return $status;
		}
		
		/* Delete all the attributes for stock entry */
		function fnDeleteStockArrtibutes($StockId)
		{
			$sSQL = "delete from pms_stock_register_attributes where stock_id='".mysql_real_escape_string($StockId)."'";
			$this->query($sSQL);
		}

		function fnSaveStockAttribute($arrAttributes, $StockId)
		{
			if(count($arrAttributes) > 0)
			{
				foreach($arrAttributes as $curAttributeKey => $curAttributeValue)
				{
					$arrSave["stock_id"] = $StockId;
					$arrSave["attribute_id"] = $curAttributeKey;
					$arrSave["attribute_value_id"] = $curAttributeValue;
					
					$this->insertArray("pms_stock_register_attributes",$arrSave);
				}
			}
		}

		/* Check if the inventory location already exists */
		function fnValidateStockRegister($inventory_location, $id = 0)
		{
			$cond = "";
			if($id != 0)
				$cond = " and id!='".mysql_real_escape_string($id)."'";

			$sSQL = "select * from pms_stock_register where uniqueid='".mysql_real_escape_string($inventory_location)."' and (isdisposed='0' or isdisposed is null) $cond";
			$this->query($sSQL);
			if($this->num_rows() == 0)
				return true;
			else
				return false;
		}

		/* Get all the stock register */
		function fnGetAllStockRegister($type_id = 0, $status = '')
		{
			$arrStockRegister = array();
			$arrStatus = array("0"=>"Stock","1"=>"In use","2"=>"Scrap","3"=>"Repair");
			$arrWarrantyStatus = array("0"=>"No","1"=>"Yes");

			$cond = " where 1=1 and (sr.isdisposed='0' or sr.isdisposed is null)";
			if(trim($type_id) !='' && trim($type_id) != 0)
				$cond .= " and t.id='".mysql_real_escape_string(trim($type_id))."'";

			if(trim($status) !='')
				$cond .= " and sr.status='".mysql_real_escape_string(trim($status))."'";

			$sSQL = "select sr.*, l.location_name, t.type, t.description as inventory_description, date_format(purchasedate,'%d-%m-%Y') as purchasedate, date_format(warrenty_expiry,'%d-%m-%Y') as warrenty_expiry, if(warrenty_expiry!='' and warrenty_expiry >= now(),1,0) as warranty_status,m.make from pms_stock_register as sr LEFT JOIN pms_inventory_location l ON sr.location_id = l.id LEFT JOIN pms_inventory_type t ON sr.inventory_type_id = t.id LEFT JOIN pms_inventory_make m ON sr.inventory_make_id = m.id".$cond;
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$tmpStockRegister = $this->fetchrow();
					
					/* Get text for the status */
					$tmpStockRegister["status_text"] = "";
					if(isset($arrStatus[$this->f("status")]))
						$tmpStockRegister["status_text"] = $arrStatus[$this->f("status")];
					
					/* Get display text for warranty */
					$warranty = "";
					if($tmpStockRegister["warranty_year"] != "" && $tmpStockRegister["warranty_year"] != "0")
						$warranty .= $tmpStockRegister["warranty_year"]." year(s) ";
					
					if($tmpStockRegister["warranty_month"] != "" && $tmpStockRegister["warranty_month"] != "0")
						$warranty .= $tmpStockRegister["warranty_month"]." month(s) ";
						
					$tmpStockRegister["warranty_text"] = $warranty;
					
					/* Get text for warranty status */
					$tmpStockRegister["warranty_status_text"] = $arrWarrantyStatus[$tmpStockRegister["warranty_status"]];
					
					/* Get attributes text */
					$attributes_text = $comma = "";
					$arrAttributes = $this->fnGetSelectedStockAttributesDetail($tmpStockRegister["id"]);
					//print_r($arrAttributes);
					if(count($arrAttributes) > 0)
					{
						foreach($arrAttributes as $curAttribute)
						{
							$attributes_text .= $comma . $curAttribute["attribute_id_name"]." : ".$curAttribute["attribute_value_id_name"];
							$comma = ", ";
						}
					}
					
					$tmpStockRegister["attribute_text"] = $attributes_text;
					
					$arrStockRegister[] = $tmpStockRegister;
				}
			}
			return $arrStockRegister;
		}
		
		/* Get all the stock register by id */
		function fnGetStockRegisterById($id)
		{
			$arrStockRegister = array();
			$sSQL = "select sr.*, l.location_name, t.type, t.description as inventory_description, date_format(purchasedate,'%Y-%m-%d') as purchasedate, date_format(warrenty_expiry,'%Y-%m-%d') as warrenty_expiry, date_format(expected_return_date,'%Y-%m-%d') as expected_return_date, if(warrenty_expiry!='' and warrenty_expiry >= now(),1,0) as warranty_status, m.make, v.vendor_name, if(sentto_vendor_id=0,'Inhouse',v1.vendor_name) as sentto_vendor from pms_stock_register as sr LEFT JOIN pms_inventory_location l ON sr.location_id = l.id LEFT JOIN pms_inventory_type t ON sr.inventory_type_id = t.id LEFT JOIN pms_inventory_make m ON sr.inventory_make_id = m.id LEFT JOIN pms_inventory_vendor v ON sr.vendor_id = v.id LEFT JOIN pms_inventory_vendor v1 ON sr.sentto_vendor_id = v1.id where sr.id='".mysql_real_escape_string($id)."' and (sr.isdisposed='0' or sr.isdisposed is null)";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrStatus = array("0"=>"Stock","1"=>"In use","2"=>"Scrap","3"=>"Repair");
					$arrWarrantyStatus = array("0"=>"No","1"=>"Yes");

					$arrStockRegister = $this->fetchrow();
					
					/* Get text for the status */
					$arrStockRegister["status_text"] = "";
					if(isset($arrStatus[$this->f("status")]))
						$arrStockRegister["status_text"] = $arrStatus[$this->f("status")];
						
					/* Get display text for warranty */
					$warranty = "";
					if($arrStockRegister["warranty_year"] != "" && $arrStockRegister["warranty_year"] != "0")
						$warranty .= $arrStockRegister["warranty_year"]." year(s) ";
					
					if($arrStockRegister["warranty_month"] != "" && $arrStockRegister["warranty_month"] != "0")
						$warranty .= $arrStockRegister["warranty_month"]." month(s) ";
						
					$arrStockRegister["warranty_text"] = $warranty;
					
					/* Get text for warranty status */
					$arrStockRegister["warranty_status_text"] = $arrWarrantyStatus[$arrStockRegister["warranty_status"]];
				}
			}

			return $arrStockRegister;
		}
		
		function fnGetSelectedStockAttributes($stock_id)
		{
			$arrSelectedAttributes = array();
			
			$sSQL = "select attribute_id, attribute_value_id from pms_stock_register_attributes where stock_id='".mysql_real_escape_string($stock_id)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrSelectedAttributes[$this->f("attribute_id")] = $this->f("attribute_value_id");
				}
			}
			
			return $arrSelectedAttributes;
		}
		
		function fnGetSelectedStockAttributesDetail($stock_id)
		{
			$arrSelectedAttributes = array();
			$db = new DB_Sql();			
			$sSQL = "select sa.attribute_id, sa.attribute_value_id, a1.attribute_name as attribute_id_name, a2.attribute_name as attribute_value_id_name from pms_stock_register_attributes sa LEFT JOIN pms_inventory_attributes a1 ON sa.attribute_id = a1.id LEFT JOIN pms_inventory_attributes a2 ON sa.attribute_value_id = a2.id where sa.stock_id='".mysql_real_escape_string($stock_id)."'";
			$db->query($sSQL);
			if($db->num_rows() > 0)
			{
				while($db->next_record())
				{
					$arrSelectedAttributes[] = array("attribute_id"=>$db->f("attribute_id"), "attribute_id_name"=>$db->f("attribute_id_name"), "attribute_value_id"=>$db->f("attribute_value_id"), "attribute_value_id_name"=>$db->f("attribute_value_id_name"));
				}
			}
			
			return $arrSelectedAttributes;
		}
		
		function fnGetInventoryInRepair($InventoryTypeId, $InventoryMakeId, $InventoryVendorId, $InventoryExpectedReturnFrom, $InventoryExpectedReturnTo)
		{
			$arrRepairInventory =  array();
			
			$cond = "";
			if($InventoryTypeId != '' && $InventoryTypeId != 0)
				$cond .= " and sr.inventory_type_id='".mysql_real_escape_string($InventoryTypeId)."'";

			if($InventoryMakeId != '' && $InventoryMakeId != 0)
				$cond .= " and sr.inventory_make_id='".mysql_real_escape_string($InventoryMakeId)."'";

			if($InventoryVendorId != '')
				$cond .= " and sr.sentto_vendor_id='".mysql_real_escape_string($InventoryVendorId)."'";

			if($InventoryExpectedReturnFrom != "" && $InventoryExpectedReturnTo != "")
			{
				$cond .= " and date_format(sr.expected_return_date,'%Y-%m-%d') between '".mysql_real_escape_string($InventoryExpectedReturnFrom)."' and '".mysql_real_escape_string($InventoryExpectedReturnTo)."'";
			}

			/* status 3 for repair */
			$sSQL = "select t.type, m.make, sr.serialno, sr.uniqueid, l.location_name, date_format(purchasedate,'%d-%m-%Y') as purchasedate, date_format(expected_return_date,'%d-%m-%Y') as expected_return_date, date_format(warrenty_expiry,'%d-%m-%Y') as warrenty_expiry, v.vendor_name, if(sentto_vendor_id=0,'Inhouse',v1.vendor_name) as senttovendor from pms_stock_register sr LEFT JOIN pms_inventory_type t ON sr.inventory_type_id = t.id LEFT JOIN pms_inventory_make m ON sr.inventory_make_id = m.id LEFT JOIN pms_inventory_vendor v ON sr.vendor_id = v.id LEFT JOIN pms_inventory_location l ON sr.location_id = l.id LEFT JOIN pms_inventory_vendor v1 ON sr.sentto_vendor_id = v1.id where sr.status='3' and (sr.isdisposed='0' or sr.isdisposed is null) ".$cond;
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRepairInventory[] = $this->fetchrow();
				}
			}
			
			return $arrRepairInventory;
		}
		
		function fnGetPurchaseInventory($InventoryTypeId, $InventoryMakeId, $InventoryVendorId,$InventoryLocationId, $InventoryPurchaseDateFrom, $InventoryPurchaseDateTo, $UniqueId)
		{
			$arrPurchaseInventory =  array();
			
			$cond = "";
			if($InventoryTypeId != '' && $InventoryTypeId != 0)
				$cond .= " and sr.inventory_type_id='".mysql_real_escape_string($InventoryTypeId)."'";

			if($InventoryMakeId != '' && $InventoryMakeId != 0)
				$cond .= " and sr.inventory_make_id='".mysql_real_escape_string($InventoryMakeId)."'";

			if($InventoryVendorId != '')
				$cond .= " and sr.vendor_id='".mysql_real_escape_string($InventoryVendorId)."'";

			if($InventoryLocationId != '' && $InventoryLocationId != 0)
				$cond .= " and sr.location_id='".mysql_real_escape_string($InventoryLocationId)."'";

			if($UniqueId != '')
				$cond .= " and sr.uniqueid like '%".mysql_real_escape_string($UniqueId)."%'";

			if($InventoryPurchaseDateFrom != "" && $InventoryPurchaseDateTo != "")
			{
				$cond .= " and date_format(sr.purchasedate,'%Y-%m-%d') between '".mysql_real_escape_string($InventoryPurchaseDateFrom)."' and '".mysql_real_escape_string($InventoryPurchaseDateTo)."'";
			}

			$sSQL = "select t.type, m.make, sr.invoice_no, sr.uniqueid, l.location_name, date_format(purchasedate,'%d-%m-%Y') as purchasedate, date_format(warrenty_expiry,'%d-%m-%Y') as warrenty_expiry, v.vendor_name, sr.status from pms_stock_register sr LEFT JOIN pms_inventory_type t ON sr.inventory_type_id = t.id LEFT JOIN pms_inventory_make m ON sr.inventory_make_id = m.id LEFT JOIN pms_inventory_vendor v ON sr.vendor_id = v.id LEFT JOIN pms_inventory_location l ON sr.location_id = l.id where 1=1 and (sr.isdisposed='0' or sr.isdisposed is null) ".$cond;
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrStatus = array("0"=>"Stock","1"=>"In use","2"=>"Scrap","3"=>"Repair");
					
					$arrTemp = $this->fetchrow();
					
					/* Get text for the status */
					$arrTemp["status_text"] = "";
					if(isset($arrStatus[$this->f("status")]))
						$arrTemp["status_text"] = $arrStatus[$this->f("status")];
					
					$arrPurchaseInventory[] = $arrTemp;
				}
			}
			
			return $arrPurchaseInventory;
		}

		function fnGetScrapDisposal($InventoryTypeId, $InventoryMakeId, $ScrapDisposalDateFrom, $ScrapDisposalDateTo, $UniqueId)
		{
			$arrScrapDisposal =  array();
			
			$cond = "";
			if($InventoryTypeId != '' && $InventoryTypeId != 0)
				$cond .= " and sr.inventory_type_id='".mysql_real_escape_string($InventoryTypeId)."'";

			if($InventoryMakeId != '' && $InventoryMakeId != 0)
				$cond .= " and sr.inventory_make_id='".mysql_real_escape_string($InventoryMakeId)."'";

			if($UniqueId != '')
				$cond .= " and sr.uniqueid like '%".mysql_real_escape_string($UniqueId)."%'";

			if($ScrapDisposalDateFrom != "" && $ScrapDisposalDateTo != "")
			{
				$cond .= " and date_format(sr.disposeddate,'%Y-%m-%d') between '".mysql_real_escape_string($ScrapDisposalDateFrom)."' and '".mysql_real_escape_string($ScrapDisposalDateTo)."'";
			}

			$sSQL = "select t.type, m.make, sr.invoice_no, sr.serialno, sr.uniqueid, l.location_name, date_format(disposeddate,'%d-%m-%Y') as disposeddate,date_format(purchasedate,'%d-%m-%Y') as purchasedate, sd.serialno as disposal_gatepass_no from pms_stock_register sr LEFT JOIN pms_inventory_type t ON sr.inventory_type_id = t.id LEFT JOIN pms_inventory_make m ON sr.inventory_make_id = m.id LEFT JOIN pms_inventory_location l ON sr.location_id = l.id LEFT JOIN pms_scrap_disposal_log sd ON sd.stock_register_id = sr.id where sr.isdisposed='1' and sr.status='2' ".$cond;
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrScrapDisposal[] = $this->fetchrow();
				}
			}
			
			return $arrScrapDisposal;
		}
		
		function fnGetStockSummary()
		{
			$arrStockSummary = array();
			
			$sSQL = "select inventory_type_id, status, COUNT(status) as count from pms_stock_register where (isdisposed='0' or isdisposed is null) GROUP BY inventory_type_id, status";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrStockSummary[$this->f("inventory_type_id")][$this->f("status")] = $this->f("count");
				}
			}
			
			return $arrStockSummary;
		}
		
		function fnGetLocationWiseInventoryList($LocationId)
		{
			$arrInventoryList = array();
			
			$cond = "";
			if($LocationId != "" && $LocationId != "0")
				$cond = " and sr.location_id = '".mysql_real_escape_string($LocationId)."'";

			$sSQL = "select sr.id as srid, t.type, m.make, sr.serialno, sr.uniqueid, l.location_name, date_format(purchasedate,'%d-%m-%Y') as purchasedate, date_format(warrenty_expiry,'%d-%m-%Y') as warrenty_expiry, v.vendor_name, sr.status, sr.warranty_year, sr.warranty_month, if(warrenty_expiry!='' and warrenty_expiry >= now(),1,0) as warranty_status from pms_stock_register sr LEFT JOIN pms_inventory_type t ON sr.inventory_type_id = t.id LEFT JOIN pms_inventory_make m ON sr.inventory_make_id = m.id LEFT JOIN pms_inventory_vendor v ON sr.vendor_id = v.id LEFT JOIN pms_inventory_location l ON sr.location_id = l.id where 1=1 and (sr.isdisposed='0' or sr.isdisposed is null) ".$cond;
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$arrWarrantyStatus = array("0"=>"No","1"=>"Yes");
				$arrStatus = array("0"=>"Stock","1"=>"In use","2"=>"Scrap","3"=>"Repair");
				
				while($this->next_record())
				{
					$arrTemp = $this->fetchrow();
					
					/* Get text for the status */
					$arrTemp["status_text"] = "";
					if(isset($arrStatus[$this->f("status")]))
						$arrTemp["status_text"] = $arrStatus[$this->f("status")];

					/* Get display text for warranty */
					$warranty = "";
					if($arrTemp["warranty_year"] != "" && $arrTemp["warranty_year"] != "0")
						$warranty .= $arrTemp["warranty_year"]." year(s) ";
					
					if($arrTemp["warranty_month"] != "" && $arrTemp["warranty_month"] != "0")
						$warranty .= $arrTemp["warranty_month"]." month(s) ";
						
					$arrTemp["warranty_text"] = $warranty;
					
					/* Get text for warranty status */
					$arrTemp["warranty_status_text"] = $arrWarrantyStatus[$arrTemp["warranty_status"]];

					$arrInventoryList[] = $arrTemp;
				}
			}

			return $arrInventoryList;
		}
		
		function fnGetStockTypeByStockId($Id)
		{
			$inventory_type_id = 0;
			
			$sSQL = "select inventory_type_id from pms_stock_register where id='".mysql_real_escape_string($Id)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$inventory_type_id = $this->f("inventory_type_id");
				}
			}
			
			return $inventory_type_id;
		}
		
		function fnGetInStockInventoryByType($TypeId)
		{
			$StockInventory = array();
			
			$sSQL = "select id, uniqueid from pms_stock_register where inventory_type_id='".mysql_real_escape_string($TypeId)."' and status='0' and (isdisposed='0' or isdisposed is null)";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$StockInventory[] = $this->fetchRow();
				}
			}
			
			return $StockInventory;
		}
		
		function fnGetScrapInventory()
		{
			$StockInventory = array();
			
			$sSQL = "select sr.id as srid, t.type, m.make, sr.serialno, sr.uniqueid, l.location_name, date_format(purchasedate,'%Y-%m-%d') as purchasedate, date_format(warrenty_expiry,'%Y-%m-%d') as warrenty_expiry, v.vendor_name, sr.status, sr.warranty_year, sr.warranty_month, if(warrenty_expiry!='' and warrenty_expiry >= now(),1,0) as warranty_status from pms_stock_register sr LEFT JOIN pms_inventory_type t ON sr.inventory_type_id = t.id LEFT JOIN pms_inventory_make m ON sr.inventory_make_id = m.id LEFT JOIN pms_inventory_vendor v ON sr.vendor_id = v.id LEFT JOIN pms_inventory_location l ON sr.location_id = l.id where sr.status='2' and (sr.isdisposed='0' or sr.isdisposed is null)";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$StockInventory[] = $this->fetchRow();
				}
			}
			
			return $StockInventory;
		}
		
		function fnScrapDisposal($arrStockId)
		{
			$MaxDisposedServialNo = 0;
			
			if(count($arrStockId) > 0)
			{
				$MaxDisposedServialNo = $this->fnGetMaxDisposedSerialNo() + 1;
				
				$curDate = Date('Y-m-d H:i:s');
				foreach($arrStockId as $curStockId)
				{
					/* Save scrap disposal */
					$arrDisposalInfo["stock_register_id"] = $curStockId;
					$arrDisposalInfo["serialno"] = $MaxDisposedServialNo;
					$arrDisposalInfo["addedon"] = $curDate;
					
					$this->insertArray("pms_scrap_disposal_log",$arrDisposalInfo);
					
					/* update stock register to mark stock as disposed */
					$arrStockInfo["id"] = $arrDisposalInfo["stock_register_id"];
					$arrStockInfo["isdisposed"] = 1;
					$arrStockInfo["disposeddate"] = $curDate;
					
					$this->updateArray("pms_stock_register",$arrStockInfo);
					
					/* Insert in log */
					$this->fnStockRegisterLog($arrStockInfo["id"]);
				}
			}
			
			return $MaxDisposedServialNo;
		}
		
		function fnGetMaxDisposedSerialNo()
		{
			$MaxDisposedServialNo = 0;
			
			$sSQL = "select max(serialno) as serialno from pms_scrap_disposal_log";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$MaxDisposedServialNo = $this->f("serialno");
				}
			}
			
			return $MaxDisposedServialNo;
		}
		
		function fnGetScrapDisposalInformationBySerialNo($SerialNo)
		{
			$arrGatepassInformation = array();
			
			$sSQL = "select date_format(sd.addedon, '%d-%m-%Y') as disposal_date, sr.uniqueid, sr.serialno, t.type from pms_scrap_disposal_log sd LEFT JOIN pms_stock_register sr ON sr.id = sd.stock_register_id LEFT JOIN pms_inventory_type t ON t.id = sr.inventory_type_id where sd.serialno='".mysql_real_escape_string($SerialNo)."' and sr.isdisposed='1'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrGatepassInformation[] = $this->fetchRow();
				}
			}
			
			return $arrGatepassInformation;
		}
		
		function fnStockRegisterLog($StockId)
		{
			$arrStockRegister = $arrStockRegisterAttributes = array();
			$db = new DB_Sql();
			$StockLogId = 0;
			
			$sSQL = "select * from pms_stock_register where id='".mysql_real_escape_string($StockId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrStockRegister = $this->fetchRow();
					$arrStockRegister["stock_id"] = $StockId;
					$arrStockRegister["login_id"] = $_SESSION["id"];
					$arrStockRegister["login_type"] = $_SESSION["usertype"];
					$arrStockRegister["addedon"] = Date('Y-m-d H:i:s');
					
					if(isset($arrStockRegister["id"]))
						unset($arrStockRegister["id"]);
					
					$StockLogId = $db->insertArray("pms_stock_register_log",$arrStockRegister);
				}
			}
			
			$sSQL = "select * from pms_stock_register_attributes where stock_id='".mysql_real_escape_string($StockId)."'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$arrStockRegisterAttributes = $this->fetchRow();
					$arrStockRegisterAttributes["stock_log_id"] = $StockLogId;
					
					if(isset($arrStockRegisterAttributes["id"]))
						unset($arrStockRegisterAttributes["id"]);
					
					$db->insertArray("pms_stock_register_attributes_log",$arrStockRegisterAttributes);
				}
			}
		}
		
		function fnGetDetailCurrentStockStatus()
		{
			$arrStock = array();

			$sSQL = "select sr.*, it.type as inventory_type, il.location_name as inventory_location, im.make as inventory_make, iv.vendor_name, iv1.vendor_name as sendto_vendor_name, date_format(sr.purchasedate, '%d-%m-%Y') as purchase_date, date_format(sr.warrenty_expiry,'%d-%m-%Y') as warrenty_expiry, date_format(sr.expected_return_date,'%d-%m-%Y') as expected_return_date, if(sr.warrenty_expiry!='' and sr.warrenty_expiry >= now(),1,0) as warranty_status from pms_stock_register sr LEFT JOIN pms_inventory_type it ON it.id = sr.inventory_type_id LEFT JOIN pms_inventory_location il ON il.id = sr.location_id LEFT JOIN pms_inventory_make im ON im.id = sr.inventory_make_id LEFT JOIN pms_inventory_vendor iv ON iv.id = sr.vendor_id LEFT JOIN pms_inventory_vendor iv1 ON iv1.id = sr.sentto_vendor_id where (sr.isdisposed is null or sr.isdisposed ='' or sr.isdisposed ='0')";
			$this->query($sSQL);
			if($this->num_rows())
			{
				$arrWarrantyStatus = array("0"=>"No","1"=>"Yes");
				$arrStatus = array("0"=>"Stock","1"=>"In use","2"=>"Scrap","3"=>"Repair");
				
				while($this->next_record())
				{
					$arrTemp = $this->fetchRow();
					
					/* Get text for the status */
					$arrTemp["status_text"] = "";
					if(isset($arrStatus[$this->f("status")]))
						$arrTemp["status_text"] = $arrStatus[$this->f("status")];

					/* Get display text for warranty */
					$warranty = "";
					if($arrTemp["warranty_year"] != "" && $arrTemp["warranty_year"] != "0")
						$warranty .= $arrTemp["warranty_year"]." year(s) ";

					if($arrTemp["warranty_month"] != "" && $arrTemp["warranty_month"] != "0")
						$warranty .= $arrTemp["warranty_month"]." month(s) ";

					$arrTemp["warranty_text"] = $warranty;

					/* Get text for warranty status */
					$arrTemp["warranty_status_text"] = $arrWarrantyStatus[$arrTemp["warranty_status"]];
					
					/* Fetch attributes */
					$arrTemp["attributes"] = $this->fnGetSelectedStockAttributesDetail($arrTemp["id"]);

					$arrStock[] = $arrTemp;
					
				}
			}

			return $arrStock;
		}
	}

?>

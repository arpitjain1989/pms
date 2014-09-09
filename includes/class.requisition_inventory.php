<?php

	include_once('db_mysql.php');

	class requisition_inventory extends DB_Sql
	{
		function __construct()
		{
		}
		
		/*
		 * Save requistion inventory
		 * */
		function fnSaveRequisitionInventory($arrRequisitionInventory)
		{
			$arrRequisitionInventory["allowed_designation"][] = 0;
			$arrRequisitionInventory["allowed_designation"] = implode(",",$arrRequisitionInventory["allowed_designation"]);
			
			/*if(!isset($arrRequisitionInventory["allow_teamleaders"]))
				$arrRequisitionInventory["allow_teamleaders"] = 0;

			if(!isset($arrRequisitionInventory["allow_managers"]))
				$arrRequisitionInventory["allow_managers"] = 0;*/

			if(!isset($arrRequisitionInventory["is_approval_required"]))
				$arrRequisitionInventory["is_approval_required"] = 0;

			$arrRequisitionInventory["last_modified"] = Date('Y-m-d H:i:s');

			/* Check if the id already exists */
			if(isset($arrRequisitionInventory["id"]) && trim($arrRequisitionInventory["id"]) != "")
			{
				/* If the id already exists, check if the requisition inventory is already added in any other record, then update */
				if($this->fnValidateRequisitionInventory($arrRequisitionInventory["title"], $arrRequisitionInventory["id"]))
					$this->updateArray("pms_requisition_for",$arrRequisitionInventory);
				else
					return false;				
			}
			else
			{
				/* If id does not exists, check if the make is already exists, if not insert the make */
				if($this->fnValidateRequisitionInventory($arrRequisitionInventory["title"]))
				{
					$this->insertArray("pms_requisition_for",$arrRequisitionInventory);
				}
				else
					return false;

			}
			return true;
		}
		
		/* Check if the requisition inventory already exists */
		function fnValidateRequisitionInventory($inventory_make, $id = 0)
		{
			$cond = "";
			if($id != 0)
				$cond = " and id!='".mysql_real_escape_string($id)."'";

			$sSQL = "select * from pms_requisition_for where title='".mysql_real_escape_string($inventory_make)."' $cond";
			$this->query($sSQL);
			if($this->num_rows() == 0)
				return true;
			else
				return false;
		}
		
		/* Get all the requisition inventory */
		function fnGetAllRequisitionInventory()
		{
			$arrRequisitionInventory = array();
			
			$sSQL = "select * from pms_requisition_for";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRequisitionInventory[] = $this->fetchrow();
				}
			}
			
			return $arrRequisitionInventory;
		}
		
		/* Get all the requisition inventory by id */
		function fnGetRequisitionInventoryById($id)
		{
			$arrRequisitionInventory = array();
			$sSQL = "select *, if(allow_teamleaders=1,'Yes','No') as allow_teamleaders_text, if(allow_managers=1,'Yes','No') as allow_managers_text, if(is_approval_required=1,'Yes','No') as approval_required_text from pms_requisition_for where id='".mysql_real_escape_string($id)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrRequisitionInventory = $this->fetchrow();
				}
			}

			return $arrRequisitionInventory;
		}
		
		function fnGetRequisitionFor()
		{
			$arrRequisitionFor = array();
			
			$cond = " where 1!=1";
			
			/*if(isset($_SESSION["designation"]) && (trim($_SESSION["designation"]) == "7" || trim($_SESSION["designation"]) == "13"))
			{
				$cond = " where allow_teamleaders='1'";
			}
			else if(isset($_SESSION["designation"]) && (trim($_SESSION["designation"]) == "6" || trim($_SESSION["designation"]) == "18" || trim($_SESSION["designation"]) == "19" || trim($_SESSION["designation"]) == "25" || trim($_SESSION["designation"]) == "17"))
			{
				$cond = " where allow_managers='1'";
			}
			
			$sSQL = "select * from pms_requisition_for $cond";*/
			
			$sSQL = "select * from pms_requisition_for WHERE INSTR(concat(',',allowed_designation,','), ',".mysql_real_escape_string($_SESSION["designation"]).",') > 0";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRequisitionFor[] = $this->fetchRow();
				}
			}

			return $arrRequisitionFor;
		}
		
		function fnGetRequisitionInventoryTitleById($id)
		{
			$RequisitionInventoryTitle = "";
			$sSQL = "select title from pms_requisition_for where id='".mysql_real_escape_string($id)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$RequisitionInventoryTitle = $this->f("title");
				}
			}

			return $RequisitionInventoryTitle;
		}
		
		function fnGetApprovalRequiredForRequisitionInventory($id)
		{
			$isApprovalRequired = 0;
			
			$sSQL = "select is_approval_required from pms_requisition_for where id='".mysql_real_escape_string($id)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$isApprovalRequired = $this->f("is_approval_required");
				}
			}
			
			return $isApprovalRequired;
		}
	}
?>

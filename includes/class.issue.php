<?php

	include_once('db_mysql.php');

	class issue extends DB_Sql
	{
		function __construct()
		{
		}
		
		/*
		 * Save issue
		 * */
		function fnSaveIssue($arrIssue)
		{
			/* Check if the id already exists */
			if(isset($arrIssue["id"]) && trim($arrIssue["id"]) != "")
			{
				/* If the id already exists, check if the issue is already added in any other record, then update */
				if($this->fnValidateIssue($arrIssue["issue"], $arrIssue["id"]))
					$this->updateArray("pms_issue",$arrIssue);
				else
					return false;				
			}
			else
			{
				/* If id does not exists, check if the issue is already exists, if not insert the issue */
				if($this->fnValidateIssue($arrIssue["issue"]))
				{
					$arrIssue["addedon"] = Date('Y-m-d H:i:s');
					$this->insertArray("pms_issue",$arrIssue);
				}
				else
					return false;

			}
			return true;
		}
		
		/* Check if the issue already exists */
		function fnValidateIssue($issue, $id = 0)
		{
			$cond = "";
			if($id != 0)
				$cond = " and id!='".mysql_real_escape_string($id)."'";

			$sSQL = "select * from pms_issue where issue='".mysql_real_escape_string($issue)."' $cond";
			$this->query($sSQL);
			if($this->num_rows() == 0)
				return true;
			else
				return false;
		}
		
		/* Get all the issue */
		function fnGetAllIssue()
		{
			$arrIssue = array();
			
			$sSQL = "select i.*, issue_category, time_format(estimated_resolution_time, '%H:%i') as estimated_resolution_time from pms_issue i LEFT JOIN pms_issue_category ic ON i.issue_category_id = ic.id";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrIssue[] = $this->fetchrow();
				}
			}
			
			return $arrIssue;
		}
		
		/* Get issue by id */
		function fnGetIssueById($id)
		{
			$arrIssue = array();
			$sSQL = "select i.*, issue_category, time_format(estimated_resolution_time, '%H:%i') as estimated_resolution_time from pms_issue i LEFT JOIN pms_issue_category ic ON i.issue_category_id = ic.id where i.id='".mysql_real_escape_string($id)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrIssue = $this->fetchrow();
				}
			}

			return $arrIssue;
		}
		
		/* Get issues by category */
		function fnGetIssueByCategoryId($CategoryId)
		{
			$arrIssue = array();
			
			$sSQL = "select * from pms_issue where issue_category_id = '".mysql_real_escape_string($CategoryId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrIssue[] = $this->fetchrow();
				}
			}

			return $arrIssue;
		}
		
		/* Save issue access information */
		function fnSaveIssueAccess($DesignationId, $AccessRights)
		{
			if(count($AccessRights) > 0)
			{
				/* Delete previously provided access rights */
				$sSQL = "update pms_issue_access set isdeleted='1', deleted_datetime='".Date('Y-m-d H:i:s')."' where designation_id='".mysql_real_escape_string($DesignationId)."' and isdeleted='0'";
				$this->query($sSQL);
				
				foreach($AccessRights as $curAccessRight)
				{
					$arrAccessRights["designation_id"] = $DesignationId;
					$arrAccessRights["issue_id"] = $curAccessRight;
					$arrAccessRights["isdeleted"] = "0";
					$arrAccessRights["addedon"] = Date('Y-m-d H:i:s');
					
					$this->insertArray("pms_issue_access",$arrAccessRights);
				}
			}
			return true;
		}
		
		/* Get issue access by designation */
		function fnGetIssueAccessByDesignationId($DesignationId)
		{
			$arrIssueAccess = array();
			
			$sSQL = "select issue_id from pms_issue_access where designation_id='".mysql_real_escape_string($DesignationId)."' and isdeleted='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrIssueAccess[] = $this->f("issue_id");
				}
			}
			
			return $arrIssueAccess;
		}
		
		/* Fetch distinct issue category information as per the access provided according to the designation */
		function fnGetIssueCategoryAccessDetailByDesignation($DesignationId)
		{
			$arrIssueCategory = array();
			
			$sSQL = "select distinct ic.id as issue_categgory_id, ic.issue_category from pms_issue_access ia INNER JOIN pms_issue i ON i.id = ia.issue_id INNER JOIN pms_issue_category ic ON i.issue_category_id = ic.id where ia.isdeleted='0' and ia.designation_id='".mysql_real_escape_string($DesignationId)."'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$arrIssueCategory[] = $this->fetchrow();
				}
			}
			
			return $arrIssueCategory;
		}
		
		function fnGetIssueAccessByIssueCategoryAndDesignation($CategoryId, $DesignationId)
		{
			$arrIssue = array();
			
			$sSQL = "select i.* from pms_issue_access ia INNER JOIN pms_issue i ON i.id = ia.issue_id where ia.designation_id='".mysql_real_escape_string($DesignationId)."' and ia.isdeleted='0' and i.issue_category_id='".mysql_real_escape_string($CategoryId)."'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$arrIssue[] = $this->fetchrow();
				}
			}
			
			return $arrIssue;
		}
		
		/* Get issue name by issue id */
		function fnGetIssueNameById($IssueId)
		{
			$IssueName = "";
			
			$sSQL = "select issue from pms_issue where id = '".mysql_real_escape_string($IssueId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$IssueName = $this->f("issue");
				}
			}

			return $IssueName;
		}
	}
?>

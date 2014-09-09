<?php

	include_once('db_mysql.php');

	class issue_category extends DB_Sql
	{
		function __construct()
		{
		}
		
		/*
		 * Save issue category
		 * */
		function fnSaveIssueCategory($arrIssueCategory)
		{
			/* Check if the id already exists */
			if(isset($arrIssueCategory["id"]) && trim($arrIssueCategory["id"]) != "")
			{
				/* If the id already exists, check if the issue category is already added in any other record, then update */
				if($this->fnValidateIssueCategory($arrIssueCategory["issue_category"], $arrIssueCategory["id"]))
					$this->updateArray("pms_issue_category",$arrIssueCategory);
				else
					return false;				
			}
			else
			{
				/* If id does not exists, check if the issue category is already exists, if not insert the issue category */
				if($this->fnValidateIssueCategory($arrIssueCategory["issue_category"]))
				{
					$arrIssueCategory["addedon"] = Date('Y-m-d H:i:s');
					$this->insertArray("pms_issue_category",$arrIssueCategory);
				}
				else
					return false;

			}
			return true;
		}
		
		/* Check if the issue category already exists */
		function fnValidateIssueCategory($issue_category, $id = 0)
		{
			$cond = "";
			if($id != 0)
				$cond = " and id!='".mysql_real_escape_string($id)."'";

			$sSQL = "select * from pms_issue_category where issue_category='".mysql_real_escape_string($issue_category)."' $cond";
			$this->query($sSQL);
			if($this->num_rows() == 0)
				return true;
			else
				return false;
		}
		
		/* Get all the issue category */
		function fnGetAllIssueCategory()
		{
			$arrIssueCategory = array();
			
			$sSQL = "select * from pms_issue_category";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrIssueCategory[] = $this->fetchrow();
				}
			}
			
			return $arrIssueCategory;
		}
		
		/* Get issue category by id */
		function fnGetIssueCategoryById($id)
		{
			$arrIssueCategory = array();
			$sSQL = "select * from pms_issue_category where id='".mysql_real_escape_string($id)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrIssueCategory = $this->fetchrow();
				}
			}

			return $arrIssueCategory;
		}
		
		function fnGetIssueCategoryNameById($CategoryId)
		{
			$CategoryName = "";
			
			$sSQL = "select issue_category from pms_issue_category where id='".mysql_real_escape_string($CategoryId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$CategoryName = $this->f("issue_category");
				}
			}
			
			return $CategoryName;
		}
	}
?>

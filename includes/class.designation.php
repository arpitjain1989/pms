<?php
include_once('db_mysql.php');
	class designations extends DB_Sql
	{
		function __construct()
		{
		}
		
		function fnInsertDesignation($arrDesignation)
		{
			if(!isset($arrPost["allow_delegation"]))
			{
				$arrPost["allow_delegation"] = 0;
				$arrPost["delegation_designation"] = "0";
			}
			else
			{
				if(isset($arrPost["delegation_designation_id"]) && count($arrPost["delegation_designation_id"]) > 0)
				{
					$arrPost["delegation_designation_id"][] = 0;
					$arrPost["delegation_designation"] = implode(",", $arrPost["delegation_designation_id"]);
				}
				else
					$arrPost["delegation_designation"] = "0";
			}

			if(!isset($arrPost["consider_break_exceed"]))
				$arrPost["consider_break_exceed"] = 0;
			
			if(!isset($arrPost["consider_late_commings"]))
				$arrPost["consider_late_commings"] = 0;

			if(!isset($arrPost["consider_inout_time"]))
				$arrPost["consider_inout_time"] = 0;

			if(!isset($arrPost["allow_roster_generation"]))
				$arrPost["allow_roster_generation"] = 0;
			
			$this->insertArray('pms_designation',$arrDesignation);
			return true;
		}
		
		function fnGetAllDesignations()
		{
			$arrDesignationValues = array();
			$query = "SELECT * FROM `pms_designation` order by title";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrDesignationValues[] = $this->fetchrow();
				}
			}
			return $arrDesignationValues;
		}

		function fnGetDesignationById($id)
		{
			$arrDesignationValues = array();
			$query = "SELECT d.*, d1.title as parent_designation_name, d2.title as first_reporting_head_name, d3.title as second_reporting_head_name, if(d.allow_delegation = 1,'Yes','No') as allow_designation_text, if(d.consider_break_exceed = 1,'Yes','No') as consider_break_exceed_text, if(d.consider_late_commings = 1,'Yes','No') as consider_late_commings_text, if(d.consider_inout_time = 1,'Yes','No') as consider_inout_time_text, if(d.allow_roster_generation = 1,'Yes','No') as allow_roster_generation_text, time_format(d.fullday_minimum_working_hour, '%H:%i') as fullday_minimum_working_hour, time_format(d.fullday_break_minutes, '%H:%i') as fullday_break_minutes, time_format(d.halfday_minimum_working_hour, '%H:%i') as halfday_minimum_working_hour, time_format(d.halfday_break_minutes, '%H:%i') as halfday_break_minutes, time_format(d.sm_minimum_working_hour, '%H:%i') as sm_minimum_working_hour, time_format(d.sm_break_minutes, '%H:%i') as sm_break_minutes FROM pms_designation d LEFT JOIN pms_designation d1 ON d.parent_designation_id = d1.id LEFT JOIN pms_designation d2 ON d.first_reporting_head = d2.id LEFT JOIN pms_designation d3 ON d.second_reporting_head = d3.id WHERE d.id = '".mysql_real_escape_string($id)."'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrDesignationValues = $this->fetchrow();
				}
			}
			return $arrDesignationValues;
		}

		function fnUpdateDesignations($arrPost)
		{
			if(!isset($arrPost["allow_delegation"]))
			{
				$arrPost["allow_delegation"] = 0;
				$arrPost["delegation_designation"] = "0";
			}
			else
			{
				if(isset($arrPost["delegation_designation_id"]) && count($arrPost["delegation_designation_id"]) > 0)
				{
					$arrPost["delegation_designation_id"][] = 0;
					print_r($arrPost["delegation_designation_id"]);
					$arrPost["delegation_designation"] = implode(",", $arrPost["delegation_designation_id"]);
				}
				else
					$arrPost["delegation_designation"] = "0";
			}

			if(!isset($arrPost["consider_break_exceed"]))
				$arrPost["consider_break_exceed"] = 0;

			if(!isset($arrPost["consider_late_commings"]))
				$arrPost["consider_late_commings"] = 0;

			if(!isset($arrPost["consider_inout_time"]))
				$arrPost["consider_inout_time"] = 0;

			if(!isset($arrPost["allow_roster_generation"]))
				$arrPost["allow_roster_generation"] = 0;

			$this->updateArray('pms_designation',$arrPost);
			return true;
		}
		
		function fnDeleteDesignation($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_designation` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}

		function fnGetDesignationIdByName($name)
		{
			$DesignationId = array();
			$query = "select id FROM `pms_designation` WHERE `title` = '".mysql_real_escape_string(trim($name))."'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$DesignationId = $this->f("id");
				}
			}
			return $DesignationId;
		}

		function fnGetDesignationNameById($designationId)
		{
			$DesignationName = "";
			$query = "select title FROM `pms_designation` WHERE id = '".mysql_real_escape_string($designationId)."'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$DesignationName = $this->f("title");
				}
			}
			return $DesignationName;
		}

		function fnGetDesNameById($id)
		{
			$DesignationName = '';
			$query = "SELECT title FROM `pms_designation` WHERE `id` = '".mysql_real_escape_string($id)."' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$DesignationName = $this->f('title');
				}
			}
			return $DesignationName;
		}

		function fnAddCurrentOpenings($post)
		{
			$date = Date('Y-m-d H:i:s');
			$updateCurrentOpenings = $this->fnUpdateCurrentOpenings();
			if(isset($post["chkshifttime"]) && count($post["chkshifttime"]) > 0)
			{
				foreach($post["chkshifttime"] as $designationId)
				{
					$arrDesignationValues = array("latest_opening" => $designationId,"date" => Date('Y-m-d H:i:s'));
					$this->insertArray("pms_latest_openings",$arrDesignationValues);
				}
			}
			return true;
		}
		function fnUpdateCurrentOpenings()
		{
			$date = Date('Y-m-d H:i:s');
			$query = "UPDATE `pms_latest_openings` SET isDelete = '1',date_delete = '$date'";
			$this->query($query);
			return true;
		}
		function fnGetCurrentDesignations()
		{
			$currentOpenings = array();
			$query = "select latest_opening as id FROM `pms_latest_openings` where isDelete='0'";	
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$currentOpenings[] = $this->f("id");
				}
			}
			return $currentOpenings;
		}

		/* Get all the designation not having the given ID */
		function fnGetParentDesignation()
		{
			$arrDesignation = array();
			
			$sSQL = "select id, title from pms_designation";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrDesignation[$this->f("id")] = $this->f("title");
				}
			}
			
			return $arrDesignation;
		}
		
		function fnGetDesignationHierarchy($DesignationId)
		{
			$arrDesignation = array();
			$db = new DB_Sql();

			$sSQL = "select d.parent_designation_id, d1.title from pms_designation d LEFT JOIN pms_designation d1 ON d.parent_designation_id = d1.id where d.id='".mysql_real_escape_string($DesignationId)."'";
			$db->query($sSQL);
			if($db->num_rows() > 0)
			{
				if($db->next_record())
				{
					if($db->f("parent_designation_id") != "" && $db->f("parent_designation_id") != "0")
					{
						$arrDesignation[$db->f("parent_designation_id")] = $db->f("title");
						$tmpData = $this->fnGetDesignationHierarchy($db->f("parent_designation_id"));
						$arrDesignation = $arrDesignation + $tmpData;
					}
				}
			}
			return $arrDesignation;
		}
		
		function fnGetDesignationBreaksToBeDeducted()
		{
			$arrDesignation = array();
			$db = new DB_Sql();
			
			$sSQL = "select id from pms_designation where consider_break_exceed='0' or consider_break_exceed is null";
			$db->query($sSQL);
			if($db->num_rows() > 0)
			{
				while($db->next_record())
				{
					$arrDesignation[] = $db->f("id");
				}
			}
			
			return $arrDesignation;
		}
		
		function fnGetAllParentDesignations()
		{
			$arrDesignation = array();
			
			$sSQL = "select distinct parent_designation_id as did from pms_designation where parent_designation_id!='' or parent_designation_id is not null";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrDesignation[] = $this->f("did");
				}
			}
			return $arrDesignation;
		}
		
		function fnGetDesignationforRoster()
		{
			$arrDesignation = array();
			
			$sSQL = "select id from pms_designation where allow_roster_generation='1'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$arrDesignation[] = $this->f("id");
				}
			}
			
			return $arrDesignation;
		}
		
		function fnCheckIfParentDesignation($DesignationId)
		{
			$flag = false;
			
			$sSQL = "select id from pms_designation where parent_designation_id='".mysql_real_escape_string($DesignationId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				$flag = true;
			}
			
			return $flag;
		}
	}
?>

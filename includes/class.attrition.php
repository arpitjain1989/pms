<?php
	include_once('db_mysql.php');
	class attrition extends DB_Sql
	{
		function __construct()
		{
		}
		function fnGetAllAttritions($ids)
		{
			$arrLeaveFormValues = array();
			
			$query = "SELECT employee.*,attr.*,head.name as headname,attr.id as attrition_id,userid as employee_id,DATE_FORMAT(`attendance_date`,'%d-%m-%Y') as attr_date FROM `pms_attrition_process` AS attr INNER JOIN `pms_employee` AS employee ON attr.userid = employee.id LEFT JOIN `pms_employee` AS head ON employee.teamleader = head.id WHERE attr.userid IN($ids)";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrLeaveFormValues[] = $this->fetchrow();
				}
			}
			return $arrLeaveFormValues;
		}

		/* Get attrition Details by id */
		function fnGetAttrationDetailsById($id)
		{
		
			$arrLeaveValues = array();
			$query = "SELECT employee.*,attr.*,attr.id as attrid,employee.name as emp_name,date_format(attr.attendance_date,'%d-%m-%Y') as att_date,date_format(attr.tl_holdtill,'%d-%m-%Y') as teamleader_holdtill,date_format(attr.manager_holdtill,'%d-%m-%Y') as man_holdtill,date_format(attr.hr_holdtill,'%d-%m-%Y') as hr_hold_till,date_format(attr.admin_holdtill,'%d-%m-%Y') as admin_hold_till FROM `pms_attrition_process` AS attr INNER JOIN `pms_employee` AS employee ON attr.userid = employee.id WHERE attr.id ='$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrLeaveValues = $this->fetchrow();
					
					if($arrLeaveValues["tl_status"] == 0)
						$arrLeaveValues["tlstatus"] = "Pending";
					else if($arrLeaveValues["tl_status"] == 1)
						$arrLeaveValues["tlstatus"] = "Process termination";
					else if($arrLeaveValues["tl_status"] == 2)
						$arrLeaveValues["tlstatus"] = "Hold";

					if($arrLeaveValues["manager_status"] == 0)
						$arrLeaveValues["managerstatus"] = "Pending";
					else if($arrLeaveValues["manager_status"] == 1)
						$arrLeaveValues["managerstatus"] = "Process termination";
					else if($arrLeaveValues["manager_status"] == 2)
						$arrLeaveValues["managerstatus"] = "Hold";

					if($arrLeaveValues["admin_status"] == 0)
						$arrLeaveValues["adminstatus"] = "Pending";
					else if($arrLeaveValues["admin_status"] == 1)
						$arrLeaveValues["adminstatus"] = "Process termination";
					else if($arrLeaveValues["admin_status"] == 2)
						$arrLeaveValues["adminstatus"] = "Hold";

					if($arrLeaveValues["hr_status"] == 0)
						$arrLeaveValues["hrstatus"] = "Pending";
					else if($arrLeaveValues["hr_status"] == 1)
						$arrLeaveValues["hrstatus"] = "Process termination";
					else if($arrLeaveValues["hr_status"] == 2)
						$arrLeaveValues["hrstatus"] = "Hold";
				}
			}
			
			return $arrLeaveValues;
		}

		function fnUpdateAttrition($post)
		{
			$arrPost = array();
			$arrPost['id'] = $post['hdnid'];
			$arrAttrition = $this->fnGetAttrationDetailsById($arrPost['id']);
			if(count($arrAttrition) > 0)
			{
				if($_SESSION['admin_type'] == 'admin')
				{
					$arrPost['admin_date'] = $post['adminholddate'];
					$arrPost['adminholdcomment'] = $post['adminholdcomment'];
				}
				else if($_SESSION['admin_type'] == 'hradmin')
				{
					$arrPost['hr_holdtill'] = $post['hrholddate'];
					$arrPost['hrholdcomment'] = $post['hrholdcomment'];
				}
				else if($arrAttrition['managerid'] == $_SESSION["id"])
				{
					$arrPost['manager_holdtill'] = $post['managerholddate'];
					$arrPost['managerholdcomment'] = $post['managerholdcomment'];
				}
				else if($arrAttrition['tlid'] == $_SESSION["id"])
				{
					$arrPost['tl_holdtill'] = $post['tlholddate'];
					$arrPost['tlholdcomment'] = $post['tlholdcomment'];
				}

				$this->updateArray('pms_attrition_process',$arrPost);
				return true;
			}
			else
				return false;
		}
		
	}
?>

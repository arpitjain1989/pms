<?php
	include_once('db_mysql.php');
	class joinees extends DB_Sql
	{
		function __construct()
		{
		}
		
		/* Get all joiners that are got Shortlisted */
		function fnGetAllJoinersForJoinings()
		{
			$arrAllJoiners = array();
			$query = "SELECT user.id as cand_id,user.recommend_om as rec_om,user.name as cand_name,date_format(user.final_hr_exp_date_of_joining,'%d-%m-%Y') as hr_exp_joining_date,date_format(user.expected_joining_date,'%d-%m-%Y') as cand_exp_joining_date,emp.name as teamLeader_name_hr,shifts.title as shift_title FROM `pms_user_registration` as user left join `pms_employee` as emp on user.final_hr_teamleader_by_manager = emp.id left join pms_shift_times as shifts on user.final_hr_shift_timning_by_manager = shifts.id  WHERE user.final_hr_status = '4' AND user.isjoin = '0'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllJoiners[] =  $this->fetchrow();
				}
			}
			return $arrAllJoiners;
		}
		
		/* Get joinees details using id */
		function fnGetJoineesById($id)
		{
			$arrAllJoiners = array();
			$query = "SELECT user.id as cand_id,user.recommend_om as rec_om,user.name as cand_name,date_format(user.final_hr_exp_date_of_joining,'%d-%m-%Y') as hr_exp_joining_date,date_format(user.expected_joining_date,'%d-%m-%Y') as cand_exp_joining_date,emp.name as teamLeader_name_hr,shifts.title as shift_title FROM `pms_user_registration` as user left join `pms_employee` as emp on user.final_hr_teamleader_by_manager = emp.id left join pms_shift_times as shifts on user.final_hr_shift_timning_by_manager = shifts.id  WHERE user.id = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrAllJoiners =  $this->fetchrow();
				}
			}
			return $arrAllJoiners;
		}

		/* Get expected date of joining that is decided by manager for candidate */
		function fnGetExpDateOfJoinByManager($man_id,$eid)
		{
			$shift_title = '';
			$query = "SELECT date_format(exp_date_of_joining,'%d-%m-%Y') as official_shift_timings FROM `pms_rct_opm_comments` WHERE `cand_id` = '$eid' and  `ops_id` = '$man_id'";
			$this->query($query);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$shift_title = $this->f('official_shift_timings');
				}
			}
			return $shift_title;
		}
		
		/* Get expected teamleader that is decided by manager */
		function fnGetTemLeaderByManager($man_id,$eid)
		{
			$teamleader = '';
			$query = "SELECT emp.name as emp_name FROM `pms_rct_opm_comments` as comment left join pms_employee as emp on comment.teamleader_by_manager = emp.id WHERE comment.cand_id = '$eid' and  comment.ops_id = '$man_id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$teamleader = $this->f('emp_name');
				}
			}
			return $teamleader;
		}
		
		/* Get expected shift that is decided by manager */
		function fnGetShiftByManager($man_id,$eid)
		{
			$shift_title = '';
			$query = "SELECT shift.title as shift_title FROM `pms_rct_opm_comments` as comment left join pms_shift_times as shift on comment.shift_timning_by_manager = shift.id WHERE comment.cand_id = '$eid' and  comment.ops_id = '$man_id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$shift_title = $this->f('shift_title');
				}
			}
			return $shift_title;
		}
		
		/* Get document details for the candidate */
		function fnGetDocumentsDetails($id)
		{
			$allDocuments = array();
			$query = "SELECT * FROM `pms_candidate_document_details` WHERE candid = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$allDocuments = $this->fetchrow();
				}
			}
			return $allDocuments;
			//pms_candidate_document_details
		}
		
		/* Check particula id is joined or not */
		function fnCheckJoinee($id)
		{
			$allEmployee = array();
			$query = "select id from pms_user_registration WHERE (final_hr_status = '4' OR (om_status = '1' AND final_hr_status = '0' )) AND isjoin = '0' and id='$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$allEmployee[] = $this->fetchrow();
				}
			}
			$tcount = count($allEmployee);
			return $tcount;
		}
	}
?>

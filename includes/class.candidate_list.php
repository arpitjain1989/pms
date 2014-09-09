<?php
include_once('db_mysql.php');
	class candidate_list extends DB_Sql
	{
		function __construct()
		{
		}
		function fnInsertCandidateList($arrEmployee)
		{
			$arrNewRecords = array("title"=>$arrEmployee['title'],"description"=>$arrEmployee['description']);
			$this->insertArray('pms_user_registration',$arrNewRecords);
			return true;
		}
		function fnGetAllCandidateList()
		{
			$arrRCTDivisionValues = array();
			$query = "SELECT reg . * , des . * , des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id where reg.recommend_hr_round = '1'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}
		function fnGetAllCandidates()
		{
			$arrRCTDivisionValues = array();
			$query = "SELECT reg. * , des. * , des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date, reg.id AS cand_id FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}

		function fnGetAllPendingCandidateList()
		{
			$arrRCTDivisionValues = array();
			//$query = "SELECT reg . * , des . * , des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id where reg.recommend_hr_round = '1' and reg.recommend_test IN('0','3') or (reg.recommend_test = '1' and reg.recommend_om_round ='0' and  reg.final_hr_status = '0' ) ";
			
			$query = "SELECT reg . * , des . * , des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id where reg.recommend_hr_round = '1' and reg.recommend_test IN('0','3')";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}

		function fnGetAllCandidateListIQ()
		{
			$arrRCTDivisionValues = array();
			$query = "SELECT reg . * , des . * , des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}
		function fnGetAllPendingCandidateListIQ()
		{
			$arrRCTDivisionValues = array();
			//$query = "SELECT reg . * , des . * , des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id where reg.recommend_hr_round = '0' or (reg.recommend_hr_round = '1' and reg.recommend_test = '0' and reg.recommend_om_round ='0' and  reg.final_hr_status = '0')";
			
			$query = "SELECT reg . * , des . * , des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id where reg.recommend_hr_round = '0'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}
		
		

		function fnGetCandidateListById($id)
		{
			$arrRCTDivisionValues = array();
			$query = "SELECT * FROM `pms_user_registration` WHERE `id` = '".mysql_real_escape_string($id)."' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}

		function fnUpdateCandidateList($arrPost,$arrRequest)
		{
			$this->updateArray('pms_user_registration',$arrPost);
			return true;
		}

		function fnDeleteCandidateList($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_user_registration` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}
		function fnGetCandidateById($id)
		{
			$arrCandidatevalues = array();
			$query = "SELECT reg.*,reg.recommend_om_round as cand_recommend_om_round,reg.id as cand_id,reg.des_id as designation_id,reg.rctsource as rctsource_id,reg.interviewer as interviewer_taker,reg.initiative as reg_initiative,reg.communication_listening as reg_communication_listening,reg.attitute as reg_attitute,reg.hrcomments as reg_hrcomments,reg.recommend_test as reg_recommend_test,reg.name as cand_name,DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date, DATE_FORMAT( reg.final_hr_exp_date_of_joining, '%Y-%m-%d' ) AS final_hr_date,DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,DATE_FORMAT( reg.dob, '%Y-%m-%d' ) AS reg_dob_date,DATE_FORMAT( reg.expected_joining_date, '%Y-%m-%d' ) AS reg_expt_date,reg.reference_trans as reference_trans,reg.totExperience as total_experience,reg.relExperience as rel_job_exp,reg.graduation_degree as education,des.title AS des_title FROM `pms_user_registration` as reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id where reg.id = '$id'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrCandidatevalues = $this->fetchrow();
				}
			}
			//echo '<pre>'; print_r($arrCandidatevalues);
			return $arrCandidatevalues;
			
		}
		function fnUpdateCandidate($arrPostData)
		{
			if (file_exists("media/resume/" . $_FILES["file"]["name"]))
				{
					header("Location: candidates.php?info=exist");
				}
				else
				{
					move_uploaded_file($_FILES["file"]["tmp_name"],"media/resume/" . $_FILES["file"]["name"]);
				}
				
			$arrPostData['resume'] = $_FILES["file"]["name"];
			$arrPostData['isactive'] = '1';
			//echo '<pre>'; print_r($arrPostData); die;
			$this->updateArray('pms_user_registration',$arrPostData);
			return true;
		}
		
		function fnUpdateCandidateIQ($arrPostData)
		{
			if($arrPostData['recommend_hr_round'] == '2')
			{
				$arrPostData['isactive'] = '1';
			}
			else if($arrPostData['recommend_hr_round'] == '1')
			{
				$arrPostData['isactive'] = '0';
			}
			$this->updateArray('pms_user_registration',$arrPostData);
			return true;
		}
		
		function fnUpdateCandidatesById($arrPostData)
		{
			if (file_exists("media/resume/" . $_FILES["file"]["name"]))
				{
					header("Location: candidates.php?info=exist");
				}
				else
				{
					move_uploaded_file($_FILES["file"]["tmp_name"],"media/resume/" . $_FILES["file"]["name"]);
				}
				
			$arrPostData['resume'] = $_FILES["file"]["name"];
			//echo '<pre>'; print_r($arrPostData); die;
			$this->updateArray('pms_user_registration',$arrPostData);
			return true;
		}
		function fnGetTotalInterviewer()
		{
			$arrHrs = array();
			/* Modified the query as the designations for HR will be derived from interview settings module
			 * 
			 * $query = "select id as interviewer_id,name as interviewer_name from pms_employee where designation IN(19,20,26,22)";*/

			include_once("class.interview_settings.php");
			$objInterviewSettings = new interview_settings();
			
			$arrInterviewSettings = $objInterviewSettings->fnGetInterviewSettings();
			
			$interviewer_designations = '0';
			if(isset($arrInterviewSettings["interviewer_designations"]) && trim($arrInterviewSettings["interviewer_designations"]) != "")
				$interviewer_designations = $arrInterviewSettings["interviewer_designations"];

			$query = "select id as interviewer_id,name as interviewer_name from pms_employee where designation IN($interviewer_designations) and status='0'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrHrs[] = $this->fetchrow();
				}
			}
			return $arrHrs;
		}

		function fnGetAllCandidateListByStatus()
		{
			//echo '<pre>'; print_r($_SESSION);
			$arrRCTDivisionValues = array();
			$query = "SELECT reg . * , des . * , des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id WHERE reg.recommend_om_round = '1' and reg.recommend_om = '".$_SESSION['id']."' and reg.om_status IN(0,3) and final_hr_status  = '0'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}

		function fnGetAllCandidateListBySta()
		{
			//echo '<pre>'; print_r($_SESSION);
			$arrRCTDivisionValues = array();
			$query = "SELECT reg . * , des . * , des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id WHERE reg.recommend_om_round = '1' and reg.recommend_om = '".$_SESSION['id']."' and reg.om_status = '0'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}

		function fnGetAllCandidateListOM()
		{
			//echo '<pre>'; print_r($_SESSION);
			$arrRCTDivisionValues = array();
			$query = "SELECT reg . * , des . * ,emp1.name AS mana_name, des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id LEFT JOIN pms_employee AS emp1 ON reg.recommend_om = emp1.id WHERE reg.recommend_om_round = '1' and reg.om_status IN(0) and reg.final_hr_status = '0'";

			$this->query($query);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}

		function fnUpdateOPMComments($arrPostData,$sessId)
		{
			//echo '<pre>'; print_r($arrPostData); 
			$today = date("Y-m-d H:i:s");
			if($arrPostData['status'])
			{
				$query1 = "update pms_user_registration set om_status = '".$arrPostData['status']."',om_reasign_flag = '0' where id = '".$arrPostData['id']."'";
				$this->query($query1);
			}
				//die;	
			
			$arrNewRecords = array("status"=>$arrPostData['status'],"comments"=>$arrPostData['opmcomment'],"cand_id"=>$arrPostData['id'],"ops_id"=>$sessId,"salary_offered"=>$arrPostData['salary_offered'],"exp_date_of_joining"=>$arrPostData['exp_date_of_joining'],"teamleader_by_manager"=>$arrPostData['teamleader_by_manager'],"shift_timning_by_manager"=>$arrPostData['shift_timning_by_manager'],"date"=>$today);
			$this->insertArray('pms_rct_opm_comments',$arrNewRecords);
			return true;
			
			
		}

		function fnGetOpsCommentsById($id,$sessId)
		{
			$table_id = '';
			$arrOpsComments = array();
			$query = "SELECT max( id ) AS maxId FROM pms_rct_opm_comments WHERE cand_id='$id' GROUP BY cand_id";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$table_id = $this->f('maxId');
				}
			}

			$sql = "SELECT status as ops_status ,comments as ops_comments,ops_id as ops_id,salary_offered as salary_offered,exp_date_of_joining as exp_date_of_joining,teamleader_by_manager as teamleader_by_manager,shift_timning_by_manager as shift_timning_by_manager FROM `pms_rct_opm_comments` where id='$table_id'";

			$this->query($sql);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrOpsComments = $this->fetchrow();
				}
			}
			//echo '<pre>'; print_r($arrOpsComments); die;
			return $arrOpsComments;
		}

		function fnGetOpsAllCommentsById($id)
		{
			
			$arrOpsComments = array();
			$query = "SELECT emp1.name AS teamleader_name,com.exp_date_of_joining as exp_date_of_joining,com.date as om_date,date_format(com.date,'%d-%m-%Y') as om_decision_date, shift.title AS shift_title, com.status AS ops_status, com.comments AS ops_comments, emp.name AS ops_name, com.salary_offered AS sal_offer, date_format( com.exp_date_of_joining, '%d-%m-%Y' ) AS exp_join,date_format( com.exp_date_of_joining, '%Y-%m-%d' ) AS expect_join, com.teamleader_by_manager AS allocate_teamleader, com.shift_timning_by_manager AS shift_time FROM `pms_rct_opm_comments` AS com LEFT JOIN pms_employee AS emp ON com.ops_id = emp.id LEFT JOIN pms_employee AS emp1 ON com.`teamleader_by_manager` = emp1.id LEFT JOIN pms_shift_times AS shift ON com.shift_timning_by_manager = shift.id WHERE com.cand_id = '$id'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrOpsComments[] = $this->fetchrow();
				}
			}
			return $arrOpsComments;
		}

		function fnUpdateFinalHrStatus($arrPostData)
		{
			$finalArray = array();
			$today = date("Y-m-d H:i:s");
			//echo 'helo<pre>'; print_r($arrPostData);  die; 
			if($arrPostData['om_reasign'] == '1')
			{
				$arrPostData['om_status'] = '0';
				//echo $query = "update `pms_user_registration` set om_reasign_flag = '1',om_status='0',final_hr_status='0',recommend_om='".$arrPostData['recommend_om']."' where id='".$arrPostData['id']."'";
				//$this->query($query);
				$this->updateArray('pms_user_registration',$arrPostData);
				/*$query = "update `pms_user_registration` set om_reasign_flag = '1'";
				$this->query($query);

				$query1 = "SELECT max(id) as maxId FROM pms_rct_opm_comments where cand_id = '".$arrPostData['id']."' GROUP BY id";
				$this->query($query1);
				
				if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$id = $this->f('maxId');
					}
				}
				//echo 'hello'.$id; print_r($_SESSION);
				$query123 = "update pms_rct_opm_comments set user_des_id = '".$_SESSION['designation']."',user_id = '".$_SESSION['id']."',user_type = '".$_SESSION['usertype']."', 	date_reassign = '".$today."',active = '1' ";
				$this->query($query123);
				return true;*/
			}
			else
			{
				$checkPreviousStatus = $this->fnCheckPreviousStats($arrPostData['id']);
				//echo '<pre>'; echo $arrPostData['id']; print_r($arrPostData); die;
				//echo $checkPreviousStatus; die;
				if($checkPreviousStatus != $arrPostData['final_hr_status'])
				{
					$this->fnUpdateStatusUpdate($arrPostData['final_hr_status'],$arrPostData['id']);
				}
				
				$finalArray['final_hr_remark_date'] = $today;
				$finalArray['final_hr_comment_by'] = $_SESSION['id'];

				$finalArray['id'] = $arrPostData['id'];
				$finalArray['final_hr_salary_offered'] = $arrPostData['final_hr_salary_offered'];
				$finalArray['final_hr_exp_date_of_joining'] = $arrPostData['final_hr_exp_date_of_joining'];
				$finalArray['final_hr_teamleader_by_manager'] = $arrPostData['final_hr_teamleader_by_manager'];
				$finalArray['final_hr_shift_timning_by_manager'] = $arrPostData['final_hr_shift_timning_by_manager'];

				/*if($arrPostData['isjoin'] == '1')
				{
					$this->fnGetCandidateAndSaveInEmployee($arrPostData['id']);
				}*/

				//echo '<pre>'; print_r($arrPostData);die;
				$query = "update pms_user_registration set final_hr_remark_date = date_format('$today','%Y-%m-%d'),final_hr_status = '".mysql_real_escape_string($arrPostData['final_hr_status'])."',final_hr_remarks = '".mysql_real_escape_string($arrPostData['final_hr_remarks'])."',final_hr_comment_by = '".$_SESSION['id']."',final_hr_salary_offered = '".mysql_real_escape_string($arrPostData['salary_offered'])."',final_hr_exp_date_of_joining = date_format('".$arrPostData['exp_date_of_joining']."','%Y-%m-%d'),final_hr_teamleader_by_manager = '".mysql_real_escape_string($arrPostData['teamleader_by_manager'])."',final_hr_shift_timning_by_manager = '".mysql_real_escape_string($arrPostData['shift_timning_by_manager'])."',om_reasign_flag = '".mysql_real_escape_string($arrPostData['om_reasign'])."' where id = '".mysql_real_escape_string($arrPostData['id'])."' ";
				$this->query($query);
				$this->updateArray('pms_user_registration',$finalArray);
				
				return true;
			}
		}

		function fnUpdateStatusUpdate($status,$candId)
		{
			//echo '<br>status:'.$status; die;
			$arrPostData = array();
			$date = date('Y-m-d H:i:s');
			$arrPostData['cand_id'] = $candId;
			$arrPostData['status'] = $status;
			$arrPostData['date'] = $date;
			//echo '<pre>'; print_r($arrPostData); die;
			$this->insertArray('pms_rct_status',$arrPostData);
			return true;
		}

		function fnCheckPreviousStats($canId)
		{
			//echo '$canId'.$canId;
			$id = '';
			$query = "select  final_hr_status from pms_user_registration where id = '$canId'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$id = $this->f('final_hr_status');
				}
			}
			return $id;
		}

		function fnGetCandidateAndSaveInEmployee($eid)
		{
			$curDate = date('Y-m-d H:i:s');
			$currentDate = date('Y-m-d');
			
			$query123 = "update pms_user_registration set isjoin = '1' where id = '$eid'"; 
			$this->query($query123);
		
			$arrCandidateDetails = array();
			$query = "select reg.*,om_com.*,om_com.salary_offered as manager_sal_off,om_com.exp_date_of_joining as man_exp_date_joining from  pms_user_registration as reg left join pms_rct_opm_comments as om_com on reg.recommend_om = om_com.ops_id where reg.id = '$eid' and om_com.cand_id = '$eid'";
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrCandidateDetails = $this->fetchrow();
				}
			}

			//echo '<pre>';print_r($arrCandidateDetails);  die;
			if($arrCandidateDetails)
			{
				if($arrCandidateDetails["final_hr_teamleader_by_manager"] != "" && $arrCandidateDetails["final_hr_teamleader_by_manager"] != "0")
				{
					$teamleader_final = $arrCandidateDetails["final_hr_teamleader_by_manager"];
				}
				else
				{
					$teamleader_final = $arrCandidateDetails["teamleader_by_manager"];
				}

				if($arrCandidateDetails["final_hr_teamleader_by_manager"] != "" && $arrCandidateDetails["final_hr_teamleader_by_manager"] != "0")
				{
					$shift_final = $arrCandidateDetails["final_hr_shift_timning_by_manager"];
				}
				else
				{
					$shift_final = $arrCandidateDetails["shift_timning_by_manager"];
				}
				
				if($arrCandidateDetails["final_hr_salary_offered"] != "" && $arrCandidateDetails["final_hr_salary_offered"] != "0")
				{
					$salary_offered_fin = $arrCandidateDetails["final_hr_salary_offered"];
				}
				else
				{
					$salary_offered_fin = $arrCandidateDetails["manager_sal_off"];
				}
				
				if($arrCandidateDetails["final_hr_salary_offered"] != "" && $arrCandidateDetails["final_hr_salary_offered"] != "0")
				{
					$salary_offered_fin = $arrCandidateDetails["final_hr_salary_offered"];
				}
				else
				{
					$salary_offered_fin = $arrCandidateDetails["manager_sal_off"];
				}
				
				//echo 'hello'.$shift_final; die;
				
				$arrNewEmployee = array("name" => $arrCandidateDetails['name'],"address" => $arrCandidateDetails['address'],"designation"=>$arrCandidateDetails['des_id'],"current_address"=>$arrCandidateDetails['comm_address'],"official_email" => $arrCandidateDetails['email'],"dob" => $arrCandidateDetails['dob'],"contact" => $arrCandidateDetails['phnumber'],"cand_id" => $arrCandidateDetails['id'],"qualification" => $arrCandidateDetails['graduation_degree'],"experience" => $arrCandidateDetails['totExperience'],"teamleader" => $teamleader_final,"shiftid" => $shift_final,"status" => "0","created_date"=>$curDate,"date_of_joining"=>$curDate,"start_ctc"=>$salary_offered_fin,"location"=>$arrCandidateDetails['area'],"city"=>$arrCandidateDetails['city'],"zip"=>$arrCandidateDetails['zip']);
				

				//echo 'hello1<pre>'; print_r($arrNewEmployee); die;
				$this->insertArray('pms_employee',$arrNewEmployee);

				/* Insert Hired status for the candidate */
				$this->fnUpdateStatusUpdate(9,$eid);

				include_once('includes/class.designation.php');
				include_once('includes/class.rct_division.php');
				include_once('includes/class.shifts.php');

				$objDesignation = new designations();
				$objRctDivision = new rct_division();
				$objShift = new shifts();

				$designation_emp = $objDesignation->fnGetDesNameById($arrCandidateDetails['des_id']);

				$department_emp = $objRctDivision->fnGetDivisionNameById($arrCandidateDetails['dev_id']);
				
				$department_emp = $department_emp;

				$shiftName = $objShift->fnGetShiftTimes($shift_final);
				
				$Subject = 'Login Id for New Joinee';

				$content = "Dear Team, <br /><br />";
				$content .= "Kindly prepare the login id&prime;s as per the below mentioned details:<br/>";
				$content .= "";
				$content .= "<table cellspacing='2' cellpadding='2' bgcolor='#E6E6E6'>";

				$content .= "<tr bgcolor='#FFFFFF'><td><b>Name of the Team Member :</b></td><td><b>Date Of Joining</b></td><td><b>Designation</b></td><td><b>Department</b></td><td><b>Shift Timings</b></td></tr>";
				
				$content .= "<tr bgcolor='#FFFFFF'><td>".$arrCandidateDetails['name']."</td><td>".$currentDate."</td><td>".$designation_emp."</td><td>".$department_emp."</td><td>".$shiftName."</td></tr></table>";
				
				
				$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
				//echo '<br>'.$content.'<br>';
				//sendmail('hr@transformsolution.net',$Subject,$content);
				sendmail('itsupport@transformsolution.net',$Subject,$content);

				$last_id = mysql_insert_id();

				$insertDocumentDetails = $this->fnInsertDocumentsById($last_id,$eid);
			}
			return true;
		}


		function fnInsertDocumentsById($last_id,$eid)
		{
			//echo $last_id;
			$document_details = array();
			$documents = array();
			$query = "select * from `pms_candidate_document_details` where candid = '$eid'";
			$this->query($query);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$document_details[] = $this->fetchrow();
				}
			}
			//echo '<pre>'; print_r($document_details); die;
			if(count($document_details) > 0)
			{
				foreach($document_details as $docs)
				{
					$documents['userid'] =  $last_id;
					$documents['photos'] =  $docs['photos'];
					$documents['ssc'] =  $docs['ssc'];
					$documents['hsc'] =  $docs['hsc'];
					$documents['lc'] =  $docs['lc'];
					//$documents['degree'] =  $docs['degree'];
					$documents['degree_name'] =  $docs['degree'];
					//$documents['pg'] =  $docs['pg'];
					$documents['pg_name'] =  $docs['pg'];
					$documents['additional_cert'] =  $docs['additional_cert'];
					//$documents['id_proof'] =  $docs['id_proof'];
					//$documents['address_proof'] =  $docs['address_proof'];
					$documents['bgr'] =  $docs['bgr'];
					$documents['prv_comp_doc'] =  $docs['prv_comp_doc'];
					$documents['given_id_proof'] =  $docs['id_proof'];
					$documents['given_address_proof'] =  $docs['address_proof'];
					//$documents['extra_id_proof'] =  $docs['extra_id_proof'];
					$documents['given_extra_id_proof'] =  $docs['extra_id_proof'];
				}
				//echo '<pre>'; print_r($documents); die;
				$this->insertArray('pms_document_details',$documents);
			}
		}
		function fnCheckEmployeeExistence($id)
		{
			$query = "select id from pms_employee where cand_id = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$emp_id = $this->f('id');
				}
			}
			return $emp_id;
		}

		function fnGetAllCandidateListForRCT()
		{
			$arrRCTDivisionValues = array();
			$query = "SELECT reg . * , des . * ,divi.title as divi_title,source.title as source_title, des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id LEFT JOIN pms_rct_division AS divi ON divi.id = reg.des_id LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}
		
		function fnGetAllRctSourceForRctReport()
		{
			$arrRCTDivisionValues = array();
			$query = "SELECT reg.rctsource,count(reg.id) as count_rct_source, if( rctsource = '0', 'Employee Reference', source.title ) AS source_name FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id LEFT JOIN pms_rct_division AS divi ON divi.id = reg.des_id LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource  GROUP BY reg.rctsource";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}
		
		function fnGetAllCandidateListForMonthlyRCT()
		{
			$year = date('Y');
			$month = date('m');
			$arrRCTDivisionValues = array();
			
			$arrMaxIds = array();
			$strIds = '0';
			$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrMaxIds[] = $this->f("maxId");
				}
			}

			if(count($arrMaxIds) > 0)
				$strIds = implode(",", $arrMaxIds);
			
			$query = "SELECT reg . * , des . * ,divi.title as divi_title,source.title as source_title, des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name,stat.status FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id LEFT JOIN pms_rct_division AS divi ON divi.id = reg.dev_id LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource  inner join pms_rct_status as stat on (reg.id = stat.cand_id and stat.id in($strIds)) WHERE date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}
		
		function fnGetAllCandidateListForMonthlyResourceCal()
		{
			$year = date('Y');
			$month = date('m');
			$arrRCTDivisionValues = array();
			$query = "SELECT reg.rctsource,count(reg.id) as count_rct_source, if( rctsource = '0', 'Employee Reference', source.title ) AS source_name FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id LEFT JOIN pms_rct_division AS divi ON divi.id = reg.des_id LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource  GROUP BY reg.rctsource";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}

		function fnGetAllCandidateByMonth($month,$year,$status)
		{
			//echo '<br>status'.$status;
			//echo $month.'----'.$year;
			$arrRCTDivisionValues = array();
			
			//$query = "SELECT reg . * , des . * ,date_format(reg.date,'%d-%m-%Y') as reg_date,divi.title as divi_title,source.title as source_title, des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name,des.title as des_title FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id LEFT JOIN pms_rct_division AS divi ON divi.id = reg.dev_id LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource WHERE date_format( reg.date, '%m-%Y' ) = '".$month.'-'.$year."'";

			if($status == '')
			{
				 $arrMaxIds = array();
				 $strIds = '0';
				 $sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) <= '".$year.'-'.$month."' GROUP BY cand_id";
				 $this->query($sSQL);
				 if($this->num_rows() > 0)
				 {
					 while($this->next_record())
					 {
						 $arrMaxIds[] = $this->f("maxId");
					 }
				 }
				 
				 if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				 $query = "SELECT reg.* , stat.* , des.* ,date_format(reg.date,'%d-%m-%Y') as reg_date,divi.id as div_id,divi.title as divi_title,source.title as source_title, des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name,des.title as des_title FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id LEFT JOIN pms_rct_division AS divi ON divi.id = reg.dev_id LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) WHERE ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' and stat.status in(2,3,5,7,4) ) or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.id ORDER BY reg.date DESC";
			}
			else
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) <= '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$hrStatus = $status;
				if($status == 9)
					$hrStatus = 4;
				
				$query = "SELECT reg . * , stat.* , date_format(reg.date,'%d-%m-%Y') as reg_date,divi.title as divi_title,divi.id as div_id,source.title as source_title, des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name,des.title as des_title FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id LEFT JOIN pms_rct_division AS divi ON divi.id = reg.dev_id LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds) and stat.status = '$status') WHERE stat.status = '$status' and reg.final_hr_status in (0,'$hrStatus') and  ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' ) or ( date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' and stat.status  = '$status')) GROUP BY reg.id";
			}

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}
		
		function fnGetAllRctSourceForRctReportWithStatus($month,$year,$status)
		{
			//echo '<br>status'.$status;
			//echo $month.'----'.$year;
			$arrRCTDivisionValues = array();

			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) <= '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				 $query = "SELECT reg.rctsource,count(reg.id) as count_rct_source, if( rctsource = '0', 'Employee Reference', source.title ) AS source_name  FROM `pms_user_registration` AS reg  LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) WHERE ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' and stat.status in(2,3,5,7,4) ) or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.rctsource";
			}
			else
			{
				//$query = "SELECT reg . * , stat.* , des . * ,date_format(reg.date,'%d-%m-%Y') as reg_date,divi.title as divi_title,divi.id as div_id,source.title as source_title, des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name,des.title as des_title FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id LEFT JOIN pms_rct_division AS divi ON divi.id = reg.dev_id LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in(SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id)) WHERE stat.status = '$status' and  ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.id";

				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);

				$query = "SELECT reg.rctsource,count(reg.id) as count_rct_source, if( rctsource = '0', 'Employee Reference', source.title ) AS source_name FROM `pms_user_registration` AS reg LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) WHERE stat.status = '$status' and  ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.rctsource";
			}

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}
		
		function fnGetAllCandidateForMonthlyReport($month,$year,$status)
		{
			$arrRCTDivisionValues = array();
			
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);

				$query = "SELECT reg . * , stat.* , des . * ,date_format(reg.date,'%d-%m-%Y') as reg_date,divi.id as div_id,divi.title as divi_title,source.title as source_title, des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name,des.title as des_title FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id LEFT JOIN pms_rct_division AS divi ON divi.id = reg.dev_id LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource LEFT join pms_rct_status as stat on (reg.id = stat.cand_id and stat.id in($strIds)) WHERE date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id ORDER BY reg.date DESC";
				
				//echo $query = "SELECT reg . * , stat.* , des . * ,date_format(reg.date,'%d-%m-%Y') as reg_date,divi.id as div_id,divi.title as divi_title,source.title as source_title, des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name,des.title as des_title FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id LEFT JOIN pms_rct_division AS divi ON divi.id = reg.dev_id LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource left join pms_rct_status as stat on (reg.id = stat.cand_id and stat.id in (SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id)) WHERE date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id ORDER BY reg.date DESC";
			}
			else
			{
				//$query = "SELECT reg . * , stat.* , des . * ,date_format(reg.date,'%d-%m-%Y') as reg_date,divi.title as divi_title,divi.id as div_id,source.title as source_title, des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name,des.title as des_title FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id LEFT JOIN pms_rct_division AS divi ON divi.id = reg.dev_id LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in(SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id)) WHERE stat.status = '$status' and  date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
				
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);

				$query = "SELECT reg . * , stat.* , des . * ,date_format(reg.date,'%d-%m-%Y') as reg_date,divi.title as divi_title,divi.id as div_id,source.title as source_title, des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name,des.title as des_title FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id LEFT JOIN pms_rct_division AS divi ON divi.id = reg.dev_id LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource inner join pms_rct_status as stat on  reg.id = stat.cand_id WHERE stat.id in($strIds) and stat.status = '$status' and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}
		
		function fnGetAllCandidateForMonthlyReportwithStatus($month,$year,$status)
		{
			$arrRCTDivisionValues = array();
			
			if($status == '')
			{
				$query = "SELECT reg.rctsource,count(reg.id) as count_rct_source, if( rctsource = '0', 'Employee Reference', source.title ) AS source_name FROM `pms_user_registration` AS reg LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource left join pms_rct_status as stat on  reg.id = stat.cand_id WHERE date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.rctsource";
			}
			else
			{
				//$query = "SELECT reg . * , stat.* , des . * ,date_format(reg.date,'%d-%m-%Y') as reg_date,divi.title as divi_title,divi.id as div_id,source.title as source_title, des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name,des.title as des_title FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id LEFT JOIN pms_rct_division AS divi ON divi.id = reg.dev_id LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in(SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id)) WHERE stat.status = '$status' and  date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";

				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);

				$query = "SELECT reg.rctsource,count(reg.id) as count_rct_source, if( rctsource = '0', 'Employee Reference', source.title ) AS source_name FROM `pms_user_registration` AS reg LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) WHERE stat.status = '$status' and  date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.rctsource";
			}

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}

		
		
		function fnGetAllOpsComments($id)
		{
			$arrOpsComments = array();
			$query = "SELECT com.status as ops_status ,com.comments as ops_comments,emp.name as ops_name FROM `pms_rct_opm_comments` as com left join pms_employee as emp on com.ops_id = emp.id where com.cand_id='$id'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrOpsComments[] = $this->fetchrow();
				}
			}
			return $arrOpsComments;
		}
		function fnGetAllOperationsComments($id)
		{
			$arrOpsComments = array();
			$query = "SELECT com.status as ops_status ,com.comments as ops_comments,emp.name as ops_name FROM `pms_rct_opm_comments` as com left join pms_employee as emp on com.ops_id = emp.id where com.cand_id='$id' and com.ops_id != '".$_SESSION['id']."'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrOpsComments[] = $this->fetchrow();
				}
			}
			return $arrOpsComments;
		}
		
		function fnGetAllEmpForTestRound()
		{
			$arrEmpForTest = array();
			$query = "SELECT t1.*,t2.*,DATE_FORMAT( t1.date, '%d-%m-%Y' ) AS reg_date,t1.id as reg_id FROM `pms_user_registration` as t1 left join `pms_designation` AS t2 ON t1.des_id = t2.id WHERE t1.`recommend_test` = '1'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmpForTest[] = $this->fetchrow();
				}
			}
			return $arrEmpForTest;
		}
		
		function fnGetAllEmpForTestRoundPending()
		{
			$arrEmpForTest = array();
			//$query = "SELECT t1.*,t2.*,DATE_FORMAT( t1.date, '%d-%m-%Y' ) AS reg_date,t1.id as reg_id FROM `pms_user_registration` as t1 left join `pms_designation` AS t2 ON t1.des_id = t2.id WHERE t1.recommend_test = '1' and t1.recommend_om_round = '0' and t1.final_hr_status = '0' or (t1.recommend_om_round = '1' and  t1.om_status = '0')";
			
			$query = "SELECT t1.*,t2.*,DATE_FORMAT( t1.date, '%d-%m-%Y' ) AS reg_date,t1.id as reg_id FROM `pms_user_registration` as t1 left join `pms_designation` AS t2 ON t1.des_id = t2.id WHERE t1.recommend_test = '1' and t1.recommend_om_round = '0'";
			
			//reg.recommend_hr_round = '1' and reg.recommend_test = '0' or (reg.recommend_test = '1' and reg.recommend_om_round ='0' and  reg.final_hr_status = '0' )
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmpForTest[] = $this->fetchrow();
				}
			}
			return $arrEmpForTest;
		}

		function fnGetEmpTestMarksById($id)
		{
			$arrEmpForTest = array();
			$query = "SELECT * FROM `pms_user_registration` WHERE `recommend_test` = '1'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmpForTest[] = $this->fetchrow();
				}
			}
			return $arrEmpForTest;
		}

		function fnGetPendingCandidateListByManagerStatus()
		{
			$arrRCTDivisionValues = array();
			//echo $query = "SELECT reg . * , des . * , des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id WHERE reg.recommend_om_round = '1' and reg.recommend_om = '$id'";

			$query = "SELECT t1.id as cand_id,t1.name as cand_name,t2.title as des_title,date_format(t1.date,'%d-%m-%Y') as registration_date FROM pms_user_registration AS t1 left join `pms_designation` AS t2 ON t1.des_id = t2.id  WHERE t1.recommend_om_round = '1' and t1.om_status = '1' and t1.om_reasign_flag != '2' and t1.final_hr_status  IN(0,3,5) or ( t1.om_status = '3' AND t1.final_hr_status = '0')";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}
		
		function fnGetAllCandidateListByManagerStatus()
		{
			$arrRCTDivisionValues = array();
			//echo $query = "SELECT reg . * , des . * , des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id WHERE reg.recommend_om_round = '1' and reg.recommend_om = '$id'";

			$query = "SELECT t1.id as cand_id,t1.name as cand_name,t2.title as des_title,date_format(t1.date,'%d-%m-%Y') as registration_date FROM pms_user_registration AS t1 left join `pms_designation` AS t2 ON t1.des_id = t2.id  WHERE t1.recommend_om_round = '1' ";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTDivisionValues[] = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}
		
		function fnGetOpsComments($cand_id,$man_id)
		{
			$om_comment = '';
			$query = "SELECT comments as om_comment FROM `pms_rct_opm_comments` WHERE `cand_id` = '$cand_id' and ops_id = '$man_id'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$om_comment = $this->f('om_comment');
				}
			}
			return $om_comment;
		}
		function fnGetOpsComments1($cand_id,$man_id)
		{
			$om_exp_date = '';
			$query = "SELECT date_format(exp_date_of_joining,'%d-%m-%y') as om_exp_date_of_joining FROM `pms_rct_opm_comments` WHERE `cand_id` = '$cand_id' and ops_id = '$man_id' ORDER BY id DESC LIMIT 0 , 1";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$om_exp_date = $this->f('om_exp_date_of_joining');
				}
			}
			return $om_exp_date;
		}
		
		function fnGetAllRctSource()
		{
			$arrRctSource = array();
			$query = "SELECT * FROM `pms_rct_source`";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRctSource[] = $this->fetchrow();
				}
			}
			return $arrRctSource;
		}
		
		function fnGetAllRctDivision()
		{
			$arrRctDivision = array();
			$query = "SELECT * FROM `pms_rct_division`";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRctDivision[] = $this->fetchrow();
				}
			}
			return $arrRctDivision;
		}

		function fnGetAllRctSourceRecordsCount($source,$month,$year,$status)
		{
			$arrValues = array();

			//$query = "SELECT reg.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id left join pms_rct_source as source on reg.rctsource = source.id WHERE  reg.rctsource = '".$source."' and reg.isactive = '1' and ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' and stat.status in(2,5,7) ) or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.id";
			if($status == '' )
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) <= '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , des . * ,date_format(reg.date,'%d-%m-%Y') as reg_date,divi.id as div_id,divi.title as divi_title,source.title as source_title, des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name,des.title as des_title FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id LEFT JOIN pms_rct_division AS divi ON divi.id = reg.dev_id LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) WHERE reg.rctsource='".$source."' and reg.isactive = '1' and ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' and stat.status in(2,3,5,7,4) ) or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.id ORDER BY reg.date DESC";

				
				//$query = "SELECT reg.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id left join pms_rct_source as source on reg.rctsource = source.id WHERE  reg.rctsource = '".$source."' and reg.isactive = '1' and ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' and stat.status in(2,3,5,7,4) ) or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.id";
			}
			else
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_source as source on reg.rctsource = source.id WHERE  reg.rctsource = '".$source."' and reg.isactive = '1' and  stat.status = '$status' and  ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.id";
			}
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrValues[] = $this->fetchrow();
				}
			}
			$count = count($arrValues);
			return $count;
		}
		
		function fnGetAllRctSourceRecordsCountMonthlyReport($source,$month,$year,$status)
		{
			$arrValues = array();

			//$query = "SELECT reg.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id left join pms_rct_source as source on reg.rctsource = source.id WHERE  reg.rctsource = '".$source."' and reg.isactive = '1' and ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' and stat.status in(2,5,7) ) or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.id";
			if($status == '' )
			{
				$query = "SELECT reg.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id left join pms_rct_source as source on reg.rctsource = source.id WHERE  reg.rctsource = '".$source."' and reg.isactive = '1' and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_source as source on reg.rctsource = source.id WHERE  reg.rctsource = '".$source."' and reg.isactive = '1' and  stat.status = '$status' and  (date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') GROUP BY reg.id";
			}
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrValues[] = $this->fetchrow();
				}
			}
			$count = count($arrValues);
			return $count;
		}
		
		function fnGetAllEmployeeReference($source,$month,$year,$status)
		{
			$arrValues = array();

			//$query = "SELECT reg.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id left join pms_rct_source as source on reg.rctsource = source.id WHERE  reg.rctsource = '".$source."' and reg.isactive = '1' and ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' and stat.status in(2,5,7) ) or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.id";
			if($status == '' )
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) <= '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				 $query = "SELECT reg . * FROM `pms_user_registration` AS reg    inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) WHERE reg.rctsource = '".$source."' and reg.isactive = '1' and ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' and stat.status in(2,3,5,7,4) ) or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.id ORDER BY reg.date DESC";
				 
				//$query = "SELECT reg.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id  WHERE  reg.rctsource = '".$source."' and reg.isactive = '1' and ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' and stat.status in(2,3,5,7,4) ) or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.id";
			}
			else
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on (reg.id = stat.cand_id and stat.id in($strIds)) WHERE  reg.rctsource = '".$source."' and reg.isactive = '1' and  stat.status = '$status' and  ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.id";
			}
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrValues[] = $this->fetchrow();
				}
			}
			$count = count($arrValues);
			return $count;
		}
		
		function fnGetAllEmployeeReferenceMonthlyReport($source,$month,$year,$status)
		{
			$arrValues = array();

			//$query = "SELECT reg.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id left join pms_rct_source as source on reg.rctsource = source.id WHERE  reg.rctsource = '".$source."' and reg.isactive = '1' and ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' and stat.status in(2,5,7) ) or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.id";
			if($status == '' )
			{
				$query = "SELECT reg.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id  WHERE  reg.rctsource = '".$source."' and reg.isactive = '1' and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on (reg.id = stat.cand_id and stat.id in($strIds)) WHERE  reg.rctsource = '".$source."' and reg.isactive = '1' and  stat.status = '$status' and  (date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') GROUP BY reg.id";
			}
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrValues[] = $this->fetchrow();
				}
			}
			$count = count($arrValues);
			return $count;
		}
		
		function fnGetAllRctDivisionRecordsCount($div,$month,$year,$status)
		{
			$arrCount = array();
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) <= '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , des . * ,date_format(reg.date,'%d-%m-%Y') as reg_date,divi.id as div_id,divi.title as divi_title,source.title as source_title, des.title AS des_title, DATE_FORMAT( reg.date, '%d-%m-%Y' ) AS reg_date,reg.id as cand_id,emp.name as emp_name,des.title as des_title FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id LEFT JOIN pms_rct_division AS divi ON divi.id = reg.dev_id LEFT JOIN pms_rct_source AS source ON source.id = reg.rctsource inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) WHERE  reg.dev_id = '$div' and reg.isactive = '1' and  ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' and stat.status in(2,3,5,7,4) ) or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.id ORDER BY reg.date DESC";

				
				//$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id left join pms_rct_division as division on reg.dev_id = division.id WHERE reg.dev_id = '$div' and reg.isactive = '1' and ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' and stat.status in(2,3,5,7,4) ) or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.id";
			}
			else
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) <= '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE reg.dev_id = '$div' and reg.isactive = '1' and  stat.status = '$status' and  ((date_format( reg.date, '%Y-%m' ) <= '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' ))  GROUP BY reg.id";
			}

			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrCount[] = $this->fetchrow();
				}
			}
			$count = count($arrCount);
			return $count;
		}
		
		function fnGetAllRctDivisionRecordsCountMonthlyReport($div,$month,$year,$status)
		{
			$arrCount = array();
			if($status == '')
			{
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id left join pms_rct_division as division on reg.dev_id = division.id WHERE reg.dev_id = '$div' and reg.isactive = '1' and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else
			{
				//$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in(SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id)) left join pms_rct_division as division on reg.dev_id = division.id WHERE reg.dev_id = '$div' and reg.isactive = '1' and stat.status = '$status' and date_format(reg.date, '%Y-%m') = '".$year.'-'.$month."' GROUP BY reg.id";
				
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE reg.dev_id = '$div' and reg.isactive = '1' and  stat.status = '$status' and  date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."'  GROUP BY reg.id";
			}
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrCount[] = $this->fetchrow();
				}
			}
			$count = count($arrCount);
			return $count;
		}
		
		function fnGetAllShortlistedCandidatesCount($div,$month,$year,$status)
		{
			$countArray = array();
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) <= '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(4)  and  date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' ) GROUP BY reg.id";
			}
			else if($status == '4')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) <= '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(4)  and  date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' ) GROUP BY reg.id";
			}
			else
			{
				return 0;
			}
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$countArray[] = $this->fetchrow();
				}
			}
			$count = count($countArray);
			return $count;
		}
		
		function fnGetAllHiredCandidatesCount($div,$month,$year,$status)
		{
			$countArray = array();
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(9)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' ) GROUP BY reg.id";
			}
			else if($status == '9')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(9)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' ) GROUP BY reg.id";
			}
			else
			{
				return 0;
			}
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$countArray[] = $this->fetchrow();
				}
			}
			$count = count($countArray);
			return $count;
		}
		
		function fnGetAllShortlistedCandidatesCountMonthlyReport($div,$month,$year,$status)
		{
			$countArray = array();
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(4) and  date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else if($status == '4')
			{
				//$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in(SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(4) and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
				
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(4)  and  date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else
			{
				return 0;
			}
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$countArray[] = $this->fetchrow();
				}
			}
			$count = count($countArray);
			return $count;
		}
		
		function fnGetAllHiredCandidatesCountMonthlyReport($div,$month,$year,$status)
		{
			$countArray = array();
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(9) and  date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else if($status == '9')
			{
				//$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in(SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(4) and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
				
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(9)  and  date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else
			{
				return 0;
			}
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$countArray[] = $this->fetchrow();
				}
			}
			$count = count($countArray);
			return $count;
		}
		
		function fnGetAllRejectedCandidatesCount($div,$month,$year,$status)
		{
			$countArray = array();
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(6)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' ) GROUP BY reg.id";
			}
			else if($status == '6')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(6)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' ) GROUP BY reg.id";
			}
			else
			{
				return 0;
			}
			
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$countArray[] = $this->fetchrow();
				}
			}
			$count = count($countArray);
			return $count;
		}
		
		function fnGetAllRejectedCandidatesCountMonthlyReport($div,$month,$year,$status)
		{
			$countArray = array();
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(6) and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else if($status == '6')
			{
				//$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in(SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(6)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') GROUP BY reg.id";
				
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(6)  and  date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else
			{
				return 0;
			}
			
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$countArray[] = $this->fetchrow();
				}
			}
			$count = count($countArray);
			return $count;
		}
		
		function fnGetAllDeclinedCandidatesCount($div,$month,$year,$status)
		{
			$countArray = array();
			//$query = "SELECT count(id) as sourceCount FROM `pms_user_registration` WHERE (final_hr_status = '4' or (final_hr_status = 0 and om_status = 1)) and dev_id = '$source' and date_format( date, '%m-%Y' ) = '".$month."-".$year."'";

			//$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(1)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' ) GROUP BY reg.id";
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(1)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."') GROUP BY reg.id";
			}
			else if($status == '1')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(1)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' ) GROUP BY reg.id";
			}
			else
			{
				return 0;
			}
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$countArray[] = $this->fetchrow();
				}
			}
			$count = count($countArray);
			return $count;
		}
		function fnGetAllDeclinedCandidatesCountMonthlyReport($div,$month,$year,$status)
		{
			$countArray = array();
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(1) and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else if($status == '1')
			{
				//$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in(SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(1)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') GROUP BY reg.id";
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(1)  and  date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else
			{
				return 0;
			}
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$countArray[] = $this->fetchrow();
				}
			}
			$count = count($countArray);
			return $count;
		}
		
		function fnGetAllHoldCandidatesCount($div,$month,$year,$status)
		{
			$countArray = array();
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) <= '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(5)  and  date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' ) GROUP BY reg.id";
			}
			else if($status == '5')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(5)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' ) GROUP BY reg.id";
			}
			else
			{
				return 0;
			}
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$countArray[] = $this->fetchrow();
				}
			}
			$count = count($countArray);
			return $count;
		}

		function fnGetAllHoldCandidatesCountMonthlyReport($div,$month,$year,$status)
		{
			$countArray = array();
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(5) and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else if($status == '5')
			{
				//$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in(SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(5)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') GROUP BY reg.id";
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(5) and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else
			{
				return 0;
			}
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$countArray[] = $this->fetchrow();
				}
			}
			$count = count($countArray);
			return $count;
		}
		
		function fnGetAllTestCandidatesCount($div,$month,$year,$status)
		{
			$countArray = array();
			//$query = "SELECT count(id) as sourceCount FROM `pms_user_registration` WHERE (final_hr_status = '4' or (final_hr_status = 0 and om_status = 1)) and dev_id = '$source' and date_format( date, '%m-%Y' ) = '".$month."-".$year."'";

			//$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(7)  and  date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' ) GROUP BY reg.id";
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) <= '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(7)  and  date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' ) GROUP BY reg.id";
			}
			else if($status == '7')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(7)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' ) GROUP BY reg.id";
			}
			else
			{
				return 0;
			}
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$countArray[] = $this->fetchrow();
				}
			}
			$count = count($countArray);
			return $count;
		}
		
		
		function fnGetAllTestCandidatesCountMonthlyReport($div,$month,$year,$status)
		{
			$countArray = array();
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(7) and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else if($status == '7')
			{
				//$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in(SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(7) and date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') GROUP BY reg.id";
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(7) and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else
			{
				return 0;
			}
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$countArray[] = $this->fetchrow();
				}
			}
			$count = count($countArray);
			return $count;
		}
		function fnGetAllFRTCandidatesCount($div,$month,$year,$status)
		{
			$countArray = array();
			//$query = "SELECT count(id) as sourceCount FROM `pms_user_registration` WHERE (final_hr_status = '4' or (final_hr_status = 0 and om_status = 1)) and dev_id = '$source' and date_format( date, '%m-%Y' ) = '".$month."-".$year."'";

			//$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(2)  and  date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' ) GROUP BY reg.id";
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) <= '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(2)  and  date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' ) GROUP BY reg.id";
			}
			else if($status == '2')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(2)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' ) GROUP BY reg.id";
			}
			else
			{
				return 0; 
			}
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$countArray[] = $this->fetchrow();
				}
			}
			$count = count($countArray);
			return $count;
		}
		function fnGetAllFRTCandidatesCountMonthlyReport($div,$month,$year,$status)
		{
			$countArray = array();
			//$query = "SELECT count(id) as sourceCount FROM `pms_user_registration` WHERE (final_hr_status = '4' or (final_hr_status = 0 and om_status = 1)) and dev_id = '$source' and date_format( date, '%m-%Y' ) = '".$month."-".$year."'";

			//$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(2)  and  date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' ) GROUP BY reg.id";
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(2) and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else if($status == '2')
			{
				//$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in(SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(2)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' ) GROUP BY reg.id";
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(2)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else
			{
				return 0; 
			}
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$countArray[] = $this->fetchrow();
				}
			}
			$count = count($countArray);
			return $count;
		}
		
		function fnGetAllFutureCandidatesCount($div,$month,$year,$status)
		{
			$countArray = array();
			//$query = "SELECT count(id) as sourceCount FROM `pms_user_registration` WHERE (final_hr_status = '4' or (final_hr_status = 0 and om_status = 1)) and dev_id = '$source' and date_format( date, '%m-%Y' ) = '".$month."-".$year."'";

			//$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '3' and ( stat.status in(3)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' ) GROUP BY reg.id";
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) <= '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(3)  and  date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' ) GROUP BY reg.id";
			}
			else if($status == '3')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(3)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' ) GROUP BY reg.id";
			}
			else
			{
				return 0; 
			}
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$countArray[] = $this->fetchrow();
				}
			}
			$count = count($countArray);
			return $count;
		}
		
		function fnGetAllFutureCandidatesCountMonthlyReport($div,$month,$year,$status)
		{
			$countArray = array();
			//$query = "SELECT count(id) as sourceCount FROM `pms_user_registration` WHERE (final_hr_status = '4' or (final_hr_status = 0 and om_status = 1)) and dev_id = '$source' and date_format( date, '%m-%Y' ) = '".$month."-".$year."'";

			//$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '3' and ( stat.status in(3)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' ) GROUP BY reg.id";
			if($status == '')
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(3) and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else if($status == '3')
			{
				//$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in(SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and ( stat.status in(3)  and  date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') GROUP BY reg.id";
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
					
				$query = "SELECT reg . * , stat.* , division.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) left join pms_rct_division as division on reg.dev_id = division.id WHERE division.id = '$div' and reg.isactive = '1' and stat.status in(3) and date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			else
			{
				return 0; 
			}
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$countArray[] = $this->fetchrow();
				}
			}
			$count = count($countArray);
			return $count;
		}

		function fnGetAllJoiners()
		{
			$arrAllJoiners = array();
			//$query = "SELECT id,name FROM `pms_user_registration` WHERE om_status = '1' and final_hr_status = '4' and isjoin = '0'";
			
			$query = "SELECT user.id as user_id,user.name as user_name FROM `pms_user_registration` as user WHERE user.final_hr_status = '4' AND user.isjoin = '0'";
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

		/* Its only used for joinings.php page */
		/* Using for getting all details regarding the joiners */
		function fnGetAllJoinersForJoinings()
		{
			$arrAllJoiners = array();
			$query = "SELECT user.id as cand_id,user.recommend_om as rec_om,user.name as cand_name,date_format(user.final_hr_exp_date_of_joining,'%d-%m-%Y') as hr_exp_joining_date,date_format(user.expected_joining_date,'%d-%m-%Y') as cand_exp_joining_date,emp.name as teamLeader_name_hr,shifts.title as shift_title FROM `pms_user_registration` as user left join `pms_employee` as emp on user.final_hr_teamleader_by_manager = emp.id left join pms_shift_times as shifts on user.final_hr_shift_timning_by_manager = shifts.id  WHERE (user.final_hr_status = '4' OR (user.om_status = '1' AND user.final_hr_status = '0' )) AND user.isjoin = '0'";
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

		

		function fnGetAllCandidatesTotal($month,$year,$status)
		{
			$arrAllCandidates = array();
			if($status == '')
			{
				$query = "SELECT reg . * , stat.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id WHERE reg.isactive = '1' and  ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) <= '".$year.'-'.$month."' and stat.status in(2,3,5,7,4) ) or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.id";
			}
			else
			{
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);

				$query = "SELECT reg . * , stat.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) WHERE reg.isactive = '1' and  stat.status = '$status' and  ((date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') or ( date_format( stat.date, '%Y-%m' ) = '".$year.'-'.$month."' )) GROUP BY reg.id";
			}
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllCandidates[] =  $this->fetchrow();
				}
			}
			return count($arrAllCandidates);
		}
		
		function fnGetAllCandidatesMonthlyTotal($month,$year,$status)
		{
			$arrAllCandidates = array();
			if($status == '')
			{
				$query = "SELECT reg . * , stat.* FROM `pms_user_registration` AS reg left join pms_rct_status as stat on  reg.id = stat.cand_id WHERE reg.isactive = '1' and  (date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') GROUP BY reg.id";
			}
			else
			{
				//$query = "SELECT reg . * , stat.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in(SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id)) WHERE reg.isactive = '1' and  stat.status = '$status' and  (date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."') GROUP BY reg.id";
				
				$arrMaxIds = array();
				$strIds = '0';
				$sSQL = "SELECT max( id ) AS maxId FROM pms_rct_status WHERE date_format( date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY cand_id";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrMaxIds[] = $this->f("maxId");
					}
				}

				if(count($arrMaxIds) > 0)
					$strIds = implode(",", $arrMaxIds);
				
				$query = "SELECT reg . * , stat.* FROM `pms_user_registration` AS reg inner join pms_rct_status as stat on  (reg.id = stat.cand_id and stat.id in($strIds)) WHERE reg.isactive = '1' and  stat.status = '$status' and  date_format( reg.date, '%Y-%m' ) = '".$year.'-'.$month."' GROUP BY reg.id";
			}
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllCandidates[] =  $this->fetchrow();
				}
			}
			return count($arrAllCandidates);
		}
		
		function fnGetEmployeeNameById($id)
		{
			$name = '';
			$query = "select name from pms_employee where id = '$id'";
			$this->query($query);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$name =  $this->f("name");
				}
			}
			return $name;
		}
		function fnCheckFutureRoundConduct($cid)
		{
			$id = '';
			$query = "select id from pms_user_registration where id = '$cid' and recommend_om_round_date = '0000-00-00 00:00:00'";
			$this->query($query);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$id =  $this->f("id");
				}
			}
			//echo $id; die;
			return $id;
		}
		
		function fnCheckFutureRoundConductIQ($cid)
		{
			$id = '';
			$query = "select id from pms_user_registration where id = '$cid' and recommend_test = '0'";
			$this->query($query);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$id =  $this->f("id");
				}
			}
			//echo $id; die;
			return $id;
		}
		function fnCheckFinalRoundConduct($cid)
		{
			$id = '';
			$query = "select id from pms_user_registration where id = '$cid' and final_hr_remark_date = '0000-00-00 00:00:00'";
			$this->query($query);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$id =  $this->f("id");
				}
			}
			//echo $id; die;
			return $id;
		}
		
		function fnGetReferenceSourceName($rctsource)
		{
			$id = '';
			$query = "select title from pms_rct_source where id = '$rctsource'";
			$this->query($query);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$title =  $this->f("title");
				}
			}
			//echo $id; die;
			return $title;
		}

		function fnGetPendingInterviewCount()
		{
			$count = '0';
			$query = "select managers_designations from pms_interview_designations";
			$this->query($query);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$ids =  $this->f("managers_designations");
				}
			}
			if(isset($ids) && count($ids)>0)
			{
				$getIds = explode(',',$ids);
				if (in_array($_SESSION['designation'], $getIds))
				{
					$sql = "SELECT count(reg . id) as count FROM `pms_user_registration` AS reg LEFT JOIN `pms_designation` AS des ON reg.des_id = des.id left join pms_employee as emp on reg.interviewer = emp.id WHERE reg.recommend_om_round = '1' and reg.recommend_om = '".$_SESSION['id']."' and reg.om_status = '0'";
					$this->query($sql);
					if($this->num_rows() > 0)
					{
						if($this->next_record())
						{
							$count =  $this->f("count");
						}
					}
					return $count;
				}
				else
				{
				  return 0;
				}
			}
			else
			{
				return 0;
			}
		}

		function fnGetAllEmployeeForRctMail()
		{
			$arrAllEmployees = array();
			$query = "select name,email from pms_employee where `rct_mail_send` = '1' and `status` = '0'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllEmployees[] =  $this->fetchrow();
				}
			}
			//echo '<pre>'; print_r($arrAllEmployees); die;
			return $arrAllEmployees;
		}
	}
?>

<?php
include_once('db_mysql.php');
	class resignation extends DB_Sql
	{
		function __construct()
		{
		}
		function fnInsertResignation($arrEmployee)
		{
			//echo '<pre>'; print_r($arrEmployee);die;
			/* Include files */
			include_once("class.employee.php");
			include_once("class.designation.php");

			/* Create objects */
			$objEmployee = new employee();
			$objDesignation = new designations();

			/* Fetch details for the user designation */
			$arrDesignationInfo = $objDesignation->fnGetDesignationById($_SESSION['designation']);
			
			$arrHeads = $objEmployee->fnGetReportHeadHierarchy($_SESSION['id']);
			
			//echo '<pre>'; print_r($arrHeads);die;
			

			if(isset($arrHeads[$arrDesignationInfo["second_reporting_head"]]))
			{
				$secReportingHead = $arrHeads[$arrDesignationInfo["second_reporting_head"]];
				$arrEmployee['manager_id'] = $arrHeads[$arrDesignationInfo["second_reporting_head"]]["id"];
				if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
				{
					$fstReportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];
					$arrEmployee['teamleader_id'] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
				}
			}
			else
			{
				$fstReportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];
				$arrEmployee['manager_id'] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
			}
			
			//echo '<pre>'; print_r($arrEmployee); die;
			
			$arrEmployee['date_of_resignation'] = date('Y-m-d H:i:s');
			$lastInsertId = $this->insertArray('pms_resignation',$arrEmployee);
			return $lastInsertId;
		}
		function fnGetAllResignation()
		{
			$arrResignationValues = array();
			$query = "SELECT id,date_format(date_of_resignation,'%d-%m-%Y') as d_o_reg,date_format(last_working_date,'%d-%m-%Y') as l_w_date FROM `pms_resignation` where user_id = '".$_SESSION['id']."'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrResignationValues[] = $this->fetchrow();
				}
			}
			return $arrResignationValues;
		}

		function fnGetResignationById($id)
		{
			$curDate = date('Y-m-d H:i:s');
			$arrResignationValues = array();
			$query = "SELECT reg.id as reg_id,date_format(reg.date_of_resignation,'%Y-%m-%d') as dor,reg.comment_tl,reg.comment_manager,reg.teamleader_id,reg.manager_id,date_format(reg.date_of_resignation,'%d-%m-%Y') as d_o_reg,date_format(reg.last_working_date,'%d-%m-%Y') as l_w_date,reg.emp_reason as reason,reg.user_id as user_id,e.name as name,reg.status_tl as status,reg.status_manager as status_manager,reg.status_hr,reg.comment_hr,date_format(last_working_date,'%Y-%m-%d') as last_w_date, date_format(last_working_date_hr,'%Y-%m-%d') as last_w_date_hr,date_format(last_working_date_manager,'%Y-%m-%d') as last_w_date_man,date_format(last_working_date_manager,'%d-%m-%Y') as last_w_d_man,reg.comment_tl as com_teaml,reg.comment_manager as com_manage,DATEDIFF(reg.last_working_date_manager, reg.date_of_resignation) as diff, reg.notice_served_days, reg.manager_waived_paid,reg.mana_waived_paid_off_days,date_format(reg.exit_date,'%d-%m-%Y') as d_dol,DATEDIFF('$curDate', reg.date_of_resignation) as diff1 FROM `pms_resignation` as reg left join pms_employee as e on reg.user_id = e.id WHERE reg.`id` = '".mysql_real_escape_string($id)."' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrResignationValues = $this->fetchrow();
				}
			}
			return $arrResignationValues;
		}

		function fnGetResignationIdByName($title)
		{
			$ResignationId = 0;
			$query = "SELECT id FROM `pms_resignation` WHERE `title` = '".mysql_real_escape_string(trim($title))."'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$ResignationId = $this->f("id");
				}
			}
			return $ResignationId;
		}
		
		function fnGetResignationNameById($id)
		{
			$ResignationName = 0;
			$query = "SELECT title FROM `pms_resignation` WHERE `id` = '".mysql_real_escape_string(trim($id))."'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$ResignationName= $this->f("title");
				}
			}
			return $ResignationName;
		}

		function fnUpdateResignation($arrPost)
		{
			$curDate = date('Y-m-d H:i:s');
			if(isset($arrPost['isexit']) && $arrPost['isexit'] == '1')
			{
				$arrPost['exit_date'] = $curDate;
			}
			
			if(isset($arrPost['status']))
			{
				$arrPost['tl_status_date'] = $curDate;
				$arrPost['status_tl'] = $arrPost['status'];
				$arrPost['comment_tl'] = $arrPost['comment'];
			}
			
			if(isset($arrPost['status_manager']))
			{
				$arrPost['manager_status_date'] = $curDate;
				$arrPost['status_manager'] = $arrPost['status_manager'];
				$arrPost['comment_manager'] = $arrPost['comment_manager'];	
			}
			
			if(isset($arrPost['status_hr']))
			{
				$arrPost['hr_status_date'] = $curDate;
				$arrPost['status_hr'] = $arrPost['status_hr'];
				$arrPost['comment_hr'] = $arrPost['comment_hr'];	
			}
			
			///echo '<pre>hello<br>'; print_r($arrPost); die;
			$this->updateArray('pms_resignation',$arrPost);
			return true;
		}
		
		function fnUpdateHrResignation($arrPost)
		{
			$curDate = date('Y-m-d H:i:s');
			$arrPost['id'] = $arrPost['hdnid'];
			$arrPost['hr_status_date'] = $curDate;
			$arrPost['status_hr'] = $arrPost['status_hr'];
			$arrPost['comment_hr'] = $arrPost['comment_hr'];	

			//echo '<pre>hello<br>'; print_r($arrPost); die;
			$this->updateArray('pms_resignation',$arrPost);
			return true;
		}

		function fnDeleteResignation($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_resignation` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}
		function fnGetNoticePeriodDays($desId)
		{
			$query = "select notice,date_format(DATE_ADD( now() , INTERVAL notice DAY ),'%Y-%m-%d') as da from `pms_designation` where id = '".mysql_real_escape_string($desId)."'";
			$this->query($query);
			
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$finalDate = $this->f('da');
				}
			}
			return $finalDate;
		}
		function fnCheckResignation($id)
		{
			$res_id = '';
			$query = "select id from `pms_resignation` where `user_id` = '$id' and `revoke` = '0'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$res_id = $this->f('id');
				}
			}
			return $res_id;
		}

		function fnGetAllResignationRequest($ids)
		{
			$arrResignationValues = array();
			//echo '<pre>'; print_r($_SESSION);die;
			if($_SESSION['usertype'] == 'admin')
			{
				$query = "SELECT reg.*,reg.id as reg_id,date_format(reg.date_of_resignation,'%d-%m-%Y') as d_o_reg,date_format(reg.last_working_date,'%d-%m-%Y') as l_w_date,e.name as ename,reg.emp_reason as reason FROM `pms_resignation` as reg left join pms_employee as e on reg.user_id = e.id where `revoke` = '0' and `user_id` IN($ids) and status_manager = '1' and isexit ='0'";
			}
			else
			{
				//$query = "SELECT reg.*,reg.id as reg_id,date_format(reg.date_of_resignation,'%d-%m-%Y') as d_o_reg,date_format(reg.last_working_date,'%d-%m-%Y') as l_w_date,e.name as ename,reg.emp_reason as reason FROM `pms_resignation` as reg left join pms_employee as e on reg.user_id = e.id where `revoke` = '0' and (teamleader_id = '".$_SESSION['id']."' or (manager_id = '".$_SESSION['id']."' and ((teamleader_id != '0' and status_tl = '1') or (teamleader_id = '0')))) ";

				$query = "SELECT reg.*,reg.id as reg_id,date_format(reg.date_of_resignation,'%d-%m-%Y') as d_o_reg,date_format(reg.last_working_date,'%d-%m-%Y') as l_w_date,e.name as ename,reg.emp_reason as reason FROM `pms_resignation` as reg left join pms_employee as e on reg.user_id = e.id where `revoke` = '0' and (teamleader_id = '".$_SESSION['id']."' or (manager_id = '".$_SESSION['id']."' and ((teamleader_id != '0' and status_tl = '1') or (teamleader_id = '0'))))";
			}
			//echo $query;die;
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrResignationValues[] = $this->fetchrow();
				}
			}
			return $arrResignationValues;
		}
		
		function fnGetAlldolRequest($ids)
		{
			$arrResignationValues = array();
			//echo '<pre>'; print_r($_SESSION);die;
			if($_SESSION['usertype'] == 'admin')
			{
				$query = "SELECT reg.*,reg.id as reg_id,date_format(reg.date_of_resignation,'%d-%m-%Y') as d_o_reg,date_format(reg.last_working_date,'%d-%m-%Y') as l_w_date,e.name as ename,reg.emp_reason as reason FROM `pms_resignation` as reg left join pms_employee as e on reg.user_id = e.id where `revoke` = '0' and `user_id` IN($ids) and status_manager = '1'";
			}
			else
			{
				$query = "SELECT reg.*,reg.id as reg_id,date_format(reg.date_of_resignation,'%d-%m-%Y') as d_o_reg,date_format(reg.last_working_date,'%d-%m-%Y') as l_w_date,e.name as ename,reg.emp_reason as reason FROM `pms_resignation` as reg left join pms_employee as e on reg.user_id = e.id where `revoke` = '0' and (manager_id = '".$_SESSION['id']."' and status_manager = '1')";
			}
			//echo $query;
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrResignationValues[] = $this->fetchrow();
				}
			}
			return $arrResignationValues;
		}
		
		function fnGetAllHrResignationRequest($ids)
		{
			$arrResignationValues = array();
			//echo '<pre>'; print_r($_SESSION);die;
			
			//$query = "SELECT reg.id as reg_id,date_format(reg.date_of_resignation,'%d-%m-%Y') as d_o_reg,date_format(reg.last_working_date,'%d-%m-%Y') as l_w_date,e.name as ename,reg.emp_reason as reason FROM `pms_resignation` as reg left join pms_employee as e on reg.user_id = e.id where `revoke` = '0' and `user_id` IN($ids)";

			$query = "SELECT reg.id as reg_id,date_format(reg.date_of_resignation,'%d-%m-%Y') as d_o_reg,date_format(reg.last_working_date,'%d-%m-%Y') as l_w_date,e.name as ename,reg.emp_reason as reason,reg.status_tl,reg.status_manager,reg.status_hr FROM `pms_resignation` as reg left join pms_employee as e on reg.user_id = e.id where `revoke` = '0' and status_manager = '1'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrResignationValues[] = $this->fetchrow();
				}
			}
			return $arrResignationValues;
		}

		
		function fnGetAllEmpForDol()
		{
			$date = date('Y-m-d');
			$arrAllEmpDol = array();

			$query = "SELECT e.name as emp_name,e.email as user_email,res.user_id,res.teamleader_id,res.manager_id from pms_resignation  as res left join pms_employee as e on e.id = res.user_id where `revoke` = '0' and res.status_manager = '1' and date_format(res.last_working_date_manager,'%Y-%m-%d')='".$date."'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllEmpDol[$this->f('teamleader_id')][] = $this->fetchrow();
					$arrAllEmpDol[$this->f('manager_id')][] = $this->fetchrow();
				}
			}
			//echo '<pre>';print_r($arrAllEmpDol); die;
			return $arrAllEmpDol;
		}
		
		function fnGetAllEmpForHrDol()
		{
			$date = date('Y-m-d');
			$arrAllEmpDol = array();

			$query = "SELECT e.name as emp_name,e.email as user_email,res.user_id,res.teamleader_id,res.manager_id from pms_resignation  as res left join pms_employee as e on e.id = res.user_id where `revoke` = '0' and res.status_manager = '1' and date_format(res.last_working_date_manager,'%Y-%m-%d')='".$date."'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllEmpDol[] = $this->fetchrow();
				}
			}
			return $arrAllEmpDol;
		}

		function fnGetAllClearanceRequest($ids)
		{
			$arrResignationValues = array();
			
			$fnCheckItDesignations = $this->fnCheckDesignation($_SESSION['designation']);
			$fnCheckAdminDesignation = $this->fnCheckAdminDesignation($_SESSION['designation']);
			$fnCheckHrDesignation = $this->fnCheckHrDesignation($_SESSION['designation']);
			
			if($fnCheckItDesignations != '' && $fnCheckItDesignations != '0')
			{
				$query = "SELECT clr.*,clr.id as clearance_id,e.name as eName,res.*,date_format(res.`date_of_resignation`,'%d-%m-%Y') as dor,date_format(res.`last_working_date_manager`,'%d-%m-%Y') as l_w_d_manager FROM `pms_clearance` as clr left join pms_employee as e on clr.user_id = e.id left join pms_resignation as res on res.id = clr.ress_id where clr.`manager_status` = '1' and clr.exit = '0'";
			}
			else if($fnCheckAdminDesignation != '' && $fnCheckAdminDesignation != '0')
			{
				$query = "SELECT clr.*,clr.id as clearance_id,e.name as eName,res.*,date_format(res.`date_of_resignation`,'%d-%m-%Y') as dor,date_format(res.`last_working_date_manager`,'%d-%m-%Y') as l_w_d_manager FROM `pms_clearance` as clr left join pms_employee as e on clr.user_id = e.id left join pms_resignation as res on res.id = clr.ress_id where clr.`manager_status` = '1' and clr.`refer_admin_by_it` = '1' and clr.exit = '0'";
			}
			else if($fnCheckHrDesignation != '' && $fnCheckHrDesignation != '0')
			{
				$query = "SELECT clr.*,clr.id as clearance_id,e.name as eName,res.*,date_format(res.`date_of_resignation`,'%d-%m-%Y') as dor,date_format(res.`last_working_date_manager`,'%d-%m-%Y') as l_w_d_manager FROM `pms_clearance` as clr left join pms_employee as e on clr.user_id = e.id left join pms_resignation as res on res.id = clr.ress_id where clr.`manager_status` = '1' and clr.`refer_admin_by_it` = '1' and clr.`refer_to_hr` = '1' and clr.exit = '0'";
			}
			else
			{
				$query = "SELECT clr.*,clr.id as clearance_id,e.name as eName,res.*,date_format(res.`date_of_resignation`,'%d-%m-%Y') as dor,date_format(res.`last_working_date_manager`,'%d-%m-%Y') as l_w_d_manager FROM `pms_clearance` as clr left join pms_employee as e on clr.user_id = e.id left join pms_resignation as res on res.id = clr.ress_id where (clr.`teamleader_id` = '".$_SESSION['id']."' or clr.`manager_id` = '".$_SESSION['id']."') and clr.exit = '0'";
			}
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrResignationValues[] = $this->fetchrow();
				}
			}
			return $arrResignationValues;
		}

		function fnCheckDesignation($des)
		{
			//echo $des; die;
			$id = '';
			$query = "select id from `pms_support_designations` where `support_designation_id`='$des'";
			$this->query($query);
			if($this->num_rows() > 0 )
			{
				if($this->next_record())
				{
					$id = $this->f('id');
				}
			}
			return $id;
		}
		function fnCheckAdminDesignation($des)
		{
			//echo $des; die;
			$des_ids = '';
			$arrAdminIds = array(0);
			$query = "select `admin_designations` from `pms_interview_designations`";
			$this->query($query);
			if($this->num_rows() > 0 )
			{
				if($this->next_record())
				{
					$des_ids = $this->f('admin_designations');
				}
			}
			$arrAdminIds = explode(",",$des_ids);
			
			if(in_array($des,$arrAdminIds))
				return 1;
			else return 0;
		}
		
		function fnCheckHrDesignation($des)
		{
			//echo $des; die;
			$des_ids = '';
			$arrHrIds = array(0);
			$query = "select `hr_designations` from `pms_interview_designations`";
			$this->query($query);
			if($this->num_rows() > 0 )
			{
				if($this->next_record())
				{
					$des_ids = $this->f('hr_designations');
				}
			}
			$arrHrIds = explode(",",$des_ids);
			
			if(in_array($des,$arrHrIds))
				return 1;
			else return 0;
		}

		function fnEmployeeForClearance()
		{
			//echo '<pre>'; print_r($_SESSION); die;
			$arrEmployeeClearance = array();
			$query = "select res.id as res_id,res.user_id as emp_id,emp.name as emp_name from pms_resignation as res left join pms_employee as emp on res.user_id=emp.id left join pms_clearance as cl on res.id = cl.ress_id where ((res.teamleader_id != '0' and res.teamleader_id != '' and res.`teamleader_id`='".$_SESSION['id']."') or (res.manager_id != '0' and res.`manager_id`='".$_SESSION['id']."')) and res.status_manager = '1' and res.isexit = '1'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployeeClearance[] = $this->fetchrow();
				}
			}
			return $arrEmployeeClearance;
		}

		function fnGetEmplResignationById($id)
		{
			//echo 'hello'; die;
			$arrEmployee = array();

			$query = "SELECT  cl.*,res.teamleader_id,res.manager_id,date_format( emp.`date_of_joining` , '%d-%m-%Y' ) AS joi_date,des.title AS des_title, e1.name AS man_name, res.user_id AS emp_id, emp.designation, emp.name AS emp_name, date_format( res.`date_of_resignation` , '%d-%m-%Y' ) AS resignation_date, date_format( res.`exit_date` , '%d-%m-%Y' ) AS `exi_date` FROM `pms_resignation` AS `res` LEFT JOIN `pms_employee` AS emp ON res.user_id = emp.id LEFT JOIN pms_employee AS e1 ON res.`manager_id` = e1.`id` LEFT JOIN pms_designation AS des ON emp.designation = des.id left join pms_clearance as cl on cl.ress_id = res.id WHERE res.`id` = '$id'";


			//echo $query = "select res.user_id as emp_id,emp.name as emp_name,date_format(res.`date_of_resignation`,'%d-%m-%Y') as resignation_date,date_format(res.`exit_date`,'%d-%m-%Y') as reliving_date from pms_resignation as res left join pms_employee as emp on res.user_id=emp.id where res.`id` = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrEmployee = $this->fetchrow();
				}
			}
			return $arrEmployee;
		}
		
		function fnGetClearanceDetailsById($id)
		{
			//echo 'hello'; die;
			$arrClearance = array();

			$query = "SELECT cl.`id`,cl.`ress_id`,e.name as em_name FROM `pms_clearance` as cl left join pms_employee as e on e.id = cl.user_id WHERE cl.`id` = '$id'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrClearance = $this->fetchrow();
				}
			}
			return $arrClearance;
		}

		function fnInsertClearance($post)
		{
			$this->insertArray('pms_clearance',$post);
			return true;
		}
		
		function fnUpdateClearance($post)
		{
			$post['id'] = $post['clearance_id'];
			//echo '<pre>'; print_r($post); die;
			$this->updateArray('pms_clearance',$post);
			return true;
		}

		function fnGetItDesignations()
		{
			$arrClearance = array();

			$query = "SELECT cl.`id`,cl.`ress_id`,e.name as em_name FROM `pms_clearance` as cl left join pms_employee as e on e.id = cl.user_id WHERE cl.`id` = '$id'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrClearance = $this->fetchrow();
				}
			}
			return $arrClearance;
		}
		
	}
?>

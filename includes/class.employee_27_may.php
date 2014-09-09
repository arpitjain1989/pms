<?php
	include_once('db_mysql.php');
	class employee extends DB_Sql
	{
		function __construct()
		{
		}

		function fnInsertEmployee($arrEmployee)
		{
			//print_r($arrEmployee);die;

			$error = '';
			if(isset($arrEmployee))
			{
			//print_r($arrEmployee);
				if($arrEmployee['email'] != '')
				{
					$chkEmail = $this->fnCheckExist($arrEmployee,'insert','email');
					if($chkEmail > 0)
					{
						$error .= 'Email already exists.<br />';
					}
				}
				if($arrEmployee['username'] != '')
				{
					$chkUname = $this->fnCheckExist($arrEmployee,'insert','username');
					if($chkUname > 0)
					{
						$error .= 'Username already exists.<br />';
					}
				}
				if($arrEmployee['employee_code'] != '')
				{
					$chkEmp_code = $this->fnCheckExist($arrEmployee,'insert','employee_code');
					if($chkEmp_code > 0)
					{
						$error .= 'Employee code already exists.<br />';
					}
				}
				if($error == '')
				{
					$arrEmployee["password"] = md5($arrEmployee["password"]);
					$arrEmployee["created_date"] = Date('Y-m-d H:i:s');

					$lastInsertId = $this->insertArray('pms_employee',$arrEmployee);
					if(isset($lastInsertId))
					{
						$this->fnInsertReportingHistory($lastInsertId,$_POST['teamleader']);
					}
					return true;
				}
				else
				{
					$_SESSION['error'] = $error;
					$_SESSION['arrEmp'] = $arrEmployee;
					header("Location: employee.add.php?info=fail");
					exit;
				}
			}
		}

		function fnCheckExist($arrEmployee,$action,$val)
		{
			$arrEmployeeValues = array();
			if($action == 'insert')
			{
				$query = "SELECT `$val` FROM `pms_employee` WHERE `$val` = '".$arrEmployee[$val]."'";
			}
			else if($action == 'update')
			{
				$query = "SELECT `$val` FROM `pms_employee` WHERE `$val` = '".$arrEmployee[$val]."' AND `id`!='".$arrEmployee['id']."'";
			}
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployeeValues[] = $this->fetchrow();
				}
			}
			return count($arrEmployeeValues);
		}

		function fnGetAllEmployee()
		{
			$arrEmployeeValues = array();
			$query = "SELECT * FROM `pms_employee`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployeeValues[] = $this->fetchrow();
				}
			}
			return $arrEmployeeValues;
		}

		function fnGetEmployeeById($id)
		{
			$arrEmployeeValues = array();
			$query = "SELECT e . * , e.role, e.id AS emp_id, des.id AS des_id, d.id AS dep_id, des.title AS des_title, d.title AS dep_title, st.title AS shifttimings, e.teamleader, e1.id,e1.name as teamleader, e.designation, des.id, e.department,e.teamleader as teamleader_id, date_format(e.relieving_date_by_manager,'%Y-%m-%d') as relieving_date_by_manager, date_format(e.relieving_date_by_manager,'%Y-%m-%d') as rel_date_by_manager,date_format(e.date_of_joining,'%Y-%m-%d') as d_of_join,date_format(e.date_of_resign_terminate,'%Y-%m-%d') as d_of_res_ter,date_format(e.dob,'%Y-%m-%d') as d_birth FROM pms_employee AS e LEFT JOIN pms_employee AS e1 ON e.teamleader = e1.id LEFT JOIN pms_departments AS d ON e.department = d.id LEFT JOIN pms_designation AS des ON e.designation = des.id LEFT JOIN pms_shift_times st ON st.id = e.shiftid WHERE e.id = '".mysql_real_escape_string($id)."'";

			//echo $query = "SELECT * ,role as role,employee.id AS emp_id,employee.teamleader as teamleader_id,designation.id as des_id,departments.id as dep_id, designation.title as des_title,departments.title as dep_title, st.title as shifttimings FROM `pms_employee` AS employee LEFT JOIN pms_departments AS departments ON employee.department =departments.id LEFT JOIN pms_designation AS designation ON employee.designation =designation.id LEFT JOIN pms_shift_times st ON st.id = employee.shiftid WHERE employee.id ='".mysql_real_escape_string($id)."'";
			//$query = "SELECT * FROM `pms_employee` WHERE `id` = '".mysql_real_escape_string($id)."' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrEmployeeValues = $this->fetchrow();
				}
			}
			return $arrEmployeeValues;
		}

		function fnUpdateEmployee($arrPost)
		{
			//echo '<pre>'; print_r($arrPost);die;
			
			$checkReportingHead = $this->fnCheckReportingHead($_POST['id'],$_POST['teamleader']);
			
			if(isset($arrPost["password"]) && $arrPost["password"] != '')
			{
				$arrPost["password"] = md5($arrPost["password"]);
				//$this->updateArray('pms_admin',$arrPost);
			}
			else
			{
				unset($arrPost["password"]);
			}
			$error = '';
			if(isset($arrPost))
			{
			//print_r($arrEmployee);
				if($arrPost['email'] != '')
				{
					$chkEmail = $this->fnCheckExist($arrPost,'update','email');
					if($chkEmail > 0)
					{
						$error .= 'Email already exists.<br />';
					}
				}
				if($arrPost['username'] != '')
				{
					$chkUname = $this->fnCheckExist($arrPost,'update','username');
					if($chkUname > 0)
					{
						$error .= 'Username already exists.<br />';
					}
				}
				if($arrPost['employee_code'] != '')
				{
					$chkEmp_code = $this->fnCheckExist($arrPost,'update','employee_code');
					if($chkEmp_code > 0)
					{
						$error .= 'Employee code already exists.<br />';
					}
				}
				if(!isset($arrPost['not_include_head_count']))
				{
					$arrPost['not_include_head_count'] = '0';
				}
				if(!isset($arrPost['rct_mail_send']))
				{
					$arrPost['rct_mail_send'] = '0';
				}
				//echo $error; die;
				
				/* Check if designation is updated, if updated set the old designation to last designation */
				$arrEmp = $this->fnGetEmployeeDetailById($_POST['id']);
				
				if($arrEmp["designation"] != $_POST['designation'])
				{
					include_once("class.designation.php");
					$objDesignation = new designations();
					
					/* Fetch text for designation */
					$arrPost['old_designation'] = $objDesignation->fnGetDesignationNameById($arrEmp["designation"]);
				}
				
				if($arrEmp["current_salary_ctc"] != $_POST['current_salary_ctc'])
				{
					/* If salary is changed, save last salary in start salary, as discussed with Adil sir, Start salary needs to be changed to last salary */
					$arrPost['start_ctc'] = $arrEmp["current_salary_ctc"];
				}
				
				if($error == '')
				{
					$this->updateArray('pms_employee',$arrPost);

					if($checkReportingHead == '0' || $checkReportingHead == '')
					{
						$this->fnInsertReportingHistory($_POST['id'],$_POST['teamleader']);
						$this->fnUpdateHeadCount($arrPost);
					}
					else
					{
						/* Check if entry made for employee already */
						$sSQL = "select * from pms_rep_heads_history where userid='".mysql_real_escape_string($_POST['id'])."'";
						$this->query($sSQL);
						if($this->num_rows() == 0)
						{
							$this->fnInsertReportingHistory($_POST['id'],$_POST['teamleader']);
						}
					}

					if($arrPost['not_include_head_count'] != $arrEmp['not_include_head_count'])
					{
						$this->fnUpdateExcludedHeadCount($arrPost['id'], $arrPost['date_of_joining'], $arrPost['relieving_date_by_manager']);
					}
					return true;
				}
				else
				{
					$_SESSION['error'] = $error;
					header("Location: employee.add.php?id=".$arrPost['id']."&action=update&info=fail");
					exit;
				}
			}
		}
		
		function fnUpdateExcludedHeadCount($userId, $dateOfJoining, $releavingDateByManager)
		{
			$startDate = $dateOfJoining;
			
			$endDate = Date('Y-m-d');
			if($releavingDateByManager != '0000-00-00' && $releavingDateByManager != '' && $releavingDateByManager < $endDate)
				$endDate = $releavingDateByManager;

			while($startDate < $endDate)
			{
				$arrHeads = $this->fnCheckEmployeeReportingHeadHierarchy($userId, $startDate);

				foreach($arrHeads as $curHeads)
				{
					if($curHeads != 0)
						$headCount = $this->fnGetHeadCountById($curHeads, $startDate);
				}

				$startDate = date('Y-m-d', strtotime('+1 Day', strtotime($startDate)));
			}
		}

		function fnInsertReportingHistory($id,$tlId)
		{
			$newArray = array();
			$newArray['modified_date'] = date('Y-m-d H:i:s');
			$newArray['userid'] = $id;
			$newArray['rep_head'] = $tlId;
			$newArray['added_by'] = $_SESSION['id'];
			//echo '<pre>'; print_r($newArray); 
			$this->insertArray('pms_rep_heads_history',$newArray);
			return true;
		}

		function fnCheckReportingHead($id,$tl_id)
		{
			$reporting_head = '0';
			$query = "select teamleader from pms_employee where id = '$id'";
			$this->query($query);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$reporting_head = $this->f('teamleader');
				}
			}
			
			if($reporting_head == $tl_id)
				return true;
			else
				return false;
		}

		function fnUpdateProfile($arrPost)
		{

			if(isset($arrPost["password"]) && $arrPost["password"] != '')
			{
				$arrPost["password"] = md5($arrPost["password"]);
				//$this->updateArray('pms_admin',$arrPost);
			}
			else
			{
				unset($arrPost["password"]);
			}
			$error = '';
			if(isset($arrPost))
			{
			//print_r($arrEmployee);
				if($arrPost['email'] != '')
				{
					$chkEmail = $this->fnCheckExist($arrPost,'update','email');
					if($chkEmail > 0)
					{
						$error .= 'Email already exists.<br />';
					}
				}
				if($arrPost['username'] != '')
				{
					$chkUname = $this->fnCheckExist($arrPost,'update','username');
					if($chkUname > 0)
					{
						$error .= 'Username already exists.<br />';
					}
				}
				if($arrPost['employee_code'] != '')
				{
					$chkEmp_code = $this->fnCheckExist($arrPost,'update','employee_code');
					if($chkEmp_code > 0)
					{
						$error .= 'Employee code already exists.<br />';
					}
				}
				//echo $error; die;
				if($error == '')
				{
						//echo 'hello1'; die;
					$this->updateArray('pms_employee',$arrPost);
					return true;
				}
				else
				{
					$_SESSION['error'] = $error;
					header("Location: employee_profile.php?info=fail");
					exit;
				}
			}
		}

		function fnDeleteEmployee($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_employee` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}

		function fnGetDepartmentList()
		{
			$arrDepartments = array();
			$query = "SELECT `id` as department_id, `title` as department_title FROM  `pms_departments`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrDepartments[] = $this->fetchrow();
				}
			}
			return $arrDepartments;
		}

		function fnGetDesignationList()
		{
			$arrDesignation = array();
			$query = "SELECT `id` as designation_id, `title` as designation_title FROM  `pms_designation`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrDesignation[] = $this->fetchrow();
				}
			}
			return $arrDesignation;
		}

		/*function fnGetDepartmentName($name)
		{
			$arrDepName = array();
			$query = "select `id` as dep_id FROM `pms_departments` WHERE `title` = '$name'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrDepName[] = $this->fetchrow();
				}
			}
			return $arrDepName;

		}*/

		/*function fnGetDesignationName($name)
		{
			$arrDesName = array();
			$query = "select `id` as des_id FROM `pms_designation` WHERE `title` = '$name'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrDesName[] = $this->fetchrow();
				}
			}
			return $arrDesName;
		}*/
		function fnGetRole()
		{
			$arrRole = array();
			$query = "select `id` as id,`title` as title FROM `pms_roles` ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRole[] = $this->fetchrow();
				}
			}
			return $arrRole;
		}

		function fnGetEmployeeDetailById($id)
		{
			$arrEmployee = array();
			$query = "select e.*,date_format(e.dob,'%Y-%m-%d') as e_date_of_birth FROM `pms_employee` as e WHERE e.id = '$id' ";
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

		function fnGetEmployeesByDesignation($DesignationId = 0)
		{
			$arrEmployee = array();

			$sSQL = "select e.*, e1.name as reporting_head from pms_employee e LEFT JOIN pms_employee e1 ON e.teamleader = e1.id where e.designation in ($DesignationId) and e.status = '0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchrow();
				}
			}

			return $arrEmployee;
		}

		function fnGetEmployeeIdsByDesignation($DesignationId = 0, $date = '', $st_date)
		{
			$arrEmployee = array();

			$cond = '';
			if(trim($date) != "")
			{
				$cond = " and ((status='0' and date_format(date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($st_date)."') or (date_format(relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($date)."' and status='1'))";
			}
			else
			{
				$cond = " and status='0'";
			}

			$sSQL = "select id from pms_employee where designation in ($DesignationId) $cond";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->f("id");
				}
			}

			return $arrEmployee;
		}

		/*function fnGetAllEmployeeByDesignationId($id)
		{
			$arrEmployee = array();
			if($id == '5' || $id == '9' || $id == '10' || $id == '15' || $id == '16' || $id == '30' || $id == '31' || $id == '32' || $id == '33' || $id == '34' || $id == '35' || $id == '36' || $id == '37' || $id == '38' || $id == '39'  || $id == '40' || $id == '41' || $id == '42' || $id == '46')
			{
				$query = "select `id` as employee_id,`name`  as employee_name FROM `pms_employee` WHERE designation in(7)";
			}
			else if($id == '7' || $id == '11' || $id == '12' || $id == '13')
			{
				$query = "select `id` as employee_id,`name`  as employee_name FROM `pms_employee` WHERE designation = '6'";
			}
			else if($id == '6' || $id == '18' || $id == '19' || $id == '25')
			{
				$query = "select `id` as employee_id,`name`  as employee_name FROM `pms_employee` WHERE designation in (8, 17)";
			}
			else if($id == '14')
			{
				$query = "select `id` as employee_id,`name`  as employee_name FROM `pms_employee` WHERE designation = '13'";
			}
			else if($id == '24')
			{
				$query = "select `id` as employee_id,`name`  as employee_name FROM `pms_employee` WHERE designation = '25'";
			}
			else if($id == '21' || $id == '23'|| $id == '27' || $id == '28')
			{
				$query = "select `id` as employee_id,`name`  as employee_name FROM `pms_employee` WHERE designation = '18'";
			}
			else if($id == '20' || $id == '22' || $id == '26')
			{
				$query = "select `id` as employee_id,`name`  as employee_name FROM `pms_employee` WHERE designation = '19'";
			}
			//echo $query; die;
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchrow();
				}
			}
			return $arrEmployee;
		}*/

		function fnGetAllEmployeeByDesignationId($id)
		{
			/* THIS FUNCTION IS NOT BEING USED ANYWHERE CURRENTLY */

			$arrEmployee = array();
			$curDate = Date('Y-m-d');
			$query = '';
			
			if($id == '5' || $id == '9' || $id == '10' || $id == '15' || $id == '16' || $id == '30' || $id == '31' || $id == '32' || $id == '33' || $id == '34' || $id == '35' || $id == '36' || $id == '37' || $id == '38' || $id == '39'  || $id == '40' || $id == '41' || $id == '42' || $id == '46')
			{
				$query = "select `id` as employee_id,`name`  as employee_name FROM `pms_employee` WHERE designation in(7) and status = '0' and date_of_joining <= '$curDate'";
			}
			else if($id == '7' || $id == '11' || $id == '12' || $id == '13' || $id == '43')
			{
				$query = "select `id` as employee_id,`name`  as employee_name FROM `pms_employee` WHERE designation = '6' and status = '0' and date_of_joining <= '$curDate'";
			}
			else if($id == '6' || $id == '18' || $id == '19' || $id == '25' || $id == '44')
			{
				$query = "select `id` as employee_id,`name`  as employee_name FROM `pms_employee` WHERE designation in (8, 17) and status = '0' and date_of_joining <= '$curDate'";
			}
			else if($id == '14')
			{
				$query = "select `id` as employee_id,`name`  as employee_name FROM `pms_employee` WHERE designation = '13' and status = '0' and date_of_joining <= '$curDate'";
			}
			else if($id == '24')
			{
				$query = "select `id` as employee_id,`name`  as employee_name FROM `pms_employee` WHERE designation = '25' and status = '0' and date_of_joining <= '$curDate'";
			}
			else if($id == '21' || $id == '23'|| $id == '27' || $id == '28')
			{
				$query = "select `id` as employee_id,`name`  as employee_name FROM `pms_employee` WHERE designation = '18' and status = '0' and date_of_joining <= '$curDate'";
			}
			else if($id == '20' || $id == '22' || $id == '26')
			{
				$query = "select `id` as employee_id,`name`  as employee_name FROM `pms_employee` WHERE designation = '19' and status = '0' and date_of_joining <= '$curDate'";
			}
			//echo $query; die;
			if($query != '')
			{
				$this->query($query);

				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrEmployee[] = $this->fetchrow();
					}
				}
			}
			return $arrEmployee;
		}
		
		function fnGetReportingHead($id,$fdate,$edate)
		{
			$query = "SELECT employee1.name as teamleadername FROM `pms_employee` AS employee INNER JOIN `pms_employee` AS employee1 ON employee.teamleader = employee1.id WHERE employee.id = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$teamleader = $this->f('teamleadername');
				}
			}
			if($teamleader != '')
			{
				return $teamleader;
			}
			else
			{
				return 0;
			}
		}

		function fnGetReportingHeadById($id)
		{
			$teamleader = '';
			$query = "SELECT employee1.name as teamleadername FROM `pms_employee` AS employee INNER JOIN `pms_employee` AS employee1 ON employee.teamleader = employee1.id WHERE employee.id = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$teamleader = $this->f('teamleadername');
				}
			}
			if($teamleader != '')
			{
				return $teamleader;
			}
			else
			{
				$teamleader = 'Admin';
				return $teamleader;
			}
		}

		function fnGetReportingHeadId($id)
		{
			$TeamLeaderId = 0;

			$sSQL = "SELECT teamleader FROM `pms_employee` WHERE id = '$id'";
			$this->query($sSQL);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$TeamLeaderId = $this->f('teamleader');
				}
			}
			return $TeamLeaderId;
		}

		function fnGetReportingHeadDetails($id)
		{
			$query = "SELECT employee1.id as teamleaderid,employee1.email as eemail,employee1.name as ename FROM `pms_employee` AS employee INNER JOIN `pms_employee` AS employee1 ON employee.teamleader = employee1.id WHERE employee.id = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrLeaveFormValues = $this->fetchrow();
				}
			}
			return $arrLeaveFormValues;
		}

		function fnGetReportingHeadByDes($desid)
		{
			$arrLeaveFormValues = array();
			/*if($desid == '5' || $desid == '9' || $desid == '10' || $desid == '30' || $desid == '31' || $desid == '32' || $desid == '33' || $desid == '34' || $desid == '35' || $desid == '36' || $desid == '37' || $desid == '38' || $desid == '39' || $desid == '40' || $desid == '41' || $desid == '42' || $desid == '46')
			{
				$designation = '7';
			}
			else if($desid == '11' || $desid == '12' || $desid == '15' || $desid == '16' || $desid == '7' || $desid == '13')
			{
				$designation = '6';
			}
			else if($desid == '14')
			{
				$designation = '13';
			}
			else if($desid == '21' || $desid == '22' || $desid == '23' || $desid == '27' || $desid == '28')
			{
				$designation = '18';
			}
			else if($desid == '26' || $desid == '29')
			{
				$designation = '19';
			}
			else if($desid == '24')
			{
				$designation = '25';
			}*/
			
			include_once("class.designation.php");
			$objDesignation = new designations();
			
			$arrDesignation = $objDesignation->fnGetDesignationById($desid);
			$parent_designation = 0;
			if(isset($arrDesignation["parent_designation_id"]) && $arrDesignation["parent_designation_id"] != '')
				$parent_designation = $arrDesignation["parent_designation_id"];

			$query = "SELECT id,name from pms_employee where designation = '".mysql_real_escape_string($parent_designation)."' and status='0'";
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

		function fnGetAllemployees($id)
		{
			$db = new DB_Sql;
			$arrEmployees = array();

			$cond = '';
			if($id!=0)
			{
				$cond = " WHERE `teamleader`='$id' and status='0'";
			}

			$query = "SELECT id,status FROM `pms_employee` $cond";
			//echo $query;
			$db->query($query);

			if($db->num_rows() > 0 )
			{
				while($db->next_record())
				{
					$arrEmployees[$db->f("id")] = $db->f("id");
					if($id != $db->f("id"))
					{
						$tmpData = $this->fnGetAllemployees($db->f("id"));
						$arrEmployees = $arrEmployees + $tmpData;
					}
				}
			}

			return $arrEmployees;
		}

		function fnGetAllemployeesReleavingDateWise($end_date, $id, $start_date)
		{
			$db = new DB_Sql;
			$arrEmployees = array();

			$cond = '';
			if($end_date != "")
			{
				$cond .= " and ((status='0' and date_format(date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($start_date)."') or (date_format(relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($end_date)."' and status='1'))";
			}
			else
			{
				 $cond .= " and status='0'";
			}

			if($id!=0)
			{
				$cond .= " and `teamleader`='$id'";
			}

			$query = "SELECT id,status FROM `pms_employee` WHERE 1=1 $cond";
			//echo $query;
			$db->query($query);

			if($db->num_rows() > 0 )
			{
				while($db->next_record())
				{
					$arrEmployees[$db->f("id")] = $db->f("id");
					if($id != $db->f("id"))
					{
						$tmpData = $this->fnGetAllemployeesReleavingDateWise($end_date, $db->f("id"), $start_date);
						$arrEmployees = $arrEmployees + $tmpData;
					}
				}
			}

			return $arrEmployees;
		}

		function fnGetAllEmployeesDetailsReleavingDateWise($end_date, $id)
		{
			$db = new DB_Sql;
			$arrEmployees = array();

			$cond = '';
			if($end_date != "")
			{
				$cond .= " and (status='0' or date_format(relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($end_date)."')";
			}
			else
			{
				 $cond .= " and status='0'";
			}

			if($id!=0)
			{
				$cond .= " and `teamleader`='$id'";
			}

			$query = "SELECT id,name FROM `pms_employee` WHERE 1=1 $cond";
			//echo $query;
			$db->query($query);

			if($db->num_rows() > 0 )
			{
				while($db->next_record())
				{
					$arrEmployees[$db->f("id")] = $db->f("name");
					if($id != $db->f("id"))
					{
						$tmpData = $this->fnGetAllEmployeesDetailsReleavingDateWise($end_date, $db->f("id"));
						$arrEmployees = $arrEmployees + $tmpData;
					}
				}
			}

			return $arrEmployees;
		}
		
		function fnGetDelegateEmployeeId($id)
		{
			$arrIds = array();
			$date = date('Y-m-d');
			$sSQL = "select `employee_id` from pms_leave_form where delegate='".$id."' and '$date' BETWEEN DATE_FORMAT(`start_date`,'%Y-%m-%d') AND DATE_FORMAT(`end_date`,'%Y-%m-%d') and (status_manager='1' or manager_delegate_status='1')";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrIds[] = $this->f("employee_id");
				}
			}

			return $arrIds;
		}

		function fnGetDelegateManagerId($id)
		{
			$arrIds = array();
			$date = date('Y-m-d');
			$sSQL = "select `employee_id` from pms_leave_form where manager_delegate='".$id."' and '$date' BETWEEN DATE_FORMAT(`start_date`,'%Y-%m-%d') AND DATE_FORMAT(`end_date`,'%Y-%m-%d') and (status_manager='1' or manager_delegate_status='1')";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrIds[] = $this->f("employee_id");
				}
			}

			return $arrIds;
		}

		function fnGetEmployeeByEmail($email)
		{
			$EmployeeId = 0;
			$sSQL = "select id from pms_employee where email='".mysql_real_escape_string(trim($email))."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$EmployeeId = $this->f("id");
				}
			}

			return $EmployeeId;
		}
		function fnGetEmployeeByEmployeeCode($emcode)
		{
			$EmployeeId = 0;
			$sSQL = "select id from pms_employee where employee_code='".mysql_real_escape_string(trim(str_replace(' ','',$emcode)))."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$EmployeeId = $this->f("id");
				}
			}

			return $EmployeeId;
		}

		function fnGetReportingHeads($EmployeeId)
		{
			/* THIS FILE IS CURRENTLY USED ANYWHERE */

			$arrHeads = array();

			$employeeInfo = $this->fnGetEmployeeDetailById($EmployeeId);

			if($employeeInfo["teamleader"] != 0)
			{
				$employeeInfo = $this->fnGetEmployeeDetailById($employeeInfo["teamleader"]);

				$arrHeads[] = array("name"=>$employeeInfo["name"],"email"=>$employeeInfo["email"],"id"=>$employeeInfo["id"], "designation"=>$employeeInfo["designation"]);

				if($employeeInfo["teamleader"] != 0)
				{
					$employeeInfo = $this->fnGetEmployeeDetailById($employeeInfo["teamleader"]);

					if($employeeInfo["designation"] != 8 && $employeeInfo["designation"] != 17)
						$arrHeads[] = array("name"=>$employeeInfo["name"],"email"=>$employeeInfo["email"],"id"=>$employeeInfo["id"], "designation"=>$employeeInfo["designation"]);
				}
			}

			return $arrHeads;
		}

		function fnGetAllLeaveAvail($id)
		{
			$leaveBal = 0;
			$sSQL = "select leave_bal from pms_employee where id='$id'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$leaveBal = $this->f("leave_bal");
				}
			}

			return $leaveBal;
		}

		function fnGetEmployeeNameById($EmployeeId)
		{
			$EmployeeName = "";
			$sSQL = "select name FROM `pms_employee` WHERE id = '$EmployeeId' ";
			$this->query($sSQL);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$EmployeeName = $this->f("name");
				}
			}
			return $EmployeeName;
		}

		function fnGetEmployeeIdByName($EmployeeName)
		{
			$EmployeeId = 0;
			$sSQL = "select id FROM `pms_employee` WHERE name = '".mysql_real_escape_string($EmployeeName)."' ";
			$this->query($sSQL);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$EmployeeId = $this->f("id");
				}
			}
			return $EmployeeId;
		}

		function fnGetUnrosteredEmployees($employeeids)
		{
			$arrEmployee = array();

			if(trim($employeeids) == "")
				$employeeids = 0;

			/* Fetch agents & tls */
			/*$sSQL = "select id, teamleader from pms_employee where designation in (7,13,5,9,10,11,12,15,16,14,30,31,32,33,34,35,36,37,38,39,40,41,42) and id not in ($employeeids) and status='0'";*/

			$sSQL = "select e.id, e.teamleader from pms_employee e INNER JOIN pms_designation d ON e.designation = d.id where d.allow_roster_generation='1' and e.id not in ($employeeids) and status='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = array("id"=>$this->f("id"), "tlid"=>$this->f("teamleader"));
				}
			}

			return $arrEmployee;
		}

		function fnGetEmployeeShiftById($EmployeeId)
		{
			$EmployeeShiftId = "";
			$sSQL = "select shiftid FROM `pms_employee` WHERE id = '$EmployeeId' ";
			$this->query($sSQL);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$EmployeeShiftId = $this->f("shiftid");
				}
			}
			return $EmployeeShiftId;
		}

		function fnGetEmployeesByReportingHead($HeadId)
		{
			$arrEmployees = array();

			$sSQL = "SELECT * FROM `pms_employee` where teamleader='".mysql_real_escape_string($HeadId)."' order by name";
			$this->query($sSQL);

			if($this->num_rows() > 0 )
			{
				while($this->next_record())
				{
					$arrEmployees[] = $this->fetchrow();
				}
			}

			return $arrEmployees;
		}

		function fnGetEmployeesShiftByReportingHead($HeadId)
		{
			$shifts = array();

			$sSQL = "select distinct shiftid from pms_employee where teamleader='".mysql_real_escape_string($HeadId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$shifts[] = $this->f("shiftid");
				}
			}

			return $shifts;
		}

		function fnGetAllPhDetails($id)
		{
			$phs = array();
			//$d = date_create();
			//echo date_create($d->format('Y-m-1'))->format('Y-m-d');

			$date = date('Y-m');
			$start = $date.'-01';
			$current_date = date('Y-m-d');
			//echo $sSQL = "select id,title,holidaydate from pms_holidays where DATE_FORMAT(`holidaydate`,'%d-%m-%Y') between DATE_FORMAT('$start','%d-%m-%Y') and DATE_FORMAT('$current_date','%d-%m-%Y') ";die;
			$sSQL = "select id,title,holidaydate from pms_holidays where DATE_FORMAT(`holidaydate`,'%Y-%m-%d') between '$start' and '$current_date'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$phs[] = $this->f("holidaydate");
				}
			}
		//print_r($phs);

			return $phs;
		}

		function fnCheckPh($id,$phval)
		{
			$count = 0;
			$query = "select in_time,out_time from `pms_attendance` where date_format(`date`,'%d-%m-%Y')=date_format('$phval','%d-%m-%Y') AND `user_id` = '$id' and ((`leave_id` != '10' and `in_time`!='00:00:00') or `in_time`!='00:00:00')";
			$this->query($query);
			$in_time = "";
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$in_time = $this->f("in_time");
				}
			}

			if(trim($in_time) != '')
			{
				$count = count($in_time);
			}

			return $count;
		}

		function fnCheckPhTakenOrLeave($id)
		{
			$count = 0;
			$date = date('Y-m');
			$start = $date;
			$current_date = date('Y-m');
			$month_end_date = $date;
			$ids = array();
			//echo $start;
			//echo $current_date;
			$count = 0;
			//$query = "select id,nodays from `pms_leave_form` where (DATE_FORMAT(`start_date`,'%Y-%m') between '$start' and '$month_end_date' or DATE_FORMAT(`end_date`,'%Y-%m') between '$start' and '$month_end_date') and `ph`=1 and (`status_manager`='1' or (`status_manager`!= 2 and status = 1 ) or (`status_manager`= 0 and status = 0 ))";
			$query = "select id,nodays from `pms_leave_form` where (DATE_FORMAT(`start_date`,'%Y-%m') between '$start' and '$month_end_date' or DATE_FORMAT(`end_date`,'%Y-%m') between '$start' and '$month_end_date') and `ph`=1 and employee_id='$id' and (((status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1))) and isactive='0') or (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1'))) and (status != 2 and delegate_status!=2 AND status_manager !=2 AND manager_delegate_status !=2)";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$ids[] = $this->f("id");
					$count = $count + $this->f("nodays");
				}
			}
			/*if(count($id) > 0)
			{
				$count = count($ids);
			}*/
		//	echo $count;
			return $count;
		}



		function fnGetAllTeamLeaders($id,$curdes,$session_des,$start_date,$end_date)
		{
			/* THIS FUNCTION IS CURRENLY NOT USED ANYWHERE */

			$arrAllTeamLeaders = array();
			//echo $id.'---'.$curdes.'======'.$session_des.':::::'.$start_date.'-------'.$end_date;
			if(($session_des == 8 || $session_des == 17) && ($curdes == 6))
			{
				$query = "select id,name from `pms_employee` where designation = 6 and `id` != '$id' and ((status = '0' and date_of_joining <= '".$start_date."') or (status = '1' and relieving_date_by_manager >= '".$start_date."' and relieving_date_by_manager >= '".$end_date."'))";
			}
			else
			{
				$query = "select id,name from `pms_employee` where designation in (7,13) and `id` != '$id' and ((status = '0' and date_of_joining <= '".$start_date."') or (status = '1' and relieving_date_by_manager >= '".$start_date."' and relieving_date_by_manager >= '".$end_date."'))";
			}
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllTeamLeaders[] = $this->fetchrow();
				}
			}

			return $arrAllTeamLeaders;
		}

		function fnGetAllManagers($id,$curdes,$session_des)
		{
			/* THIS FUNCTION IS NOT CURRENTLY USED ANYWHERE */

			$arrAllManagers = array();
			//echo $id.'---'.$curdes.'======'.$session_des;

			$query = "select id,name from `pms_employee` where designation in (6) and `id` != '$id' and status = '0'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllManagers[] = $this->fetchrow();
				}
			}
			return $arrAllManagers;
		}

		function fnGetDelegateTo($deligateTo)
		{
			$value = '';
			$query = "SELECT name FROM `pms_employee` WHERE `id`='$deligateTo'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$value = $this->f('name');
				}
			}
			return $value;
		}

		function fnGetDirectReportingManagers()
		{
			/* THIS FUNCTION IS CURRENTLY NOT USED ANYWHERE */

			$arrEmployee = array();

			$sSQL = "select * from pms_employee where designation = '6' and id in (select distinct teamleader from pms_employee where designation in (5,9,10,11,12,15,16,14,30,31,32,33,34,35,36,37,38,39,40,41,42,43,46) and status='0') and status='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchrow();
				}
			}

			return $arrEmployee;
		}

		function fnGetTeamleadersByReportingHead($HeadId)
		{
			/* THIS FUNCTION IS CURRENTLY NOT USED ANYWHERE */
			$arrEmployees = array();

			$sSQL = "SELECT * FROM `pms_employee` where teamleader='".mysql_real_escape_string($HeadId)."' and designation in (7,13) order by name";
			$this->query($sSQL);

			if($this->num_rows() > 0 )
			{
				while($this->next_record())
				{
					$arrEmployees[] = $this->fetchrow();
				}
			}

			return $arrEmployees;
		}

		function fnGetAgentsByReportingHead($HeadId)
		{
			/* THIS FUNCTION IS CURRENTLY NOT USED ANYWHERE */
			$arrEmployees = array();

			$sSQL = "SELECT * FROM `pms_employee` where (teamleader='".mysql_real_escape_string($HeadId)."' or teamleader in (SELECT id FROM `pms_employee` where teamleader='".mysql_real_escape_string($HeadId)."' and designation in (7,13))) and designation in (5,9,10,11,12,14,15,16,30,31,32,33,34,35,36,37,38,39,40,41,42,46) order by name";
			$this->query($sSQL);

			if($this->num_rows() > 0 )
			{
				while($this->next_record())
				{
					$arrEmployees[] = $this->fetchrow();
				}
			}

			return $arrEmployees;
		}

		/*function fnGetEmployees($ManagerId, $TeamLeaderId, $AgentId, $shiftid, $startdate, $enddate)
		{
			$arrEmployee = array();

			if($AgentId != "" && $AgentId != 0)
			{
				$cond .= " and id='".mysql_real_escape_string($AgentId)."'";
			}
			else if($TeamLeaderId != "" && $TeamLeaderId != 0)
			{
				$cond .= " and teamleader='".mysql_real_escape_string($TeamLeaderId)."'";
			}
			else if($ManagerId != "" && $ManagerId != 0)
			{
				$cond .= " and (teamleader='".mysql_real_escape_string($ManagerId)."' or teamleader in (SELECT id FROM `pms_employee` where teamleader='".mysql_real_escape_string($ManagerId)."' and designation in (7,13)))";
			}

			if($shiftid != "" && $shiftid != "0")
			{
				$cond .= " and id in (select distinct user_id from pms_attendance where shift_id = '".mysql_real_escape_string($shiftid)."' and date_format(date,'%Y-%m-%d') between '".mysql_real_escape_string($startdate)."' and '".mysql_real_escape_string($enddate)."')";
			}

			$sSQL = "select * from pms_employee where designation in (5,7,9,10,11,12,13,14,15,16) $cond order by name";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchrow();
				}
			}

			return $arrEmployee;
		}*/

		function fnGetEmployees($ManagerId, $TeamLeaderId, $shiftid, $month, $year, $singleSearch = false)
		{			
			//echo $curDate = date('Y-m-d'); die;
			$arrEmployee = array();
			$cond = "";

			if($singleSearch)
			{
				$id = 0;
				/*if($AgentId != 0)
				{
					$id = $AgentId;
				}
				else */
				if($TeamLeaderId != 0)
				{
					$id = $TeamLeaderId;
				}
				else if($ManagerId != 0)
				{
					$id = $ManagerId;
				}

				$cond .= " and e.id='".mysql_real_escape_string($id)."'";
			}
			else
			{
				/*if($AgentId != "" && $AgentId != 0)
				{
					$cond .= " and e.id='".mysql_real_escape_string($AgentId)."'";
				}
				else */if($TeamLeaderId != "" && $TeamLeaderId != 0)
				{
					//$cond .= " and e.id='".mysql_real_escape_string($TeamLeaderId)."'";
					$cond .= " and (e.teamleader='".mysql_real_escape_string($TeamLeaderId)."' or e.id='".mysql_real_escape_string($TeamLeaderId)."')";
				}
				else if($ManagerId != "" && $ManagerId != 0)
				{
					//$cond .= " and (e.teamleader='".mysql_real_escape_string($ManagerId)."' or e.id='".mysql_real_escape_string($ManagerId)."')";
					$cond .= " and (e.teamleader='".mysql_real_escape_string($ManagerId)."' or e.teamleader in (SELECT id FROM `pms_employee` where teamleader='".mysql_real_escape_string($ManagerId)."') or e.id='".mysql_real_escape_string($ManagerId)."')";
				}

				if($shiftid != "" && $shiftid != "0")
				{
					$cond .= " and e.id in (select distinct user_id from pms_attendance where shift_id = '".mysql_real_escape_string($shiftid)."' and date_format(date,'%Y-%m') = '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."')";
				}
			}

			//$sSQL = "select * from pms_employee where designation in (5,7,9,10,11,12,13,14,15,16) $cond order by name";
			//$sSQL = "select e.*, e1.name as reporting_head from pms_employee e INNER JOIN pms_employee e1 ON e.teamleader = e1.id where e.designation not in (8,17) and e.status = '0' $cond order by name";

			//echo $sSQL = "select e.*, e1.name as reporting_head from pms_employee e INNER JOIN pms_employee e1 ON e.teamleader = e1.id where e.designation not in (8,17) and e.status = '0' $cond order by name"; die;

			$sSQL = "select e.*, e1.name as reporting_head from pms_employee e INNER JOIN pms_employee e1 ON e.teamleader = e1.id where ((e.status = '0' and '".$year."-".$month."' >= date_format(e.date_of_joining,'%Y-%m')) or ( e.status = '1' and '".$year."-".$month."' <= date_format(e.relieving_date_by_manager,'%Y-%m'))) $cond order by name";
			$this->query($sSQL);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchrow();
				}
			}

			return $arrEmployee;
		}

		function fnGetAllEmployeesDetails($id)
		{
			$db = new DB_Sql;
			$arrEmployees = array();

			$current = date('Y-m-d');

			$cond = '';
			if($id!=0)
			{
				$cond = " AND e.teamleader= '".mysql_real_escape_string($id)."'";
			}

			$query = "SELECT e.*, e1.name as reporting_head_name, date_format(e.date_of_joining,'%d-%m-%Y') as date_of_joining, d.title as designation_title FROM pms_employee e LEFT JOIN pms_employee e1 ON e.teamleader = e1.id LEFT JOIN pms_designation d ON e.designation = d.id WHERE ((e.status = '0' and date_format(e.date_of_joining,'%Y-%m-%d') <= '$current') or (e.status = '1' and date_format(e.relieving_date_by_manager,'%Y-%m-%d') >= '$current')) $cond";
			$db->query($query);

			if($db->num_rows() > 0 )
			{
				while($db->next_record())
				{
					$arrEmployees[$db->f("id")] = $db->fetchrow();
					if($id != $db->f("id"))
					{
						$tmpData = $this->fnGetAllEmployeesDetails($db->f("id"));
						$arrEmployees = $arrEmployees + $tmpData;
					}
				}
			}

			return $arrEmployees;
		}

		function fnGetEmployeeDesignation($id)
		{
			$DesignationId = 0;
			$query = "SELECT designation from pms_employee where id='".mysql_real_escape_string($id)."'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$DesignationId = $this->f('designation');
				}
			}

			return $DesignationId;

		}

		function fnGetSupportReportingHeads()
		{
			/* THIS FUNCTION IS CURRENTLY NOT USED ANYWHERE */
			$arrEmployee = array();
			$sSQL = "SELECT * from pms_employee where designation in (18,19,17)";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchRow();
				}
			}
			return $arrEmployee;
		}
		function fnGetAllEmployeeForIncentive($start)
		{
			//echo '<pre>'; print_r($start);

			$arrEmployeeValues = array();
			$query = "SELECT e1.id as emp_id,e1.name as emp_name,e1.designation as emp_des,e1.teamleader,e2.name as teamlead_name FROM `pms_employee` as e1 left join pms_employee as e2 on e1.teamleader = e2.id WHERE ((e1.status = '0' and (e1.date_of_joining <= '".$start['first_date']."' or e1.date_of_joining <= '".$start['last_date']."')) or (e1.status = '1' and (e1.relieving_date_by_manager >= '".$start['first_date']."' or e1.relieving_date_by_manager >= '".$start['last_date']."')))";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployeeValues[] = $this->fetchrow();
				}
			}
			return $arrEmployeeValues;
		}
		function fnGetAllEmployeeForReference()
		{
			$arrEmployeeValues = array();
			$query = "SELECT e1.id as emp_id,e1.name as emp_name FROM `pms_employee` as e1";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployeeValues[] = $this->fetchrow();
				}
			}
			return $arrEmployeeValues;
		}

		function fnGetAllManagersForInterview()
		{
			$arrEmployees = array();
			/* Modified the query as the designations for HR will be derived from interview settings module
			 * 
			 *
			 * $sSQL = "SELECT * FROM `pms_employee` where designation IN('6','18','19')";
			*/
			
			include_once("class.interview_settings.php");
			$objInterviewSettings = new interview_settings();
			
			$arrInterviewSettings = $objInterviewSettings->fnGetInterviewSettings();
			
			$managers_designations = '0';
			if(isset($arrInterviewSettings["managers_designations"]) && trim($arrInterviewSettings["managers_designations"]) != "")
				$managers_designations = $arrInterviewSettings["managers_designations"];
			
			$sSQL = "SELECT * FROM `pms_employee` where designation IN ($managers_designations) and status='0'";
			$this->query($sSQL);

			if($this->num_rows() > 0 )
			{
				while($this->next_record())
				{
					$arrEmployees[] = $this->fetchrow();
				}
			}

			return $arrEmployees;
		}

		function fnGetAllTeamleads()
		{
			/* THIS FUNCTION IS NOT USED ANYWHERE CURRENTLY */
			$arrEmployees = array();
			$sSQL = "SELECT id,name FROM `pms_employee` where designation IN('7','13')";
			$this->query($sSQL);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployees[] = $this->fetchrow();
				}
			}
			return $arrEmployees;
		}
		function fnGetAllEmployeeDetails()
		{
			$arrEmployees = array();

			$sSQL = "SELECT e.*,date_format(e.date_of_joining,'%d-%m-%Y') as emp_date_joining,date_format(e.dob,'%d-%m-%Y') as emp_dob,date_format(e.date_of_resign_terminate,'%d-%m-%Y') as resign_terminate,date_format(e.relieving_date_by_manager,'%d-%m-%Y') as relieving_date FROM `pms_employee` as e";
			$this->query($sSQL);

			if($this->num_rows() > 0 )
			{
				while($this->next_record())
				{
					$arrEmployees[] = $this->fetchrow();
				}
			}

			return $arrEmployees;
		}
		function fnGetAllActiveEmployeeDetails()
		{
			$arrEmployees = array();

			$sSQL = "SELECT e.*,date_format(e.date_of_joining,'%d-%m-%Y') as emp_date_joining,date_format(e.dob,'%d-%m-%Y') as emp_dob,date_format(e.date_of_resign_terminate,'%d-%m-%Y') as resign_terminate,date_format(e.relieving_date_by_manager,'%d-%m-%Y') as relieving_date FROM `pms_employee` as e where e.status = '0'";
			$this->query($sSQL);

			if($this->num_rows() > 0 )
			{
				while($this->next_record())
				{
					$arrEmployees[] = $this->fetchrow();
				}
			}

			return $arrEmployees;
		}
		function fnGetAllInductionDetails($stat)
		{
			//echo $stat; die;
			$arrEmployees = array();


			$cond = '';
			if($stat == 'attend')
			{
				$cond = " WHERE induction = 1";
			}
			else if($stat == 'pending')
			{
				$cond = " WHERE induction IN(0,2)";
			}

			$sSQL = "SELECT id,name,if(induction=1,'Attend','Pending') as induction_status  FROM `pms_employee` $cond ";
			$this->query($sSQL);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployees[] = $this->fetchrow();
				}
			}
			return $arrEmployees;
		}

		function fnGetEmployeeDob()
		{
			$arrEmployeeValues = array();
			$query = "SELECT id,name,dob,email FROM `pms_employee` where status = '0'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployeeValues[] = $this->fetchrow();
				}
			}
			return $arrEmployeeValues;
		}
		function fnGetReferredEmployeeById($id)
		{
			$emp_name = '';
			$query = "select name from pms_employee where id = '$id'";
			$this->query($query);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$emp_name = $this->f('name');
				}
			}
			return $emp_name;
		}
		function fnUpdateEmployeeByExcel($arr)
		{
			//echo '<pre>'; print_r($arr); die;
			$query = "update pms_employee set `date_of_joining` = '".$arr['date_of_joining']."',`induction` = '".$arr['induction']."',`father_husband_name` = '".$arr['father_husband_name']."',`current_address` = '".mysql_real_escape_string($arr['current_address'])."',`location` = '".$arr['location']."',`city` = '".$arr['city']."',`zip` = '".$arr['zip']."',`dob` = '".$arr['dob']."',`emergency_contact_name` = '".$arr['emergency_contact_name']."',`relation` = '".$arr['relation']."',`phone_number` = '".$arr['phone_number']."',`company_mobile_no` = '".$arr['company_mobile_no']."',`official_email` = '".$arr['official_email']."',`qualification` = '".$arr['qualification']."',`gender` = '".$arr['gender']."',`experience` = '".$arr['experience']."',`old_designation` = '".$arr['old_designation']."',`id_card` = '".$arr['id_card']."',`blood_group` = '".$arr['blood_group']."',`current_salary_ctc` = '".$arr['current_salary_ctc']."',`start_ctc` = '".$arr['start_ctc']."',`retention_bonus_scheme` = '".$arr['retention_bonus_scheme']."',`retention_amount` = '".$arr['retention_amount']."',`offer_letter_issued` = '".$arr['offer_letter_issued']."',`pf_no` = '".$arr['pf_no']."',`esic_no` = '".$arr['esic_no']."',`icici_bank_ac_no` = '".mysql_real_escape_string($arr['icici_bank_ac_no'])."',`terminated_absconding_resigned` = '".$arr['terminated_absconding_resigned']."',`date_of_resign_terminate` = '".$arr['date_of_resign_terminate']."',`relieving_date_by_manager` = '".$arr['relieving_date_by_manager']."',`reason_of_leaving` = '".$arr['reason_of_leaving']."' where id = '".$arr['id']."'";
			//die;
			$this->query($query);
			return true;
		}

		function fnUpdateEmployeeUpdateLastDate($arr)
		{
			//echo '<pre>'; print_r($arr); die;
			$query = "update pms_employee set `relieving_date_by_manager` = '".$arr['relieving_date_by_manager']."',`status` = '1' where id = '".$arr['id']."'";
			//die;
			$this->query($query);
			return true;
		}

		/* Get All employees birthday dates and some data that we need to show in birthday calender */
		function fetchEmployeeBitrhDayData($start,$end)
		{
			$arrHighlights = array();
			$date = date('Y');
			$year1 = date("Y", strtotime($start));
			$year2 = date("Y", strtotime($end));
			//$sSQL = "SELECT name as emp_name,id as emp_id,date_format(`dob`,'%m-%d') as emp_dob from pms_employee where status = '0'";

			$sSQL = "SELECT name AS emp_name, id AS emp_id, date_format( `dob` , '$year1-%m-%d' ) AS emp_dob FROM pms_employee WHERE status = '0' and date_format(`dob`,'$year1-%m-%d') between '$start' and '$end' UNION SELECT name, id, date_format( `dob` , '$year2-%m-%d' ) FROM pms_employee WHERE status = '0' and date_format(`dob`,'$year2-%m-%d') between '$start' and '$end'";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{

					$arrHighlights[] = array(
											'id' => $this->f("emp_id"),
											'title' => $this->f("emp_name"),
											'start' => $this->f("emp_dob"),
											'color' => $this->f("colorcode")
										);
				}
			}
			return $arrHighlights;
		}
		/* Insert personal history form data into employee table */
		function fnUpdateEmployeePersonalHistory($post)
		{
			$this->updateArray('pms_employee',$post);
			return true;
		}
		
		function fnGetReportHeadHierarchy($UserId)
		{
			$arrHeads = array();
			$db = new DB_Sql();

			$sSQL = "select e1.id, e1.name, e1.designation from pms_employee e INNER JOIN pms_employee e1 ON e.teamleader = e1.id where e.id='".mysql_real_escape_string($UserId)."' and e1.status = '0'";
			$db->query($sSQL);
			if($db->num_rows() > 0)
			{
				if($db->next_record())
				{
					$arrHeads[$db->f("designation")]["id"] = $db->f("id");
					$arrHeads[$db->f("designation")]["name"] = $db->f("name");

					$arrTemp = $this->fnGetReportHeadHierarchy($db->f("id"));

					$arrHeads = $arrHeads + $arrTemp;
				}
			}

			return $arrHeads;
		}

		function fnGetReportHeadIdsHierarchy($UserId)
		{
			$arrHeads = array();
			$db = new DB_Sql();

			$sSQL = "select e1.id from pms_employee e INNER JOIN pms_employee e1 ON e.teamleader = e1.id where e.id='".mysql_real_escape_string($UserId)."' and e1.status = '0'";
			$db->query($sSQL);
			if($db->num_rows() > 0)
			{
				if($db->next_record())
				{
					$arrHeads[$db->f("id")] = $db->f("id");

					$arrTemp = $this->fnGetReportHeadIdsHierarchy($db->f("id"));

					$arrHeads = $arrHeads + $arrTemp;
				}
			}
			return $arrHeads;
		}
		
		function fnCheckEmployeeId($id)
		{
			$e_id = '';
			$sSQL = "select id from pms_employee where id = '".$id."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$e_id = $this->f('id');
				}
			}
			return $e_id;
		}
		
		function fnGetReportingHeadForRoster()
		{
			$arrHeads = array();

			include_once("class.designation.php");
			$objDesignation = new designations();

			$arrDesignation = $objDesignation->fnGetDesignationforRoster();
			$arrDesignation[] = 0;

			$desIds = implode(",",$arrDesignation);

			$sSQL = "select distinct e2.id, e2.name from pms_employee e1 INNER JOIN pms_employee e2 ON e1.teamleader = e2.id where e1.designation in ($desIds) and e1.status = '0' order by e2.name";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$arrHeads[$this->f("id")] = $this->fetchRow();
				}
			}

			return $arrHeads;
		}

		function fnGetAllEmployeesForRoster($end_date, $id)
		{
			$arrEmployees = array();

			include_once("class.designation.php");
			$objDesignation = new designations();

			$arrDesignation = $objDesignation->fnGetDesignationforRoster();
			$arrDesignation[] = 0;

			$desIds = implode(",",$arrDesignation);

			$cond = '';
			if($end_date != "")
			{
				$cond .= " and (status='0' or date_format(relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($end_date)."')";
			}
			else
			{
				 $cond .= " and status='0'";
			}

			if($id!=0)
			{
				$cond .= " and `teamleader`='$id' and status='0'";
			}

			$query = "SELECT id,status FROM `pms_employee` WHERE 1=1 and designation in ($desIds) $cond order by name";
			$this->query($query);

			if($this->num_rows() > 0 )
			{
				while($this->next_record())
				{
					$arrEmployees[$this->f("id")] = $this->f("id");
				}
			}

			return $arrEmployees;
		}
		
		function fnGetFirstTeamMember($ReportingHeadId, $year, $month)
		{
			$teamMemberId = 0;

			$sSQL = "select e.id from pms_employee as e where e.teamleader='".mysql_real_escape_string($ReportingHeadId)."' and ((e.status = '0' and '".$year."-".$month."' >= date_format(e.date_of_joining,'%Y-%m')) or (e.status = '1' and '".$year."-".$month."' <= date_format(e.relieving_date_by_manager,'%Y-%m'))) and (select count(id) from pms_employee where teamleader=e.id and ((status = '0' and '".$year."-".$month."' >= date_format(date_of_joining,'%Y-%m')) or (status = '1' and '".$year."-".$month."' <= date_format(relieving_date_by_manager,'%Y-%m')))) > 0 order by name limit 0,1";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$teamMemberId = $this->f("id");
				}
			}
			
			return $teamMemberId;
		}
		
		function fnGetJoineesByDateRange($startDate,$endDate)
		{
			$arrEmployee = array();
			
			$sSQL = "select e.id, e.employee_code, e.name, h.name as head_name, date_format(e.date_of_joining, '%d-%m-%Y') as date_of_joining from pms_employee e INNER JOIN pms_employee h ON e.teamleader = h.id where date_format(e.date_of_joining, '%Y-%m-%d') between '".mysql_real_escape_string($startDate)."' and '".mysql_real_escape_string($endDate)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchRow();
				}
			}
			
			return $arrEmployee;
		}
		
		function fnGetLeaversByDateRange($startDate,$endDate)
		{
			$arrEmployee = array();
			
			$sSQL = "select e.id, e.employee_code, e.name, h.name as head_name, date_format(e.date_of_joining, '%d-%m-%Y') as date_of_joining, date_format(e.relieving_date_by_manager, '%d-%m-%Y') as relieving_date_by_manager from pms_employee e INNER JOIN pms_employee h ON e.teamleader = h.id where date_format(e.relieving_date_by_manager, '%Y-%m-%d') between '".mysql_real_escape_string($startDate)."' and '".mysql_real_escape_string($endDate)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchRow();
				}
			}
			
			return $arrEmployee;
		}

		function fnUpdateEmployeeStatus($eid)
		{
			$date = date('Y-m-d H:m:i');
			$query = "update pms_employee set status = '1',`relieving_date_by_manager` = '$date' where id='$eid'";
			$this->query($query);
			return true;
		}
		
		function fnGetAllEmployeesByHeadAndDesignation($id, $designation)
		{
			$db = new DB_Sql;
			$arrEmployees = array();

			$current = date('Y-m-d');

			$query = "select id from pms_employee where ((status = '0' and date_format(date_of_joining,'%Y-%m-%d') <= '$current') or (status = '1' and date_format(relieving_date_by_manager,'%Y-%m-%d') >= '$current')) and teamleader='$id' and designation in (".$designation.")";
			$db->query($query);

			if($db->num_rows() > 0 )
			{
				while($db->next_record())
				{
					$arrEmployees[$db->f("id")] = $db->f("id");
					if($id != $db->f("id"))
					{
						$tmpData = $this->fnGetAllEmployeesByHeadAndDesignation($db->f("id"), $designation);
						$arrEmployees = $arrEmployees + $tmpData;
					}
				}
			}

			return $arrEmployees;
		}
		
		function fnGetEmployeesForAttrition($id,$month,$year,$repor_head)
		{
			$arrEmployee = array();
			//echo '<pre>id:'.$id.'<br>month:'.$month.'<br>year'.$year.'<br>reportingHead:'.$repor_head;
			//print_r($_SESSION); die;	
			
			$sSQL = "select e.id, e.employee_code, e.name,date_format(e.date_of_joining, '%d-%m-%Y') as date_of_joining,e.teamleader, date_format(e.relieving_date_by_manager, '%Y-%m') as relieving_date_by_manager,date_format(e.relieving_date_by_manager, '%d-%m-%Y') as rel_date_by_manager,e.reason_of_leaving,e.terminated_absconding_resigned,date_format(e.date_of_joining,'%Y-%m-%d') as doj,date_format(e.relieving_date_by_manager,'%Y-%m-%d') as rel_date,TIMESTAMPDIFF(MONTH , e.date_of_joining, e.relieving_date_by_manager ) as months_worked from pms_employee e where date_format(e.relieving_date_by_manager, '%Y-%m') = '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."' and e.not_include_head_count='0'";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchRow();
				}
			}
			$arrNew = array();
			foreach($arrEmployee as $emp)
			{
				//echo '<br>'; print_r($emp);
				if($repor_head == '0')
				{
					$repor_head = $id;
				}
				//echo '<pre>' ; print_r($emp);
				$CheckEmployeeReportingHead = $this->fnCheckEmployeeReportingHead($emp['id'],$emp['relieving_date_by_manager']);
				//echo '<br>CheckEmployeeReportingHead:'.$CheckEmployeeReportingHead.'<br>repor_head:'.$repor_head;
				if(($repor_head == 'all' || $repor_head == '0') && count($_SESSION["SearchAttrition"]["tls"]) > 0)
				{
					if(in_array($emp['teamleader'],$_SESSION["SearchAttrition"]["tls"]))
					{
						$arrNew[] = $emp;
					}
				}
				else
				{
					if(($CheckEmployeeReportingHead == $emp['teamleader']) && ($emp['teamleader'] == $repor_head))
					{
						$arrNew[] = $emp;
					}
				}
			}
			//echo '<pre>'; print_r($arrNew);
			return $arrNew;
		}
		
		function fnGetEmployeesForMonthlyAttrition($id,$month,$year,$repor_head)
		{
			$arrEmployee = array();
			//echo '<pre>id:'.$id.'<br>month:'.$month.'<br>year'.$year.'<br>reportingHead:'.$repor_head;
			//print_r($_SESSION);
				
			
			$sSQL = "select e.id, e.employee_code, e.name,date_format(e.date_of_joining, '%d-%m-%Y') as date_of_joining,e.teamleader, date_format(e.relieving_date_by_manager, '%Y-%m') as relieving_date_by_manager,date_format(e.relieving_date_by_manager, '%d-%m-%Y') as rel_date_by_manager,e.reason_of_leaving,e.terminated_absconding_resigned,date_format(e.date_of_joining,'%Y-%m-%d') as doj,date_format(e.relieving_date_by_manager,'%Y-%m-%d') as rel_date,TIMESTAMPDIFF(MONTH , e.date_of_joining, e.relieving_date_by_manager ) as months_worked from pms_employee e where date_format(e.relieving_date_by_manager, '%Y-%m') = '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."' and e.not_include_head_count='0'";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchRow();
				}
			}
			$arrNew = array();
			foreach($arrEmployee as $emp)
			{
				//echo '<br>'; print_r($emp); 
				if($repor_head == '0')
				{
					$repor_head = $id;
				}
				//echo '<pre>' ; print_r($emp); 
				$CheckEmployeeReportingHead = $this->fnCheckEmployeeReportingHead($emp['id'],$emp['relieving_date_by_manager']);
				//echo '<br>CheckEmployeeReportingHead:'.$CheckEmployeeReportingHead.'<br>repor_head:'.$repor_head;
				if(($repor_head == 'all' || $repor_head == '0') && count($_SESSION["SearchAttrition"]["tls"]) > 0)
				{
					if(in_array($emp['teamleader'],$_SESSION["SearchAttrition"]["tls"]))
					{
						$arrNew[] = $emp;
					}
				}
				else
				{
					if(($CheckEmployeeReportingHead == $emp['teamleader']) && ($emp['teamleader'] == $repor_head))
					{
						$arrNew[] = $emp;
					}
				}
			}
			//echo '<pre>'; print_r($arrNew); die;
			return $arrNew;
		}
		
		function fnGetEmployeesForHrAttrition($id,$month,$year,$repor_head)
		{
			//echo 'hello'; 
			$arrEmployee = array();
			include_once("class.attendance.php");
			$objAttendance = new attendance();
			$getAllReporintHeads = $objAttendance->fnGetEmployees();
			//echo '<pre>id:'.$id.'<br>month:'.$month.'<br>year'.$year.'<br>reportingHead:';
			//print_r($repor_head);
			//print_r($getAllReporintHeads); 
			
			$sSQL = "select e.id, e.employee_code, e.name,date_format(e.date_of_joining, '%d-%m-%Y') as date_of_joining,e.teamleader, date_format(e.relieving_date_by_manager, '%Y-%m') as relieving_date_by_manager,date_format(e.relieving_date_by_manager, '%d-%m-%Y') as rel_date_by_manager,e.reason_of_leaving,e.terminated_absconding_resigned,date_format(e.date_of_joining,'%Y-%m-%d') as doj,date_format(e.relieving_date_by_manager,'%Y-%m-%d') as rel_date,TIMESTAMPDIFF(MONTH , e.date_of_joining, e.relieving_date_by_manager ) as months_worked from pms_employee e where date_format(e.relieving_date_by_manager, '%Y-%m') = '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."' and e.not_include_head_count='0'";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchRow();
				}
			}
			$arrNew = array();
			$newReportingHeads = array();

			//echo 'inside<pre>' ; print_r($_SESSION);
				
			foreach($arrEmployee as $emp)
			{
				$CheckEmployeeReportingHead = $this->fnCheckEmployeeReportingHeadHierarchy($emp['id'],$emp['relieving_date_by_manager']);
				//echo '<pre>'; print_r($emp);
				$remove = array(0);
				$final_reporting_heads_hierarchy = array_diff($CheckEmployeeReportingHead, $remove);
			
				//die;
				
				//echo '<br>repor_head:';
				//print_r($repor_head);
				
				if(isset($repor_head) && $repor_head == '0')
				{
					$arrNew[] = $emp;
				}
				else if(isset($repor_head) && count($repor_head) > '0')
				{
					//echo 'here1---------';
					//print_r($_SESSION['SearchAttrition']['reporting_head']);
					//echo '<br>============';
					//print_r($final_reporting_heads_hierarchy);
					//echo '<br>++++++++++++';
					$result = array_intersect($repor_head, $final_reporting_heads_hierarchy);
					//print_r($result);
					if(count($result) > 0)
					{
						$arrNew[] = $emp;
					}
				}
				
				
			}
			
			//echo '<pre>'; print_r($arrNew); die;
			return $arrNew;
		}
		
		function fnGetEmployeesForYearlyHrAttrition($id,$month,$year,$repor_head)
		{
			$arrEmployee = array();
			include_once("class.attendance.php");
			$objAttendance = new attendance();
			$getAllReporintHeads = $objAttendance->fnGetEmployees();
			
			$sSQL = "select e.id, e.employee_code, e.name,date_format(e.date_of_joining, '%d-%m-%Y') as date_of_joining,e.teamleader, date_format(e.relieving_date_by_manager, '%Y-%m') as relieving_date_by_manager,date_format(e.relieving_date_by_manager, '%d-%m-%Y') as rel_date_by_manager,e.reason_of_leaving,e.terminated_absconding_resigned,date_format(e.date_of_joining,'%Y-%m-%d') as doj,date_format(e.relieving_date_by_manager,'%Y-%m-%d') as rel_date,TIMESTAMPDIFF(MONTH , e.date_of_joining, e.relieving_date_by_manager ) as months_worked from pms_employee e where date_format(e.relieving_date_by_manager, '%Y-%m') between '".mysql_real_escape_string($year)."-01' and '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."' and e.not_include_head_count='0'";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchRow();
				}
			}
			$arrNew = array();
			$newReportingHeads = array();

			//echo 'inside<pre>' ; print_r($_SESSION);
				
			foreach($arrEmployee as $emp)
			{
				$CheckEmployeeReportingHead = $this->fnCheckEmployeeReportingHeadHierarchy($emp['id'],$emp['relieving_date_by_manager']);
				//echo '<pre>'; print_r($emp);
				$remove = array(0);
				$final_reporting_heads_hierarchy = array_diff($CheckEmployeeReportingHead, $remove);
			
				//die;
				
				//echo '<br>repor_head:';
				//print_r($repor_head);
				
				if(isset($repor_head) && $repor_head == '0')
				{
					$arrNew[] = $emp;
				}
				else if(isset($repor_head) && count($repor_head) > '0')
				{
					//echo 'here1---------';
					//print_r($_SESSION['SearchAttrition']['reporting_head']);
					//echo '<br>============';
					//print_r($final_reporting_heads_hierarchy);
					//echo '<br>++++++++++++';
					$result = array_intersect($_SESSION['SearchAttrition']['reporting_head'], $final_reporting_heads_hierarchy);
					//print_r($result);
					if(count($result) > 0)
					{
						$arrNew[] = $emp;
					}
				}
				
				
			}
			
			//echo '<pre>'; print_r($arrNew); die;
			return $arrNew;
		}
		
		function fnGetEmployeesForHrMultipleReportingAttrition($ids,$month,$year)
		{
			$arrEmployee = array();
			include_once("class.attendance.php");
			$objAttendance = new attendance();
			
			$sSQL = "select e.id, e.employee_code, e.name,date_format(e.date_of_joining, '%d-%m-%Y') as date_of_joining,e.teamleader, date_format(e.relieving_date_by_manager, '%Y-%m') as relieving_date_by_manager,date_format(e.relieving_date_by_manager, '%d-%m-%Y') as rel_date_by_manager,e.reason_of_leaving,e.terminated_absconding_resigned,date_format(e.date_of_joining,'%Y-%m-%d') as doj,date_format(e.relieving_date_by_manager,'%Y-%m-%d') as rel_date,TIMESTAMPDIFF(MONTH , e.date_of_joining, e.relieving_date_by_manager ) as months_worked from pms_employee e where date_format(e.relieving_date_by_manager, '%Y-%m') = '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."' and e.not_include_head_count='0'";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchRow();
				}
			}
			$arrNew = array();
			$newReportingHeads = array();
			
			//echo 'inside<pre>' ; print_r($arrEmployee);print_r($ids);
				
			foreach($arrEmployee as $emp)
			{
				//echo '<br>inhere-------------------------<br>';
				$CheckEmployeeReportingHead = $this->fnCheckEmployeeReportingHeadHierarchy($emp['id'],$emp['relieving_date_by_manager']);
				//echo '<pre>'; print_r($emp);
				$remove = array(0);
				$final_reporting_heads_hierarchy = array_diff($CheckEmployeeReportingHead, $remove);
			
				//die;
				
				//echo '<br>repor_head:';print_r($repor_head);
				
				if(isset($ids) && count($ids) > '0')
				{
					//echo '<br>$ids:';
					//print_r($ids);
					//echo '<br>final_reporting_heads_hierarchy:';
					//print_r($final_reporting_heads_hierarchy);
					//echo '<br>++++++++++++';
					$result = array_intersect($ids, $final_reporting_heads_hierarchy);
					//echo '<br>result:';print_r($result);
					if(count($result) > 0)
					{
						//echo '<br>hello123';
						$arrNew[] = $emp;
						//print_r($arrNew);
					}
				}
			}
			
			
			return $arrNew;
		}
		
		function fnGetEmployeesForHrMultipleReportingYearlyAttrition($ids,$month,$year)
		{
			$arrEmployee = array();
			include_once("class.attendance.php");
			$objAttendance = new attendance();
			//echo '<br>month:'.$month;
						//echo '<br>month:'.$month;
			/*if($month != '0' && $month > 1)
			{
				$first_date = $year.'-'.$month.'-01';
				$prev_month = date ("m", strtotime("-1 month", strtotime($first_date)));
			}
			else
			{
				$prev_month = $month;
			}*/
			//echo '<br>prev_month:'.$prev_month; die;
			$sSQL = "select e.id, e.employee_code, e.name,date_format(e.date_of_joining, '%d-%m-%Y') as date_of_joining,e.teamleader, date_format(e.relieving_date_by_manager, '%Y-%m') as relieving_date_by_manager,date_format(e.relieving_date_by_manager, '%d-%m-%Y') as rel_date_by_manager,e.reason_of_leaving,e.terminated_absconding_resigned,date_format(e.date_of_joining,'%Y-%m-%d') as doj,date_format(e.relieving_date_by_manager,'%Y-%m-%d') as rel_date,TIMESTAMPDIFF(MONTH , e.date_of_joining, e.relieving_date_by_manager ) as months_worked from pms_employee e where date_format(e.relieving_date_by_manager, '%Y-%m') between '".mysql_real_escape_string($year)."-01' and '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."' and e.not_include_head_count='0'";
//die;
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchRow();
				}
			}
			$arrNew = array();
			$newReportingHeads = array();
			
			//echo 'inside<pre>' ; print_r($arrEmployee);print_r($ids);
				
			foreach($arrEmployee as $emp)
			{
				//echo '<br>inhere-------------------------<br>';
				$CheckEmployeeReportingHead = $this->fnCheckEmployeeReportingHeadHierarchy($emp['id'],$emp['relieving_date_by_manager']);
				//echo '<pre>'; print_r($emp);
				$remove = array(0);
				$final_reporting_heads_hierarchy = array_diff($CheckEmployeeReportingHead, $remove);
			
				//die;
				
				//echo '<br>repor_head:';print_r($repor_head);
				
				if(isset($ids) && count($ids) > '0')
				{
					//echo '<br>$ids:';
					//print_r($ids);
					//echo '<br>final_reporting_heads_hierarchy:';
					//print_r($final_reporting_heads_hierarchy);
					//echo '<br>++++++++++++';
					$result = array_intersect($ids, $final_reporting_heads_hierarchy);
					//echo '<br>result:';print_r($result);
					if(count($result) > 0)
					{
						//echo '<br>hello123';
						$arrNew[] = $emp;
						//print_r($arrNew);
					}
				}
			}
			
			
			return $arrNew;
		}
		
		function fnGetEmployeesForHrAttritionBetweenMonths($id,$month,$year,$repor_head)
		{
			$arrEmployee = array();
			include_once("class.attendance.php");
			$objAttendance = new attendance();
			$getAllReporintHeads = $objAttendance->fnGetEmployees();
			//echo '<pre>id:'.$id.'<br>month:'.$month.'<br>year'.$year.'<br>reportingHead:'.$repor_head;
			//print_r($_SESSION);
				
			
			$sSQL = "select e.id, e.employee_code, e.name,date_format(e.date_of_joining, '%d-%m-%Y') as date_of_joining,e.teamleader, date_format(e.relieving_date_by_manager, '%Y-%m') as relieving_date_by_manager,date_format(e.relieving_date_by_manager, '%d-%m-%Y') as rel_date_by_manager,e.reason_of_leaving,e.terminated_absconding_resigned,date_format(e.date_of_joining,'%Y-%m-%d') as doj,date_format(e.relieving_date_by_manager,'%Y-%m-%d') as rel_date,TIMESTAMPDIFF(MONTH , e.date_of_joining, e.relieving_date_by_manager ) as months_worked from pms_employee e where date_format(e.relieving_date_by_manager, '%Y-%m') = '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."' and e.not_include_head_count='0'";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchRow();
				}
			}
			$arrNew = array();
			$newReportingHeads = array();
			if($repor_head == '0')
			{
				
				//echo 'hello<pre>'; print_r($getAllReporintHeads);
				if(count($getAllReporintHeads))
				{
					foreach($getAllReporintHeads as $heads)
					{
						$newReportingHeads[] = $heads['employee_id'];
					}
				}
			}
			foreach($arrEmployee as $emp)
			{
				//echo '<br>'; print_r($emp); 
				
				//echo '<pre>' ; print_r($emp);
				$CheckEmployeeReportingHead = $this->fnCheckEmployeeReportingHead($emp['id'],$emp['relieving_date_by_manager']);
				//echo '<br>CheckEmployeeReportingHead:'.$CheckEmployeeReportingHead.'<br>repor_head:'.$repor_head;
				if(($repor_head == 'all' || $repor_head == '0'))
				{
					if(in_array($emp['teamleader'],$newReportingHeads))
					{
						$arrNew[] = $emp;
					}
				}
				else
				{
					if(($CheckEmployeeReportingHead == $emp['teamleader']) && ($emp['teamleader'] == $repor_head))
					{
						$arrNew[] = $emp;
					}
				}
			}
			//echo '<pre>'; print_r($arrNew); die;
			return $arrNew;
		}
		
		function fnGetEmployeesForAttritionBetweenMonths($id,$month,$year,$repor_head)
		{
			include_once("class.employee.php");
			$objEmployee = new employee();
			$arrEmployee = array();

			$sSQL = "select e.id, e.employee_code, e.name,date_format(e.date_of_joining, '%d-%m-%Y') as date_of_joining,e.teamleader, date_format(e.relieving_date_by_manager, '%Y-%m') as relieving_date_by_manager,date_format(e.relieving_date_by_manager, '%d-%m-%Y') as rel_date_by_manager,e.reason_of_leaving,e.terminated_absconding_resigned,date_format(e.date_of_joining,'%Y-%m-%d') as doj,date_format(e.relieving_date_by_manager,'%Y-%m-%d') as rel_date,TIMESTAMPDIFF(MONTH , e.date_of_joining, e.relieving_date_by_manager ) as months_worked from pms_employee e where date_format(e.relieving_date_by_manager, '%Y-%m') between '".mysql_real_escape_string($year)."-01' and '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."' and e.not_include_head_count='0'";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchRow();
				}
			}
			//echo 'here';
			$arrNew = array();
			if(count($arrEmployee) > 0)
			{
				//echo 'Inhere';
				foreach($arrEmployee as $emp)
				{
					//echo '<br>inhere-------------------------<br>';
					$CheckEmployeeReportingHead = $this->fnCheckEmployeeReportingHeadHierarchy($emp['id'],$emp['relieving_date_by_manager']);
					//echo '<pre>'; print_r($CheckEmployeeReportingHead);
					$remove = array(0);
					$final_reporting_heads_hierarchy = array_diff($CheckEmployeeReportingHead, $remove);
				
					//die;
					
					//echo '<br>final_reporting_heads_hierarchy:';print_r($final_reporting_heads_hierarchy);
					//echo $repor_head;
					$arrReportingHead = $objEmployee->fnTeamLeaderExistForManager($_SESSION['id']);
					$arrTl = array();
					if(count($arrReportingHead) > 0 )
					{
						
						foreach($arrReportingHead as $curReportingHead)
						{
							if(isset($curReportingHead['tl_id']))
							{
								array_push($arrTl,$curReportingHead['tl_id']);
							}
						}
					}
					$arrTl[] = $_SESSION['id'];
					//print_r($arrTl); die;
					
					if(isset($repor_head) && ($repor_head== 'all' || $repor_head== '0'))
					{
						if(count($arrTl) > 0)
						{
							$result = array_intersect($arrTl, $final_reporting_heads_hierarchy);
						}
						//echo '<br>result:';print_r($result);
						if(count($result) > 0)
						{
							//echo '<br>hello123';
							$arrNew[] = $emp;
							//print_r($arrNew);
						}	
					}
					else
					{
						if(isset($repor_head))
						{
							if(in_array($repor_head,$final_reporting_heads_hierarchy))
							{
								$arrNew[] = $emp;
							}
							//$result = array_intersect($repor_head, $final_reporting_heads_hierarchy);
						}
					}
					if(isset($ids) && count($ids) > '0')
					{
						//echo '<br>$ids:';
						//print_r($ids);
						//echo '<br>final_reporting_heads_hierarchy:';
						//print_r($final_reporting_heads_hierarchy);
						//echo '<br>++++++++++++';
						$result = array_intersect($ids, $final_reporting_heads_hierarchy);
						//echo '<br>result:';print_r($result);
						if(count($result) > 0)
						{
							//echo '<br>hello123';
							$arrNew[] = $emp;
							//print_r($arrNew);
						}
					}
				}
				//print_r($arrNew); die;
				return $arrNew;
			}
		}
		
		function fnCheckEmployeeReportingHead($id,$date)
		{
			$reporting_head = '0';
			$sSQL = "select `rep_head` from `pms_rep_heads_history` where date_format(`modified_date`,'%Y-%m') <= '$date' and userid = '$id' order by `id` desc LIMIT 0,1";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$reporting_head = $this->f('rep_head');
				}
			}
			return $reporting_head;
		}
		
		function fnCheckEmployeeReportingHeadHierarchy($id,$date)
		{
			$reporting_heads = array();
			$db = new DB_Sql();
			
			$sSQL = "select `rep_head` from `pms_rep_heads_history` where date_format(`modified_date`,'%Y-%m') <= '$date' and userid = '$id' order by `id` desc LIMIT 0,1";
			$db->query($sSQL);
			if($db->num_rows() > 0)
			{
				if($db->next_record())
				{
					$reporting_heads[] = $db->f("rep_head");
					//echo '<br>here<pre>';print_r($reporting_heads);
					if($db->f('rep_head') != '0')
					{
						//echo '<br>inhere<br>';
						$arrTemp = $this->fnCheckEmployeeReportingHeadHierarchy($db->f('rep_head'),$date);
						//echo '<br>________________<pre>';print_r($arrTemp);
						$reporting_heads = array_merge($reporting_heads,$arrTemp);
						
						//echo '<br>========<pre>';print_r($reporting_heads);
					}
					//echo '<br>in here	'; print_r($arrTemp);
					
				}
			}
			//echo 'hereaaa<pre>'; print_r($reporting_heads);
			return $reporting_heads;
		}
		
		function fnTeamLeaderExistForManager($id)
		{
			$arrValues = array();
			$sSQL = "select e1.id as e1Id,e2.id as tl_id,e3.id as agId from pms_employee as e1 left join pms_employee as e2 on e2.teamleader=e1.id left join pms_employee as e3 on e3.teamleader = e2.id where e1.id='$id' and e3.id is NOT NULL";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrValues[] = $this->fetchRow();
				}
			}
			if(count($arrValues) > 0)
			{
				$arrVal = array();
				$sql = "select e1.id as mnId,e2.id as tl_id,e2.name as tlName,e3.id as agId from pms_employee as e1 left join pms_employee as e2 on e2.teamleader=e1.id left join pms_employee as e3 on e3.teamleader = e2.id where e1.id='$id' and e3.id is NOT NULL group by e2.id";
				$this->query($sql);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrVal[] = $this->fetchRow();
					}
				}
				//echo '<pre>'; print_r($arrVal); die;
				return $arrVal;
			}
		}
		
		function fnGetAllReportingHeads()
		{
			$arrVal = array();
			$sql = "SELECT DISTINCT teamleader FROM pms_employee where teamleader > '0'";
			$this->query($sql);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrVal[] = $this->f('teamleader');
				}
			}
			return $arrVal;
		}
		
		function fnGetHeadCount($rep_id)
		{
			$prev_date = date("Y-m-d", strtotime( '-1 days' ) );
			$date = date('Y-m-01'); // hard-coded '01' for first day
			//$date = date('Y-02-01'); // hard-coded '01' for first day
			//echo '<br>end_date:'.$end_date  = date('Y-m-t');
			//$end_date  = date('Y-02-28');

			$arrData = array();
			$arrData['added_date'] = date('Y-m-d H:i:s');
			//while(strtotime($date) <= strtotime($end_date))
			//{
			//echo '<br>reoringHead:'.$rep_id;
			//echo "<br>date:".$date;
			//echo '<br>here1';
			$getDetail = $this->fnGetHeadCountDateWise($rep_id,$prev_date);
			//echo 'here3';
			//echo '<pre>'; print_r($getDetail);
			$head_count = count($getDetail);
			/*if(count($getDetail)>0)
			{
				$arrTemp = $getDetail;
				foreach($getDetail as $detail)
				{
					$getDetail = $this->fnGetHeadCountDateWise($detail['id'],$date);
				}
			}*/
			
			$arrData['date'] = $prev_date;
			$arrData['userid'] = $rep_id ;
			$arrData['head_count'] = $head_count;
			//echo '<pre>'; print_r($arrData);
			
			if(count($arrData) > 0)
			{
				$checkEntryExist = $this->fnCheckEntryExistForHeadCount($arrData['userid'],$prev_date);
				//echo '<br>checkEntryExist:'.$checkEntryExist;
				if($checkEntryExist == '0')
				{
					$arrData['id'] ='';
					$this->insertArray('pms_head_counting',$arrData);
				}
				else
				{
					$arrData['id'] = $checkEntryExist;
					$this->updateArray('pms_head_counting',$arrData);
				}
			}
			//die;
			//}
		}
		
		function fnGetHeadCountById($rep_id,$date)
		{
			//$prev_date = date("Y-m-d", strtotime( '-1 days' ) );
			//echo '<br>date:'.$date = date('Y-m-01'); // hard-coded '01' for first day
			//$date = date('Y-02-01'); // hard-coded '01' for first day
			//echo '<br>end_date:'.$end_date  = date('Y-m-t');
			//$end_date  = date('Y-02-28');

			$arrData = array();
			$arrData['added_date'] = date('Y-m-d H:i:s');
			//while(strtotime($date) <= strtotime($end_date))
			//{
			//echo '<br>reoringHead:'.$rep_id;
			//echo "<br>date:".$date;
			//echo '<br>here1';
			$getDetail = $this->fnGetHeadCountDateWise($rep_id,$date);
			//echo 'here3';
			//echo '<br>---------------------------------<br><pre>'; print_r($getDetail);
			$head_count = count($getDetail);
			/*if(count($getDetail)>0)
			{
				$arrTemp = $getDetail;
				foreach($getDetail as $detail)
				{
					$getDetail = $this->fnGetHeadCountDateWise($detail['id'],$date);
				}
			}*/
			
			$arrData['date'] = $date;
			$arrData['userid'] = $rep_id ;
			$arrData['head_count'] = $head_count;
			//echo '<pre>'; 
			print_r($arrData);
			
			if(count($arrData) > 0)
			{
				$checkEntryExist = $this->fnCheckEntryExistForHeadCount($arrData['userid'],$date);
				//echo '<br>checkEntryExist:'.$checkEntryExist;
				if($checkEntryExist == '0')
				{
					$arrData['id'] ='';
					$this->insertArray('pms_head_counting',$arrData);
				}
				else
				{
					$arrData['id'] = $checkEntryExist;
					$this->updateArray('pms_head_counting',$arrData);
				}
			}
			//die;
			//}
		}
		
		function fnGetHeadCountForEachEmployeeUsingDate($rep_id,$month,$year)
		{
			//echo '<br>in Here...rep_id:'.$rep_id.'+++++++month:'.$month; // hard-coded '01' for first day
			//$date = date('Y-02-01');
			$first_date = $year.'-'.$month.'-01';
			//echo '<br>first_date:'.$first_date = '2014-3-01';
			$last_date = $year.'-'.$month.'-'.date('t');
			//echo '<br>last_date:'.$last_date = '2014-3-15';

			
			 // hard-coded '01' for first day
			//echo '<br>end_date:'.$end_date  = date('Y-m-t');
			//$end_date  = date('Y-02-28');

			$arrData = array();
			$arrData['added_date'] = date('Y-m-d H:i:s');
			while (strtotime($first_date) <= strtotime($last_date))
			{
				//echo '<br><br><br>--------------reoringHead:'.$rep_id;
				//echo "========<br>date:".$first_date;
				$getDetail = $this->fnGetHeadCountDateWise($rep_id,$first_date);
				//echo 'here<pre>'; print_r($getDetail);
				$head_count = count($getDetail);
				//die;
				/*if(count($getDetail)>0)
				{
					$arrTemp = $getDetail;
					foreach($getDetail as $detail)
					{
						$getDetail = $this->fnGetHeadCountDateWise($detail['id'],$date);
					}
				}*/
				
				
				$arrData['date'] = $first_date;
				$arrData['userid'] = $rep_id ;
				$arrData['head_count'] = $head_count;
				//echo '<br>-------------------<pre>'; print_r($arrData);echo '===================='; 
				if(count($arrData) > 0)
				{
					$checkEntryExist = $this->fnCheckEntryExistForHeadCount($arrData['userid'],$arrData['date']);
					
					if($checkEntryExist == '0')
					{
						$arrData['id'] ='';
						$this->insertArray('pms_head_counting',$arrData);
					}
					else
					{
						$arrData['id'] = $checkEntryExist;
						
						$this->updateArray('pms_head_counting',$arrData);
					}
					
				}
				
				$first_date = date ("Y-m-d", strtotime("+1 day", strtotime($first_date)));
			}
		}
		
		function fnCheckEntryExistForHeadCount($userId,$entryForDate)
		{
			$headId = 0;
			$query = "select id from pms_head_counting where date_format(date,'%Y-%m-%d')='$entryForDate' and userid = '$userId'";
			$this->query($query);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$headId = $this->f('id');
				}
			}
			return $headId;
		}
		
		function fnGetHeadCountUsingUserId($uId,$date)
		{
			$headcount = 0;
			//echo '<br>'.
			$query = "select `head_count` from pms_head_counting where userid in ($uId) and date_format(date,'%Y-%m-%d') = '$date'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$headcount = $this->f('head_count');
				}
			}
			
			return $headcount;
		}
		
		function fnGetHeadCountDateWise($report_head,$date)
		{
			//echo '<br>repor_head:'.$report_head;
			//echo '<br>date:'.$date;
			
			$db = new DB_Sql;
			$arrEmployees = array();

			//$current = date('Y-m-d');

			//echo $query = "select h.id,h.userid,h.modified_date,h.rep_head from `pms_rep_heads_history` as h where h.`rep_head` = '$report_head' and h.id in (select id from (SELECT hi.`id` as hid,hi.`modified_date`,em.`id` FROM `pms_rep_heads_history` as hi left join pms_employee as em on hi.userid=em.id where date_format(em.`date_of_joining`,'%Y-%m-%d') <= '$date' and (date_format(em.`relieving_date_by_manager`,'%Y-%m-%d') > '$date' OR date_format( em.`relieving_date_by_manager` , '%Y-%m-%d' ) = '0000-00-00')  GROUP BY hi.`userid` HAVING date_format(hi.`modified_date` , '%Y-%m-%d' ) <= '$date' AND date_format(hi.`modified_date` , '%Y-%m-%d' ) = date_format( max( hi.`modified_date` ) , '%Y-%m-%d' )) as a)";
			
			//echo $query = "select h.id,h.userid,h.modified_date,h.rep_head from `pms_rep_heads_history` as h where h.`rep_head` = '$report_head' and h.id in (select id from (SELECT hi.`id` as id,hi.`modified_date` FROM `pms_rep_heads_history` as hi left join pms_employee as em on hi.userid=em.id where date_format(em.`date_of_joining`,'%Y-%m-%d') <= '$date' and (date_format(em.`relieving_date_by_manager`,'%Y-%m-%d') > '$date' OR date_format( em.`relieving_date_by_manager` , '%Y-%m-%d' ) = '0000-00-00')  GROUP BY hi.`userid` HAVING date_format(hi.`modified_date` , '%Y-%m-%d' ) <= '$date' AND date_format(hi.`modified_date` , '%Y-%m-%d' ) = date_format( max( hi.`modified_date` ) , '%Y-%m-%d' )) as a)";
			
			//echo $query = "select h.id,h.userid,h.modified_date,h.rep_head from `pms_rep_heads_history` as h where h.`rep_head` = '$report_head' and h.id in (select id from (SELECT hi.`id` as id,hi.`modified_date` FROM `pms_rep_heads_history` as hi left join pms_employee as em on hi.userid=em.id where date_format(em.`date_of_joining`,'%Y-%m-%d') <= '$date' and (date_format(em.`relieving_date_by_manager`,'%Y-%m-%d') > '$date' OR date_format( em.`relieving_date_by_manager` , '%Y-%m-%d' ) = '0000-00-00')  GROUP BY hi.`userid` HAVING date_format(hi.`modified_date` , '%Y-%m-%d' ) <= '$date') as a)";
			
			//echo '<br>---------------------------<br>'.$query = "select * from (select userid as tmpusr, max(date_format(modified_date,'%Y-%m-%d')) as tmpdt from pms_rep_heads_history where date_format(modified_date,'%Y-%m-%d') <= '$date' group by userid) as a INNER JOIN pms_rep_heads_history ON (userid = tmpusr and date_format(modified_date,'%Y-%m-%d') = tmpdt) left join pms_employee as e on e.id = userid where date_format(e.`date_of_joining`,'%Y-%m-%d') <= '$date' and (date_format(e.`relieving_date_by_manager`,'%Y-%m-%d') > '$date' OR date_format( e.`relieving_date_by_manager` , '%Y-%m-%d' ) = '0000-00-00') and rep_head = '$report_head'";
			
			/* echo '<br>LAST UPD 23Apr2014 ---- MOdified this query by chandni as the
			 *  head count was not calcualted propery
			 * ---------------------------<br>'.
			 * $query = "select * from (select userid as tmpusr, max(date_format(modified_date,'%Y-%m-%d')) as tmpdt from pms_rep_heads_history where date_format(modified_date,'%Y-%m-%d') <= '$date' and rep_head = '$report_head' group by userid) as a INNER JOIN pms_rep_heads_history ON (userid = tmpusr and date_format(modified_date,'%Y-%m-%d') = tmpdt) left join pms_employee as e on e.id = userid where date_format(e.`date_of_joining`,'%Y-%m-%d') <= '$date' and (date_format(e.`relieving_date_by_manager`,'%Y-%m-%d') > '$date' OR date_format( e.`relieving_date_by_manager` , '%Y-%m-%d' ) = '0000-00-00') and rep_head = '$report_head'";
			* 
			**/
			
			$query = "select * from (select h.userid as tmpusr, date_format(h.modified_date,'%Y-%m-%d') as tmpdt from pms_rep_heads_history h LEFT JOIN pms_rep_heads_history h1 ON (h.userid = h1.userid and date_format(h1.modified_date,'%Y-%m-%d') = (select date_format(modified_date,'%Y-%m-%d') from pms_rep_heads_history  where date_format(modified_date,'%Y-%m-%d') > date_format(h.modified_date,'%Y-%m-%d') and userid = h.userid order by modified_date limit 0,1)) where h.rep_head='".mysql_real_escape_string($report_head)."' and ('".mysql_real_escape_string($date)."' between date_format(h.modified_date,'%Y-%m-%d') and date_format(DATE_SUB(h1.modified_date,INTERVAL 1 DAY),'%Y-%m-%d') or ('".mysql_real_escape_string($date)."' >= date_format(h.modified_date,'%Y-%m-%d') and date_format(h1.modified_date,'%Y-%m-%d') is null))) as a INNER JOIN pms_rep_heads_history ON (userid = tmpusr and date_format(modified_date,'%Y-%m-%d') = tmpdt) left join pms_employee as e on e.id = userid where date_format(e.date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($date)."' and (date_format(DATE_ADD(e.relieving_date_by_manager,INTERVAL 1 DAY),'%Y-%m-%d') > '".mysql_real_escape_string($date)."' OR date_format(e.relieving_date_by_manager, '%Y-%m-%d') = '0000-00-00') and rep_head = '".mysql_real_escape_string($report_head)."'";
			
			//$sSQL = "select * from pms_rep_head_history h LEFT JOIN pms_rep_head_history h1 ON (h.userid=h1.userid and h.modified_date = (select date_format(modified_date) from pms_rep_head_history where date_format(modified_date) > date_format(h1.modified_date) and userid=h.userid order by modified_date limit 0,1) where h.rep_head='".mysql_real_escape_string($report_head)."' and '".mysql_real_escape_string($date)."' >= date_format(h.modified_date,'%Y-%m-%d') and '".mysql_real_escape_string($date)."' <= date_format(h1.modified_date,'%Y-%m-%d')";
			
			$db->query($query);

			if($db->num_rows() > 0 )
			{
				while($db->next_record())
				{
					if($db->f("not_include_head_count") != '1')
						$arrEmployees[$db->f("userid")] = $db->f("userid");
					if($report_head != $db->f("userid"))
					{
						//echo 'hello';
						//$tmpData = $this->fnGetAllEmployeesByHeadAndDesignation($db->f("id"), $designation);
						$tmpData = $this->fnGetHeadCountDateWise($db->f("userid"), $date);
						$arrEmployees = $arrEmployees + $tmpData;
					}
				}
			}
			
			//print_r($arrEmployees);
			return $arrEmployees;
		}
		
		function fnGetReportingHeadHistory($emp_id)
		{
			$arrHistory = array();
			$query = "SELECT h.id as his_id, h.rep_head, date_format( h.modified_date, '%d-%m-%Y' ) AS effective_date, e.name AS reportingHead,em.name as employeeName,em.designation as emp_designation FROM `pms_rep_heads_history` AS h LEFT JOIN pms_employee AS e ON h.rep_head = e.id left join pms_employee as em on h.userid = em.id WHERE h.userid = '$emp_id'";
			$this->query($query);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrHistory[] = $this->fetchRow();
				}
			}
			//echo '<pre>'; print_r($arrVal); die;
			return $arrHistory;
		}
		
		function fnGetReportingHistoryHeadById($hid)
		{
			$arrHistory = array();
			$query = "SELECT h.id as his_id,h.userid, h.rep_head,date_format(h.modified_date,'%Y-%m-%d') as ef_date, date_format( h.modified_date, '%d-%m-%Y' ) AS effective_date, e.name AS reportingHead,em.name as employeeName,em.designation as emp_designation FROM `pms_rep_heads_history` AS h LEFT JOIN pms_employee AS e ON h.rep_head = e.id left join pms_employee as em on h.userid = em.id WHERE h.id = '$hid'";
			$this->query($query);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrHistory = $this->fetchRow();
				}
			}
			//echo '<pre>'; print_r($arrVal); die;
			return $arrHistory;
		}
		
		function fnUpdateReportingHeadHistory($arrPostData)
		{
			//echo '<pre>'; print_r($arrPostData);
			$prevDetails = $this->fnGetReportingHistoryHeadById($arrPostData['id']);
			//print_r($prevDetails);
			//echo '<br>effective_date:'.$arrPostData['effective_date'].'<br>eff:'.$prevDetails['ef_date']; 
			if($arrPostData['effective_date'] > $prevDetails['ef_date'])
			{
				$change_month = date("m",strtotime($prevDetails['ef_date']));
				$change_year = date("Y",strtotime($prevDetails['ef_date']));
			}
			else
			{
				$change_month = date("m",strtotime($arrPostData['effective_date']));
				$change_year = date("Y",strtotime($arrPostData['effective_date']));
			}
			$change_date = strtotime($change_year.'-'.$change_month.'-'.'01');
			$cur_date = strtotime(date('Y-m-d'));
			//echo '<br>month:'.$change_month; echo '<br>year:'.$change_year;
			
			$getAllReporintHeads = $this->fnGetReportHeadIdsHierarchy($arrPostData['uid']);
			//print_r($getAllReporintHeads);
			$test = array();
			if($prevDetails['rep_head'] != $arrPostData['rep_head'])
			{
				$test = $this->fnGetReportHeadIdsHierarchy($arrPostData['rep_head']);
				$test[] = $prevDetails['rep_head'];
			}
			else
			{
				/* Fetch reporting head before the current reporting head */
				$sSQL = "select rep_head from pms_rep_heads_history where userid='".mysql_real_escape_string($arrPostData['uid'])."' and date_format(modified_date,'%Y-%m-%d') < '".mysql_real_escape_string($prevDetails['ef_date'])."' order by date_format(modified_date,'%Y-%m-%d') desc limit 0,1";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$test = $this->fnGetReportHeadIdsHierarchy($this->f("rep_head"));
						$test[] = $this->f("rep_head");
					}
				}
			}
			//echo '<br>------------'; print_r($test);echo '<br>==============';
			$result = array_merge($getAllReporintHeads,$test);
			//print_r($result);
			$final_reporting_heads_hierarchy = array_unique($result);
			//print_r($final_reporting_heads_hierarchy);
			//die;	
			

			//echo '<br>changeMonth'.$change_month;
			$curMonth = date('m');
			$curYear = date('Y');

			//echo '<br>change_month:'.$change_month.'>>>====curMonth:'.$curMonth;

			$arrPost = array();
			$arrPost['id'] = $arrPostData['id'];
			$arrPost['rep_head'] = $arrPostData['rep_head'];
			$arrPost['modified_date'] = $arrPostData['effective_date'];
			$arrPost['added_by'] = $_SESSION['id'];
			//print_r($arrPost); die;
			$this->updateArray('pms_rep_heads_history',$arrPost);

			$start = $month = strtotime('2009-02-01');
			$end = strtotime('2011-01-01');
			
			//echo '<br>chagne date:'.$change_date.'<br>cur Date:'.$cur_date;
			//die;
			
			foreach($final_reporting_heads_hierarchy as $heads)
			{
				//echo 'this';
				//$temp = 3;
				$temp_date = $change_date;
				$temp_year = $change_year;
				//echo '<br>in here heads:'.$heads;
				while($temp_date < $cur_date)
				{
					$month = date('m', $temp_date);
					$year = date('Y',$temp_date);
					$headCount = $this->fnGetHeadCountForEachEmployeeUsingDate($heads,$month,$year);
					$temp_date = strtotime("+1 month", $temp_date);
				}
				//die;
				/*
				while($temp_month <= $curMonth)
				{
					echo '<br>temp:'.$temp_month.'<br>curMonth:'.$curMonth;
					//
					echo '<br>tamp:'.$temp_month;
					$temp_month++;
					echo '<br>=============hello';
				}*/
			}
			//print_r($getAllReporintHeads);
			//die;

			return $arrPostData['uid'];
		}
		
		function fnUpdateHeadCount($arrPost)
		{
			//echo '<pre>'; print_r($arrPost);
			$change_date = strtotime(date('Y-m-01'));
			$cur_date = strtotime(date('Y-m-d'));
			//echo '<br>month:'.$change_month; echo '<br>year:'.$change_year;
			
			$getAllReporintHeads = $this->fnGetReportHeadIdsHierarchy($arrPost['id']);
			//print_r($getAllReporintHeads);
			$test = array();
			if($arrPost['hdnteamleader'] != $arrPost['teamleader'])
			{
				$test = $this->fnGetReportHeadIdsHierarchy($arrPost['hdnteamleader']);
				$test[] = $arrPost['hdnteamleader'];
			}
			//echo '<br>------------'; print_r($test); echo '<br>==============';
			$result = array_merge($getAllReporintHeads,$test);
			//print_r($result);
			$final_reporting_heads_hierarchy = array_unique($result);
			//print_r($final_reporting_heads_hierarchy);
			//die;
			$month = date('m');
			$year = date('Y');
			foreach($final_reporting_heads_hierarchy as $heads)
			{
				//echo '<br>in here heads:'.$heads;
				$headCount = $this->fnGetHeadCountForEachEmployeeUsingDate($heads,$month,$year);
			}
			return true;
		}
		
		function fnDeleteReportingHeadHistory($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_employee` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}
		
		function fnGetOverallHeadCount($repo_heads,$date)
		{
			$temp = $newArray = array();
			
			foreach($repo_heads as $rep)
			{
				$getAllReporintHeads = $this->fnGetReportHeadIdsHierarchy($rep);

				$common_rep_heads = array_intersect($getAllReporintHeads,$repo_heads);

				if(count($common_rep_heads) > 0)
				{
					$temp = $this->fnGetOverallHeadCount($common_rep_heads,$date);
					$newArray = array_merge($newArray,$temp);
				}
				else
				{
					$newArray[] = $rep;
				}
			}
			$final_result_newArray = array_unique($newArray);
			return $final_result_newArray;
		}
		
		function fnFetchActiveEmployeesForDesignation()
		{
			$arrEmployeeCount = array();
			
			$sSQL = "select count(e.id) as employee_count, d.id as designation_id, d.title as designation_title from pms_employee e INNER JOIN pms_designation d ON d.id = e.designation where status = '0' group by designation order by d.title";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$arrEmployeeCount[] = $this->fetchRow();
				}
			}

			return $arrEmployeeCount;
		}
		
		function fnGetAllReportingHeadsDetails($searchYear, $searchMonth)
		{
			if($searchYear != "")
				$strCond = $searchYear.'-';
			else
				$strCond = Date('Y-');

			if($searchMonth != "")
				$strCond .= $searchMonth;
			else
				$strCond .= Date('m');

			$arrReportingHead = array();
			$sql = "SELECT DISTINCT e.teamleader as reportinghead_id, e1.name as reportinghead_name FROM pms_employee e INNER JOIN pms_employee e1 ON e.teamleader = e1.id where e.teamleader > '0' and ((e1.status='0' and date_format(e1.date_of_joining,'%Y-%m') <= '".mysql_real_escape_string($strCond)."') or (date_format(e1.relieving_date_by_manager,'%Y-%m') >= '".mysql_real_escape_string($strCond)."' and e1.status='1')) order by e1.name";
			$this->query($sql);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrReportingHead[] = $this->fetchRow();
				}
			}
			return $arrReportingHead;
		}
		
		function fnGetAllReportingHeadsDetailsById($headId, $searchYear, $searchMonth)
		{
			if($searchYear != "")
				$strCond = $searchYear.'-';
			else
				$strCond = Date('Y-');

			if($searchMonth != "")
				$strCond .= $searchMonth;
			else
				$strCond .= Date('m');

			$arrHeads = array();
			$db = new DB_Sql();

			$sSQL = "select e.id as emp_id, e1.id as reportinghead_id, e1.name as reportinghead_name from pms_employee e INNER JOIN pms_employee e1 ON e.teamleader = e1.id where e.teamleader='".mysql_real_escape_string($headId)."' and ((e1.status='0' and date_format(e1.date_of_joining,'%Y-%m') <= '".mysql_real_escape_string($strCond)."') or (date_format(e1.relieving_date_by_manager,'%Y-%m') >= '".mysql_real_escape_string($strCond)."' and e1.status='1'))";
			$db->query($sSQL);
			if($db->num_rows() > 0)
			{
				while($db->next_record())
				{
					if(!isset($arrHeads[$db->f("reportinghead_id")]))
						$arrHeads[$db->f("reportinghead_id")] = $db->fetchRow();

					$arrTemp = $this->fnGetAllReportingHeadsDetailsById($db->f("emp_id"), $searchYear, $searchMonth);

					$arrHeads = $arrHeads + $arrTemp;
				}
			}

			return $arrHeads;
		}


		function fnGetDailyHeadCount($month, $year, $reporting_head, $isadmin = false)
		{
			$firstDate = $year.'-'.$month.'-01';
			$lastDate = date ("Y-m-t",strtotime($firstDate));
			$curDate = Date('Y-m-d');

			if($lastDate > $curDate)
				$lastDate = $curDate;

			$arrHeads = $arrDailyHeadCounts = array();

			if($reporting_head != "" && $reporting_head != "0" && !is_array($reporting_head))
				$arrHeads[] = array("reportinghead_id" => $reporting_head);
			else
			{
				if($isadmin)
					$arrHeads = $this->fnGetAllReportingHeadsDetails($year, $month);
				else if(is_array($reporting_head))
					$arrHeads = $reporting_head;
			}

			if(count($arrHeads) > 0)
			{
				foreach($arrHeads as $curReportingHead)
				{
					$calculationFirstDate = $firstDate;
					while($calculationFirstDate <= $lastDate)
					{
						/* Fetch Opeaning head count */
						$arrDateHeadCount = $this->fnGetHeadCountDateWiseOpeaningBalance($curReportingHead["reportinghead_id"], $calculationFirstDate);
						
						$cntHeadCount = count($arrDateHeadCount);
						
						/* Fetch leavers by date */
						$arrDateHeadCount1 = $arrDateHeadCount;
						$arrDateHeadCount1[] = $curReportingHead["reportinghead_id"];
						
						$arrLeaversCount = $this->fnGetHeadCountDateWiseLeavers($arrDateHeadCount1, $calculationFirstDate);
						
						/* Fetch joiners by date */
						$arrJoinersCount = $this->fnGetHeadCountDateWiseJoiners($arrDateHeadCount1, $calculationFirstDate);
						
						$cntJoinersCount = count(array_diff($arrJoinersCount, $arrLeaversCount));
						
						$cntLeaversCount = count(array_diff($arrLeaversCount, $arrJoinersCount));
						
						$cntOpeaningHeadCount = $cntHeadCount - $cntJoinersCount + $cntLeaversCount;
						$cntClosingHeadCount = $cntOpeaningHeadCount + $cntJoinersCount - $cntLeaversCount;
						
						$arrDailyHeadCounts[$curReportingHead["reportinghead_id"]][$calculationFirstDate]["OpeaningHeadCount"] = $cntOpeaningHeadCount;
						$arrDailyHeadCounts[$curReportingHead["reportinghead_id"]][$calculationFirstDate]["JoinersCount"] = $cntJoinersCount;
						$arrDailyHeadCounts[$curReportingHead["reportinghead_id"]][$calculationFirstDate]["LeaversCount"] = $cntLeaversCount;
						$arrDailyHeadCounts[$curReportingHead["reportinghead_id"]][$calculationFirstDate]["ClosingHeadCount"] = $cntClosingHeadCount;

						$calculationFirstDate = date ("Y-m-d", strtotime("+1 day", strtotime($calculationFirstDate)));
					}
				}
			}
			
			return $arrDailyHeadCounts;
		}
		
		function fnGetHeadCountDateWiseOpeaningBalance($report_head,$date)
		{
			$db = new DB_Sql;
			$arrEmployees = array();
			
			//select userid as tmpusr, max(date_format(modified_date,'%Y-%m-%d')) as tmpdt from pms_rep_heads_history where date_format(modified_date,'%Y-%m-%d') <= '".mysql_real_escape_string($date)."' and rep_head = '".mysql_real_escape_string($report_head)."' group by userid
			
			//select h.* from pms_rep_heads_history h LEFT JOIN pms_rep_heads_history h1 ON (h.userid = h1.userid and date_format(h1.modified_date,'%Y-%m-%d') = (select date_format(modified_date,'%Y-%m-%d') from pms_rep_heads_history  where date_format(modified_date,'%Y-%m-%d') > date_format(h.modified_date,'%Y-%m-%d') order by modified_date limit 0,1)) where h.rep_head='2'
			
			//select h.userid as tmpusr, max(date_format(h.modified_date,'%Y-%m-%d')) as tmpdt from pms_rep_heads_history h LEFT JOIN pms_rep_heads_history h1 ON (h.userid = h1.userid and date_format(h1.modified_date,'%Y-%m-%d') = (select date_format(modified_date,'%Y-%m-%d') from pms_rep_heads_history  where date_format(modified_date,'%Y-%m-%d') > date_format(h.modified_date,'%Y-%m-%d') order by modified_date limit 0,1)) where h.rep_head='".mysql_real_escape_string($report_head)."' and '".mysql_real_escape_string($date)."' between date_format(h.modified_date,'%Y-%m-%d') and date_format(h1.modified_date,'%Y-%m-%d')
			
			//echo '<br>-----------------------<br/>'.$sSQL = "select * from (select userid as tmpusr, max(date_format(modified_date,'%Y-%m-%d')) as tmpdt from pms_rep_heads_history where date_format(modified_date,'%Y-%m-%d') <= '".mysql_real_escape_string($date)."' and rep_head = '".mysql_real_escape_string($report_head)."' group by userid) as a INNER JOIN pms_rep_heads_history ON (userid = tmpusr and date_format(modified_date,'%Y-%m-%d') = tmpdt) left join pms_employee as e on e.id = userid where date_format(e.date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($date)."' and (date_format(e.relieving_date_by_manager,'%Y-%m-%d') > '".mysql_real_escape_string($date)."' OR date_format( e.relieving_date_by_manager, '%Y-%m-%d' ) = '0000-00-00') and rep_head = '".mysql_real_escape_string($report_head)."'";
			
			//date_format(e.date_of_joining,'%Y-%m-%d') != '0000-00-00' and
			$sSQL = "select * from (select h.userid as tmpusr, date_format(h.modified_date,'%Y-%m-%d') as tmpdt from pms_rep_heads_history h LEFT JOIN pms_rep_heads_history h1 ON (h.userid = h1.userid and date_format(h1.modified_date,'%Y-%m-%d') = (select date_format(modified_date,'%Y-%m-%d') from pms_rep_heads_history  where date_format(modified_date,'%Y-%m-%d') > date_format(h.modified_date,'%Y-%m-%d') and userid = h.userid order by modified_date limit 0,1)) where h.rep_head='".mysql_real_escape_string($report_head)."' and ('".mysql_real_escape_string($date)."' between date_format(h.modified_date,'%Y-%m-%d') and date_format(DATE_SUB(h1.modified_date,INTERVAL 1 DAY),'%Y-%m-%d') or ('".mysql_real_escape_string($date)."' >= date_format(h.modified_date,'%Y-%m-%d') and date_format(h1.modified_date,'%Y-%m-%d') is null))) as a INNER JOIN pms_rep_heads_history ON (userid = tmpusr and date_format(modified_date,'%Y-%m-%d') = tmpdt) left join pms_employee as e on e.id = userid where date_format(e.date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($date)."' and (date_format(DATE_ADD(e.relieving_date_by_manager, INTERVAL 1 DAY),'%Y-%m-%d') > '".mysql_real_escape_string($date)."' OR date_format( e.relieving_date_by_manager, '%Y-%m-%d' ) = '0000-00-00') and rep_head = '".mysql_real_escape_string($report_head)."'";
			
			$db->query($sSQL);
			if($db->num_rows() > 0)
			{
				while($db->next_record())
				{
					if($db->f("not_include_head_count") != '1')
						$arrEmployees[$db->f("userid")] = $db->f("userid");

					if($report_head != $db->f("userid"))
					{
						$tmpData = $this->fnGetHeadCountDateWiseOpeaningBalance($db->f("userid"), $date);
						$arrEmployees = $arrEmployees + $tmpData;
					}
				}
			}

			return $arrEmployees;
		}
		
		function fnGetHeadCountDateWiseLeavers($report_head,$date)
		{
			$db = new DB_Sql;
			$arrEmployees = array();

			if(is_array($report_head) && count($report_head) > 0)
			{
				$report_head = implode(",", $report_head);
			}

			//echo "<br/>---<br/>".$sSQL = "select * from (select h.userid as tmpusr, date_format(h.modified_date,'%Y-%m-%d') as tmpdt from pms_rep_heads_history h LEFT JOIN pms_rep_heads_history h1 ON (h.userid = h1.userid and date_format(h1.modified_date,'%Y-%m-%d') = (select date_format(modified_date,'%Y-%m-%d') from pms_rep_heads_history  where date_format(modified_date,'%Y-%m-%d') > date_format(h.modified_date,'%Y-%m-%d') and userid = h.userid order by modified_date limit 0,1)) where h.rep_head in (".mysql_real_escape_string($report_head).") and date_format(h1.modified_date,'%Y-%m-%d') = '".mysql_real_escape_string($date)."') as a INNER JOIN pms_rep_heads_history ON (userid = tmpusr and date_format(modified_date,'%Y-%m-%d') = tmpdt) left join pms_employee as e on e.id = userid where  date_format(e.date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($date)."' and (date_format(e.relieving_date_by_manager,'%Y-%m-%d') > '".mysql_real_escape_string($date)."' OR date_format( e.relieving_date_by_manager, '%Y-%m-%d' ) = '0000-00-00') and rep_head in (".mysql_real_escape_string($report_head).")";
			
			//echo '<br>-----------------------<br/>';
			$sSQL = "(select * from (select h.userid as tmpusr, date_format(h.modified_date,'%Y-%m-%d') as tmpdt from pms_rep_heads_history h LEFT JOIN pms_rep_heads_history h1 ON (h.userid = h1.userid and date_format(h1.modified_date,'%Y-%m-%d') = (select date_format(modified_date,'%Y-%m-%d') from pms_rep_heads_history  where date_format(modified_date,'%Y-%m-%d') > date_format(h.modified_date,'%Y-%m-%d') and userid = h.userid order by modified_date limit 0,1)) where h.rep_head in (".mysql_real_escape_string($report_head).") and date_format(h1.modified_date,'%Y-%m-%d') = '".mysql_real_escape_string($date)."') as a INNER JOIN pms_rep_heads_history ON (userid = tmpusr and date_format(modified_date,'%Y-%m-%d') = tmpdt) left join pms_employee as e on e.id = userid where  date_format(e.date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($date)."' and (date_format(DATE_ADD(e.relieving_date_by_manager,INTERVAL 1 DAY),'%Y-%m-%d') >= '".mysql_real_escape_string($date)."' OR date_format( e.relieving_date_by_manager, '%Y-%m-%d' ) = '0000-00-00') and rep_head in (".mysql_real_escape_string($report_head)."))
			
			UNION
			
			(select * from (select h.userid as tmpusr, date_format(h.modified_date,'%Y-%m-%d') as tmpdt from pms_rep_heads_history h LEFT JOIN pms_rep_heads_history h1 ON (h.userid = h1.userid and date_format(h1.modified_date,'%Y-%m-%d') = (select date_format(modified_date,'%Y-%m-%d') from pms_rep_heads_history  where date_format(modified_date,'%Y-%m-%d') > date_format(h.modified_date,'%Y-%m-%d') and userid = h.userid order by modified_date limit 0,1)) where h.rep_head in (".mysql_real_escape_string($report_head).") and ('".mysql_real_escape_string($date)."' between date_format(h.modified_date,'%Y-%m-%d') and date_format(h1.modified_date,'%Y-%m-%d') or ('".mysql_real_escape_string($date)."' >= date_format(h.modified_date,'%Y-%m-%d') and date_format(h1.modified_date,'%Y-%m-%d') is null))) as a INNER JOIN pms_rep_heads_history ON (userid = tmpusr and date_format(modified_date,'%Y-%m-%d') = tmpdt) left join pms_employee as e on e.id = userid where date_format(DATE_ADD(e.relieving_date_by_manager,INTERVAL 1 DAY),'%Y-%m-%d') = '".mysql_real_escape_string($date)."' and rep_head in (".mysql_real_escape_string($report_head)."))";
			
			$db->query($sSQL);
			if($db->num_rows() > 0)
			{
				while($db->next_record())
				{
					if($db->f("not_include_head_count") != '1')
						$arrEmployees[$db->f("userid")] = $db->f("userid");
					/*if($report_head != $db->f("userid"))
					{
						$tmpData = $this->fnGetHeadCountDateWiseLeavers($db->f("userid"), $date);
						$arrEmployees = $arrEmployees + $tmpData;
					}*/
				}
			}

			return $arrEmployees;
		}
		
		function fnGetHeadCountDateWiseJoiners($report_head,$date)
		{
			$db = new DB_Sql;
			$arrEmployees = array();

			if(is_array($report_head) && count($report_head) > 0)
			{
				$report_head = implode(",", $report_head);
			}

			$sSQL = "select * from (select userid as tmpusr, date_format(modified_date,'%Y-%m-%d') as tmpdt from pms_rep_heads_history where rep_head in (".mysql_real_escape_string($report_head).") and date_format(modified_date,'%Y-%m-%d') = '".mysql_real_escape_string($date)."') as a INNER JOIN pms_rep_heads_history ON (userid = tmpusr and date_format(modified_date,'%Y-%m-%d') = tmpdt) left join pms_employee as e on e.id = userid where date_format(e.date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($date)."' and (date_format(e.relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($date)."' OR date_format( e.relieving_date_by_manager, '%Y-%m-%d' ) = '0000-00-00') and rep_head in (".mysql_real_escape_string($report_head).")";
			
			$db->query($sSQL);
			if($db->num_rows() > 0)
			{
				while($db->next_record())
				{
					if($db->f("not_include_head_count") != '1')
						$arrEmployees[$db->f("userid")] = $db->f("userid");
					/*if($report_head != $db->f("userid"))
					{
						$tmpData = $this->fnGetHeadCountDateWiseJoiners($db->f("userid"), $date);
						$arrEmployees = $arrEmployees + $tmpData;
					}*/
				}
			}

			return $arrEmployees;
		}
	
		function fnGetDepartmentWiseDailyHeadCount($month, $year, $departments)
		{
			$firstDate = $year.'-'.$month.'-01';
			$lastDate = date ("Y-m-t",strtotime($firstDate));
			$curDate = Date('Y-m-d');

			if($lastDate > $curDate)
				$lastDate = $curDate;

			if(count($departments) > 0)
			{
				foreach($departments as $curdepartment)
				{
					$calculationFirstDate = $firstDate;
					while($calculationFirstDate <= $lastDate)
					{
						/* Fetch Opeaning head count */
						$arrDateHeadCount = $this->fnGetDepartmentWiseDailyHeadCountOpeaningBalance($curdepartment["id"], $calculationFirstDate);

						$cntHeadCount = count($arrDateHeadCount);

						$arrLeaversCount = $this->fnGetDepartmentWiseDailyLeaversHeadCount($curdepartment["id"], $calculationFirstDate);

						/* Fetch joiners by date */
						$arrJoinersCount = $this->fnGetDepartmentWiseDailyJoinersHeadCount($curdepartment["id"], $calculationFirstDate);

						$cntJoinersCount = count(array_diff($arrJoinersCount, $arrLeaversCount));

						$cntLeaversCount = count(array_diff($arrLeaversCount, $arrJoinersCount));

						$cntOpeaningHeadCount = $cntHeadCount - $cntJoinersCount + $cntLeaversCount;
						$cntClosingHeadCount = $cntOpeaningHeadCount + $cntJoinersCount - $cntLeaversCount;

						$arrDailyHeadCounts[$curdepartment["id"]][$calculationFirstDate]["OpeaningHeadCount"] = $cntOpeaningHeadCount;
						$arrDailyHeadCounts[$curdepartment["id"]][$calculationFirstDate]["JoinersCount"] = $cntJoinersCount;
						$arrDailyHeadCounts[$curdepartment["id"]][$calculationFirstDate]["LeaversCount"] = $cntLeaversCount;
						$arrDailyHeadCounts[$curdepartment["id"]][$calculationFirstDate]["ClosingHeadCount"] = $cntClosingHeadCount;

						$calculationFirstDate = date ("Y-m-d", strtotime("+1 day", strtotime($calculationFirstDate)));
					}
				}
			}
			
			return $arrDailyHeadCounts;
		}
		
		function fnGetDepartmentWiseDailyHeadCountOpeaningBalance($departmentId, $date)
		{
			$db = new DB_Sql;
			$arrEmployees = array();

			$sSQL = "select id from pms_employee where department = '".mysql_real_escape_string($departmentId)."' and date_format(date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($date)."' and (date_format(DATE_ADD(relieving_date_by_manager, INTERVAL 1 DAY),'%Y-%m-%d') > '".mysql_real_escape_string($date)."' OR date_format( relieving_date_by_manager, '%Y-%m-%d' ) = '0000-00-00') and not_include_head_count='0'";
			$db->query($sSQL);
			if($db->num_rows() > 0)
			{
				while($db->next_record())
				{
					$arrEmployees[$db->f("id")] = $db->f("id");
				}
			}

			return $arrEmployees;
		}

		function fnGetDepartmentWiseDailyLeaversHeadCount($departmentId,$date)
		{
			$db = new DB_Sql;
			$arrEmployees = array();

			$sSQL = "select id from pms_employee where department = '".mysql_real_escape_string($departmentId)."' and date_format(DATE_ADD(relieving_date_by_manager,INTERVAL 1 DAY),'%Y-%m-%d') = '".mysql_real_escape_string($date)."' and not_include_head_count='0'";
			$db->query($sSQL);
			if($db->num_rows() > 0)
			{
				while($db->next_record())
				{
					$arrEmployees[$db->f("id")] = $db->f("id");
				}
			}

			return $arrEmployees;
		}
		
		function fnGetDepartmentWiseDailyJoinersHeadCount($departmentId,$date)
		{
			$db = new DB_Sql;
			$arrEmployees = array();

			$sSQL = "select id from pms_employee where department = '".mysql_real_escape_string($departmentId)."' and date_format(date_of_joining,'%Y-%m-%d') = '".mysql_real_escape_string($date)."' and not_include_head_count='0'";
			$db->query($sSQL);
			if($db->num_rows() > 0)
			{
				while($db->next_record())
				{
					$arrEmployees[$db->f("id")] = $db->f("id");
				}
			}

			return $arrEmployees;
		}

	
		function fnGetHeadCountDetails($reportingHead, $date, $detailsFor)
		{
			$arrEmployee = array();

			/* Fetch Opeaning head count */
			$arrDateHeadCount = $this->fnGetHeadCountDateWiseOpeaningBalance($reportingHead, $date);

			$arrDateHeadCount1 = $arrDateHeadCount;
			$arrDateHeadCount1[] = $reportingHead;

			/* Fetch leavers by date */
			$arrLeavers = $this->fnGetHeadCountDateWiseLeavers($arrDateHeadCount1, $date);

			/* Fetch joiners by date */
			$arrJoiners = $this->fnGetHeadCountDateWiseJoiners($arrDateHeadCount1, $date);
			
			$arrNewJoiners = array_diff($arrJoiners, $arrLeavers);
			$arrNewLeavers = array_diff($arrLeavers, $arrJoiners);
			
			//$cntOpeaningHeadCount = $cntHeadCount - $cntJoinersCount + $cntLeaversCount;
			//$cntClosingHeadCount = $cntOpeaningHeadCount + $cntJoinersCount - $cntLeaversCount;

			$arrEmployeeIds = array();
			switch($detailsFor)
			{
				case 1:
					$arrEmployeeIds = array_merge(array_diff($arrDateHeadCount, $arrNewJoiners),$arrNewLeavers);
					break;
				case 2:
					$arrEmployeeIds = $arrNewJoiners;
					break;
				case 3:
					$arrEmployeeIds = $arrNewLeavers;
					break;
				case 4:
					$arrEmployeeIds = $arrDateHeadCount;
					break;
			}
			
			$arrEmployeeIds = array_filter($arrEmployeeIds,'strlen');
			if(count($arrEmployeeIds) > 0)
			{
				$strEmployee = implode(',', $arrEmployeeIds);
				
				$sSQL = "select e.employee_code, e.name, e1.name as reporting_head_name, date_format(e.date_of_joining,'%d-%m-%Y') as date_of_joining, d.title as current_designation from pms_employee e INNER JOIN pms_employee e1 ON e.teamleader = e1.id LEFT JOIN pms_designation d ON e.designation = d.id where e.id in ($strEmployee)";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrEmployee[] = $this->fetchRow();
					}
				}
			}
			return $arrEmployee;
		}
		
		function fnGetDepartmentWiseHeadCountDetails($reportingHead, $date, $detailsFor)
		{
			$arrEmployee = array();

			/* Fetch Opeaning head count */
			$arrDateHeadCount = $this->fnGetDepartmentWiseDailyHeadCountOpeaningBalance($reportingHead, $date);

			$arrDateHeadCount1 = $arrDateHeadCount;
			$arrDateHeadCount1[] = $reportingHead;

			/* Fetch leavers by date */
			$arrLeavers = $this->fnGetDepartmentWiseDailyLeaversHeadCount($reportingHead, $date);

			/* Fetch joiners by date */
			$arrJoiners = $this->fnGetDepartmentWiseDailyJoinersHeadCount($reportingHead, $date);
			
			$arrNewJoiners = array_diff($arrJoiners, $arrLeavers);
			$arrNewLeavers = array_diff($arrLeavers, $arrJoiners);
			
			//$cntOpeaningHeadCount = $cntHeadCount - $cntJoinersCount + $cntLeaversCount;
			//$cntClosingHeadCount = $cntOpeaningHeadCount + $cntJoinersCount - $cntLeaversCount;

			$arrEmployeeIds = array();
			switch($detailsFor)
			{
				case 1:
					$arrEmployeeIds = array_merge(array_diff($arrDateHeadCount, $arrNewJoiners),$arrNewLeavers);
					break;
				case 2:
					$arrEmployeeIds = $arrNewJoiners;
					break;
				case 3:
					$arrEmployeeIds = $arrNewLeavers;
					break;
				case 4:
					$arrEmployeeIds = $arrDateHeadCount;
					break;
			}

			$arrEmployeeIds = array_filter($arrEmployeeIds,'strlen');
			if(count($arrEmployeeIds) > 0)
			{
				$strEmployee = implode(',', $arrEmployeeIds);

				$sSQL = "select e.employee_code, e.name, e1.name as reporting_head_name, date_format(e.date_of_joining,'%d-%m-%Y') as date_of_joining, d.title as current_designation from pms_employee e LEFT JOIN pms_employee e1 ON e.teamleader = e1.id LEFT JOIN pms_designation d ON e.designation = d.id where e.id in ($strEmployee)";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrEmployee[] = $this->fetchRow();
					}
				}
			}
			return $arrEmployee;
		}


		function fnGetEmployeeByRoles($arrRoles)
		{
			$arrEmployee = array();
			$arrRoles[] = 0;
			$strRoles = implode(",",$arrRoles);
			
			$sSQL = "select id, name from pms_employee where status = '0' and role in (".mysql_real_escape_string($strRoles).") and role != '0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchRow();
				}
			}
			
			return $arrEmployee;
		}
		
		function fnGetHighestReportingHead()
		{
			$arrHeads = array();

			$sSQL = "select distinct e.teamleader as headid from pms_employee e INNER JOIN pms_employee e1 ON e.teamleader = e1.id where e1.teamleader='0' and e1.status='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrHeads[] = $this->f("headid");
				}
			}
			
			return $arrHeads;
		}
		function fnGetLeaversForAttrition($reporting_head, $leaver_type, $month, $year)
		{
			$arrEmployee = array();

			include_once("class.attendance.php");
			$objAttendance = new attendance();

			$getAllReporintHeads = $objAttendance->fnGetEmployees();

			$sSQL = "select e.id, e.employee_code, e.name,date_format(e.date_of_joining, '%d-%m-%Y') as date_of_joining,e.teamleader, date_format(e.relieving_date_by_manager, '%Y-%m') as relieving_date_by_manager,date_format(e.relieving_date_by_manager, '%d-%m-%Y') as rel_date_by_manager,e.reason_of_leaving,e.terminated_absconding_resigned,date_format(e.date_of_joining,'%Y-%m-%d') as doj,date_format(e.relieving_date_by_manager,'%Y-%m-%d') as rel_date,TIMESTAMPDIFF(MONTH , e.date_of_joining, e.relieving_date_by_manager ) as months_worked from pms_employee e where date_format(e.relieving_date_by_manager, '%Y-%m') = '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."' and e.not_include_head_count='0' and e.terminated_absconding_resigned='".mysql_real_escape_string($leaver_type)."'";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchRow();
				}
			}
			$arrNew = array();
			$newReportingHeads = array();

			foreach($arrEmployee as $emp)
			{
				$CheckEmployeeReportingHead = $this->fnCheckEmployeeReportingHeadHierarchy($emp['id'],$emp['relieving_date_by_manager']);

				$remove = array(0);
				$final_reporting_heads_hierarchy = array_diff($CheckEmployeeReportingHead, $remove);

				if(isset($reporting_head) && $reporting_head == '0')
				{
					$arrNew[] = $emp;
				}
				else if(isset($reporting_head) && count($reporting_head) > '0')
				{
					$result = array_intersect($reporting_head, $final_reporting_heads_hierarchy);

					if(count($result) > 0)
					{
						$arrNew[] = $emp;
					}
				}
			}

			return $arrNew;
		}

		function fnGetJoinersForMonth($reporting_head, $month, $year)
		{
			$arrEmployee = array();

			include_once("class.attendance.php");
			$objAttendance = new attendance();

			$getAllReporintHeads = $objAttendance->fnGetEmployees();

			$sSQL = "select e.id, e.employee_code, e.name,date_format(e.date_of_joining, '%d-%m-%Y') as date_of_joining,e.teamleader, date_format(e.date_of_joining,'%Y-%m-%d') as doj, date_format(e.date_of_joining,'%Y-%m') as doj_ym from pms_employee e where date_format(e.date_of_joining, '%Y-%m') = '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."' and e.not_include_head_count='0'";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchRow();
				}
			}
			$arrNew = array();
			$newReportingHeads = array();

			foreach($arrEmployee as $emp)
			{
				$CheckEmployeeReportingHead = $this->fnCheckEmployeeReportingHeadHierarchy($emp['id'],$emp['doj_ym']);

				$remove = array(0);
				$final_reporting_heads_hierarchy = array_diff($CheckEmployeeReportingHead, $remove);

				if(isset($reporting_head) && $reporting_head == '0')
				{
					$arrNew[] = $emp;
				}
				else if(isset($reporting_head) && count($reporting_head) > '0')
				{
					$result = array_intersect($reporting_head, $final_reporting_heads_hierarchy);

					if(count($result) > 0)
					{
						$arrNew[] = $emp;
					}
				}
			}

			return $arrNew;
		}

		function fnGetJoinersYTD($reporting_head, $month, $year)
		{
			$arrEmployee = array();

			include_once("class.attendance.php");
			$objAttendance = new attendance();

			$getAllReporintHeads = $objAttendance->fnGetEmployees();

			$sSQL = "select e.id, e.employee_code, e.name,date_format(e.date_of_joining, '%d-%m-%Y') as date_of_joining,e.teamleader, date_format(e.date_of_joining,'%Y-%m-%d') as doj, date_format(e.date_of_joining,'%Y-%m') as doj_ym from pms_employee e where date_format(e.date_of_joining, '%Y-%m') between '".mysql_real_escape_string($year)."-01' and '".mysql_real_escape_string($year)."-".mysql_real_escape_string($month)."' and e.not_include_head_count='0'";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchRow();
				}
			}
			$arrNew = array();
			$newReportingHeads = array();

			foreach($arrEmployee as $emp)
			{
				$CheckEmployeeReportingHead = $this->fnCheckEmployeeReportingHeadHierarchy($emp['id'],$emp['doj_ym']);

				$remove = array(0);
				$final_reporting_heads_hierarchy = array_diff($CheckEmployeeReportingHead, $remove);

				if(isset($reporting_head) && $reporting_head == '0')
				{
					$arrNew[] = $emp;
				}
				else if(isset($reporting_head) && count($reporting_head) > '0')
				{
					$result = array_intersect($reporting_head, $final_reporting_heads_hierarchy);

					if(count($result) > 0)
					{
						$arrNew[] = $emp;
					}
				}
			}

			return $arrNew;
		}
		
		function fnGetNotIncludedInHeadCountEmployees()
		{
			/* Fetches a list of all the employees that are not included in headcount calculation */
			$arrEmployee = array();

			$sSQL = "select e.id, e.name, e.employee_code, date_format(e.date_of_joining,'%d-%m-%Y') as date_of_joining, date_format(e.relieving_date_by_manager,'%d-%m-%Y') as relieving_date_by_manager, e1.name as reporting_head from pms_employee e LEFT JOIN pms_employee e1 ON e.teamleader = e1.id where e.not_include_head_count='1'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchRow();
				}
			}
			
			return $arrEmployee;
		}
	}
?>

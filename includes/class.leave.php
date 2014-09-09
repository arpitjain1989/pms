<?php
	include_once('db_mysql.php');
	class leave extends DB_Sql
	{
		function __construct()
		{
		}
		function fnInsertLeaveType($arrLeaveType)
		{
			$arrNewRecords = array("title"=>$arrLeaveType['title'],"description"=>$arrLeaveType['description'],"color"=>$arrLeaveType['color']);
			$this->insertArray('pms_leave_type',$arrNewRecords);
			return true;
		}
		function fnGetAllLeaveTypes()
		{
			$arrLeaveTypeValues = array();
			$query = "SELECT * FROM `pms_leave_type`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrLeaveTypeValues[] = $this->fetchrow();
				}
			}
			return $arrLeaveTypeValues;
		}

		function fnGetLeaveTypeId($id)
		{
			$arrLeaveTypeValues = array();
			$query = "SELECT * FROM `pms_leave_type` WHERE `id` = '".mysql_real_escape_string($id)."' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrLeaveTypeValues = $this->fetchrow();
				}
			}
			return $arrLeaveTypeValues;
		}

		function fnGetLeaveTypeIdByTitle($title)
		{
			$LeaveTypeId = 0;
			$sSQL = "SELECT id FROM `pms_leave_type` WHERE `title` = '".mysql_real_escape_string($title)."'";
			$this->query($sSQL);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$LeaveTypeId = $this->f("id");
				}
			}
			return $LeaveTypeId;
		}

		function fnGetLeaveColorArray()
		{
			$arrLeave = array();

			$sSQL = "select * from pms_leave_type";
			$this->query($sSQL);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrLeave[$this->f("title")] = $this->f("color");
				}
			}

			return $arrLeave;
		}

		function fnUpdateLeaveType($arrPost)
		{
			$this->updateArray('pms_leave_type',$arrPost);
		return true;
		}

		function fnDeleteLeaveType($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_leave_type` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}

		/* Function to insert leave form */
		function fnInsertLeaveForm($emp_id,$arrLeaveForm)
		{
			/* Include files */
			include_once("class.employee.php");
			include_once("class.designation.php");

			/* Create objects */
			$objEmployee = new employee();
			$objDesignation = new designations();

			/* Get designation of the employee for which the leave is to be added */
			$arrLeaveUser = $objEmployee->fnGetEmployeeById($emp_id);

			if(count($arrLeaveUser) > 0)
			{
				/* Fetch user designation */
				$DesignationId = $arrLeaveUser["designation"];

				/* Fetch details for the user designation */
				$arrDesignationInfo = $objDesignation->fnGetDesignationById($DesignationId);

				if(count($arrDesignationInfo) > 0)
				{
					/* Fetch reporting head hierarchy */
					$arrHeads = $objEmployee->fnGetReportHeadHierarchy($emp_id);

					if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && isset($arrDesignationInfo["second_reporting_head"]) && trim($arrDesignationInfo["second_reporting_head"]) != "" && trim($arrDesignationInfo["second_reporting_head"]) != "0")
					{
						if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
						{
							$fstReportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];
							$arrLeaveForm['teamleader_id'] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
						}

						if(isset($arrHeads[$arrDesignationInfo["second_reporting_head"]]))
						{
							$secReportingHead = $arrHeads[$arrDesignationInfo["second_reporting_head"]];
							$arrLeaveForm['manager_id'] = $arrHeads[$arrDesignationInfo["second_reporting_head"]]["id"];
						}
					}
					else if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && trim($arrDesignationInfo["second_reporting_head"]) == "0")
					{
						if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
						{
							$fstReportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];

							$arrLeaveForm['manager_id'] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
							$arrLeaveForm['teamleader_id'] = 0;
						}
					}
				}
			}

			/*if($_SESSION['designation'] == "5" || $_SESSION['designation'] == "9" || $_SESSION['designation'] == "10" || $_SESSION['designation'] == "11" || $_SESSION['designation'] == "12" || $_SESSION['designation'] == "14" || $_SESSION['designation'] == "15" || $_SESSION['designation'] == "16" || $_SESSION['designation'] == "20" || $_SESSION['designation'] == "21" || $_SESSION['designation'] == "22" || $_SESSION['designation'] == "23" || $_SESSION['designation'] == "24" || $_SESSION['designation'] == "25" || $_SESSION['designation'] == "26" || $_SESSION['designation'] == "27" || $_SESSION['designation'] == "28" || $_SESSION['designation'] == '30' || $_SESSION['designation'] == '31' || $_SESSION['designation'] == '32' || $_SESSION['designation'] == '33' || $_SESSION['designation'] == '34' || $_SESSION['designation'] == '35' || $_SESSION['designation'] == '36' || $_SESSION['designation'] == '37' || $_SESSION['designation'] == '38' ||  $_SESSION['designation'] == '39' || $_SESSION['designation'] == '40' || $_SESSION['designation'] == '41' || $_SESSION['designation'] == '42' || $_SESSION['designation'] == '43' || $_SESSION['designation'] == '46')
			{
				$TeamleaderInfo = $objEmployee->fnGetEmployeeById($_SESSION["teamleader"]);
				$manager_id = $TeamleaderInfo['teamleader_id'];
				$arrLeaveForm['teamleader_id'] = $_SESSION['teamleader'];
				$arrLeaveForm['manager_id'] = $manager_id;
			}
			else if($_SESSION['designation'] == 7 || $_SESSION['designation'] == 13)
			{
			$TeamleaderInfo = $objEmployee->fnGetEmployeeById($_SESSION["teamleader"]);
				$arrLeaveForm['teamleader_id'] = $_SESSION['teamleader'];
				$arrLeaveForm['manager_id'] = 0;
			}
			else if($_SESSION['designation'] == 6 || $_SESSION['designation'] == 18 || $_SESSION['designation'] == 19 || $_SESSION['designation'] == 44)
			{
				$arrLeaveForm['teamleader_id'] = 0;
				$arrLeaveForm['manager_id'] = 1;
			}*/

			$maxDateToApply = date('Y-m-d', strtotime("+100 day"));

			if($arrLeaveForm['start_date'] > $maxDateToApply)
			{
				return -1;
			}

			$shiftCheck = $this->fnCheckShiftMoment($arrLeaveForm['start_date'],$arrLeaveForm['end_date']);
			//echo '<pre>'; print_r($arrLeaveForm);
			$checkDeligateId = $this->fnCheckDeligate($arrLeaveForm['teamleader_id']);
			$checkDeligateManagerId = $this->fnCheckDeligate($arrLeaveForm['manager_id']);

			/*if($arrLeaveForm['manager_id'] == '0')
			{
				$checkDeligateManagerId = $this->fnCheckDeligateManager($arrLeaveForm['teamleader_id']);
			}
			else
			{
				$checkDeligateManagerId = $this->fnCheckDeligateManager($arrLeaveForm['manager_id']);
			}*/
			//echo 'hello'.$checkDeligateManagerId;
			//die;
			//echo 'checkDeligateId--'.$checkDeligateId.'<br />';
			//echo 'checkDeligateManagerId--'.$checkDeligateManagerId.'<br />';
			//die;
			if(isset($checkDeligateId) && $checkDeligateId != '')
			{
				$delegateTeamleaderId = $checkDeligateId;
			}
			else
			{
				$delegateTeamleaderId = 0;
			}

			if(isset($checkDeligateManagerId) && $checkDeligateManagerId != '')
			{
				$delegateManagerId = $checkDeligateManagerId;
			}
			else
			{
				$delegateManagerId = 0;
			}



			//$alreadyLeave = $this->fnCheckAlreadyLeave($_SESSION['id'],$arrLeaveForm['start_date'],$arrLeaveForm['end_date']);
			if($shiftCheck != '')
			{
				return 0;
			}
			else
			{
				$count = '';
				//$query = "SELECT count( id ) AS count,status,status_manager,deligateManagerId,manager_delegate_status FROM pms_leave_form WHERE ('".$arrLeaveForm['start_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d') or '".$arrLeaveForm['end_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d')) and employee_id = '".$_SESSION['id']."' and (status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1)))";

				/*$query ="SELECT count( id ) AS count,status,status_manager,deligateManagerId,manager_delegate_status FROM pms_leave_form WHERE ('".$arrLeaveForm['start_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d') or '".$arrLeaveForm['end_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d')) and employee_id = '".$_SESSION['id']."' and (((status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1))) and isactive='0') or (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1')))";*/
				/*check if leave already added */
				$query ="SELECT count( id ) AS count,status,status_manager,deligateManagerId,manager_delegate_status FROM pms_leave_form WHERE ('".$arrLeaveForm['start_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d') or '".$arrLeaveForm['end_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d')) and employee_id = '".$_SESSION['id']."' and (((status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1))) and isactive='0') or (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1'))) and (status != 2 and delegate_status!=2 AND status_manager !=2 AND manager_delegate_status !=2)";

				$this->query($query);

				if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$count = $this->f("count");
						$status = $this->f("sta");
						$status_manager = $this->f("status_manager");
					}
				}
				if($count > 0)
				{
					fnRedirectUrl("leave_form.php?info=exist");
				}
				else
				{
					$query = "SELECT count( id ) AS count,status,status_manager FROM pms_half_leave_form WHERE DATE_FORMAT(start_date,'%Y-%m-%d') between '".$arrLeaveForm['start_date']."' and '".$arrLeaveForm['end_date']."' and employee_id = '".$_SESSION['id']."' and (((status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1))) and isactive='0') or (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1'))) and (status_manager='0' and (status_manager='0' and manager_delegate_status='0') and (status != 2 and (status =0 and  delegate_status!=2)))";

					//$query = "SELECT count( id ) AS count,status,status_manager FROM pms_half_leave_form WHERE '".$arrLeaveForm['start_date']."' = DATE_FORMAT(start_date,'%Y-%m-%d') and employee_id = '".$_SESSION['id']."' and status_manager IN(0,1)";
					$this->query($query);

					if($this->num_rows() > 0)
					{
						if($this->next_record())
						{
							$count = $this->f("count");
							$status = $this->f("sta");
							$status_manager = $this->f("status_manager");
						}
					}
					if($count > 0)
					{
						fnRedirectUrl("leave_form.php?info=exist");
					}
					else
					{
						/* Check if approved LWP added by admin */
						$sSQL = "select * from pms_approved_lwp where date_format(lwp_date, '%Y-%m-%d') between '".mysql_real_escape_string($arrLeaveForm['start_date'])."' and '".mysql_real_escape_string($arrLeaveForm['end_date'])."' and user_id='".$_SESSION['id']."' and approval_status in (0,1)";
						$this->query($sSQL);
						if($this->num_rows() > 0)
						{
							return -2;
						}
						else
						{
							$arrLeaveForm['date'] = date('Y-m-d H:i:s');
							$arrLeaveForm['employee_id'] = $emp_id;
							//$arrLeaveForm['ph']=$arrLeaveForm['chkPh'];
							//echo '<pre>';
							//print_r($arrLeaveForm);die;

							/* Add code for TL & Manager */
							$arrLeaveForm["tlapprovalcode"] = leaveform_uid();
							$arrLeaveForm["managerapprovalcode"] = leaveform_uid();
							$arrLeaveForm["deligateTeamLeaderId"] = $delegateTeamleaderId;
							if($arrLeaveForm["deligateTeamLeaderId"] != 0)
							{
								$arrLeaveForm["delegatedtlapprovalcode"] = leaveform_uid();
							}

							$arrLeaveForm["deligateManagerId"] = $delegateManagerId;
							if($arrLeaveForm["deligateManagerId"] != 0)
							{
								$arrLeaveForm["delegatedmanagerapprovalcode"] = leaveform_uid();
							}

							$lastInsertId = $this->insertArray('pms_leave_form',$arrLeaveForm);
							return $lastInsertId;
						}
					}
				}
			}
		}

		function fnCheckDeligate($id)
		{
			$date = date('Y-m-d');
			$delegate = "";
			//echo $date; die;
			$query = "select delegate from pms_leave_form where employee_id = '$id' and '$date' between DATE_FORMAT(`start_date`,'%Y-%m-%d') and DATE_FORMAT(`end_date`,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1')) ORDER BY `id` LIMIT 0,1";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$delegate = $this->f("delegate");
				}
			}
			return $delegate;
		}

		function fnCheckDeligateManager($id)
		{
			$date = date('Y-m-d');
			$manager_delegate = "";
			//echo $date; die;
			$query = "select `manager_delegate` from pms_leave_form where employee_id = '$id' and '$date' between DATE_FORMAT(`start_date`,'%Y-%m-%d') and DATE_FORMAT(`end_date`,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1')) ORDER BY `id` LIMIT 0,1";
			$this->query($query);

			if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$manager_delegate = $this->f("manager_delegate");
					}
				}
				return $manager_delegate;
		}

		function fnInsertHalfLeaveForm($des_id,$arrLeaveForm)
		{
			/* Include files */
			include_once("class.employee.php");
			include_once("class.designation.php");

			/* Create objects */
			$objEmployee = new employee();
			$objDesignation = new designations();

			/* Get designation of the employee for which the leave is to be added */
			$arrLeaveUser = $objEmployee->fnGetEmployeeById($arrLeaveForm['userid']);

			if(count($arrLeaveUser) > 0)
			{
				/* Fetch user designation */
				$DesignationId = $arrLeaveUser["designation"];

				/* Fetch details for the user designation */
				$arrDesignationInfo = $objDesignation->fnGetDesignationById($DesignationId);

				if(count($arrDesignationInfo) > 0)
				{
					/* Fetch reporting head hierarchy */
					$arrHeads = $objEmployee->fnGetReportHeadHierarchy($arrLeaveForm['userid']);

					if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && isset($arrDesignationInfo["second_reporting_head"]) && trim($arrDesignationInfo["second_reporting_head"]) != "" && trim($arrDesignationInfo["second_reporting_head"]) != "0")
					{
						if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
						{
							$fstReportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];
							$arrLeaveForm['teamleader_id'] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
						}

						if(isset($arrHeads[$arrDesignationInfo["second_reporting_head"]]))
						{
							$secReportingHead = $arrHeads[$arrDesignationInfo["second_reporting_head"]];
							$arrLeaveForm['manager_id'] = $arrHeads[$arrDesignationInfo["second_reporting_head"]]["id"];
						}
					}
					else if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && trim($arrDesignationInfo["second_reporting_head"]) == "0")
					{
						if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
						{
							$fstReportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];

							$arrLeaveForm['manager_id'] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
							$arrLeaveForm['teamleader_id'] = 0;
						}
					}
				}
			}

			/*if($_SESSION['designation'] == "5" || $_SESSION['designation'] == "9" || $_SESSION['designation'] == "10" || $_SESSION['designation'] == "11" || $_SESSION['designation'] == "12" || $_SESSION['designation'] == "14" || $_SESSION['designation'] == "15" || $_SESSION['designation'] == "16" || $_SESSION['designation'] == "20" || $_SESSION['designation'] == "21" || $_SESSION['designation'] == "22" || $_SESSION['designation'] == "23" || $_SESSION['designation'] == "24" || $_SESSION['designation'] == "25" || $_SESSION['designation'] == "26" || $_SESSION['designation'] == "27" || $_SESSION['designation'] == "28" || $_SESSION['designation'] == '30' || $_SESSION['designation'] == '31' || $_SESSION['designation'] == '32' || $_SESSION['designation'] == '33' || $_SESSION['designation'] == '34' || $_SESSION['designation'] == '35' || $_SESSION['designation'] == '36' || $_SESSION['designation'] == '37' || $_SESSION['designation'] == '38' ||  $_SESSION['designation'] == '39' || $_SESSION['designation'] == '40' || $_SESSION['designation'] == '41' || $_SESSION['designation'] == '42' || $_SESSION['designation'] == '43' || $_SESSION['designation'] == '46')
			{
				$TeamleaderInfo = $objEmployee->fnGetEmployeeById($_SESSION["teamleader"]);
				$manager_id = $TeamleaderInfo['teamleader_id'];
				$arrLeaveForm['teamleader_id'] = $_SESSION['teamleader'];
				$arrLeaveForm['manager_id'] = $manager_id;
				//echo '<pre>'; print_r($TeamleaderInfo);
			}
			else if($_SESSION['designation'] == 7 || $_SESSION['designation'] == 13)
			{
			$TeamleaderInfo = $objEmployee->fnGetEmployeeById($_SESSION["teamleader"]);
				$arrLeaveForm['teamleader_id'] = $_SESSION['teamleader'];
				$arrLeaveForm['manager_id'] = 0;
			}
			else if($_SESSION['designation'] == 6 || $_SESSION['designation'] == 18 || $_SESSION['designation'] == 19 || $_SESSION['designation'] == 44)
			{
				$arrLeaveForm['teamleader_id'] = 0;
				$arrLeaveForm['manager_id'] = 0;
			}*/

			$maxDateToApply = date('Y-m-d', strtotime("+100 day"));

			if($arrLeaveForm['start_date'] > $maxDateToApply)
			{
				return -1;
			}

			$shiftCheck = $this->fnCheckShiftMoment($arrLeaveForm['start_date'],$arrLeaveForm['start_date']);
			//$alreadyLeave = $this->fnCheckAlreadyLeave($_SESSION['id'],$arrLeaveForm['start_date'],$arrLeaveForm['end_date']);

			/* Get delegated teamleader and manager*/
			$checkDeligateId = $this->fnCheckDeligate($arrLeaveForm['teamleader_id']);
			$checkDeligateManagerId = $this->fnCheckDeligate($arrLeaveForm['manager_id']);

			/*if($arrLeaveForm['manager_id'] == '0')
			{
				$checkDeligateManagerId = $this->fnCheckDeligateManager($arrLeaveForm['teamleader_id']);
			}
			else
			{
				$checkDeligateManagerId = $this->fnCheckDeligateManager($arrLeaveForm['manager_id']);
			}*/

			if(isset($checkDeligateId) && $checkDeligateId != '')
			{
				$delegateTeamleaderId = $checkDeligateId;
			}
			else
			{
				$delegateTeamleaderId = 0;
			}

			if(isset($checkDeligateManagerId) && $checkDeligateManagerId != '')
			{
				$delegateManagerId = $checkDeligateManagerId;
			}
			else
			{
				$delegateManagerId = 0;
			}

			$arrLeaveForm["deligateTeamLeaderId"] = $delegateTeamleaderId;
			$arrLeaveForm["deligateManagerId"] = $delegateManagerId;

			if($shiftCheck != '')
			{
				return 0;
			}
			else
			{
				$count = '';
				 //$query = "SELECT count( id ) AS count,status,status_manager FROM pms_leave_form WHERE ('".$arrLeaveForm['start_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d') or '".$arrLeaveForm['end_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d')) and employee_id = '".$_SESSION['id']."' and status_manager IN(0,1)";
				//$query = "SELECT count( id ) AS count,status,status_manager FROM pms_half_leave_form WHERE '".$arrLeaveForm['start_date']."' = DATE_FORMAT(start_date,'%Y-%m-%d') and employee_id = '".$_SESSION['id']."' and (status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1)))";
				$query = "SELECT count( id ) AS count,status,status_manager FROM pms_half_leave_form WHERE '".$arrLeaveForm['start_date']."' = DATE_FORMAT(start_date,'%Y-%m-%d') and employee_id = '".$_SESSION['id']."' and (((status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1))) and isactive='0') or (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1'))) and (status_manager='0' and (status_manager='0' and manager_delegate_status='0') and (status != 2 and (status =0 and  delegate_status!=2)))";

				//$query = "SELECT count( id ) AS count,status,status_manager FROM pms_half_leave_form WHERE '".$arrLeaveForm['start_date']."' = DATE_FORMAT(start_date,'%Y-%m-%d') and employee_id = '".$_SESSION['id']."' and status_manager IN(0,1)";
				$this->query($query);

				if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$count = $this->f("count");
						$status = $this->f("sta");
						$status_manager = $this->f("status_manager");
					}
				}
				if($count > 0)
				{
					fnRedirectUrl("halfleave_form.php?info=exist");
				}
				else
				{
					$count1 = '';
					$query1 ="SELECT count( id ) AS count,status,status_manager,deligateManagerId,manager_delegate_status FROM pms_leave_form WHERE ('".$arrLeaveForm['start_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d')) and employee_id = '".$_SESSION['id']."' and (((status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1))) and isactive='0') or (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1'))) and (status != 2 and delegate_status!=2)";
					
					//echo $query1 = "SELECT count( id ) AS count,status,status_manager FROM pms_leave_form WHERE '".$arrLeaveForm['start_date']."' = DATE_FORMAT(start_date,'%Y-%m-%d') and employee_id = '".$_SESSION['id']."' and (((status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1))) and isactive='0') or (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1'))) and (status_manager='0' and (status_manager='0' and manager_delegate_status='0') and (status != 2 and (status =0 and  delegate_status!=2)))"; die;

					//$query = "SELECT count( id ) AS count,status,status_manager FROM pms_half_leave_form WHERE '".$arrLeaveForm['start_date']."' = DATE_FORMAT(start_date,'%Y-%m-%d') and employee_id = '".$_SESSION['id']."' and status_manager IN(0,1)";
					$this->query($query1);

					if($this->num_rows() > 0)
					{
						if($this->next_record())
						{
							$count1 = $this->f("count");
							$status = $this->f("sta");
							$status_manager = $this->f("status_manager");
						}
					}
					if($count1 > 0)
					{
						fnRedirectUrl("halfleave_form.php?info=exist");
					}
					else
					{
						/* Check if approved LWP added by admin */
						$sSQL = "select * from pms_approved_lwp where date_format(lwp_date, '%Y-%m-%d') = '".mysql_real_escape_string($arrLeaveForm['start_date'])."' and user_id='".$_SESSION['id']."' and approval_status in (0,1)";
						$this->query($sSQL);
						if($this->num_rows() > 0)
						{
							return -2;
						}
						else
						{
							$arrLeaveForm['date'] = date('Y-m-d H:i:s');
							$arrLeaveForm['employee_id'] = $des_id;

							/* Add code for TL & Manager */
							$arrLeaveForm["tlapprovalcode"] = halfdayleaveform_uid();
							if($arrLeaveForm["deligateTeamLeaderId"] != 0)
							{
								$arrLeaveForm["delegatedtlapprovalcode"] = halfdayleaveform_uid();
							}

							$arrLeaveForm["managerapprovalcode"] = halfdayleaveform_uid();
							if($arrLeaveForm["deligateManagerId"] != 0)
							{
								$arrLeaveForm["delegatedmanagerapprovalcode"] = halfdayleaveform_uid();
							}

							$lastInsertId = $this->insertArray('pms_half_leave_form',$arrLeaveForm);
							return $lastInsertId;
						}
					}
				}
			}
		}

		function fnCheckShiftMoment($start,$end)
		{
			$id ='';
			$query = "select id from `pms_shift_movement` where `movement_date` between '$start' and '$end' and isCancel ='0' and `userid`='".$_SESSION['id']."' and (((approvedby_manager IN(0,1) or (approvedby_manager ='0' and delegatedmanager_id!='0' and delegatedmanager_status IN(0,1))) and isactive='0') or (approvedby_manager ='1' or (approvedby_manager ='0' and delegatedmanager_id!='0' and delegatedmanager_status ='1'))) and (approvedby_tl != 2 and delegatedtl_status!=2)";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$id = $this->f('id');
				}
			}
			return $id;
		}

		function fnCheckUserShiftMovement($userid,$start,$end)
		{
			$id ='';
			$query = "select id from `pms_shift_movement` where `movement_date` between '$start' and '$end' and isCancel ='0' and `userid`='".$userid."' and (((approvedby_manager IN(0,1) or (approvedby_manager ='0' and delegatedmanager_id!='0' and delegatedmanager_status IN(0,1))) and isactive='0') or (approvedby_manager ='1' or (approvedby_manager ='0' and delegatedmanager_id!='0' and delegatedmanager_status ='1'))) and (approvedby_tl != 2 and delegatedtl_status!=2)";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$id = $this->f('id');
				}
			}
			return $id;
		}

		function fnCheckShiftMomentById($start,$end,$uid)
		{
			$id ='';
			$query = "select id from `pms_shift_movement` where `movement_date` between '$start' and '$end' and isCancel ='0' and `userid`='".$uid."' and (((approvedby_manager IN(0,1) or (approvedby_manager ='0' and delegatedmanager_id!='0' and delegatedmanager_status IN(0,1))) and isactive='0') or (approvedby_manager ='1' or (approvedby_manager ='0' and delegatedmanager_id!='0' and delegatedmanager_status ='1'))) and (approvedby_tl != 2 and delegatedtl_status!=2)";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$id = $this->f('id');
				}
			}
			return $id;
		}

		function fnGetAllLeaveForm($id)
		{
			$arrLeaveFormValues = array();
			$query = "SELECT *,if(isactive=1,'No','Yes') as isactive_text,date_format(`start_date`,'%d-%m-%Y') as startdate,date_format(`end_date`,'%d-%m-%Y') as enddate FROM `pms_leave_form` WHERE `employee_id` = '$id'";
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
		function fnGetAllHalfLeaveForm($id)
		{
			$arrLeaveFormValues = array();
			$query = "SELECT *,l.id as leave_id FROM `pms_half_leave_form` as l left join pms_employee as p on l.employee_id = p.id WHERE l.`employee_id` = '$id' and p.status = '0'";
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

		function fnGetLeaveFormById($id)
		{
			$arrLeaveFormValues = array();
			$query = "SELECT * FROM `pms_leave_form` WHERE `id` = '".mysql_real_escape_string($id)."' ";
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

		function fnGetHalfLeaveFormById($id)
		{
			$arrLeaveFormValues = array();
			$query = "SELECT *,date_format(`start_date`,'%d-%m-%Y') as startDate  FROM `pms_half_leave_form` WHERE `id` = '".mysql_real_escape_string($id)."' ";
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


		function fnUpdateLeaveForm($arrPost)
		{
			/* THIS FUNCTION IS CURRENTLY NOT USED ANYWHERE */

			$post = $arrPost;
			$LeaveInfo = $this->fnGetLeaveDetailsById($arrPost["id"]);

			$date= date('d-m-Y');
			include_once('class.employee.php');
			$objEmployee = new employee();
			$status = array("0"=>"Pending", "1"=>"Approved", "2"=>"Unapproved");

			$TeamLaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo['teamleader_id']);
			$ManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo['manager_id']);
			$DeligateTeamLeaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo['deligateTeamLeaderId']);
			$DeligateManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo['deligateManagerId']);
			//echo '<pre>';  print_r($_SESSION);
			$Subject = 'Emergency Leave';
			/* When manager login */
			if($_SESSION['designation'] == '6' || $_SESSION['designation'] == '18' || $_SESSION['designation'] == '19' || $_SESSION['designation'] == '44')
			{
				//echo 'hello'; die;
				/* First check that login user is actual manager or delegate manager */
				if($_SESSION['id'] == $LeaveInfo['deligateManagerId'])
				{
					/* send mail to manager */
						$content = "Dear ".$ManagerInfo['name'].", <br /><br />";
						$content .= $DeligateManagerInfo["name"]." has ".$status[$post["status_manager_delegate"]]." an Emergency leave request for ".$LeaveInfo["emplo_name"]." for date ".$LeaveInfo['startdate'].".";
						$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
						//echo 'hello'.$content.'<br>';
						sendmail($ManagerInfo['email'],$Subject,$content);

					/* send mail to deligated teamleader*/
					if($LeaveInfo['deligateTeamLeaderId'] != '' && $LeaveInfo['deligateManagerId'] != '0')
					{
						$content = "Dear ".$DeligateTeamLeaderInfo['name'].", <br /><br />";
						$content .= $DeligateManagerInfo["name"]." has ".$status[$post["status_manager_delegate"]]." an Emergency leave request for ".$LeaveInfo["emplo_name"]." for date ".$LeaveInfo['startdate'].".";
						$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
						//echo 'hello'.$content.'<br>';
						sendmail($DeligateTeamLeaderInfo['email'],$Subject,$content);
					}

					/* send mail to original teamleader */
					$content = "Dear ".$TeamLaderInfo['name'].", <br /><br />";
					$content .= $DeligateManagerInfo["name"]." has ".$status[$post["status_manager_delegate"]]." an Emergency leave request for ".$LeaveInfo["emplo_name"]." for date ".$LeaveInfo['startdate'].".";
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					//echo 'hello'.$content.'<br>';
					sendmail($TeamLaderInfo['email'],$Subject,$content);

					/* send mail to applyer */
					$content = "Dear ".$LeaveInfo['name'].", <br /><br />";
					$content .= $DeligateManagerInfo["name"]." has ".$status[$post["status_manager_delegate"]]." an Emergency leave request for you for date ".$LeaveInfo['startdate'].".";

					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					//echo 'hello1'.$content.'<br>';
					sendmail($LeaveInfo['email'],$Subject,$content);

				}
				else
				{
					/* send mail to deligate manager if exist*/
					if($LeaveInfo['deligateManagerId'] != '' && $LeaveInfo['deligateManagerId'] != '0')
					{
						$content = "Dear ".$DeligateManagerInfo['name'].", <br /><br />";
						$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." an Emergency leave request for ".$LeaveInfo["emplo_name"]." for date ".$LeaveInfo['startdate'].".";
						$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
						//echo 'hello'.$content.'<br>';
						sendmail($DeligateManagerInfo['email'],$Subject,$content);
					}
					/* send mail to deligated teamleader*/
					if($LeaveInfo['deligateTeamLeaderId'] != '' && $LeaveInfo['deligateManagerId'] != '0')
					{
						$content = "Dear ".$DeligateTeamLeaderInfo['name'].", <br /><br />";
						$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." an Emergency leave request for ".$LeaveInfo["emplo_name"]." for date ".$LeaveInfo['startdate'].".";
						$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
						//echo 'hello1'.$content.'<br>';
						sendmail($DeligateTeamLeaderInfo['email'],$Subject,$content);
					}

					/* send mail to original teamleader */
					$content = "Dear ".$TeamLaderInfo['name'].", <br /><br />";
					$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." an Emergency leave request for ".$LeaveInfo["emplo_name"]." for date ".$LeaveInfo['startdate'].".";
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					//echo 'hello2'.$content.'<br>';
					sendmail($TeamLaderInfo['email'],$Subject,$content);

					/* send mail to applyer */
					$content = "Dear ".$LeaveInfo['name'].", <br /><br />";
					$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." an Emergency leave request for you for date ".$LeaveInfo['startdate'].".";

					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					//echo 'hello3'.$content.'<br>';
					sendmail($LeaveInfo['email'],$Subject,$content);
				}
			}
			else if($_SESSION['designation'] == '7' || $_SESSION['designation'] == '13')
			{
				//echo 'hello'; die;
				/* First check that login user is actual TeamLeader or delegate TeamLeader */
				/* This is deligate teamleader login */
				if($_SESSION['id'] == $LeaveInfo['deligateTeamLeaderId'])
				{
					/* send mail to manager */
						$content = "Dear ".$ManagerInfo['name'].", <br /><br />";
						$content .= $DeligateTeamLeaderInfo["name"]." has ".$status[$post["delegate_status"]]." an Emergency leave request for ".$LeaveInfo["emplo_name"]." for date ".$LeaveInfo['startdate'].".";
						$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
						//echo 'hello'.$content.'<br>';
						sendmail($ManagerInfo['email'],$Subject,$content);

					/* send mail to deligated manager*/
					if($LeaveInfo['deligateManagerId'] != '' && $LeaveInfo['deligateManagerId'] != '0')
					{
						$content = "Dear ".$DeligateManagerInfo['name'].", <br /><br />";
						$content .= $DeligateTeamLeaderInfo["name"]." has ".$status[$post["delegate_status"]]." an Emergency leave request for ".$LeaveInfo["emplo_name"]." for date ".$LeaveInfo['startdate'].".";
						$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
						//echo 'hello'.$content.'<br>';
						sendmail($DeligateManagerInfo['email'],$Subject,$content);
					}

					/* send mail to original teamleader */
					$content = "Dear ".$TeamLaderInfo['name'].", <br /><br />";
					$content .= $DeligateTeamLeaderInfo["name"]." has ".$status[$post["delegate_status"]]." an Emergency leave request for ".$LeaveInfo["emplo_name"]." for date ".$LeaveInfo['startdate'].".";
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					//echo 'hello'.$content.'<br>';
					sendmail($TeamLaderInfo['email'],$Subject,$content);

					/* send mail to applyer */
					$content = "Dear ".$LeaveInfo['name'].", <br /><br />";
					$content .= $DeligateTeamLeaderInfo["name"]." has ".$status[$post["delegate_status"]]." an Emergency leave request for you for date ".$LeaveInfo['startdate'].".";

					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					//echo 'hello1'.$content.'<br>';
					sendmail($LeaveInfo['email'],$Subject,$content);
				}
				/* This is teamleader login */
				else
				{
					/* send mail to deligate manager if exist*/
					if($LeaveInfo['deligateManagerId'] != '' && $LeaveInfo['deligateManagerId'] != '0')
					{
						$content = "Dear ".$DeligateManagerInfo['name'].", <br /><br />";
						$content .= $TeamLaderInfo["name"]." has ".$status[$post["status"]]." an Emergency leave request for ".$LeaveInfo["emplo_name"]." for date ".$LeaveInfo['startdate'].".";
						$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
						//echo 'hello'.$content.'<br>';
						sendmail($DeligateManagerInfo['email'],$Subject,$content);
					}
					/* send mail to deligated teamleader*/
					if($LeaveInfo['deligateTeamLeaderId'] != '' && $LeaveInfo['deligateManagerId'] != '0')
					{
						$content = "Dear ".$DeligateTeamLeaderInfo['name'].", <br /><br />";
						$content .= $TeamLaderInfo["name"]." has ".$status[$post["status"]]." an Emergency leave request for ".$LeaveInfo["emplo_name"]." for date ".$LeaveInfo['startdate'].".";
						$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
						//echo 'hello'.$content.'<br>';
						sendmail($DeligateTeamLeaderInfo['email'],$Subject,$content);
					}

					/* send mail to original manager */
					$content = "Dear ".$ManagerInfo['name'].", <br /><br />";
					$content .= $TeamLaderInfo["name"]." has ".$status[$post["status"]]." an Emergency leave request for ".$LeaveInfo["emplo_name"]." for date ".$LeaveInfo['startdate'].".";
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					//echo 'hello'.$content.'<br>';
					sendmail($ManagerInfo['email'],$Subject,$content);

					/* send mail to applyer */
					$content = "Dear ".$LeaveInfo['name'].", <br /><br />";
					$content .= $TeamLaderInfo["name"]." has ".$status[$post["status"]]." an Emergency leave request for you for date ".$LeaveInfo['startdate'].".";

					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					//echo 'hello1'.$content.'<br>';
					sendmail($LeaveInfo['email'],$Subject,$content);
				}
			}
			if($leaveInfo["isactive"] == "1")
			{
				return false;
			}
			else
			{
				$this->updateArray('pms_leave_form',$arrPost);
				return true;
			}
		}

		function fnDeleteLeaveForm($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_leave_form` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}

		function fnGetAllLeaveRequest($ids)
		{
			$arrLeaveFormValues = array();
			$current_date = date('Y-m-d');
			//$query = "SELECT employee.*,leaves.*,head.name as headname,leaves.id as leaves_id,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS start_date,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') AS end_date, if(isemergency=1,'Yes','No') as isemergency FROM `pms_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id LEFT JOIN `pms_employee` AS head ON employee.teamleader = head.id WHERE employee_id IN($ids) and `isactive` = '0'";
			//echo $query = "SELECT employee.*,leaves.*,head.name as headname,leaves.id as leaves_id,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS start_date,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') AS end_date, if(isemergency=1,'Yes','No') as isemergency FROM `pms_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id LEFT JOIN `pms_employee` AS head ON employee.teamleader = head.id WHERE employee_id IN($ids) and `isactive` = '0' and (DATE_FORMAT(leaves.`start_date`,'%Y-%m-%d') >= '$current_date' and DATE_FORMAT(leaves.`end_date`,'%Y-%m-%d') >= '$current_date') and ((employee.designation IN(7,13,6) and (leaves.`status_manager` = '0' or (leaves.`deligateManagerId` != '0' && leaves.`status_manager` = '0'))) or (employee.designation NOT IN (7,13,6) and (leaves.`status`= '0' or leaves.`status_manager` = '0' or (leaves.`deligateTeamLeaderId` != '0' and leaves.`delegate_status` = '0') or (leaves.`deligateManagerId` != '0' && leaves.`status_manager` = '0')))) and employee.status = '0'";


			//$query = "SELECT employee.*,leaves.*,head.name as headname,leaves.id as leaves_id,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS start_date,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') AS end_date, if(isemergency=1,'Yes','No') as isemergency FROM `pms_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id LEFT JOIN `pms_employee` AS head ON employee.teamleader = head.id WHERE employee_id IN($ids) and `isactive` = '0' and (DATE_FORMAT(leaves.`start_date`,'%Y-%m-%d') >= '$current_date' and DATE_FORMAT(leaves.`end_date`,'%Y-%m-%d') >= '$current_date') and ((employee.designation IN(7,13,6) and (leaves.`status_manager` = '0' or (leaves.`deligateManagerId` != '0' and leaves.`status_manager` = '0' and leaves.`manager_delegate_status` = '0'))) or (employee.designation NOT IN (7,13,6) and (leaves.`status`= '0' or leaves.`status_manager` = '0' or (leaves.`deligateTeamLeaderId` != '0' and leaves.`delegate_status` = '0' and leaves.`manager_delegate_status` = '0') or (leaves.`deligateManagerId` != '0' && leaves.`status_manager` = '0')))) and (leaves.`status_manager` != '2' or (leaves.`deligateManagerId` != '0' and  leaves.`manager_delegate_status` != '2' and leaves.`status_manager` = '0')) and employee.status = '0'";

			$prevdate = Date('Y-m-d', strtotime('-1 day'));

			$query = "SELECT employee.*,leaves.*,employee.id as eid,DATE_FORMAT(leaves.start_date,'%Y-%m-%d') as leave_start_date,DATE_FORMAT(leaves.end_date,'%Y-%m-%d') as leave_end_date,head.name as headname,leaves.id as leaves_id,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS start_date,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') AS end_date, if(isemergency=1,'Yes','No') as isemergency FROM `pms_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id LEFT JOIN `pms_employee` AS head ON employee.teamleader = head.id WHERE employee_id IN($ids) and `isactive` = '0' and ((DATE_FORMAT(leaves.`start_date`,'%Y-%m-%d') >= '$current_date' and DATE_FORMAT(leaves.`end_date`,'%Y-%m-%d') >= '$current_date') or (DATE_FORMAT(leaves.`start_date`,'%Y-%m-%d') >= '$prevdate' and DATE_FORMAT(leaves.`end_date`,'%Y-%m-%d') >= '$prevdate' and isemergency = '1')) and ((leaves.`status`= '0' or leaves.`status_manager` = '0' or (leaves.`deligateTeamLeaderId` != '0' and leaves.`delegate_status` = '0' and leaves.`manager_delegate_status` = '0') or (leaves.`deligateManagerId` != '0' && leaves.`status_manager` = '0'))) and (leaves.id not in (select id from pms_leave_form where `status_manager` = '2' or (`status_manager` = '0' and `manager_delegate_status` = '2') or (`status_manager` = '0' and `manager_delegate_status` in (0,null) and `status`= '2') or (`status_manager` = '0' and `manager_delegate_status` in (0,null) and `status`= '0' and `delegate_status` = '2'))) and employee.status = '0'";

			$this->query($query);

			include_once('includes/class.shifts.php');
			include_once('includes/class.attendance.php');
			include_once('includes/class.employee.php');

			$objShifts = new shifts();
			$objAttendance = new attendance();
			$objEmployee = new employee();

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					//$arrLeaveFormValues[] = $this->fetchrow();
					/*echo '<br>leave_start_date'.$this->f("leave_start_date");
					echo '<br>leave_end_date'.$this->f("leave_end_date");
					echo '<br>employee_id'.$this->f("eid");*/
					if($this->f("leave_start_date") == $prevdate && $this->f("leave_end_date") == $prevdate)
					{
						//echo 'here';
						$starttime = "00:00";
						$endtime = "00:00";

						/* Get data from attendance */
						$ShiftId = $objAttendance->fnGetAttendanceShiftByUserAndDate($this->f("eid"), $this->f("leave_start_date"));

						if($ShiftId == "" || $ShiftId == "0")
						{
							/* Get the default shift from the employee */
							$ShiftId = $objEmployee->fnGetEmployeeShiftById($this->f("eid"));
						}

						$arrShift = $objShifts->fnGetShiftById($ShiftId);
						/*echo '<pre>'; print_r($arrShift);*/
						if(count($arrShift) > 0)
						{
							$starttime = $arrShift["starttime"];
							$endtime = $arrShift["endtime"];
						}
						/*echo '<br>starttime'.$starttime;
						echo '<br>endtime'.$endtime;*/

						if($endtime <= $starttime)
						{
							$arrLeaveFormValues[] = $this->fetchrow();
						}
					}
					else
					{
						$arrLeaveFormValues[] = $this->fetchrow();
					}
				}
			}
			return $arrLeaveFormValues;
		}

		/* Gives all half leave requests for his/her team */
		function fnGetAllHalfLeaveRequest($ids)
		{
			$arrLeaveFormValues = array();
			$current_date = date('Y-m-d');
			$prevdate = Date('Y-m-d', strtotime('-1 day'));

			$query = "SELECT employee.*,leaves.*,employee.id as eid,head.name as headname,leaves.id as leaves_id,DATE_FORMAT(leaves.start_date,'%Y-%m-%d') as leave_start_date,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS start_date FROM `pms_half_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id LEFT JOIN `pms_employee` AS head ON employee.teamleader = head.id  WHERE employee_id IN($ids) and employee.status = '0' and leaves.isactive = '0' and DATE_FORMAT(leaves.`start_date`,'%Y-%m-%d') >= '$prevdate' and (((leaves.status_manager IN(0,1) or (leaves.status_manager ='0' and leaves.deligateManagerId!='0' and leaves.manager_delegate_status IN(0,1))) and isactive='0') or (leaves.status_manager ='1' or (leaves.status_manager ='0' and leaves.deligateManagerId!='0' and leaves.manager_delegate_status ='1'))) and (leaves.status != 2 and leaves.delegate_status!=2 AND leaves.status_manager !=2 AND leaves.manager_delegate_status !=2)";
				$this->query($query);

				include_once('includes/class.shifts.php');
				include_once('includes/class.attendance.php');
				include_once('includes/class.employee.php');

				$objShifts = new shifts();
				$objAttendance = new attendance();
				$objEmployee = new employee();

				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						//$arrLeaveFormValues[] = $this->fetchrow();

						if($this->f("leave_start_date") == $prevdate )
						{
							//echo 'here';
							$starttime = "00:00";
							$endtime = "00:00";

							/* Get data from attendance */
							$ShiftId = $objAttendance->fnGetAttendanceShiftByUserAndDate($this->f("eid"), $this->f("leave_start_date"));

							if($ShiftId == "" || $ShiftId == "0")
							{
								/* Get the default shift from the employee */
								$ShiftId = $objEmployee->fnGetEmployeeShiftById($this->f("eid"));
							}

							$arrShift = $objShifts->fnGetShiftById($ShiftId);
							/*echo '<pre>'; print_r($arrShift);*/
							if(count($arrShift) > 0)
							{
								$starttime = $arrShift["starttime"];
								$endtime = $arrShift["endtime"];
							}
							/*echo '<br>starttime'.$starttime;
							echo '<br>endtime'.$endtime;*/

							if($endtime <= $starttime)
							{
								$arrLeaveFormValues[] = $this->fetchrow();
							}
						}
						else
						{
							$arrLeaveFormValues[] = $this->fetchrow();
						}


					}
				}
			return $arrLeaveFormValues;
		}

		/* Get Leave Details by leave id */
		function fnGetLeaveDetailsById($id)
		{
			$arrLeaveValues = array();
			$query = "SELECT *, leaves.id as leaveid,leaves.deligateTeamLeaderId as deligateTeamLeaderId,leaves.start_date as starting_date,leaves.status as team_leader_status_id,leaves.status_manager as manager_status_id,leaves.end_date as ending_date,leaves.nodays as actual_nods,leaves.status as team_leader_status,leaves.comment as team_leader_comment,leaves.reason as actual_reason,DATE_FORMAT(leaves.date,'%d-%m-%Y') AS apply_actual_date,leaves.id as leave_id,leaves.status as leave_status,leaves.id AS leaves_id,employee.id as employee_id,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS start_date,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS new_start_date,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') AS end_date,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') AS new_end_date,DATE_FORMAT(leaves.start_date,'%d-%m-%Y')
AS startdate,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') AS enddate,DATE_FORMAT(leaves.date,'%d-%m-%Y') AS date,leaves.address as leave_address,leaves.contact as leave_contact,employee.name as emplo_name,employee.`leave_bal`
as balance, if(isemergency=1,'Yes','No') as isemergency,if(isactive=1,'No','Yes') as isactive_text FROM `pms_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id WHERE leaves.id ='$id'";
				$this->query($query);

				if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$arrLeaveValues = $this->fetchrow();
					}
				}
			return $arrLeaveValues;
		}



		function fnGetHalfLeaveDetailsById($id)
		{
			$arrLeaveValues = array();
			$query = "SELECT *,leaves.start_date as starting_date,leaves.status as team_leader_status_id,leaves.status_manager as manager_status_id,leaves.nodays as actual_nods,leaves.status as team_leader_status,leaves.delegate_status as delegate_teamleader_status,leaves.`manager_delegate_status` as delegate_manager_status,leaves.comment as team_leader_comment,leaves.reason as actual_reason,DATE_FORMAT(leaves.date,'%d-%m-%Y') AS apply_actual_date,leaves.id as leave_id,leaves.status as leave_status,leaves.id AS leaves_id,employee.id as employee_id,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS start_date,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS new_start_date,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS startdate,DATE_FORMAT(leaves.date,'%d-%m-%Y') AS date,leaves.address as leave_address,leaves.contact as leave_contact,employee.name as emplo_name,employee.`leave_bal` as balance FROM `pms_half_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id WHERE leaves.id ='$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrLeaveValues = $this->fetchrow();
				}
			}
			return $arrLeaveValues;
		}



		function fnUpdateLeaveStatus($post)
		{
			$date = date('d-m-Y');
			if(isset($post['isactive']) && $post['isactive'] == 1 )
			{
				header("Location: leave_request.php?info=timepast");
				exit;
			}
			
			if(isset($post['isactive']))
				unset($post['isactive']);
			
			if($_SESSION["usertype"] == "employee")
			{
				include_once('class.employee.php');
				$objEmployee = new employee();

				$status = array("0"=>"Pending", "1"=>"Approved", "2"=>"Unapproved");

				$newArrData = $post;
				$newArrData["id"] = $post["hdnid"];

				$LeaveInfo = $this->fnLeaveInfoById($post["hdnid"]);

				if(isset($LeaveInfo["teamleader_id"]) && $LeaveInfo["teamleader_id"] == $_SESSION["id"])
				{
					$newArrData["approved_date"] = Date("Y-m-d H:i:s");
					
					// Leave approved / rejected by the first reporting head
					/*$arrTeamLeaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["teamleader_id"]);

					if(count($arrTeamLeaderInfo) > 0)
					{
						$main_content .= "Leave/s application for ".$LeaveInfo["name"]." has been ".$status[$post["status"]]." by ".$arrTeamLeaderInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".";
						$main_content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						$Subject = "Leave Request ".$status[$post["status"]];

						// Send mail to user who added the leave 
						$MailTo = $LeaveInfo["email"];
						$content = "Dear ".$LeaveInfo["name"].",<br><br>Your application for Leave/s has been ".$status[$post["status"]]." by ".$arrTeamLeaderInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br><br>Regards,<br>".SITEADMINISTRATOR;
						sendmail($MailTo, $Subject, $content);

						// Send mail to second reporting head if exists 
						if(isset($LeaveInfo["manager_id"]) && trim($LeaveInfo["manager_id"]) != '0' && trim($LeaveInfo["manager_id"]) != '')
						{
							$arrManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["manager_id"]);
							if(count($arrManagerInfo) > 0)
							{
								$MailTo = $arrManagerInfo["email"];
								$content = "Dear ".$arrManagerInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}

						// Send Mail to delegate Team Leader if exists 
						if(isset($LeaveInfo["deligateTeamLeaderId"]) && trim($LeaveInfo["deligateTeamLeaderId"]) != '0' && trim($LeaveInfo["deligateTeamLeaderId"]) != '')
						{
							$arrDelegatedTeamLeaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateTeamLeaderId"]);
							if(count($arrDelegatedTeamLeaderInfo) > 0)
							{
								$MailTo = $arrDelegatedTeamLeaderInfo["email"];
								$content = "Dear ".$arrDelegatedTeamLeaderInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}

						// Send Mail to delegate Manager if exists 
						if(isset($LeaveInfo["deligateManagerId"]) && trim($LeaveInfo["deligateManagerId"]) != '0' && trim($LeaveInfo["deligateManagerId"]) != '')
						{
							$arrDelegatedManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateManagerId"]);
							if(count($arrDelegatedManagerInfo) > 0)
							{
								$MailTo = $arrDelegatedManagerInfo["email"];
								$content = "Dear ".$arrDelegatedManagerInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}
					}*/
				}
				else if(isset($LeaveInfo["manager_id"]) && $LeaveInfo["manager_id"] == $_SESSION["id"])
				{
					$newArrData["approved_date_manager"] = Date("Y-m-d H:i:s");
					
					/* Leave approved / rejected by the second reporting head */
					$arrManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["manager_id"]);

					if(count($arrManagerInfo) > 0)
					{
						/* Leave approved / rejected by second reporting head */
						$main_content = "Leave/s application for ".$LeaveInfo["name"]." has been ".$status[$post["status_manager"]]." by ".$arrManagerInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".";
						$main_content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						$Subject = "Leave Request ".$status[$post["status_manager"]];

						/* Send mail to user who added the leave */
						$MailTo = $LeaveInfo["email"];
						$content = "Dear ".$LeaveInfo["name"].",<br><br>Your application for Leave/s has been ".$status[$post["status_manager"]]." by ".$arrManagerInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br><br>";

						/* If status is approved, check if delegate is assigned */
						if($status[$post["status_manager"]] == "Approved")
						{
							/* Check for delegates */
							if($post["delegate"] != 0)
							{
								$DelegatedUserInfo = $objEmployee->fnGetEmployeeById($post["delegate"]);

								if(count($DelegatedUserInfo) > 0)
								{
									$content .="As ".$status[$post["status_manager"]].", you can avail these leaves from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".Your responsibilities would be delegated to ".$DelegatedUserInfo["name"]." while you are on leave/s, which please note.<br><br>";

									/* Send mail to delegated user */
									$Delegate_MailTo = $DelegatedUserInfo["email"];
									$Delegate_Subject = "Leave Request ".$status[$post["status_manager"]].' for '.$LeaveInfo['name'];
									$Delegate_content = "Dear ".$DelegatedUserInfo["name"].",<br><br>Kindly note that ".$LeaveInfo["name"]." will be on leave from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"]." and his work responsibilities are delegated to you for while he is on leave/s for the above period.<br><br>Regards,<br>".SITEADMINISTRATOR;
									sendmail($Delegate_MailTo, $Delegate_Subject, $Delegate_content);
								}
							}
						}

						$content .= "Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);

						/* Send mail to first reporting head if exists */
						if(isset($LeaveInfo["teamleader_id"]) && trim($LeaveInfo["teamleader_id"]) != '0' && trim($LeaveInfo["teamleader_id"]) != '')
						{
							$arrTeamLeaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["teamleader_id"]);
							if(count($arrTeamLeaderInfo) > 0)
							{
								$MailTo = $arrTeamLeaderInfo["email"];
								$content = "Dear ".$arrTeamLeaderInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}

						/* Send Mail to delegate Team Leader if exists */
						if(isset($LeaveInfo["deligateTeamLeaderId"]) && trim($LeaveInfo["deligateTeamLeaderId"]) != '0' && trim($LeaveInfo["deligateTeamLeaderId"]) != '')
						{
							$arrDelegatedTeamLeaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateTeamLeaderId"]);
							if(count($arrDelegatedTeamLeaderInfo) > 0)
							{
								$MailTo = $arrDelegatedTeamLeaderInfo["email"];
								$content = "Dear ".$arrDelegatedTeamLeaderInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}

						/* Send Mail to delegate Manager if exists */
						if(isset($LeaveInfo["deligateManagerId"]) && trim($LeaveInfo["deligateManagerId"]) != '0' && trim($LeaveInfo["deligateManagerId"]) != '')
						{
							$arrDelegatedManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateManagerId"]);
							if(count($arrDelegatedManagerInfo) > 0)
							{
								$MailTo = $arrDelegatedManagerInfo["email"];
								$content = "Dear ".$arrDelegatedManagerInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}
					}
				}
				else if((isset($LeaveInfo["deligateManagerId"]) && $LeaveInfo["deligateManagerId"] == $_SESSION["id"]) || (isset($newArrData["deligateManagerId"]) && $newArrData["deligateManagerId"] == $_SESSION["id"]))
				{
					$newArrData["manager_delegate_date"] = Date("Y-m-d H:i:s");
					
					/* Leave approved / rejected by the delegated second reporting head */
					$arrDelegateManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateManagerId"]);

					if(count($arrDelegateManagerInfo) > 0)
					{
						$main_content .= "Leave/s application for ".$LeaveInfo["name"]." has been ".$status[$post["manager_delegate_status"]]." by ".$arrDelegateManagerInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".";
						$main_content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						$Subject = "Leave Request ".$status[$post["manager_delegate_status"]];

						/* Send mail to user who added the leave */
						$MailTo = $LeaveInfo["email"];
						$content = "Dear ".$LeaveInfo["name"].",<br><br>Your application for Leave/s has been ".$status[$post["manager_delegate_status"]]." by ".$arrManagerInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br><br>";

						/* If status is approved, check if delegate is assigned */
						if($status[$post["manager_delegate_status"]] == "Approved")
						{
							/* Check for delegates */
							if($post["delegate"] != 0)
							{
								$DelegatedUserInfo = $objEmployee->fnGetEmployeeById($post["delegate"]);

								if(count($DelegatedUserInfo) > 0)
								{
									$content .="As ".$status[$post["manager_delegate_status"]].", you can avail these leaves from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".Your responsibilities would be delegated to ".$DelegatedUserInfo["name"]." while you are on leave/s, which please note.<br><br>";

									/* Send mail to delegated user */
									$Delegate_MailTo = $DelegatedUserInfo["email"];
									$Delegate_Subject = "Leave Request ".$status[$post["manager_delegate_status"]].' for '.$LeaveInfo['name'];
									$Delegate_content = "Dear ".$DelegatedUserInfo["name"].",<br><br>Kindly note that ".$LeaveInfo["name"]." will be on leave from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"]." and his work responsibilities are delegated to you for while he is on leave/s for the above period.<br><br>Regards,<br>".SITEADMINISTRATOR;
									sendmail($Delegate_MailTo, $Delegate_Subject, $Delegate_content);
								}
							}
						}

						$content .= "Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);

						/* Send mail to second reporting head if exists */
						if(isset($LeaveInfo["manager_id"]) && trim($LeaveInfo["manager_id"]) != '0' && trim($LeaveInfo["manager_id"]) != '')
						{
							$arrManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["manager_id"]);
							if(count($arrManagerInfo) > 0)
							{
								$MailTo = $arrManagerInfo["email"];
								$content = "Dear ".$arrManagerInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}

						/* Send mail to first reporting head if exists */
						if(isset($LeaveInfo["teamleader_id"]) && trim($LeaveInfo["teamleader_id"]) != '0' && trim($LeaveInfo["teamleader_id"]) != '')
						{
							$arrTeamLeaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["teamleader_id"]);
							if(count($arrTeamLeaderInfo) > 0)
							{
								$MailTo = $arrTeamLeaderInfo["email"];
								$content = "Dear ".$arrTeamLeaderInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}

						/* Send Mail to delegate Team Leader if exists */
						if(isset($LeaveInfo["deligateTeamLeaderId"]) && trim($LeaveInfo["deligateTeamLeaderId"]) != '0' && trim($LeaveInfo["deligateTeamLeaderId"]) != '')
						{
							$arrDelegatedTeamLeaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateTeamLeaderId"]);
							if(count($arrDelegatedTeamLeaderInfo) > 0)
							{
								$MailTo = $arrDelegatedTeamLeaderInfo["email"];
								$content = "Dear ".$arrDelegatedTeamLeaderInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}
					}
				}
				else if((isset($LeaveInfo["deligateTeamLeaderId"]) && $LeaveInfo["deligateTeamLeaderId"] == $_SESSION["id"]) || (isset($newArrData["deligateTeamLeaderId"]) && $newArrData["deligateTeamLeaderId"] == $_SESSION["id"]))
				{
					$newArrData["delegate_date"] = Date("Y-m-d H:i:s");
					
					// Leave approved / rejected by the delegated first reporting head 
					/*$arrDelegateTeamLeaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateTeamLeaderId"]);

					if(count($arrDelegateTeamLeaderInfo) > 0)
					{
						$main_content .= "Leave/s application for ".$LeaveInfo["name"]." has been ".$status[$post["delegate_status"]]." by ".$arrDelegateTeamLeaderInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".";
						$main_content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						$Subject = "Leave Request ".$status[$post["delegate_status"]];

						// Send mail to user who added the leave 
						$MailTo = $LeaveInfo["email"];
						$content = "Dear ".$LeaveInfo["name"].",<br><br>Your application for Leave/s has been ".$status[$post["delegate_status"]]." by ".$arrDelegateTeamLeaderInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br><br>Regards,<br>".SITEADMINISTRATOR;
						sendmail($MailTo, $Subject, $content);

						// Send mail to second reporting head if exists 
						if(isset($LeaveInfo["manager_id"]) && trim($LeaveInfo["manager_id"]) != '0' && trim($LeaveInfo["manager_id"]) != '')
						{
							$arrManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["manager_id"]);
							if(count($arrManagerInfo) > 0)
							{
								$MailTo = $arrManagerInfo["email"];
								$content = "Dear ".$arrManagerInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}

						// Send mail to first reporting head if exists 
						if(isset($LeaveInfo["teamleader_id"]) && trim($LeaveInfo["teamleader_id"]) != '0' && trim($LeaveInfo["teamleader_id"]) != '')
						{
							$arrTeamLeaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["teamleader_id"]);
							if(count($arrTeamLeaderInfo) > 0)
							{
								$MailTo = $arrTeamLeaderInfo["email"];
								$content = "Dear ".$arrTeamLeaderInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}

						// Send Mail to delegate Manager if exists 
						if(isset($LeaveInfo["deligateManagerId"]) && trim($LeaveInfo["deligateManagerId"]) != '0' && trim($LeaveInfo["deligateManagerId"]) != '')
						{
							$arrDelegatedManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateManagerId"]);
							if(count($arrDelegatedManagerInfo) > 0)
							{
								$MailTo = $arrDelegatedManagerInfo["email"];
								$content = "Dear ".$arrDelegatedManagerInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}
					}*/
				}
				
				$this->updateArray('pms_leave_form',$newArrData);
				
				$LeaveInfo = $this->fnLeaveInfoById($post["hdnid"]);

				/*if($post['status_manager'] == '0')
				{
					$newArrData = array('id'=>$post['hdnid'],'comment' => $post['comment'],'comment_manager'=>$post['comment_manager'],'status_manager'=>$post['status_manager'],'approved_date_manager'=>$currentdate,'delegate'=>$post['delegate'],'manager_delegate'=>$post['manager_delegate'],'manager_delegate_status'=>$post['manager_delegate_status'],'manager_delegate_comment'=>$post['manager_delegate_comment']);
				}
				else if($post['status_manager'] == '1')
				{
					$currentdate = date('Y-m-d H:i:s');
					$newArrData = array('id'=>$post['hdnid'],'comment' => $post['comment'],'comment_manager'=>$post['comment_manager'],'status_manager'=>$post['status_manager'],'approved_date_manager'=>$currentdate,'delegate'=>$post['delegate'],'manager_delegate'=>$post['manager_delegate'],'manager_delegate_status'=>$post['manager_delegate_status'],'manager_delegate_comment'=>$post['manager_delegate_comment']);
				}
				else if($post['delegate_status'] == '0')
				{
					$newArrData = array('id'=>$post['hdnid'],'delegate_comment'=>$post['delegate_comment'],'delegate_status'=>$post['delegate_status'],'delegate_date'=>$date);
				}
				else if($post['delegate_status'] == '1')
				{
					$newArrData = array('id'=>$post['hdnid'],'delegate_comment'=>$post['delegate_comment'],'delegate_status'=>$post['delegate_status'],'delegate_date'=>$date);
				}
				else if($post['manager_delegate_status'] == '0')
				{
					$newArrData = array('id'=>$post['hdnid'],'manager_delegate_comment'=>$post['manager_delegate_comment'],'manager_delegate_status'=>$post['manager_delegate_status'],'manager_delegate_date'=>$date,'delegate'=>$post['delegate']);
				}
				else if($post['manager_delegate_status'] == '1')
				{
					$newArrData = array('id'=>$post['hdnid'],'manager_delegate_comment'=>$post['manager_delegate_comment'],'manager_delegate_status'=>$post['manager_delegate_status'],'manager_delegate_date'=>$date,'delegate'=>$post['delegate']);
				}
				else
				{
					$currentdate = date('Y-m-d H:i:s');
					$newArrData = array('id'=>$post['hdnid'],'status' => $post['status'],'comment' => $post['comment'],'approved_date' => $post['approved_date'],'approved_date'=>$date,'comment_manager'=>$post['comment_manager'],'status_manager'=>$post['status_manager'],'delegate'=>$post['delegate'],'delegate_comment'=>$post['delegate_comment'],'delegate_status'=>$post['delegate_status'],'manager_delegate_status'=>$post['manager_delegate_status'],'manager_delegate_comment'=>$post['manager_delegate_comment']);
				}*/
				//print_r($post); die;
				//print_r($newArrData); die;
				
				/* changes in attendance when approve / unapprove */
				//if($_SESSION['designation'] == '6' || $_SESSION['designation'] == '18' || $_SESSION['designation'] == '19' || $_SESSION['designation'] == '17' || $_SESSION['designation'] == '25' || $_SESSION['designation'] == '44')

				if((isset($LeaveInfo["manager_id"]) && $LeaveInfo["manager_id"] == $_SESSION["id"]) || (isset($LeaveInfo["deligateManagerId"]) && $LeaveInfo["deligateManagerId"] == $_SESSION["id"]))
				{
					include_once('class.attendance.php');
					$objAttendance = new attendance();

					$LeaveInfo = $this->fnLeaveInfoById($post["hdnid"]);
					$next_monday_date = date('Y-m-d', strtotime('next monday'));

					//echo '<pre>'; print_r($LeaveInfo);
					if($LeaveInfo["isemergency"] == "1")
					{
						//echo 'hello<pre>'; print_r($LeaveInfo);
						if($LeaveInfo["status_manager"] == "1" || ($LeaveInfo["status_manager"] == "0" && $LeaveInfo["manager_delegate_status"] == "1"))
						{
							/* Approve */
							$arrInfo["user_id"] = $LeaveInfo["employee_id"];
							$arrInfo["date"] = $LeaveInfo["start_dt"];
							$arrInfo["leave_id"] = $this->fnGetLeaveTypeIdByTitle("UPL");
							$objAttendance->fnInsertRosterAttendance($arrInfo);
						}
						else
						{
							/* Un approve */
							$arrInfo["user_id"] = $LeaveInfo["employee_id"];
							$arrInfo["date"] = $LeaveInfo["start_dt"];
							$arrInfo["leave_id"] = $this->fnGetLeaveTypeIdByTitle("");

							$objAttendance->fnInsertRosterAttendance($arrInfo);
						}
					}
					else
					{
						if($LeaveInfo["status_manager"] == "1" || ($LeaveInfo["status_manager"] == "0" && $LeaveInfo["manager_delegate_status"] == "1"))
						{
							/* Approve */
							$arrInfo["user_id"] = $LeaveInfo["employee_id"];
							$arrInfo["start_dt"] = $LeaveInfo["start_dt"];
							if($LeaveInfo["ph"] == '1')
							{
								$arrInfo["leave_id"] = $this->fnGetLeaveTypeIdByTitle("PH");
							}
							else if($arrInfo["start_dt"] >= $next_monday_date)
							{
								$arrInfo["leave_id"] = $this->fnGetLeaveTypeIdByTitle("PPL");
							}
							else
							{
								$arrInfo["leave_id"] = $this->fnGetLeaveTypeIdByTitle("UPL");
							}
							$arrInfo["end_dt"] = $LeaveInfo["end_dt"];
							$objAttendance->fnInsertRosterAttendance($arrInfo);
						}
						else
						{
							/* Un approve */
							$arrInfo["user_id"] = $LeaveInfo["employee_id"];
							$arrInfo["start_dt"] = $LeaveInfo["start_dt"];
							$arrInfo["end_dt"] = $LeaveInfo["end_dt"];
							$arrInfo["leave_id"] = $this->fnGetLeaveTypeIdByTitle("");
							$objAttendance->fnInsertRosterAttendance($arrInfo);
						}
					}
				}

				return true;
				
			}
			else
			{
				return 0;
			}
		}
		
		function fnUpdateHalfLeaveStatus($post)
		{
			if(isset($post['isactive']) && $post['isactive'] == 1 )
			{
				header("Location: leave_request.php?info=timepast");
			}

			if(isset($post['isactive']))
				unset($post['isactive']);

			if($_SESSION["usertype"] == "employee")
			{
				include_once('class.employee.php');
				$objEmployee = new employee();

				$status = array("0"=>"Pending", "1"=>"Approved", "2"=>"Unapproved");

				$newArrData = $post;
				$newArrData["id"] = $post["hdnid"];

				$LeaveInfo = $this->fnLeaveInfoById1($post["hdnid"]);

				if(isset($LeaveInfo["teamleader_id"]) && $LeaveInfo["teamleader_id"] == $_SESSION["id"])
				{
					$newArrData["approved_date"] = Date("Y-m-d H:i:s");
					
					/* Leave approved / rejected by the first reporting head */
					/*$arrTeamLeaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["teamleader_id"]);

					if(count($arrTeamLeaderInfo) > 0)
					{
						$main_content .= "Halfday Leave application for ".$LeaveInfo["name"]." has been ".$status[$post["status"]]." by ".$arrTeamLeaderInfo["name"]." on ".$LeaveInfo["leavedate"]." for ".$LeaveInfo["startdate"].".";
						$main_content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						$Subject = "Half Leave Request ".$status[$post["status"]];

						// Send mail to user who added the leave 
						$MailTo = $LeaveInfo["email"];
						$content = "Dear ".$LeaveInfo["name"].",<br><br>Your application for Halfday Leave has been ".$status[$post["status"]]." by ".$arrTeamLeaderInfo["name"]." on ".$LeaveInfo["leavedate"]." for ".$LeaveInfo["startdate"].".<br><br>Regards,<br>".SITEADMINISTRATOR;
						sendmail($MailTo, $Subject, $content);

						// Send mail to second reporting head if exists 
						if(isset($LeaveInfo["manager_id"]) && trim($LeaveInfo["manager_id"]) != '0' && trim($LeaveInfo["manager_id"]) != '')
						{
							$arrManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["manager_id"]);
							if(count($arrManagerInfo) > 0)
							{
								$MailTo = $arrManagerInfo["email"];
								$content = "Dear ".$arrManagerInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}

						// Send Mail to delegate Team Leader if exists 
						if(isset($LeaveInfo["deligateTeamLeaderId"]) && trim($LeaveInfo["deligateTeamLeaderId"]) != '0' && trim($LeaveInfo["deligateTeamLeaderId"]) != '')
						{
							$arrDelegatedTeamLeaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateTeamLeaderId"]);
							if(count($arrDelegatedTeamLeaderInfo) > 0)
							{
								$MailTo = $arrDelegatedTeamLeaderInfo["email"];
								$content = "Dear ".$arrDelegatedTeamLeaderInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}

						// Send Mail to delegate Manager if exists 
						if(isset($LeaveInfo["deligateManagerId"]) && trim($LeaveInfo["deligateManagerId"]) != '0' && trim($LeaveInfo["deligateManagerId"]) != '')
						{
							$arrDelegatedManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateManagerId"]);
							if(count($arrDelegatedManagerInfo) > 0)
							{
								$MailTo = $arrDelegatedManagerInfo["email"];
								$content = "Dear ".$arrDelegatedManagerInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}
					}*/
				}
				else if(isset($LeaveInfo["manager_id"]) && $LeaveInfo["manager_id"] == $_SESSION["id"])
				{
					$newArrData["approved_date_manager"] = Date("Y-m-d H:i:s");
					
					/* Leave approved / rejected by the second reporting head */
					$arrManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["manager_id"]);

					if(count($arrManagerInfo) > 0)
					{
						/* Leave approved / rejected by second reporting head */
						$main_content .= "Halfday Leave application for ".$LeaveInfo["name"]." has been ".$status[$post["status_manager"]]." by ".$arrManagerInfo["name"]." on ".$LeaveInfo["leavedate"]." for ".$LeaveInfo["startdate"].".";
						$main_content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						$Subject = "Half Leave Request ".$status[$post["status_manager"]];

						/* Send mail to user who added the leave */
						$MailTo = $LeaveInfo["email"];
						$content = "Dear ".$LeaveInfo["name"].",<br><br>Your application for Halfday Leave has been ".$status[$post["status_manager"]]." by ".$arrManagerInfo["name"]." on ".$LeaveInfo["leavedate"]." for ".$LeaveInfo["startdate"].".<br><br>";
						$content .= "Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);

						/* Send mail to first reporting head if exists */
						if(isset($LeaveInfo["teamleader_id"]) && trim($LeaveInfo["teamleader_id"]) != '0' && trim($LeaveInfo["teamleader_id"]) != '')
						{
							$arrTeamLeaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["teamleader_id"]);
							if(count($arrTeamLeaderInfo) > 0)
							{
								$MailTo = $arrTeamLeaderInfo["email"];
								$content = "Dear ".$arrTeamLeaderInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}

						/* Send Mail to delegate Team Leader if exists */
						if(isset($LeaveInfo["deligateTeamLeaderId"]) && trim($LeaveInfo["deligateTeamLeaderId"]) != '0' && trim($LeaveInfo["deligateTeamLeaderId"]) != '')
						{
							$arrDelegatedTeamLeaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateTeamLeaderId"]);
							if(count($arrDelegatedTeamLeaderInfo) > 0)
							{
								$MailTo = $arrDelegatedTeamLeaderInfo["email"];
								$content = "Dear ".$arrDelegatedTeamLeaderInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}

						/* Send Mail to delegate Manager if exists */
						if(isset($LeaveInfo["deligateManagerId"]) && trim($LeaveInfo["deligateManagerId"]) != '0' && trim($LeaveInfo["deligateManagerId"]) != '')
						{
							$arrDelegatedManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateManagerId"]);
							if(count($arrDelegatedManagerInfo) > 0)
							{
								$MailTo = $arrDelegatedManagerInfo["email"];
								$content = "Dear ".$arrDelegatedManagerInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}
					}
				}
				else if((isset($LeaveInfo["deligateManagerId"]) && $LeaveInfo["deligateManagerId"] == $_SESSION["id"]) || (isset($newArrData["deligateManagerId"]) && $newArrData["deligateManagerId"] == $_SESSION["id"]))
				{
					$newArrData["manager_delegate_date"] = Date("Y-m-d H:i:s");
					
					/* Leave approved / rejected by the delegated second reporting head */
					$arrDelegateManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateManagerId"]);

					if(count($arrDelegateManagerInfo) > 0)
					{
						$main_content .= "Halfday Leave application for ".$LeaveInfo["name"]." has been ".$status[$post["manager_delegate_status"]]." by ".$arrDelegateManagerInfo["name"]." on ".$LeaveInfo["leavedate"]." for ".$LeaveInfo["startdate"].".";
						$main_content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						$Subject = "Half Leave Request ".$status[$post["manager_delegate_status"]];

						/* Send mail to user who added the leave */
						$MailTo = $LeaveInfo["email"];
						$content = "Dear ".$LeaveInfo["name"].",<br><br>Your application for Halfday Leave has been ".$status[$post["manager_delegate_status"]]." by ".$arrManagerInfo["name"]." on ".$LeaveInfo["leavedate"]." for ".$LeaveInfo["startdate"].".<br><br>";

						$content .= "Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);

						/* Send mail to second reporting head if exists */
						if(isset($LeaveInfo["manager_id"]) && trim($LeaveInfo["manager_id"]) != '0' && trim($LeaveInfo["manager_id"]) != '')
						{
							$arrManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["manager_id"]);
							if(count($arrManagerInfo) > 0)
							{
								$MailTo = $arrManagerInfo["email"];
								$content = "Dear ".$arrManagerInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}

						/* Send mail to first reporting head if exists */
						if(isset($LeaveInfo["teamleader_id"]) && trim($LeaveInfo["teamleader_id"]) != '0' && trim($LeaveInfo["teamleader_id"]) != '')
						{
							$arrTeamLeaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["teamleader_id"]);
							if(count($arrTeamLeaderInfo) > 0)
							{
								$MailTo = $arrTeamLeaderInfo["email"];
								$content = "Dear ".$arrTeamLeaderInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}

						/* Send Mail to delegate Team Leader if exists */
						if(isset($LeaveInfo["deligateTeamLeaderId"]) && trim($LeaveInfo["deligateTeamLeaderId"]) != '0' && trim($LeaveInfo["deligateTeamLeaderId"]) != '')
						{
							$arrDelegatedTeamLeaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateTeamLeaderId"]);
							if(count($arrDelegatedTeamLeaderInfo) > 0)
							{
								$MailTo = $arrDelegatedTeamLeaderInfo["email"];
								$content = "Dear ".$arrDelegatedTeamLeaderInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}
					}
				}
				else if((isset($LeaveInfo["deligateTeamLeaderId"]) && $LeaveInfo["deligateTeamLeaderId"] == $_SESSION["id"]) || (isset($newArrData["deligateTeamLeaderId"]) && $newArrData["deligateTeamLeaderId"] == $_SESSION["id"]))
				{
					$newArrData["delegate_date"] = Date("Y-m-d H:i:s");
					
					/* Leave approved / rejected by the delegated first reporting head */
					/*$arrDelegateTeamLeaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateTeamLeaderId"]);

					if(count($arrDelegateTeamLeaderInfo) > 0)
					{
						$main_content .= "Halfday Leave application for ".$LeaveInfo["name"]." has been ".$status[$post["delegate_status"]]." by ".$arrDelegateTeamLeaderInfo["name"]." on ".$LeaveInfo["leavedate"]." for ".$LeaveInfo["startdate"].".";
						$main_content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						$Subject = "Half Leave Request ".$status[$post["delegate_status"]];

						// Send mail to user who added the leave 
						$MailTo = $LeaveInfo["email"];
						$content = "Dear ".$LeaveInfo["name"].",<br><br>Your application for Halfday Leave has been ".$status[$post["delegate_status"]]." by ".$arrDelegateTeamLeaderInfo["name"]." on ".$LeaveInfo["leavedate"]." for ".$LeaveInfo["startdate"].".<br><br>Regards,<br>".SITEADMINISTRATOR;
						sendmail($MailTo, $Subject, $content);

						// Send mail to second reporting head if exists 
						if(isset($LeaveInfo["manager_id"]) && trim($LeaveInfo["manager_id"]) != '0' && trim($LeaveInfo["manager_id"]) != '')
						{
							$arrManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["manager_id"]);
							if(count($arrManagerInfo) > 0)
							{
								$MailTo = $arrManagerInfo["email"];
								$content = "Dear ".$arrManagerInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}

						// Send mail to first reporting head if exists 
						if(isset($LeaveInfo["teamleader_id"]) && trim($LeaveInfo["teamleader_id"]) != '0' && trim($LeaveInfo["teamleader_id"]) != '')
						{
							$arrTeamLeaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["teamleader_id"]);
							if(count($arrTeamLeaderInfo) > 0)
							{
								$MailTo = $arrTeamLeaderInfo["email"];
								$content = "Dear ".$arrTeamLeaderInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}

						// Send Mail to delegate Manager if exists 
						if(isset($LeaveInfo["deligateManagerId"]) && trim($LeaveInfo["deligateManagerId"]) != '0' && trim($LeaveInfo["deligateManagerId"]) != '')
						{
							$arrDelegatedManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateManagerId"]);
							if(count($arrDelegatedManagerInfo) > 0)
							{
								$MailTo = $arrDelegatedManagerInfo["email"];
								$content = "Dear ".$arrDelegatedManagerInfo["name"].",<br><br>".$main_content;

								sendmail($MailTo, $Subject, $content);
							}
						}
					}*/
				}

				$currentdate = date('Y-m-d H:i:s');
				
				/*
				if(isset($post['status_manager']) && $post['status_manager'] == '0')
				{
					$newArrData = array('id'=>$post['hdnid'],'status' => $post['status'],'comment' => $post['comment'],'approved_date' => $post['approved_date'],'comment_manager'=>$post['comment_manager'],'status_manager'=>$post['status_manager'],'approved_date_manager'=>$currentdate,'delegate'=>$post['delegate'],'delegate_status'=>$post['delegate_status'],'delegate_comment'=>$post['delegate_comment'],'delegate_date'=>$currentdate,'manager_delegate_status'=>$post['manager_delegate_status'],'manager_delegate_comment'=>$post['manager_delegate_comment'],'manager_delegate_date'=>$currentdate);
				}
				else if(isset($post['status_manager']) &&  $post['status_manager'] == '1')
				{
					$newArrData = array('id'=>$post['hdnid'],'status' => $post['status'],'comment' => $post['comment'],'approved_date' => $post['approved_date'],'approved_date'=>$date,'comment_manager'=>$post['comment_manager'],'status_manager'=>$post['status_manager'],'approved_date_manager'=>$currentdate,'delegate'=>$post['delegate'],'delegate_status'=>$post['delegate_status'],'delegate_comment'=>$post['delegate_comment'],'delegate_date'=>$currentdate,'manager_delegate_status'=>$post['manager_delegate_status'],'manager_delegate_comment'=>$post['manager_delegate_comment'],'manager_delegate_date'=>$currentdate);
				}
				else if(isset($post['manager_delegate_status']) && $post['manager_delegate_status'] == '1' || $post['manager_delegate_status'] == '0')
				{
					$newArrData = array('id'=>$post['hdnid'],'employee_id'=>$post['employeeid'],'manager_delegate_status' => $post['manager_delegate_status'],'manager_delegate_comment' => $post['manager_delegate_comment'],'manager_delegate_date'=> $currentdate,'deligateManagerId'=>$post['deligateManagerId']);
				}
				*/
				//echo '<pre>';
				//print_r($newArrData); die;
				$this->updateArray('pms_half_leave_form',$newArrData);

				/* changes in attendance when approve / unapprove */
				//if($_SESSION['designation'] == '6' || $_SESSION['designation'] == '18' || $_SESSION['designation'] == '19' || $_SESSION['designation'] == '25' || $_SESSION['designation'] == '44' || $_SESSION['designation'] == '17')
				
				$LeaveInfo = $this->fnHalfLeaveInfoById($post["hdnid"]);
				
				if((isset($LeaveInfo["manager_id"]) && $LeaveInfo["manager_id"] == $_SESSION["id"]) || (isset($LeaveInfo["deligateManagerId"]) && $LeaveInfo["deligateManagerId"] == $_SESSION["id"]))
				{
					include_once('class.attendance.php');

					$objAttendance = new attendance();

					//$LeaveInfo = $this->fnHalfLeaveInfoById($post["hdnid"]);
					$next_monday_date = date('Y-m-d', strtotime('next monday'));

					if($LeaveInfo["status_manager"] == "1" || ($LeaveInfo["status_manager"] == "0" && $LeaveInfo["manager_delegate_status"] == "1"))
					{
						/* Approve */
						$arrInfo["user_id"] = $LeaveInfo["employee_id"];
						$arrInfo["date"] = $LeaveInfo["start_dt"];
						if($arrInfo["date"] >= $next_monday_date )
						{
							$arrInfo["leave_id"] = $this->fnGetLeaveTypeIdByTitle("PHL");
						}
						else
						{
							$arrInfo["leave_id"] = $this->fnGetLeaveTypeIdByTitle("UHL");
						}

						$objAttendance->fnInsertRosterAttendance($arrInfo);
					}
					else
					{
						/* Un approve */
						$arrInfo["user_id"] = $LeaveInfo["employee_id"];
						$arrInfo["date"] = $LeaveInfo["start_dt"];
						$arrInfo["leave_id"] = $this->fnGetLeaveTypeIdByTitle("");

						$objAttendance->fnInsertRosterAttendance($arrInfo);
					}
				}
				return true;
			}
			else
			{
				return 0;
			}
		}

		function fnLeaveInfoById($id)
		{
			$arrLeaveFormValues = array();
			$query = "SELECT *,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') as startdate, DATE_FORMAT(leaves.start_date,'%Y-%m-%d') as start_dt, DATE_FORMAT(leaves.end_date,'%Y-%m-%d') as end_dt, DATE_FORMAT(leaves.date,'%d-%m-%Y') as leavedate,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') as enddate FROM `pms_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id WHERE leaves.`id` = '".mysql_real_escape_string($id)."' ";
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
		function fnHalfLeaveInfoById($id)
		{
			$arrLeaveFormValues = array();
			$query = "SELECT *,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') as startdate, DATE_FORMAT(leaves.start_date,'%Y-%m-%d') as start_dt, DATE_FORMAT(leaves.date,'%d-%m-%Y') as leavedate,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') as enddate FROM `pms_half_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id WHERE leaves.`id` = '".mysql_real_escape_string($id)."' ";
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
		function fnLeaveInfoById1($id)
		{
			$arrLeaveFormValues = array();
			$query = "SELECT *,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') as startdate, DATE_FORMAT(leaves.start_date,'%Y-%m-%d') as start_dt, DATE_FORMAT(leaves.date,'%d-%m-%Y') as leavedate,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') as enddate FROM `pms_half_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id WHERE leaves.`id` = '$id'";

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

		function fnGetLeaveDetailByHead($id,$ids,$start_date,$end_date)
		{
			//echo $id.'---'.$ids.'========='.$leaderid.'+++++'.$start_date.'==='.$end_date;
			$arrLeaveRecordDetails = array();
			$query = "SELECT *,leaves.id as leavesid, DATE_FORMAT(leaves.start_date,'%Y-%m-%d') AS startdate,leaves.nodays as noofdays,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') as hstart_date,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') as hend_date,employee.name as empName FROM `pms_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id WHERE leaves.employee_id IN($ids) AND (DATE_FORMAT(leaves.start_date,'%Y-%m-%d') between DATE_FORMAT('$start_date','%Y-%m-%d') AND DATE_FORMAT('$end_date','%Y-%m-%d') OR DATE_FORMAT(leaves.end_date,'%Y-%m-%d') between DATE_FORMAT('$start_date','%Y-%m-%d') AND DATE_FORMAT('$end_date','%Y-%m-%d')) AND leaves.id != '$id'";
			$this->query($query);

				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrLeaveRecordDetails[] = $this->fetchrow();
					}
				}
			return $arrLeaveRecordDetails;
		}

		function fnGetLastThreeLeaves($id,$leave_id)
		{
			$arrLastLeaves = array();
			$query = "SELECT nodays as number_d,status as status_t,status_manager as status_m,DATE_FORMAT(start_date,'%d-%m-%Y') AS start_d,DATE_FORMAT(end_date,'%d-%m-%Y') AS end_d FROM `pms_leave_form` WHERE `employee_id` = '$id' AND `id` != '$leave_id' ORDER BY id DESC LIMIT 3";
			$this->query($query);

				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrLastLeaves[] = $this->fetchrow();
					}
				}
			return $arrLastLeaves;
		}

		function fnGetLeaveDetailById($id)
		{
			$arrLastLeaves = array();
			$query = "SELECT *,DATE_FORMAT(`date`,'%Y') AS cur_year,DATE_FORMAT(`start_date`,'%d-%m-%Y') AS startDate,DATE_FORMAT(`end_date`,'%d-%m-%Y') AS endDate  FROM `pms_leave_form` WHERE `id` = '$id'";
			$this->query($query);

				if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$arrLastLeaves = $this->fetchrow();
					}
				}
			return $arrLastLeaves;
		}

		function fnGetLastLeave($id,$leave_id)
		{
			$arrLastLeaves = array();
			$query = "SELECT nodays as number_d,status as status_t,status_manager as status_m,DATE_FORMAT(start_date,'%d-%m-%Y') AS start_d,DATE_FORMAT(end_date,'%d-%m-%Y') AS end_d,delegate_status as d_TeamLeaderStatus,deligateTeamLeaderId as d_teamleaderId,deligateManagerId as d_managerId,manager_delegate_status as d_ManagerStatus FROM `pms_leave_form` WHERE `employee_id` = '$id' AND `id` != '$leave_id' ORDER BY id DESC LIMIT 1";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrLastLeaves = $this->fetchrow();
				}
			}
			return $arrLastLeaves;
		}

		function fnGetEmployeeLeaveByDate($EmployeeId, $Date)
		{
			$arrLeave = array();
			$sSQL = "select * from pms_leave_form where employee_id ='$EmployeeId' and '$Date' between date_format(start_date,'%Y-%m-%d') and date_format(end_date,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1'))";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrLeave = $this->fetchrow();
				}
			}

			return $arrLeave;
		}

		function fnDisableLeaveUpdation($EmployeeId, $FromDate, $TillDate)
		{
			$sSQL = "update pms_leave_form set isactive='1' where employee_id='".mysql_real_escape_string($EmployeeId)."' and (date_format(start_date,'%Y-%m-%d') between '".mysql_real_escape_string($FromDate)."' and '".mysql_real_escape_string($TillDate)."' or date_format(end_date,'%Y-%m-%d') between '".mysql_real_escape_string($FromDate)."' and '".mysql_real_escape_string($TillDate)."')";
			$this->query($sSQL);

			$sSQL = "update pms_half_leave_form set isactive='1' where employee_id='".mysql_real_escape_string($EmployeeId)."' and date_format(start_date,'%Y-%m-%d') between '".mysql_real_escape_string($FromDate)."' and '".mysql_real_escape_string($TillDate)."'";
			$this->query($sSQL);

			/* As requested by parvez sir, that SM doen not effect if it is rostered or not so do not deactivate it */
			/*$sSQL = "update pms_shift_movement set isactive='1' where userid='".mysql_real_escape_string($EmployeeId)."' and date_format(movement_date,'%Y-%m-%d') between '".mysql_real_escape_string($FromDate)."' and '".mysql_real_escape_string($TillDate)."'";
			$this->query($sSQL);*/
		}

		function fnDisableAllLeaveUpdationsByDate($TillDate)
		{
			$sSQL = "update pms_leave_form set isactive='1' where date_format(start_date,'%Y-%m-%d') <= '".mysql_real_escape_string($TillDate)."'";
			$this->query($sSQL);

			$sSQL = "update pms_half_leave_form set isactive='1' where date_format(start_date,'%Y-%m-%d') <= '".mysql_real_escape_string($TillDate)."'";
			$this->query($sSQL);

			/* As requested by parvez sir, that SM doen not effect if it is rostered or not so do not deactivate it */
			/*$sSQL = "update pms_shift_movement set isactive='1' where  date_format(movement_date,'%Y-%m-%d') <= '".mysql_real_escape_string($TillDate)."'";
			$this->query($sSQL);*/
		}

		function fnSaveEmergencyLeave($arrLeaveInfo)
		{
			//echo '<pre>'; print_r($arrLeaveInfo); die;
			$shiftCheck = $this->fnCheckShiftMomentById($arrLeaveInfo['start_date'],$arrLeaveInfo['end_date'],$arrLeaveInfo['employee_id']);
			//echo 'shiftCheck:'.$shiftCheck; die;
			if($shiftCheck > 0)
			{
				//echo 'hello'; die;
				header("Location: emergency_leave_list.php?info=shift");
				exit;
			}
			else
			{
				//echo 'hello1'; die;
				$leaveExist = $this->fnCheckEmergencyLeaveExist($arrLeaveInfo);
				
				if($leaveExist > 0)
				{
					header("Location: emergency_leave_list.php?info=exist");
					exit;
				}
				else
				{
					//echo 'hello'; die;
					$halfLeaveExist = $this->fnCheckHalfLeaveExist($arrLeaveInfo);
					if($halfLeaveExist > 0)
					{
						header("Location: emergency_leave_list.php?info=half");
						exit;
					}
					else
					{
						/* Check if approved LWP added by admin */
						$sSQL = "select * from pms_approved_lwp where date_format(lwp_date, '%Y-%m-%d') = '".mysql_real_escape_string($arrLeaveInfo['start_date'])."' and user_id='".mysql_real_escape_string($arrLeaveInfo['employee_id'])."' and approval_status in (0,1)";
						$this->query($sSQL);
						if($this->num_rows() > 0)
						{
							header("Location: emergency_leave_list.php?info=admexist");
							exit;
						}
						else
						{
							//echo 'hello1'; die;
							$this->insertArray("pms_leave_form",$arrLeaveInfo);
							return true;
						}
					}
				}
			}
		}

		function fnCheckEmergencyLeaveExist($data)
		{
			$sSQL ="SELECT count( id ) AS count,status,status_manager,deligateManagerId,manager_delegate_status FROM pms_leave_form WHERE ('".$data['start_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d') or '".$data['end_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d')) and employee_id = '".$data['employee_id']."' and (((status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1))) and isactive='0') or (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1'))) and (status != 2 and delegate_status!=2)";
			//$sSQL = "select count(id) as count from pms_leave_form where employee_id ='".$data['employee_id']."' and '".$data['start_date']."' between date_format(start_date,'%Y-%m-%d') and date_format(end_date,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1'))";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$count = $this->f('count');
				}
			}
			return $count;
		}

		function fnCheckHalfLeaveExist($data)
		{
			
			$sSQL ="SELECT count( id ) AS count,status,status_manager,deligateManagerId,manager_delegate_status FROM pms_half_leave_form WHERE ('".$data['start_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(start_date,'%Y-%m-%d')) and employee_id = '".$data['employee_id']."' and (((status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1))) and isactive='0') or (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1'))) and (status != 2 and delegate_status!=2)";
			//$sSQL = "select count(id) as count from pms_leave_form where employee_id ='".$data['employee_id']."' and '".$data['start_date']."' between date_format(start_date,'%Y-%m-%d') and date_format(end_date,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1'))";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$count = $this->f('count');
				}
			}
			return $count;
		}

		function fnGetAllEmergencyLeaveForm($id)
		{
			include_once("class.employee.php");

			$objEmployee = new employee();
			$arrEmployees = $objEmployee->fnGetAllemployees($_SESSION["id"]);

			include_once("class.employee.php");

			$objEmployee = new employee();

			$arrtemp = array();

			/* Get Delegated Manager id */
			$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($_SESSION['id']);

			if(count($arrDelegatedManagerId) > 0 )
			{
				foreach($arrDelegatedManagerId as $delegatesManagerIds)
				{
					$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
					$arrEmployees = $arrEmployees + $arrtemp;
				}
			}
			
			$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);

			if(count($arrDelegatedTeamLeaderId) > 0 )
			{
				foreach($arrDelegatedTeamLeaderId as $delegatesIds)
				{
					$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
					$arrEmployees = $arrEmployees + $arrtemp;
				}
			}
			
			/*if($_SESSION["designation"] == "6" || $_SESSION["designation"] == "18" || $_SESSION["designation"] == "19")
			{
				// Get Delegated Manager id 
				$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($_SESSION['id']);

				if(count($arrDelegatedManagerId) > 0 )
				{
					foreach($arrDelegatedManagerId as $delegatesManagerIds)
					{
						$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
						$arrEmployees = $arrEmployees + $arrtemp;
					}
				}
			}
			else if ($_SESSION["designation"] == "7" || $_SESSION["designation"] == "13")
			{
				// Get delegated teamleader id 
				$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);

				if(count($arrDelegatedTeamLeaderId) > 0 )
				{
					foreach($arrDelegatedTeamLeaderId as $delegatesIds)
					{
						$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
						$arrEmployees = $arrEmployees + $arrtemp;
					}
				}
			}*/

			if(count($arrEmployees) > 0)
			{
				$arrEmployees = array_filter($arrEmployees,'strlen');
			}

			$ids = "0";
			if(count($arrEmployees) > 0)
			{
				$ids .= ",".implode(",",$arrEmployees);
			}


			$arrLeave = array();

			//$sSQL = "select l.*, e.name as employeename from pms_leave_form l INNER JOIN pms_employee e ON e.id = l.employee_id where l.employee_id in ($empstr) and l.isemergency='1'";
			$sSQL = "select l.*, e.name as employeename from pms_leave_form l INNER JOIN pms_employee e ON e.id = l.employee_id where l.employee_id in ($ids) and l.isemergency='1'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$arrLeave[] = $this->fetchrow();
				}
			}

			return $arrLeave;
		}

		function fnGetAllLeavesByEmployeeId($eid,$month,$year)
		{
			//echo $eid.'--'.$month.'---'.$year;
			$date = $year.'-'.$month;

			$arrTotalLeaves = array();


			//$sSQL = "SELECT l.*,e.name,DATE_FORMAT(l.start_date,'%d-%m-%Y') as startDate,DATE_FORMAT(l.end_date,'%d-%m-%Y') as endDate FROM `pms_leave_form` AS l LEFT JOIN pms_employee AS e ON l.employee_id = e.id WHERE l.employee_id = '$eid' AND (DATE_FORMAT(l.start_date,'%Y-%m') = '$date' or DATE_FORMAT(l.end_date,'%Y-%m') = '$date')";
			$sSQL = "(SELECT l.id,e.name as emp_name,l.nodays as noOfDays,date_format(l.start_date,'%d-%m-%Y') as startdate,l.start_date as start_date,date_format(l.end_date,'%d-%m-%Y') as enddate,l.end_date as end_date,l.reason as leave_reason,l.status_manager as leave_status,l.isactive as active_status FROM `pms_leave_form` AS l LEFT JOIN pms_employee AS e ON l.employee_id = e.id WHERE l.employee_id = '$eid' AND (DATE_FORMAT(l.start_date,'%Y-%m') = '$date' or DATE_FORMAT(l.end_date,'%Y-%m') = '$date'))
			union
			(SELECT hl.id,e.name as emp_name,hl.nodays as noOfDays,date_format(hl.start_date,'%d-%m-%Y') as startdate,hl.start_date as start_date,date_format(hl.start_date,'%d-%m-%Y') as enddate,hl.start_date as end_date,hl.reason as leave_reason,hl.status_manager as leave_status,hl.isactive as active_status FROM `pms_half_leave_form` AS hl LEFT JOIN pms_employee AS e ON hl.employee_id = e.id WHERE hl.employee_id = '$eid' AND (DATE_FORMAT(hl.start_date,'%Y-%m') = '$date'))";
			//echo '<br><br><br>';
			$this->query($sSQL);

			if($this->num_rows())
			{
				while($this->next_record())
				{
					$arrTotalLeaves[] = $this->fetchrow();
				}
			}
			//print_r($arrTotalLeaves);
			return $arrTotalLeaves;
		}

		function fnGetEmployeeHalfdayLeaveByDate($EmployeeId, $Date)
		{
			$arrHalfdayLeave = array();
			$sSQL = "select h.* from pms_half_leave_form h INNER JOIN pms_employee e ON e.id = h.employee_id where h.employee_id='".mysql_real_escape_string($EmployeeId)."' and date_format(h.start_date,'%Y-%m-%d') = '".mysql_real_escape_string($Date)."' and (h.status_manager='1' or (h.status_manager='0' and h.manager_delegate_status='1'))";
			$this->query($sSQL);
			if($this->num_rows())
			{
				if($this->next_record())
				{
					$arrHalfdayLeave = $this->fetchrow();
				}
			}

			return $arrHalfdayLeave;
		}

		function checkPresent($id,$start_date,$end_date)
		{
			$sSQL = "select id,in_time,out_time from pms_attendance where user_id = '$id' and date_format(`date`,'%Y-%m-%d') between date_format('$start_date','%Y-%m-%d') and date_format('$end_date','%Y-%m-%d')";

			$this->query($sSQL);

			if($this->num_rows())
			{
				if($this->next_record())
				{
					$intime = $this->f('in_time');
					$outtime = $this->f('out_time');

				}
			}
			//echo 'intime'.$intime.'outtime'.$outtime;
			if($intime == '00:00:00' && $outtime == '00:00:00')
			{
				return 1;
			}
			else
			{
				return 0;
			}
		}

		function fnIsUserHalfDayApprovedByDate($UserId, $LeaveDate)
		{
			$ishalfdayleave = 0;

			$sSQL = "select date_format(start_date, '%Y-%m-%d') as start_date, date_format(date, '%Y') as date_y, date_format(date, '%m') as date_m, date_format(date, '%d') as date_d, employee_id from pms_half_leave_form where employee_id='".mysql_real_escape_string($UserId)."' and date_format(start_date,'%Y-%m-%d') = '".mysql_real_escape_string($LeaveDate)."' and (status_manager='1' or (status_manager='0' and deligateManagerId!='' and manager_delegate_status='1'))";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				/* For support */
				if($this->next_record())
				{
					include_once("class.employee.php");
					include_once("class.designation.php");

					$objEmployee = new employee();
					$objDesignation = new designations();

					$designationId = $objEmployee->fnGetEmployeeDesignation($this->f("employee_id"));

					$arrDesignation = $objDesignation->fnGetDesignationById($designationId);

					if(isset($arrDesignation["allow_roster_generation"]) && $arrDesignation["allow_roster_generation"] == '1')
					{
						/* check if halfday leave is rosterd / unrostered rostered */
						$sSQL = "select rd.attendance from pms_roster r INNER JOIN pms_roster_detail rd ON r.id = rd.rosterid and rd.attendance='PHL' and date_format(rd.rostereddate, '%Y-%m-%d') = '".mysql_real_escape_string($LeaveDate)."' and r.userid=".mysql_real_escape_string($UserId);
						$this->query($sSQL);
						if($this->num_rows() > 0)
						{
							$ishalfdayleave = 4;
						}
						else
						{
							$ishalfdayleave = 5;
						}
					}
					else
					{
						$next_monday_date = date("Y-m-d",strtotime('next Monday',mktime(0,0,0,$this->f("date_m"),$this->f("date_d"),$this->f("date_y"))));

						if($this->f("start_date") >= $next_monday_date)
						{
							$ishalfdayleave = 4;
						}
						else
						{
							$ishalfdayleave = 5;
						}
					}
				}
			}

			return $ishalfdayleave;
		}

		function fnGetEarnedLeaves($UserId, $month, $year)
		{
			//$month = '06';
			$half_monthly_leave = $monthly_leave = $total_leaves_earned = 0;

			/* Check if leave earned half monthly */
			$sSQL = "select added_no_of_leaves from pms_leave_history where emp_id='".mysql_real_escape_string($UserId)."' and month='".mysql_real_escape_string($month)."' and year='".mysql_real_escape_string($year)."' and ishalfmonthly='1'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$half_monthly_leave = $this->f("added_no_of_leaves");
				}
			}


			/* Check if leave earned monthly */
			$sSQL = "select leave_earns from pms_attendance_report where employee_id='".mysql_real_escape_string($UserId)."' and month='".mysql_real_escape_string($month)."' and year='".mysql_real_escape_string($year)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$monthly_leave = $this->f("leave_earns");
				}
			}

			$total_leaves_earned = $half_monthly_leave + $monthly_leave;


			return $total_leaves_earned;
		}
		
		/*function fnCheckPendingLeaveRequestByUserId($UserId)
		{
			include_once("class.employee.php");
			$objEmployee = new employee();
			
			// Check if the leave request is pending by the user 
			$current_date = date('Y-m-d');
			$prevdate = Date('Y-m-d', strtotime('-1 day'));
			$pending_leave_count = 0;
			
			// Get delegated teamleader id 
			$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($UserId);

			// Get Delegated Manager id 
			$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($UserId);
			
			$arrEmployee = array();
			$arrtemp = array();
			if(count($arrDelegatedTeamLeaderId) > 0 )
			{
				foreach($arrDelegatedTeamLeaderId as $delegatesIds)
				{
					//echo $delegatesIds;
					$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
					$arrEmployee = $arrEmployee + $arrtemp;
				}
			}
			if(count($arrDelegatedManagerId) > 0 )
			{
				foreach($arrDelegatedManagerId as $delegatesManagerIds)
				{
					//echo $delegatesIds;
					$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
					$arrEmployee = $arrEmployee + $arrtemp;
				}
			}
			
			$arrEmployee[] = 0;
			
			if(count($arrEmployee) > 0)
			{
				$arrEmployee = array_filter($arrEmployee,'strlen');
			}
			
			$ids = "0";
			if(count($arrEmployee) > 0)
			{
				$ids = implode(',',$arrEmployee);
			}
			
			$sSQL = "select count(l.id) as pending_leave_count from pms_leave_form l INNER JOIN pms_employee e ON e.id = l.employee_id where l.isactive = '0' and e.status = '0' and ((DATE_FORMAT(l.start_date,'%Y-%m-%d') >= '$current_date' and DATE_FORMAT(l.end_date,'%Y-%m-%d') >= '$current_date') or (DATE_FORMAT(l.start_date,'%Y-%m-%d') >= '$prevdate' and DATE_FORMAT(l.end_date,'%Y-%m-%d') >= '$prevdate' and l.isemergency = '1')) and ((l.teamleader_id='".mysql_real_escape_string($UserId)."' and (l.status='0' and l.delegate_status in (0,null) and l.status_manager = '0' and l.manager_delegate_status in (0,null))) or (l.manager_id='".mysql_real_escape_string($UserId)."' and (l.status_manager = '0' and l.status != '2' and l.delegate_status != '2' and l.manager_delegate_status in (0,null))) or ((l.deligateTeamLeaderId='".mysql_real_escape_string($UserId)."' or (l.employee_id in ($ids) and '".mysql_real_escape_string($UserId)."' = (select delegate from pms_leave_form where employee_id = l.teamleader_id and '$current_date' between DATE_FORMAT(`start_date`,'%Y-%m-%d') and DATE_FORMAT(`end_date`,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1')) ORDER BY `id` LIMIT 0,1))) and (l.delegate_status in (0,null) and l.status ='0' and l.status_manager = '0' and l.manager_delegate_status in (0,null))) or ((l.deligateManagerId='".mysql_real_escape_string($UserId)."' or (l.employee_id in ($ids) and '".mysql_real_escape_string($UserId)."' = (select delegate from pms_leave_form where employee_id = l.manager_id and '$current_date' between DATE_FORMAT(`start_date`,'%Y-%m-%d') and DATE_FORMAT(`end_date`,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1')) ORDER BY `id` LIMIT 0,1))) and l.manager_delegate_status in (0,null) and l.status != '2' and l.delegate_status != '2' and l.status_manager = '0')) and l.isadminadded in (0,null)";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$pending_leave_count = $this->f("pending_leave_count");
				}
			}
			
			return $pending_leave_count;
		}*/

		function fnCheckPendingLeaveRequestByUserId($UserId)
		{
			include_once("class.employee.php");
			$objEmployee = new employee();
			
			/* Check if the leave request is pending by the user */
			$current_date = date('Y-m-d');
			$prevdate = Date('Y-m-d', strtotime('-1 day'));
			$pending_leave_count = 0;
			
			/* Get delegated teamleader id */
			$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($UserId);

			/* Get Delegated Manager id */
			$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($UserId);
			
			$arrEmployee = array();
			$arrtemp = array();
			if(count($arrDelegatedTeamLeaderId) > 0 )
			{
				foreach($arrDelegatedTeamLeaderId as $delegatesIds)
				{
					//echo $delegatesIds;
					$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
					$arrEmployee = $arrEmployee + $arrtemp;
				}
			}
			if(count($arrDelegatedManagerId) > 0 )
			{
				foreach($arrDelegatedManagerId as $delegatesManagerIds)
				{
					//echo $delegatesIds;
					$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
					$arrEmployee = $arrEmployee + $arrtemp;
				}
			}
			
			$temp1 = $objEmployee->fnGetAllemployees($UserId);
			$arrEmployee = $arrEmployee + $temp1;

			$arrEmployee[] = 0;
			
			if(count($arrEmployee) > 0)
			{
				$arrEmployee = array_filter($arrEmployee,'strlen');
			}
			
			$ids = "0";
			if(count($arrEmployee) > 0)
			{
				$ids = implode(',',$arrEmployee);
			}
			
			/*$sSQL = "select count(l.id) as pending_leave_count from pms_leave_form l INNER JOIN pms_employee e ON e.id = l.employee_id where l.isactive = '0' and e.status = '0' and ((DATE_FORMAT(l.start_date,'%Y-%m-%d') >= '$current_date' and DATE_FORMAT(l.end_date,'%Y-%m-%d') >= '$current_date') or (DATE_FORMAT(l.start_date,'%Y-%m-%d') >= '$prevdate' and DATE_FORMAT(l.end_date,'%Y-%m-%d') >= '$prevdate' and l.isemergency = '1')) and ((l.teamleader_id='".mysql_real_escape_string($UserId)."' and (l.status='0' and l.delegate_status in (0,null) and l.status_manager = '0' and l.manager_delegate_status in (0,null))) or (l.manager_id='".mysql_real_escape_string($UserId)."' and (l.status_manager = '0' and l.status != '2' and l.delegate_status != '2' and l.manager_delegate_status in (0,null))) or ((l.deligateTeamLeaderId='".mysql_real_escape_string($UserId)."' or (l.employee_id in ($ids) and '".mysql_real_escape_string($UserId)."' = (select delegate from pms_leave_form where employee_id = l.teamleader_id and '$current_date' between DATE_FORMAT(`start_date`,'%Y-%m-%d') and DATE_FORMAT(`end_date`,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1')) ORDER BY `id` LIMIT 0,1))) and (l.delegate_status in (0,null) and l.status ='0' and l.status_manager = '0' and l.manager_delegate_status in (0,null))) or ((l.deligateManagerId='".mysql_real_escape_string($UserId)."' or (l.employee_id in ($ids) and '".mysql_real_escape_string($UserId)."' = (select delegate from pms_leave_form where employee_id = l.manager_id and '$current_date' between DATE_FORMAT(`start_date`,'%Y-%m-%d') and DATE_FORMAT(`end_date`,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1')) ORDER BY `id` LIMIT 0,1))) and l.manager_delegate_status in (0,null) and l.status != '2' and l.delegate_status != '2' and l.status_manager = '0')) and l.isadminadded in (0,null)";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$pending_leave_count = $this->f("pending_leave_count");
				}
			}*/
			
			
			//$arrLeaveFormValues = array();
			$current_date = date('Y-m-d');
			//$query = "SELECT employee.*,leaves.*,head.name as headname,leaves.id as leaves_id,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS start_date,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') AS end_date, if(isemergency=1,'Yes','No') as isemergency FROM `pms_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id LEFT JOIN `pms_employee` AS head ON employee.teamleader = head.id WHERE employee_id IN($ids) and `isactive` = '0'";
			//echo $query = "SELECT employee.*,leaves.*,head.name as headname,leaves.id as leaves_id,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS start_date,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') AS end_date, if(isemergency=1,'Yes','No') as isemergency FROM `pms_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id LEFT JOIN `pms_employee` AS head ON employee.teamleader = head.id WHERE employee_id IN($ids) and `isactive` = '0' and (DATE_FORMAT(leaves.`start_date`,'%Y-%m-%d') >= '$current_date' and DATE_FORMAT(leaves.`end_date`,'%Y-%m-%d') >= '$current_date') and ((employee.designation IN(7,13,6) and (leaves.`status_manager` = '0' or (leaves.`deligateManagerId` != '0' && leaves.`status_manager` = '0'))) or (employee.designation NOT IN (7,13,6) and (leaves.`status`= '0' or leaves.`status_manager` = '0' or (leaves.`deligateTeamLeaderId` != '0' and leaves.`delegate_status` = '0') or (leaves.`deligateManagerId` != '0' && leaves.`status_manager` = '0')))) and employee.status = '0'";


			//$query = "SELECT employee.*,leaves.*,head.name as headname,leaves.id as leaves_id,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS start_date,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') AS end_date, if(isemergency=1,'Yes','No') as isemergency FROM `pms_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id LEFT JOIN `pms_employee` AS head ON employee.teamleader = head.id WHERE employee_id IN($ids) and `isactive` = '0' and (DATE_FORMAT(leaves.`start_date`,'%Y-%m-%d') >= '$current_date' and DATE_FORMAT(leaves.`end_date`,'%Y-%m-%d') >= '$current_date') and ((employee.designation IN(7,13,6) and (leaves.`status_manager` = '0' or (leaves.`deligateManagerId` != '0' and leaves.`status_manager` = '0' and leaves.`manager_delegate_status` = '0'))) or (employee.designation NOT IN (7,13,6) and (leaves.`status`= '0' or leaves.`status_manager` = '0' or (leaves.`deligateTeamLeaderId` != '0' and leaves.`delegate_status` = '0' and leaves.`manager_delegate_status` = '0') or (leaves.`deligateManagerId` != '0' && leaves.`status_manager` = '0')))) and (leaves.`status_manager` != '2' or (leaves.`deligateManagerId` != '0' and  leaves.`manager_delegate_status` != '2' and leaves.`status_manager` = '0')) and employee.status = '0'";

			$prevdate = Date('Y-m-d', strtotime('-1 day'));

			$query = "select e.*,l.*,e.id as eid,DATE_FORMAT(l.start_date,'%Y-%m-%d') as leave_start_date,DATE_FORMAT(l.end_date,'%Y-%m-%d') as leave_end_date,l.id as leaves_id,DATE_FORMAT(l.start_date,'%d-%m-%Y') AS start_date,DATE_FORMAT(l.end_date,'%d-%m-%Y') AS end_date, if(isemergency=1,'Yes','No') as isemergency from pms_leave_form l INNER JOIN pms_employee e ON e.id = l.employee_id where l.isactive = '0' and e.status = '0' and ((DATE_FORMAT(l.start_date,'%Y-%m-%d') >= '$current_date' and DATE_FORMAT(l.end_date,'%Y-%m-%d') >= '$current_date') or (DATE_FORMAT(l.start_date,'%Y-%m-%d') >= '$prevdate' and DATE_FORMAT(l.end_date,'%Y-%m-%d') >= '$prevdate' and l.isemergency = '1')) and ((l.teamleader_id='".mysql_real_escape_string($UserId)."' and (l.status='0' and l.delegate_status in (0,null) and l.status_manager = '0' and l.manager_delegate_status in (0,null))) or (l.manager_id='".mysql_real_escape_string($UserId)."' and (l.status_manager = '0' and l.status != '2' and l.delegate_status != '2' and l.manager_delegate_status in (0,null))) or ((l.deligateTeamLeaderId='".mysql_real_escape_string($UserId)."' or (l.employee_id in ($ids) and '".mysql_real_escape_string($UserId)."' = (select delegate from pms_leave_form where employee_id = l.teamleader_id and '$current_date' between DATE_FORMAT(`start_date`,'%Y-%m-%d') and DATE_FORMAT(`end_date`,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1')) ORDER BY `id` LIMIT 0,1))) and (l.delegate_status in (0,null) and l.status ='0' and l.status_manager = '0' and l.manager_delegate_status in (0,null))) or ((l.deligateManagerId='".mysql_real_escape_string($UserId)."' or (l.employee_id in ($ids) and '".mysql_real_escape_string($UserId)."' = (select delegate from pms_leave_form where employee_id = l.manager_id and '$current_date' between DATE_FORMAT(`start_date`,'%Y-%m-%d') and DATE_FORMAT(`end_date`,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1')) ORDER BY `id` LIMIT 0,1))) and l.manager_delegate_status in (0,null) and l.status != '2' and l.delegate_status != '2' and l.status_manager = '0')) and l.isadminadded in (0,null)";

			$this->query($query);

			include_once('includes/class.shifts.php');
			include_once('includes/class.attendance.php');
			include_once('includes/class.employee.php');

			$objShifts = new shifts();
			$objAttendance = new attendance();
			$objEmployee = new employee();

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					//$arrLeaveFormValues[] = $this->fetchrow();
					/*echo '<br>leave_start_date'.$this->f("leave_start_date");
					echo '<br>leave_end_date'.$this->f("leave_end_date");
					echo '<br>employee_id'.$this->f("eid");*/
					if($this->f("leave_start_date") == $prevdate && $this->f("leave_end_date") == $prevdate)
					{
						//echo 'here';
						$starttime = "00:00";
						$endtime = "00:00";

						/* Get data from attendance */
						$ShiftId = $objAttendance->fnGetAttendanceShiftByUserAndDate($this->f("eid"), $this->f("leave_start_date"));

						if($ShiftId == "" || $ShiftId == "0")
						{
							/* Get the default shift from the employee */
							$ShiftId = $objEmployee->fnGetEmployeeShiftById($this->f("eid"));
						}

						$arrShift = $objShifts->fnGetShiftById($ShiftId);
						/*echo '<pre>'; print_r($arrShift);*/
						if(count($arrShift) > 0)
						{
							$starttime = $arrShift["starttime"];
							$endtime = $arrShift["endtime"];
						}
						/*echo '<br>starttime'.$starttime;
						echo '<br>endtime'.$endtime;*/

						if($endtime <= $starttime)
						{
							$pending_leave_count++;
						}
					}
					else
					{
						$pending_leave_count++;
					}
				}
			}
			
			return $pending_leave_count;
		}

		function fnCheckPendingHalfdayLeaveRequestByUserId($UserId)
		{
			/*
			 * Check if the leave request is pending by the user 
			 * 
			 * Count only those halfday leaves of previous date for which the shift of the user fall in two 
			 * shifts and all the leaves from current date
			 * 
			 * */

			include_once("class.employee.php");
			$objEmployee = new employee();

			$current_date = date('Y-m-d');
			$prevdate = Date('Y-m-d', strtotime('-1 day'));
			$pending_halfday_leave_count = 0;

			/* Get delegated teamleader id */
			$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($UserId);

			/* Get Delegated Manager id */
			$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($UserId);
			
			$arrEmployee = array();
			$arrtemp = array();
			if(count($arrDelegatedTeamLeaderId) > 0 )
			{
				foreach($arrDelegatedTeamLeaderId as $delegatesIds)
				{
					//echo $delegatesIds;
					$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
					$arrEmployee = $arrEmployee + $arrtemp;
				}
			}
			if(count($arrDelegatedManagerId) > 0 )
			{
				foreach($arrDelegatedManagerId as $delegatesManagerIds)
				{
					//echo $delegatesIds;
					$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
					$arrEmployee = $arrEmployee + $arrtemp;
				}
			}
			
			$arrEmployee[] = 0;
			
			if(count($arrEmployee) > 0)
			{
				$arrEmployee = array_filter($arrEmployee,'strlen');
			}
			
			$ids = "0";
			if(count($arrEmployee) > 0)
			{
				$ids = implode(',',$arrEmployee);
			}

			$sSQL = "select hl.id, date_format(start_date,'%Y-%m-%d') as st_date, e.id as eid, e.shiftid from pms_half_leave_form hl INNER JOIN pms_employee e ON e.id = hl.employee_id where hl.isactive = '0' and e.status = '0' and DATE_FORMAT(hl.start_date,'%Y-%m-%d') >= '$prevdate' and ((hl.teamleader_id='".mysql_real_escape_string($UserId)."' and (hl.status='0' and hl.delegate_status in (0,null) and hl.status_manager = '0' and hl.manager_delegate_status in (0,null))) or (hl.manager_id='".mysql_real_escape_string($UserId)."' and (hl.status_manager = '0' and hl.status != '2' and hl.delegate_status != '2' and hl.manager_delegate_status in (0,null))) or ((hl.deligateTeamLeaderId='".mysql_real_escape_string($UserId)."' or (hl.employee_id in ($ids) and '".mysql_real_escape_string($UserId)."' = (select delegate from pms_leave_form where employee_id = hl.teamleader_id and '$current_date' between DATE_FORMAT(`start_date`,'%Y-%m-%d') and DATE_FORMAT(`end_date`,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1')) ORDER BY `id` LIMIT 0,1))) and (hl.delegate_status in (0,null) and hl.status ='0' and hl.status_manager = '0' and hl.manager_delegate_status in (0,null))) or ((hl.deligateManagerId='".mysql_real_escape_string($UserId)."' or (hl.employee_id in ($ids) and '".mysql_real_escape_string($UserId)."' = (select delegate from pms_leave_form where employee_id = hl.manager_id and '$current_date' between DATE_FORMAT(`start_date`,'%Y-%m-%d') and DATE_FORMAT(`end_date`,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1')) ORDER BY `id` LIMIT 0,1))) and hl.manager_delegate_status in (0,null) and hl.status != '2' and hl.delegate_status != '2' and hl.status_manager = '0')) and hl.isadminadded in (0,null)";
			
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				include_once('includes/class.shifts.php');
				include_once('includes/class.attendance.php');
			
				$objShifts = new shifts();
				$objAttendance = new attendance();
				
				while($this->next_record())
				{
					if($this->f("st_date") == $prevdate)
					{
						$starttime = "00:00";
						$endtime = "00:00";
						
						/* Get data from attendance */
						$ShiftId = $objAttendance->fnGetAttendanceShiftByUserAndDate($this->f("eid"), $this->f("st_date"));

						if($ShiftId == "" || $ShiftId == "0")
						{
							/* Get data from attendance */
							$ShiftId = $this->f("shiftid");
						}
						
						$arrShift = $objShifts->fnGetShiftById($ShiftId);
						
						if(count($arrShift) > 0)
						{
							$starttime = $arrShift["starttime"];
							$endtime = $arrShift["endtime"];
						}
						
						if($endtime <= $starttime)
						{
							$pending_halfday_leave_count++;
						}
					}
					else
						$pending_halfday_leave_count++;
				}
			}

			return $pending_halfday_leave_count;
		}
		
		function fnGetAllAdminLeave()
		{
			$arrAdminLeaves = array();
			
			$sSQL = "select l.id, l.nodays, date_format(l.start_date, '%d-%m-%Y') as startdate, date_format(l.end_date, '%d-%m-%Y') as enddate, e.name as employee_name, e1.name as reporting_head_name from pms_leave_form l INNER JOIN pms_employee e ON l.employee_id = e.id INNER JOIN pms_employee e1 ON e.teamleader = e1.id where l.isadminadded='1'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAdminLeaves[] = $this->fetchRow();
				}
			}
			
			return $arrAdminLeaves;
		}
		
		function fnInserAdminLeaveForm($arrLeaveForm)
		{
			/* Include files */
			include_once("class.employee.php");
			include_once("class.designation.php");

			/* Create objects */
			$objEmployee = new employee();
			$objDesignation = new designations();

			/* Get designation of the employee for which the leave is to be added */
			$arrLeaveUser = $objEmployee->fnGetEmployeeById($arrLeaveForm["employee_id"]);

			if(count($arrLeaveUser) > 0)
			{
				/* Fetch user designation */
				$DesignationId = $arrLeaveUser["designation"];

				/* Fetch details for the user designation */
				$arrDesignationInfo = $objDesignation->fnGetDesignationById($DesignationId);

				if(count($arrDesignationInfo) > 0)
				{
					/* Fetch reporting head hierarchy */
					$arrHeads = $objEmployee->fnGetReportHeadHierarchy($arrLeaveForm["employee_id"]);

					if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && isset($arrDesignationInfo["second_reporting_head"]) && trim($arrDesignationInfo["second_reporting_head"]) != "" && trim($arrDesignationInfo["second_reporting_head"]) != "0")
					{
						if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
						{
							$fstReportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];
							$arrLeaveForm['teamleader_id'] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
						}

						if(isset($arrHeads[$arrDesignationInfo["second_reporting_head"]]))
						{
							$secReportingHead = $arrHeads[$arrDesignationInfo["second_reporting_head"]];
							$arrLeaveForm['manager_id'] = $arrHeads[$arrDesignationInfo["second_reporting_head"]]["id"];
						}
					}
					else if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && trim($arrDesignationInfo["second_reporting_head"]) == "0")
					{
						if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
						{
							$fstReportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];

							$arrLeaveForm['manager_id'] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
							$arrLeaveForm['teamleader_id'] = 0;
						}
					}
				}
			}

			$maxDateToApply = date('Y-m-d', strtotime("+100 day"));

			if($arrLeaveForm['start_date'] > $maxDateToApply)
			{
				return -1;
			}

			$shiftCheck = $this->fnCheckUserShiftMovement($arrLeaveForm["employee_id"],$arrLeaveForm['start_date'],$arrLeaveForm['end_date']);

			$checkDeligateId = $this->fnCheckDeligate($arrLeaveForm['teamleader_id']);
			$checkDeligateManagerId = $this->fnCheckDeligate($arrLeaveForm['manager_id']);

			if(isset($checkDeligateId) && $checkDeligateId != '')
			{
				$delegateTeamleaderId = $checkDeligateId;
			}
			else
			{
				$delegateTeamleaderId = 0;
			}

			if(isset($checkDeligateManagerId) && $checkDeligateManagerId != '')
			{
				$delegateManagerId = $checkDeligateManagerId;
			}
			else
			{
				$delegateManagerId = 0;
			}

			if($shiftCheck != '')
			{
				return 0;
			}
			else
			{
				$count = '';
				/*check if leave already added */
				$query ="SELECT count( id ) AS count,status,status_manager,deligateManagerId,manager_delegate_status FROM pms_leave_form WHERE ('".$arrLeaveForm['start_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d') or '".$arrLeaveForm['end_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d')) and employee_id = '".$arrLeaveForm["employee_id"]."' and (((status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1))) and isactive='0') or (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1'))) and (status != 2 and delegate_status!=2 AND status_manager !=2 AND manager_delegate_status !=2)";

				$this->query($query);

				if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$count = $this->f("count");
						$status = $this->f("sta");
						$status_manager = $this->f("status_manager");
					}
				}
				if($count > 0)
				{
					fnRedirectUrl("admin_leave_form.php?info=exist");
				}
				else
				{
					/*$query = "SELECT count( id ) AS count,status,status_manager FROM pms_half_leave_form WHERE DATE_FORMAT(start_date,'%Y-%m-%d') between '".$arrLeaveForm['start_date']."' and '".$arrLeaveForm['end_date']."' and employee_id = '".$arrLeaveForm["employee_id"]."' and (((status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1))) and isactive='0') or (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1'))) and (status_manager='0' and (status_manager='0' and manager_delegate_status='0') and (status != 2 and (status =0 and  delegate_status!=2)))";

					//$query = "SELECT count( id ) AS count,status,status_manager FROM pms_half_leave_form WHERE '".$arrLeaveForm['start_date']."' = DATE_FORMAT(start_date,'%Y-%m-%d') and employee_id = '".$_SESSION['id']."' and status_manager IN(0,1)";
					$this->query($query);

					if($this->num_rows() > 0)
					{
						if($this->next_record())
						{
							$count = $this->f("count");
							$status = $this->f("sta");
							$status_manager = $this->f("status_manager");
						}
					}
					if($count > 0)
					{
						fnRedirectUrl("admin_leave_form.php?info=exist");
					}
					else
					{*/
						/* Check if approved LWP added by admin */
						$sSQL = "select * from pms_approved_lwp where date_format(lwp_date, '%Y-%m-%d') between '".mysql_real_escape_string($arrLeaveForm['start_date'])."' and '".mysql_real_escape_string($arrLeaveForm['end_date'])."' and user_id='".mysql_real_escape_string($arrLeaveForm["employee_id"])."' and approval_status in (0,1)";
						$this->query($sSQL);
						if($this->num_rows() > 0)
						{
							return -2;
						}
						else
						{
							$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrLeaveForm["employee_id"])."' and date_format(date, '%Y-%m-%d') between '".mysql_real_escape_string($arrLeaveForm['start_date'])."' and '".mysql_real_escape_string($arrLeaveForm['end_date'])."' and (in_time!='00:00:00' or out_time!='00:00:00')";
							$this->query($sSQL);
							if($this->num_rows() > 0)
								return -3;
							else
							{
								$arrLeaveForm['date'] = date('Y-m-d H:i:s');
								/*$arrLeaveForm["status_manager"] = 1;*/
								$arrLeaveForm["isadminadded"] = 1;
								
								/* Add code for TL & Manager */
								/*$arrLeaveForm["tlapprovalcode"] = leaveform_uid();
								$arrLeaveForm["managerapprovalcode"] = leaveform_uid();*/
								$arrLeaveForm["deligateTeamLeaderId"] = $delegateTeamleaderId;
								/*if($arrLeaveForm["deligateTeamLeaderId"] != 0)
								{
									$arrLeaveForm["delegatedtlapprovalcode"] = leaveform_uid();
								}*/

								$arrLeaveForm["deligateManagerId"] = $delegateManagerId;
								/*if($arrLeaveForm["deligateManagerId"] != 0)
								{
									$arrLeaveForm["delegatedmanagerapprovalcode"] = leaveform_uid();
								}*/

								$lastInsertId = $this->insertArray('pms_leave_form',$arrLeaveForm);
								
								/*include_once('class.attendance.php');
								$objAttendance = new attendance();

								$LeaveInfo = $this->fnLeaveInfoById($lastInsertId);
								$next_monday_date = date('Y-m-d', strtotime('next monday'));

								//echo '<pre>'; print_r($LeaveInfo);
								
								if($LeaveInfo["status_manager"] == "1" || ($LeaveInfo["status_manager"] == "0" && $LeaveInfo["manager_delegate_status"] == "1"))
								{
									$arrInfo["user_id"] = $LeaveInfo["employee_id"];
									$arrInfo["start_dt"] = $LeaveInfo["start_dt"];
									if($arrInfo["start_dt"] >= $next_monday_date)
									{
										$arrInfo["leave_id"] = $this->fnGetLeaveTypeIdByTitle("PPL");
									}
									else
									{
										$arrInfo["leave_id"] = $this->fnGetLeaveTypeIdByTitle("UPL");
									}
									$arrInfo["end_dt"] = $LeaveInfo["end_dt"];
									$objAttendance->fnInsertRosterAttendance($arrInfo);
								}*/
								
								return $lastInsertId;
							}
						}
					/*}*/
				}
			}
		}
		
		function fnGetAllAdminHalfLeave()
		{
			$arrAdminHalfLeaves = array();
			
			$sSQL = "select h.id, date_format(h.start_date, '%d-%m-%Y') as startdate, e.name as employee_name, e1.name as reporting_head_name, h.halfdayfor from pms_half_leave_form h INNER JOIN pms_employee e ON h.employee_id = e.id INNER JOIN pms_employee e1 ON e.teamleader = e1.id where h.isadminadded='1'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAdminHalfLeaves[] = $this->fetchRow();
				}
			}
			
			return $arrAdminHalfLeaves;
		}
		
		function fnInserAdminHalfLeaveForm($arrLeaveForm)
		{
			/* Include files */
			include_once("class.employee.php");
			include_once("class.designation.php");

			/* Create objects */
			$objEmployee = new employee();
			$objDesignation = new designations();

			/* Get designation of the employee for which the leave is to be added */
			$arrLeaveUser = $objEmployee->fnGetEmployeeById($arrLeaveForm["employee_id"]);

			if(count($arrLeaveUser) > 0)
			{
				/* Fetch user designation */
				$DesignationId = $arrLeaveUser["designation"];

				/* Fetch details for the user designation */
				$arrDesignationInfo = $objDesignation->fnGetDesignationById($DesignationId);

				if(count($arrDesignationInfo) > 0)
				{
					/* Fetch reporting head hierarchy */
					$arrHeads = $objEmployee->fnGetReportHeadHierarchy($arrLeaveForm["employee_id"]);

					if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && isset($arrDesignationInfo["second_reporting_head"]) && trim($arrDesignationInfo["second_reporting_head"]) != "" && trim($arrDesignationInfo["second_reporting_head"]) != "0")
					{
						if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
						{
							$fstReportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];
							$arrLeaveForm['teamleader_id'] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
						}

						if(isset($arrHeads[$arrDesignationInfo["second_reporting_head"]]))
						{
							$secReportingHead = $arrHeads[$arrDesignationInfo["second_reporting_head"]];
							$arrLeaveForm['manager_id'] = $arrHeads[$arrDesignationInfo["second_reporting_head"]]["id"];
						}
					}
					else if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && trim($arrDesignationInfo["second_reporting_head"]) == "0")
					{
						if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
						{
							$fstReportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];

							$arrLeaveForm['manager_id'] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
							$arrLeaveForm['teamleader_id'] = 0;
						}
					}
				}
			}

			$maxDateToApply = date('Y-m-d', strtotime("+100 day"));

			if($arrLeaveForm['start_date'] > $maxDateToApply)
			{
				return -1;
			}

			$shiftCheck = $this->fnCheckUserShiftMovement($arrLeaveForm["employee_id"],$arrLeaveForm['start_date'],$arrLeaveForm['start_date']);

			$checkDeligateId = $this->fnCheckDeligate($arrLeaveForm['teamleader_id']);
			$checkDeligateManagerId = $this->fnCheckDeligate($arrLeaveForm['manager_id']);

			if(isset($checkDeligateId) && $checkDeligateId != '')
			{
				$delegateTeamleaderId = $checkDeligateId;
			}
			else
			{
				$delegateTeamleaderId = 0;
			}

			if(isset($checkDeligateManagerId) && $checkDeligateManagerId != '')
			{
				$delegateManagerId = $checkDeligateManagerId;
			}
			else
			{
				$delegateManagerId = 0;
			}

			if($shiftCheck != '')
			{
				return 0;
			}
			else
			{
				//$count = '';
				/*check if leave already added */
				/*$query ="SELECT count( id ) AS count,status,status_manager,deligateManagerId,manager_delegate_status FROM pms_leave_form WHERE DATE_FORMAT(start_date,'%Y-%m-%d') = '".$arrLeaveForm['start_date']."' and employee_id = '".$arrLeaveForm["employee_id"]."' and (((status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1))) and isactive='0') or (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1'))) and (status != 2 and delegate_status!=2 AND status_manager !=2 AND manager_delegate_status !=2)";

				$this->query($query);

				if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$count = $this->f("count");
						$status = $this->f("sta");
						$status_manager = $this->f("status_manager");
					}
				}
				if($count > 0)
				{
					fnRedirectUrl("admin_half_leave_form.php?info=exist");
				}
				else
				{*/
					$query = "SELECT count( id ) AS count,status,status_manager FROM pms_half_leave_form WHERE DATE_FORMAT(start_date,'%Y-%m-%d') = '".$arrLeaveForm['start_date']."' and employee_id = '".$arrLeaveForm["employee_id"]."' and (((status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1))) and isactive='0') or (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1'))) and (status_manager='0' and (status_manager='0' and manager_delegate_status='0') and (status != 2 and (status =0 and  delegate_status!=2)))";
					$this->query($query);

					if($this->num_rows() > 0)
					{
						if($this->next_record())
						{
							$count = $this->f("count");
							$status = $this->f("sta");
							$status_manager = $this->f("status_manager");
						}
					}
					if($count > 0)
					{
						fnRedirectUrl("admin_half_leave_form.php?info=exist");
					}
					else
					{
						/* Check if approved LWP added by admin */
						$sSQL = "select * from pms_approved_lwp where date_format(lwp_date, '%Y-%m-%d') = '".mysql_real_escape_string($arrLeaveForm['start_date'])."' and user_id='".mysql_real_escape_string($arrLeaveForm["employee_id"])."' and approval_status in (0,1)";
						$this->query($sSQL);
						if($this->num_rows() > 0)
						{
							return -2;
						}
						else
						{
							$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrLeaveForm["employee_id"])."' and date_format(date, '%Y-%m-%d') = '".mysql_real_escape_string($arrLeaveForm['start_date'])."' and (in_time!='00:00:00' or out_time!='00:00:00')";
							$this->query($sSQL);
							if($this->num_rows() > 0)
							{
								if($this->next_record())
								{
							
									$total_working_hours = $this->f("total_working_hours");
									$leave_id = $this->f("leave_id");
									
							
								}
								else
								{
								$total_working_hours ='00:00';
								}
							
								
									
							}
							$sSQL = "select designation from pms_employee where id='".mysql_real_escape_string($arrLeaveForm["employee_id"])."'";
							$this->query($sSQL);
							if($this->num_rows() > 0)
							{
							$designatioId = $this->f("designation");
							$arrDesignationInfo = $objDesignation->fnGetDesignationById($DesignationId);
							 $minimumWorkHours = $arrDesignationInfo['halfday_minimum_working_hour'];
							}
							else
							{
							 $minimumWorkHours = '03:45';
							}
							
							
							if( strtotime($total_working_hours)< $minimumWorkHours )
							{
								
										return -5;
 										//do some work
							}
							else
							{
								$arrLeaveForm['date'] = date('Y-m-d H:i:s');
								$arrLeaveForm["isadminadded"] = 1;
								
								/* Add code for TL & Manager */
								$arrLeaveForm["deligateTeamLeaderId"] = $delegateTeamleaderId;

								$arrLeaveForm["deligateManagerId"] = $delegateManagerId;

								$lastInsertId = $this->insertArray('pms_half_leave_form',$arrLeaveForm);
								
								return $lastInsertId;
							}
						}
					}
			}
		}
		
		
		function fnGetAllAdminLeaveRequests()
		{
			/* Fetch all pending leave request added by admin */
			$arrLeaveRequest = array();

			$sSQL = "select l.id, l.nodays, date_format(l.start_date, '%d-%m-%Y') as startdate, date_format(l.end_date, '%d-%m-%Y') as enddate, e.name as employee_name, e1.name as reporting_head_name from pms_leave_form l INNER JOIN pms_employee e ON l.employee_id = e.id INNER JOIN pms_employee e1 ON e.teamleader = e1.id where l.isadminadded='1' and l.status_manager = '0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrLeaveRequest[] = $this->fetchRow();
				}
			}

			return $arrLeaveRequest;
		}
		
		function fnGetAdminLeaveRequestById($leaveId)
		{
			$arrLeaveRequest = array();
			
			$sSQL = "select l.*, date_format(l.start_date, '%d-%m-%Y') as startdate, date_format(l.end_date, '%d-%m-%Y') as enddate, date_format(l.start_date, '%Y-%m-%d') as start_date, date_format(l.end_date, '%Y-%m-%d') as end_date, e.name as employee_name, e1.name as reporting_head_name from pms_leave_form l INNER JOIN pms_employee e ON l.employee_id = e.id INNER JOIN pms_employee e1 ON e.teamleader = e1.id where l.isadminadded='1' and l.status_manager = '0' and l.id = '".mysql_real_escape_string($leaveId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrLeaveRequest = $this->fetchRow();
				}
			}
			
			return $arrLeaveRequest;
		}
		
		function fnApproveAdminLeaveRequest($leaveRequest)
		{
			if(isset($leaveRequest["id"]) && trim($leaveRequest["id"]) != "")
			{
				$LeaveRequestInfo = $this->fnGetAdminLeaveRequestById($leaveRequest["id"]);
				
				if(count($LeaveRequestInfo) > 0)
				{
					/* update leave status */
					$updateInfo["id"] = $leaveRequest["id"];
					$updateInfo["status_manager"] = $leaveRequest["status_manager"];
					$updateInfo["comment_manager"] = $leaveRequest["comment_manager"];
					$updateInfo["approved_date_manager"] = Date('Y-m-d H:i:s');
					
					$this->updateArray('pms_leave_form',$updateInfo);
					
					/* Update attendance entry */
					include_once('class.attendance.php');
					include_once('class.employee.php');

					$objAttendance = new attendance();
					$objEmployee = new employee();

					$next_monday_date = date('Y-m-d', strtotime('next monday'));

					if($updateInfo["status_manager"] == "1")
					{
						/* Approve */
						$arrInfo["user_id"] = $LeaveRequestInfo["employee_id"];
						$arrInfo["start_dt"] = $LeaveRequestInfo["start_date"];
						if($arrInfo["start_dt"] >= $next_monday_date)
						{
							$arrInfo["leave_id"] = $this->fnGetLeaveTypeIdByTitle("PPL");
						}
						else
						{
							$arrInfo["leave_id"] = $this->fnGetLeaveTypeIdByTitle("UPL");
						}
						$arrInfo["end_dt"] = $LeaveRequestInfo["end_date"];
						
						$objAttendance->fnInsertRosterAttendance($arrInfo);
					}
					
					/* Send mail to user */
					$arrStatus = array("1"=>"Approved", "2"=>"Rejected");
					$leaveStatus = $arrStatus[$updateInfo["status_manager"]];
					
					$Subject = 'Admin Leave application - '.$leaveStatus;
					
					$curEmployee = $objEmployee->fnGetEmployeeDetailById($LeaveRequestInfo['employee_id']);
					
					$table = "Admin has ".$leaveStatus." a leave for ".$curEmployee["name"]." from ".$LeaveRequestInfo["startdate"]." to ".$LeaveRequestInfo["enddate"]." for ".$LeaveRequestInfo["nodays"]." day(s).<br/>Reason for adding leave is : ".$LeaveRequestInfo['reason'];
					
					if(trim($curEmployee["email"]) != "")
					{
						$userMailContent = "Dear ".$curEmployee['name'].", <br /><br />"."Admin has ".$leaveStatus." a leave for you from ".$LeaveRequestInfo["startdate"]." to ".$LeaveRequestInfo["enddate"]." for ".$LeaveRequestInfo['nodays']." day(s).<br/>Reason for adding leave is : ".$LeaveRequestInfo['reason']."<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($curEmployee['email'],$Subject,$userMailContent);
					}
					
					/* First Reporting Head */
					if(isset($LeaveRequestInfo['teamleader_id']) && trim($LeaveRequestInfo['teamleader_id']) != ""  && trim($LeaveRequestInfo['teamleader_id']) != "0")
					{
						$arrTeamLeader = $objEmployee->fnGetEmployeeById($LeaveRequestInfo['teamleader_id']);

						$content = "Dear ".$arrTeamLeader['name'].", <br /><br />".$table."<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($arrTeamLeader['email'],$Subject,$content);
					}

					/* Second Reporting Head */
					if(isset($LeaveRequestInfo['manager_id']) && trim($LeaveRequestInfo['manager_id']) != ""  && trim($LeaveRequestInfo['manager_id']) != "0")
					{
						$arrManager = $objEmployee->fnGetEmployeeById($LeaveRequestInfo['manager_id']);

						$content = "Dear ".$arrManager['name'].", <br /><br />".$table."<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($arrManager['email'],$Subject,$content);
					}

					if(isset($LeaveRequestInfo['deligateTeamLeaderId']) && $LeaveRequestInfo['deligateTeamLeaderId'] != '' && $LeaveRequestInfo['deligateTeamLeaderId'] != '0')
					{
						$deligatedTeamleaderDetails = $objEmployee->fnGetEmployeeById($LeaveRequestInfo['deligateTeamLeaderId']);

						$content = "Dear ".$deligatedTeamleaderDetails['name'].", <br /><br />".$table."<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($deligatedTeamleaderDetails['email'],$Subject,$content);
					}

					if(isset($LeaveRequestInfo['deligateManagerId']) && $LeaveRequestInfo['deligateManagerId'] != '' && $LeaveRequestInfo['deligateManagerId'] != '0')
					{
						$deligatedManagerDetails = $objEmployee->fnGetEmployeeById($LeaveRequestInfo['deligateManagerId']);

						$content = "Dear ".$deligatedManagerDetails['name'].", <br /><br />".$table."<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($deligatedManagerDetails['email'],$Subject,$content);
					}

					return $updateInfo["status_manager"];
				}
				else
					return 0;
			}
			else
				return 0;
		}
	
		function fnGetAllAdminHalfLeaveRequests()
		{
			$arrAdminHalfLeaves = array();
			
			$sSQL = "select h.id, date_format(h.start_date, '%d-%m-%Y') as startdate, e.name as employee_name, e1.name as reporting_head_name, h.halfdayfor from pms_half_leave_form h INNER JOIN pms_employee e ON h.employee_id = e.id INNER JOIN pms_employee e1 ON e.teamleader = e1.id where h.isadminadded='1' and h.status_manager = '0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAdminHalfLeaves[] = $this->fetchRow();
				}
			}
			
			return $arrAdminHalfLeaves;
		}
		
		function fnGetAdminHalfLeaveRequestById($halfLeaveId)
		{
			$arrAdminHalfLeaves = array();
			
			$sSQL = "select h.*, date_format(h.start_date, '%d-%m-%Y') as startdate, date_format(h.start_date, '%Y-%m-%d') as start_date, e.name as employee_name, e1.name as reporting_head_name, h.halfdayfor from pms_half_leave_form h INNER JOIN pms_employee e ON h.employee_id = e.id INNER JOIN pms_employee e1 ON e.teamleader = e1.id where h.isadminadded='1' and h.status_manager = '0' and h.id = '".mysql_real_escape_string($halfLeaveId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrAdminHalfLeaves = $this->fetchRow();
				}
			}
			
			return $arrAdminHalfLeaves;
		}
		
		function fnApproveAdminHalfLeaveRequest($leaveRequest)
		{
			if(isset($leaveRequest["id"]) && trim($leaveRequest["id"]) != "")
			{
				$LeaveRequestInfo = $this->fnGetAdminHalfLeaveRequestById($leaveRequest["id"]);

				if(count($LeaveRequestInfo) > 0)
				{
					/* update leave status */
					$updateInfo["id"] = $leaveRequest["id"];
					$updateInfo["status_manager"] = $leaveRequest["status_manager"];
					$updateInfo["comment_manager"] = $leaveRequest["comment_manager"];
					$updateInfo["approved_date_manager"] = Date('Y-m-d H:i:s');

					$this->updateArray('pms_half_leave_form',$updateInfo);

					/* Update attendance entry */
					include_once('class.attendance.php');
					include_once('class.employee.php');

					$objAttendance = new attendance();
					$objEmployee = new employee();

					$next_monday_date = date('Y-m-d', strtotime('next monday'));

					if($updateInfo["status_manager"] == "1")
					{
						/* Approve */
						$arrInfo["user_id"] = $LeaveRequestInfo["employee_id"];
						$arrInfo["date"] = $LeaveRequestInfo["start_date"];
						if($arrInfo["date"] >= $next_monday_date)
						{
							$arrInfo["leave_id"] = $this->fnGetLeaveTypeIdByTitle("PHL");
						}
						else
						{
							$arrInfo["leave_id"] = $this->fnGetLeaveTypeIdByTitle("UHL");
						}
						
						$objAttendance->fnInsertRosterAttendance($arrInfo);
					}
					
					/* Send mail to user */
					$arrStatus = array("1"=>"Approved", "2"=>"Rejected");
					$leaveStatus = $arrStatus[$updateInfo["status_manager"]];
					
					$Subject = 'Admin Leave application - '.$leaveStatus;
					
					$curEmployee = $objEmployee->fnGetEmployeeDetailById($LeaveRequestInfo['employee_id']);
					
					$arrHalfDayFor = array("1"=>"First Half", "2"=>"Second Half");
					
					$table = "Admin has ".$leaveStatus." a half day leave for ".$curEmployee["name"]." for date ".$LeaveRequestInfo["startdate"]." for ".$arrHalfDayFor[$LeaveRequestInfo['halfdayfor']].".<br/>Reason for adding leave is : ".$LeaveRequestInfo['reason'];
					
					if(trim($curEmployee["email"]) != "")
					{
						$userMailContent = "Dear ".$curEmployee['name'].", <br /><br />"."Admin has ".$leaveStatus." a half day leave for you for date ".$LeaveRequestInfo["startdate"]."  for ".$arrHalfDayFor[$LeaveRequestInfo['halfdayfor']].".<br/>Reason for adding leave is : ".$LeaveRequestInfo['reason']."<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($curEmployee['email'],$Subject,$userMailContent);
					}
					
					/* First Reporting Head */
					if(isset($LeaveRequestInfo['teamleader_id']) && trim($LeaveRequestInfo['teamleader_id']) != ""  && trim($LeaveRequestInfo['teamleader_id']) != "0")
					{
						$arrTeamLeader = $objEmployee->fnGetEmployeeById($LeaveRequestInfo['teamleader_id']);

						$content = "Dear ".$arrTeamLeader['name'].", <br /><br />".$table."<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($arrTeamLeader['email'],$Subject,$content);
					}

					/* Second Reporting Head */
					if(isset($LeaveRequestInfo['manager_id']) && trim($LeaveRequestInfo['manager_id']) != ""  && trim($LeaveRequestInfo['manager_id']) != "0")
					{
						$arrManager = $objEmployee->fnGetEmployeeById($LeaveRequestInfo['manager_id']);

						$content = "Dear ".$arrManager['name'].", <br /><br />".$table."<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($arrManager['email'],$Subject,$content);
					}

					if(isset($LeaveRequestInfo['deligateTeamLeaderId']) && $LeaveRequestInfo['deligateTeamLeaderId'] != '' && $LeaveRequestInfo['deligateTeamLeaderId'] != '0')
					{
						$deligatedTeamleaderDetails = $objEmployee->fnGetEmployeeById($LeaveRequestInfo['deligateTeamLeaderId']);

						$content = "Dear ".$deligatedTeamleaderDetails['name'].", <br /><br />".$table."<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($deligatedTeamleaderDetails['email'],$Subject,$content);
					}

					if(isset($LeaveRequestInfo['deligateManagerId']) && $LeaveRequestInfo['deligateManagerId'] != '' && $LeaveRequestInfo['deligateManagerId'] != '0')
					{
						$deligatedManagerDetails = $objEmployee->fnGetEmployeeById($LeaveRequestInfo['deligateManagerId']);

						$content = "Dear ".$deligatedManagerDetails['name'].", <br /><br />".$table."<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($deligatedManagerDetails['email'],$Subject,$content);
					}

					return $updateInfo["status_manager"];
				}
				else
					return 0;
			}
			else
				return 0;
		}

		function fnGetAllAdminApprovedLWP()
		{
			$arrApprovedLwp = array();
			
			$sSQL = "select l.*, e.name as employee_name, e1.name as reporting_head_name, t.title as leave_title, date_format(lwp_date, '%d-%m-%Y') as lwp_date from pms_approved_lwp l INNER JOIN pms_employee e ON e.id = l.user_id INNER JOIN pms_employee e1 ON e.teamleader = e1.id INNER JOIN pms_leave_type t ON t.id = l.leave_id";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrApprovedLwp[] = $this->fetchRow();
				}
			}
			
			return $arrApprovedLwp;
		}
		
		function fnGetLwpAndSpecialLeaveType()
		{
			$arrLWP = array();
			
			$sSQL = "select id, title from pms_leave_type where id in (6,7,17)";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrLWP[] = $this->fetchRow();
				}
			}
			
			return $arrLWP;
		}
		
		function fnInserAdminApprovedLwpForm($arrLeaveForm)
		{
			$db = new DB_Sql();
			
			/* Include files */
			include_once("class.employee.php");
			include_once("class.designation.php");

			/* Create objects */
			$objEmployee = new employee();
			$objDesignation = new designations();

			/* Get designation of the employee for which the leave is to be added */
			$arrLeaveUser = $objEmployee->fnGetEmployeeById($arrLeaveForm["user_id"]);

			if(count($arrLeaveUser) > 0)
			{
				/* Fetch user designation */
				$DesignationId = $arrLeaveUser["designation"];

				/* Fetch details for the user designation */
				$arrDesignationInfo = $objDesignation->fnGetDesignationById($DesignationId);

				if(count($arrDesignationInfo) > 0)
				{
					/* Fetch reporting head hierarchy */
					$arrHeads = $objEmployee->fnGetReportHeadHierarchy($arrLeaveForm["user_id"]);

					if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && isset($arrDesignationInfo["second_reporting_head"]) && trim($arrDesignationInfo["second_reporting_head"]) != "" && trim($arrDesignationInfo["second_reporting_head"]) != "0")
					{
						if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
						{
							$fstReportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];
							$arrLeaveForm['teamleader_id'] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
						}

						if(isset($arrHeads[$arrDesignationInfo["second_reporting_head"]]))
						{
							$secReportingHead = $arrHeads[$arrDesignationInfo["second_reporting_head"]];
							$arrLeaveForm['manager_id'] = $arrHeads[$arrDesignationInfo["second_reporting_head"]]["id"];
						}
					}
					else if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && trim($arrDesignationInfo["second_reporting_head"]) == "0")
					{
						if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
						{
							$fstReportingHead = $arrHeads[$arrDesignationInfo["first_reporting_head"]];

							$arrLeaveForm['manager_id'] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
							$arrLeaveForm['teamleader_id'] = 0;
						}
					}
				}
			}

			$maxDateToApply = date('Y-m-d', strtotime("+100 day"));

			if($arrLeaveForm['lwp_date'] > $maxDateToApply)
			{
				return -1;
			}

			$shiftCheck = $this->fnCheckUserShiftMovement($arrLeaveForm["user_id"],$arrLeaveForm['lwp_date'],$arrLeaveForm['lwp_date']);

			$checkDeligateId = $this->fnCheckDeligate($arrLeaveForm['teamleader_id']);
			$checkDeligateManagerId = $this->fnCheckDeligate($arrLeaveForm['manager_id']);

			if(isset($checkDeligateId) && $checkDeligateId != '')
			{
				$delegateTeamleaderId = $checkDeligateId;
			}
			else
			{
				$delegateTeamleaderId = 0;
			}

			if(isset($checkDeligateManagerId) && $checkDeligateManagerId != '')
			{
				$delegateManagerId = $checkDeligateManagerId;
			}
			else
			{
				$delegateManagerId = 0;
			}

			if($shiftCheck != '')
			{
				return -2;
			}
			
			else
			{
				$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrLeaveForm["user_id"])."' and date_format(date, '%Y-%m-%d') = '".mysql_real_escape_string($arrLeaveForm['lwp_date'])."' and (in_time!='00:00:00' or out_time!='00:00:00')";
				$db->query($sSQL);
				if($db->num_rows() > 0)
					return -3;
					else
					{
					$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrLeaveForm["user_id"])."' and date_format(date, '%Y-%m-%d') = '".mysql_real_escape_string($arrLeaveForm['lwp_date_to'])."'";
					$db->query($sSQL);
					if($db->num_rows() == 0)
						return -5;
					else
					{
					$sSQL = "select * from pms_approved_lwp where user_id='".mysql_real_escape_string($arrLeaveForm["user_id"])."' and date_format(lwp_date, '%Y-%m-%d') = '".mysql_real_escape_string($arrLeaveForm['lwp_date'])."' and approval_status!='2'";
					$db->query($sSQL);
					if($db->num_rows() > 0)
						return -4;
						
					else
					{
						$arrLeaveForm['added_date'] = date('Y-m-d H:i:s');

						/* Add code for TL & Manager */
						$arrLeaveForm["deligateTeamLeaderId"] = $delegateTeamleaderId;
						$arrLeaveForm["deligateManagerId"] = $delegateManagerId;

						$lastInsertId = $this->insertArray('pms_approved_lwp',$arrLeaveForm);

						return $lastInsertId;
					}
					}
					
				}
			}
		}
	
		function fnGetAdminApprovedLwpById($leaveId)
		{
			$arrLeave = array();
			
			$sSQL = "select l.*, e.name as employee_name, e1.name as reporting_head_name, t.title as leave_title, date_format(lwp_date, '%d-%m-%Y') as lwp_date from pms_approved_lwp l INNER JOIN pms_employee e ON e.id = l.user_id INNER JOIN pms_employee e1 ON e.teamleader = e1.id INNER JOIN pms_leave_type t ON t.id = l.leave_id where l.id='".mysql_real_escape_string($leaveId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrLeave = $this->fetchRow();
				}
			}
			
			return $arrLeave;
		}
		
		function fnGetAllAdminApprovedLwpRequests()
		{
			$arrApprovedLwp = array();
			
			$sSQL = "select l.*, e.name as employee_name, e1.name as reporting_head_name, t.title as leave_title, date_format(lwp_date, '%d-%m-%Y') as lwp_date from pms_approved_lwp l INNER JOIN pms_employee e ON e.id = l.user_id INNER JOIN pms_employee e1 ON e.teamleader = e1.id INNER JOIN pms_leave_type t ON t.id = l.leave_id where l.approval_status = '0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrApprovedLwp[] = $this->fetchRow();
				}
			}
			
			return $arrApprovedLwp;
		}

		function fnGetAdminApprovedLwpRequestById($leaveId)
		{
			$arrApprovedLwp = array();

			$sSQL = "select l.*, e.name as employee_name, e1.name as reporting_head_name, t.title as leave_title, date_format(lwp_date, '%d-%m-%Y') as lwp_date, date_format(lwp_date, '%Y-%m-%d') as lwpdate from pms_approved_lwp l INNER JOIN pms_employee e ON e.id = l.user_id INNER JOIN pms_employee e1 ON e.teamleader = e1.id INNER JOIN pms_leave_type t ON t.id = l.leave_id where l.approval_status = '0' and l.id='".mysql_real_escape_string($leaveId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrApprovedLwp = $this->fetchRow();
				}
			}

			return $arrApprovedLwp;
		}
		
		function fnApproveAdminLwpRequest($leaveRequest)
		{
					$arrAdminLeaves = array();

			if(isset($leaveRequest["id"]) && trim($leaveRequest["id"]) != "")
			{
				$LeaveRequestInfo = $this->fnGetAdminApprovedLwpRequestById($leaveRequest["id"]);
				//echo "<pre>";
				//print_r($LeaveRequestInfo);
				//exit;
				if(count($LeaveRequestInfo) > 0)
				{
					/* update leave status */
					$updateInfo["id"] = $leaveRequest["id"];
					$updateInfo["approval_status"] = $leaveRequest["approval_status"];
					$updateInfo["approval_comment"] = $leaveRequest["approval_comment"];
					$updateInfo["approval_date"] = Date('Y-m-d H:i:s');
					$updateInfo["approved_by"] = $_SESSION["id"];
					$updateInfo["approved_by_type"] = $_SESSION["usertype"];

					$this->updateArray('pms_approved_lwp',$updateInfo);

					/* Update attendance entry */
					include_once('class.attendance.php');
					include_once('class.employee.php');

					$objAttendance = new attendance();
					$objEmployee = new employee();

					if($updateInfo["approval_status"] == "1")
					{
						/* Check if data in attendance already entered */
						  $sSQL = "select * from pms_attendance where user_id = '".mysql_real_escape_string($LeaveRequestInfo["user_id"])."' and date_format(date, '%Y-%m-%d') >= '".mysql_real_escape_string($LeaveRequestInfo["lwpdate"])."' and date_format(date, '%Y-%m-%d') <= '".mysql_real_escape_string($LeaveRequestInfo["lwp_date_to"])."'";
						//echo "<br>";
						$this->query($sSQL);
						
						
						if($this->num_rows() > 0)
						{
							while($this->next_record())
							{
							$arrAdminLeaves[] = $this->fetchRow();
							}
							foreach($arrAdminLeaves as $key=>$value)
							{
								$sSQL = "update pms_attendance set leave_id = '".mysql_real_escape_string($LeaveRequestInfo["leave_id"])."' where user_id = '".mysql_real_escape_string($LeaveRequestInfo["user_id"])."'and id='".$value['id']."'";
								$this->query($sSQL);

							//echo $value['id']."<br>";
							}
							//exit;

						}
						else
						{
							$arrInfo["user_id"] = $LeaveRequestInfo["user_id"];
							$arrInfo["leave_id"] = $LeaveRequestInfo["leave_id"];
							$arrInfo["date"] = $LeaveRequestInfo["lwpdate"];

							$this->insertArray('pms_attendance',$arrInfo);
						}
					}

					/* Send mail to user */
					$arrStatus = array("1"=>"Approved", "2"=>"Rejected");
					$leaveStatus = $arrStatus[$updateInfo["approval_status"]];

					$Subject = 'Admin Approved LWP application - '.$leaveStatus;

					$curEmployee = $objEmployee->fnGetEmployeeDetailById($LeaveRequestInfo['user_id']);

					$arrHalfDayFor = array("1"=>"First Half", "2"=>"Second Half");
					
					$table = "Admin has ".$leaveStatus." a ".$LeaveRequestInfo["leave_title"]." for ".$curEmployee["name"]." for date ".$LeaveRequestInfo["lwp_date"].".<br/>Reason for adding leave is : ".$LeaveRequestInfo['reason'];
					
					if(trim($curEmployee["email"]) != "")
					{
						$userMailContent = "Dear ".$curEmployee['name'].", <br /><br />"."Admin has ".$leaveStatus." a ".$LeaveRequestInfo["leave_title"]." for you for start date : ".$LeaveRequestInfo["lwp_date"].", end date :".$LeaveRequestInfo["lwp_date_to"].".<br/>Reason for adding leave is : ".$LeaveRequestInfo['reason']."<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($curEmployee['email'],$Subject,$userMailContent);
					}
					
					/* First Reporting Head */
					if(isset($LeaveRequestInfo['teamleader_id']) && trim($LeaveRequestInfo['teamleader_id']) != ""  && trim($LeaveRequestInfo['teamleader_id']) != "0")
					{
						$arrTeamLeader = $objEmployee->fnGetEmployeeById($LeaveRequestInfo['teamleader_id']);

						$content = "Dear ".$arrTeamLeader['name'].", <br /><br />".$table."<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($arrTeamLeader['email'],$Subject,$content);
					}

					/* Second Reporting Head */
					if(isset($LeaveRequestInfo['manager_id']) && trim($LeaveRequestInfo['manager_id']) != ""  && trim($LeaveRequestInfo['manager_id']) != "0")
					{
						$arrManager = $objEmployee->fnGetEmployeeById($LeaveRequestInfo['manager_id']);

						$content = "Dear ".$arrManager['name'].", <br /><br />".$table."<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($arrManager['email'],$Subject,$content);
					}

					if(isset($LeaveRequestInfo['deligateTeamLeaderId']) && $LeaveRequestInfo['deligateTeamLeaderId'] != '' && $LeaveRequestInfo['deligateTeamLeaderId'] != '0')
					{
						$deligatedTeamleaderDetails = $objEmployee->fnGetEmployeeById($LeaveRequestInfo['deligateTeamLeaderId']);

						$content = "Dear ".$deligatedTeamleaderDetails['name'].", <br /><br />".$table."<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($deligatedTeamleaderDetails['email'],$Subject,$content);
					}

					if(isset($LeaveRequestInfo['deligateManagerId']) && $LeaveRequestInfo['deligateManagerId'] != '' && $LeaveRequestInfo['deligateManagerId'] != '0')
					{
						$deligatedManagerDetails = $objEmployee->fnGetEmployeeById($LeaveRequestInfo['deligateManagerId']);

						$content = "Dear ".$deligatedManagerDetails['name'].", <br /><br />".$table."<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($deligatedManagerDetails['email'],$Subject,$content);
					}

					return $updateInfo["approval_status"];
				}
				else
					return 0;
			}
			else
				return 0;
		}
		
		function fnGetSpecialLeaveByUserAndDate($userId, $dt)
		{
			$arrLeaveInfo = array();
			
			$sSQL = "select * from pms_approved_lwp where user_id='".mysql_real_escape_string($userId)."' and date_format(lwp_date,'%Y-%m-%d') = '".mysql_real_escape_string($dt)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrLeaveInfo = $this->fetchRow();
				}
			}
			
			return $arrLeaveInfo;
		}
		
		function fnCountAdminLeaveRequests()
		{
			$pending_admin_leave_count = 0;
			
			$sSQL = "select count(id) as pending_admin_leave_count from pms_leave_form where isadminadded='1' and status_manager = '0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$pending_admin_leave_count = $this->f("pending_admin_leave_count");
				}
			}
			
			return $pending_admin_leave_count;
		}

		function fnCountAdminHalfLeaveRequests()
		{
			$pending_admin_half_leave_count = 0;
			
			$sSQL = "select count(id) as pending_admin_half_leave_count from pms_half_leave_form where isadminadded='1' and status_manager = '0'";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$pending_admin_half_leave_count = $this->f("pending_admin_half_leave_count");
				}
			}
			
			return $pending_admin_half_leave_count;
		}
		
		function fnCountAdminApprovedLwpRequests()
		{
			$pending_admin_approved_lwp_count = 0;
			
			$sSQL = "select count(id) as pending_admin_approved_lwp_count from pms_approved_lwp where approval_status = '0'";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$pending_admin_approved_lwp_count = $this->f("pending_admin_approved_lwp_count");
				}
			}

			return $pending_admin_approved_lwp_count;
		}
		
		function fnGetAllAdminLeaveByDates($fromdate,$todate)
		{
			$arrAdminLeaves = array();
			
			$sSQL = "select date_format(l.start_date, '%d-%m-%Y') as startdate, date_format(l.end_date, '%d-%m-%Y') as enddate, date_format(l.date, '%d-%m-%Y') as addeddate, e.name as employee_name, e1.name as reporting_head_name, l.reason, l.status_manager from pms_leave_form l INNER JOIN pms_employee e ON l.employee_id = e.id INNER JOIN pms_employee e1 ON e.teamleader = e1.id where l.isadminadded='1' and (date_format(l.start_date, '%Y-%m-%d') between '".mysql_real_escape_string($fromdate)."' and '".mysql_real_escape_string($todate)."' or date_format(l.end_date, '%Y-%m-%d') between '".mysql_real_escape_string($fromdate)."' and '".mysql_real_escape_string($todate)."')";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAdminLeaves[] = $this->fetchRow();
				}
			}
			
			return $arrAdminLeaves;
		}
		
		function fnGetAllAdminHalfLeaveByDates($fromdate,$todate)
		{
			$arrAdminHalfLeaves = array();
			
			$sSQL = "select date_format(h.start_date, '%d-%m-%Y') as startdate, e.name as employee_name, e1.name as reporting_head_name, h.halfdayfor, date_format(h.date, '%d-%m-%Y') as addeddate, h.reason, h.status_manager from pms_half_leave_form h INNER JOIN pms_employee e ON h.employee_id = e.id INNER JOIN pms_employee e1 ON e.teamleader = e1.id where h.isadminadded='1' and date_format(h.start_date, '%Y-%m-%d') between '".mysql_real_escape_string($fromdate)."' and '".mysql_real_escape_string($todate)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAdminHalfLeaves[] = $this->fetchRow();
				}
			}
			
			return $arrAdminHalfLeaves;
		}
		
		function fnGetAllAdminApprovedLWPByDate($fromdate,$todate)
		{
			$arrApprovedLwp = array();

			$sSQL = "select l.*, e.name as employee_name, e1.name as reporting_head_name, t.title as leave_title, date_format(lwp_date, '%d-%m-%Y') as lwp_date, date_format(added_date, '%d-%m-%Y') as addeddate from pms_approved_lwp l INNER JOIN pms_employee e ON e.id = l.user_id INNER JOIN pms_employee e1 ON e.teamleader = e1.id INNER JOIN pms_leave_type t ON t.id = l.leave_id where date_format(lwp_date, '%Y-%m-%d') between '".mysql_real_escape_string($fromdate)."' and '".mysql_real_escape_string($todate)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrApprovedLwp[] = $this->fetchRow();
				}
			}

			return $arrApprovedLwp;
		}
		function fnGetclosingLeaveBalanceByMonth($id,$month,$year,$status="")
		{
			 if($status =="")
			 {
					  $sSQL = "select opening_leave from pms_leave_history where emp_id='".$id."' and month='".$month."' and year='".$year."' and ishalfmonthly='1'";
				//exit;
					$this->query($sSQL);
					
					if($this->num_rows() > 0)
					{
						if($this->next_record())
						{
						 $closing = $this->f("opening_leave");
							
						}
					}
					else
					{
			 		 $sSQL = "SELECT opening_leave FROM `pms_attendance_report` where employee_id='".$id."' and month='".$month."' AND year='".$year."'";
			  
			//exit;
						$this->query($sSQL);
						if($this->num_rows() > 0)
						{
							if($this->next_record())
							{
								$closing = $this->f("opening_leave");
							}
						}
					}
			}
			
			else
			{
			$sSQL = "SELECT closing_balance,pay_days FROM `pms_attendance_report` where employee_id='".$id."' and month='".$month."' AND year='".$year."'";
			  
			//exit;
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$closing = $this->f("closing_balance");
						$paydays = $this->f("pay_days");
						if($paydays >= 24)
						{
						$closing = $closing+1;
						}
					}
				}
			
			}

			return $closing;
			
		
		
		
		}
	}
?>

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

		function fnInsertLeaveForm($des_id,$arrLeaveForm)
		{
			//print_r($arrLeaveForm); die;
			include_once('class.employee.php');
			$objEmployee = new employee();

			if($_SESSION['designation'] == "5" || $_SESSION['designation'] == "9" || $_SESSION['designation'] == "10" || $_SESSION['designation'] == "11" || $_SESSION['designation'] == "12" || $_SESSION['designation'] == "14" || $_SESSION['designation'] == "15" || $_SESSION['designation'] == "16" || $_SESSION['designation'] == "20" || $_SESSION['designation'] == "21" || $_SESSION['designation'] == "22" || $_SESSION['designation'] == "23" || $_SESSION['designation'] == "24" || $_SESSION['designation'] == "25" || $_SESSION['designation'] == "26" || $_SESSION['designation'] == "27" || $_SESSION['designation'] == "28")
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
			else if($_SESSION['designation'] == 6 || $_SESSION['designation'] == 18 || $_SESSION['designation'] == 19)
			{
				$arrLeaveForm['teamleader_id'] = 0;
				$arrLeaveForm['manager_id'] = 1;
			}


			$shiftCheck = $this->fnCheckShiftMoment($arrLeaveForm['start_date'],$arrLeaveForm['end_date']);
			//echo '<pre>'; print_r($arrLeaveForm);
			$checkDeligateId = $this->fnCheckDeligate($arrLeaveForm['teamleader_id']);

			if($arrLeaveForm['manager_id'] == '0')
			{
				$checkDeligateManagerId = $this->fnCheckDeligateManager($arrLeaveForm['teamleader_id']);
			}
			else
			{
				$checkDeligateManagerId = $this->fnCheckDeligateManager($arrLeaveForm['manager_id']);
			}
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
				$query = "SELECT count( id ) AS count,status,status_manager,deligateManagerId,manager_delegate_status FROM pms_leave_form WHERE ('".$arrLeaveForm['start_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d') or '".$arrLeaveForm['end_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d')) and employee_id = '".$_SESSION['id']."' and (status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1)))";
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
					$arrLeaveForm['date'] = date('Y-m-d');
					$arrLeaveForm['employee_id'] = $des_id;
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
					
					//echo '<pre>'; print_r($arrLeaveForm); die;
					$lastInsertId = $this->insertArray('pms_leave_form',$arrLeaveForm);
					return $lastInsertId;
				}
			}
		}

		function fnCheckDeligate($id)
		{
			$date = date('Y-m-d');
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

			include_once('class.employee.php');
			$objEmployee = new employee();

			if($_SESSION['designation'] == "5" || $_SESSION['designation'] == "9" || $_SESSION['designation'] == "10" || $_SESSION['designation'] == "11" || $_SESSION['designation'] == "12" || $_SESSION['designation'] == "14" || $_SESSION['designation'] == "15" || $_SESSION['designation'] == "16" || $_SESSION['designation'] == "20" || $_SESSION['designation'] == "21" || $_SESSION['designation'] == "22" || $_SESSION['designation'] == "23" || $_SESSION['designation'] == "24" || $_SESSION['designation'] == "25" || $_SESSION['designation'] == "26" || $_SESSION['designation'] == "27" || $_SESSION['designation'] == "28")
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
			else if($_SESSION['designation'] == 6 || $_SESSION['designation'] == 18 || $_SESSION['designation'] == 19)
			{
				$arrLeaveForm['teamleader_id'] = 0;
				$arrLeaveForm['manager_id'] = 0;
			}


			$shiftCheck = $this->fnCheckShiftMoment($arrLeaveForm['start_date'],$arrLeaveForm['start_date']);
			//$alreadyLeave = $this->fnCheckAlreadyLeave($_SESSION['id'],$arrLeaveForm['start_date'],$arrLeaveForm['end_date']);
			if($shiftCheck != '')
			{
				return 0;
			}
			else
			{
				$count = '';
				 //$query = "SELECT count( id ) AS count,status,status_manager FROM pms_leave_form WHERE ('".$arrLeaveForm['start_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d') or '".$arrLeaveForm['end_date']."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d')) and employee_id = '".$_SESSION['id']."' and status_manager IN(0,1)";
				//$query = "SELECT count( id ) AS count,status,status_manager FROM pms_half_leave_form WHERE '".$arrLeaveForm['start_date']."' = DATE_FORMAT(start_date,'%Y-%m-%d') and employee_id = '".$_SESSION['id']."' and (status_manager IN(0,1) or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status IN(0,1)))";
				$query = "SELECT count( id ) AS count,status,status_manager FROM pms_half_leave_form WHERE '".$arrLeaveForm['start_date']."' = DATE_FORMAT(start_date,'%Y-%m-%d') and employee_id = '".$_SESSION['id']."' and status_manager IN(0,1)";
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
					$arrLeaveForm['date'] = date('Y-m-d');
					$arrLeaveForm['employee_id'] = $des_id;

					/* Add code for TL & Manager */
					$arrLeaveForm["tlapprovalcode"] = halfdayleaveform_uid();
					$arrLeaveForm["managerapprovalcode"] = halfdayleaveform_uid();

					$lastInsertId = $this->insertArray('pms_half_leave_form',$arrLeaveForm);
					return $lastInsertId;
				}
			}
		}

		function fnCheckShiftMoment($start,$end)
		{
			$id ='';
			$query = "select id from `pms_shift_movement` where `movement_date` between '$start' and '$end' and isCancel ='0' and `userid`='".$_SESSION['id']."' ";
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
			$query = "SELECT * FROM `pms_leave_form` WHERE `employee_id` = '$id'";
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
			$query = "SELECT * FROM `pms_half_leave_form` WHERE `employee_id` = '$id'";
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
			echo '<pre>';  print_r($_SESSION);
			$Subject = 'Emergency Leave';
			/* When manager login */
			if($_SESSION['designation'] == '6' || $_SESSION['designation'] == '18' || $_SESSION['designation'] == '19')
			{
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
						//echo 'hello'.$content.'<br>';
						sendmail($DeligateTeamLeaderInfo['email'],$Subject,$content);
					}

					/* send mail to original teamleader */
					$content = "Dear ".$TeamLaderInfo['name'].", <br /><br />";
					$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." an Emergency leave request for ".$LeaveInfo["emplo_name"]." for date ".$LeaveInfo['startdate'].".";
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					//echo 'hello'.$content.'<br>';
					sendmail($TeamLaderInfo['email'],$Subject,$content);
					
					/* send mail to applyer */
					$content = "Dear ".$LeaveInfo['name'].", <br /><br />";
					$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." an Emergency leave request for you for date ".$LeaveInfo['startdate'].".";
					
					$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
					//echo 'hello1'.$content.'<br>';
					sendmail($LeaveInfo['email'],$Subject,$content);
				}
			}
			else if($_SESSION['designation'] == '7' || $_SESSION['designation'] == '13')
			{
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
			$query = "SELECT employee.*,leaves.*,head.name as headname,leaves.id as leaves_id,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS start_date,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') AS end_date, if(isemergency=1,'Yes','No') as isemergency FROM `pms_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id LEFT JOIN `pms_employee` AS head ON employee.teamleader = head.id WHERE employee_id IN($ids) and `isactive` = '0' and (DATE_FORMAT(leaves.`start_date`,'%Y-%m-%d') >= '$current_date' and DATE_FORMAT(leaves.`end_date`,'%Y-%m-%d') >= '$current_date') and ((employee.designation IN(7,13,6) and (leaves.`status_manager` = '0' or (leaves.`deligateManagerId` != '0' && leaves.`status_manager` = '0'))) or (employee.designation NOT IN (7,13,6) and (leaves.`status`= '0' or leaves.`status_manager` = '0' or (leaves.`deligateTeamLeaderId` != '0' and leaves.`delegate_status` = '0') or (leaves.`deligateManagerId` != '0' && leaves.`status_manager` = '0'))))";


			//$sSQL = "SELECT employee.*,leaves.*,head.name as headname,leaves.id as leaves_id,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS start_date,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') AS end_date, if(isemergency=1,'Yes','No') as isemergency FROM `pms_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id LEFT JOIN `pms_employee` AS head ON employee.teamleader = head.id WHERE employee_id IN(6,7) and `isactive` = '0' and (DATE_FORMAT(leaves.`start_date`,'%Y-%m-%d') >= '2013-04-06' and DATE_FORMAT(leaves.`end_date`,'%Y-%m-%d') >= '2013-04-06') and ((employee.designation IN(7,13,6) and (leaves.`status_manager` = '0' or (leaves.`deligateManagerId` != '0' && leaves.`status_manager` = '0'))) or (employee.designation NOT IN (7,13,6) and (leaves.`status` = '0' or leaves.`status_manager` = '0' or (leaves.`deligateTeamLeaderId` != '0' and leaves.`delegate_status` = '0') or (leaves.`deligateManagerId` != '0' && leaves.`status_manager` = '0'))))";
			
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

		function fnGetAllHalfLeaveRequest($ids)
		{
			$arrLeaveFormValues = array();
			$query = "SELECT employee.*,leaves.*,head.name as headname,leaves.id as leaves_id,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS start_date FROM `pms_half_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id LEFT JOIN `pms_employee` AS head ON employee.teamleader = head.id WHERE employee_id IN($ids)";
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

		/* Get Leave Details by leave id */
		function fnGetLeaveDetailsById($id)
		{
			$arrLeaveValues = array();
			$query = "SELECT *, leaves.id as leaveid,leaves.deligateTeamLeaderId as deligateTeamLeaderId,leaves.start_date as starting_date,leaves.status as team_leader_status_id,leaves.status_manager as manager_status_id,leaves.end_date as ending_date,leaves.nodays as actual_nods,leaves.status as team_leader_status,leaves.comment as team_leader_comment,leaves.reason as actual_reason,DATE_FORMAT(leaves.date,'%d-%m-%Y') AS apply_actual_date,leaves.id as leave_id,leaves.status as leave_status,leaves.id AS leaves_id,employee.id as employee_id,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS start_date,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS new_start_date,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') AS end_date,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') AS new_end_date,DATE_FORMAT(leaves.start_date,'%d-%m-%Y')
AS startdate,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') AS enddate,DATE_FORMAT(leaves.date,'%d-%m-%Y') AS date,leaves.address as leave_address,leaves.contact as leave_contact,employee.name as emplo_name,employee.`leave_bal`
as balance, if(isemergency=1,'Yes','No') as isemergency FROM `pms_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id WHERE leaves.id ='$id'";
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
			$query = "SELECT *,leaves.start_date as starting_date,leaves.status as team_leader_status_id,leaves.status_manager as manager_status_id,leaves.nodays as actual_nods,leaves.status as team_leader_status,leaves.comment as team_leader_comment,leaves.reason as actual_reason,DATE_FORMAT(leaves.date,'%d-%m-%Y') AS apply_actual_date,leaves.id as leave_id,leaves.status as leave_status,leaves.id AS leaves_id,employee.id as employee_id,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS start_date,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS new_start_date,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') AS startdate,DATE_FORMAT(leaves.date,'%d-%m-%Y') AS date,leaves.address as leave_address,leaves.contact as leave_contact,employee.name as emplo_name,employee.`leave_bal`
as balance FROM `pms_half_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id WHERE leaves.id ='$id'";
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
			//echo '<pre>'; print_r($post); 
			if(isset($post['isactive']) && $post['isactive'] == 1 )
			{
				header("Location: leave_request.php?info=timepast");
			}
			if($_SESSION["usertype"] == "employee")
			{
				
				include_once('class.employee.php');
				$objEmployee = new employee();

				$status = array("0"=>"Pending", "1"=>"Approved", "2"=>"Unapproved");

				$LeaveInfo = $this->fnLeaveInfoById($post["hdnid"]);
				
				/* Manager login */
				if($_SESSION['designation'] == '6' || $_SESSION['designation'] == '18' || $_SESSION['designation'] == '19')
				{
					/* Manager Information */
					$ManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo['teamleader_id']);
					
					/*  Leave added by Teamleader */
					if($LeaveInfo['designation'] == '7' || $LeaveInfo['designation'] == '13')
					{
						/* when delegated manager login */
						if($_SESSION['id'] == $post['session_delegateManager'])
						{
							$DeligatedManagerInfo = $objEmployee->fnGetEmployeeById($post["session_delegateManager"]);
						
							/* Leave send to manager of teamleader */
							$MailTo = $ManagerInfo["email"];
							$Subject = "Leave Request ".$status[$post["manager_delegate_status"]];
							$content = "Dear ".$ManagerInfo["name"].",<br><br>";
							//$content .= $DeligatedManagerInfo["name"]." has ".$status[$post["manager_delegate_status"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
							$content .= "".$LeaveInfo["name"]." application for Leave/s has been ".$status[$post["manager_delegate_status"]]." by ".$DeligatedManagerInfo["name"]." on ".$LeaveInfo["leavedate"].". ";
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
							//echo '<br>hello1'.$content.'<br>';
							sendmail($MailTo, $Subject, $content);


							if($post["delegate"] != 0)
							{
								$DelegatedTeamleaderInfo = $objEmployee->fnGetEmployeeById($post["delegate"]);
							}
							/* Mail send to teamleader that applied for leave */
							$MailTo = $LeaveInfo["email"];
							$Subject = "Leave Request ".$status[$post["manager_delegate_status"]];

							$content = "Dear ".$LeaveInfo["name"].",<br><br>";
							//$content .= $DeligatedManagerInfo["name"]." has ".$status[$post["manager_delegate_status"]]." your leave request on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
							$content .= "Your application for Leave/s has been ".$status[$post["manager_delegate_status"]]." by ".$DeligatedManagerInfo["name"]." on ".$LeaveInfo["leavedate"].". ";
							if($status[$post["manager_delegate_status"]] == "Approved")
							{
								$content .="As ".$status[$post["manager_delegate_status"]].", you can avail these leaves from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".Your responsibilities would be delegated to ".$DelegatedTeamleaderInfo["name"]." while you are on leave/s, which please note.";
							}
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
							//echo '<br>hello2'.$content.'<br>';
							sendmail($MailTo, $Subject, $content);
							

							/* Mail send to delegate teamleader */
							if($post["delegate"] != 0)
							{
								$DelegatedTeamleaderInfo = $objEmployee->fnGetEmployeeById($post["delegate"]);

								$MailTo = $DelegatedTeamleaderInfo["email"];
								$Subject = "Leave Request ".$status[$post["manager_delegate_status"]].' for '.$LeaveInfo['name'];
								$content = "Dear ".$DelegatedTeamleaderInfo["name"].",<br><br>";
								//$content .= $DeligatedManagerInfo["name"]." has ".$status[$post["manager_delegate_status"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"]." and you are delegated team leader on behalf of him.<br/><br/>";
								$content .= "Kindly note that ".$LeaveInfo["name"]." will be on leave from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"]." and his work responsibilities are delegated to you for while he is on leave/s for the above period.";
								$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
								//echo '<br>hello4'.$content.'<br>';
								sendmail($MailTo, $Subject, $content);
							}
							
						}
						else
						{
							if($post["delegate"] != 0)
							{
								$DelegatedTeamleaderInfo = $objEmployee->fnGetEmployeeById($post["delegate"]);
							}
							
							$DeligatedManagerInfo = $objEmployee->fnGetEmployeeById($post["session_delegateManager"]);

							/* Leave send to delegate manager of teamleader */
							if($LeaveInfo['deligateManagerId'] != '' && $LeaveInfo['deligateManagerId'] != '0')
							{
								$MailTo = $DeligatedManagerInfo["email"];
								$Subject = "Leave Request ".$status[$post["status_manager"]];
								$content = "Dear ".$DeligatedManagerInfo["name"].",<br><br>";
								//$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
								$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
								$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
								//echo 'h1<pre>'.$content.'<br>';
								sendmail($MailTo, $Subject, $content);
							}

							/* Mail send to teamleader that applied for leave */
							
							$MailTo = $LeaveInfo["email"];
							$Subject = "Leave Request ".$status[$post["status_manager"]];

							$content = "Dear ".$LeaveInfo["name"].",<br><br>";
							//$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." your leave request on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
							$content .= "Your application for Leave/s has been ".$status[$post["status_manager"]]." by ".$ManagerInfo["name"]." on ".$LeaveInfo["leavedate"].".";

							if($status[$post["status_manager"]] == 'Approved')
							{
								$content .= " As ".$status[$post["status_manager"]].", you can avail these leaves from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".Your responsibilities would be delegated to ".$DelegatedTeamleaderInfo['name']." while you are on leave/s, which please note.";
							}
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
							//echo '<br>hello2'.$content.'<br>';
							sendmail($MailTo, $Subject, $content);

							/* Mail send to delegate teamleader */
							if($post["delegate"] != 0)
							{
								//$DelegatedTeamleaderInfo = $objEmployee->fnGetEmployeeById($post["delegate"]);
								$Subject = "Leave Request ".$status[$post["status_manager"]];
								$MailTo = $DelegatedTeamleaderInfo["email"];

								$content = "Dear ".$DelegatedTeamleaderInfo["name"].",<br><br>";
								//$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"]." and you are delegated team leader on behalf of him.<br/><br/>";
								$content .= "Kindly note that ".$LeaveInfo["name"]." will be on leave from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"]." and his work responsibilities are delegated to you for while he is on leave/s for the above period.";
								$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
								//echo '<br>hello4'.$content.'<br>';
								sendmail($MailTo, $Subject, $content);
							}
						}
					}

					/* Login By Manager and leave applyer is agent */
					else if($post['actual_Designation'] == '5' || $post['actual_Designation'] == '9' || $post['actual_Designation'] == '10'  || $post['actual_Designation'] == '14' || $post['actual_Designation'] == '15' || $post['actual_Designation'] == '16' )
					{
						
					
						/* First check that login user is actual manager or delegate manager */
											/* This is delegated manager */
						if($_SESSION['id'] == $post['session_delegateManager'])
						{
							
							/* Delegated manager details */
							$DeligatedManagerInfo = $objEmployee->fnGetEmployeeById($_SESSION['id']);
							
							/* send mail to actual manager */
							$managerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo['manager_id']);

							$MailTo = $managerInfo["email"];
							$Subject = "Leave Request ".$status[$post["manager_delegate_status"]];
							$content = "Dear ".$managerInfo["name"].",<br><br>";
							$content .= $DeligatedManagerInfo["name"]." has ".$status[$post["manager_delegate_status"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
							//echo 'hello3<br>'.$content.'<br>';
							sendmail($MailTo, $Subject, $content);


							/* send mail to agent */
							$MailTo = $LeaveInfo["email"];
							$Subject = "Leave Request ".$status[$post["manager_delegate_status"]];

							$content = "Dear ".$LeaveInfo["name"].",<br><br>";
							$content .= $DeligatedManagerInfo["name"]." has ".$status[$post["manager_delegate_status"]]." your Leave request on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
							//echo 'helo1'.$content.'<br>';
							sendmail($MailTo, $Subject, $content);


							/* send mail to actual teamleader */
							$TeamleaderInfo = $objEmployee->fnGetEmployeeById($post["actual_Teamleader"]);
							$MailTo = $TeamleaderInfo["email"];
							$Subject = "Leave Request ".$status[$post["manager_delegate_status"]];

							$content = "Dear ".$TeamleaderInfo["name"].",<br><br>";
							$content .= $DeligatedManagerInfo["name"]." has ".$status[$post["manager_delegate_status"]]." Leave request of ".$LeaveInfo["name"]." on  ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
							//echo 'helo2'.$content.'<br>';
							sendmail($MailTo, $Subject, $content);

							/* send mail to delegated teamleader if exist */
							if($LeaveInfo["deligateTeamLeaderId"] != '' && $LeaveInfo["deligateTeamLeaderId"] != '0')
							{
								$DelegatedTeamleaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateTeamLeaderId"]);
								$MailTo = $DelegatedTeamleaderInfo["email"];
								$Subject = "Leave Request ".$status[$post["manager_delegate_status"]];

								$content = "Dear ".$DelegatedTeamleaderInfo["name"].",<br><br>";
								$content .= $DeligatedManagerInfo["name"]." has ".$status[$post["manager_delegate_status"]]."  Leave request of ".$LeaveInfo["name"]." on ".  $LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
								$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
								//echo 'helo3'.$content.'<br>';
								sendmail($MailTo, $Subject, $content);
							}
						}
											/* This is actual manager */
						else
						{
							//echo 'hello1'; die;
//print_r($post);
							
							/* Get actual manager detais */
							$ManagerInfo = $objEmployee->fnGetEmployeeById($_SESSION['id']);


							/* send mail to delegated manager */
							if($LeaveInfo['deligateManagerId'] != 0)
							{
								$delegatedManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo['deligateManagerId']);

								//echo '<pre>'; print_r($LeaveInfo); die;
								
								$MailTo = $delegatedManagerInfo["email"];
								$Subject = "Leave Request ".$status[$post["status_manager"]];
								$content = "Dear ".$delegatedManagerInfo["name"].",<br><br>";
								$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".";
								if($status[$post["status_manager"]] == "Approved")
								{
									$content .= " As approved, ".$LeaveInfo["name"]." can avail these leaves from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
								}
								$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
								//echo 'helo1'.$content.'<br>'; die;
								sendmail($MailTo, $Subject, $content);
							}

							/* send mail to agent */
							$MailTo = $LeaveInfo["email"];
							$Subject = "Leave Request ".$status[$post["status_manager"]];

							$content = "Dear ".$LeaveInfo["name"].",<br><br>";
							$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." your Leave request on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
							//echo 'helo2'.$content.'<br>';
							sendmail($MailTo, $Subject, $content);


							/* send mail to actual teamleader */
							$TeamleaderInfo = $objEmployee->fnGetEmployeeById($post["actual_Teamleader"]);
							$MailTo = $TeamleaderInfo["email"];
							$Subject = "Leave Request ".$status[$post["status_manager"]];

							$content = "Dear ".$TeamleaderInfo["name"].",<br><br>";
							$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
							//echo 'helo3'.$content.'<br>';
							sendmail($MailTo, $Subject, $content);

							/* send mail to delegated teamleader if exist */
							if($LeaveInfo["deligateTeamLeaderId"] != '' && $LeaveInfo["deligateTeamLeaderId"] != '0')
							{
								$DelegatedTeamleaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["deligateTeamLeaderId"]);
								$MailTo = $DelegatedTeamleaderInfo["email"];
								$Subject = "Leave Request ".$status[$post["status_manager"]];

								$content = "Dear ".$DelegatedTeamleaderInfo["name"].",<br><br>";
								$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
								$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
								//echo 'helo4'.$content.'<br>';
								sendmail($MailTo, $Subject, $content);
							}
						}
					}
					/* Login By Manager and leave applyer is directaly under manager */
					else if($post['actual_Designation'] == '20' || $post['actual_Designation'] == '21' || $post['actual_Designation'] == '22' || $post['actual_Designation'] == '23' || $post['actual_Designation'] == '24' || $post['actual_Designation'] == '25' || $post['actual_Designation'] == '26' || $post['actual_Designation'] == '27' || $post['actual_Designation'] == '28' || $post['actual_Designation'] == '11' || $post['actual_Designation'] == '12' )
					{
						
						/* First check that login user is actual manager or delegate manager */
											/* This is delegated manager */
						if($_SESSION['id'] == $post['session_delegateManager'])
						{
							
							/* Delegated manager details */
							$DeligatedManagerInfo = $objEmployee->fnGetEmployeeById($_SESSION['id']);
							
							/* send mail to actual manager */
							$managerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo['manager_id']);

							$MailTo = $managerInfo["email"];
							$Subject = "Leave Request ".$status[$post["manager_delegate_status"]];
							$content = "Dear ".$managerInfo["name"].",<br><br>";
							$content .= $DeligatedManagerInfo["name"]." has ".$status[$post["manager_delegate_status"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
							//echo 'hello3<br>'.$content.'<br>';
							sendmail($MailTo, $Subject, $content);


							/* send mail to agent */
							$MailTo = $LeaveInfo["email"];
							$Subject = "Leave Request ".$status[$post["manager_delegate_status"]];

							$content = "Dear ".$LeaveInfo["name"].",<br><br>";
							$content .= $DeligatedManagerInfo["name"]." has ".$status[$post["manager_delegate_status"]]." your Leave request on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".";
							if($status[$post["manager_delegate_status"]] == "Approved")
							{
								$content .= " As approved, you can avail these leaves from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
							}
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
							//echo 'helo1'.$content.'<br>';
							sendmail($MailTo, $Subject, $content);

						}
											/* This is actual manager */
						else
						{
							/* Get actual manager detais */
							$ManagerInfo = $objEmployee->fnGetEmployeeById($_SESSION['id']);


							/* send mail to delegated manager */
							if($LeaveInfo['deligateManagerId'] != 0)
							{
								$delegatedManagerInfo = $objEmployee->fnGetEmployeeById($LeaveInfo['deligateManagerId']);

								//echo '<pre>'; print_r($LeaveInfo); die;
								
								$MailTo = $delegatedManagerInfo["email"];
								$Subject = "Leave Request ".$status[$post["status_manager"]];
								$content = "Dear ".$delegatedManagerInfo["name"].",<br><br>";
								$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
								$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
								//echo 'helo1'.$content.'<br>';
								sendmail($MailTo, $Subject, $content);
							}

							/* send mail to agent */
							$MailTo = $LeaveInfo["email"];
							$Subject = "Leave Request ".$status[$post["status_manager"]];

							$content = "Dear ".$LeaveInfo["name"].",<br><br>";
							$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." your Leave request on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".";
							if($status[$post["status_manager"]] == "Approved")
							{
								$content .= " As approved, you can avail these leaves from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
							}
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
							//echo 'helo2'.$content.'<br>'; die;
							sendmail($MailTo, $Subject, $content);
						}
					}
				}

				//print_r($post); die;
				/* TeamLeader login */
				if($_SESSION['designation'] == '7' || $_SESSION['designation'] == '13')
				{
						
					//echo '<pre>';print_r($_SESSION);
					//print_r($post);
					
					
					//print_r($LeaveInfo);
					/* Login teamleader is delegated */
						if($_SESSION['id'] == $post['session_delegateTeamleader'])
						{
							//echo 'helo'; die;
							$TeamleaderInfo = $objEmployee->fnGetEmployeeById($post["actual_Teamleader"]);
							$DeligatedTeamleaderInfo = $objEmployee->fnGetEmployeeById($post["session_delegateTeamleader"]);

							
							/* mail send to applyer */
							$MailTo = $LeaveInfo["email"];
							$Subject = "Leave Request ".$status[$post["status"]];

							$content = "Dear ".$LeaveInfo["name"].",<br><br>";
							
							$content .= "Your application for Leave/s has been ".$status[$post["delegate_status"]]." by ".$DeligatedTeamleaderInfo["name"]." on ".$LeaveInfo['leavedate'].". ";

							if($status[$post["delegate_status"]] == "Approved")
							{
								$content .= "As approved, you can avail these leaves from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
							}
							
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
							//echo 'helo1'.$content.'<br>';
							sendmail($MailTo, $Subject, $content);

							/* mail send to teamleader */
							if(isset($TeamleaderInfo))
							{
								//echo 'hello';
								$ManagerInfo = $objEmployee->fnGetEmployeeById($TeamleaderInfo["teamleader_id"]);
									//echo '<pre>';print_r($ManagerInfo);
								$MailTo = $TeamleaderInfo["email"];

								$content = "Dear ".$TeamleaderInfo["name"].",<br><br>";
								$content .= "".$LeaveInfo["name"]." application for Leave/s has been ".$status[$post["delegate_status"]]." by ".$DeligatedTeamleaderInfo["name"]." on ".$LeaveInfo["leavedate"].". ";

								if($status[$post["delegate_status"]] == "Approved")
								{
									$content .= "As approved, ".$LeaveInfo["name"]." can avail these leaves from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
								}
								
								$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
								//echo '<br>'.$content.'<br>';
								sendmail($MailTo, $Subject, $content);
							}

							/* mail send to manager */
							if($TeamleaderInfo["teamleader"] != '0')
							{
								//echo 'hello';
								$ManagerInfo = $objEmployee->fnGetEmployeeById($TeamleaderInfo["teamleader_id"]);
									//echo '<pre>';print_r($ManagerInfo);
								$MailTo = $ManagerInfo["email"];

								$content = "Dear ".$ManagerInfo["name"].",<br><br>";
								//$content .= $DeligatedTeamleaderInfo["name"]." has ".$status[$post["delegate_status"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
								$content .= "".$LeaveInfo["name"]." application for Leave/s has been ".$status[$post["delegate_status"]]." by ".$DeligatedTeamleaderInfo["name"]." on ".$LeaveInfo["leavedate"].". ";
								if($status[$post["delegate_status"]] == "Approved")
								{
									$content .= "As approved, ".$LeaveInfo["name"]." can avail these leaves from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
								}
								$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
								//echo '<br>'.$content.'<br>';
								sendmail($MailTo, $Subject, $content);
							}
							
							if($post["session_delegateManager"] != '0')
							{
								//echo 'hello';
								$DelegateManagerInfo = $objEmployee->fnGetEmployeeById($post["session_delegateManager"]);
									//echo '<pre>';print_r($ManagerInfo);
								$MailTo = $DelegateManagerInfo["email"];

								$content = "Dear ".$DelegateManagerInfo["name"].",<br><br>";
								//$content .= $DeligatedTeamleaderInfo["name"]." has ".$status[$post["delegate_status"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
								$content .= "".$LeaveInfo["name"]." application for Leave/s has been ".$status[$post["delegate_status"]]." by ".$DeligatedTeamleaderInfo["name"]." on ".$LeaveInfo["leavedate"].". ";
								if($status[$post["delegate_status"]] == "Approved")
								{
									$content .= "As approved, ".$LeaveInfo["name"]." can avail these leaves from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
								}
								$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
								//echo '<br>'.$content.'<br>';
								sendmail($MailTo, $Subject, $content);
							}
							
						}
						/* when login teamleader is actual not delegated */
						else
						{
							$TeamleaderInfo = $objEmployee->fnGetEmployeeById($_SESSION["id"]);
							//print_r($TeamleaderInfo); die;
							$DeligatedTeamleaderInfo = $objEmployee->fnGetEmployeeById($post["session_delegateTeamleader"]);
							//print_r($DeligatedTeamleaderInfo); die;

							/* mail send to applyer */
							$MailTo = $LeaveInfo["email"];
							$Subject = "Leave Request ".$status[$post["status"]];

							$content = "Dear ".$LeaveInfo["name"].",<br><br>";
							//$content .= $TeamleaderInfo["name"]." has ".$status[$post["status"]]." your Leave request on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
							$content .= "Your application for Leave/s has been ".$status[$post["status"]]." by ".$TeamleaderInfo["name"]." on ".$LeaveInfo["leavedate"]." . ";

							if($status[$post["status"]] == "Approved")
							{
								$content .= "As approved, you can avail these leaves from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
							}
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
							//echo '<br>h1'.$content.'<br>';
							sendmail($MailTo, $Subject, $content);

							/* mail send to delegated teamleader if exist */
							if($post['session_delegateTeamleader'] != '' && $post['session_delegateTeamleader'] != '0')
							{
								//echo 'hello';
								$ManagerInfo = $objEmployee->fnGetEmployeeById($TeamleaderInfo["teamleader_id"]);
									//echo '<pre>';print_r($ManagerInfo);
								$MailTo = $DeligatedTeamleaderInfo["email"];

								$content = "Dear ".$DeligatedTeamleaderInfo["name"].",<br><br>";
								//$content .= $TeamleaderInfo["name"]." has ".$status[$post["status"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
								$content .= "".$LeaveInfo["name"]." application for Leave/s has been ".$status[$post["status"]]." by ".$TeamleaderInfo["name"]." on ".$LeaveInfo["leavedate"].".<br/><br/>";
								if($status[$post["status"]] == "Approved")
								{
									$content .= "As approved, ".$LeaveInfo["name"]." can avail these leaves from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
								}
								$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
								//echo '<br>h2'.$content.'<br>';
								sendmail($MailTo, $Subject, $content);
							}

							/* send mail to manager */
							if($TeamleaderInfo["teamleader"] != '0')
							{
								$ManagerInfo = $objEmployee->fnGetEmployeeById($_SESSION["teamleader"]);

								$MailTo = $ManagerInfo["email"];

								$content = "Dear ".$ManagerInfo["name"].",<br><br>";
								//$content .= $TeamleaderInfo["name"]." has ".$status[$post["status"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
								$content .= "".$LeaveInfo["name"]." application for Leave/s has been ".$status[$post["status"]]." by ".$TeamleaderInfo["name"]." on ".$LeaveInfo["leavedate"].".";
								if($status[$post["status"]] == "Approved")
								{
									$content .= "As approved, ".$LeaveInfo["name"]." can avail these leaves from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
								}
								$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
								//echo '<br>h3'.$content.'<br>';
								sendmail($MailTo, $Subject, $content);
								
							}
							/* send mail to delegated manager  */
							if($post['session_delegateManager'] != '0')
							{
								$DelegateManagerInfo = $objEmployee->fnGetEmployeeById($post["session_delegateManager"]);

								$MailTo = $DelegateManagerInfo["email"];

								$content = "Dear ".$DelegateManagerInfo["name"].",<br><br>";
								//$content .= $TeamleaderInfo["name"]." has ".$status[$post["status"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
								$content .= "".$LeaveInfo["name"]." application for Leave/s has been ".$status[$post["status"]]." by ".$TeamleaderInfo["name"]." on ".$LeaveInfo["leavedate"].".<br/><br/>";
								if($status[$post["status"]] == "Approved")
								{
									$content .= "As approved, ".$LeaveInfo["name"]." can avail these leaves from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
								}
								$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
								//echo '<br>h4'.$content.'<br>';
								sendmail($MailTo, $Subject, $content);
							}
						}
					}
				/* When login is ceo and applyer is manager */
				else if($_SESSION['designation'] == '17')
				{
					
					//echo 'gagan<pre>'; print_r($LeaveInfo); 
					/* Get all information about ceo */
					$ceoInfo = $objEmployee->fnGetEmployeeById($_SESSION['id']);
					

					/* Get Delegated manager details */
					if($post['manager_delegate'] != 0)
					{
						$delegatedManageDetails = $objEmployee->fnGetEmployeeById($post['manager_delegate']);
					}
					
					/* Send mail to manager */
					$MailTo = $LeaveInfo["email"];
					$Subject = "Leave Request ".$status[$post["status_manager"]];
					$content = "Dear ".$LeaveInfo["name"].",<br><br>";
					if($post['status_manager'] == 1)
					{
						$content .= "Your application for Leave/s has been ".$status[$post["status_manager"]]." by ".$ceoInfo["name"]." on ".$LeaveInfo["leavedate"].". As ".$status[$post["status_manager"]].", you can avail these leaves from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>Your responsibilities would be delegated to ".$delegatedManageDetails['name']." while you are on leave/s, which please note.<br/><br/>";
					}
					else
					{
						$content .= "Your application for Leave/s has been ".$status[$post["status_manager"]]." by ".$ceoInfo["name"]." on ".$LeaveInfo["leavedate"].".<br/><br/>";
					}
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
					
					//echo 'helo2'.$content; 
					sendmail($MailTo, $Subject, $content);

					/* send mail to delegated team manager to infor he is delegated */
					if($post['status_manager'] == 1)
					{
						if($post['manager_delegate'] != '0')
						{
							$MailTo = $delegatedManageDetails["email"];
							$Subject = "Leave Request ".$status[$post["status_manager"]].' for '.$LeaveInfo["name"];
							$content = "Dear ".$delegatedManageDetails["name"].",<br><br>";

							$content .= "Kindly note that ".$LeaveInfo["name"]." will be on leave from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"]." and his work responsibilities are delegated to you for while he is on leave/s for the above period.";
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
							//echo 'helo1'.$content; die;
							sendmail($MailTo, $Subject, $content);	
							
						}
					}
				}
				//die;
				//$getReportingHeadDetails = $objEmployee->fnGetReportingHeadDetails($_POST['employeeid']);
				//$getManagerDetails= $objEmployee->fnGetReportingHeadDetails($getReportingHeadDetails['teamleaderid']);

				//print_r($getManagerDetails);
				//print_r($LeaveInfo);
				//$mailReceiver =

				$date = date('Y-m-d');
				/*if($post['status_manager'] != '0')
				{
					$currentdate = date('Y-m-d');
					$newArrData = array('id'=>$post['hdnid'],'status' => $post['status'],'comment' => $post['comment'],'approved_date' => $post['approved_date'],'approved_date'=>$date,'comment_manager'=>$post['comment_manager'],'status_manager'=>$post['status_manager'],'approved_date_manager'=>$currentdate);
				}
				else
				{
					$newArrData = array('id'=>$post['hdnid'],'status' => $post['status'],'comment' => $post['comment'],'approved_date' => $post['approved_date'],'comment_manager'=>$post['comment_manager'],'status_manager'=>$post['status_manager'],'approved_date_manager'=>$currentdate);
				}*/

			//echo '<pre>'; print_r($post); die;
			if($post['status_manager'] == '0')
			{
				$newArrData = array('id'=>$post['hdnid'],'status' => $post['status'],'comment' => $post['comment'],'approved_date' => $post['approved_date'],'comment_manager'=>$post['comment_manager'],'status_manager'=>$post['status_manager'],'approved_date_manager'=>$currentdate,'delegate'=>$post['delegate'],'manager_delegate'=>$post['manager_delegate'],'manager_delegate_status'=>$post['manager_delegate_status'],'manager_delegate_comment'=>$post['manager_delegate_comment']);
			}
			else if($post['status_manager'] == '1')
			{
				$currentdate = date('Y-m-d');
				$newArrData = array('id'=>$post['hdnid'],'status' => $post['status'],'comment' => $post['comment'],'approved_date' => $post['approved_date'],'approved_date'=>$date,'comment_manager'=>$post['comment_manager'],'status_manager'=>$post['status_manager'],'approved_date_manager'=>$currentdate,'delegate'=>$post['delegate'],'manager_delegate'=>$post['manager_delegate'],'manager_delegate_status'=>$post['manager_delegate_status'],'manager_delegate_comment'=>$post['manager_delegate_comment'],'manager_delegate_date'=>$currentdate);
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
				//echo 'hello';
				$currentdate = date('Y-m-d');
				$newArrData = array('id'=>$post['hdnid'],'status' => $post['status'],'comment' => $post['comment'],'approved_date' => $post['approved_date'],'approved_date'=>$date,'comment_manager'=>$post['comment_manager'],'status_manager'=>$post['status_manager'],'approved_date_manager'=>$currentdate,'delegate'=>$post['delegate'],'delegate_comment'=>$post['delegate_comment'],'delegate_status'=>$post['delegate_status']);
			}
//print_r($post); die;
				//print_r($newArrData); die;
				$this->updateArray('pms_leave_form',$newArrData);

				/* changes in attendance when approve / unapprove */
				if($_SESSION['designation'] == '6' || $_SESSION['designation'] == '18' || $_SESSION['designation'] == '19')
				{
					include_once('class.attendance.php');

					$objAttendance = new attendance();

					$LeaveInfo = $this->fnLeaveInfoById($post["hdnid"]);
					if($LeaveInfo["isemergency"] == "1")
					{
						if($LeaveInfo["status_manager"] == "1")
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
							$arrInfo["leave_id"] = $this->fnGetLeaveTypeIdByTitle("A");

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
			//echo '<pre>'; print_r($post); die;
			if($_SESSION["usertype"] == "employee")
			{
				include_once('class.employee.php');
				$objEmployee = new employee();

				$status = array("0"=>"Pending", "1"=>"Approved", "2"=>"Unapproved");

				$LeaveInfo = $this->fnLeaveInfoById($post["hdnid"]);

				if($_SESSION['designation'] == '6' || $_SESSION['designation'] == '18' || $_SESSION['designation'] == '19')
				{
					$ManagerInfo = $objEmployee->fnGetEmployeeById($_SESSION['id']);


					$MailTo = $LeaveInfo["email"];
					$Subject = "Leave Request ".$status[$post["status"]];

					$content = "Dear ".$LeaveInfo["name"].",<br><br>";
					$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." your leave request on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
					sendmail($MailTo, $Subject, $content);

					if($LeaveInfo["teamleader"] != 0)
					{
						$TeamleaderInfo = $objEmployee->fnGetEmployeeById($LeaveInfo["teamleader"]);

						$MailTo = $TeamleaderInfo["email"];

						$content = "Dear ".$TeamleaderInfo["name"].",<br><br>";
						$content .= $ManagerInfo["name"]." has ".$status[$post["status_manager"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
						//echo $content;
						sendmail($MailTo, $Subject, $content);
					}
				}

				//print_r($post); die;
				if($_SESSION['designation'] == '7' || $_SESSION['designation'] == '13')
				{
					$TeamleaderInfo = $objEmployee->fnGetEmployeeById($_SESSION["id"]);
					//print_r($TeamleaderInfo); die;
					$MailTo = $LeaveInfo["email"];
					$Subject = "Leave Request ".$status[$post["status"]];

					$content = "Dear ".$LeaveInfo["name"].",<br><br>";
					$content .= $TeamleaderInfo["name"]." has ".$status[$post["status"]]." your Leave request on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
					sendmail($MailTo, $Subject, $content);

					if($TeamleaderInfo["teamleader"] != 0)
					{
						$ManagerInfo = $objEmployee->fnGetEmployeeById($TeamleaderInfo["teamleader"]);

						$MailTo = $ManagerInfo["email"];

						$content = "Dear ".$ManagerInfo["name"].",<br><br>";
						$content .= $TeamleaderInfo["name"]." has ".$status[$post["status"]]." leave request of ".$LeaveInfo["name"]." on ".$LeaveInfo["leavedate"]." from ".$LeaveInfo["startdate"]." to ".$LeaveInfo["enddate"].".<br/><br/>";
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}
				}

				//$getReportingHeadDetails = $objEmployee->fnGetReportingHeadDetails($_POST['employeeid']);
				//$getManagerDetails= $objEmployee->fnGetReportingHeadDetails($getReportingHeadDetails['teamleaderid']);

				//print_r($getManagerDetails);
				//print_r($LeaveInfo);
				//$mailReceiver =

				$date = date('Y-m-d');
				/*if($post['status_manager'] != '0')
				{
					$currentdate = date('Y-m-d');
					$newArrData = array('id'=>$post['hdnid'],'status' => $post['status'],'comment' => $post['comment'],'approved_date' => $post['approved_date'],'approved_date'=>$date,'comment_manager'=>$post['comment_manager'],'status_manager'=>$post['status_manager'],'approved_date_manager'=>$currentdate);
				}
				else
				{
					$newArrData = array('id'=>$post['hdnid'],'status' => $post['status'],'comment' => $post['comment'],'approved_date' => $post['approved_date'],'comment_manager'=>$post['comment_manager'],'status_manager'=>$post['status_manager'],'approved_date_manager'=>$currentdate);
				}*/

				if($post['status_manager'] == '0')
				{
					$newArrData = array('id'=>$post['hdnid'],'status' => $post['status'],'comment' => $post['comment'],'approved_date' => $post['approved_date'],'comment_manager'=>$post['comment_manager'],'status_manager'=>$post['status_manager'],'approved_date_manager'=>$currentdate,'delegate'=>$post['delegate']);
				}
			else if($post['status_manager'] == '1')
			{
				$currentdate = date('Y-m-d');
					$newArrData = array('id'=>$post['hdnid'],'status' => $post['status'],'comment' => $post['comment'],'approved_date' => $post['approved_date'],'approved_date'=>$date,'comment_manager'=>$post['comment_manager'],'status_manager'=>$post['status_manager'],'approved_date_manager'=>$currentdate,'delegate'=>$post['delegate']);
			}
		else {
			$currentdate = date('Y-m-d');
					$newArrData = array('id'=>$post['hdnid'],'status' => $post['status'],'comment' => $post['comment'],'approved_date' => $post['approved_date'],'approved_date'=>$date,'comment_manager'=>$post['comment_manager'],'status_manager'=>$post['status_manager'],'approved_date_manager'=>$currentdate,'delegate'=>$post['delegate']);
			}

				//print_r($newArrData); die;
				$this->updateArray('pms_half_leave_form',$newArrData);
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
			$query = "SELECT *,DATE_FORMAT(leaves.start_date,'%d-%m-%Y') as startdate, DATE_FORMAT(leaves.start_date,'%Y-%m-%d') as start_dt, DATE_FORMAT(leaves.date,'%d-%m-%Y') as leavedate,DATE_FORMAT(leaves.end_date,'%d-%m-%Y') as enddate FROM `pms_leave_form` AS leaves INNER JOIN `pms_employee` AS employee ON leaves.employee_id = employee.id WHERE leaves.`id` = '".mysql_real_escape_string($id)."' ";
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

		function fnDisableLeaveUpdation($EmployeeId, $TillDate)
		{
			$sSQL = "update pms_leave_form set isactive='1' where employee_id='".mysql_real_escape_string($EmployeeId)."' and date_format(start_date,'%Y-%m-%d') <= '".mysql_real_escape_string($TillDate)."'";
			$this->query($sSQL);
		}

		function fnSaveEmergencyLeave($arrLeaveInfo)
		{
			$leaveExist = $this->fnCheckEmergencyLeaveExist($arrLeaveInfo); 
			if($leaveExist > 0)
			{
			//	echo 'hello'; die;
					header("Location: emergency_leave_list.php?info=exist");
					exit;
			}
			else
			{
				//echo 'hello1'; die;
				$this->insertArray("pms_leave_form",$arrLeaveInfo);
				return true;
			}
		}

		function fnCheckEmergencyLeaveExist($data)
		{
			$sSQL = "select count(id) as count from pms_leave_form where employee_id ='".$data['employee_id']."' and '".$data['start_date']."' between date_format(start_date,'%Y-%m-%d') and date_format(end_date,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1'))";
			
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
			
			if($_SESSION["designation"] == "6" || $_SESSION["designation"] == "18" || $_SESSION["designation"] == "19")
			{
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
			}
			else if ($_SESSION["designation"] == "7" || $_SESSION["designation"] == "13")
			{
				/* Get delegated teamleader id */
				$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);	
				
				if(count($arrDelegatedTeamLeaderId) > 0 )
				{
					foreach($arrDelegatedTeamLeaderId as $delegatesIds)
					{
						$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
						$arrEmployees = $arrEmployees + $arrtemp;
					}
				}
			}
			
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
			$sSQL = "select h.* from pms_half_leave_form h INNER JOIN pms_employee e ON e.id = h.employee_id where h.employee_id='".mysql_real_escape_string($EmployeeId)."' and date_format(h.start_date,'%Y-%m-%d') = '".mysql_real_escape_string($Date)."' and h.status_manager='1'";
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
	}
?>

<?php
include_once('db_mysql.php');
	class calculation extends DB_Sql
	{
		function __construct()
		{
		}

		/*function fnGetTotalBreackExceeds($id,$month,$year,$des)
		{
			$breack_exceed = '';
			if($des == '7' || $des == '13')
			{
				$query = "SELECT *,COUNT(isExceededBreak) as count_break_exceed FROM `pms_attendance` WHERE DATE_FORMAT(date,'%m-%Y') = '".$month."-".$year."' AND user_id = '$id' AND `isExceededBreak` = 1 AND `ishoursapproved`=0 and `total_working_hours` < '07:20:00'";
			}
			else
			{
				$query = "SELECT *,COUNT(isExceededBreak) as count_break_exceed FROM `pms_attendance` WHERE DATE_FORMAT(date,'%m-%Y') = '".$month."-".$year."' AND user_id = '$id' AND `isExceededBreak` = 1 AND `ishoursapproved`=0 ";
			}
			
			//$query = "SELECT * FROM `pms_attendance` WHERE ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$breack_exceed = $this->f("count_break_exceed");
				}
			}
			return $breack_exceed;
		}


		function fnGetTotalLateComings($id,$month,$year,$des)
		{
			$late_comings = '';
			if($des == '7' || $des == '13')
			{
				$query = "SELECT COUNT(is_late) as late_comings FROM `pms_attendance` WHERE DATE_FORMAT(date,'%m-%Y') = '".$month."-".$year."' AND user_id = '$id' AND `is_late` = 1 AND `ishoursapproved`=0 and `total_working_hours` < '07:20:00'";
			}
			else
			{
				$query = "SELECT COUNT(is_late) as late_comings FROM `pms_attendance` WHERE DATE_FORMAT(date,'%m-%Y') = '".$month."-".$year."' AND user_id = '$id' AND `is_late` = 1 AND `ishoursapproved`=0 and `total_working_hours` < '07:20:00' AND `ishoursapproved`=0";
			}
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$late_comings = $this->f("late_comings");
				}
			}
			return $late_comings;
		}

		function fnGetTotalPresents($id,$month,$year)
		{
			$late_comings = '';

			$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(date,'%m-%Y') = '".$month."-".$year."' AND at.user_id = '$id' and (`total_working_hours`	 > '06:00:00')";
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$total_in_time_count = $this->f("total_in_time_count");
				}
			}
			//echo 'hello'; die;
			//echo count($arrAttendanceValues);
			//die;
			//print_r($arrAttendanceValues); die;
			return $total_in_time_count;
		}

		function fnGetTotalLeave($id,$month,$year,$leaveType)
		{
			$leave = '';
			if($leaveType == 'ppl')
			{
				$leave_id = 1;
			}
			else if($leaveType == 'ph')
			{
				$leave_id = 10;
			}
			else if($leaveType == 'upl')
			{
				$leave_id = 2;
			}
			else if($leaveType == 'phl')
			{
				$leave_id = 4;
			}
			else if($leaveType == 'uhl')
			{
				$leave_id = 5;
			}
			else if($leaveType == 'wo')
			{
				$leave_id = 9;
			}
			else if($leaveType == 'ha')
			{
				$leave_id = 12;
			}
			else if($leaveType == 'hlwp')
			{
				$leave_id = 8;
			}
			else if($leaveType == 'ulwp')
			{
				$leave_id = 7;
			}
			else if($leaveType == 'plwp')
			{
				$leave_id = 6;
			}
			else if($leaveType == 'a')
			{
				$leave_id = 3;
			}
			else if($leaveType == 'smplt')
			{
				$leave_id = 11;
			}
			echo $query = "SELECT COUNT(leave_id) as leaves FROM `pms_attendance` WHERE DATE_FORMAT(date,'%m-%Y') = '".$month."-".$year."' AND user_id = '$id' AND `leave_id` = '$leave_id'";
			//$query = "SELECT * FROM `pms_attendance` WHERE ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$leave = $this->f("leaves");
				}
			}
			//echo 'hello'; die;
			//echo count($arrAttendanceValues);
			//die;
			//print_r($arrAttendanceValues); die;
			return $leave;
		}*/


		function fnGetTotalLeaveAvails($id)
		{
			$leave_avail = '';
			$query = "SELECT leave_bal  FROM `pms_employee` WHERE id = '$id'";
			//$query = "SELECT * FROM `pms_attendance` WHERE ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$leave_avail = $this->f("leave_bal");
				}
			}
			//echo 'hello'; die;
			//echo count($arrAttendanceValues);
			//die;
			//print_r($arrAttendanceValues); die;
			return $leave_avail;
		}

		function fnGetTotalOpeningLeaves($id)
		{
			$leave_avail = '';
			$query = "SELECT opening_leave_balance  FROM `pms_employee` WHERE id = '$id'";
			//$query = "SELECT * FROM `pms_attendance` WHERE ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$leave_avail = $this->f("opening_leave_balance");
				}
			}
			//echo 'hello'; die;
			//echo count($arrAttendanceValues);
			//die;
			//print_r($arrAttendanceValues); die;
			return $leave_avail;
		}

		/*function fnInsertSummary($Data)
		{
			//echo '<pre>'; print_r($Data); die;
			$this->insertArray('pms_attendance_report',$Data);
			return true;
		}*/

		function fnInsertHalfSummary($Data,$month,$year)
		{
			$Data['month'] = $month;
			$Data['year'] = $year;

			$this->insertArray('pms_leave_history',$Data);

			//$this->insertArray('pms_half_month_attendance_report',$Data);
			return true;
		}
		
		
		function fnInsertHalfSummaryLog($Data,$month,$year)
		{
			$Data['month'] = $month;
			$Data['year'] = $year;

			$this->insertArray('pms_leave_history_log',$Data);

			//$this->insertArray('pms_half_month_attendance_report',$Data);
			return true;
		}

		function fnUpdateHalfSummary($id,$Data,$month,$year)
		{
			//echo '<pre>'; print_r($Data); die;
			$Data['id'] = $id;
			$Data['month'] = $month;
			$Data['year'] = $year;
			$this->updateArray('pms_leave_history',$Data);
			//$this->updateArray('pms_half_month_attendance_report',$Data);
			return true;
		}
/*		function fnUpdateSummary($id,$Data)
		{
			//echo '<pre>'; print_r($Data); die;
			$Data['id'] = $id;
			$this->updateArray('pms_attendance_report',$Data);
			return true;
		}
*/
		function fnGetEmployees()
		{
			$arrEmployee = '';
			$query = "SELECT * FROM pms_employee";
			$this->query($query);

			if($this->num_rows() >0 )
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchrow();
				}
			}
			//print_r($arrEmployee);
			return $arrEmployee;
		}

		function fnCheckExistence($eid,$month,$year)
		{
			$id = '';
			$query = "SELECT id FROM `pms_leave_history` WHERE `emp_id` = '$eid' AND `month` ='$month' AND `year` = '$year'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$id = $this->f("id");
				}
			}
			return $id;
		}
		
		function fnCheckExistenceMonthlyReport($eid,$month,$year)
		{
			$id = '';
			$query = "SELECT id FROM `pms_attendance_report` WHERE `employee_id` = '$eid' AND `month` ='$month' AND `year` = '$year'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$id = $this->f("id");
				}
			}
			return $id;
		}

		function fnCheckHalfExistence($eid,$month,$year)
		{
			$id = '';
			$query = "SELECT id FROM  `pms_leave_history` WHERE `emp_id` = '$eid' AND `month` ='$month' AND `year` = '$year' and ishalfmonthly = '1'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$id = $this->f("id");
				}
			}
			return $id;
		}

	function fnUpdateRemainingLeave($id,$leaves)
		{
			$Data = array("leave_bal"=>$leaves,"id"=>$id);
			$this->updateArray('pms_employee',$Data);
			return true;
		}
	/*
		function fnGetAllCalculation($month,$year)
		{
			$arrAllCalculation = array();
			$query = "SELECT r.*,e.name as employee_name FROM `pms_attendance_report` AS r INNER JOIN `pms_employee` AS e ON e.id = r.employee_id WHERE r.month = '$month' AND r.year = '$year'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllCalculation[] = $this->fetchrow();
				}
			}
			//print_r($arrAllCalculation);
			return $arrAllCalculation;
		}

		function fnGetAllHalfCalculation($month,$year)
		{
			$arrAllCalculation = array();
			$query = "SELECT lh.*,e.name as employee_name FROM `pms_leave_history` AS lh INNER JOIN `pms_employee` AS e ON e.id = lh.emp_id WHERE lh.month = '$month' AND lh.year = '$year'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllCalculation[] = $this->fetchrow();
				}
			}
			//print_r($arrAllCalculation);
			return $arrAllCalculation;
		}

		function fnGetWeekOfDates($eid,$month,$year)
		{
			$arrAllweekOffs = array();
			$query = "SELECT date FROM `pms_attendance` WHERE DATE_FORMAT(date,'%m-%Y') = '".$month."-".$year."' AND user_id = '$eid' AND leave_id IN(9)";
			$this->query($query);

			if($this->num_rows() >0 )
			{
				while($this->next_record())
				{
					$arrAllweekOffs[] = $this->fetchrow();
				}
			}
			//print_r($arrEmployee);
			return $arrAllweekOffs;
		}

		function fnGetPHOfDates($eid,$month,$year)
		{
			$arrAllPublicHolidays = array();
			$query = "SELECT date FROM `pms_attendance` WHERE DATE_FORMAT(date,'%m-%Y') = '".$month."-".$year."' AND user_id = '$eid' AND leave_id IN(10)";
			$this->query($query);

			if($this->num_rows() >0 )
			{
				while($this->next_record())
				{
					$arrAllPublicHolidays[] = $this->fetchrow();
				}
			}
			//print_r($arrEmployee);
			return $arrAllPublicHolidays;
		} */

		function fnGetAllHalfCalculation($month,$year)
		{
			$arrAllCalculation = array();
			$query = "SELECT lh.*,e.name as employee_name FROM `pms_leave_history` AS lh INNER JOIN `pms_employee` AS e ON e.id = lh.emp_id WHERE lh.month = '$month' AND lh.year = '$year'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllCalculation[] = $this->fetchrow();
				}
			}
			//print_r($arrAllCalculation);
			return $arrAllCalculation;
		}
		
		function fnCheckLeaveExistence($uid,$date)
		{
			$leave_id = '';
			$query = "SELECT status_manager,status,manager_delegate_status FROM `pms_leave_form` WHERE `employee_id`='$uid' AND DATE_FORMAT('$date','%Y-%m-%d') between DATE_FORMAT(`start_date`,'%Y-%m-%d') AND DATE_FORMAT(`end_date`,'%Y-%m-%d')";
			$this->query($query);

			if($this->num_rows() >0 )
			{
				if($this->next_record())
				{
					$manager_status = $this->f('status_manager');
					$teamleader_status = $this->f('status');
					$delegate_manager_status = $this->f('manager_delegate_status');
				}
			}
			//echo 'manager_status---'.$manager_status;
			//echo 'teamLeader_status---'.$teamleader_status;
			if($manager_status == 1 || ($delegate_manager_status == 1 && $manager_status == 0))
			{
				$final_status = 'approved';
			}
			else if($manager_status != '2' && $teamleader_status == '1')
			{
				$final_status = 'approved';
			}
			else
			{
				$final_status = 'unapproved';
			}
		//echo $final_status;
			return $final_status;
		} 
		function fnCheckPreviousDateLeaveId($uid,$date,$choose)
		{
			$leave_id = '';
			//echo $date;
			$query = "select in_time as intime,out_time as outtime,leave_id as leaveid from pms_attendance where DATE_FORMAT(date,'%Y-%m-%d')=DATE_FORMAT('$date','%Y-%m-%d') and user_id ='$uid'";
			$this->query($query);

			if($this->num_rows() >0 )
			{
				if($this->next_record())
				{
					$leave_id = $this->f('leaveid');
				}
			}
			//echo 'leave_id'.$leave_id.'<br>';
			if($leave_id == 10)
			{
				//echo '<br>ph loop<br>';
				if($choose == 'prev')
				{
					$prev_date = date('Y-m-d', strtotime('-1 day', strtotime($date)));
					$leave_id = $this->fnCheckPreviousDateLeaveId($uid,$prev_date,'prev');
					if($leave_id != 10)
					{
						return $leave_id;
					}
				}
				else
				{
					$next_date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
					$leave_id = $this->fnCheckPreviousDateLeaveId($uid,$next_date,'next');
					if($leave_id != 10)
					{
						return $leave_id;
					}
				}
			}
			else
			{
				//echo '--------leave_id--------'.$leave_id;

				return $leave_id;
			}
		} 
		function fnCheckDate($uid,$date)
		{
			$leave = array(1,2,3,6,7);
			$arrPrevDate = array();
			$query = "select in_time as intime,out_time as outtime,leave_id as leaveid from pms_attendance where DATE_FORMAT(date,'%Y-%m-%d')='$date' and user_id ='$uid'";
			$this->query($query);

			if($this->num_rows() >0 )
			{
				if($this->next_record())
				{
					$arrPrevDate = $this->fetchrow();
				}
			}
			//print_r($arrPrevDate);
			if(in_array($arrPrevDate['leaveid'], $leave))
				{
					return 'leave';
				}
			else if($arrPrevDate['leaveid'] == 10)
				{
					return 'ph';
				}
			else
				{
					return 'present';
				}
		}

		function fnCheckNextDate($userid,$cur,$next,$temp)
		{

			$previous_date = $prev;
			$arrLeaves = array();

			$checkNextDay = $this->fnCheckDate($userid,$next);
			//echo 'next----'.$next.'--------checkNextDay------'.$checkNextDay.'.....';echo '<br>';
			if($checkNextDay == 'ph')
			{
				//echo 'first--';
				$arrLeaves[] = $next;
				$arrLeaves[] = $cur;
				$temp = $arrLeaves;
				$next = date('Y-m-d', strtotime('+1 day', strtotime($next)));
				$checkNextDay = $this->fnCheckNextDate($userid,'',$next,$temp);

				if($checkNextDay == 'noSendwitch')
				{
					return $checkNextDay;
				}
				else
				{
					return $checkNextDay;
				}

			}
			else if($checkNextDay == 'leave')
			{
				//echo 'second---';
				$arrLeaves[] = $next;
				$temp = array_merge($arrLeaves, $temp);
				//print_r($temp);
				return $temp;
			}
			else if($checkNextDay == 'present')
			{
				//echo 'third-------';

				$sendwitch = 'noSendwitch';

				return $sendwitch;
				exit;
			}

		}



		function fnCheckPrevDate($userid,$prev,$temp1)
		{
			//echo '<br/>--userid--------'.$userid.'--prev-------'.$prev;

			//$prev_date = date('Y-m-d', strtotime('-1 day', strtotime($cur)));
			$arrLeaves = array();
			$checkPrevDay = $this->fnCheckDate($userid,$prev);
			//echo  '<br>'.$prev.'========'.$checkPrevDay.'<br>';
			if($checkPrevDay == 'ph')
			{
				$arrLeaves[] = $prev;
				$temp1 = $arrLeaves;
				$prev = date('Y-m-d', strtotime('-1 day', strtotime($prev)));
				$checkPrevDay = $this->fnCheckPrevDate($userid,$prev,$temp1);
				if($checkPrevDay == 'noSendwitch')
				{
					return $checkPrevDay;
				}
				else
				{
					return $checkPrevDay;
				}
			}
			else if($checkPrevDay == 'leave')
			{
				$arrLeaves[] = $prev;
				$temp1 = array_merge($arrLeaves, $temp1);
				//print_r($temp1);
				return $temp1;
			}
			else if($checkPrevDay == 'present')
			{
				$sendwitch = 'noSendwitch';

				return $sendwitch;
			}
		}

	/*	function fnGetNextDate($cur)
		{
			$next_date = date('Y-m-d', strtotime('+1 day', strtotime($cur)));
			return $next_date;
		}
*/

		

/***********All functions for half_monthly_request.php ******************/

		function fnGetHalfTotalBreaks($id,$day,$month,$year,$des)
		{
			//~ $start_date = date('Y-m-01');
			//~ $end_date = date('Y-m-t');
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.$day;
			$breack_exceed = '';
			if($des == '7' || $des == '13')
			{
				$query = "SELECT COUNT(isExceededBreak) as count_break_exceed FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$id' AND `isExceededBreak` = 1 AND `ishoursapproved`=0 and `total_working_hours` < '07:20:00'";
			}
			else
			{
				$query = "SELECT COUNT(isExceededBreak) as count_break_exceed FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$id' AND `isExceededBreak` = 1 AND `ishoursapproved`=0";
			}
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$breack_exceed = $this->f("count_break_exceed");
				}
			}
			return $breack_exceed;
		}

		function fnGetHalfTotalLateComings($id,$day,$month,$year,$des)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.$day;
			//~ $start_date = date('Y-m-01');
			//~ $end_date = date('Y-m-t');
			
			$late_comings = '';

			$query = "SELECT COUNT(is_late) as late_comings FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$id' AND `is_late` = 1 AND `late_time` > '00:04:00'";
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$late_comings = $this->f("late_comings");
				}
			}
			//echo 'hello'; die;
			//echo count($arrAttendanceValues);
			//die;
			//print_r($arrAttendanceValues); die;
			if($des == '6' || $des == '18' || $des == '7' || $des == '13' || $des == '19' || $des == '21' || $des == '22' || $des == '23' || $des == '24' || $des == '25' || $des == '27' || $des == '28' || $des == '20' || $des == '26' || $des == '44')
			{
				$late_comings = 0;
				return $late_comings;
			}
			else
			{
				return $late_comings;
			}
			
		}

		function fnGetHalfTotalPresents($id,$day,$month,$year,$des)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.$day;
			//~ $start_date = date('Y-m-01');
			//~ $end_date = date('Y-m-t');
			$late_comings = '';
			
			if($des == '7' || $des == '13')
			{
				$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and ((`total_working_hours`	 > '06:40:00') or ishoursapproved='1') and leave_id not in(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15)";
			}
			else if($des == '6' || $des == '18' || $des == '19' || $des == '44')
			{
				$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (`total_working_hours`	 >= '04:00:00') and leave_id not in(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15)";
			}
			else if($des == '21' || $des == '22' || $des == '23' || $des == '24' || $des == '25' || $des == '26' || $des == '27' || $des == '28')
			{
				$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (`total_working_hours`	 >= '07:30:00') and leave_id not in(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15)";
			}
			else
			{
				$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (`total_working_hours`	 > '06:40:00') and leave_id not in(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15)";
			}
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$total_in_time_count = $this->f("total_in_time_count");
					//$timediff = $this->f("diff");
				}
			}
			$checkShiftMovementDays = $this->fnGetHalfTotalOfficialShiftMovementDays($id,$day,$month,$year);
			
			$totalShiftMovementTaken = $checkShiftMovementDays['total'];

			$approvedShiftMovements = $checkShiftMovementDays['approved'];

			$unApprovedShiftMovements = $totalShiftMovementTaken - $checkShiftMovementDays['approved'];

			$DeductedUnApprovedShiftMovement = $unApprovedShiftMovements * .5;

		
			$checkHalfDays = $this->fnGetTotalHalfDays($id,$month,$year);
			$present_days_in_shift_movements = $totalShiftMovementTaken - ($unApprovedShiftMovements*.5);

			$total_full_days = ($total_in_time_count + $totalShiftMovementTaken);

			return $total_full_days;
		}

		function fnGetHalfTotalOfficialShiftMovementDays($id,$day,$month,$year)
		{
			$total_in_time_count = array();
			$total_shift_movements = array();
			//~ $start_date = $year.'-'.$month.'-'.'01';
			//~ $end_date = $year.'-'.$month.'-'.$day;

			$start_date = date('Y-m-01');
			$end_date = date('Y-m-t');


			$query = "SELECT at.* FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and at.official_total_working_hours >= '05:20:00'  AND at.`leave_id` in(14,11)";

			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$total_in_time_count[] = $this->fetchrow("total_in_time_count");
				}
			}

			$total_shift_changes = count($total_in_time_count);
			//print_r($total_in_time_count);
			if(count($total_in_time_count) > 0 )
			{
				$total_count = 0;
				foreach($total_in_time_count as $total)
				{
					$query1 = "SELECT *,count(id) as count FROM `pms_shift_movement` WHERE DATE_FORMAT( `movement_date` , '%d-%m-%Y' ) = DATE_FORMAT( '".$total['date']."', '%d-%m-%Y' ) and `approvedby_manager` = 1";
					$this->query($query1);
					if($this->num_rows() > 0)
					{
						if($this->next_record())
						{
							$count = $this->f("count");
						}
					}
					if($count > 0)
					{
						$total_count++;
					}
				}
			}
			else
			{
				return 0;
			}
			$approved_shift_changes = $total_count;
			$final_approved_shift_changes = $total_shift_changes - $approved_shift_changes;
			/*echo '<br>total_shift_changes---------'.$total_shift_changes;
			echo '<br>approved_shift_changes---------'.$approved_shift_changes;
			echo '<br>final_approved_shift_changes---------'.$final_approved_shift_changes;*/
			$shiftStatus = array("total"=>$total_shift_changes,"approved"=>$approved_shift_changes);
			//echo '<pre>';print_r($shiftStatus); die;
			return $shiftStatus;

		}

		function fnGetTotalHalfDays($id,$month,$year)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.'15';

			//$query = "SELECT COUNT( in_time ) AS total_in_time_count,TIMEDIFF(at.`out_time`,at.`in_time`) as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (TIMEDIFF(at.`out_time`,at.`in_time`) >= '04:00:00' and TIMEDIFF(at.`out_time`,at.`in_time`) < '07:45:00') AND  at.`leave_id` NOT IN ( 13, 5, 6 ) and is_late = 0";
			//$query = "SELECT COUNT( in_time ) AS total_in_time_count,TIMEDIFF(at.`out_time`,at.`in_time`) as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (TIMEDIFF(at.`out_time`,at.`in_time`) >= '04:00:00' and TIMEDIFF(at.`out_time`,at.`in_time`) < '07:45:00') AND  at.`leave_id` NOT IN ( 13, 5, 6 ) and is_late = 0";
			$query = "SELECT COUNT( in_time ) AS total_in_time_count,TIMEDIFF(at.`out_time`,at.`in_time`) as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and at.official_total_working_hours >= '04:00:00' AND  at.`leave_id` NOT IN ( 13, 5, 6 ) and is_late = 0";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$total_in_time_count = $this->f("total_in_time_count");
				}
			}
			return $total_in_time_count;
		}


		function fnGetHalfTotalLeaveByIntimeOut($id,$month,$year)
		{
			//~ $start_date = $year.'-'.$month.'-'.'01';
			//~ $end_date = $year.'-'.$month.'-'.$day;
			$start_date = date('Y-m-01');
			$end_date = date('Y-m-t');


			$query = "SELECT COUNT( in_time ) AS total_in_time_count,TIMEDIFF(at.`out_time`,at.`in_time`) as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (TIMEDIFF(at.`out_time`,at.`in_time`) >= '04:00:00' and TIMEDIFF(at.`out_time`,at.`in_time`) < '07:45:00') AND  at.`leave_id` NOT IN ( 13, 5, 6 ) and is_late = 0";
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$total_in_time_count = $this->f("total_in_time_count");
				}
			}
			//echo 'total_in_time_count'.$total_in_time_count;
			return $total_in_time_count;
		}

		function fnGetHalfTotalLeave($id,$day,$month,$year,$leaveType)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.$day;
			$leave = '';
			if($leaveType == 'ppl')
			{
				$leave_id = 1;
			}
			else if($leaveType == 'ph')
			{
				$leave_id = 10;
			}
			else if($leaveType == 'upl')
			{
				$leave_id = 2;
			}
			else if($leaveType == 'phl')
			{
				$leave_id = 4;
			}
			else if($leaveType == 'uhl')
			{
				$leave_id = 5;
			}
			else if($leaveType == 'wo')
			{
				$leave_id = 9;
			}
			else if($leaveType == 'ha')
			{
				$leave_id = 12;
			}
			else if($leaveType == 'hlwp')
			{
				$leave_id = 8;
			}
			else if($leaveType == 'ulwp')
			{
				$leave_id = 7;
			}
			else if($leaveType == 'plwp')
			{
				$leave_id = 6;
			}
			else if($leaveType == 'a')
			{
				$leave_id = 3;
			}
			else if($leaveType == 'smplt')
			{
				$leave_id = 11;
			}
			$query = "SELECT COUNT(leave_id) as leaves FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$id' AND `leave_id` = '$leave_id'";
			//$query = "SELECT * FROM `pms_attendance` WHERE ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$leave = $this->f("leaves");
				}
			}
			//echo 'hello'; die;
			//echo count($arrAttendanceValues);
			//die;
			//print_r($arrAttendanceValues); die;
			return $leave;
		}
		
		function fnGetHalfWeekOfDates($eid,$day,$month,$year)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.$day;
			$arrAllweekOffs = array();
			$query = "SELECT date FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$eid' AND leave_id IN(9)";
			$this->query($query);

			if($this->num_rows() >0 )
			{
				while($this->next_record())
				{
					$arrAllweekOffs[] = $this->fetchrow();
				}
			}
			//print_r($arrEmployee);
			return $arrAllweekOffs;
		}
		
		function fnInsertHalfMonthReport($arrData)
		{
			//echo 'here'; print_r($arrData); die;
			$query = "select id from `pms_half_month_report` where employee_id ='".$arrData['employee_id']."' and month ='".$arrData['month']."' and year ='".$arrData['year']."' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$DepartmentId = $this->f("id");
				}
			}
			//echo 'hello'.$DepartmentId;
			if($DepartmentId != '')
			{
				//echo 'hello';
				$arrData['id'] = $DepartmentId;
				$this->updateArray('pms_half_month_report',$arrData);
			}
			else
			{
				//echo 'hello1';
				$this->insertArray('pms_half_month_report',$arrData);
			}
			
		}

		function fnGetAllLeaveRecord($id,$month,$year)
		{
			//echo 'id--'.$id.'month---'.$month.'year--'.$year;
			$arrLeaveRecord = array();
			$query = "SELECT `opening_leave`,`closing_leave` FROM `pms_leave_history` WHERE `id` = '".mysql_real_escape_string($id)."' and `ishalfmonthly` = '1' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrLeaveRecord = array("opening"=>$this->f('opening_leave'),"closing"=>$this->f('closing_leave'));
				}
			}
			return $arrLeaveRecord;
			
		}

		/***********All functions for monthly_report.php ******************/

		function fnHalfMonthlyLeaveEarned($id,$month,$year)
		{
			$noOfLeaves = 0;
			$sSQL = "select added_no_of_leaves from pms_leave_history where emp_id='".mysql_real_escape_string($id)."' and month='".mysql_real_escape_string($month)."' and year='".mysql_real_escape_string($year)."' and ishalfmonthly = '1'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$noOfLeaves = $this->f("added_no_of_leaves");
				}
			}
			
			return $noOfLeaves;
		}

		function fnGetTotalBreaks($id,$month,$year,$des)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = date("Y-m-t", strtotime($start_date));

			$breack_exceed = 0;

			if($des == '7' || $des == '13')
			{
				$query = "SELECT COUNT(isExceededBreak) as count_break_exceed FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$id' AND `isExceededBreak` = 1 AND `ishoursapproved`=0 and `total_working_hours` < '07:20:00'";
			}
			else
			{
				$query = "SELECT COUNT(isExceededBreak) as count_break_exceed FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$id' AND `isExceededBreak` = 1 AND `ishoursapproved`=0";
			}
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$breack_exceed = $this->f("count_break_exceed");
				}
			}
			return $breack_exceed;
		}
		
		function fnGetTotalLateComings($id,$month,$year,$des)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = date("Y-m-t", strtotime($start_date));

			$late_comings = 0;
			/*if($des == '7' || $des == '13')
			{
				echo $query = "SELECT COUNT(is_late) as late_comings FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$id' AND `is_late` = 1 AND `ishoursapproved`=0 and `total_working_hours` < '07:20:00'";
			}
			else
			{
				echo $query = "SELECT COUNT(is_late) as late_comings FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$id' AND `is_late` = 1 AND `ishoursapproved`=0";
			}*/

			$query = "SELECT COUNT(is_late) as late_comings FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$id' AND `is_late` = 1 AND `late_time` > '00:04:00'";
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$late_comings = $this->f("late_comings");
				}
			}
			
			if($des == '6' || $des == '18' || $des == '7' || $des == '13' || $des == '19' || $des == '21' || $des == '22' || $des == '23' || $des == '24' || $des == '25' || $des == '27' || $des == '28' || $des == '20' || $des == '26' || $des == '44')
			{
				$late_comings = 0;
			}

			return $late_comings;
		}

		function fnGetTotalPresents($id,$month,$year,$des=0)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = date("Y-m-t", strtotime($start_date));
			
			$late_comings = '';
			
			if($des == '7' || $des == '13')
			{
				$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and ((`total_working_hours`	 > '06:40:00') or ishoursapproved='1') and leave_id not in(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15)";
			}
			else if($des == '6' || $des == '18' || $des == '19' || $des == '44')
			{
				$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (`total_working_hours`	 >= '04:00:00') and leave_id not in(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15)";
			}
			else if($des == '21' || $des == '22' || $des == '23' || $des == '24' || $des == '25' || $des == '26' || $des == '27' || $des == '28')
			{
				$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (`total_working_hours`	 >= '07:30:00') and leave_id not in(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15)";
			}
			else
			{
				$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (`total_working_hours`	 > '06:40:00') and leave_id not in(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15)";
			}
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$total_in_time_count = $this->f("total_in_time_count");
					$timediff = $this->f("diff");
				}
			}
			//echo '<br>total_in_time_count:'.$total_in_time_count;
			//$timediff = $this->f("diff");
			$checkShiftMovementDays = $this->fnGetTotalOfficialShiftMovementDays($id,$month,$year);

			//echo '<pre>hello';print_r($checkShiftMovementDays); 

			$totalShiftMovementTaken = $checkShiftMovementDays['total'];

			$approvedShiftMovements = $checkShiftMovementDays['approved'];

			$unApprovedShiftMovements = $totalShiftMovementTaken - $checkShiftMovementDays['approved'];

			$DeductedUnApprovedShiftMovement = $unApprovedShiftMovements * .5;

			//echo $DeductedUnApprovedShiftMovement;

			//$checkHalfDays = $this->fnGetTotalHalfDays1($id,$month,$year);
			$present_days_in_shift_movements = $totalShiftMovementTaken - ($unApprovedShiftMovements*.5);

			//echo '<br><br>total_in_time_count---'.$total_in_time_count.'<br><br>totalShiftMovementTaken----'.$totalShiftMovementTaken.'<br><br>checkHalfDays---'.$checkHalfDays;

			//$total_full_days = ($total_in_time_count + $totalShiftMovementTaken + ($checkHalfDays * .5)) ;
			/* Removed halfday calculation as this is added later in the main file */
			//echo '<br>total_in_time_count-----------'.$total_in_time_count.'<br>totalShiftMovementTaken----'.$totalShiftMovementTaken.'<br><br>';
			$total_full_days = ($total_in_time_count + $approvedShiftMovements);
			//$total_full_days = ($total_in_time_count + $present_days_in_shift_movements);

			//echo 'total_in_time_count--'.$total_in_time_count.'totalShiftMovementTaken--'.$totalShiftMovementTaken.'approvedShiftMovements--'.$approvedShiftMovements.'unApprovedShiftMovements---'.$unApprovedShiftMovements.'total_full_days'.$total_full_days;
			//echo '<br><br>total_full_days----'.$total_full_days.'<br><br>';
			return $total_full_days;
		}

		function fnGetTotalOfficialShiftMovementDays($id,$month,$year)
		{
			$total_count = 0;
			$smPltCount = 0;
			
			$total_in_time_count = array();
			$total_shift_movements = array();
			
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = date("Y-m-t", strtotime($start_date));

			$query213 = "SELECT count(id) as mov_count FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and at.official_total_working_hours >= '05:20:00'  AND at.`leave_id` in(11)";
			$this->query($query213);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$smPltCount = $this->f("mov_count");
				}
			}
//echo '<br>smPltCount'.$smPltCount;

			$query = "SELECT at.* FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and at.official_total_working_hours >= '05:00:00'  AND at.`leave_id` in(14)";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$total_in_time_count[] = $this->fetchrow("total_in_time_count");
				}
			}

			$total_shift_movements = count($total_in_time_count);
			//print_r($total_in_time_count);
			if(count($total_in_time_count) > 0 )
			{
				
				
				foreach($total_in_time_count as $total)
				{
					$mov_id = 0;
					$mov_comp_id = 0;
					//echo '<pre>gagan'.$total['user_id']; print_r($total);
					$query1 = "SELECT id as mov_id FROM `pms_shift_movement` WHERE DATE_FORMAT( `movement_date` , '%Y-%m-%d' ) = DATE_FORMAT( '".$total['date']."', '%Y-%m-%d' ) and (`approvedby_manager` = 1 or (`approvedby_manager` != 2 and delegatedmanager_id != '0' and delegatedmanager_status = '1')) and userid = '".$total['user_id']."' and isCancel='0'";
					$this->query($query1);
					if($this->num_rows() > 0)
					{
						if($this->next_record())
						{
							$mov_id = $this->f("mov_id");
						}
					}


					$query100 = "SELECT id as mov_comp_id FROM `pms_shift_movement_compensation` WHERE shift_movement_id = '$mov_id' and (`approvedby_tl` = 1 or (`approvedby_tl` != 2 and delegatedtl_id != '0' and delegatedtl_status = '1')) and userid = '".$total['user_id']."'";
					$this->query($query100);
					if($this->num_rows() > 0)
					{
						if($this->next_record())
						{
							$mov_comp_id = $this->f("mov_comp_id");
						}
					}

					//echo '<br>mov_comp_id:'.$mov_comp_id;
					if($mov_comp_id != '0' &&  $mov_comp_id != '')
					{
						$total_count += 1;
					}
				}
			}
			
			$total_shift_mov = $total_shift_movements + $smPltCount;
			$official_total_count_w_componsate = $total_count + $smPltCount;
			$approved_shift_movement = $official_total_count_w_componsate;
			//$final_approved_shift_changes = $total_shift_changes - $approved_shift_changes;
			//echo '<br>total_shift_movement---------'.$total_shift_movement;
			//echo '<br>approved_shift_movement---------'.$approved_shift_movement;
			//echo '<br>final_approved_shift_changes---------'.$final_approved_shift_changes;
			$shiftStatus = array("total"=>$total_shift_mov,"approved"=>$approved_shift_movement);
			//echo '<br><pre>shiftStatus';print_r($shiftStatus);
			return $shiftStatus;
		}

		/*function fnGetTotalHalfDays1($id,$month,$year)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = date("Y-m-t", strtotime($start_date));

			//$query = "SELECT COUNT( in_time ) AS total_in_time_count,TIMEDIFF(at.`out_time`,at.`in_time`) as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (TIMEDIFF(at.`out_time`,at.`in_time`) >= '04:00:00' and TIMEDIFF(at.`out_time`,at.`in_time`) < '07:45:00') AND  at.`leave_id` NOT IN ( 13, 5, 6 ) and is_late = 0";
			//echo $query = "SELECT COUNT( in_time ) AS total_in_time_count,TIMEDIFF(at.`out_time`,at.`in_time`) as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and TIMEDIFF(at.`out_time`,at.`in_time`) >= '04:00:00' AND  at.`leave_id` NOT IN ( 13, 5, 6 ) and is_late = 0";
			
			$query = "SELECT COUNT( in_time ) AS total_in_time_count,TIMEDIFF(at.`out_time`,at.`in_time`) as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and at.official_total_working_hours >= '04:00:00' AND  at.`leave_id` NOT IN ( 13, 5, 6 ) and is_late = 0";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$total_in_time_count = $this->f("total_in_time_count");
				}
			}
			return $total_in_time_count;
		}*/

		function fnGetTotalHalfDays1($id,$month,$year,$des)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = date("Y-m-t", strtotime($start_date));

			$count = 0;

			if($des != '6' && $des != '18' && $des != '19' && $des != '44')
			{
				$query = "SELECT COUNT( id ) AS count_halfdays FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and ((at.official_total_working_hours >= '03:30:00' AND at.official_total_working_hours < '06:40:00' and leave_id ='0') or (leave_id in(11,14) and at.official_total_working_hours >= '03:30:00' AND at.official_total_working_hours < '05:00:00'))";
				$this->query($query);

				if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$count = $this->f("count_halfdays");
					}
				}
			}
			return $count;
		}


		/*function fnGetHalfTotalLeaveByIntimeOut($id,$month,$year)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = date("Y-m-t", strtotime($start_date));

			$query = "SELECT COUNT( in_time ) AS total_in_time_count,TIMEDIFF(at.`out_time`,at.`in_time`) as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (TIMEDIFF(at.`out_time`,at.`in_time`) >= '04:00:00' and TIMEDIFF(at.`out_time`,at.`in_time`) < '07:45:00') AND  at.`leave_id` NOT IN ( 13, 5, 6 ) and is_late = 0";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$total_in_time_count = $this->f("total_in_time_count");
				}
			}
			//echo 'total_in_time_count'.$total_in_time_count;
			return $total_in_time_count;
		}*/

		function fnGetTotalLeave($id,$month,$year,$leaveType)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = date("Y-m-t", strtotime($start_date));

			$leave = '';
			if($leaveType == 'ppl')
			{
				$leave_id = 1;
			}
			else if($leaveType == 'ph')
			{
				$leave_id = 10;
			}
			else if($leaveType == 'upl')
			{
				$leave_id = 2;
			}
			else if($leaveType == 'phl')
			{
				$leave_id = 4;
			}
			else if($leaveType == 'uhl')
			{
				$leave_id = 5;
			}
			else if($leaveType == 'wo')
			{
				$leave_id = 9;
			}
			else if($leaveType == 'ha')
			{
				$leave_id = 12;
			}
			else if($leaveType == 'hlwp')
			{
				$leave_id = 8;
			}
			else if($leaveType == 'ulwp')
			{
				$leave_id = 7;
			}
			else if($leaveType == 'plwp')
			{
				$leave_id = 6;
			}
			else if($leaveType == 'a')
			{
				$leave_id = 3;
			}
			else if($leaveType == 'smplt')
			{
				$leave_id = 11;
			}
			$query = "SELECT COUNT(leave_id) as leaves FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$id' AND `leave_id` = '$leave_id'";
			//$query = "SELECT * FROM `pms_attendance` WHERE ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$leave = $this->f("leaves");
				}
			}
			//echo 'hello'; die;
			//echo count($arrAttendanceValues);
			//die;
			//print_r($arrAttendanceValues); die;
			return $leave;
		}
		function fnGetWeekOfDates($eid,$month,$year)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = date("Y-m-t", strtotime($start_date));
			
			$arrAllweekOffs = array();
			$query = "SELECT date FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$eid' AND leave_id IN(9)";
			$this->query($query);

			if($this->num_rows() >0 )
			{
				while($this->next_record())
				{
					$arrAllweekOffs[] = $this->fetchrow();
				}
			}
			//print_r($arrEmployee);
			return $arrAllweekOffs;
		}
		function fnInsertMonthReport($arrData)
		{
			$this->insertArray('pms_attendance_report',$arrData);
			return true;
			
		}
		function fnUpdateMonthReport($arrData)
		{
			$this->updateArray('pms_attendance_report',$arrData);
			return true;
			
		}

		function fnGetMonthlyRecord($id,$month,$year)
		{
			$arrLeaveRecord = array();
			$query = "SELECT `opening_leave`,`closing_leave` FROM `pms_leave_history` WHERE `id` = '".mysql_real_escape_string($id)."' and `ishalfmonthly` = '1' and month = '$month' AND year = '$year'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrLeaveRecord = array("opening"=>$this->f('opening_leave'),"closing"=>$this->f('closing_leave'));
				}
			}
			return $arrLeaveRecord;
		}
		
		function fnGetMonthlyReport($month,$year)
		{
			$arrAllCalculation = array();
			$db = new DB_Sql();
			$query = "SELECT e.name, h.*, e1.name as reporting_head FROM  `pms_attendance_report` h INNER JOIN pms_employee e ON h.employee_id = e.id INNER JOIN pms_employee e1 ON e.teamleader = e1.id WHERE h.month = '$month' AND h.year = '$year' order by e.name";

			//echo $query = "SELECT e.name, h.*,lh.opening_leave as pre_closing_balance FROM  `pms_attendance_report` h INNER JOIN pms_employee e ON h.employee_id = e.id left join pms_leave_history as lh on h.employee_id = lh.emp_id  WHERE h.month = '$month' AND h.year = '$year' and lh.ishalfmonthly = '1' order by e.name ";
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$tempArr = $this->fetchrow();
					
					/* Half monthly */
					
					$opeaningBal = 0;
					$leaveEarned = 0;
					
					$sSQL = "select opening_leave, added_no_of_leaves from pms_leave_history where emp_id='".mysql_real_escape_string($tempArr["employee_id"])."' and month='".mysql_real_escape_string($month)."' and year='".mysql_real_escape_string($year)."' and ishalfmonthly='1'";
					$db->query($sSQL);
					
					if($db->num_rows() > 0)
					{
						if($db->next_record())
						{
							$opeaningBal = $db->f("opening_leave");
							$leaveEarned = $db->f("added_no_of_leaves");
						}
					}
					else
					{
						$sSQL = "select opening_leave_balance from pms_employee where id='".mysql_real_escape_string($tempArr["employee_id"])."'";
						$db->query($sSQL);
						if($db->num_rows() > 0)
						{
							if($db->next_record())
							{
								$opeaningBal = $db->f("opening_leave_balance"); 
							}
						}
						
					}

					$tempArr["leave_earns"] = $tempArr["leave_earns"] + $leaveEarned;
					$tempArr["opening_leave"] = $opeaningBal;
					
					$arrAllCalculation[] = $tempArr;
				}
			}
			return $arrAllCalculation;
		}
		/*	function fnGetMonthlyReport($month,$year)
		 {
			$arrAllCalculation = array();
			//$query = "SELECT e.name, h . * FROM  `pms_attendance_report` h INNER JOIN pms_employee e ON h.employee_id = e.id  WHERE h.month = '$month' AND h.year = '$year' order by e.name";
			$query = "SELECT e.name, h.*, e1.name as reporting_head FROM  `pms_attendance_report` h INNER JOIN pms_employee e ON h.employee_id = e.id INNER JOIN pms_employee e1 ON e.teamleader = e1.id WHERE h.month = '$month' AND h.year = '$year' order by e.name";
			//$query = "SELECT lh.*,e.name as employee_name FROM `pms_leave_history` AS lh INNER JOIN `pms_employee` AS e ON e.id = lh.emp_id WHERE lh.month = '$month' AND lh.year = '$year'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllCalculation[] = $this->fetchrow();
				}
			}
			return $arrAllCalculation;
		}*/

		function fnGetTempMonthlyReport($id,$month,$year)
		{
			$arrAllCalculation = array();
			$query = "SELECT * FROM  `pms_attendance` WHERE  `user_id` ='$id' AND DATE_FORMAT( DATE,  '%Y-%m' ) =  '".$year."-".$month."' ORDER BY DATE"; 
			//$query = "SELECT lh.*,e.name as employee_name FROM `pms_leave_history` AS lh INNER JOIN `pms_employee` AS e ON e.id = lh.emp_id WHERE lh.month = '$month' AND lh.year = '$year'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllCalculation[] = $this->fetchrow();
				}
			}
			return $arrAllCalculation;
		}

		/* Functions for incentive report */



		
		function fnGetTotalIncentiveBreaks($id,$start_date,$end_date,$des)
		{

			$breack_exceed = 0;

			if($des == '7' || $des == '13')
			{
				$query = "SELECT COUNT(isExceededBreak) as count_break_exceed FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$id' AND `isExceededBreak` = 1 AND `ishoursapproved`=0 and `total_working_hours` < '07:20:00'";
			}
			else
			{
				$query = "SELECT COUNT(isExceededBreak) as count_break_exceed FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$id' AND `isExceededBreak` = 1 AND `ishoursapproved`=0";
			}
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$breack_exceed = $this->f("count_break_exceed");
				}
			}
			return $breack_exceed;
		}
		
		function fnGetTotalIncentiveLateComings($id,$start_date,$end_date,$des)
		{
			//$start_date = $year.'-'.$month.'-'.'01';
			//$end_date = date("Y-m-t", strtotime($start_date));

			$late_comings = 0;
			

			$query = "SELECT COUNT(is_late) as late_comings FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$id' AND `is_late` = 1 AND `late_time` > '00:04:00'";
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$late_comings = $this->f("late_comings");
				}
			}
			
			if($des == '6' || $des == '18' || $des == '7' || $des == '13' || $des == '19' || $des == '21' || $des == '22' || $des == '23' || $des == '24' || $des == '25' || $des == '27' || $des == '28' || $des == '20' || $des == '26' || $des == '44')
			{
				$late_comings = 0;
			}

			return $late_comings;
		}

		function fnGetTotalIncentivePresents($id,$start_date,$end_date,$des=0)
		{
			//$start_date = $year.'-'.$month.'-'.'01';
			//$end_date = date("Y-m-t", strtotime($start_date));
			
			$late_comings = '';
			
			if($des == '7' || $des == '13')
			{
				$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and ((`total_working_hours`	 > '06:40:00') or ishoursapproved='1') and leave_id not in(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15)";
			}
			else if($des == '6' || $des == '18' || $des == '19' || $des == '44')
			{
				$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (`total_working_hours`	 >= '04:00:00') and leave_id not in(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15)";
			}
			else if($des == '21' || $des == '22' || $des == '23' || $des == '24' || $des == '25' || $des == '26' || $des == '27' || $des == '28')
			{
				$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (`total_working_hours`	 >= '07:30:00') and leave_id not in(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15)";
			}
			else
			{
				$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (`total_working_hours`	 > '06:40:00') and leave_id not in(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15)";
			}
			
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$total_in_time_count = $this->f("total_in_time_count");
					$timediff = $this->f("diff");
				}
			}
			//echo '<br>total_in_time_count:'.$total_in_time_count;
			//$timediff = $this->f("diff");
			$checkShiftMovementDays = $this->fnGetTotalIncentiveOfficialShiftMovementDays($id,$start_date,$end_date);

			//echo '<pre>hello';print_r($checkShiftMovementDays); 

			$totalShiftMovementTaken = $checkShiftMovementDays['total'];

			$approvedShiftMovements = $checkShiftMovementDays['approved'];

			$unApprovedShiftMovements = $totalShiftMovementTaken - $checkShiftMovementDays['approved'];

			$DeductedUnApprovedShiftMovement = $unApprovedShiftMovements * .5;

			//echo $DeductedUnApprovedShiftMovement;

			//$checkHalfDays = $this->fnGetTotalHalfDays1($id,$month,$year);
			$present_days_in_shift_movements = $totalShiftMovementTaken - ($unApprovedShiftMovements*.5);

			//echo '<br><br>total_in_time_count---'.$total_in_time_count.'<br><br>totalShiftMovementTaken----'.$totalShiftMovementTaken.'<br><br>checkHalfDays---'.$checkHalfDays;

			//$total_full_days = ($total_in_time_count + $totalShiftMovementTaken + ($checkHalfDays * .5)) ;
			/* Removed halfday calculation as this is added later in the main file */
			//echo '<br>total_in_time_count-----------'.$total_in_time_count.'<br>totalShiftMovementTaken----'.$totalShiftMovementTaken.'<br><br>';
			$total_full_days = ($total_in_time_count + $approvedShiftMovements);
			//$total_full_days = ($total_in_time_count + $present_days_in_shift_movements);

			//echo 'total_in_time_count--'.$total_in_time_count.'totalShiftMovementTaken--'.$totalShiftMovementTaken.'approvedShiftMovements--'.$approvedShiftMovements.'unApprovedShiftMovements---'.$unApprovedShiftMovements.'total_full_days'.$total_full_days;
			//echo '<br><br>total_full_days----'.$total_full_days.'<br><br>';
			return $total_full_days;
		}

		function fnGetTotalIncentiveOfficialShiftMovementDays($id,$start_date,$end_date)
		{
			
			$total_count = 0;
			$smPltCount = 0;
			
			$total_in_time_count = array();
			$total_shift_movements = array();
			
			//$start_date = $year.'-'.$month.'-'.'01';
			//$end_date = date("Y-m-t", strtotime($start_date));

			$query213 = "SELECT count(id) as mov_count FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and at.official_total_working_hours >= '05:20:00'  AND at.`leave_id` in(11)";
			$this->query($query213);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$smPltCount = $this->f("mov_count");
				}
			}
//echo '<br>smPltCount'.$smPltCount;

			$query = "SELECT at.* FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and at.official_total_working_hours >= '05:20:00'  AND at.`leave_id` in(14)";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$total_in_time_count[] = $this->fetchrow("total_in_time_count");
				}
			}

			$total_shift_movements = count($total_in_time_count);
			//print_r($total_in_time_count);
			if(count($total_in_time_count) > 0 )
			{
				
				
				foreach($total_in_time_count as $total)
				{
					$mov_id = 0;
					$mov_comp_id = 0;
					//echo '<pre>gagan'.$total['user_id']; print_r($total);
					$query1 = "SELECT id as mov_id FROM `pms_shift_movement` WHERE DATE_FORMAT( `movement_date` , '%Y-%m-%d' ) = DATE_FORMAT( '".$total['date']."', '%Y-%m-%d' ) and (`approvedby_manager` = 1 or (`approvedby_manager` != 2 and delegatedmanager_id != '0' and delegatedmanager_status = '1')) and userid = '".$total['user_id']."'";
					$this->query($query1);
					if($this->num_rows() > 0)
					{
						if($this->next_record())
						{
							$mov_id = $this->f("mov_id");
						}
					}


					$query100 = "SELECT id as mov_comp_id FROM `pms_shift_movement_compensation` WHERE shift_movement_id = '$mov_id' and (`approvedby_tl` = 1 or (`approvedby_tl` != 2 and delegatedtl_id != '0' and delegatedtl_status = '1')) and userid = '".$total['user_id']."'";
					$this->query($query100);
					if($this->num_rows() > 0)
					{
						if($this->next_record())
						{
							$mov_comp_id = $this->f("mov_comp_id");
						}
					}

					//echo '<br>mov_comp_id:'.$mov_comp_id;
					if($mov_comp_id != '0' &&  $mov_comp_id != '')
					{
						$total_count += 1;
					}
				}
			}
			
			$total_shift_mov = $total_shift_movements + $smPltCount;
			$official_total_count_w_componsate = $total_count + $smPltCount;
			$approved_shift_movement = $official_total_count_w_componsate;
			//$final_approved_shift_changes = $total_shift_changes - $approved_shift_changes;
			//echo '<br>total_shift_movement---------'.$total_shift_movement;
			//echo '<br>approved_shift_movement---------'.$approved_shift_movement;
			//echo '<br>final_approved_shift_changes---------'.$final_approved_shift_changes;
			$shiftStatus = array("total"=>$total_shift_mov,"approved"=>$approved_shift_movement);
			//echo '<br><pre>shiftStatus';print_r($shiftStatus);
			return $shiftStatus;
		}
		

		function fnGetTotalIncentiveLeave($id,$start_date,$end_date,$leaveType)
		{
			//$start_date = $year.'-'.$month.'-'.'01';
			//$end_date = date("Y-m-t", strtotime($start_date));

			$leave = '';
			if($leaveType == 'ppl')
			{
				$leave_id = 1;
			}
			else if($leaveType == 'ph')
			{
				$leave_id = 10;
			}
			else if($leaveType == 'upl')
			{
				$leave_id = 2;
			}
			else if($leaveType == 'phl')
			{
				$leave_id = 4;
			}
			else if($leaveType == 'uhl')
			{
				$leave_id = 5;
			}
			else if($leaveType == 'wo')
			{
				$leave_id = 9;
			}
			else if($leaveType == 'ha')
			{
				$leave_id = 12;
			}
			else if($leaveType == 'hlwp')
			{
				$leave_id = 8;
			}
			else if($leaveType == 'ulwp')
			{
				$leave_id = 7;
			}
			else if($leaveType == 'plwp')
			{
				$leave_id = 6;
			}
			else if($leaveType == 'a')
			{
				$leave_id = 3;
			}
			else if($leaveType == 'smplt')
			{
				$leave_id = 11;
			}
			$query = "SELECT COUNT(leave_id) as leaves FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$id' AND `leave_id` = '$leave_id'";
			//$query = "SELECT * FROM `pms_attendance` WHERE ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$leave = $this->f("leaves");
				}
			}
			//echo 'hello'; die;
			//echo count($arrAttendanceValues);
			//die;
			//print_r($arrAttendanceValues); die;
			return $leave;
		}

		function fnGetTotalIncentiveHalfDays1($id,$start_date,$end_date,$des)
		{
			//$start_date = $year.'-'.$month.'-'.'01';
			//$end_date = date("Y-m-t", strtotime($start_date));

			$count = 0;

			if($des != '6' && $des != '18' && $des != '19' && $des != '44')
			{
				$query = "SELECT COUNT( id ) AS count_halfdays FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and ((at.official_total_working_hours >= '03:30:00' AND at.official_total_working_hours < '06:40:00' and leave_id ='0') or (leave_id in(11,14) and at.official_total_working_hours >= '03:30:00' AND at.official_total_working_hours < '05:20:00'))";
				$this->query($query);

				if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$count = $this->f("count_halfdays");
					}
				}
			}
			return $count;
		}
		function fnGetIncentiveWeekOfDates($eid,$start_date,$end_date)
		{
			//$start_date = $year.'-'.$month.'-'.'01';
			//$end_date = date("Y-m-t", strtotime($start_date));
			
			$arrAllweekOffs = array();
			$query = "SELECT date FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$eid' AND leave_id IN(9)";
			$this->query($query);

			if($this->num_rows() >0 )
			{
				while($this->next_record())
				{
					$arrAllweekOffs[] = $this->fetchrow();
				}
			}
			//print_r($arrEmployee);
			return $arrAllweekOffs;
		}
	}
?>

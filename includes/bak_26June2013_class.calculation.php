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

/*		function fnCheckExistence($eid,$month,$year)
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
		}*/

		function fnCheckHalfExistence($eid,$month,$year)
		{
			$id = '';
			$query = "SELECT id FROM  `pms_leave_history` WHERE `emp_id` = '$eid' AND `month` ='$month' AND `year` = '$year'";
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
			$query = "SELECT status_manager,status FROM `pms_leave_form` WHERE `employee_id`='$uid' AND DATE_FORMAT('$date','%Y-%m-%d') between DATE_FORMAT(`start_date`,'%Y-%m-%d') AND DATE_FORMAT(`end_date`,'%Y-%m-%d')";
			$this->query($query);

			if($this->num_rows() >0 )
			{
				if($this->next_record())
				{
					$manager_status = $this->f('status_manager');
					$teamleader_status = $this->f('status');
				}
			}
			//echo 'manager_status---'.$manager_status;
			//echo 'teamLeader_status---'.$teamleader_status;
			if($manager_status == 1)
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

		function fnGetHalfTotalBreaks($id,$month,$year,$des)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.'15';
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

		function fnGetHalfTotalLateComings($id,$month,$year,$des)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.'15';
			$late_comings = '';
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
			//echo 'hello'; die;
			//echo count($arrAttendanceValues);
			//die;
			//print_r($arrAttendanceValues); die;
			if($des == '6' || $des == '18' || $des == '7' || $des == '13' || $des == '19' || $des == '21' || $des == '22' || $des == '23' || $des == '24' || $des == '25' || $des == '27' || $des == '28' || $des == '20' || $des == '26')
			{
				$late_comings = 0;
				return $late_comings;
			}
			else
			{
				return $late_comings;
			}
			
		}

		function fnGetHalfTotalPresents($id,$month,$year,$des=0)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.'15';
			$late_comings = '';
			//echo $query = "SELECT at.*,COUNT( id ) AS total_in_time_count,TIMEDIFF(at.`out_time`,at.`in_time`) as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (TIMEDIFF(at.`out_time`,at.`in_time`) >= '07:45:00')";
			//$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (`total_working_hours`	 >= '07:10:00')";
			if($des == '7' || $des == '13' || $des == '6' || $des == '18' || $des == '19' )
			{
				$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and ((`total_working_hours`	 > '06:00:00') or ishoursapproved='1') and leave_id not in(11,14)";
			}
			else
			{
				$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (`total_working_hours`	 > '06:00:00') and leave_id not in(11,14)";
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
			//echo $total_in_time_count;
			//$timediff = $this->f("diff");
			$checkShiftMovementDays = $this->fnGetHalfTotalOfficialShiftMovementDays($id,$month,$year);
			//print_r($checkShiftMovementDays); 
			$totalShiftMovementTaken = $checkShiftMovementDays['total'];

			$approvedShiftMovements = $checkShiftMovementDays['approved'];

			$unApprovedShiftMovements = $totalShiftMovementTaken - $checkShiftMovementDays['approved'];

			$DeductedUnApprovedShiftMovement = $unApprovedShiftMovements * .5;

			//echo $DeductedUnApprovedShiftMovement;

			$checkHalfDays = $this->fnGetTotalHalfDays($id,$month,$year);
			$present_days_in_shift_movements = $totalShiftMovementTaken - ($unApprovedShiftMovements*.5);

			//echo '<br><br>total_in_time_count---'.$total_in_time_count.'<br><br>totalShiftMovementTaken----'.$totalShiftMovementTaken.'<br><br>checkHalfDays---'.$checkHalfDays;

			//$total_full_days = ($total_in_time_count + $totalShiftMovementTaken + ($checkHalfDays * .5)) ;
			/* Removed halfday calculation as this is added later in the main file */
			$total_full_days = ($total_in_time_count + $totalShiftMovementTaken);

			//echo '<br>checkHalfDays--'.$checkHalfDays.'total_in_time_count--'.$total_in_time_count.'totalShiftMovementTaken--'.$totalShiftMovementTaken.'approvedShiftMovements--'.$approvedShiftMovements.'unApprovedShiftMovements---'.$unApprovedShiftMovements.'total_full_days'.$total_full_days;
			//echo '<br><br>total_full_days----'.$total_full_days.'<br><br>';
			return $total_full_days;
		}

		function fnGetHalfTotalOfficialShiftMovementDays($id,$month,$year)
		{
			$total_in_time_count = array();
			$total_shift_movements = array();
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.'15';

			//$query = "SELECT at.* FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (TIMEDIFF(at.`out_time`,at.`in_time`) >= '06:00:00' and TIMEDIFF(at.`out_time`,at.`in_time`) < '08:00:00') AND at.`leave_id` = 14";

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
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.'15';

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

		function fnGetHalfTotalLeave($id,$month,$year,$leaveType)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.'15';
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
		function fnGetHalfWeekOfDates($eid,$month,$year)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.'15';
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
			$sSQL = "select added_no_of_leaves from pms_leave_history where emp_id='".mysql_real_escape_string($id)."' and month='".mysql_real_escape_string($month)."' and year='".mysql_real_escape_string($year)."'";
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
			
			if($des == '6' || $des == '18' || $des == '7' || $des == '13' || $des == '19' || $des == '21' || $des == '22' || $des == '23' || $des == '24' || $des == '25' || $des == '27' || $des == '28' || $des == '20' || $des == '26')
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
			
			if($des == '7' || $des == '13' || $des == '6' || $des == '18' || $des == '19' )
			{
				$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and ((`total_working_hours`	 > '05:20:00') or ishoursapproved='1') and leave_id not in(11,14,4, 5, 8, 12)";
			}
			else
			{
				$query = "SELECT at.*,COUNT( id ) AS total_in_time_count, `total_working_hours`  as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (`total_working_hours`	 > '05:20:00') and leave_id not in(11,14,4, 5, 8, 12)";
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
			//echo $total_in_time_count;
			//$timediff = $this->f("diff");
			$checkShiftMovementDays = $this->fnGetTotalOfficialShiftMovementDays($id,$month,$year);
			//print_r($checkShiftMovementDays); 
			$totalShiftMovementTaken = $checkShiftMovementDays['total'];

			$approvedShiftMovements = $checkShiftMovementDays['approved'];

			$unApprovedShiftMovements = $totalShiftMovementTaken - $checkShiftMovementDays['approved'];

			$DeductedUnApprovedShiftMovement = $unApprovedShiftMovements * .5;

			//echo $DeductedUnApprovedShiftMovement;

			$checkHalfDays = $this->fnGetTotalHalfDays1($id,$month,$year);
			$present_days_in_shift_movements = $totalShiftMovementTaken - ($unApprovedShiftMovements*.5);

			//echo '<br><br>total_in_time_count---'.$total_in_time_count.'<br><br>totalShiftMovementTaken----'.$totalShiftMovementTaken.'<br><br>checkHalfDays---'.$checkHalfDays;

			//$total_full_days = ($total_in_time_count + $totalShiftMovementTaken + ($checkHalfDays * .5)) ;
			/* Removed halfday calculation as this is added later in the main file */
			//echo '<br>total_in_time_count-----------'.$total_in_time_count.'<br>totalShiftMovementTaken----'.$totalShiftMovementTaken.'<br><br>';
			$total_full_days = ($total_in_time_count + $totalShiftMovementTaken);

			//echo '<br>checkHalfDays--'.$checkHalfDays.'total_in_time_count--'.$total_in_time_count.'totalShiftMovementTaken--'.$totalShiftMovementTaken.'approvedShiftMovements--'.$approvedShiftMovements.'unApprovedShiftMovements---'.$unApprovedShiftMovements.'total_full_days'.$total_full_days;
			//echo '<br><br>total_full_days----'.$total_full_days.'<br><br>';
			return $total_full_days;
		}

		function fnGetTotalOfficialShiftMovementDays($id,$month,$year)
		{
			$total_in_time_count = array();
			$total_shift_movements = array();
			
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = date("Y-m-t", strtotime($start_date));

			
			//$query = "SELECT at.* FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (TIMEDIFF(at.`out_time`,at.`in_time`) >= '06:00:00' and TIMEDIFF(at.`out_time`,at.`in_time`) < '08:00:00') AND at.`leave_id` in(14,11)";
			
			//$query = "SELECT at.* FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and TIMEDIFF(at.`out_time`,at.`in_time`) >= '06:00:00'  AND at.`leave_id` in(14,11)";

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

		function fnGetTotalHalfDays1($id,$month,$year)
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
			//echo 'here'; print_r($arrData); die;
			$query = "select id from `pms_attendance_report` where employee_id ='".$arrData['employee_id']."' and month ='".$arrData['month']."' and year ='".$arrData['year']."' ";
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
				$this->updateArray('pms_attendance_report',$arrData);
			}
			else
			{
				//echo 'hello1';
				$this->insertArray('pms_attendance_report',$arrData);
			}
			
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
			$query = "SELECT e.name, h . * FROM  `pms_attendance_report` h INNER JOIN pms_employee e ON h.employee_id = e.id  WHERE h.month = '$month' AND h.year = '$year'";
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

		function fnGetTempMonthlyReport($id)
		{
			$arrAllCalculation = array();
			$query = "SELECT * FROM  `pms_attendance` WHERE  `user_id` ='$id' AND DATE_FORMAT( DATE,  '%Y-%m' ) =  '2013-05' ORDER BY DATE"; 
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
	}
?>

<?php
include_once('db_mysql.php');
	class calculation extends DB_Sql
	{
		function __construct()
		{
		}
		
		function fnGetTotalBreackExceeds($id,$month,$year)
		{
			$breack_exceed = '';
			$query = "SELECT *,COUNT(isExceededBreak) as count_break_exceed FROM `pms_attendance` WHERE DATE_FORMAT(date,'%m-%Y') = '".$month."-".$year."' AND user_id = '$id' AND `isExceededBreak` = 1";
			//$query = "SELECT * FROM `pms_attendance` WHERE ";
			$this->query($query);
			
			if($this->num_rows() > 0)
			{	
				if($this->next_record())
				{
					$breack_exceed = $this->f("count_break_exceed");
				}
			}
			//echo 'hello'; die;
			//echo count($arrAttendanceValues);  
			//die;
			//print_r($arrAttendanceValues); die;
			return $breack_exceed;
		}
		
		
		function fnGetTotalLateComings($id,$month,$year)
		{
			$late_comings = '';
			$query = "SELECT COUNT(is_late) as late_comings FROM `pms_attendance` WHERE DATE_FORMAT(date,'%m-%Y') = '".$month."-".$year."' AND user_id = '$id' AND `is_late` = 1";
			//$query = "SELECT * FROM `pms_attendance` WHERE ";
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
			return $late_comings;
		}
		
		function fnGetTotalPresents($id,$month,$year)
		{
			$late_comings = '';
			$query = "SELECT COUNT(in_time) as total_in_time_count FROM `pms_attendance` WHERE DATE_FORMAT(date,'%m-%Y') = '".$month."-".$year."' AND user_id = '$id' AND (`in_time` != '00:00:00') "; 
			//$query = "SELECT * FROM `pms_attendance` WHERE ";
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
			$query = "SELECT COUNT(leave_id) as leaves FROM `pms_attendance` WHERE DATE_FORMAT(date,'%m-%Y') = '".$month."-".$year."' AND user_id = '$id' AND `leave_id` = '$leave_id'";  
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
		
		function fnInsertSummary($Data)
		{
			//echo '<pre>'; print_r($Data); die;
			$this->insertArray('pms_attendance_report',$Data);
			return true;
		}
		
		function fnInsertHalfSummary($Data,$month,$year)
		{
			$Data['month'] = $month;
			$Data['year'] = $year; 
			
			$this->insertArray('pms_leave_history',$Data); 
			return true;
		}
		
		function fnUpdateHalfSummary($id,$Data,$month,$year)
		{
			//echo '<pre>'; print_r($Data); die;
			$Data['id'] = $id;
			$Data['month'] = $month;
			$Data['year'] = $year;
			$this->updateArray('pms_attendance_report',$Data);
			return true;
		}
		function fnUpdateSummary($id,$Data)
		{
			//echo '<pre>'; print_r($Data); die;
			$Data['id'] = $id;
			$this->updateArray('pms_attendance_report',$Data);
			return true;
		}
		
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
			echo $query = "SELECT date FROM `pms_attendance` WHERE DATE_FORMAT(date,'%m-%Y') = '".$month."-".$year."' AND user_id = '$eid' AND leave_id IN(10)";
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
		
		function fnCheckNextDate($userid,$cur,$prev,$next,$temp)
		{
			
			$arrLeaves = array();
			$temp1 =array();
			//echo 'cur----'.$cur.'prev-------'.$prev;
			//$checkPrevDay = $this->fnCheckDate($userid,$prev);
			$checkNextDay = $this->fnCheckDate($userid,$next);
			echo '<pre>';
			echo 'next----'.$next.'--------checkNextDay------'.$checkNextDay.'.....';
			
			if($checkNextDay == 'ph')
			{
				echo 'first--';
				$arrLeaves[] = $next;
				$temp = $arrLeaves;
				$next = date('Y-m-d', strtotime('+1 day', strtotime($next)));
				$checkNextDay = $this->fnCheckNextDate($userid,'','',$next,$temp);
			}
			else if($checkNextDay == 'leave')
			{
					echo 'second---';
				$arrLeaves[] = $next;
				$temp = array_merge($arrLeaves, $temp);
				print_r($temp);
				$checkPrevDay = $this->fnCheckNextDate($userid,$$cur,$prev,$temp1);
				/*if(count($temp) > 1)
				{
					if($checkPrevDay == 'ph')
					{
						$arrLeaves[] = $prev;
						$temp = array_merge($arrLeaves, $temp);
						$prev = date('Y-m-d', strtotime('-1 day', strtotime($prev)));
						$checkPrevDay = $this->fnCheckNextDate($userid,'',$prev,'',$temp);
						
						
					}
					else if($checkPrevDay == 'leave')
					{
						
						$arrLeaves[] = $prev;
						$temp = array_merge($arrLeaves, $temp);
						return $temp;
					}
					else if($checkPrevDay == 'present')
					{
						return $temp;
					}
				}*/
			}
			else if($checkNextDay == 'present')
			{
				return true;
			}
			return $arrLeaves;
		}
		
		
		
		function fnCheckPrevDate($userid,$cur,$prev,$temp1)
		{
			$arrLeaves = array();
			$next_date = date('Y-m-d', strtotime('+1 day', strtotime($cur)));
			
			$checkPrevDay = $this->fnCheckDate($userid,$next_date);
			//echo  $cur.'----'.$next_date.'========'.$checkPrevDay.'<br>';
			if($checkPrevDay == 'ph')
			{
				array_push($arrLeaves,$cur);
				$checkPrevDay = $this->fnCheckNextDate($userid,$next_date);
			}
			else if($checkPrevDay == 'leave')
			{
				array_push($arrLeaves,$cur);
				return $arrLeaves;
			}
			else if($checkPrevDay == 0)
			{
				return $arrLeaves;
			}
			return $arrLeaves;
		}
		
		function fnGetNextDate($cur)
		{
			$next_date = date('Y-m-d', strtotime('+1 day', strtotime($cur)));
			return $next_date;
		}
		
		/***********All functions for half_monthly_request.php ******************/
		
		function fnGetTotalBreacks($id,$month,$year)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.'15';
			$breack_exceed = '';
			$query = "SELECT *,COUNT(isExceededBreak) as count_break_exceed FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$id' AND `isExceededBreak` = 1";
			//$query = "SELECT * FROM `pms_attendance` WHERE ";
			$this->query($query);
			
			if($this->num_rows() > 0)
			{	
				if($this->next_record())
				{
					$breack_exceed = $this->f("count_break_exceed");
				}
			}
			//echo 'hello'; die;
			//echo count($arrAttendanceValues);  
			//die;
			//print_r($arrAttendanceValues); die;
			return $breack_exceed;
		}
		
		function fnGetHalfTotalLateComings($id,$month,$year)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.'15';
			$late_comings = '';
			$query = "SELECT COUNT(is_late) as late_comings FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND user_id = '$id' AND `is_late` = 1";
			//$query = "SELECT * FROM `pms_attendance` WHERE ";
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
			return $late_comings;
		}
		
		function fnGetHalfTotalPresents($id,$month,$year)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.'15';
			$late_comings = '';
			$query = "SELECT at.*,COUNT( id ) AS total_in_time_count,TIMEDIFF(at.`out_time`,at.`in_time`) as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (TIMEDIFF(at.`out_time`,at.`in_time`) >= '07:45:00')";
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
			
			$checkHalfDays = $this->fnGetTotalHalfDays($id,$month,$year);
			$present_days_in_shift_movements = $totalShiftMovementTaken - ($unApprovedShiftMovements*.5);
			
			$total_full_days = ($total_in_time_count + $totalShiftMovementTaken + ($checkHalfDays * .5)) ;
			
			//echo '<br>checkHalfDays--'.$checkHalfDays.'total_in_time_count--'.$total_in_time_count.'totalShiftMovementTaken--'.$totalShiftMovementTaken.'approvedShiftMovements--'.$approvedShiftMovements.'unApprovedShiftMovements---'.$unApprovedShiftMovements.'total_full_days'.$total_full_days;
			
			return $total_full_days;
		}
		
		function fnGetTotalOfficialShiftMovementDays($id,$month,$year)
		{
			$total_in_time_count = array();
			$total_shift_movements = array();
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.'15';
			
			$query = "SELECT at.* FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (TIMEDIFF(at.`out_time`,at.`in_time`) >= '06:00:00' and TIMEDIFF(at.`out_time`,at.`in_time`) < '08:00:00') AND at.`leave_id` = 13";
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
					$query1 = "SELECT *,count(id) as count FROM `pms_shift_movement` WHERE DATE_FORMAT( `movement_date` , '%d-%m-%Y' ) = DATE_FORMAT( '".$total['date']."', '%d-%m-%Y' ) and((`approvedby_tl`=1 and `approvedby_manager` != 2  ) or `approvedby_manager` = 1)";	
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
			return $shiftStatus;
			
		}
		
		function fnGetTotalHalfDays($id,$month,$year)
		{
			$start_date = $year.'-'.$month.'-'.'01';
			$end_date = $year.'-'.$month.'-'.'15';
			
			$query = "SELECT COUNT( in_time ) AS total_in_time_count,TIMEDIFF(at.`out_time`,at.`in_time`) as diff FROM `pms_attendance` as at WHERE DATE_FORMAT(at.date,'%Y-%m-%d') between '$start_date' AND '$end_date' AND at.user_id = '$id' and (TIMEDIFF(at.`out_time`,at.`in_time`) >= '04:00:00' and TIMEDIFF(at.`out_time`,at.`in_time`) < '08:00:00') AND  at.`leave_id` NOT IN ( 13, 5, 6 )";
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
	}
?>
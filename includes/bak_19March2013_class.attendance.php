<?php
include_once('db_mysql.php');
	class attendance extends DB_Sql
	{
		function __construct()
		{
		}

		function fnInsertAttendance($arrEmployee)
		{
			$date = $arrEmployee['hdndate'];
			
			$fieldArr = array("in_time","out_time","break1_in","break1_out","break2_in","break2_out","break3_in","break3_out","break4_in","break4_out","break5_in","break5_out");
			//echo '<pre>';
			foreach ($arrEmployee['hdnemployeeid'] as $value)
			{
				//print_r($value);
				foreach($fieldArr as $k)
				{
					if(trim($arrEmployee[$k][$value]) == "")
					{
						$arrEmployee[$k][$value] = "00:00";
					}
					else
					{
						$arrEmployee[$k][$value] = str_replace(':','',$arrEmployee[$k][$value]);

						$arrEmployee[$k][$value] = str_pad($arrEmployee[$k][$value], 4, "0", STR_PAD_LEFT);

						$arrEmployee[$k][$value] = substr($arrEmployee[$k][$value], 0, 2) . ":" . substr($arrEmployee[$k][$value], 2);
					}
				}
				$intim = $arrEmployee['in_time'][$value].':00';
				$outim = $arrEmployee['out_time'][$value].':00';
				
				$ActualshiftTimings = $this->fnGetShiftTimes($value,$arrEmployee['in_time'][$value],$arrEmployee['leave_id'][$value],$arrEmployee['shift_id'][$value],$arrEmployee['hdndate']);
				//print_r($arrEmployee);
				//echo '<br>hellohere<br>';
				//print_r($ActualshiftTimings);
				//echo '<br>hellohere<br>';
				$checkTeamLeader = $this->fnGetTeamLeaderId($value);
				//echo 'checkTeamLeader'.$checkTeamLeader;
				$checklate = 0;
				$late_time = '00:00:00';
				echo 'intime---'.$intim.'--shiftInTime---'.$ActualshiftTimings['starttime'];
				echo '<br>outim----'.$outim.'--shiftOutTime---'.$ActualshiftTimings['endtime'];
				
				if($intim != '00:00:00' && $outim != '00:00:00')
				{
					if($arrEmployee['leave_id'][$value] == 14)
					{
						//echo 'gagan<br>';
						
						$arrShiftMovementTime = $this->fnValidateLateComming1($value,$arrEmployee['in_time'][$value],$arrEmployee['leave_id'][$value],$arrEmployee['shift_id'][$value],$arrEmployee['hdndate']);
						 //print_r($arrShiftMovementTime);
						 
						 if($intim > $arrShiftMovementTime['movement_totime'])
						 {
							 $checklate = 1;
							 $difference = strtotime(Date('Y-m-d')." ".$intim) - strtotime(Date('Y-m-d')." ".$arrShiftMovementTime['movement_totime']);
								$late_time = gmdate("H:i:s", $difference);
						 }
						 else
						 {
							$checklate = 0;
						 }
					}
					else
					{
						if($intim > $ActualshiftTimings[starttime])
							{
								$checklate = 1;
								$difference = strtotime(Date('Y-m-d')." ".$intim) - strtotime(Date('Y-m-d')." ".$ActualshiftInTime);
								$late_time = gmdate("H:i:s", $difference);
							}
					}	
				}
						
				$id_attendance  =  $this->fnGetAttendance($date,$value);

				
				
				$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00'),TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00')) AS totalbreak, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00'),TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00')) > '00:44:00','1','0') as isExceeded";
				
				$this->query($query);
				if($this->num_rows() > 0)
					{
						if($this->next_record())
						{
							$totalBreak = $this->f("totalbreak");
							$breakExceed = $this->f("isExceeded");
						}
					}
					
				$differ = '00:00:00';
				$differ1 = '00:00:00';

				if($checkTeamLeader == '7' || $checkTeamLeader == '13')
				{
					echo '<br>hello<br>';
					if($intim != '00:00:00' && $outim != '00:00:00')
					{
						if($outim < $intim)
						{
							echo '<br>hello2<br>';
							echo $qQuery = "Select ADDTIME(TIMEDIFF('24:00:00','$intim'),'$outim') as differ1";
							$this->query($qQuery);

							if($this->num_rows() > 0)
							{
								if($this->next_record())
								{
									$differ1 = $this->f("differ1");
									$differ = $this->f("differ1");
								}
							}
							
						}
						else
						{
							echo '<br>hello3<br>';
							if($totalBreak == '0' || $totalBreak =='00:00:00')
							{
								echo $query1 = "Select TIMEDIFF('$outim','$intim') as differ1";
							}
							else
							{
								echo $query1 = "Select TIMEDIFF(TIMEDIFF('$outim','$intim'),'$totalBreak') as differ1";
							}
							
								
							$this->query($query1);

							if($this->num_rows() > 0)
							{
								if($this->next_record())
								{
									$differ1 = $this->f("differ1");
									$differ = $this->f("differ1");
								}
							}
						}
					}	
				}
				else
				{
					echo '<pre>';
					echo 'actualshifttimings<br>';
					print_r($ActualshiftTimings);
					echo 'actualshifttimings<br>';
					echo '<br>hello1<br>';
					if($intim != '00:00:00' && $outim != '00:00:00')
					{
						if($intim <= $ActualshiftTimings['starttime'])
						{
							$officialShiftStartTime = $ActualshiftTimings['starttime'];
						}
						else
						{
							$officialShiftStartTime = $intim;
						}
						if($outim >= $ActualshiftTimings['endtime'])
						{
							$officialShiftEndTime = $ActualshiftTimings['endtime'];
						}
						else
						{
							$officialShiftEndTime = $outim;
						}
						
						
						
						

						if($totalBreak == '0' || $totalBreak =='00:00:00')
						{
							$query = "Select TIMEDIFF('$officialShiftEndTime','$officialShiftStartTime') as differ";
						}
						else
						{
							$query = "Select TIMEDIFF(TIMEDIFF('$officialShiftEndTime','$officialShiftStartTime'),'$totalBreak') as differ";
						}
			
							//die;
						$this->query($query);

						if($this->num_rows() > 0)
						{
							if($this->next_record())
							{
								$differ = $this->f("differ");
							}
						}


						if($totalBreak == '0' || $totalBreak =='00:00:00')
						{
							if($outim < $intim)
							{
								$query1 = "Select ADDTIME(TIMEDIFF('24:00:00','$intim'),'$outim') as differ1";
							}
							else
							{
								$query1 = "Select TIMEDIFF('$outim','$intim') as differ1";
							}
						}
						else
						{
							if($outim < $intim)
							{
								$query1 = "Select TIMEDIFF(ADDTIME(TIMEDIFF('24:00:00','$intim'),'$outim'),'$totalBreak') as differ1";
							}
							else
							{
								$query1 = "Select TIMEDIFF(TIMEDIFF('$outim','$intim'),'$totalBreak') as differ1";
							}
						
						}
						echo $query1;
						$this->query($query1);

						if($this->num_rows() > 0)
						{
							if($this->next_record())
							{
								$differ1 = $this->f("differ1");
							}
						}
					}
				}	
				echo '<br>differ----'.$differ.'----differ1---'.$differ1.'<br>';
				//echo '<br>working_hours_difference'.$differ1.'<br>';
				//die;
				if($id_attendance == '0')
				{
					$arrNewRecords = array("user_id"=>$value,"date"=>$date,"in_time"=>$arrEmployee['in_time'][$value].':00',"out_time"=>$arrEmployee['out_time'][$value].':00',"break1_in"=>$arrEmployee['break1_in'][$value].':00',"break1_out"=>$arrEmployee['break1_out'][$value].':00',"break2_in"=>$arrEmployee['break2_in'][$value].':00',"break2_out"=>$arrEmployee['break2_out'][$value].':00',"break3_in"=>$arrEmployee['break3_in'][$value].':00',"break3_out"=>$arrEmployee['break3_out'][$value].':00',"break4_in"=>$arrEmployee['break4_in'][$value].':00',"break4_out"=>$arrEmployee['break4_out'][$value].':00',"break5_in"=>$arrEmployee['break5_in'][$value].':00',"break5_out"=>$arrEmployee['break5_out'][$value].':00',"leave_id"=>$arrEmployee['leave_id'][$value],"is_late"=>$checklate,"total_break_time"=>$totalBreak,"isExceededBreak"=>$breakExceed,"late_time"=>$late_time,"official_total_working_hours"=>$differ,"total_working_hours"=>$differ1);
					$this->insertArray('pms_attendance',$arrNewRecords);
					//print_r($arrNewRecords);
				}
				else
				{
					//echo 'hello';
					$arrNewRecords = array("id"=>$arrEmployee['hdnattendanceid'][$value],"user_id"=>$value,"date"=>$date,"in_time"=>$arrEmployee['in_time'][$value].':00',"out_time"=>$arrEmployee['out_time'][$value].':00',"break1_in"=>$arrEmployee['break1_in'][$value].':00',"break1_out"=>$arrEmployee['break1_out'][$value].':00',"break2_in"=>$arrEmployee['break2_in'][$value].':00',"break2_out"=>$arrEmployee['break2_out'][$value].':00',"break3_in"=>$arrEmployee['break3_in'][$value].':00',"break3_out"=>$arrEmployee['break3_out'][$value].':00',"break4_in"=>$arrEmployee['break4_in'][$value].':00',"break4_out"=>$arrEmployee['break4_out'][$value].':00',"break5_in"=>$arrEmployee['break5_in'][$value].':00',"break5_out"=>$arrEmployee['break5_out'][$value].':00',"leave_id"=>$arrEmployee['leave_id'][$value],"is_late"=>$checklate,"total_break_time"=>$totalBreak,"isExceededBreak"=>$breakExceed,"late_time"=>$late_time,"official_total_working_hours"=>$differ,"total_working_hours"=>$differ1);
					print_r($arrNewRecords); 
					$this->updateArray('pms_attendance',$arrNewRecords);
					//print_r($arrNewRecords);
				}
				//echo '<pre>';
				//print_r($arrNewRecords);
			}
		die;
			//print_r($arrNewRecords);
			return true;
		}

		function fnGetTeamLeaderId($id)
		{
			//echo 'id----'.$id;
			$designationId = '';
			$query = "SELECT designation FROM `pms_employee` WHERE id = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$designationId = $this->f("designation");
				}
			}
			//echo 'designation'.$designationId;
//die;
			return $designationId;
		}
	
		function fnGetAttendance($date,$id)
		{
			$arrAttendanceValues = array();
			$query = "SELECT id FROM `pms_attendance` WHERE date = '$date' AND user_id = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrAttendanceValues = $this->f("id");
				}
			}
			if(count($arrAttendanceValues) > 0 )
			{
				return $arrAttendanceValues;
			}
			else
			{
				return 0;
			}
			//echo $arrAttendanceValues;

		}

		

		function fnGetAllAttendances()
		{
			$arrAttendanceValues = array();
			$query = "SELECT DISTINCT DATE_FORMAT(date, '%Y-%m-%d') as date  FROM `pms_attendance` ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAttendanceValues[] = $this->fetchrow();
				}
			}
			//echo 'hello'; die;
			return $arrAttendanceValues;
		}

		function fnGetAttendanceById($id)
		{
			$arrAttendanceValues = array();
			$query = "SELECT * FROM `pms_attendance` WHERE `id` = '".mysql_real_escape_string($id)."' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAttendanceValues[] = $this->fetchrow();
				}
			}
			return $arrAttendanceValues;
		}


		function fnUpdateAttendances($arrPost)
		{
			$this->updateArray('pms_attendance',$arrPost);
		return true;
		}

		function fnDeleteAttendance($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_attendance` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}

		function fnGetEmployees()
		{
			$arrEmployeeValues = array();
			$query = "SELECT id as employee_id ,name as employee_name FROM `pms_employee`  WHERE `designation` IN('6', '7','13')";
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

		function fnGetLeaveType()
		{
			$arrEmployeeValues = array();
			$query = "SELECT id as leave_id ,title as leave_title FROM `pms_leave_type`";
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

		function fnGetEmployeeDetails($id)
		{
			$db = new DB_Sql();
			$arrEmployeeValues = array();
			//$query = "SELECT id as employee_id ,name as employee_name FROM `pms_employee`  WHERE (`teamleader` = '$id' OR id='$id') AND designation NOT IN(6,8,17)";
			$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON employee.id = attendance.user_id  WHERE (employee.`teamleader` = '$id' OR employee.id='$id') AND employee.designation NOT IN(6,8, 17)";
			$db->query($query);

			if($db->num_rows() > 0)
			{
				while($db->next_record())
				{
					$arrEmployeeValues[$db->f("employee_id")] = $db->fetchrow();
					if($id != $db->f("employee_id"))
					{
						$tmpData = $this->fnGetEmployeeDetails($db->f("employee_id"));
						//$arrEmployeeValues = array_merge($arrEmployeeValues,$tmpData);
						$arrEmployeeValues = $arrEmployeeValues + $tmpData;
						//print_r($arrEmployeeValues);
					}
				}
			}
			//echo "<br><br>";
			//print_r($arrEmployeeValues);
			//print_r($a);
			//die;
			return $arrEmployeeValues;
		}

		function fnGetEmployeeDetails1($id,$date)
		{
			$db = new DB_Sql();
			$arrEmployeeValues = array();


			include_once('includes/class.roster.php');
			$objRoster = new roster();

			//$query = "SELECT id as employee_id ,name as employee_name FROM `pms_employee`  WHERE (`teamleader` = '$id' OR id='$id') AND designation NOT IN(6,8,17)";
			if($id == '')
			{
				$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in,DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out,DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in,DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date')  WHERE employee.designation NOT IN(6,8,17)  order by employee.name";
			}
			else
			{
				$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in,DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out,DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in,DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date')  WHERE (employee.`teamleader` = '$id' OR employee.id='$id') AND employee.designation NOT IN(6,8,17)  order by employee.name";
			}
			$db->query($query);
			if($db->num_rows() > 0)
			{
				while($db->next_record())
				{
					$tmprow = $db->fetchrow();

					if($tmprow["leave_id"] == "13")
					{
						$shiftid = $objRoster->fnGetRosteredShiftByUserAndDate($db->f("employee_id"), $date);

						$tmprow["shiftid"] = $shiftid;
					}

					$arrEmployeeValues[$db->f("employee_id")] = $tmprow;
					if($id != $db->f("employee_id"))
					{
						$tmpData = $this->fnGetEmployeeDetails1($db->f("employee_id"),$date);
						//$arrEmployeeValues = array_merge($arrEmployeeValues,$tmpData);
						$arrEmployeeValues = $arrEmployeeValues + $tmpData;
						//print_r($arrEmployeeValues);
					}
				}
			}
			return $arrEmployeeValues;
		}

		
		function fnValidateLateComming1($EmployeeId, $InTime,$leaveid,$shiftId,$date)
		{
			//echo 'EmployeeId-----'.$EmployeeId.'----leaveid-----'.$leaveid.'---shiftId---'.$shiftId.'---InTime---'.$InTime.'---date---'.$date; 
			
				$sSQL = "select id,movement_fromtime,movement_totime from pms_shift_movement where userid='$EmployeeId' and date_format(`movement_date`,'%Y-%m-%d') = '$date' and ((approvedby_tl = 1 and  approvedby_manager != 2) or (approvedby_manager = 1) or (approvedby_tl = 1 and  approvedby_manager = 1))";
				$this->query($sSQL);

				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrShiftMovementDetails = $this->fetchrow();
					}
				}
				//print_r($arrShiftMovementDetails);
				return $arrShiftMovementDetails;
				//$sqlnew = "select starttime from pms_shift_times where id = '$shiftId'";
			
		}
		
		function fnGetShiftTimes($EmployeeId, $InTime,$leaveid,$shiftId,$date)
		{
			//$arrShiftIdDetails = array();
			//echo 'EmployeeId-----'.$EmployeeId.'<br>leaveid-----'.$leaveid.'<br>shiftId---'.$shiftId.'<br>InTime---'.$InTime.'<br>date---'.$date; 
			
				$sSQL = "select starttime,endtime from pms_shift_times where id = '$shiftId'";
				$this->query($sSQL);

				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrShiftIdDetails = $this->fetchrow();
					}
				}
				//echo '<pre>';
				//print_r($arrShiftIdDetails);
				return $arrShiftIdDetails;
				//$sqlnew = "select starttime from pms_shift_times where id = '$shiftId'";
		}
		
		/*function fnGetLateComingTime($EmployeeId, $InTime,$leaveid,$date)
		{
			$arrShiftMovementDetails = array();
			echo '----EmployeeId======'.$EmployeeId.'----InTime======'.$InTime.'===leaveid----'.$leaveid.'======date-------'.$date;
			if($leaveid = '14')
			{
				$sSQL = "select id,movement_fromtime,movement_totime from pms_shift_movement where userid='$EmployeeId' and date_format(`movement_date`,'%Y-%m-%d') = '$date'";
				$this->query($sSQL);

				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrShiftMovementDetails = $this->fetchrow();
					}
				}
				//print_r($arrShiftMovementDetails);
				$movement_from_time = $arrShiftMovementDetails['movement_fromtime'];
				echo $sqlnew = "SELECT ADDTIME( starttime, '00:05:00' ) AS start_time, starttime FROM pms_shift_times WHERE id ='$shiftId' and ((`approvedby_tl`=1 and `approvedby_manager`=1) and (`approvedby_tl`=1 and `approvedby_manager`!= 2) or (`approvedby_tl` != 2 and `approvedby_manager` = 1))";
			}
			else
			{
				echo $sqlnew = "select ADDTIME(starttime,'00:05:00') as start_time, starttime from pms_employee e INNER JOIN pms_shift_times st ON e.shiftid = st.id where e.id='".mysql_real_escape_string($EmployeeId)."'";
			}
 die;
			$this->query($sqlnew);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrAttendanceValues = $this->f("start_time");
				}
			}
			$intim = $InTime.':00';
			//echo "arrAttendanceValues----".Date('Y-m-d')." ".$arrAttendanceValues."----intim----".Date('Y-m-d')." ".$intim;
			
			
			$difference = strtotime(Date('Y-m-d')." ".$arrAttendanceValues) - strtotime(Date('Y-m-d')." ".$intim);
			$new_time = gmdate("H:i:s", $difference);
			//echo "<br/>". $new_time = date('Y-m-d H:i:s', $difference);
			if(isset($new_time))
			{
				return $new_time;
			}
			else
			{
				return 0;
			}
		}*/


		function fetchAttendenceData($start, $end, $ids)
		{
			$arrHighlights = array();
			//echo SELECT DAYOFWEEK('2013-03-31');
			//echo $sSQL = "SELECT a.id as aid, e.name, l.title, l.color as colorcode, date_format(a.`date`,'%Y-%m-%d') as startdate FROM pms_attendance a INNER JOIN pms_employee e ON a.user_id = e.id INNER JOIN pms_leave_type l ON a.leave_id = l.id where date_format(a.`date`,'%Y-%m-%d') BETWEEN '$start' AND '$end' AND e.id IN($ids) and (a.leave_id NOT IN (0,10) OR( a.leave_id = 9 AND date_format('%w') != 0))";
			$sSQL = "SELECT  a.id as aid, e.name, l.title, l.color as colorcode, date_format(a.`date`,'%Y-%m-%d') as startdate FROM pms_attendance a INNER JOIN pms_employee e ON a.user_id = e.id INNER JOIN pms_leave_type l ON a.leave_id = l.id where date_format(a.`date`,'%Y-%m-%d') BETWEEN '$start' AND '$end' AND e.id IN($ids) and (a.leave_id NOT IN (0,9,10) OR ( a.leave_id = 9 AND date_format(a.`date`,'%w') != 0) OR (a.leave_id = 10 AND date_format(a.`date`,'%Y-%m-%d') NOT IN (select date_format(holidaydate,'%Y-%m-%d') from pms_holidays)))";
			
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrHighlights[] = array(
											'id' => $this->f("aid"),
											'title' => $this->f("name") . " - " . $this->f("title"),
											'start' => $this->f("startdate"),
											'color' => $this->f("colorcode")
										);
				}
			}

			return $arrHighlights;
		}

		function fnGetAllUnApprove($id,$leave_id,$year)
		{
			$totalCount = '';
			/*$query = "SELECT nodays as number_d,status as status_t,status_manager as status_m,DATE_FORMAT(start_date,'%d-%m-%Y') AS start_d,DATE_FORMAT(end_date,'%d-%m-%Y') AS end_d FROM `pms_leave_form` WHERE `employee_id` = '$id' AND DATE_FORMAT(start_date,'%Y') ='$year' AND `id` != '$leave_id' ORDER BY id";*/
			$query = "SELECT COUNT(`user_id`) as total_count FROM `pms_attendance` WHERE `user_id` = '$id' AND DATE_FORMAT(date,'%Y') ='$year' AND `leave_id` = '2'";
			$this->query($query);

				if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$totalCount = $this->f(total_count);
					}
				}
			return $totalCount;
		}

		function fnInsertRosterAttendance($arrInfo)
		{
			$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrInfo["id"] = $this->f("id");

					$this->updateArray('pms_attendance',$arrInfo);
				}
			}
			else
			{
				$this->insertArray('pms_attendance',$arrInfo);
			}
		}
		
		function fnGetInsufficientWorkHours($date)
		{
			$arrInsufficientWorkHours = array();
			$sSQL = "select a.id as attendanceid, e.name, time_format(official_total_working_hours,'%H:%i') as workhours, ishoursapproved, time_format(additionaltime,'%H:%i') as additionaltime from pms_attendance a INNER JOIN pms_employee e ON e.id = a.user_id where ((((a.official_total_working_hours between '07:19:00' and '07:10:00' and a.leave_id='0') or (a.official_total_working_hours between '05:19:00' and '05:10:00' and a.leave_id='14')) and a.user_id in (select id from pms_employee where designation in (5, 9, 10, 11, 12, 14, 15, 16))) or (((a.official_total_working_hours < '07:20:00' and a.leave_id='0') or (a.official_total_working_hours < '05:20:00' and a.leave_id='0')) and a.user_id in  (select id from pms_employee where designation in (7,13)))) and date_format(a.date,'%Y-%m-%d') = '".mysql_real_escape_string($date)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrInsufficientWorkHours[] = $this->fetchrow();
				}
			}
			
			return $arrInsufficientWorkHours;
		}
		function fnGetAllManagers()
		{
			$arrManager = array();
			
			$query = "SELECT id as manager_id,name as manager_name FROM  `pms_employee` WHERE `designation` = '6'";
			$this->query($query);

				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrManager[] = $this->fetchrow();
					}
				}
				//print_r($arrManager);
			return $arrManager;
		}
		
		function fnGetAllTeamLeaders()
		{
			$arrManager = array();
			
			$query = "SELECT id as teamleader_id,name as teamleader_name FROM  `pms_employee` WHERE `designation` = '7' or `designation` = '13'";
			$this->query($query);

				if($this->num_rows() > 0)
				{
					while($this->next_record())
					{
						$arrManager[] = $this->fetchrow();
					}
				}
				//print_r($arrManager);
			return $arrManager;
		}
	}
?>
<?php
include_once('db_mysql.php');
	class attendance extends DB_Sql
	{
		function __construct()
		{
		}

		function fnInsertAttendance($arrEmployee)
		{
			//echo '<pre>';
			//print_r($arrEmployee);

			$date = $arrEmployee['hdndate'];
			//echo "<pre>";

			$fieldArr = array("in_time","out_time","break1_in","break1_out","break2_in","break2_out","break3_in","break3_out");

			foreach ($arrEmployee[hdnemployeeid] as $value)
			{
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

				//echo $arrEmployee['hdnattendanceid'][$value];
				$checklate = $this->fnValidateLateComming($value,$arrEmployee['in_time'][$value]);
				//echo  $checklate;
				$id_attendance  =  $this->fnGetAttendance($date,$value);
				//echo $id_attendance;
				//echo $arrEmployee['hdnattendanceid'][$value];

				//print_r($arrEmployee);


				//echo $breack_two = $arrEmployee['break2_out'][$value] -  $arrEmployee['break2_in'][$value];
				//echo $total_break = $breack_one + $breack_two;




				 $query = "Select ADDTIME (ADDTIME (TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00'),TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00')) AS totalbreak, if(ADDTIME (ADDTIME (TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00'),TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00')) > '00:44:00','1','0') as isExceeded";

				$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$totalBreak = $this->f("totalbreak");
					$breakExceed = $this->f("isExceeded");
				}
			}


				if($id_attendance == '0')
				{
					$arrNewRecords = array("user_id"=>$value,"date"=>$date,"in_time"=>$arrEmployee['in_time'][$value].':00',"out_time"=>$arrEmployee['out_time'][$value].':00',"break1_in"=>$arrEmployee['break1_in'][$value].':00',"break1_out"=>$arrEmployee['break1_out'][$value].':00',"break2_in"=>$arrEmployee['break2_in'][$value].':00',"break2_out"=>$arrEmployee['break2_out'][$value].':00',"break3_in"=>$arrEmployee['break3_in'][$value].':00',"break3_out"=>$arrEmployee['break3_out'][$value].':00',"leave_id"=>$arrEmployee['leave_id'][$value],"is_late"=>$checklate,"total_break_time"=>$totalBreak,"isExceededBreak"=>$breakExceed);
					$this->insertArray('pms_attendance',$arrNewRecords);
					//print_r($arrNewRecords);
				}
				else
				{
					$arrNewRecords = array("id"=>$arrEmployee['hdnattendanceid'][$value],"user_id"=>$value,"date"=>$date,"in_time"=>$arrEmployee['in_time'][$value].':00',"out_time"=>$arrEmployee['out_time'][$value].':00',"break1_in"=>$arrEmployee['break1_in'][$value].':00',"break1_out"=>$arrEmployee['break1_out'][$value].':00',"break2_in"=>$arrEmployee['break2_in'][$value].':00',"break2_out"=>$arrEmployee['break2_out'][$value].':00',"break3_in"=>$arrEmployee['break3_in'][$value].':00',"break3_out"=>$arrEmployee['break3_out'][$value].':00',"leave_id"=>$arrEmployee['leave_id'][$value],"is_late"=>$checklate,"total_break_time"=>$totalBreak,"isExceededBreak"=>$breakExceed);
					//print_r($arrNewRecords); die;
					$this->updateArray('pms_attendance',$arrNewRecords);
					//print_r($arrNewRecords);
				}
			}
			//print_r($arrNewRecords);
			//die;
			return true;
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
			$query = "SELECT * FROM `pms_attendance` WHERE `id` = '".mysql_escape_string($id)."' ";
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
					$query = "DELETE FROM `pms_attendance` WHERE `id` = '".mysql_escape_string($arrval)."'";
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
			$query = "SELECT id as employee_id ,name as employee_name FROM `pms_employee`  WHERE `designation` IN('6', '7', '13')";
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
			$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON employee.id = attendance.user_id  WHERE (employee.`teamleader` = '$id' OR employee.id='$id') AND employee.designation NOT IN(6,8,17)";
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
			//$query = "SELECT id as employee_id ,name as employee_name FROM `pms_employee`  WHERE (`teamleader` = '$id' OR id='$id') AND designation NOT IN(6,8,17)";
			if($id == '')
			{
				$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date')  WHERE employee.designation NOT IN(6,8,17) ";
			}
			else
			{
				$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date')  WHERE (employee.`teamleader` = '$id' OR employee.id='$id') AND employee.designation NOT IN(6,8,17) ";
			}
			$db->query($query);
			if($db->num_rows() > 0)
			{
				while($db->next_record())
				{
					$arrEmployeeValues[$db->f("employee_id")] = $db->fetchrow();
					if($id != $db->f("employee_id"))
					{
						$tmpData = $this->fnGetEmployeeDetails1($db->f("employee_id"),$date);
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

		function fnValidateLateComming($EmployeeId, $InTime)
		{
			//echo $EmployeeId.'----'.$InTime;
			$sSQL = "select ADDTIME(starttime,'00:05:00') as start_time, starttime from pms_employee e INNER JOIN pms_shift_times st ON e.shiftid = st.id where e.id='".mysql_escape_string($EmployeeId)."'";
			$this->query($sSQL);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrAttendanceValues = $this->f("start_time");
				}
			}
			//echo $InTime.':00'.'======'.$arrAttendanceValues; die;
			if($InTime <= $arrAttendanceValues)
			{
				return 0;
			}
			else
			{
				return 1;
			}

		}


		function fetchAttendenceData($start, $end)
		{
			$arrHighlights = array();

			$sSQL = "SELECT a.id as aid, e.name, l.title, l.color as colorcode, date_format(a.`date`,'%Y-%m-%d') as startdate FROM pms_attendance a INNER JOIN pms_employee e ON a.user_id = e.id INNER JOIN pms_leave_type l ON a.leave_id = l.id where date_format(a.`date`,'%Y-%m-%d') BETWEEN '$start' AND '$end' AND a.leave_id NOT IN (0,9,10)";
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
			echo $query = "SELECT COUNT(`user_id`) as total_count FROM `pms_attendance` WHERE `user_id` = '$id' AND DATE_FORMAT(date,'%Y') ='$year' AND `leave_id` = '2'";
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
			$sSQL = "select * from pms_attendance where user_id='".mysql_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_escape_string($arrInfo["date"])."'";
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
	}
?>

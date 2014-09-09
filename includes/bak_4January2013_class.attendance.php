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

			foreach ($arrEmployee[hdnemployeeid] as $value)
			{
			//echo $arrEmployee['hdnattendanceid'][$value];
				if(isset($arrEmployee['hdnattendanceid'][$value]) && $arrEmployee['hdnattendanceid'][$value] != '')
				{
					$arrNewRecords = array("id"=>$arrEmployee['hdnattendanceid'][$value],"user_id"=>$value,"date"=>$date,"in_time"=>$arrEmployee['in_time'][$value].':00',"out_time"=>$arrEmployee['out_time'][$value].':00',"break1_in"=>$arrEmployee['break1_in'][$value].':00',"break1_out"=>$arrEmployee['break1_out'][$value].':00',"break2_in"=>$arrEmployee['break2_in'][$value].':00',"break2_out"=>$arrEmployee['break2_out'][$value].':00',"break3_in"=>$arrEmployee['break3_in'][$value].':00',"break3_out"=>$arrEmployee['break3_out'][$value].':00');
					$this->updateArray('pms_attendance',$arrNewRecords);
				}
				else
				{
					$arrNewRecords = array("user_id"=>$value,"date"=>$date,"in_time"=>$arrEmployee['in_time'][$value].':00',"out_time"=>$arrEmployee['out_time'][$value].':00',"break1_in"=>$arrEmployee['break1_in'][$value].':00',"break1_out"=>$arrEmployee['break1_out'][$value].':00',"break2_in"=>$arrEmployee['break2_in'][$value].':00',"break2_out"=>$arrEmployee['break2_out'][$value].':00',"break3_in"=>$arrEmployee['break3_in'][$value].':00',"break3_out"=>$arrEmployee['break3_out'][$value].':00');
				$this->insertArray('pms_attendance',$arrNewRecords);
				}
			}
			return true;
		}
		function fnGetAllAttendances()
		{
			$arrAttendanceValues = array();
			$query = "SELECT DISTINCT date FROM `pms_attendance` ";
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



	}
?>
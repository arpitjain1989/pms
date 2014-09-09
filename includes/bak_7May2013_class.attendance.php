<?php
	include_once('db_mysql.php');

	class attendance extends DB_Sql
	{
		function __construct()
		{
		}



		/*function fnInsertAttendance($arrEmployee)
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
				//echo '<pre>';
				//print_r($arrEmployee);
				$checkTeamLeader = $this->fnGetTeamLeaderId($value);
				//echo 'checkTeamLeader'.$checkTeamLeader;
				$checklate = 0;
				$late_time = '00:00:00';
				//echo 'intime---'.$intim.'--shiftInTime---'.$ActualshiftTimings['starttime'];
				//echo '<br>outim----'.$outim.'--shiftOutTime---'.$ActualshiftTimings['endtime'];

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
								$difference = strtotime(Date('Y-m-d')." ".$intim) - strtotime(Date('Y-m-d')." ".$ActualshiftInTime['movement_totime']);
								$late_time = gmdate("H:i:s", $difference);
							}
					}
				}

				$id_attendance  =  $this->fnGetAttendance($date,$value);
				$Actual_exceedTime = '00:00:00';
				$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00'),TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00')) AS totalbreak, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00'),TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break4_out'][$value].":00'
,'".$arrEmployee['break4_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00')) > '00:44:00','1','0') as isExceeded";

				$this->query($query);
				if($this->num_rows() > 0)
					{
						if($this->next_record())
						{
							$totalBreak = $this->f("totalbreak");
							$breakExceed = $this->f("isExceeded");
							if($breakExceed == '1')
							{
								$exceedTime = strtotime(Date('Y-m-d')." ".$totalBreak) - strtotime(Date('Y-m-d')." 00:44:00");
								$Actual_exceedTime = gmdate("H:i:s", $exceedTime);
							}

						}
					}

				$differ = '00:00:00';
				$differ1 = '00:00:00';

				if($checkTeamLeader == '7' || $checkTeamLeader == '13')
				{

					if($totalBreak == '0' || $totalBreak =='00:00:00')
					{
						$query1 = "Select TIMEDIFF('$outim','$intim') as differ1";
					}
					else
					{
						$query1 = "Select TIMEDIFF(TIMEDIFF('$outim','$intim'),'$totalBreak') as differ1";
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
				else
				{
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
							$query1 = "Select TIMEDIFF('$outim','$intim') as differ1";
						}
						else
						{
							$query1 = "Select TIMEDIFF(TIMEDIFF('$outim','$intim'),'$totalBreak') as differ1";
						}


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

				//echo '<br>working_hours_difference'.$differ1.'<br>';
				//die;
				if($id_attendance == '0')
				{
					$arrNewRecords = array("user_id"=>$value,"date"=>$date,"in_time"=>$arrEmployee['in_time'][$value].':00',"out_time"=>$arrEmployee['out_time'][$value].':00',"break1_in"=>$arrEmployee['break1_in'][$value].':00',"break1_out"=>$arrEmployee['break1_out'][$value].':00',"break2_in"=>$arrEmployee['break2_in'][$value].':00',"break2_out"=>$arrEmployee['break2_out'][$value].':00',"break3_in"=>$arrEmployee['break3_in'][$value].':00',"break3_out"=>$arrEmployee['break3_out'][$value].':00',"break4_in"=>$arrEmployee['break4_in'][$value].':00',"break4_out"=>$arrEmployee['break4_out'][$value].':00',"break5_in"=>$arrEmployee['break5_in'][$value].':00',"break5_out"=>$arrEmployee['break5_out'][$value].':00',"leave_id"=>$arrEmployee['leave_id'][$value],"is_late"=>$checklate,"total_break_time"=>$totalBreak,"isExceededBreak"=>$breakExceed,"late_time"=>$late_time,"official_total_working_hours"=>$differ,"total_working_hours"
=>$differ1,"break_exceed_time"=>$Actual_exceedTime);
					$this->insertArray('pms_attendance',$arrNewRecords);
					//print_r($arrNewRecords);
				}
				else
				{
					//echo 'hello';
					$arrNewRecords = array("id"=>$arrEmployee['hdnattendanceid'][$value],"user_id"=>$value,"date"=>$date,"in_time"=>$arrEmployee['in_time'][$value].':00',"out_time"=>$arrEmployee['out_time'][$value].':00',"break1_in"=>$arrEmployee['break1_in'][$value].':00',"break1_out"=>$arrEmployee['break1_out'][$value].':00',"break2_in"=>$arrEmployee['break2_in'][$value].':00',"break2_out"=>$arrEmployee['break2_out'][$value].':00',"break3_in"=>$arrEmployee['break3_in'][$value].':00',"break3_out"=>$arrEmployee['break3_out'][$value].':00',"break4_in"=>$arrEmployee['break4_in'][$value].':00',"break4_out"=>$arrEmployee['break4_out'][$value].':00',"break5_in"=>$arrEmployee['break5_in'][$value].':00',"break5_out"=>$arrEmployee['break5_out'][$value].':00',"leave_id"=>$arrEmployee['leave_id'][$value],"is_late"=>$checklate,"total_break_time"=>$totalBreak,"isExceededBreak"=>$breakExceed,"late_time"=>$late_time
,"official_total_working_hours"=>$differ,"total_working_hours"=>$differ1,"break_exceed_time"=>$Actual_exceedTime);
					//print_r($arrNewRecords); die;
					$this->updateArray('pms_attendance',$arrNewRecords);
					//print_r($arrNewRecords);
				}
				//echo '<pre>';
				//print_r($arrNewRecords);
			}
		//die;
			//print_r($arrNewRecords);
			return true;
		}*/

		function fnInsertAttendance($arrEmployee)
		{
			set_time_limit(0);

			$date = $arrEmployee['hdndate'];

			$fieldArr = array("in_time","out_time","break1_in","break1_out","break2_in","break2_out","break3_in","break3_out","break4_in","break4_out","break5_in","break5_out");
			//echo '<pre>'; print_r($arrEmployee); die;
			foreach ($arrEmployee['hdnemployeeid'] as $value)
			{
				//echo '<br>'.$value.'<br>';
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
				//echo 'intime'.$intim.'outim--- '.$outim; 
				$ActualshiftTimings = $this->fnGetShiftTimes($value,$arrEmployee['in_time'][$value],$arrEmployee['leave_id'][$value],$arrEmployee['shift_id'][$value],$arrEmployee['hdndate']);
				//print_r($arrEmployee);
				//echo '<br>hellohere<br>';
				//print_r($ActualshiftTimings);
				//echo '<br>hellohere<br>';
				$checkTeamLeader = $this->fnGetTeamLeaderId($value);
				//echo '<br>checkTeamLeader------'.$checkTeamLeader;
				$checklate = 0;
				$late_time = '00:00:00';
				
				if($intim != '00:00:00' && $outim != '00:00:00')
				{
					if($arrEmployee['leave_id'][$value] == 14)
					{
						$arrShiftMovementTime = $this->fnValidateLateComming1($value,$arrEmployee['in_time'][$value],$arrEmployee['leave_id'][$value],$arrEmployee['shift_id'][$value],$arrEmployee['hdndate']);
						//echo 'yourhere';
						//print_r($arrShiftMovementTime);
						//echo 'yourhere';
						if(count($arrShiftMovementTime) > 0)
						{
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
							if($intim > $ActualshiftTimings['starttime'])
							{
								$checklate = 1;
								$difference = strtotime(Date('Y-m-d')." ".$intim) - strtotime(Date('Y-m-d')." ".$ActualshiftTimings['starttime']);
								$late_time = gmdate("H:i:s", $difference);
							}
						}
					}
					else if($arrEmployee['leave_id'][$value] == 4 || $arrEmployee['leave_id'][$value] == 5 || $arrEmployee['leave_id'][$value] == 12 )
					{
							$checklate = 0;
							$difference = '00:00:00';
							$late_time = '00:00:00';
					}
					else
					{
						//echo '<br>yourhere1<br>';
						//echo '<br>intim----'.$intim.'<br>ActualshiftTimings---'.$ActualshiftTimings['starttime'];
						//echo '<br>yourhere1<br>';
						if($intim > $ActualshiftTimings['starttime'])
						{
							$checklate = 1;
							$difference = strtotime(Date('Y-m-d')." ".$intim) - strtotime(Date('Y-m-d')." ".$ActualshiftTimings['starttime']);
							$late_time = gmdate("H:i:s", $difference);
						}
					}
				}
				//echo 'checklate'.$checklate.'difference'.$difference; //die;

				$id_attendance  =  $this->fnGetAttendance($date,$value);

				//$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00'),TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00')) AS totalbreak, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00'),TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00')) > '00:44:00','1','0') as isExceeded";


				/****************Get total break times from all five breaks***********************/
				$Actual_exceedTime = '00:00:00';
				
				/* Get employee designation */
				
				include_once("class.employee.php");
				$objEmployee = new employee();
				$emp_designation = $objEmployee->fnGetEmployeeDesignation($value);
				
				if($emp_designation == "24" || $emp_designation == "25")
				{
					/* IT Support */
					$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('".$arrEmployee['break1_out'][$value].":00' < '".$arrEmployee['break1_in'][$value].":00' ,ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break1_in'][$value].":00'), '".$arrEmployee['break1_out'][$value].":00'),TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00')) ,if('".$arrEmployee['break2_out'][$value].":00' < '".$arrEmployee['break2_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break2_in'][$value].":00'), '".$arrEmployee['break2_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00'))),if('".$arrEmployee['break3_out'][$value].":00' < '".$arrEmployee['break3_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break3_in'][$value].":00'), '".$arrEmployee['break3_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00'))),if('".$arrEmployee['break4_out'][$value].":00' < '".$arrEmployee['break4_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break4_in'][$value].":00'), '".$arrEmployee['break4_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00'))),if('".$arrEmployee['break5_out'][$value].":00' < '".$arrEmployee['break5_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break5_in'][$value].":00'), '".$arrEmployee['break5_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00'))) AS totalbreak, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('".$arrEmployee['break1_out'][$value].":00' < '".$arrEmployee['break1_in'][$value].":00' ,ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break1_in'][$value].":00'), '".$arrEmployee['break1_out'][$value].":00'),TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00')),if('".$arrEmployee['break2_out'][$value].":00' < '".$arrEmployee['break2_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break2_in'][$value].":00'), '".$arrEmployee['break2_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00'))),if('".$arrEmployee['break3_out'][$value].":00' < '".$arrEmployee['break3_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break3_in'][$value].":00'), '".$arrEmployee['break3_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00'))),if('".$arrEmployee['break4_out'][$value].":00' < '".$arrEmployee['break4_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break4_in'][$value].":00'), '".$arrEmployee['break4_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00'))),if('".$arrEmployee['break5_out'][$value].":00' < '".$arrEmployee['break5_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break5_in'][$value].":00'), '".$arrEmployee['break5_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00'))) > '01:00:00','1','0') as isExceeded";
				}
				else
				{
					$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('".$arrEmployee['break1_out'][$value].":00' < '".$arrEmployee['break1_in'][$value].":00' ,ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break1_in'][$value].":00'), '".$arrEmployee['break1_out'][$value].":00'),TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00')) ,if('".$arrEmployee['break2_out'][$value].":00' < '".$arrEmployee['break2_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break2_in'][$value].":00'), '".$arrEmployee['break2_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00'))),if('".$arrEmployee['break3_out'][$value].":00' < '".$arrEmployee['break3_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break3_in'][$value].":00'), '".$arrEmployee['break3_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00'))),if('".$arrEmployee['break4_out'][$value].":00' < '".$arrEmployee['break4_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break4_in'][$value].":00'), '".$arrEmployee['break4_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00'))),if('".$arrEmployee['break5_out'][$value].":00' < '".$arrEmployee['break5_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break5_in'][$value].":00'), '".$arrEmployee['break5_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00'))) AS totalbreak, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('".$arrEmployee['break1_out'][$value].":00' < '".$arrEmployee['break1_in'][$value].":00' ,ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break1_in'][$value].":00'), '".$arrEmployee['break1_out'][$value].":00'),TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00')),if('".$arrEmployee['break2_out'][$value].":00' < '".$arrEmployee['break2_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break2_in'][$value].":00'), '".$arrEmployee['break2_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00'))),if('".$arrEmployee['break3_out'][$value].":00' < '".$arrEmployee['break3_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break3_in'][$value].":00'), '".$arrEmployee['break3_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00'))),if('".$arrEmployee['break4_out'][$value].":00' < '".$arrEmployee['break4_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break4_in'][$value].":00'), '".$arrEmployee['break4_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00'))),if('".$arrEmployee['break5_out'][$value].":00' < '".$arrEmployee['break5_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break5_in'][$value].":00'), '".$arrEmployee['break5_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00'))) > '00:40:00','1','0') as isExceeded";
				}

				$this->query($query);
				if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$totalBreak = $this->f("totalbreak");
						$breakExceed = $this->f("isExceeded");
						if($breakExceed == '1')
						{
							if($emp_designation == "24" || $emp_designation == "25")
							{
								$exceedTime = strtotime(Date('Y-m-d')." ".$totalBreak) - strtotime(Date('Y-m-d')." 01:00:00");
							}
							else
							{
								$exceedTime = strtotime(Date('Y-m-d')." ".$totalBreak) - strtotime(Date('Y-m-d')." 00:40:00");
							}
							$Actual_exceedTime = gmdate("H:i:s", $exceedTime);
						}
					}
				}
					//echo '<br>'.'totalBreak'.$totalBreak.'<br>';
					//echo 'breakExceed'.$breakExceed.'<br>';
				$differ = '00:00:00';
				$differ1 = '00:00:00';

				$allowed = array(6,7,13,18,19,20,21,22,23,24,25,26,27,28);

				//if($checkTeamLeader == '7' || $checkTeamLeader == '13' || $checkTeamLeader == '6')
				if(in_array($checkTeamLeader,$allowed))
				{
					/* For team leader and manager */

					if($intim != '00:00:00' && $outim != '00:00:00')
					{
						if($outim < $intim)
						{
							//echo '<br>hello2<br>';
							$qQuery = "Select ADDTIME(TIMEDIFF('24:00:00','$intim'),'$outim') as differ1";
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
							//echo '<br>hello3<br>';
							if($totalBreak == '0' || $totalBreak =='00:00:00')
							{
								$query1 = "Select TIMEDIFF('$outim','$intim') as differ1";
							}
							else
							{
								$query1 = "Select TIMEDIFF(TIMEDIFF('$outim','$intim'),'$totalBreak') as differ1";
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
					//echo '<pre>';
					//echo 'actualshifttimings<br>';
					//print_r($ActualshiftTimings);
					///echo 'actualshifttimings<br>';
					//echo '<br>hello1<br>';
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
						/*else if($outim < $ActualshiftTimings['starttime'])
						{
							$officialShiftEndTime = $ActualshiftTimings['endtime'];
						}*/
						else if($outim < $intim)
						{
							$officialShiftEndTime = $ActualshiftTimings['endtime'];
						}
						else
						{
							$officialShiftEndTime = $outim;
						}

						if($totalBreak == '0' || $totalBreak =='00:00:00')
						{
							if($officialShiftEndTime < $officialShiftStartTime)
							{
								$query = "Select ADDTIME(TIMEDIFF('24:00:00','$officialShiftStartTime'),'$officialShiftEndTime') as differ";
							}
							else
							{
								$query = "Select TIMEDIFF('$officialShiftEndTime','$officialShiftStartTime') as differ";
							}
						}
						else
						{
							if($officialShiftEndTime < $officialShiftStartTime)
							{
								$query = "Select TIMEDIFF(ADDTIME(TIMEDIFF('24:00:00','$officialShiftStartTime'),'$officialShiftEndTime'),'$totalBreak') as differ";
							}
							else
							{
								$query = "Select TIMEDIFF(TIMEDIFF('$officialShiftEndTime','$officialShiftStartTime'),'$totalBreak') as differ";
							}
						}
					//echo $query;
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
						//echo $query1;
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
				//echo '<br>differ----'.$differ.'----differ1---'.$differ1.'<br>';
				//echo '<br>working_hours_difference'.$differ1.'<br>';
				//die;
				if($id_attendance == '0')
				{
					//echo 'hello1<br>';

					$arrNewRecords = array("user_id"=>$value,"date"=>$date,"in_time"=>$arrEmployee['in_time'][$value].':00',"out_time"=>$arrEmployee['out_time'][$value].':00',"break1_in"=>$arrEmployee['break1_in'][$value].':00',"break1_out"=>$arrEmployee['break1_out'][$value].':00',"break2_in"=>$arrEmployee['break2_in'][$value].':00',"break2_out"=>$arrEmployee['break2_out'][$value].':00',"break3_in"=>$arrEmployee['break3_in'][$value].':00',"break3_out"=>$arrEmployee['break3_out'][$value].':00',"break4_in"=>$arrEmployee['break4_in'][$value].':00',"break4_out"=>$arrEmployee['break4_out'][$value].':00',"break5_in"=>$arrEmployee['break5_in'][$value].':00',"break5_out"=>$arrEmployee['break5_out'][$value].':00',"leave_id"=>$arrEmployee['leave_id'][$value],"is_late"=>$checklate,"total_break_time"=>$totalBreak,"isExceededBreak"=>$breakExceed,"late_time"=>$late_time,"official_total_working_hours"=>$differ,"total_working_hours"=>$differ1,"break_exceed_time"=>$Actual_exceedTime,"shift_id"=>$arrEmployee['shift_id'][$value]);
					$this->insertArray('pms_attendance',$arrNewRecords);
					//print_r($arrNewRecords);
				}
				else
				{
					//echo '<br>hello<br>';
					$arrNewRecords = array("id"=>$arrEmployee['hdnattendanceid'][$value],"user_id"=>$value,"date"=>$date,"in_time"=>$arrEmployee['in_time'][$value].':00',"out_time"=>$arrEmployee['out_time'][$value].':00',"break1_in"=>$arrEmployee['break1_in'][$value].':00',"break1_out"=>$arrEmployee['break1_out'][$value].':00',"break2_in"=>$arrEmployee['break2_in'][$value].':00',"break2_out"=>$arrEmployee['break2_out'][$value].':00',"break3_in"=>$arrEmployee['break3_in'][$value].':00',"break3_out"=>$arrEmployee['break3_out'][$value].':00',"break4_in"=>$arrEmployee['break4_in'][$value].':00',"break4_out"=>$arrEmployee['break4_out'][$value].':00',"break5_in"=>$arrEmployee['break5_in'][$value].':00',"break5_out"=>$arrEmployee['break5_out'][$value].':00',"leave_id"=>$arrEmployee['leave_id'][$value],"is_late"=>$checklate,"total_break_time"=>$totalBreak,"isExceededBreak"=>$breakExceed,"late_time"=>$late_time, "official_total_working_hours"=>$differ ,"total_working_hours"=>$differ1,"break_exceed_time"=>$Actual_exceedTime,"shift_id"=>$arrEmployee['shift_id'][$value]);
					//echo '<pre>'; print_r($arrNewRecords);
					$this->updateArray('pms_attendance',$arrNewRecords);
					//print_r($arrNewRecords);
				}
				
				
				
				/* Send mail if absent for 3 days */
				$this->fnSendAbsentMail($arrNewRecords["user_id"]);

				//echo '<pre>';
				//echo '<br>';
				//print_r($arrNewRecords);
				//echo '<br>';
			}
		//die;
			//print_r($arrNewRecords);
			return true;
		}

		/* Send mail if absent for 3 days */
		function fnSendAbsentMail($user)
		{
			$arrDt = explode("-",$date);

			include_once("class.employee.php");
			$objEmployee = new employee();

			$sSQL = "select *, date_format(date,'%Y-%m-%d') as date from pms_attendance where leave_id='3' and user_id ='".mysql_real_escape_string($user)."' order by date desc limit 0,1";
			$this->query($sSQL);
			if($this->num_rows())
			{
				if($this->next_record())
				{
					/* Check if absent for 2 days before */
					$lastDt = $this->f("date");
					$attendanceid = $this->f("date");
					$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($user)."' and (date_format(date,'%Y-%m-%d') =  date_format(DATE_SUB('".$lastDt."',INTERVAL 1 DAY),'%Y-%m-%d') or date_format(date,'%Y-%m-%d') =  date_format(DATE_SUB('".$lastDt."',INTERVAL 2 DAY),'%Y-%m-%d')) and leave_id='3'";
					$this->query($sSQL);
					if($this->num_rows() == 2)
					{
						/* Absent for 2 days so check if the next day present (anything other then not absent) */
						$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($user)."' and date_format(date,'%Y-%m-%d') =  date_format(DATE_ADD('".$lastDt."',INTERVAL 1 DAY),'%Y-%m-%d') and ((leave_id='0' and in_time='00:00:00') or leave_id in (9,10))";
						$this->query($sSQL);
						if($this->num_rows() > 0)
						{
							/* Check if data for the date already entered */
							
							$sSQL = "select * from pms_attrition_process where userid='".mysql_real_escape_string($user)."' and attendance_date='".mysql_real_escape_string($lastDt)."' and attendance_id='".mysql_real_escape_string($attendanceid)."'";
							$this->query($sSQL);
							if($this->num_rows() == 0)
							{
								/* If data not entered yet then send mail */
								
								/* check if any previous actions taken */
								$sSQL = "select *, date_format(manager_holdtill,'%Y-%m-%d') as manager_holdtill, date_format(hr_holdtill,'%Y-%m-%d') as hr_holdtill, date_format(tl_holdtill,'%Y-%m-%d') as tl_holdtill from pms_attrition_process where userid='".mysql_real_escape_string($user)."' order by id desc limit 0,1";
								$this->query($sSQL);
								if($this->num_rows() > 0)
								{
									if($this->next_record())
									{
										if($this->f("manager_status") == 1)
										{
											/* Termanite process */
											return false;
										}
										else if($this->f("manager_status") == 2)
										{
											/* Hold */
											
											/* check manager hold date, if current date less then hold date then do nothing else send mail */
											if($this->f("manager_holdtill") >= $lastDt)
											{
												return false;
											}
										}
										else
										{
											/* No action performed by manager check status of HR */
											if($this->f("hr_status") == 1)
											{
												/* Termanite process */
												return false;
											}
											else if($this->f("hr_status") == 2)
											{
												/* Hold */
												/* check manager hold date, if current date less then hold date then do nothing else send mail */
												if($this->f("hr_holdtill") >= $lastDt)
												{
													return false;
												}
											}
											else
											{
												/* No action performed by manager check status of HR */
												if($this->f("tl_status") == 1)
												{
													/* Termanite process */
													return false;
												}
												else if($this->f("tl_status") == 2)
												{
													/* Hold */
													/* check manager hold date, if current date less then hold date then do nothing else send mail */
													if($this->f("tl_holdtill") >= $lastDt)
													{
														return false;
													}
												}
											}
										}
									}
								}
								
								/* Insert in attrition table */
								$AttritionInfo["userid"] = $user;
								$AttritionInfo["attendance_id"] = $attendanceid;
								$AttritionInfo["attendance_date"] = $lastDt;
								$AttritionInfo["tl_status"] = 0;
								$AttritionInfo["tlapprovalcode"] = attrition_uid();
								$AttritionInfo["manager_status"] = 0;
								$AttritionInfo["managerapprovalcode"] = attrition_uid();
								$AttritionInfo["hr_status"] = 0;
								$AttritionInfo["hrapprovalcode"] = attrition_uid();
								$AttritionInfo["admin_status"] = 0;
								$AttritionInfo["adminapprovalcode"] = attrition_uid();
								$AttritionInfo["addedon"] = Date('Y-m-d H:i:s');
								
								/* Send mail to team leader */
								$reportingHead = $objEmployee->fnGetReportingHeadId($user);
								$arrReportingHead = $objEmployee->fnGetEmployeeDetailById($reportingHead);
								$EmployeeInfo = $objEmployee->fnGetEmployeeDetailById($user);
								
								if($arrReportingHead["designation"] == 7 || $arrReportingHead["designation"] == 13)
								{
									$AttritionInfo["tlid"] = $arrReportingHead["id"];
									$AttritionInfo["managerid"] = $arrReportingHead["teamleader"]; 
									
								}
								else if($arrReportingHead["designation"] == 6 || $arrReportingHead["designation"] == 18 || $arrReportingHead["designation"] == 19)
								{
									$AttritionInfo["managerid"] = $arrReportingHead["id"];
								}
								
								$this->insertArray("pms_attrition_process",$AttritionInfo);
								
								$tempContent = "Kindly be informed that <b>" . $EmployeeInfo["name"]."</b> is <b>ABSENT</b> since last 3 consecutive days. As per the process, HR would be sending the show cause notice/terminate to him/her.<br/><br/>";
								$tempContentFooter = "<br><br>HR would send the Show Cause Notice if we do not hear from you by EOD today.<br><br>Regards,<br>".SITEADMINISTRATOR;
								
								$Subject = "Attrition process";
								
								if(count($arrReportingHead) > 0)
								{
									if($arrReportingHead["designation"] == 7 || $arrReportingHead["designation"] == 13)
									{
										/* Reporting head is a team leader */
										
										$MailTo = $arrReportingHead["email"];
							
										$content = "Dear ".$arrReportingHead["name"].",<br><br>".$tempContent;
										$content .= "Please click either <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["tlapprovalcode"]."_Terminate_AP]'>(Send Notice/terminate)</a> or <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["tlapprovalcode"]."_Hold_AP]'>HOLD</a> to confirm your action. ".$tempContentFooter;
										
										sendmail($MailTo, $Subject, $content);
										
										/* Send mail to the manager */
										$managerId = $arrReportingHead["teamleader"]; 
										if($managerId != 0)
										{
											$arrManager = $objEmployee->fnGetEmployeeDetailById($managerId);
											
											$MailTo = $arrManager["email"];
							
											$content = "Dear ".$arrManager["name"].",<br><br>".$tempContent;
											$content .= "Please click either <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["managerapprovalcode"]."_Terminate_AP]'>(Send Notice/terminate)</a> or <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["managerapprovalcode"]."_Hold_AP]'>HOLD</a> to confirm your action.".$tempContentFooter;
											
											sendmail($MailTo, $Subject, $content);
											
										}
									}
									else if($arrReportingHead["designation"] == 6 || $arrReportingHead["designation"] == 18 || $arrReportingHead["designation"] == 19)
									{
										/* If logged in as manager */
										$MailTo = $arrReportingHead["email"];
						
										$content = "Dear ".$arrReportingHead["name"].",<br><br>".$tempContent;
										$content .= "Please click either <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["managerapprovalcode"]."_Terminate_AP]'>(Send Notice/terminate)</a> or <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["managerapprovalcode"]."_Hold_AP]'>HOLD</a> to confirm your action.".$tempContentFooter;
										
										sendmail($MailTo, $Subject, $content);
										
									}
								}
								
								/* Send mail to HR */
								$MailTo = "hr@transformsolution.net";
						
								$content = "Dear HR,<br><br>".$tempContent;
								$content .= "Please click either <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["hrapprovalcode"]."_Terminate_AP]'>(Send Notice/terminate)</a> or <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["hrapprovalcode"]."_Hold_AP]'>HOLD</a> to confirm your action.".$tempContentFooter;
								
								sendmail($MailTo, $Subject, $content);
								
								/* Send mail to Admin */
								$MailTo = "admin@transformsolution.net";
						
								$content = "Dear Admin,<br><br>".$tempContent;
								$content .= "Please click either <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["adminapprovalcode"]."_Terminate_AP]'>(Send Notice/terminate)</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["adminapprovalcode"]."_Hold_AP]'>HOLD</a> to confirm your action.".$tempContentFooter;
								
								sendmail($MailTo, $Subject, $content);
								
							}						
						}
					}
				}
			}
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

		function fnGetAttendanceDetails($date,$id)
		{
			$arrAttendanceValues = array();
			$query = "SELECT *, time_format(late_time,'%H:%i') as late_time, time_format(break_exceed_time,'%H:%i') as break_exceed_time FROM `pms_attendance` WHERE date = '$date' AND user_id = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrAttendanceValues = $this->fetchrow();
				}
			}

			return $arrAttendanceValues;
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
				if($this->next_record())
				{
					$arrAttendanceValues = $this->fetchrow();
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
			$query = "SELECT id as employee_id ,name as employee_name FROM `pms_employee`  WHERE `designation` IN('6','18','19', '7','13')";
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
			$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON employee.id = attendance.user_id  WHERE (employee.`teamleader` = '$id' OR employee.id='$id') AND employee.designation NOT IN(6,8,17,18,19)";
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
				$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in,DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out,DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in,DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date')  WHERE employee.designation NOT IN(6,8,17,18,19)  order by employee.name";
			}
			else
			{
				$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in,DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out,DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in,DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date')  WHERE (employee.`teamleader` = '$id' OR employee.id='$id') AND employee.designation NOT IN(6,8,17,18,19)  order by employee.name";
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

		function fnGetEmployeeDetails2($id,$date)
		{
			$db = new DB_Sql();
			$arrEmployeeValues = array();


			include_once('includes/class.roster.php');
			$objRoster = new roster();

			//$query = "SELECT id as employee_id ,name as employee_name FROM `pms_employee`  WHERE (`teamleader` = '$id' OR id='$id') AND designation NOT IN(6,8,17)";
			if($id == '')
			{
				$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in,DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out,DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in,DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id,late_time as late_time,break_exceed_time as break_exceed_time FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date')    order by employee.name";
			}
			else
			{
				$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in,DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out,DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in,DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date')  WHERE (employee.`teamleader` = '$id' OR employee.id='$id')   order by employee.name ASC";
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
						$tmpData = $this->fnGetEmployeeDetails2($db->f("employee_id"),$date);
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

				$sSQL = "select id,movement_fromtime,movement_totime from pms_shift_movement where userid='$EmployeeId' and date_format(`movement_date`,'%Y-%m-%d') = '$date' and (approvedby_manager in (0,1) and (approvedby_manager='0' and delegatedmanager_id!='0' and delegatedmanager_status in (0,1)))";
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
				echo $sqlnew = "SELECT ADDTIME( starttime, '00:05:00' ) AS start_time, starttime FROM pms_shift_times WHERE id ='$shiftId' and `approvedby_manager` in (0,1)";
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


		/*function fetchAttendenceData($start, $end, $ids)
		{
			$arrHighlights = array();
			//echo SELECT DAYOFWEEK('2013-03-31');
			//echo $sSQL = "SELECT a.id as aid, e.name, l.title, l.color as colorcode, date_format(a.`date`,'%Y-%m-%d') as startdate FROM pms_attendance a INNER JOIN pms_employee e ON a.user_id = e.id INNER JOIN pms_leave_type l ON a.leave_id = l.id where date_format(a.`date`,'%Y-%m-%d') BETWEEN '$start' AND '$end' AND e.id IN($ids) and (a.leave_id NOT IN (0,10) OR( a.leave_id = 9 AND date_format('%w') != 0))";
			echo $sSQL = "SELECT  a.id as aid, e.name, l.title, l.color as colorcode, date_format(a.`date`,'%Y-%m-%d') as startdate FROM pms_attendance a INNER JOIN pms_employee e ON a.user_id = e.id INNER JOIN pms_leave_type l ON a.leave_id = l.id where date_format(a.`date`,'%Y-%m-%d') BETWEEN '$start' AND '$end' AND e.id IN($ids) and (a.leave_id NOT IN (0,9,10) OR ( a.leave_id = 9 AND date_format(a.`date`,'%w') != 0) OR (a.leave_id = 10 AND date_format(a.`date`,'%Y-%m-%d') NOT IN (select date_format(holidaydate,'%Y-%m-%d') from pms_holidays)))";

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
		}*/

		function fetchAttendenceData($start, $end, $ids)
		{
			$arrHighlights = array();
			//echo SELECT DAYOFWEEK('2013-03-31');
			//echo $sSQL = "SELECT a.id as aid, e.name, l.title, l.color as colorcode, date_format(a.`date`,'%Y-%m-%d') as startdate FROM pms_attendance a INNER JOIN pms_employee e ON a.user_id = e.id INNER JOIN pms_leave_type l ON a.leave_id = l.id where date_format(a.`date`,'%Y-%m-%d') BETWEEN '$start' AND '$end' AND e.id IN($ids) and (a.leave_id NOT IN (0,10) OR( a.leave_id = 9 AND date_format('%w') != 0))";
			$sSQL = "SELECT  a.id as aid, e.id as eid, e.name, l.title, l.color as colorcode, date_format(a.`date`,'%Y-%m-%d') as startdate FROM pms_attendance a INNER JOIN pms_employee e ON a.user_id = e.id INNER JOIN pms_leave_type l ON a.leave_id = l.id where date_format(a.`date`,'%Y-%m-%d') BETWEEN '$start' AND '$end' AND e.id IN($ids) and (a.leave_id NOT IN (0,9,10) OR ( a.leave_id = 9 AND date_format(a.`date`,'%w') != 0) OR (a.leave_id = 10 AND date_format(a.`date`,'%Y-%m-%d') NOT IN (select date_format(holidaydate,'%Y-%m-%d') from pms_holidays)))";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$displaystr = "";
					if($_SESSION["id"] == $this->f("eid"))
						$displaystr = $this->f("title");
					else
						$displaystr = $this->f("name") . " - " . $this->f("title");

					$arrHighlights[] = array(
											'id' => $this->f("aid"),
											'title' => $displaystr,
											'start' => $this->f("startdate"),
											'color' => $this->f("colorcode")
										);
				}
			}

			return $arrHighlights;
		}

		function fnGetBreaksAndLate($start, $end, $ids)
		{
			$arrHighlights = array();
			/*$sSQL = "SELECT  a.id as aid, e.name, l.title, l.color as colorcode, date_format(a.`date`,'%Y-%m-%d') as startdate FROM pms_attendance a INNER JOIN pms_employee e ON a.user_id = e.id INNER JOIN pms_leave_type l ON a.leave_id = l.id where date_format(a.`date`,'%Y-%m-%d') BETWEEN '$start' AND '$end' AND e.id IN($ids) and (a.leave_id NOT IN (0,9,10) OR ( a.leave_id = 9 AND date_format(a.`date`,'%w') != 0) OR (a.leave_id = 10 AND date_format(a.`date`,'%Y-%m-%d') NOT IN (select date_format(holidaydate,'%Y-%m-%d') from pms_holidays)))";*/
			$sSQL = "SELECT a.id as aid, e.name, date_format(a.`date`,'%Y-%m-%d') as startdate, a.is_late, a.isExceededBreak, time_format(a.late_time,'%H:%i') as late_time, time_format(a.break_exceed_time,'%H:%i') as break_exceed_time FROM pms_attendance a INNER JOIN pms_employee e ON a.user_id = e.id  where date_format(a.`date`,'%Y-%m-%d') BETWEEN '$start' AND '$end' AND e.id  = '$ids' and (a.is_late='1' || a.isExceededBreak='1')";

			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					if($this->f("is_late") == "1")
					{
						$arrHighlights[] = array(
											'id' => $this->f("aid"),
											/*'title' => $this->f("name") . " - PLT [ ".$this->f("late_time")." ]",*/
											'title' => "PLT [".$this->f("late_time")."]",
											'start' => $this->f("startdate"),
											'color' => '#DB9EA6'
										);
					}
					if($this->f("isExceededBreak") == "1")
					{
						$arrHighlights[] = array(
											'id' => $this->f("aid"),
											/*'title' => $this->f("name") . " - Break exceed [ ".$this->f("break_exceed_time")." ]",*/
											'title' => "Break exceed [".$this->f("break_exceed_time")."]",
											'start' => $this->f("startdate"),
											'color' => "#ECC3BF"
										);
					}
				}
			}

			return $arrHighlights;
		}

		function fnGetAllUnApprove($id,$leave_id,$year)
		{
			$totalCount = '';
			//$query = "SELECT nodays as number_d,status as status_t,status_manager as status_m,DATE_FORMAT(start_date,'%d-%m-%Y') AS start_d,DATE_FORMAT(end_date,'%d-%m-%Y') AS end_d FROM `pms_leave_form` WHERE `employee_id` = '$id' AND DATE_FORMAT(start_date,'%Y') ='$year' AND `id` != '$leave_id' ORDER BY id";
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
			//$sSQL = "select a.id as attendanceid, e.id as employeeid, e.name, time_format(official_total_working_hours,'%H:%i') as workhours, ishoursapproved, time_format(additionaltime,'%H:%i') as additionaltime, is_late, time_format(late_time,'%H:%i') as late_time, isExceededBreak, time_format(break_exceed_time,'%H:%i') as break_exceed_time from pms_attendance a INNER JOIN pms_employee e ON e.id = a.user_id where ((((a.official_total_working_hours between '07:10:00' and '07:19:00' and a.leave_id='0') or (a.official_total_working_hours between '05:10:00' and '05:19:00' and a.leave_id='14')) and a.user_id in (select id from pms_employee where designation in (5, 9, 10, 11, 12, 14, 15, 16 ,20 ,21, 22,23,24,25,26,27,28))) or (((a.official_total_working_hours < '07:20:00' and a.leave_id='0') or (a.official_total_working_hours < '05:20:00' and a.leave_id='14')) and a.user_id in  (select id from pms_employee where designation in (7,13))) or a.is_late='1') and date_format(a.date,'%Y-%m-%d') = '".mysql_real_escape_string($date)."' order by workhours desc";
			$sSQL = "select a.id as attendanceid, e.id as employeeid, e.name, time_format(official_total_working_hours,'%H:%i') as workhours, ishoursapproved, time_format(additionaltime,'%H:%i') as additionaltime, is_late, time_format(late_time,'%H:%i') as late_time, isExceededBreak, time_format(break_exceed_time,'%H:%i') as break_exceed_time from pms_attendance a INNER JOIN pms_employee e ON e.id = a.user_id where ((((a.official_total_working_hours between '07:10:00' and '07:19:00' and a.leave_id='0') or (a.official_total_working_hours between '05:10:00' and '05:19:00' and a.leave_id='14')) and a.user_id in (select id from pms_employee where designation in (5, 9, 10, 11, 12, 14, 15, 16 ,20 ,21, 22,23,24,25,26,27,28))) or (((a.official_total_working_hours < '07:20:00' and a.leave_id='0') or (a.official_total_working_hours < '05:20:00' and a.leave_id='14')) and a.user_id in  (select id from pms_employee where designation in (7,13)))) and date_format(a.date,'%Y-%m-%d') = '".mysql_real_escape_string($date)."' order by workhours desc";
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

			$query = "SELECT id as manager_id,name as manager_name FROM  `pms_employee` WHERE `designation` in (6,18,19) order by name";
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

			$query = "SELECT id as teamleader_id,name as teamleader_name FROM  `pms_employee` WHERE `designation` in(7,13) order by name";
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
		
		function fnGetAllLeaveForLeaveReport($id,$startdate,$enddate)
		{
			$arrLeave = array();

			$query = "SELECT id FROM `pms_attendance` WHERE `user_id`= '$id' and DATE_FORMAT(`date`,'%Y-%m-%d') between '$startdate' and '$enddate' and leave_id in(1,2)";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrLeave[] = $this->fetchrow();
				}
			}
			$count = count($arrLeave);
			return $count;
		}

		function fnGetCompensatedExceedByUserAndType($UserId, $Type)
		{
			$arrCompensated = array();
			$sSQL = "select distinct attendance_id from pms_exceed_compensation where compensationfor='$Type' and userid='$UserId' and (approvedby_tl in (0,1) or (approvedby_tl='0' and delegatedtl_id!='0' and delegatedtl_status in (0,1)))";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrCompensated[] = $this->f("attendance_id");
				}
			}

			return $arrCompensated;
		}

		function fnGetUnCompensatedExceedByUser($UserId)
		{
			$arrExceeds = array();

			$arrCompensatedLate = $this->fnGetCompensatedExceedByUserAndType($UserId, '1');
			/*$arrCompensatedBreaks = $this->fnGetCompensatedExceedByUserAndType($UserId, '2');*/

			if(count($arrCompensatedLate) > 0)
				$arrCompensatedLate = array_filter($arrCompensatedLate, 'strlen');
			$arrCompensatedLate[] = 0;
			$strCompensatedLate = implode(",",$arrCompensatedLate);

			/*if(count($arrCompensatedBreaks) > 0)
				$arrCompensatedBreaks = array_filter($arrCompensatedBreaks, 'strlen');
			$arrCompensatedBreaks[] = 0;
			$strCompensatedBreaks = implode(",",$arrCompensatedBreaks);*/

			/*$sSQL = "(select id as attendanceid, date_format(date,'%Y-%m-%d') as date, time_format(late_time,'%H:%i') as compensationtime, '1' as exceedfor from pms_attendance where user_id='$UserId' and is_late='1' and time_format(late_time,'%H:%i') > '00:15' and id not in ($strCompensatedLate))
			UNION
			(select id as attendanceid, date_format(date,'%Y-%m-%d') as date, time_format(break_exceed_time,'%H:%i') as compensationtime, '2' as exceedfor from pms_attendance where user_id='$UserId' and isExceededBreak='1' and time_format(break_exceed_time,'%H:%i') > '00:15' and id not in ($strCompensatedBreaks)) order by date";*/
			$sSQL = "select id as attendanceid, date_format(date,'%Y-%m-%d') as date, time_format(late_time,'%H:%i') as compensationtime, '1' as exceedfor from pms_attendance where user_id='$UserId' and is_late='1' and time_format(late_time,'%H:%i') > '00:15' and id not in ($strCompensatedLate) order by date";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$arrExceeds[] = $this->fetchrow();
				}
			}

			return $arrExceeds;
		}

		function fnSaveCompensation($compensationInfo)
		{
			/* Save compensation for break exceed and late comming */
			
			$compensationInfo["userid"] = $_SESSION["id"];
			
			$arrAttendanceId = explode("-",$compensationInfo["attendance_id"]);
			
			$compensationInfo["attendance_id"] = $arrAttendanceId[0];
			$compensationInfo["compensationfor"] = $arrAttendanceId[1]; /* 1 => Late comming, 2 => Break exceed */
			
			if($compensationInfo["compensation_fromtime_ampm"] == "am" && $compensationInfo["compensation_fromtime_hour"] == 12)
			{
				$compensationInfo["compensation_fromtime"] = ($compensationInfo["compensation_fromtime_hour"] + 12) . ":".$compensationInfo["compensation_fromtime_minutes"];
			}
			else if($compensationInfo["compensation_fromtime_ampm"] == "pm" && $compensationInfo["compensation_fromtime_hour"] != 12)
			{
				$compensationInfo["compensation_fromtime"] = ($compensationInfo["compensation_fromtime_hour"] + 12) . ":".$compensationInfo["compensation_fromtime_minutes"];
			}
			else
			{
				$compensationInfo["compensation_fromtime"] = $compensationInfo["compensation_fromtime_hour"] . ":" . $compensationInfo["compensation_fromtime_minutes"];
			}

			if($compensationInfo["compensation_totime_ampm"] == "am" && $compensationInfo["compensation_totime_hour"] == 12)
			{
				$compensationInfo["compensation_totime"] = ($compensationInfo["compensation_totime_hour"] + 12) . ":".$compensationInfo["compensation_totime_minutes"];
			}
			else if($compensationInfo["compensation_totime_ampm"] == "pm" && $compensationInfo["compensation_totime_hour"] != 12)
			{
				$compensationInfo["compensation_totime"] = ($compensationInfo["compensation_totime_hour"] + 12) . ":".$compensationInfo["compensation_totime_minutes"];
			}
			else
			{
				$compensationInfo["compensation_totime"] = $compensationInfo["compensation_totime_hour"] . ":" . $compensationInfo["compensation_totime_minutes"];
			}
			include_once('class.employee.php');

			$objEmployee = new employee();
			
			$reportingHead = $objEmployee->fnGetReportingHeadId($_SESSION["id"]);

			$compensationInfo["firstreportingheadid"] = $reportingHead;

			$reportinghead2 = $objEmployee->fnGetReportingHeadId($reportingHead);

			$compensationInfo["secondreportingheadid"] = $reportinghead2;
			$compensationInfo["addedon"] = Date('Y-m-d H:i:s');
			$compensationInfo["approvedby_tl"] = 0;
			
			$compensationInfo["tlapprovalcode"] = compensationform_uid();

			/* Begin Block for delegation */
			include_once("class.leave.php");

			$objLeave = new leave();

			$checkDeligateReportingHead1Id = $objLeave->fnCheckDeligate($reportingHead);

			$delegateReportingHead1 = 0;
			if(isset($checkDeligateReportingHead1Id) && $checkDeligateReportingHead1Id != '')
			{
				$delegateReportingHead1 = $checkDeligateReportingHead1Id;
			}

			$compensationInfo["delegatedtl_id"] = $delegateReportingHead1;
			$compensationInfo["delegatedtl_status"] = 0;

			if($compensationInfo["delegatedtl_id"] != "")
				$compensationInfo["delegatedtlapprovalcode"] = compensationform_uid();

			/* End Block for delegation */

			$this->insertArray('pms_exceed_compensation',$compensationInfo);

			$Subject = 'Late comming compensation';
			$uniqueCode = $compensationInfo["tlapprovalcode"];
			
			$curEmployee = $objEmployee->fnGetEmployeeDetailById($_SESSION["id"]);
			$tlInfo = $objEmployee->fnGetEmployeeDetailById($reportingHead);
			
			$content = "Dear ".$tlInfo['name'].", <br /><br />".$curEmployee["name"]." has added a compensation for his/her late coming on ".$compensationInfo["exceedondate"]."<br /><br />";
			
			if($uniqueCode != "")
			{
				$content .= "Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Approve_C]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$uniqueCode."_Reject_C]'>Reject</a></b> for letting us know your decision.";
			}
			$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
			//sendmail($reportingheads['email'],$Subject,$content);

			sendmail($tlInfo['email'],$Subject,$content);
			//sendmail1("chandni.patel@transformsolution.net",$Subject,$content);

			/* Send mail to delegated team leader */
			if($compensationInfo["delegatedtl_id"] != 0)
			{
				$DelegatedTL = $objEmployee->fnGetEmployeeDetailById($compensationInfo["delegatedtl_id"]);
				$MailTo = $DelegatedTL["email"];

				$content = "Dear ".$DelegatedTL['name'].", <br /><br />".$curEmployee["name"]." has added a compensation for late comming on ".$compensationInfo["exceedondate"]."<br /><br />";
				
				if($uniqueCode != "")
				{
					$content .= "Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$compensationInfo["delegatedtlapprovalcode"]."_Approve_C]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$compensationInfo["delegatedtlapprovalcode"]."_Reject_C]'>Reject</a></b> for letting us know your decision.";
				}

				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

				sendmail($MailTo, $Subject, $content);
			}

			return true;
		}
		
		function fnGetAllCompensationsByUser($userId)
		{
			$arrCompensations = array();
			$sSQL = "select c.*, c.id as compensationid, date_format(a.date,'%Y-%m-%d') as attendancedate, date_format(c.compensation_date,'%Y-%m-%d') as compensation_date, time_format(c.compensation_fromtime,'%H:%i') as compensation_fromtime, time_format(c.compensation_totime,'%H:%i') as compensation_totime, time_format(a.late_time,'%H:%i') as late_time, time_format(a.break_exceed_time,'%H:%i') as break_exceed_time from pms_exceed_compensation c INNER JOIN pms_attendance a ON c.attendance_id = a.id where c.userid='".mysql_real_escape_string($userId)."'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$tempCompensation = $this->fetchrow();
					
					$approvedby_tl = "Pending";
					if($this->f("approvedby_tl") == "1")
						$approvedby_tl = "Approved";
					else if($this->f("approvedby_tl") == "2")
						$approvedby_tl = "Unapproved";
					
					$tempCompensation["approvedby_tl"] = $approvedby_tl;
					
					$compensationfor = "";
					$compensationfortime = "00:00";
					if($this->f("compensationfor") == "1")
					{
						$compensationfor = "Late comming";
						$compensationfortime = $this->f("late_time");
					}
					else if($this->f("compensationfor") == "2")
					{
						$compensationfor = "Break exceed";
						$compensationfortime = $this->f("break_exceed_time");
					}
						
					$tempCompensation["compensationfor"] = $compensationfor;
					$tempCompensation["compensationfortime"] = $compensationfortime;
					
					$arrCompensations[] = $tempCompensation;
				}
			}
			
			return $arrCompensations;
		}
		
		function fnUserCompensationById($CompensationId)
		{
			$arrCompensations = array();
			$sSQL = "select c.*, date_format(a.date,'%Y-%m-%d') as attendancedate, date_format(c.compensation_date,'%Y-%m-%d') as compensation_date, time_format(c.compensation_fromtime,'%H:%i') as compensation_fromtime, time_format(c.compensation_totime,'%H:%i') as compensation_totime, time_format(a.late_time,'%H:%i') as late_time, time_format(a.break_exceed_time,'%H:%i') as break_exceed_time from pms_exceed_compensation c INNER JOIN pms_attendance a ON c.attendance_id = a.id where c.id='".mysql_real_escape_string($CompensationId)."' and c.userid='".$_SESSION["id"]."'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				if($this->next_record())
				{
					$tempCompensation = $this->fetchrow();
					
					$approvedby_tl = "Pending";
					if($this->f("approvedby_tl") == "1")
						$approvedby_tl = "Approved";
					else if($this->f("approvedby_tl") == "2")
						$approvedby_tl = "Unapproved";
					
					$tempCompensation["approvedby_tl"] = $approvedby_tl;
					
					$compensationfor = "";
					$compensationfortime = "00:00";
					if($this->f("compensationfor") == "1")
					{
						$compensationfor = "Late comming";
						$compensationfortime = $this->f("late_time");
					}
					else if($this->f("compensationfor") == "2")
					{
						$compensationfor = "Break exceed";
						$compensationfortime = $this->f("break_exceed_time");
					}
						
					$tempCompensation["compensationfor"] = $compensationfor;
					$tempCompensation["compensationfortime"] = $compensationfortime;
					
					$arrCompensations = $tempCompensation;
				}
			}
			
			return $arrCompensations;
		}
		
		function fnGetAllTimeCompensationRequest()
		{
			$arrCompensations = array();
			
			include_once("class.employee.php");
			$objEmployee = new employee();
			
			/* Fetch employees who are delegated */
			$arrEmployee = array();
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
						$arrEmployee = $arrEmployee + $arrtemp;
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
						$arrEmployee = $arrEmployee + $arrtemp;
					}
				}
			}
			
			$arrEmployee[] = "";
			
			if(count($arrEmployee) > 0)
			{
				$arrEmployee = array_filter($arrEmployee,'strlen');
			}	
			
			$ids = "0";
			if(count($arrEmployee) > 0)
			{
				$ids = implode(',',$arrEmployee);
			}
			
			$sSQL = "select c.*, c.id as compensationid, date_format(a.date,'%Y-%m-%d') as attendancedate, date_format(c.compensation_date,'%Y-%m-%d') as compensation_date, time_format(c.compensation_fromtime,'%H:%i') as compensation_fromtime, time_format(c.compensation_totime,'%H:%i') as compensation_totime, time_format(a.late_time,'%H:%i') as late_time, time_format(a.break_exceed_time,'%H:%i') as break_exceed_time, e.name as employeename from pms_exceed_compensation c INNER JOIN pms_attendance a ON c.attendance_id = a.id INNER JOIN pms_employee e ON e.id = c.userid where (e.teamleader='".$_SESSION["id"]."' or c.userid in ($ids)) and (c.approvedby_tl='0' or (c.approvedby_tl='0' and c.delegatedtl_id!='0' and c.delegatedtl_status='0'))";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$tempCompensation = $this->fetchrow();
					
					$approvedby_tl = "Pending";
					if($this->f("approvedby_tl") == "1")
						$approvedby_tl = "Approved";
					else if($this->f("approvedby_tl") == "2")
						$approvedby_tl = "Unapproved";

					$tempCompensation["approvedby_tl"] = $approvedby_tl;

					$approvedby_delegatedtl = "Pending";
					if($this->f("delegatedtl_status") == "1")
						$approvedby_delegatedtl = "Approved";
					else if($this->f("delegatedtl_status") == "2")
						$approvedby_delegatedtl = "Unapproved";

					$tempCompensation["delegatedtl_status"] = $approvedby_tl;

					$compensationfor = "";
					$compensationfortime = "00:00";
					if($this->f("compensationfor") == "1")
					{
						$compensationfor = "Late comming";
						$compensationfortime = $this->f("late_time");
					}
					else if($this->f("compensationfor") == "2")
					{
						$compensationfor = "Break exceed";
						$compensationfortime = $this->f("break_exceed_time");
					}
						
					$tempCompensation["compensationfor"] = $compensationfor;
					$tempCompensation["compensationfortime"] = $compensationfortime;
					
					$arrCompensations[] = $tempCompensation;
				}
			}
			
			return $arrCompensations;
		}
		
		function fnGetTimeCompensationRequestById($CompensationId)
		{
			$arrCompensations = array();
			
			
			include_once("class.employee.php");
			$objEmployee = new employee();
			
			/* Fetch employees who are delegated */
			$arrEmployee = array();
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
						$arrEmployee = $arrEmployee + $arrtemp;
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
						$arrEmployee = $arrEmployee + $arrtemp;
					}
				}
			}
			
			$arrEmployee[] = "";
			
			if(count($arrEmployee) > 0)
			{
				$arrEmployee = array_filter($arrEmployee,'strlen');
			}	
			
			$ids = "0";
			if(count($arrEmployee) > 0)
			{
				$ids = implode(',',$arrEmployee);
			}
			
			$sSQL = "select c.*, c.id as compensationid, date_format(a.date,'%Y-%m-%d') as attendancedate, date_format(c.compensation_date,'%Y-%m-%d') as compensation_date, time_format(c.compensation_fromtime,'%H:%i') as compensation_fromtime, time_format(c.compensation_totime,'%H:%i') as compensation_totime, time_format(a.late_time,'%H:%i') as late_time, time_format(a.break_exceed_time,'%H:%i') as break_exceed_time, e.name as employeename, c.delegatedtl_id, c.delegatedtl_status, c.delegatedtl_comment from pms_exceed_compensation c INNER JOIN pms_attendance a ON c.attendance_id = a.id INNER JOIN pms_employee e ON e.id = c.userid where (e.teamleader='".$_SESSION["id"]."' or c.userid in ($ids)) and c.id = '".$CompensationId."'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				if($this->next_record())
				{
					$tempCompensation = $this->fetchrow();
					
					$approvedby_tl = "Pending";
					if($this->f("approvedby_tl") == "1")
						$approvedby_tl = "Approved";
					else if($this->f("approvedby_tl") == "2")
						$approvedby_tl = "Unapproved";
					
					$tempCompensation["approvedby_tl_text"] = $approvedby_tl;

					$approvedby_delegatedtl = "Pending";
					if($this->f("delegatedtl_status") == "1")
						$approvedby_delegatedtl = "Approved";
					else if($this->f("delegatedtl_status") == "2")
						$approvedby_delegatedtl = "Unapproved";
					
					$tempCompensation["approvedby_delegatedtl_text"] = $approvedby_delegatedtl;
					
					$compensationfor = "";
					$compensationfortime = "00:00";
					if($this->f("compensationfor") == "1")
					{
						$compensationfor = "Late comming";
						$compensationfortime = $this->f("late_time");
					}
					else if($this->f("compensationfor") == "2")
					{
						$compensationfor = "Break exceed";
						$compensationfortime = $this->f("break_exceed_time");
					}
						
					$tempCompensation["compensationfor"] = $compensationfor;
					$tempCompensation["compensationfortime"] = $compensationfortime;
					
					$arrCompensations = $tempCompensation;
				}
			}
			
			return $arrCompensations;
		}
		
		function fnUpdateCompensation($compensationInfo)
		{
			$approvalval = 0;
			if(isset($compensationInfo["approvedby_tl"]))
			{
				$approvalval = $compensationInfo["approvedby_tl"];
				$compensationInfo["tl_approveddate"] = Date('Y-m-d H:i:s');
			}
			else if(isset($compensationInfo["delegatedtl_status"]))
			{
				$approvalval = $compensationInfo["delegatedtl_status"];
				$compensationInfo["delegatedtl_date"] = Date('Y-m-d H:i:s');
			}

			$this->updateArray('pms_exceed_compensation',$compensationInfo);

			/* Send mail */

			$status = array("0"=>"Pending", "1"=>"Approved", "2"=>"Unapproved");
			$recInfo = $this->fnGetTimeCompensationRequestById($compensationInfo["id"]);

			if(count($recInfo) > 0)
			{
				include_once("class.employee.php");
				
				$objEmployee = new employee();
				
				$EmployeeInfo = $objEmployee->fnGetEmployeeById($recInfo["userid"]);
				
				if($recInfo["delegatedtl_id"] == $_SESSION["id"])
				{
					/* If logged in user is delegated team leader */
					$DelegatedTlInfo = $objEmployee->fnGetEmployeeById($_SESSION["id"]);

					if($EmployeeInfo["teamleader_id"] != "" && $EmployeeInfo["teamleader_id"] != "0")
					{
						$TlInfo = $objEmployee->fnGetEmployeeById($EmployeeInfo["teamleader_id"]);

						/* Send mail to employee who has added complensation for late comming */
						$MailTo = $EmployeeInfo["email"];
						$Subject = "Late comming compensation ".$status[$compensationInfo["delegatedtl_status"]];

						$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
						$content .= $DelegatedTlInfo["name"]." has ".strtoupper($status[$compensationInfo["delegatedtl_status"]])." your compensation request for late comming on ".$recInfo["attendancedate"];
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);

						/* Send mail to teamleader */
						$MailTo = $TlInfo["email"];
						$content = "Dear ".$TlInfo["name"].",<br><br>";
						$content .= $DelegatedTlInfo["name"]." has ".strtoupper($status[$ApprovalInfo["delegatedtl_status"]])." compensation request of ".$EmployeeInfo["name"]." for late comming on ".$recInfo["attendancedate"];
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);
					}
				}
				else
				{
					/* If logged in user is team leader */
					if($EmployeeInfo["teamleader_id"] != "" && $EmployeeInfo["teamleader_id"] != "0")
					{
						$TlInfo = $objEmployee->fnGetEmployeeById($EmployeeInfo["teamleader_id"]);

						/* Send mail to the employee */
						$MailTo = $EmployeeInfo["email"];
						$Subject = "Late comming compensation ".$status[$compensationInfo["approvedby_tl"]];

						$content = "Dear ".$EmployeeInfo["name"].",<br><br>";
						$content .= $TlInfo["name"]." has ".strtoupper($status[$compensationInfo["approvedby_tl"]])." your compensation request for late comming on ".$recInfo["attendancedate"];
						$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

						sendmail($MailTo, $Subject, $content);

						/* Send mail to delegated team leader */
						if($compensationInfo["delegatedtl_id"] != 0 && $compensationInfo["delegatedtl_id"] != "")
						{
							$DelegatedTlInfo = $objEmployee->fnGetEmployeeById($compensationInfo["delegatedtl_id"]);

							$MailTo = $DelegatedTlInfo["email"];
							$content = "Dear ".$DelegatedTlInfo["name"].",<br><br>";
							$content .= $TlInfo["name"]." has ".strtoupper($status[$compensationInfo["approvedby_tl"]])." compensation request of ".$EmployeeInfo["name"]." for late comming on ".$recInfo["attendancedate"];
							$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
							
							sendmail($MailTo, $Subject, $content);
						}
					}
				}
			}
			return $approvalval;
		}
	
		/*
		 * Fetches Leaves taken by the employee, in the particular month and year
		 * */
		function fnGetEmployeeLeaveByMonthYr($EmployeeId, $Month, $Year)
		{
			$arrLeaves = array();

			$sSQL = "select date_format(a.date,'%Y-%m-%d') as date, l.title from pms_attendance a INNER JOIN pms_leave_type l ON a.leave_id = l.id where a.user_id='".mysql_real_escape_string($EmployeeId)."' and date_format(a.date,'%Y-%m') = '".mysql_real_escape_string($Year)."-".mysql_real_escape_string($Month)."' and a.leave_id in (1,2,4,5,6,7,8) order by a.date";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrLeaves[] = $this->fetchrow();
				}
			}

			return $arrLeaves;
		}
		
		function fnGetManagers($date)
		{
			$arrEmployee = array();

			//$sSQL = "SELECT *, if(attendance.shift_id=0 or attendance.shift_id is null or attendance.shift_id = null , employee.shiftid, attendance.shift_id) as shift_id, DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time, DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time, DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in, DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out, DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in, DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out, DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in, DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out, DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in, DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out, DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in, DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out, employee.id as employee_id, employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation IN (6, 8, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28) order by employee.name";
			$sSQL = "SELECT *, if(attendance.shift_id=0 or attendance.shift_id is null or attendance.shift_id = null , employee.shiftid, attendance.shift_id) as shift_id, DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time, DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time, DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in, DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out, DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in, DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out, DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in, DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out, DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in, DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out, DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in, DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out, employee.id as employee_id, employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation IN (6,18,19) order by employee.name";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchrow();
				}
			}

			return $arrEmployee;
		}

		function fnGetHrs($date)
		{
			$arrEmployee = array();

			$sSQL = "SELECT *, if(attendance.shift_id=0 or attendance.shift_id is null or attendance.shift_id = null , employee.shiftid, attendance.shift_id) as shift_id, DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time, DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time, DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in, DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out, DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in, DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out, DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in, DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out, DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in, DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out, DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in, DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out, employee.id as employee_id, employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation IN (19, 20, 22, 26) order by employee.name";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchrow();
				}
			}

			return $arrEmployee;
		}

		function fnGetAdmins($date)
		{
			$arrEmployee = array();

			$sSQL = "SELECT *, if(attendance.shift_id=0 or attendance.shift_id is null or attendance.shift_id = null , employee.shiftid, attendance.shift_id) as shift_id, DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time, DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time, DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in, DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out, DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in, DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out, DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in, DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out, DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in, DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out, DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in, DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out, employee.id as employee_id, employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation IN ( 18, 21, 27, 28) order by employee.name";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchrow();
				}
			}

			return $arrEmployee;
		}

		function fnGetIts($date)
		{
			$arrEmployee = array();

			$sSQL = "SELECT *, if(attendance.shift_id=0 or attendance.shift_id is null or attendance.shift_id = null , employee.shiftid, attendance.shift_id) as shift_id, DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time, DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time, DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in, DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out, DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in, DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out, DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in, DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out, DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in, DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out, DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in, DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out, employee.id as employee_id, employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation IN (24, 25) order by employee.name";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$arrEmployee[] = $this->fetchrow();
				}
			}

			return $arrEmployee;
		}
		
	}
?>

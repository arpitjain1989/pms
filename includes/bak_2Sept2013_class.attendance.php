<?php
	include_once('db_mysql.php');

	class attendance extends DB_Sql
	{
		function __construct()
		{
		}



		function fnInsertAttendance($arrEmployee)
		{
			set_time_limit(0);

			$date = $arrEmployee['hdndate'];
			$curDay = date("d", strtotime($date));
			$curMonth = date("m", strtotime($date));
			$curYear = date("Y", strtotime($date));

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
						/*echo 'yourhere';
						echo '<pre>';print_r($arrShiftMovementTime);
						echo '<pre>';print_r($ActualshiftTimings);
						echo '<br>intime'.$intim;*/
						//echo 'yourhere';
						if(count($arrShiftMovementTime) > 0)
						{
							if($intim > $arrShiftMovementTime['movement_totime'])
							{
								if($arrShiftMovementTime['movement_totime'] < $ActualshiftTimings['starttime'])
								{
									if($intim >= $ActualshiftTimings['starttime'])
									{
										if($outim < $intim)
										{
											$checklate = 1;
											$difference = strtotime(Date('Y-m-d')." ".$intim) - strtotime(Date('Y-m-d')." ".$ActualshiftTimings['starttime']);
											$late_time = gmdate("H:i:s", $difference);
										}
										else
										{
											$checklate = 1;
											$difference = strtotime(Date('Y-m-d')." ".$intim) - strtotime(Date('Y-m-d')." ".$arrShiftMovementTime['starttime']);
											$late_time = gmdate("H:i:s", $difference);
										}
									}
								}
								else
								{
									$checklate = 1;
									$difference = strtotime(Date('Y-m-d')." ".$intim) - strtotime(Date('Y-m-d')." ".$arrShiftMovementTime['movement_totime']);
									$late_time = gmdate("H:i:s", $difference);
								}
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
				
				/*    Commented on 27August2013 for late coming problem with shift movement in night shift    *************
				 * if($intim != '00:00:00' && $outim != '00:00:00')
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
								if($arrShiftMovementTime['movement_totime'] < $ActualshiftTimings['starttime'])
								{
									if($intim >= $ActualshiftTimings['starttime'])
									{
										$checklate = 1;
										$difference = strtotime(Date('Y-m-d')." ".$intim) - strtotime(Date('Y-m-d')." ".$arrShiftMovementTime['starttime']);
										$late_time = gmdate("H:i:s", $difference);
									}
								}
								else
								{
									$checklate = 1;
									$difference = strtotime(Date('Y-m-d')." ".$intim) - strtotime(Date('Y-m-d')." ".$arrShiftMovementTime['movement_totime']);
									$late_time = gmdate("H:i:s", $difference);
								}
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
				}*/
				//echo 'checklate'.$checklate.'difference'.$difference; //die;

				$id_attendance  =  $this->fnGetAttendance($date,$value);

				//$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00'),TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00')) AS totalbreak, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00'),TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00')),TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00')) > '00:44:00','1','0') as isExceeded";


				/****************Get total break times from all five breaks***********************/
				$Actual_exceedTime = '00:00:00';
				
				/* Get employee designation */
				
				include_once("class.employee.php");
				$objEmployee = new employee();
				$emp_designation = $objEmployee->fnGetEmployeeDesignation($value);
				
				$outime1 = $arrEmployee['break1_out'][$value].':00';
				$intime1 = $arrEmployee['break1_in'][$value].':00';
				
				$outime2 = $arrEmployee['break2_out'][$value].':00';
				$intime2 = $arrEmployee['break2_in'][$value].':00';
				
				$outime3 = $arrEmployee['break3_out'][$value].':00';
				$intime3 = $arrEmployee['break3_in'][$value].':00';
				
				$outime4 = $arrEmployee['break4_out'][$value].':00';
				$intime4 = $arrEmployee['break4_in'][$value].':00';
				
				$outime5 = $arrEmployee['break5_out'][$value].':00';
				$intime5 = $arrEmployee['break5_in'][$value].':00';
				
				//echo 'outime----'.$outime1.'--<br> Intime=== '.$intime1.'<br>--ActualshiftTimings: '.$ActualshiftTimings['starttime'];

				/* calculate break exceed for 1st break exceed */
				if($ActualshiftTimings['endtime'] < $ActualshiftTimings['starttime'])
				{
					if($intime1 < $ActualshiftTimings['starttime'] && $intime1 > $ActualshiftTimings['endtime'] && ($outime1 > $ActualshiftTimings['starttime'] || $outime1 <= $ActualshiftTimings['endtime']))
					{
						$intime1 = $ActualshiftTimings['starttime'];
					}
					else if(($intime1 <= $ActualshiftTimings['endtime'] || $intime1 >= $ActualshiftTimings['starttime']) && $outime1 > $ActualshiftTimings['endtime'] && $outime1 < $ActualshiftTimings['starttime'])
					{
						$outime1 = $ActualshiftTimings['endtime'];
					}
					else if($intime1 < $ActualshiftTimings['starttime'] && $outime1 < $ActualshiftTimings['starttime'] && $intime1 > $ActualshiftTimings['endtime'] && $outime1 > $ActualshiftTimings['endtime'])
					{
						$intime1 = '00:00:00';
						$outime1 = '00:00:00';
					}
					
				}
				else
				{
					if($intime1 < $ActualshiftTimings['starttime'] && $outime1 > $ActualshiftTimings['starttime'] && $outime1 <= $ActualshiftTimings['endtime'])
					{
						$intime1 = $ActualshiftTimings['starttime'];
					}
					else if($intime1 <= $ActualshiftTimings['endtime'] && $intime1 >= $ActualshiftTimings['starttime'] && $outime1 > $ActualshiftTimings['endtime'])
					{
						$outime1 = $ActualshiftTimings['endtime'];
					}
					else if(($intime1 < $ActualshiftTimings['starttime'] && $outime1 < $ActualshiftTimings['starttime']) || ($intime1 > $ActualshiftTimings['endtime'] && $outime1 > $ActualshiftTimings['endtime']))
					{
						$intime1 = '00:00:00';
						$outime1 = '00:00:00';
					}
				}

				/* calculate break exceed for 2nd break exceed */
				if($ActualshiftTimings['endtime'] < $ActualshiftTimings['starttime'])
				{
					if($intime2 < $ActualshiftTimings['starttime'] && $intime2 > $ActualshiftTimings['endtime'] && ($outime2 > $ActualshiftTimings['starttime'] || $outime2 <= $ActualshiftTimings['endtime']))
					{
						$intime2 = $ActualshiftTimings['starttime'];
					}
					else if(($intime2 <= $ActualshiftTimings['endtime'] || $intime2 >= $ActualshiftTimings['starttime']) && $outime2 > $ActualshiftTimings['endtime'] && $outime2 < $ActualshiftTimings['starttime'])
					{
						$outime2 = $ActualshiftTimings['endtime'];
					}
					else if($intime2 < $ActualshiftTimings['starttime'] && $outime2 < $ActualshiftTimings['starttime'] && $intime2 > $ActualshiftTimings['endtime'] && $outime2 > $ActualshiftTimings['endtime'])
					{
						$intime2 = '00:00:00';
						$outime2 = '00:00:00';
					}
					
				}
				else
				{
					if($intime2 < $ActualshiftTimings['starttime'] && $outime2 > $ActualshiftTimings['starttime'] && $outime2 <= $ActualshiftTimings['endtime'])
					{
						$intime2 = $ActualshiftTimings['starttime'];
					}
					else if($intime2 <= $ActualshiftTimings['endtime'] && $intime2 >= $ActualshiftTimings['starttime'] && $outime2 > $ActualshiftTimings['endtime'])
					{
						$outime2 = $ActualshiftTimings['endtime'];
					}
					else if(($intime2 < $ActualshiftTimings['starttime'] && $outime2 < $ActualshiftTimings['starttime']) || ($intime2 > $ActualshiftTimings['endtime'] && $outime2 > $ActualshiftTimings['endtime']))
					{
						$intime2 = '00:00:00';
						$outime2 = '00:00:00';
					}
					
				}

				/* calculate break exceed for 3rd break exceed */
				if($ActualshiftTimings['endtime'] < $ActualshiftTimings['starttime'])
				{
					if($intime3 < $ActualshiftTimings['starttime'] && $intime3 > $ActualshiftTimings['endtime'] && ($outime3 > $ActualshiftTimings['starttime'] || $outime3 <= $ActualshiftTimings['endtime']))
					{
						$intime3 = $ActualshiftTimings['starttime'];
					}
					else if(($intime3 <= $ActualshiftTimings['endtime'] || $intime3 >= $ActualshiftTimings['starttime']) && $outime3 > $ActualshiftTimings['endtime'] && $outime3 < $ActualshiftTimings['starttime'])
					{
						$outime3 = $ActualshiftTimings['endtime'];
					}
					else if($intime3 < $ActualshiftTimings['starttime'] && $outime3 < $ActualshiftTimings['starttime'] && $intime3 > $ActualshiftTimings['endtime'] && $outime3 > $ActualshiftTimings['endtime'])
					{
						$intime3 = '00:00:00';
						$outime3 = '00:00:00';
					}
					
				}
				else
				{
					if($intime3 < $ActualshiftTimings['starttime'] && $outime3 > $ActualshiftTimings['starttime'] && $outime3 <= $ActualshiftTimings['endtime'])
					{
						$intime3 = $ActualshiftTimings['starttime'];
					}
					else if($intime3 <= $ActualshiftTimings['endtime'] && $intime3 >= $ActualshiftTimings['starttime'] && $outime3 > $ActualshiftTimings['endtime'])
					{
						$outime3 = $ActualshiftTimings['endtime'];
					}
					else if(($intime3 < $ActualshiftTimings['starttime'] && $outime3 < $ActualshiftTimings['starttime']) || ($intime3 > $ActualshiftTimings['endtime'] && $outime3 > $ActualshiftTimings['endtime']))
					{
						$intime3 = '00:00:00';
						$outime3 = '00:00:00';
					}
					
				}

				/* calculate break exceed for 4th break exceed */
				if($ActualshiftTimings['endtime'] < $ActualshiftTimings['starttime'])
				{
					if($intime4 < $ActualshiftTimings['starttime'] && $intime4 > $ActualshiftTimings['endtime'] && ($outime4 > $ActualshiftTimings['starttime'] || $outime4 <= $ActualshiftTimings['endtime']))
					{
						$intime4 = $ActualshiftTimings['starttime'];
					}
					else if(($intime4 <= $ActualshiftTimings['endtime'] || $intime4 >= $ActualshiftTimings['starttime']) && $outime4 > $ActualshiftTimings['endtime'] && $outime4 < $ActualshiftTimings['starttime'])
					{
						$outime4 = $ActualshiftTimings['endtime'];
					}
					else if($intime4 < $ActualshiftTimings['starttime'] && $outime4 < $ActualshiftTimings['starttime'] && $intime4 > $ActualshiftTimings['endtime'] && $outime4 > $ActualshiftTimings['endtime'])
					{
						$intime4 = '00:00:00';
						$outime4 = '00:00:00';
					}
					
				}
				else
				{
					if($intime4 < $ActualshiftTimings['starttime'] && $outime4 > $ActualshiftTimings['starttime'] && $outime4 <= $ActualshiftTimings['endtime'])
					{
						$intime4 = $ActualshiftTimings['starttime'];
					}
					else if($intime4 <= $ActualshiftTimings['endtime'] && $intime4 >= $ActualshiftTimings['starttime'] && $outime4 > $ActualshiftTimings['endtime'])
					{
						$outime4 = $ActualshiftTimings['endtime'];
					}
					else if(($intime4 < $ActualshiftTimings['starttime'] && $outime4 < $ActualshiftTimings['starttime']) || ($intime4 > $ActualshiftTimings['endtime'] && $outime4 > $ActualshiftTimings['endtime']))
					{
						$intime4 = '00:00:00';
						$outime4 = '00:00:00';
					}
					
				}

				/* calculate break exceed for 5th break exceed */
				if($ActualshiftTimings['endtime'] < $ActualshiftTimings['starttime'])
				{
					if($intime5 < $ActualshiftTimings['starttime'] && $intime5 > $ActualshiftTimings['endtime'] && ($outime5 > $ActualshiftTimings['starttime'] || $outime5 <= $ActualshiftTimings['endtime']))
					{
						$intime5 = $ActualshiftTimings['starttime'];
					}
					else if(($intime5 <= $ActualshiftTimings['endtime'] || $intime5 >= $ActualshiftTimings['starttime']) && $outime5 > $ActualshiftTimings['endtime'] && $outime5 < $ActualshiftTimings['starttime'])
					{
						$outime5 = $ActualshiftTimings['endtime'];
					}
					else if($intime5 < $ActualshiftTimings['starttime'] && $outime5 < $ActualshiftTimings['starttime'] && $intime5 > $ActualshiftTimings['endtime'] && $outime5 > $ActualshiftTimings['endtime'])
					{
						$intime5 = '00:00:00';
						$outime5 = '00:00:00';
					}
					
				}
				else
				{
					if($intime5 < $ActualshiftTimings['starttime'] && $outime5 > $ActualshiftTimings['starttime'] && $outime5 <= $ActualshiftTimings['endtime'])
					{
						$intime5 = $ActualshiftTimings['starttime'];
					}
					else if($intime5 <= $ActualshiftTimings['endtime'] && $intime5 >= $ActualshiftTimings['starttime'] && $outime5 > $ActualshiftTimings['endtime'])
					{
						$outime5 = $ActualshiftTimings['endtime'];
					}
					else if(($intime5 < $ActualshiftTimings['starttime'] && $outime5 < $ActualshiftTimings['starttime']) || ($intime5 > $ActualshiftTimings['endtime'] && $outime5 > $ActualshiftTimings['endtime']))
					{
						$intime5 = '00:00:00';
						$outime5 = '00:00:00';
					}
				}
				
				/*if($intime1 < $ActualshiftTimings['starttime'] && $outime1 > $ActualshiftTimings['starttime'] && $outime1 <= $ActualshiftTimings['endtime'])
				{
					$intime1 = $ActualshiftTimings['starttime'];
				}
				else if($outime1 > $ActualshiftTimings['endtime'] && $intime1 < $ActualshiftTimings['endtime'] && $intime1 >= $ActualshiftTimings['starttime'])
				{
					$outime1 = $ActualshiftTimings['endtime'];
				}
				else if(($intime1 < $ActualshiftTimings['starttime'] && $outime1 < $ActualshiftTimings['starttime'] ) || ($intime1 > $ActualshiftTimings['endtime'] && $outime1 > $ActualshiftTimings['endtime'] ))
				{
					$intime1 = '00:00:00';
					$outime1 = '00:00:00';
				}
				

				if($intime2 < $ActualshiftTimings['starttime'] && $outime2 > $ActualshiftTimings['starttime'] && $outime2 <= $ActualshiftTimings['endtime'])
				{
					$intime2 = $ActualshiftTimings['starttime'];
				}
				else if($outime2 > $ActualshiftTimings['endtime'] && $intime2 < $ActualshiftTimings['endtime'] && $intime2 >= $ActualshiftTimings['starttime'])
				{
					$outime2 = $ActualshiftTimings['endtime'];
				}
				else if(($intime2 < $ActualshiftTimings['starttime'] && $outime2 < $ActualshiftTimings['starttime'] ) || ($intime2 > $ActualshiftTimings['endtime'] && $outime2 > $ActualshiftTimings['endtime'] ))
				{
					$intime2 = '00:00:00';
					$outime2 = '00:00:00';
				}

				
				if($intime3 < $ActualshiftTimings['starttime'] && $outime3 > $ActualshiftTimings['starttime'] && $outime3 <= $ActualshiftTimings['endtime'])
				{
					$intime3 = $ActualshiftTimings['starttime'];
				}
				else if($outime3 > $ActualshiftTimings['endtime'] && $intime3 < $ActualshiftTimings['endtime'] && $intime3 >= $ActualshiftTimings['starttime'])
				{
					$outime3 = $ActualshiftTimings['endtime'];
				}
				else if(($intime3 < $ActualshiftTimings['starttime'] && $outime3 < $ActualshiftTimings['starttime'] ) || ($intime3 > $ActualshiftTimings['endtime'] && $outime3 > $ActualshiftTimings['endtime'] ))
				{
					$intime3 = '00:00:00';
					$outime3 = '00:00:00';
				}

				
				if($intime4 < $ActualshiftTimings['starttime'] && $outime4 > $ActualshiftTimings['starttime'] && $outime4 <= $ActualshiftTimings['endtime'])
				{
					$intime4 = $ActualshiftTimings['starttime'];
				}
				else if($outime4 > $ActualshiftTimings['endtime'] && $intime4 < $ActualshiftTimings['endtime'] && $intime4 >= $ActualshiftTimings['starttime'])
				{
					$outime4 = $ActualshiftTimings['endtime'];
				}
				else if(($intime4 < $ActualshiftTimings['starttime'] && $outime4 < $ActualshiftTimings['starttime'] ) || ($intime4 > $ActualshiftTimings['endtime'] && $outime4 > $ActualshiftTimings['endtime'] ))
				{
					$intime4 = '00:00:00';
					$outime4 = '00:00:00';
				}

				
				if($intime5 < $ActualshiftTimings['starttime'] && $outime5 > $ActualshiftTimings['starttime'] && $outime5 <= $ActualshiftTimings['endtime'])
				{
					$intime5 = $ActualshiftTimings['starttime'];
				}
				else if($outime5 > $ActualshiftTimings['endtime'] && $intime5 < $ActualshiftTimings['endtime'] && $intime5 >= $ActualshiftTimings['starttime'])
				{
					$outime5 = $ActualshiftTimings['endtime'];
				}
				else if(($intime5 < $ActualshiftTimings['starttime'] && $outime5 < $ActualshiftTimings['starttime'] ) || ($intime5 > $ActualshiftTimings['endtime'] && $outime5 > $ActualshiftTimings['endtime'] ))
				{
					$intime5 = '00:00:00';
					$outime5 = '00:00:00';
				}
				*/

				//echo '<br><br>outime----'.$outime1.'--<br> Intime=== '.$intime1.'<br>--ActualshiftTimings: '.$ActualshiftTimings['starttime'];
				
				/* if($emp_designation == "20" || $emp_designation == "21" || $emp_designation == "22" || $emp_designation == "23" || $emp_designation == "24" || $emp_designation == "25" || $emp_designation == "26" || $emp_designation == "27" || $emp_designation == "28" )
				{
					/* IT Support */
					/*$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')) ,if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) AS totalbreak,ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('".$arrEmployee['break1_out'][$value].":00' < '".$arrEmployee['break1_in'][$value].":00' ,ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break1_in'][$value].":00'), '".$arrEmployee['break1_out'][$value].":00'),TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00')) ,if('".$arrEmployee['break2_out'][$value].":00' < '".$arrEmployee['break2_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break2_in'][$value].":00'), '".$arrEmployee['break2_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00'))),if('".$arrEmployee['break3_out'][$value].":00' < '".$arrEmployee['break3_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break3_in'][$value].":00'), '".$arrEmployee['break3_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00'))),if('".$arrEmployee['break4_out'][$value].":00' < '".$arrEmployee['break4_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break4_in'][$value].":00'), '".$arrEmployee['break4_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00'))),if('".$arrEmployee['break5_out'][$value].":00' < '".$arrEmployee['break5_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break5_in'][$value].":00'), '".$arrEmployee['break5_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00'))) AS complete_break, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')),if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) > '00:45:00','1','0') as isExceeded";
				}
				else
				{
					$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')) ,if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) AS totalbreak,ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('".$arrEmployee['break1_out'][$value].":00' < '".$arrEmployee['break1_in'][$value].":00' ,ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break1_in'][$value].":00'), '".$arrEmployee['break1_out'][$value].":00'),TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00')) ,if('".$arrEmployee['break2_out'][$value].":00' < '".$arrEmployee['break2_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break2_in'][$value].":00'), '".$arrEmployee['break2_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00'))),if('".$arrEmployee['break3_out'][$value].":00' < '".$arrEmployee['break3_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break3_in'][$value].":00'), '".$arrEmployee['break3_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00'))),if('".$arrEmployee['break4_out'][$value].":00' < '".$arrEmployee['break4_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break4_in'][$value].":00'), '".$arrEmployee['break4_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00'))),if('".$arrEmployee['break5_out'][$value].":00' < '".$arrEmployee['break5_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break5_in'][$value].":00'), '".$arrEmployee['break5_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00'))) AS complete_break, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')),if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) > '00:40:00','1','0') as isExceeded";
				}*/

				if($arrEmployee['leave_id'][$value] == '4' || $arrEmployee['leave_id'][$value] == '5' || $arrEmployee['leave_id'][$value] == '8' || $arrEmployee['leave_id'][$value] == '12')
				{
					if($emp_designation == "20" || $emp_designation == "21" || $emp_designation == "22" || $emp_designation == "23" || $emp_designation == "24" || $emp_designation == "25" || $emp_designation == "26" || $emp_designation == "27" || $emp_designation == "28" )
					{
						/* IT Support */
						$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')) ,if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) AS totalbreak,ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('".$arrEmployee['break1_out'][$value].":00' < '".$arrEmployee['break1_in'][$value].":00' ,ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break1_in'][$value].":00'), '".$arrEmployee['break1_out'][$value].":00'),TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00')) ,if('".$arrEmployee['break2_out'][$value].":00' < '".$arrEmployee['break2_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break2_in'][$value].":00'), '".$arrEmployee['break2_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00'))),if('".$arrEmployee['break3_out'][$value].":00' < '".$arrEmployee['break3_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break3_in'][$value].":00'), '".$arrEmployee['break3_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00'))),if('".$arrEmployee['break4_out'][$value].":00' < '".$arrEmployee['break4_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break4_in'][$value].":00'), '".$arrEmployee['break4_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00'))),if('".$arrEmployee['break5_out'][$value].":00' < '".$arrEmployee['break5_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break5_in'][$value].":00'), '".$arrEmployee['break5_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00'))) AS complete_break, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')),if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) > '00:20:00','1','0') as isExceeded";
					}
					else
					{
						$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')) ,if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) AS totalbreak,ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('".$arrEmployee['break1_out'][$value].":00' < '".$arrEmployee['break1_in'][$value].":00' ,ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break1_in'][$value].":00'), '".$arrEmployee['break1_out'][$value].":00'),TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00')) ,if('".$arrEmployee['break2_out'][$value].":00' < '".$arrEmployee['break2_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break2_in'][$value].":00'), '".$arrEmployee['break2_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00'))),if('".$arrEmployee['break3_out'][$value].":00' < '".$arrEmployee['break3_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break3_in'][$value].":00'), '".$arrEmployee['break3_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00'))),if('".$arrEmployee['break4_out'][$value].":00' < '".$arrEmployee['break4_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break4_in'][$value].":00'), '".$arrEmployee['break4_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00'))),if('".$arrEmployee['break5_out'][$value].":00' < '".$arrEmployee['break5_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break5_in'][$value].":00'), '".$arrEmployee['break5_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00'))) AS complete_break, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')),if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) > '00:15:00','1','0') as isExceeded";
					}
				}
				else
				{
					if($emp_designation == "20" || $emp_designation == "21" || $emp_designation == "22" || $emp_designation == "23" || $emp_designation == "24" || $emp_designation == "25" || $emp_designation == "26" || $emp_designation == "27" || $emp_designation == "28" )
					{
						/* IT Support */
						$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')) ,if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) AS totalbreak,ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('".$arrEmployee['break1_out'][$value].":00' < '".$arrEmployee['break1_in'][$value].":00' ,ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break1_in'][$value].":00'), '".$arrEmployee['break1_out'][$value].":00'),TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00')) ,if('".$arrEmployee['break2_out'][$value].":00' < '".$arrEmployee['break2_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break2_in'][$value].":00'), '".$arrEmployee['break2_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00'))),if('".$arrEmployee['break3_out'][$value].":00' < '".$arrEmployee['break3_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break3_in'][$value].":00'), '".$arrEmployee['break3_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00'))),if('".$arrEmployee['break4_out'][$value].":00' < '".$arrEmployee['break4_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break4_in'][$value].":00'), '".$arrEmployee['break4_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00'))),if('".$arrEmployee['break5_out'][$value].":00' < '".$arrEmployee['break5_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break5_in'][$value].":00'), '".$arrEmployee['break5_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00'))) AS complete_break, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')),if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) > '00:45:00','1','0') as isExceeded";
					}
					else
					{
						$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')) ,if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) AS totalbreak,ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('".$arrEmployee['break1_out'][$value].":00' < '".$arrEmployee['break1_in'][$value].":00' ,ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break1_in'][$value].":00'), '".$arrEmployee['break1_out'][$value].":00'),TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00')) ,if('".$arrEmployee['break2_out'][$value].":00' < '".$arrEmployee['break2_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break2_in'][$value].":00'), '".$arrEmployee['break2_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00'))),if('".$arrEmployee['break3_out'][$value].":00' < '".$arrEmployee['break3_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break3_in'][$value].":00'), '".$arrEmployee['break3_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00'))),if('".$arrEmployee['break4_out'][$value].":00' < '".$arrEmployee['break4_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break4_in'][$value].":00'), '".$arrEmployee['break4_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00'))),if('".$arrEmployee['break5_out'][$value].":00' < '".$arrEmployee['break5_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break5_in'][$value].":00'), '".$arrEmployee['break5_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00'))) AS complete_break, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')),if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) > '00:40:00','1','0') as isExceeded";
					}
				}

				$this->query($query);
				if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$totalBreak = $this->f("totalbreak");
						$completeBreak = $this->f("complete_break");
						$breakExceed = $this->f("isExceeded");
						/*if($breakExceed == '1')
						{
							if($emp_designation == "20" || $emp_designation == "21" || $emp_designation == "22" || $emp_designation == "23" || $emp_designation == "24" || $emp_designation == "25" || $emp_designation == "26" || $emp_designation == "27" || $emp_designation == "28" )
							{
								$exceedTime = strtotime(Date('Y-m-d')." ".$totalBreak) - strtotime(Date('Y-m-d')." 00:45:00");
							}
							else
							{
								$exceedTime = strtotime(Date('Y-m-d')." ".$totalBreak) - strtotime(Date('Y-m-d')." 00:40:00");
							}
							$Actual_exceedTime = gmdate("H:i:s", $exceedTime);
						}*/
						if($arrEmployee['leave_id'][$value] == '4' || $arrEmployee['leave_id'][$value] == '5' || $arrEmployee['leave_id'][$value] == '8' || $arrEmployee['leave_id'][$value] == '12')
						{
							if($breakExceed == '1')
							{
								if($emp_designation == "20" || $emp_designation == "21" || $emp_designation == "22" || $emp_designation == "23" || $emp_designation == "24" || $emp_designation == "25" || $emp_designation == "26" || $emp_designation == "27" || $emp_designation == "28" )
								{
									$exceedTime = strtotime(Date('Y-m-d')." ".$totalBreak) - strtotime(Date('Y-m-d')." 00:20:00");
								}
								else
								{
									$exceedTime = strtotime(Date('Y-m-d')." ".$totalBreak) - strtotime(Date('Y-m-d')." 00:15:00");
								}
								$Actual_exceedTime = gmdate("H:i:s", $exceedTime);
							}
						}
						else
						{
							if($breakExceed == '1')
							{
								if($emp_designation == "20" || $emp_designation == "21" || $emp_designation == "22" || $emp_designation == "23" || $emp_designation == "24" || $emp_designation == "25" || $emp_designation == "26" || $emp_designation == "27" || $emp_designation == "28" )
								{
									$exceedTime = strtotime(Date('Y-m-d')." ".$totalBreak) - strtotime(Date('Y-m-d')." 00:45:00");
								}
								else
								{
									$exceedTime = strtotime(Date('Y-m-d')." ".$totalBreak) - strtotime(Date('Y-m-d')." 00:40:00");
								}
								$Actual_exceedTime = gmdate("H:i:s", $exceedTime);
							}
						}
					}
				}
					//echo '<br>'.'totalBreak'.$totalBreak.'<br>';
					//echo 'breakExceed'.$breakExceed.'<br>';
				$differ = '00:00:00';
				$differ1 = '00:00:00';

				$allowed = array(6,7,13,18,19,20,21,22,23,24,25,26,27,28,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44);

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

					$arrNewRecords = array("user_id"=>$value,"date"=>$date,"in_time"=>$arrEmployee['in_time'][$value].':00',"out_time"=>$arrEmployee['out_time'][$value].':00',"break1_in"=>$arrEmployee['break1_in'][$value].':00',"break1_out"=>$arrEmployee['break1_out'][$value].':00',"break2_in"=>$arrEmployee['break2_in'][$value].':00',"break2_out"=>$arrEmployee['break2_out'][$value].':00',"break3_in"=>$arrEmployee['break3_in'][$value].':00',"break3_out"=>$arrEmployee['break3_out'][$value].':00',"break4_in"=>$arrEmployee['break4_in'][$value].':00',"break4_out"=>$arrEmployee['break4_out'][$value].':00',"break5_in"=>$arrEmployee['break5_in'][$value].':00',"break5_out"=>$arrEmployee['break5_out'][$value].':00',"leave_id"=>$arrEmployee['leave_id'][$value],"is_late"=>$checklate,"total_break_time"=>$totalBreak,"isExceededBreak"=>$breakExceed,"late_time"=>$late_time,"official_total_working_hours"=>$differ,"total_working_hours"=>$differ1,"break_exceed_time"=>$Actual_exceedTime,"shift_id"=>$arrEmployee['shift_id'][$value],"complete_break"=>$completeBreak,"last_modified"=>Date('Y-m-d H:i:s'));
					$this->insertArray('pms_attendance',$arrNewRecords);
					//print_r($arrNewRecords);
				}
				else
				{
					//echo '<br>hello<br>';
					$arrNewRecords = array("id"=>$arrEmployee['hdnattendanceid'][$value],"user_id"=>$value,"date"=>$date,"in_time"=>$arrEmployee['in_time'][$value].':00',"out_time"=>$arrEmployee['out_time'][$value].':00',"break1_in"=>$arrEmployee['break1_in'][$value].':00',"break1_out"=>$arrEmployee['break1_out'][$value].':00',"break2_in"=>$arrEmployee['break2_in'][$value].':00',"break2_out"=>$arrEmployee['break2_out'][$value].':00',"break3_in"=>$arrEmployee['break3_in'][$value].':00',"break3_out"=>$arrEmployee['break3_out'][$value].':00',"break4_in"=>$arrEmployee['break4_in'][$value].':00',"break4_out"=>$arrEmployee['break4_out'][$value].':00',"break5_in"=>$arrEmployee['break5_in'][$value].':00',"break5_out"=>$arrEmployee['break5_out'][$value].':00',"leave_id"=>$arrEmployee['leave_id'][$value],"is_late"=>$checklate,"total_break_time"=>$totalBreak,"isExceededBreak"=>$breakExceed,"late_time"=>$late_time, "official_total_working_hours"=>$differ ,"total_working_hours"=>$differ1,"break_exceed_time"=>$Actual_exceedTime,"shift_id"=>$arrEmployee['shift_id'][$value],"complete_break"=>$completeBreak,"last_modified"=>Date('Y-m-d H:i:s'));
					//echo '<pre>'; print_r($arrNewRecords);
					$this->updateArray('pms_attendance',$arrNewRecords);
					//print_r($arrNewRecords);
				}
				
				
				
				/* Send mail if absent for 3 days */
				$this->fnSendAbsentMail($arrNewRecords["user_id"]);

				/* Insert half monthly report for the employee */
				$this->fnCalculateHalfMonthlyReport($arrNewRecords["user_id"],$curDay,$curMonth,$curYear,$emp_designation);
				//die;

				/* Insert monthly report for the employee */
				$this->fnCalculateMonthlyReport($arrNewRecords["user_id"],$curDay,$curMonth,$curYear,$emp_designation);

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

			$curdt = Date('Y-m-d');

			$sSQL = "select *, date_format(date,'%Y-%m-%d') as date from pms_attendance where leave_id='3' and user_id ='".mysql_real_escape_string($user)."' and date_format(date,'%Y-%m-%d') <= '".mysql_real_escape_string($curdt)."' order by date desc limit 0,1";
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
						$checknextpresent = $this->num_rows();
						
						$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($user)."' and date_format(date,'%Y-%m-%d') =  date_format(DATE_ADD('".$lastDt."',INTERVAL 1 DAY),'%Y-%m-%d')";
						$this->query($sSQL);
						$norecordsentered = $this->num_rows();
						
						if($checknextpresent > 0 || $norecordsentered == 0)
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
		
		function fnGetAttendanceByIdAndDate($id,$month,$year)
		{
			$arrAttendanceValues = array();
			$query = "SELECT a.*,DATE_FORMAT(a.date, '%d-%m-%Y') as attendance_date,CONCAT(s.starttime, ' - ', s.endtime) as shift_time FROM `pms_attendance` as a left join pms_shift_times as s on a.shift_id = s.id WHERE `user_id` = '".$id."' and DATE_FORMAT(`date`,'%Y-%m') = '".$year."-".$month."' order by a.date asc";
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
			$arrPost["last_modified"]=Date('Y-m-d H:i:s');
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
			$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON employee.id = attendance.user_id  WHERE (employee.`teamleader` = '$id' OR employee.id='$id') AND employee.designation NOT IN(6,8,17,18,19,44)";
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
				$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in,DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out,DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in,DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date')  WHERE employee.designation NOT IN(6,8,17,18,19,44)  order by employee.name";
			}
			else
			{
				$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in,DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out,DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in,DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date')  WHERE (employee.`teamleader` = '$id' OR employee.id='$id') AND employee.designation NOT IN(6,8,17,18,19,44)  order by employee.name";
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
				$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in,DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out,DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in,DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id,late_time as late_time,break_exceed_time as break_exceed_time FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') where employee.status = '0' order by employee.name";
			}
			else
			{
				$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in,DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out,DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in,DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date')  WHERE (employee.`teamleader` = '$id' OR employee.id='$id') and employee.status = '0' order by employee.name ASC";
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

				$sSQL = "select id,movement_fromtime,movement_totime from pms_shift_movement where userid='$EmployeeId' and date_format(`movement_date`,'%Y-%m-%d') = '$date' and (approvedby_manager = '1' or (approvedby_manager='0' and delegatedmanager_id!='0' and delegatedmanager_status = '1'))";
				
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
			include_once("class.leave.php");
			$objLeave = new leave();
			
			$defaultLeaveType = $originalLeaveType = $arrInfo["leave_id"];

			$originalPendingLeaves = $this->fnGetLastLeaveBalance($arrInfo["user_id"]);

			if(isset($arrInfo["leave_id"]) && $arrInfo["leave_id"] != "" && $arrInfo["leave_id"] != "9" && $arrInfo["leave_id"] != "10" && $arrInfo["leave_id"] != "14")
			{

				if($arrInfo['end_dt'] != '')
				{
					$curMonth = date("m",strtotime($arrInfo["start_dt"]));
					$curYear = date("Y",strtotime($arrInfo["start_dt"]));
				
					while(strtotime($arrInfo["start_dt"]) <= strtotime($arrInfo["end_dt"]))
					{
						$next_monday_date = date("Y-m-d",strtotime('next Monday',mktime(0,0,0,Date('m'),Date('d'),Date('Y'))));
						$temp_start_date = date("Y-m-d",strtotime($arrInfo["start_dt"]));
						if($defaultLeaveType == "1" || $defaultLeaveType == "2")
						{
							if($temp_start_date < $next_monday_date)
								$originalLeaveType = $objLeave->fnGetLeaveTypeIdByTitle("UPL");
							else
								$originalLeaveType = $objLeave->fnGetLeaveTypeIdByTitle("PPL");
						}
						$arrInfo["date"] = $arrInfo["start_dt"];
						//$arrInfo["user_id"] = $this->f("user_id");
						
						/* Fetch leave balance */
						$pendingLeaves = $this->fnGetLastLeaveBalance($arrInfo["user_id"]);
						
						/* Fetch PLWP & ULWP & HLWP marked for the month */
						$unpaid_leaves = $this->fnGetUserLeavesWithoutPayByMonthAndYear($arrInfo["user_id"], $curMonth, $curYear);
						//if($originalLeaveType != 0 && $originalLeaveType == "")
						//{
							if($pendingLeaves == 0.5 && ($originalLeaveType == "1" || $originalLeaveType == "2"))
							{
								if($unpaid_leaves < 3 && $originalPendingLeaves > 0)
								{
									$pendingUnpaidLeaves = 3 - $unpaid_leaves;
									
									if($pendingUnpaidLeaves >= 1)
									{
										if($originalLeaveType == "1")
										{
											/* PPL Marked, Mark as plwp */
											$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("PLWP");
										}
										else if($originalLeaveType == "2")
										{
											/* UPL Marked, Mark as ulwp */
											$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("ULWP");
										}
									}
									else if($pendingUnpaidLeaves >= 0.5)
									{
										/* if no unpaid leaves left, mark as absent */
										$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("A");
									}
								}
								else
								{
									/* if no unpaid leaves left, mark as absent */
									$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("A");
								}
							}
							else if($pendingLeaves <= 0)
							{
								/* check plwp and ulwp for the current month */
								if($unpaid_leaves < 3 && $originalPendingLeaves > 0)
								{
									$pendingUnpaidLeaves = 3 - $unpaid_leaves;

									if($pendingUnpaidLeaves >= 1)
									{
										if($originalLeaveType == "1")
										{
											/* PPL Marked, Mark as plwp */
											$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("PLWP");
										}
										else if($originalLeaveType == "2")
										{
											/* UPL Marked, Mark as ulwp */
											$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("ULWP");
										}
										else if(($originalLeaveType == "4" || $originalLeaveType == "5"))
										{
											/* PHL Marked, Mark as hlwp */
											$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("HLWP");
										}
									}
									else if($pendingUnpaidLeaves >= 0.5)
									{
										if(($originalLeaveType == "4" || $originalLeaveType == "5"))
										{
											/* PHL Marked, Mark as hlwp */
											$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("HLWP");
										}
										else
										{
											/* if no unpaid leaves left, mark as absent */
											$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("A");
										}
									}
								}
								else
								{
									if(($originalLeaveType == "4" || $originalLeaveType == "5"))
									{
										/* if no unpaid leaves left, mark as absent */
										$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("HA");
									}
									else
									{
										/* if no unpaid leaves left, mark as absent */
										$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("A");
									}
								}
							}
							else
							{
								$arrInfo['leave_id'] = $originalLeaveType;
							}
						//}
	//print_r($arrInfo);
	//die;
						$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."'";
						$this->query($sSQL);
						if($this->num_rows() > 0)
						{
							if($this->next_record())
							{
								$query = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date = '".$arrInfo['date']."'";
								$run = mysql_query($query);
								//$this->updateArray('pms_attendance',$arrInfo);
							}
						}
						else
						{
							$arrInfo["last_modified"]=Date('Y-m-d H:i:s');
							$this->insertArray('pms_attendance',$arrInfo);
						}

						$arrInfo["start_dt"] = date ("Y-m-d", strtotime("+1 day", strtotime($arrInfo["start_dt"])));
					}
				}
				else
				{
					
					$next_monday_date = date("Y-m-d",strtotime('next Monday',mktime(0,0,0,Date('m'),Date('d'),Date('Y'))));
					
					$temp_start_date = date("Y-m-d",strtotime($arrInfo["date"]));
					if($defaultLeaveType == "1" || $defaultLeaveType == "2")
					{
						if($temp_start_date < $next_monday_date)
							$originalLeaveType = $objLeave->fnGetLeaveTypeIdByTitle("UPL");
						else
							$originalLeaveType = $objLeave->fnGetLeaveTypeIdByTitle("PPL");
					}
					
					$curMonth = date("m",strtotime($arrInfo["date"]));
					$curYear = date("Y",strtotime($arrInfo["date"]));

					/* Fetch leave balance */
					$pendingLeaves = $this->fnGetLastLeaveBalance($arrInfo["user_id"]);
					
					/* Fetch PLWP & ULWP & HLWP marked for the month */
					$unpaid_leaves = $this->fnGetUserLeavesWithoutPayByMonthAndYear($arrInfo["user_id"], $curMonth, $curYear);
					//if($originalLeaveType != 0 && $originalLeaveType == "")
					//{
						if($pendingLeaves == 0.5 && ($originalLeaveType == "1" || $originalLeaveType == "2"))
						{
							if($unpaid_leaves < 3 && $originalPendingLeaves > 0)
							{
								$pendingUnpaidLeaves = 3 - $unpaid_leaves;
								
								if($pendingUnpaidLeaves >= 1)
								{
									if($originalLeaveType == "1")
									{
										/* PPL Marked, Mark as plwp */
										$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("PLWP");
									}
									else if($originalLeaveType == "2")
									{
										/* UPL Marked, Mark as ulwp */
										$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("ULWP");
									}
								}
								else if($pendingUnpaidLeaves >= 0.5)
								{
									/* if no unpaid leaves left, mark as absent */
									$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("A");
								}
							}
							else
							{
								/* if no unpaid leaves left, mark as absent */
								$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("A");
							}
						}
						else if($pendingLeaves <= 0)
						{
							/* check plwp and ulwp for the current month */
							if($unpaid_leaves < 3 && $originalPendingLeaves > 0)
							{
								$pendingUnpaidLeaves = 3 - $unpaid_leaves;

								if($pendingUnpaidLeaves >= 1)
								{
									if($originalLeaveType == "1")
									{
										/* PPL Marked, Mark as plwp */
										$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("PLWP");
									}
									else if($originalLeaveType == "2")
									{
										/* UPL Marked, Mark as ulwp */
										$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("ULWP");
									}
									else if(($originalLeaveType == "4" || $originalLeaveType == "5"))
									{
										/* PHL Marked, Mark as hlwp */
										$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("HLWP");
									}
								}
								else if($pendingUnpaidLeaves >= 0.5)
								{
									if(($originalLeaveType == "4" || $originalLeaveType == "5"))
									{
										/* PHL Marked, Mark as hlwp */
										$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("HLWP");
									}
									else
									{
										/* if no unpaid leaves left, mark as absent */
										$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("A");
									}
								}
							}
							else
							{
								if(($originalLeaveType == "4" || $originalLeaveType == "5"))
								{
									/* if no unpaid leaves left, mark as absent */
									$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("HA");
								}
								else
								{
									/* if no unpaid leaves left, mark as absent */
									$arrInfo['leave_id'] = $objLeave->fnGetLeaveTypeIdByTitle("A");
								}
							}
						}
						else
						{
							$arrInfo['leave_id'] = $originalLeaveType;
						}
					//}
	//print_r($arrInfo);
					$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."'";
					$this->query($sSQL);
					if($this->num_rows() > 0)
					{
						if($this->next_record())
						{
							$arrInfo["id"] = $this->f("id");
							$arrInfo["last_modified"]=Date('Y-m-d H:i:s');
							$this->updateArray('pms_attendance',$arrInfo);
						}
					}
					else
					{
						$arrInfo["last_modified"]=Date('Y-m-d H:i:s');
						$this->insertArray('pms_attendance',$arrInfo);
					}
				}
			}
			else
			{
				if($arrInfo['end_dt'] != '')
				{
					while(strtotime($arrInfo["start_dt"]) <= strtotime($arrInfo["end_dt"]))
					{
						$arrInfo["date"] = $arrInfo["start_dt"];
						//$arrInfo['leave_id'] = "";
						$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."'";
						$this->query($sSQL);
						if($this->num_rows() > 0)
						{
							if($this->next_record())
							{
								$query = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date = '".$arrInfo['date']."'";
								$run = mysql_query($query);
								//$this->updateArray('pms_attendance',$arrInfo);
							}
						}
						else
						{
							$arrInfo["last_modified"]=Date('Y-m-d H:i:s');
							$this->insertArray('pms_attendance',$arrInfo);
						}

						$arrInfo["start_dt"] = date ("Y-m-d", strtotime("+1 day", strtotime($arrInfo["start_dt"])));
					}
				}
				else
				{
					//$arrInfo['leave_id'] = "";
					
					$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."'";
					$this->query($sSQL);
					if($this->num_rows() > 0)
					{
						if($this->next_record())
						{
							$arrInfo["id"] = $this->f("id");
							$arrInfo["last_modified"]=Date('Y-m-d H:i:s');
							$this->updateArray('pms_attendance',$arrInfo);
						}
					}
					else
					{
						$arrInfo["last_modified"]=Date('Y-m-d H:i:s');
						$this->insertArray('pms_attendance',$arrInfo);
					}
				}
			}
//die;
		}

		function fnGetUserLeavesWithoutPayByMonthAndYear($userid, $month, $year)
		{
			$total_leaves_withoutpay = $total_unpaid_leaves = $total_unpaid_halfday_leaves = 0;
			
			$FirstDayOfMonth = $year."-".$month."-01";
			
			//$sSQL = "select * from pms_attendance where date_format(date,'%Y') = '".mysql_real_escape_string($year)."' and date_format(date,'%m') = '".mysql_real_escape_string($month)."' and user_id='".mysql_real_escape_string($userid)."' and leave_id in (6,7)";
			$sSQL = "select * from pms_attendance where date_format(date,'%Y-%m-%d') >= '".mysql_real_escape_string($FirstDayOfMonth)."' and user_id='".mysql_real_escape_string($userid)."' and leave_id in (6,7)";
			$this->query($sSQL);
			$total_unpaid_leaves = $this->num_rows();
			
			/*$sSQL = "select * from pms_attendance where date_format(date,'%Y') = '".mysql_real_escape_string($year)."' and date_format(date,'%m') = '".mysql_real_escape_string($month)."' and user_id='".mysql_real_escape_string($userid)."' and leave_id = '8'";*/
			$sSQL = "select * from pms_attendance where date_format(date,'%Y-%m-%d') >= '".mysql_real_escape_string($FirstDayOfMonth)."' and user_id='".mysql_real_escape_string($userid)."' and leave_id = '8'";
			$this->query($sSQL);
			$total_unpaid_halfday_leaves = $this->num_rows();

			$total_leaves_withoutpay = $total_unpaid_leaves + ($total_unpaid_halfday_leaves * 0.5);

			return $total_leaves_withoutpay;
		}

		function fnInsertRosterHalfAttendance($arrInfo)
		{
			$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrInfo["id"] = $this->f("id");
					$arrInfo["last_modified"]=Date('Y-m-d H:i:s');
					$this->updateArray('pms_attendance',$arrInfo);
				}
			}
			else
			{
				$arrInfo["last_modified"]=Date('Y-m-d H:i:s');
				$this->insertArray('pms_attendance',$arrInfo);
			}
		}

		function fnGetInsufficientWorkHours($date)
		{
			$arrInsufficientWorkHours = array();
			//$sSQL = "select a.id as attendanceid, e.id as employeeid, e.name, time_format(official_total_working_hours,'%H:%i') as workhours, ishoursapproved, time_format(additionaltime,'%H:%i') as additionaltime, is_late, time_format(late_time,'%H:%i') as late_time, isExceededBreak, time_format(break_exceed_time,'%H:%i') as break_exceed_time from pms_attendance a INNER JOIN pms_employee e ON e.id = a.user_id where ((((a.official_total_working_hours between '07:10:00' and '07:19:00' and a.leave_id='0') or (a.official_total_working_hours between '05:10:00' and '05:19:00' and a.leave_id='14')) and a.user_id in (select id from pms_employee where designation in (5, 9, 10, 11, 12, 14, 15, 16 ,20 ,21, 22,23,24,25,26,27,28))) or (((a.official_total_working_hours < '07:20:00' and a.leave_id='0') or (a.official_total_working_hours < '05:20:00' and a.leave_id='14')) and a.user_id in  (select id from pms_employee where designation in (7,13))) or a.is_late='1') and date_format(a.date,'%Y-%m-%d') = '".mysql_real_escape_string($date)."' order by workhours desc";

			/* This for all days break exceed not for half day need to change because need to show employee that have break exceed in halfday also */
			
			//$sSQL = "select a.id as attendanceid, e.id as employeeid, e.name, time_format(official_total_working_hours,'%H:%i') as workhours, ishoursapproved, time_format(additionaltime,'%H:%i') as additionaltime, is_late, time_format(late_time,'%H:%i') as late_time, isExceededBreak, time_format(break_exceed_time,'%H:%i') as break_exceed_time from pms_attendance a INNER JOIN pms_employee e ON e.id = a.user_id where ((((a.official_total_working_hours between '07:10:00' and '07:19:00' and a.leave_id='0') or (a.official_total_working_hours between '05:10:00' and '05:19:00' and a.leave_id='14')) and a.user_id in (select id from pms_employee where designation in (5, 9, 10, 11, 12, 14, 15, 16 ,20 ,21, 22,23,24,25,26,27,28))) or (((a.official_total_working_hours < '07:20:00' and a.leave_id='0') or (a.official_total_working_hours < '05:20:00' and a.leave_id='14')) and a.user_id in  (select id from pms_employee where designation in (7,13)))) and date_format(a.date,'%Y-%m-%d') = '".mysql_real_escape_string($date)."' and a.leave_id not in(1,2,3,9,10) order by workhours desc";

			/* In halfdaym and shift movement need to included for break exceed approval */

			//$sSQL = "select a.id as attendanceid, e.id as employeeid, e.name, time_format(official_total_working_hours,'%H:%i') as workhours, ishoursapproved, time_format(additionaltime,'%H:%i') as additionaltime, is_late, time_format(late_time,'%H:%i') as late_time, isExceededBreak, time_format(break_exceed_time,'%H:%i') as break_exceed_time from pms_attendance a INNER JOIN pms_employee e ON e.id = a.user_id where ((((a.official_total_working_hours between '07:10:00' and '07:19:00' and a.leave_id='0') or (a.official_total_working_hours between '05:10:00' and '05:19:00' and a.leave_id='14') or (a.break_exceed_time <= '00:10' and isExceededBreak = '1'  and a.leave_id IN(4,5,8,12))) and a.user_id in (select id from pms_employee where designation in (5, 9, 10, 11, 12, 14, 15, 16 ,20 ,21, 22,23,24,25,26,27,28))) or (((a.official_total_working_hours < '07:20:00' and a.leave_id='0') or (a.official_total_working_hours < '05:20:00' and a.leave_id='14')) and a.user_id in  (select id from pms_employee where designation in (7,13)))) and date_format(a.date,'%Y-%m-%d') = '".mysql_real_escape_string($date)."' and a.leave_id not in(1,2,3,9,10) order by workhours desc";

			$sSQL = "select a.id as attendanceid, e.id as employeeid, e.name, time_format(official_total_working_hours,'%H:%i') as workhours, ishoursapproved, time_format(additionaltime,'%H:%i') as additionaltime, is_late, time_format(late_time,'%H:%i') as late_time, isExceededBreak, time_format(break_exceed_time,'%H:%i') as break_exceed_time from pms_attendance a INNER JOIN pms_employee e ON e.id = a.user_id where ((((a.official_total_working_hours >= '07:10:00' and a.leave_id='0' and isExceededBreak = '1') or (a.official_total_working_hours between '05:10:00' and '05:19:00' and a.leave_id='14' and isExceededBreak = '1') or (a.break_exceed_time <= '00:10' and isExceededBreak = '1'  and a.leave_id IN(4,5,8,12))) and a.user_id in (select id from pms_employee where designation in (5, 9, 10, 11, 12, 14, 15, 16 ,20 ,21, 22,23,24,25,26,27,28,30,31,32,33,34,35,36,37,38,39,40,41,42,43))) or (((a.official_total_working_hours < '07:20:00' and a.leave_id='0') or (a.official_total_working_hours < '05:20:00' and a.leave_id='14')) and a.user_id in  (select id from pms_employee where designation in (7,13)))) and date_format(a.date,'%Y-%m-%d') = '".mysql_real_escape_string($date)."' and a.leave_id not in(1,2,3,9,10) order by workhours desc";

			
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

			$query = "SELECT id as manager_id,name as manager_name FROM  `pms_employee` WHERE `designation` in (6,18,19,44) order by name";
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
		
		function fnGetAllCEO()
		{
			$arrManager = array();

			$query = "SELECT id as manager_id,name as manager_name FROM  `pms_employee` WHERE `designation` in (8,17) order by name";
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

			//$startdate = '2013-06-01';
			//$enddate = '2013-06-30';

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
			$sSQL = "select c.*, c.id as compensationid, date_format(a.date,'%d-%m-%Y') as attendancedate, date_format(c.compensation_date,'%d-%m-%Y') as compensation_date, time_format(c.compensation_fromtime,'%H:%i') as compensation_fromtime, time_format(c.compensation_totime,'%H:%i') as compensation_totime, time_format(a.late_time,'%H:%i') as late_time, time_format(a.break_exceed_time,'%H:%i') as break_exceed_time from pms_exceed_compensation c INNER JOIN pms_attendance a ON c.attendance_id = a.id where c.userid='".mysql_real_escape_string($userId)."'";
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
			
			$sSQL = "select c.*, c.id as compensationid, date_format(a.date,'%d-%m-%Y') as attendancedate, date_format(c.compensation_date,'%d-%m-%Y') as compensation_date, time_format(c.compensation_fromtime,'%H:%i') as compensation_fromtime, time_format(c.compensation_totime,'%H:%i') as compensation_totime, time_format(a.late_time,'%H:%i') as late_time, time_format(a.break_exceed_time,'%H:%i') as break_exceed_time, e.name as employeename from pms_exceed_compensation c INNER JOIN pms_attendance a ON c.attendance_id = a.id INNER JOIN pms_employee e ON e.id = c.userid where (e.teamleader='".$_SESSION["id"]."' or c.userid in ($ids)) and (c.approvedby_tl='0' or (c.approvedby_tl='0' and c.delegatedtl_id!='0' and c.delegatedtl_status='0')) and e.status = '0'";
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
			$sSQL = "SELECT *, if(attendance.shift_id=0 or attendance.shift_id is null or attendance.shift_id = null , employee.shiftid, attendance.shift_id) as shift_id, DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time, DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time, DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in, DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out, DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in, DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out, DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in, DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out, DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in, DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out, DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in, DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out, employee.id as employee_id, employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation IN (6,18,19,44) order by employee.name";
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

			//$sSQL = "SELECT *, if(attendance.shift_id=0 or attendance.shift_id is null or attendance.shift_id = null , employee.shiftid, attendance.shift_id) as shift_id, DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time, DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time, DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in, DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out, DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in, DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out, DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in, DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out, DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in, DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out, DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in, DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out, employee.id as employee_id, employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation IN (19, 20, 22, 26) order by employee.name";

			$sSQL = "SELECT *, if(attendance.shift_id=0 or attendance.shift_id is null or attendance.shift_id = null , employee.shiftid, attendance.shift_id) as shift_id, DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time, DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time, DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in, DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out, DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in, DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out, DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in, DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out, DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in, DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out, DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in, DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out, employee.id as employee_id, employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation IN (20, 22, 26) order by employee.name";
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

			//$sSQL = "SELECT *, if(attendance.shift_id=0 or attendance.shift_id is null or attendance.shift_id = null , employee.shiftid, attendance.shift_id) as shift_id, DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time, DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time, DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in, DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out, DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in, DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out, DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in, DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out, DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in, DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out, DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in, DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out, employee.id as employee_id, employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation IN ( 18, 21, 23, 27, 28) order by employee.name";

			$sSQL = "SELECT *, if(attendance.shift_id=0 or attendance.shift_id is null or attendance.shift_id = null , employee.shiftid, attendance.shift_id) as shift_id, DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time, DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time, DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in, DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out, DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in, DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out, DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in, DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out, DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in, DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out, DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in, DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out, employee.id as employee_id, employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation IN (21, 23, 27, 28) order by employee.name";
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
		
		/* Fetch break exceeds for users who have more then 3 break exceeds. This report does not include manager, tl and support */
		function fnGetBreakExceedByYearAndMonth($year, $month)
		{
			$arrBreakExceedInformation = array();
			$db = new DB_Sql();
			
			$sSQL = "select a.user_id, count(a.id) as totalexceed, e.name from pms_attendance a LEFT JOIN pms_employee e ON e.id = a.user_id where a.isExceededBreak='1' and date_format(a.date,'%Y')='".mysql_real_escape_string($year)."' and date_format(a.date,'%m')='".mysql_real_escape_string($month)."' and a.ishoursapproved='0' and e.designation in (5,9,10,11,12,14,15,16,30,31,32,33,34,35,36,37,38,39,40,41,42,43) group by a.user_id having count(a.id) > 3";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrBreakExceedInformation[$this->f("user_id")]["name"] = $this->f("name");
					
					$sSQL = "select date_format(date,'%Y-%m-%d') as exceeddate, time_format(total_break_time, '%H:%i') as total_break_time, time_format(official_total_working_hours, '%H:%i') as official_total_working_hours from pms_attendance  where isExceededBreak='1' and date_format(date,'%Y')='".mysql_real_escape_string($year)."' and date_format(date,'%m')='".mysql_real_escape_string($month)."' and ishoursapproved='0' and user_id='".mysql_real_escape_string($this->f("user_id"))."'";
					$db->query($sSQL);
					if($db->num_rows() > 0)
					{
						while($db->next_record())
						{
							$tmpbreakexceed = array("exceeddate"=>$db->f("exceeddate"), "totalbreak"=>$db->f("total_break_time"), "official_total_working_hours"=>$db->f("official_total_working_hours"));
							$arrBreakExceedInformation[$this->f("user_id")]["exceedinfo"][] = $tmpbreakexceed;
						}
					}
				}
			}
			
			return $arrBreakExceedInformation;
		}
		
		/* Fetch break exceeds approved for users who have more then 3 break exceeds. This report does not include manager, tl and support */
		/*function fnGetBreakExceedApprovedByYearAndMonth($year, $month)
		{
			$arrBreakExceedInformation = array();
			$db = new DB_Sql();
			
			$sSQL = "select a.user_id, count(a.id) as totalexceed, e.name from pms_attendance a LEFT JOIN pms_employee e ON e.id = a.user_id where a.isExceededBreak='1' and date_format(a.date,'%Y')='".mysql_real_escape_string($year)."' and date_format(a.date,'%m')='".mysql_real_escape_string($month)."' and a.ishoursapproved='1' and e.designation in (5,9,10,11,12,14,15,16) group by a.user_id order by e.name";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrBreakExceedInformation[$this->f("user_id")]["name"] = $this->f("name");
					
					$sSQL = "select date_format(date,'%Y-%m-%d') as exceeddate, time_format(total_break_time, '%H:%i') as total_break_time, time_format(break_exceed_time, '%H:%i') as break_exceed_time, time_format(official_total_working_hours, '%H:%i') as official_total_working_hours from pms_attendance  where isExceededBreak='1' and date_format(date,'%Y')='".mysql_real_escape_string($year)."' and date_format(date,'%m')='".mysql_real_escape_string($month)."' and ishoursapproved='1' and user_id='".mysql_real_escape_string($this->f("user_id"))."'";
					$db->query($sSQL);
					if($db->num_rows() > 0)
					{
						while($db->next_record())
						{
							$tmpbreakexceed = array("exceeddate"=>$db->f("exceeddate"), "totalbreak"=>$db->f("total_break_time"), "exceedtime"=>$db->f("break_exceed_time"), "official_total_working_hours"=>$db->f("official_total_working_hours"));
							$arrBreakExceedInformation[$this->f("user_id")]["exceedinfo"][] = $tmpbreakexceed;
						}
					}
				}
			}

			return $arrBreakExceedInformation;
		}*/
		
		function fnGetBreakExceedApprovedByYearAndMonth($year, $month)
		{
			$arrBreakExceedInformation = array();
			$db = new DB_Sql();
			
			$sSQL = "select a.user_id, date_format(a.date,'%d-%m-%Y') as exceeddate, time_format(a.total_break_time, '%H:%i') as total_break_time, time_format(a.break_exceed_time, '%H:%i') as break_exceed_time, time_format(a.official_total_working_hours, '%H:%i') as official_total_working_hours, e.name from pms_attendance a LEFT JOIN pms_employee e ON e.id = a.user_id where a.isExceededBreak='1' and date_format(a.date,'%Y')='".mysql_real_escape_string($year)."' and date_format(a.date,'%m')='".mysql_real_escape_string($month)."' and a.ishoursapproved='1' and e.designation in (5,9,10,11,12,14,15,16,30,31,32,33,34,35,36,37,38,39,40,41,42,43) order by e.name, a.date";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrBreakExceedInformation[] = $this->fetchRow();
					
					/*$arrBreakExceedInformation[$this->f("user_id")]["name"] = $this->f("name");
					
					$sSQL = "select date_format(date,'%Y-%m-%d') as exceeddate, time_format(total_break_time, '%H:%i') as total_break_time, time_format(break_exceed_time, '%H:%i') as break_exceed_time, time_format(official_total_working_hours, '%H:%i') as official_total_working_hours from pms_attendance  where isExceededBreak='1' and date_format(date,'%Y')='".mysql_real_escape_string($year)."' and date_format(date,'%m')='".mysql_real_escape_string($month)."' and ishoursapproved='1' and user_id='".mysql_real_escape_string($this->f("user_id"))."'";
					$db->query($sSQL);
					if($db->num_rows() > 0)
					{
						while($db->next_record())
						{
							$tmpbreakexceed = array("exceeddate"=>$db->f("exceeddate"), "totalbreak"=>$db->f("total_break_time"), "exceedtime"=>$db->f("break_exceed_time"), "official_total_working_hours"=>$db->f("official_total_working_hours"));
							$arrBreakExceedInformation[$this->f("user_id")]["exceedinfo"][] = $tmpbreakexceed;
						}
					}*/
				}
			}

			return $arrBreakExceedInformation;
		}
		
		/* Fetch late coming for users who have more then 3 late commings and late time considered for greated then 4 mins. This report does not include manager, tl and support */
		function fnGetLateComingsByYearAndMonth($year, $month)
		{
			$arrLateCommingInformation = array();
			$db = new DB_Sql();
			
			$sSQL = "select a.user_id, count(a.id) as totallate, e.name from pms_attendance a LEFT JOIN pms_employee e ON e.id = a.user_id where a.is_late='1' and late_time > '00:04:00' and date_format(a.date,'%Y')='".mysql_real_escape_string($year)."' and date_format(a.date,'%m')='".mysql_real_escape_string($month)."' and e.designation in (5,9,10,11,12,14,15,16,30,31,32,33,34,35,36,37,38,39,40,41,42,43) group by a.user_id having count(a.id) > 3";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrLateCommingInformation[$this->f("user_id")]["name"] = $this->f("name");
					
					$sSQL = "select date_format(date,'%Y-%m-%d') as latedate, time_format(late_time, '%H:%i') as cur_late_time from pms_attendance where is_late='1' and late_time > '00:04:00' and date_format(date,'%Y')='".mysql_real_escape_string($year)."' and date_format(date,'%m')='".mysql_real_escape_string($month)."' and user_id='".mysql_real_escape_string($this->f("user_id"))."'";
					$db->query($sSQL);
					if($db->num_rows() > 0)
					{
						while($db->next_record())
						{
							$tmplatecomming = array("latedate"=>$db->f("latedate"), "late_time"=>$db->f("cur_late_time"));
							$arrLateCommingInformation[$this->f("user_id")]["lateinfo"][] = $tmplatecomming;
						}
					}
				}
			}
			
			return $arrLateCommingInformation;
		}

		function fnGetLastLeaveBalance($UserId)
		{
			$currentLeaveBalance = $totalLeaves = $totalPendingLeaveCounts = $totalHalfdayLeaves = $totalPendingHalfdayLeaveCounts = $pendingLeaves = 0;
			$curDate = Date('Y-m-d');
			$firstDate = Date('Y-m')."-01";
			
			/* Get the current leave balance */
			$sSQL = "select leave_bal from pms_employee where id='".mysql_real_escape_string($UserId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
					$currentLeaveBalance = $this->f("leave_bal");
			}
			
			/* Fetch leaves already taken from the current month  */
			$sSQL = "select count(id) as total_leaves from pms_attendance where leave_id in (1,2) and date_format(date,'%Y-%m-%d') >= '".mysql_real_escape_string($firstDate)."' and user_id='".mysql_real_escape_string($UserId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
					$totalLeaves = $this->f("total_leaves");
			}
			
			/* Fetch all pending leaves that are already added but approval still pending from current date */
			$sSQL = "select date_format(start_date, '%Y-%m-%d') as start_date, date_format(end_date, '%Y-%m-%d') as end_date from pms_leave_form where date_format(start_date,'%Y-%m-%d') > '".mysql_real_escape_string($curDate)."' and employee_id='".mysql_real_escape_string($UserId)."' and (status_manager='0' or (deligateManagerId!='0' and manager_delegate_status='0' and status_manager='0')) and (status_manager='0' and (status_manager='0' and deligateManagerId!='0' and manager_delegate_status='0') and (status != 2 and (status =0 and deligateTeamLeaderId != 0 and delegate_status!=2)))";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$start_date = $this->f("start_date");
					
					while($start_date <= $this->f("end_date"))
					{
						$totalPendingLeaveCounts = $totalPendingLeaveCounts + 1;
						$start_date = date('Y-m-d', strtotime('+1 day', strtotime($start_date)));
					}
				}
			}
			
			
			/* Fetch halfday leaves already taken from the current date */
			$sSQL = "select count(id) as total_leaves from pms_attendance where leave_id in (4,5) and date_format(date,'%Y-%m-%d') >= '".mysql_real_escape_string($firstDate)."' and user_id='".mysql_real_escape_string($UserId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
					$totalHalfdayLeaves = $this->f("total_leaves") * 0.5;
			}
			
			/* Fetch half day leaves that are already added but approval still pending from current date */
			$sSQL = "select count(id) as total_pending_halfday_leave_count from pms_half_leave_form where date_format(start_date,'%Y-%m-%d') > '".mysql_real_escape_string($curDate)."' and employee_id='".mysql_real_escape_string($UserId)."' and (status_manager='0' or (deligateManagerId!='0' and manager_delegate_status='0' and status_manager='0')) and (status_manager='0' and (status_manager='0' and deligateManagerId!='0' and manager_delegate_status='0') and (status != 2 and (status =0 and deligateTeamLeaderId != 0 and delegate_status!=2)))";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
					$totalPendingHalfdayLeaveCounts = $this->f("total_pending_halfday_leave_count") * 0.5;
			}

			/*echo "<br/>currentLeaveBalance : ".$currentLeaveBalance;
			echo "<br/>totalLeaves : ".$totalLeaves;
			echo "<br/>totalHalfdayLeaves : ".$totalHalfdayLeaves;
			echo "<br/>totalPendingLeaveCounts : ".$totalPendingLeaveCounts;
			echo "<br/>totalPendingHalfdayLeaveCounts : ".$totalPendingHalfdayLeaveCounts;*/

			$pendingLeaves = $currentLeaveBalance - ($totalLeaves + $totalHalfdayLeaves + $totalPendingLeaveCounts + $totalPendingHalfdayLeaveCounts);
			
			/*echo "<br/>pendingLeaves : ".$pendingLeaves;*/
			
			return $pendingLeaves;
		}

		function fnCalculateHalfMonthlyReport($userid,$day,$month,$year,$emp_des)
		{
			//echo '<br>----------------inHalfMonthlycalculation-----------<br>';
			include_once("class.calculation.php");
			include_once("class.employee.php");
			$objCalculation = new calculation();
			$objEmployee = new employee();
			$EmployeeInfo = $objEmployee->fnGetEmployeeById($userid);
			//echo 'hello'; die;
			$actual_cur_month = date('m');
			$attendance_date =  $year.'-'.$month.'-'.$day;

			//echo '<br><br>actual_cur_month-------'.$actual_cur_month;
			//echo '<br><br>month-------'.$month;
			if($actual_cur_month == $month)
			{
				$day = date('d');
			}
			else
			{
				$temp_date = $year.'-'.$month.'-01';
				$DisplayDate = date('t', strtotime($temp_date));
				$day = date('t');
			}
			
			$checkRecordExistence = $objCalculation -> fnCheckHalfExistence($userid,$month,$year);
			if(isset($checkRecordExistence) &&  $checkRecordExistence != '')
			{
				//echo 'already exist'; die;
			}
			else
			{
				/* Total break exists */
				$break_exceed_days = $objCalculation->fnGetHalfTotalBreaks($userid,$day,$month,$year,$emp_des);

				/* Total late comings */
				$total_late_coming = $objCalculation->fnGetHalfTotalLateComings($userid,$day,$month,$year,$emp_des);
				
				/* Total presents */
				$in_total = $objCalculation->fnGetHalfTotalPresents($userid,$day,$month,$year,$emp_des);
				

				/* Total ppl */
				$total_ppl = $objCalculation->fnGetHalfTotalLeave($userid,$day,$month,$year,'ppl');

			
				/* Total uhl */
				$total_uhl = $objCalculation->fnGetHalfTotalLeave($userid,$day,$month,$year,'uhl');

				/* Total php */
				$total_phl = $objCalculation->fnGetHalfTotalLeave($userid,$day,$month,$year,'phl');

				/* Total wo */
				$wo = $objCalculation->fnGetHalfTotalLeave($userid,$day,$month,$year,'wo');

				/* Total ph */
				$total_ph = $objCalculation->fnGetHalfTotalLeave($userid,$day,$month,$year,'ph');

				/* Total ha */
				$ha = $objCalculation->fnGetHalfTotalLeave($userid,$day,$month,$year,'ha');

				/* Total hlwp */
				$hlwp = $objCalculation->fnGetHalfTotalLeave($userid,$day,$month,$year,'hlwp');

				/* Total abs */
				$abs = $objCalculation->fnGetHalfTotalLeave($userid,$day,$month,$year,'a');

				//$abs123 = $objCalculation->fnGetHalfTotalLeaveByIntimeOut($userid,$month,$year,'a');

				/* Total plwp */
				$plwp = $objCalculation->fnGetHalfTotalLeave($userid,$day,$month,$year,'plwp');
//$plwp = 4;
				/* Total ulwp */
				$ulwp = $objCalculation->fnGetHalfTotalLeave($userid,$day,$month,$year,'ulwp');
//$ulwp = 3;
				/* Total upl */
				$total_upl = $objCalculation->fnGetHalfTotalLeave($userid,$day,$month,$year,'upl');

				/* Total smplt */
				$smplt = $objCalculation->fnGetHalfTotalLeave($userid,$day,$month,$year,'smplt');

				/* Total movement details */
				/* Not use in half monthly because if anyone is on shift movement and that is unapproved it directly reflect as half day in attendance and reflect directly also as half day in calculation */
				$MovementDetails = $objCalculation->fnGetHalfTotalOfficialShiftMovementDays($userid,$day,$month,$year);
				//echo 'hello'; print_r($MovementDetails);
				

				
				/*echo '<br>in_total---'.$in_total;
				echo '<br>break_exceed_days---'.$break_exceed_days;
				echo '<br>total_late_coming---'.$total_late_coming;
				echo '<br>total_uhl---'.$total_uhl;
				echo '<br>total_phl---'.$total_phl;
				echo '<br>wo---'.$wo;
				echo '<br>ha---'.$ha;
				echo '<br>hlwp---'.$hlwp;
				echo '<br>abs---'.$abs;
				echo '<br>plwp---'.$plwp;
				echo '<br>ulwp---'.$ulwp;
				echo '<br>total_upl---'.$total_upl;
				echo '<br>smplt---'.$smplt;
				echo '<br>MovementDetails---'.$MovementDetails.'<br>';*/


				if(isset($MovementDetails))
				{
					$total_shift_mooment_days = $MovementDetails['total'];
					$total_shift_mooment_approved_days = $MovementDetails['approved'];
					$total_shift_mooment_unapproved = $MovementDetails['total'] - $MovementDetails['approved'];
					$total_shift_mooment_unapproved_deduction = $total_shift_mooment_unapproved * .5 ;
					$total_shift_mooment_approved_days = ($total_shift_mooment_approved_days * 1) + ($total_shift_mooment_unapproved * .5 );
					//$in_total = $in_total + ($total_shift_mooment_approved_days); 
				}
				else
				{
					$total_shift_mooment_approved_days = 0;
					$total_shift_mooment_unapproved_deduction = 0;
				}
				$weekOfDays = $objCalculation->fnGetHalfWeekOfDates($userid,$day,$month,$year);
				//print_r($weekOfDays); 

				if(count($weekOfDays) > 0 )
				{
					$non_week_of_Days = array();
					$absent_mark = 0;
					$count_ppl = 0;
					$count_upl = 0;
					$count_abs = 0;
					foreach($weekOfDays as $weekDays)
					{
						//print_r($weekDays['date']);
						$cur = $weekDays['date'];
						//$cur = '2013-01-06';
						$sendwitch_dates = array();
						$prev_date = date('Y-m-d', strtotime('-1 day', strtotime($cur)));
						$next_date = date('Y-m-d', strtotime('+1 day', strtotime($cur)));
						$temp = array();
						$temp1 = array();
						$sendwitch1 = array();
						$checkNextDay = $objCalculation->fnCheckNextDate($userid,$cur,$next_date,$temp);
						$count = 0;
						
						//print_r($checkNextDay);
						if($checkNextDay == 'noSendwitch')
						{
							//echo 'No Sendwitch';
						}
						else if(is_array($checkNextDay))
						{
								$checkPrevDay = $objCalculation->fnCheckPrevDate($userid,$prev_date,$temp1);
								//print_r($checkPrevDay);
								if($checkPrevDay == 'noSendwitch')
								{
									//echo 'No Sendwitch';
								}
								if(is_array($checkPrevDay))
								{
									$sendwitch1 = array_merge($checkPrevDay,$checkNextDay);
								}
						}
						//echo 'helllo1';print_r($sendwitch1);
						if(isset($sendwitch1) && count($sendwitch1) > 0)
						{
							//echo $weekDays.'  finds in sendwitch';
							$sendwitch1[] = $cur;
						}
						//echo 'hello';print_r($sendwitch1);
						if(in_array($cur,$sendwitch1))
						{
							$non_week_of_Days[] = $cur; 
						}
					}
					//echo $count;
				}
				//print_r($non_week_of_Days);
				if(isset($non_week_of_Days) && count($non_week_of_Days) > 0)
				{
					
					$count_absent_sendwitch = 0;
					foreach($non_week_of_Days as $nonweekdays)
					{
						$prev_date = date('Y-m-d', strtotime('-1 day', strtotime($nonweekdays)));
						$next_date = date('Y-m-d', strtotime('+1 day', strtotime($nonweekdays)));
						
						$checkPrevDateLeaveId = $objCalculation->fnCheckPreviousDateLeaveId($userid,$prev_date,'prev');
						//echo 	'<Br>@@@@checkPrevDateLeaveId===='.$checkPrevDateLeaveId.'<br />';
						if($checkPrevDateLeaveId == 1)
						{
							$checkNextDateLeaveId = $objCalculation->fnCheckPreviousDateLeaveId($userid,$next_date,'next');
							//echo 	'<Br>@checkNextDateLeaveId===='.$checkNextDateLeaveId.'<br/>';
							if($checkNextDateLeaveId == 1)
							{
								$count_ppl += 1;
							}
							else
							{
								$checkLeaveForm = $objCalculation->fnCheckLeaveExistence($userid,$nonweekdays);
								if($checkLeaveForm == 'approved') 
								{
									$count_ppl += 1;
								}
								else if($checkLeaveForm == 'unapproved') 
								{
									$count_upl += 1;
								}
								else
								{
									$count_abs += 1;
								}
							}
						}
						else if($checkPrevDateLeaveId == 2)
						{
							$checkNextDateLeaveId = $objCalculation->fnCheckPreviousDateLeaveId($userid,$next_date,'next');
							//echo 	'<Br>@checkNextDateLeaveId===='.$checkNextDateLeaveId.'<br/>';
							if($checkNextDateLeaveId == 1 || $checkNextDateLeaveId == 2)
							{
								$count_upl += 1;
							}
							else
							{
								$checkLeaveForm = $objCalculation->fnCheckLeaveExistence($userid,$nonweekdays);
								if($checkLeaveForm == 'approved') 
								{
									$count_ppl += 1;
								}
								else if($checkLeaveForm == 'unapproved') 
								{
									$count_upl += 1;
								}
								else
								{
									$count_abs += 1;
								}
							}
						}
						else if($checkPrevDateLeaveId == '3')
						{
								//echo 'hello';
							$checkNextDateLeaveId = $objCalculation->fnCheckPreviousDateLeaveId($userid,$next_date,'next');
							
								$checkLeaveForm = $objCalculation->fnCheckLeaveExistence($userid,$nonweekdays);
								if($checkLeaveForm == 'approved') 
								{
									$count_ppl += 1;
								}
								else if($checkLeaveForm == 'unapproved') 
								{
									$count_abs += 1;
								}
								else
								{
									$count_abs += 1;
								}
							//echo $count_abs;
						}
						else if($checkPrevDateLeaveId != '')
						{
							$checkNextDateLeaveId = $objCalculation->fnCheckPreviousDateLeaveId($userid,$next_date,'next');
							if($checkNextDateLeaveId != '')
							{
								$checkLeaveForm = $objCalculation->fnCheckLeaveExistence($userid,$nonweekdays);
								if($checkLeaveForm == 'approved') 
								{
									$count_ppl += 1;
								}
								else if($checkLeaveForm == 'unapproved')
								{
									$count_abs += 1;
								}
								else
								{
									$count_abs += 1;
								}
							}
						}
					}
				}
				
				$not_WeekOfDays = count($non_week_of_Days);
				//echo 'in_total'.$in_total;
				//die;
				$final_wo = $wo - $not_WeekOfDays;
				$total_avail_leave = $objCalculation->fnGetTotalOpeningLeaves($userid);
				
				$present_week_ph_total =  $in_total + $final_wo + $total_ph;
				
				$deducted_late_comings = '';
				$deducted_late_comings_days = '';
				//$break_exceed_days = '';
				$deducted_break_exceed_days = '';
				if($EmployeeInfo['designation'] == '6' || $EmployeeInfo['designation'] == '18' || $EmployeeInfo['designation'] == '19' || $EmployeeInfo['designation'] == '7' || $EmployeeInfo['designation'] == '8' || $EmployeeInfo['designation'] == '13' || $EmployeeInfo['designation'] == '17' || $EmployeeInfo['designation'] == '20' || $EmployeeInfo['designation'] == '21' || $EmployeeInfo['designation'] == '22' || $EmployeeInfo['designation'] == '23' || $EmployeeInfo['designation'] == '24' || $EmployeeInfo['designation'] == '25' || $EmployeeInfo['designation'] == '26' || $EmployeeInfo['designation'] == '27' || $EmployeeInfo['designation'] == '28' || $EmployeeInfo['designation'] == '44')
				{
					$total_late_coming = '0';
					$break_exceed_days = '0';
				}
				if($total_late_coming > 3)
				{	
					$deducted_late_comings = $total_late_coming - 3;
					$total_late_coming = $total_late_coming - 3;
					if($deducted_late_comings > 2)
					{
						$deducted_late_comings = $deducted_late_comings - 2;
						$deducted_late_comings_days = $deducted_late_comings_days + 1;
						
						if($deducted_late_comings > 0)
						{
							$deducted_late_comings_days = $deducted_late_comings_days + $deducted_late_comings ;
						}
					}
					else
					{
						$deducted_late_comings_days = $deducted_late_comings_days + ($deducted_late_comings * .5);
					}
				}
				else
				{
					$deducted_late_comings_days = 0 ;
					$total_late_coming = 0 ;
				}
				
				if($break_exceed_days > 3)
				{
					$temp_break_exceed_days = $break_exceed_days - 3;
					if($temp_break_exceed_days > 0 )
					{
						$deducted_break_exceed_days = $deducted_break_exceed_days + ($temp_break_exceed_days * .25); 
					}
				}
				else
				{
					$temp_break_exceed_days = 0;
					$deducted_break_exceed_days = 0;
				}
				$total_deducted_late_and_break = ($deducted_late_comings_days + $deducted_break_exceed_days); 	
				
				
				
				if($total_phl > 0)
				{
					$deducted_phl = $total_phl * .5 ;
				}
				else
				{
					$deducted_phl = 0;
				}
				if($total_uhl > 0)
				{
					$deducted_uhl = $total_uhl * .5 ;
				}
				else
				{
					$deducted_uhl = 0;
				}
				
				$total_planned_leave_taken = $total_ppl + $deducted_phl + $count_ppl;
				//echo '<br>total_upl------'.$total_upl.'<br>deducted_uhl--------'.$deducted_uhl;
				$total_unplanned_leave_taken = $total_upl + $deducted_uhl + $count_upl;

				if(($plwp+$ulwp+($hlwp * .5)) >= 3)
				{
					$RemainingPlwpOrUlwp = 0;
				}
				else
				{
					$RemainingPlwpOrUlwp = 3 - ($plwp+$ulwp+($hlwp * .5));
				}
				//$total_planned_leave_taken = 5;
				
				//$total_unplanned_leave_taken = 5;

				//echo '<br><br><br>total_planned_leave_taken'.$total_planned_leave_taken;
				//echo '<br>total_unplanned_leave_taken'.$total_unplanned_leave_taken;
				
				
				if($total_planned_leave_taken > $total_avail_leave)
				{
					$plwp_eligible_leaves_total = $total_planned_leave_taken - $total_avail_leave;
					$remaining_ppl_leave_for_hlwp = 0;
					
					$ppl_given_for_plwp = $total_avail_leave;
					if($plwp_eligible_leaves_total > $RemainingPlwpOrUlwp)
					{
						$total_plwp_given = $RemainingPlwpOrUlwp;
						$elegible_absent_after_plwp = $plwp_eligible_leaves_total - $RemainingPlwpOrUlwp;
						$remaining_plwp_leave_for_hlwp = 0;
						
					}
					else
					{
						$elegible_absent_after_plwp = 0;
						$total_plwp_given = $plwp_eligible_leaves_total;
						$remaining_plwp_leave_for_hlwp = $RemainingPlwpOrUlwp - $total_plwp_given;
					}
				}
				else
				{
					$plwp_eligible_leaves_total = 0;
					$elegible_absent_after_plwp = 0;
					$total_plwp_given = 0;
					$remaining_plwp_leave_for_hlwp = $RemainingPlwpOrUlwp;
					$remaining_ppl_leave_for_hlwp = $total_avail_leave - $total_planned_leave_taken;
					$ppl_given_for_plwp = $total_avail_leave - $remaining_ppl_leave_for_hlwp;
				}
				
				$total_hlwp = $hlwp * .5 ;
				//$total_hlwp = 1;
				if($total_hlwp > 0)
				{
					if($elegible_absent_after_plwp > 0)
					{
						$elegible_absent_after_hlwp = $total_hlwp;
						$ppl_given_for_hlwp = 0;
						$total_hlwp_given = 0;
					}
					else
					{	
					
						if($total_hlwp > $remaining_ppl_leave_for_hlwp)
						{
							$plwp_eligible_leaves_total_in_hlwp =  $total_hlwp - $remaining_ppl_leave_for_hlwp;
							$ppl_given_for_hlwp = $remaining_ppl_leave_for_hlwp;
							$remaining_ppl_leave_for_ulwp = 0;
							
							if($plwp_eligible_leaves_total_in_hlwp > $remaining_plwp_leave_for_hlwp)
							{
								$elegible_absent_after_hlwp = $plwp_eligible_leaves_total_in_hlwp - $remaining_plwp_leave_for_hlwp ;
								
								$remaining_plwp_leave_for_ulwp = 0;
								$total_hlwp_given = $remaining_plwp_leave_for_hlwp;
								
							}
							else
							{
								$remaining_plwp_leave_for_ulwp = $remaining_plwp_leave_for_hlwp - $plwp_eligible_leaves_total_in_hlwp;
								$elegible_absent_after_hlwp =0;
								$total_hlwp_given = $plwp_eligible_leaves_total_in_hlwp;
							}
							
							
						}
						else
						{
							$remaining_ppl_leave_for_ulwp = $remaining_ppl_leave_for_hlwp - $total_hlwp;
							$plwp_eligible_leaves_total_in_hlwp = 0;
							$total_hlwp_given = 0;
							$remaining_plwp_leave_for_ulwp = $RemainingPlwpOrUlwp;
							$ppl_given_for_hlwp = $total_hlwp;
							$elegible_absent_after_hlwp = 0;
						}
					}
				}
				else
				{
					$remaining_ppl_leave_for_ulwp = $remaining_ppl_leave_for_hlwp;
					$remaining_plwp_leave_for_ulwp = $remaining_plwp_leave_for_hlwp;
					$elegible_absent_after_hlwp = 0;
					$total_hlwp_given = 0;
					$ppl_given_for_hlwp = 0;
				}
				
				$elegible_absent_after_plwp_hlwp = $elegible_absent_after_hlwp + $elegible_absent_after_plwp;
				if($total_unplanned_leave_taken > 0)
				{
					if($elegible_absent_after_plwp_hlwp > 0)
					{
						$elegible_absent_after_ulwp = $total_unplanned_leave_taken;
						$total_ulwp_given = 0;
						$ppl_given_for_ulwp = 0;
					}
					else
					{
						if($total_unplanned_leave_taken > $remaining_ppl_leave_for_ulwp)
						{
							$plwp_eligible_leaves_total_in_ulwp =  $total_unplanned_leave_taken - $remaining_ppl_leave_for_ulwp;
							$ppl_given_for_ulwp = $remaining_ppl_leave_for_ulwp;
							$remaining_ppl_leave_after_ulwp = 0;
							
							if($plwp_eligible_leaves_total_in_ulwp > $remaining_plwp_leave_for_ulwp)
							{
								$elegible_absent_after_ulwp = $plwp_eligible_leaves_total_in_ulwp - $remaining_plwp_leave_for_ulwp ;
								
								$remaining_plwp_leave_after_ulwp = 0;
								$total_ulwp_given = $remaining_plwp_leave_for_ulwp;
								$remaining_plwp_leave_after_ulwp = 0;
							}
							else
							{
								$remaining_plwp_leave_after_ulwp = $remaining_plwp_leave_for_ulwp - $plwp_eligible_leaves_total_in_ulwp;
								$total_ulwp_given = $remaining_plwp_leave_for_ulwp - $remaining_plwp_leave_after_ulwp;
								$elegible_absent_after_ulwp = 0;
							}
						}
						else
						{
							$ulwp_eligible_leaves_total = 0;
							$elegible_absent_after_ulwp = 0;
							$total_ulwp_given = 0;
							$remaining_plwp_leave_after_ulwp = $RemainingPlwpOrUlwp;
							$remaining_ppl_leave_after_ulwp = $remaining_ppl_leave_for_ulwp - $total_unplanned_leave_taken;
							$ppl_given_for_ulwp = $remaining_ppl_leave_for_ulwp - $remaining_ppl_leave_after_ulwp;
						}
					}
				}
				else
				{
					$remaining_ppl_leave_for_ulwp = $remaining_ppl_leave_for_hlwp;
					$remaining_plwp_leave_for_ulwp = $remaining_plwp_leave_for_hlwp;
					$ppl_given_for_ulwp = 0;
					$remaining_plwp_leave_after_ulwp = 0;
					$elegible_absent_after_ulwp = 0;
					$total_ulwp_given = 0;
				}
				
				if($ha > 0)
				{
					$deducted_abs = $ha * .5;
				}
				else
				{
					$deducted_abs = 0;
				}
				
				$total_abs_and_deducted_abs = $deducted_abs + $abs;
				
				$total_leave_after_plwp_ulwp_hlwp = $elegible_absent_after_plwp + $elegible_absent_after_hlwp + $elegible_absent_after_ulwp;
				//echo '<br><br>total_leave_after_plwp_ulwp_hlwp---'.$total_leave_after_plwp_ulwp_hlwp;
				$total_absence = $total_abs_and_deducted_abs + $total_leave_after_plwp_ulwp_hlwp + $count_abs;
				//echo '<br><br>total_absence---'.$total_absence;
				$total_absence_old = $total_absence;
				//echo '<br><br>total_absence_old---'.$total_absence_old;
				
				
				if($total_absence > 3)
				{
					$deducted_days = (3 * 1.25);
					$total_absence = $total_absence -3;
					if($total_absence > 0)
					{
						if($total_absence > 2)
						{
							$deducted_days = ($deducted_days + (2 * 1.50));
							$total_absence = $total_absence - 2;	
								if($total_absence > 0)
								{
										$deducted_days = ($deducted_days + ($total_absence * 2));
								}
						}
						else
						{						
							$deducted_days = ($deducted_days + ($total_absence * 1.50));
						}
					}
				}
				else
				{
					$deducted_days = ($total_absence + ($total_absence * .25));
				}
				
				$deducted_days = $deducted_days - $total_absence_old;

				$total_plwp_given = $total_plwp_given + $plwp;
				$total_hlwp_given = $total_hlwp_given + ($hlwp*.5);
				$total_ulwp_given = $total_ulwp_given + $ulwp;
		/* when we run this file after shift movement working with our system uncomment this line */
				//$final_deducted_day = $deducted_days + $total_deducted_late_and_break + $total_shift_mooment_unapproved_deduction;

		/* for now we not consider shift movement apporval done or not */
				$final_deducted_day = $deducted_days + $total_deducted_late_and_break;
			
				/*echo '<pre>';
			
				echo '<br>userid--------'.$userid;
				echo '<br>total_avail_leave--------'.$total_avail_leave;
				echo '<br>total_abs_and_deducted_abs--------'.$total_abs_and_deducted_abs;
				echo '<br>total_planned_leave_taken--------'.$total_planned_leave_taken;
				echo '<br>total_hlwp--------'.$total_hlwp;
				echo '<br>total_unplanned_leave_taken--------'.$total_unplanned_leave_taken;
				
				
				echo '<br><br><br>ppl_given_for_plwp--------'.$ppl_given_for_plwp;
				echo '<br>ppl_given_for_hlwp--------'.$ppl_given_for_hlwp;
				echo '<br>ppl_given_for_ulwp--------'.$ppl_given_for_ulwp;
				echo '<br><br>total_plwp_given--------'.$total_plwp_given;
				echo '<br>total_hlwp_given--------'.$total_hlwp_given;
				echo '<br>total_ulwp_given--------'.$total_ulwp_given;
				echo '<br><br>elegible_absent_after_plwp--------'.$elegible_absent_after_plwp;
				echo '<br>elegible_absent_after_hlwp--------'.$elegible_absent_after_hlwp;
				echo '<br>elegible_absent_after_ulwp--------'.$elegible_absent_after_ulwp;
				
				echo '<br><br><br>total_abs_and_deducted_abs--------'.$total_abs_and_deducted_abs;
				echo '<br>total_absence--------'.$total_absence;
				echo '<br>deducted_days--------'.$deducted_days;
				echo '<br>deducted_late_comings_days--------'.$deducted_late_comings_days;
				echo '<br>deducted_break_exceed_days--------'.$deducted_break_exceed_days;
				echo '<br>total_deducted_late_and_break--------'.$total_deducted_late_and_break;
				echo '<br>total_shift_mooment_unapproved_deduction--------'.$total_shift_mooment_unapproved_deduction;
				echo '<br>final_deducted_day--------'.$final_deducted_day;
				echo '<br>not_WeekOfDays--------'.$not_WeekOfDays;
				echo '<br>count_ppl'.$count_ppl;
				echo '<br>count_upl'.$count_upl;
				echo '<br>count_abs'.$count_abs;	*/
			
				$total_ppl_consumed = $ppl_given_for_plwp + $ppl_given_for_hlwp + $ppl_given_for_ulwp;
				//$total_ppl_consumed = $ppl_given_for_plwp + $ppl_given_for_ulwp;
				
				//echo '<br>in_total--------'.$in_total;
				//echo '<br>finalwo--------'.$final_wo;
				//echo '<br>total_ph--------'.$total_ph;
				//echo '<br>in_total---'.$in_total.'finalwo----'.$final_wo.'total_ph-----'.$total_ph.'total_uhl------'.$total_uhl.'total_phl-------'.$total_phl;
				
				$total_present = $in_total + $final_wo + $total_ph + ($total_uhl * .5) + ($total_phl * .5) ;
				//echo '<br>total_present-----'.$total_present.'-----final_deducted_day------'.$final_deducted_day.'total_ppl_consumed'.$total_ppl_consumed.'<br>';
				$payDays = ($total_present - $final_deducted_day) + $total_ppl_consumed;
				//echo $payDays; 
				if($payDays >= '15')
				{
					//echo 'hello';
					$leaves_earn = 1;
					$current_date = date("Y-m-d H:i:s");
					$ClosingLeavesBalance = $total_avail_leave  + $leaves_earn;
					$reason = array("p"=>$in_total,"total_plt"=>$total_late_coming,"ppl"=>$total_ppl,"uhl"=>$total_uhl,"total_phl"=>$total_phl,"wo"=>$final_wo,"ph"=>$total_ph,"ha"=>$ha,"hlwp"=>$hlwp,"a"=>$abs,"plwp"=>$plwp,"ulwp"=>$ulwp,"upl"=>$total_upl,"total_present"=>$total_present,"deducted_days"=>$final_deducted_day,"payDays"=>$payDays,"OpeningLeavesBalance"=>$total_avail_leave,"leave_earn"=>$leaves_earn,"pl_consume"=>$total_ppl_consumed,"ClosingLeavesBalance"=>$ClosingLeavesBalance);
					
					
					$summary = array("emp_id"=>$userid,"added_no_of_leaves"=>$leaves_earn,"date"=>$current_date,"reason"=>json_encode($reason),"opening_leave"=>$total_avail_leave,"closing_leave"=>$ClosingLeavesBalance,"leave_added_date"=>$attendance_date,"ishalfmonthly"=>'1');
					
					$insertSummary = $objCalculation -> fnInsertHalfSummary($summary,$month,$year);

					//echo '<br>closingLeaveBalance-------'.$ClosingLeavesBalance;
					$insertRemainingLeaves = $objCalculation -> fnUpdateRemainingLeave($userid,$ClosingLeavesBalance);

					/* save the record in summary log table always*/
					$insertSummary1 = $objCalculation -> fnInsertHalfSummaryLog($summary,$month,$year);

					
				}
			}
		}
		
		function fnCalculateMonthlyReport($userid,$day,$month,$year,$emp_des)
		{
			//echo '<br><br><br><br>----------------inMonthlycalculation-----------<br><br><br><br>';
			
			include_once("class.calculation.php");
			include_once("class.employee.php");
			$objCalculation = new calculation();
			$objEmployee = new employee();
			//echo 'hello'; die;
			$actual_cur_month = date('Y-m');
			$actual_cur_month_last_date = date('t');
			//$prev_month = date("Y-m",strtotime("-1 month"));
			$prev_month = date('Y-m', strtotime('-1 month', strtotime(date('Y-m-01'))));
			$prev_month_last_date = date("t", strtotime("-1 month") ) ;
			$attendance_date =  $year.'-'.$month.'-'.$day;
			$attendance_month = $year.'-'.$month;
			//echo '<br>actual_cur_month-----'.$actual_cur_month.'-----attendance_month----'.$attendance_month.'------month-----'.date('Y-m-d').'----'.date('Y-m-11').'<br>';
			if(date('Y-m-d') > date('Y-m-08') && ($attendance_month < $actual_cur_month))
			{
				return false;
			}
			else
			{
				//echo 'hello2'; die;
				//echo $attendance_month.' == '.$prev_month; echo '<br>'.$attendance_month.' == '.$actual_cur_month; echo '<br>'.$day.' == '.$actual_cur_month_last_date;

				$EmployeeInfo = $objEmployee->fnGetEmployeeById($userid);

				//print_r($EmployeeInfo);
				
				if( (($attendance_month == $actual_cur_month) && ($day == $actual_cur_month_last_date)) ||  ($attendance_month == $prev_month))
				{
					/* Total break exists */
					$break_exceed_days = $objCalculation->fnGetTotalBreaks($userid,$month,$year,$EmployeeInfo['designation']);

					/* Total late comings */
					$total_late_coming = $objCalculation->fnGetTotalLateComings($userid,$month,$year,$EmployeeInfo['designation']);

					/* Total presents */
					$in_total = $objCalculation->fnGetTotalPresents($userid,$month,$year,$EmployeeInfo['designation']);

					/* Total ppl */
					$total_ppl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ppl');

					/* Total uhl */
					$total_uhl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'uhl');

					/* Total php */
					$total_phl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'phl');

					/* Total wo */
					$wo = $objCalculation->fnGetTotalLeave($userid,$month,$year,'wo');

					/* Total ph */
					$total_ph = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ph');

					/* Total ha */
					$ha = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ha');

					/* Total hlwp */
					$hlwp = $objCalculation->fnGetTotalLeave($userid,$month,$year,'hlwp');

					/* Total abs */
					$abs = $objCalculation->fnGetTotalLeave($userid,$month,$year,'a');

					//$abs123 = $objCalculation->fnGetHalfTotalLeaveByIntimeOut($userid,$month,$year,'a');

					/* Total plwp */
					$plwp = $objCalculation->fnGetTotalLeave($userid,$month,$year,'plwp');

					/* Total ulwp */
					$ulwp = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ulwp');

					/* Total upl */
					$total_upl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'upl');

					/* Total smplt */
					$smplt = $objCalculation->fnGetTotalLeave($userid,$month,$year,'smplt');

					/* Total half day that not marked*/
					$checkHalfDays = $objCalculation->fnGetTotalHalfDays1($userid,$month,$year,$EmployeeInfo['designation']);

					

					/* Total movement details */
					$MovementDetails = $objCalculation->fnGetTotalOfficialShiftMovementDays($userid,$month,$year);

					/* Get half monthly leaves earned */
					$HalfMonthlyLeavesEarned = $objCalculation->fnHalfMonthlyLeaveEarned($userid,$month,$year);

					/*echo '<br>in_total---'.$in_total;
					echo '<br>break_exceed_days---'.$break_exceed_days;
					echo '<br>total_late_coming---'.$total_late_coming;
					echo '<br>total_uhl---'.$total_uhl;
					echo '<br>total_phl---'.$total_phl;
					echo '<br>wo---'.$wo;
					echo '<br>ha---'.$ha;
					echo '<br>hlwp---'.$hlwp;
					echo '<br>abs---'.$abs;
					echo '<br>plwp---'.$plwp;
					echo '<br>ulwp---'.$ulwp;
					echo '<br>total_ppl---'.$total_ppl;
					echo '<br>total_upl---'.$total_upl;
					echo '<br>smplt---'.$smplt;
					echo '<br>MovementDetails---'.$MovementDetails.'<br>';*/

					if(isset($MovementDetails))
					{
						$total_shift_mooment_days = $MovementDetails['total'];
						$total_shift_mooment_approved_days = $MovementDetails['approved'];
						$total_shift_mooment_unapproved = $MovementDetails['total'] - $MovementDetails['approved'];
						$total_shift_mooment_unapproved_deduction = $total_shift_mooment_unapproved * .5 ;
						$total_shift_mooment_approved_days = ($total_shift_mooment_approved_days * 1) + ($total_shift_mooment_unapproved * .5 );
						//$in_total = $in_total + ($total_shift_mooment_approved_days); 
					}
					else
					{
						$total_shift_mooment_approved_days = 0;
						$total_shift_mooment_unapproved_deduction = 0;
					}

					$ha = $total_shift_mooment_unapproved + $ha + $checkHalfDays;
					
					$weekOfDays = $objCalculation->fnGetWeekOfDates($userid,$month,$year);
					//print_r($weekOfDays); 
						
					if(count($weekOfDays) > 0 )
					{
						$non_week_of_Days = array();
						$absent_mark = 0;
						$count_ppl = 0;
						$count_upl = 0;
						$count_abs = 0;
						foreach($weekOfDays as $weekDays)
						{
							//print_r($weekDays['date']);
							$cur = $weekDays['date'];
							//$cur = '2013-01-06';
							$sendwitch_dates = array();
							$prev_date = date('Y-m-d', strtotime('-1 day', strtotime($cur)));
							$next_date = date('Y-m-d', strtotime('+1 day', strtotime($cur)));
							$temp = array();
							$temp1 = array();
							$sendwitch1 = array();
							$checkNextDay = $objCalculation->fnCheckNextDate($userid,$cur,$next_date,$temp);
							$count = 0;
							
							//print_r($checkNextDay);
							if($checkNextDay == 'noSendwitch')
							{
								//echo 'No Sendwitch';
							}
							else if(is_array($checkNextDay))
							{
									$checkPrevDay = $objCalculation->fnCheckPrevDate($userid,$prev_date,$temp1);
									//print_r($checkPrevDay);
									if($checkPrevDay == 'noSendwitch')
									{
										//echo 'No Sendwitch';
									}
									if(is_array($checkPrevDay))
									{
										$sendwitch1 = array_merge($checkPrevDay,$checkNextDay);
									}
							}
							//echo 'helllo1';print_r($sendwitch1);
							if(isset($sendwitch1) && count($sendwitch1) > 0)
							{
								//echo $weekDays.'  finds in sendwitch';
								$sendwitch1[] = $cur;
							}
							//echo 'hello';print_r($sendwitch1);
							if(in_array($cur,$sendwitch1))
							{
								$non_week_of_Days[] = $cur; 
							}
						}
						//echo $count;
					}
					//print_r($non_week_of_Days);
					if(isset($non_week_of_Days) && count($non_week_of_Days) > 0)
					{
						
						$count_absent_sendwitch = 0;
						foreach($non_week_of_Days as $nonweekdays)
						{
							$prev_date = date('Y-m-d', strtotime('-1 day', strtotime($nonweekdays)));
							$next_date = date('Y-m-d', strtotime('+1 day', strtotime($nonweekdays)));
							
							$checkPrevDateLeaveId = $objCalculation->fnCheckPreviousDateLeaveId($userid,$prev_date,'prev');
							//echo 	'<Br>@@@@checkPrevDateLeaveId===='.$checkPrevDateLeaveId.'<br />';
							if($checkPrevDateLeaveId == 1)
							{
								$checkNextDateLeaveId = $objCalculation->fnCheckPreviousDateLeaveId($userid,$next_date,'next');
								//echo 	'<Br>@checkNextDateLeaveId===='.$checkNextDateLeaveId.'<br/>';
								if($checkNextDateLeaveId == 1)
								{
									$count_ppl += 1;
								}
								else
								{
									$checkLeaveForm = $objCalculation->fnCheckLeaveExistence($userid,$nonweekdays);
									if($checkLeaveForm == 'approved') 
									{
										$count_ppl += 1;
									}
									else if($checkLeaveForm == 'unapproved') 
									{
										$count_upl += 1;
									}
									else
									{
										$count_abs += 1;
									}
								}
							}
							else if($checkPrevDateLeaveId == 2)
							{
								$checkNextDateLeaveId = $objCalculation->fnCheckPreviousDateLeaveId($userid,$next_date,'next');
								//echo 	'<Br>@checkNextDateLeaveId===='.$checkNextDateLeaveId.'<br/>';
								if($checkNextDateLeaveId == 1 || $checkNextDateLeaveId == 2)
								{
									$count_upl += 1;
								}
								else
								{
									$checkLeaveForm = $objCalculation->fnCheckLeaveExistence($userid,$nonweekdays);
									if($checkLeaveForm == 'approved') 
									{
										$count_ppl += 1;
									}
									else if($checkLeaveForm == 'unapproved') 
									{
										$count_upl += 1;
									}
									else
									{
										$count_abs += 1;
									}
								}
							}
							else if($checkPrevDateLeaveId == '3')
							{
									//echo 'hello';
								$checkNextDateLeaveId = $objCalculation->fnCheckPreviousDateLeaveId($userid,$next_date,'next');
								
									$checkLeaveForm = $objCalculation->fnCheckLeaveExistence($userid,$nonweekdays);
									if($checkLeaveForm == 'approved') 
									{
										$count_ppl += 1;
									}
									else if($checkLeaveForm == 'unapproved') 
									{
										$count_abs += 1;
									}
									else
									{
										$count_abs += 1;
									}
								//echo $count_abs;
							}
							else if($checkPrevDateLeaveId != '')
							{
								$checkNextDateLeaveId = $objCalculation->fnCheckPreviousDateLeaveId($userid,$next_date,'next');
								if($checkNextDateLeaveId != '')
								{
									$checkLeaveForm = $objCalculation->fnCheckLeaveExistence($userid,$nonweekdays);
									if($checkLeaveForm == 'approved') 
									{
										$count_ppl += 1;
									}
									else if($checkLeaveForm == 'unapproved')
									{
										$count_abs += 1;
									}
									else
									{
										$count_abs += 1;
									}
								}
							}
						}
					}
					
					$not_WeekOfDays = count($non_week_of_Days);
					//echo 'in_total'.$in_total;
					//die;
					$final_wo = $wo - $not_WeekOfDays;
					/* Get Total opening leaves */
					$total_avail_leave = $objCalculation->fnGetTotalOpeningLeaves($userid);
					//echo '<br>total_avail_leave------'.$total_avail_leave;
					if($HalfMonthlyLeavesEarned == '1')
					{
						$total_avail_leave = $total_avail_leave + $HalfMonthlyLeavesEarned;
					}
					//echo '<br>total_avail_leave------'.$total_avail_leave;
					$present_week_ph_total =  $in_total + $final_wo + $total_ph;
					
					$deducted_late_comings = '';
					$deducted_late_comings_days = '';
					//$break_exceed_days = '';
					$deducted_break_exceed_days = '';
					if($EmployeeInfo['designation'] == '6' || $EmployeeInfo['designation'] == '18' || $EmployeeInfo['designation'] == '19' || $EmployeeInfo['designation'] == '7' || $EmployeeInfo['designation'] == '8' || $EmployeeInfo['designation'] == '13' || $EmployeeInfo['designation'] == '17' || $EmployeeInfo['designation'] == '20' || $EmployeeInfo['designation'] == '21' || $EmployeeInfo['designation'] == '22' || $EmployeeInfo['designation'] == '23' || $EmployeeInfo['designation'] == '24' || $EmployeeInfo['designation'] == '25' || $EmployeeInfo['designation'] == '26' || $EmployeeInfo['designation'] == '27' || $EmployeeInfo['designation'] == '28' || $EmployeeInfo['designation'] == '44')
					{
						$total_late_coming = '0';
						$break_exceed_days = '0';
					}
					//echo '<br>total_late_coming--------'.$total_late_coming;
					if($total_late_coming > 3)
					{	
						$deducted_late_comings = $total_late_coming - 3;
						$total_late_coming = $total_late_coming - 3;
						if($deducted_late_comings > 2)
						{
							$deducted_late_comings = $deducted_late_comings - 2;
							$deducted_late_comings_days = $deducted_late_comings_days + 1;
							
							if($deducted_late_comings > 0)
							{
								$deducted_late_comings_days = $deducted_late_comings_days + $deducted_late_comings ;
							}
						}
						else
						{
							$deducted_late_comings_days = $deducted_late_comings_days + ($deducted_late_comings * .5);
						}
					}
					else
					{
						$deducted_late_comings_days = 0 ;
						$total_late_coming = 0 ;
					}
					//echo '<br>---deducted_late_comings_days-----------'.$deducted_late_comings_days;
					if($break_exceed_days > 3)
					{
						$break_exceed_days = $break_exceed_days - 3;
						if($break_exceed_days > 0 )
						{
							$deducted_break_exceed_days = $deducted_break_exceed_days + ($break_exceed_days * .25); 
						}
					}
					else
					{
						$break_exceed_days = 0;
						$deducted_break_exceed_days = 0;
					}
					$total_deducted_late_and_break = ($deducted_late_comings_days + $deducted_break_exceed_days); 	
					
					
					
					if($total_phl > 0)
					{
						$deducted_phl = $total_phl * .5 ;
					}
					else
					{
						$deducted_phl = 0;
					}
					if($total_uhl > 0)
					{
						$deducted_uhl = $total_uhl * .5 ;
					}
					else
					{
						$deducted_uhl = 0;
					}
					
					$total_planned_leave_taken = $total_ppl + $deducted_phl + $count_ppl;
					$total_planned_leave_taken_with_sandwitch = $total_ppl + $count_ppl;
					//echo '<br>total_upl------'.$total_upl.'<br>deducted_uhl--------'.$deducted_uhl;
					$total_unplanned_leave_taken = $total_upl + $deducted_uhl + $count_upl;
					$total_unplanned_leave_taken_with_sandwitch = $total_upl + $count_upl;

					if(($plwp+$ulwp+($hlwp * .5)) >= 3)
					{
						$RemainingPlwpOrUlwp = 0;
					}
					else
					{
						$RemainingPlwpOrUlwp = 3 - ($plwp+$ulwp+($hlwp * .5));
					}
					//echo '<br><br><br>total_planned_leave_taken------'.$total_planned_leave_taken.'<br>total_unplanned_leave_taken--------'.$total_unplanned_leave_taken;
					//$total_planned_leave_taken = 5;
					//$total_hlwp = 1;
					//$total_unplanned_leave_taken = 5;
					
					if($total_planned_leave_taken > $total_avail_leave)
					{
						$plwp_eligible_leaves_total = $total_planned_leave_taken - $total_avail_leave;
						$remaining_ppl_leave_for_hlwp = 0;
						
						$ppl_given_for_plwp = $total_avail_leave;
						if($plwp_eligible_leaves_total > $RemainingPlwpOrUlwp)
						{
							$total_plwp_given = $RemainingPlwpOrUlwp;
							$elegible_absent_after_plwp = $plwp_eligible_leaves_total - $RemainingPlwpOrUlwp;
							$remaining_plwp_leave_for_hlwp = 0;
							
						}
						else
						{
							$elegible_absent_after_plwp = 0;
							$total_plwp_given = $plwp_eligible_leaves_total;
							$remaining_plwp_leave_for_hlwp = $RemainingPlwpOrUlwp - $total_plwp_given;
						}
					}
					else
					{
						$plwp_eligible_leaves_total = 0;
						$elegible_absent_after_plwp = 0;
						$total_plwp_given = 0;
						$remaining_plwp_leave_for_hlwp = $RemainingPlwpOrUlwp;
						$remaining_ppl_leave_for_hlwp = $total_avail_leave - $total_planned_leave_taken;
						$ppl_given_for_plwp = $total_avail_leave - $remaining_ppl_leave_for_hlwp;
					}
					
					$total_hlwp = $hlwp * .5 ;

					if($total_hlwp > 0)
					{
						if($elegible_absent_after_plwp > 0)
						{
							$elegible_absent_after_hlwp = $total_hlwp;
							$ppl_given_for_hlwp = 0;
							$total_hlwp_given = 0;
						}
						else
						{	
						
							if($total_hlwp > $remaining_ppl_leave_for_hlwp)
							{
								$plwp_eligible_leaves_total_in_hlwp =  $total_hlwp - $remaining_ppl_leave_for_hlwp;
								$ppl_given_for_hlwp = $remaining_ppl_leave_for_hlwp;
								$remaining_ppl_leave_for_ulwp = 0;
								
								if($plwp_eligible_leaves_total_in_hlwp > $remaining_plwp_leave_for_hlwp)
								{
									$elegible_absent_after_hlwp = $plwp_eligible_leaves_total_in_hlwp - $remaining_plwp_leave_for_hlwp ;
									
									$remaining_plwp_leave_for_ulwp = 0;
									$total_hlwp_given = $remaining_plwp_leave_for_hlwp;
									
								}
								else
								{
									$remaining_plwp_leave_for_ulwp = $remaining_plwp_leave_for_hlwp - $plwp_eligible_leaves_total_in_hlwp;
									$elegible_absent_after_hlwp =0;
									$total_hlwp_given = $plwp_eligible_leaves_total_in_hlwp;
								}
								
								
							}
							else
							{
								$remaining_ppl_leave_for_ulwp = $remaining_ppl_leave_for_hlwp - $total_hlwp;
								$plwp_eligible_leaves_total_in_hlwp = 0;
								$total_hlwp_given = 0;
								$remaining_plwp_leave_for_ulwp = $RemainingPlwpOrUlwp;
								$ppl_given_for_hlwp = $total_hlwp;
								$elegible_absent_after_hlwp = 0;
							}
						}
					}
					else
					{
						$remaining_ppl_leave_for_ulwp = $remaining_ppl_leave_for_hlwp;
						$remaining_plwp_leave_for_ulwp = $remaining_plwp_leave_for_hlwp;
						$elegible_absent_after_hlwp = 0;
						$total_hlwp_given = 0;
						$ppl_given_for_hlwp = 0;
					}
					
					$elegible_absent_after_plwp_hlwp = $elegible_absent_after_hlwp + $elegible_absent_after_plwp;
					if($total_unplanned_leave_taken > 0)
					{
						if($elegible_absent_after_plwp_hlwp > 0)
						{
							$elegible_absent_after_ulwp = $total_unplanned_leave_taken;
							$total_ulwp_given = 0;
							$ppl_given_for_ulwp = 0;
						}
						else
						{
							if($total_unplanned_leave_taken > $remaining_ppl_leave_for_ulwp)
							{
								$plwp_eligible_leaves_total_in_ulwp =  $total_unplanned_leave_taken - $remaining_ppl_leave_for_ulwp;
								$ppl_given_for_ulwp = $remaining_ppl_leave_for_ulwp;
								$remaining_ppl_leave_after_ulwp = 0;
								
								if($plwp_eligible_leaves_total_in_ulwp > $remaining_plwp_leave_for_ulwp)
								{
									$elegible_absent_after_ulwp = $plwp_eligible_leaves_total_in_ulwp - $remaining_plwp_leave_for_ulwp ;
									
									$remaining_plwp_leave_after_ulwp = 0;
									$total_ulwp_given = $remaining_plwp_leave_for_ulwp;
									$remaining_plwp_leave_after_ulwp = 0;
								}
								else
								{
									$remaining_plwp_leave_after_ulwp = $remaining_plwp_leave_for_ulwp - $plwp_eligible_leaves_total_in_ulwp;
									$total_ulwp_given = $remaining_plwp_leave_for_ulwp - $remaining_plwp_leave_after_ulwp;
									$elegible_absent_after_ulwp = 0;
								}
							}
							else
							{
								$ulwp_eligible_leaves_total = 0;
								$elegible_absent_after_ulwp = 0;
								$total_ulwp_given = 0;
								$remaining_plwp_leave_after_ulwp = $RemainingPlwpOrUlwp;
								$remaining_ppl_leave_after_ulwp = $remaining_ppl_leave_for_ulwp - $total_unplanned_leave_taken;
								$ppl_given_for_ulwp = $remaining_ppl_leave_for_ulwp - $remaining_ppl_leave_after_ulwp;
							}
						}
					}
					else
					{
						$remaining_ppl_leave_for_ulwp = $remaining_ppl_leave_for_hlwp;
						$remaining_plwp_leave_for_ulwp = $remaining_plwp_leave_for_hlwp;
						$ppl_given_for_ulwp = 0;
						$remaining_plwp_leave_after_ulwp = 0;
						$elegible_absent_after_ulwp = 0;
						$total_ulwp_given = 0;
					}
					
					if($ha > 0)
					{
						$deducted_abs = $ha * .5;
					}
					else
					{
						$deducted_abs = 0;
					}

					/* here half day absent including in deduction */
					//$total_abs_and_deducted_abs = $deducted_abs + $abs;

					/* here hald day not including in any extra deduction */
					$total_abs_and_deducted_abs = $abs;
					
					$total_leave_after_plwp_ulwp_hlwp = $elegible_absent_after_plwp + $elegible_absent_after_hlwp + $elegible_absent_after_ulwp;
					
					//echo '<br><br>total_leave_after_plwp_ulwp_hlwp---'.$total_leave_after_plwp_ulwp_hlwp;
					
					$total_absence = $total_abs_and_deducted_abs + $total_leave_after_plwp_ulwp_hlwp + $count_abs;
					
					//echo '<br><br>total_absence---'.$total_absence;
					
					$total_absence_old = $total_absence;
					
					
					if($total_absence > 3)
					{
						$deducted_days = (3 * 1.25);
						$total_absence = $total_absence -3;
						if($total_absence > 0)
						{
							if($total_absence > 2)
							{
								$deducted_days = ($deducted_days + (2 * 1.50));
								$total_absence = $total_absence - 2;	
									if($total_absence > 0)
									{
											$deducted_days = ($deducted_days + ($total_absence * 2));
									}
							}
							else
							{						
								$deducted_days = ($deducted_days + ($total_absence * 1.50));
							}
						}
					}
					else if($total_absence < 1)
					{
						$deducted_days = $total_absence;
					}
					else
					{
						$deducted_days = ($total_absence + ($total_absence * .25));
					}
					
					$deducted_days = $deducted_days - $total_absence_old;
			/* when we run this file after shift movement working with our system uncomment this line */
					//$final_deducted_day = $deducted_days + $total_deducted_late_and_break + $total_shift_mooment_unapproved_deduction;
					
			/* for now we not consider shift movement apporval done or not */
					$final_deducted_day = $deducted_days + $total_deducted_late_and_break;

					$total_plwp_given = $total_plwp_given + $plwp;
					$total_hlwp_given = $total_hlwp_given + ($hlwp*.5);
					$total_ulwp_given = $total_ulwp_given + $ulwp;
				
					/*echo '<pre>';
				
					echo '<br>userid--------'.$userid;
					echo '<br>total_avail_leave--------'.$total_avail_leave;
					echo '<br>total_abs_and_deducted_abs--------'.$total_abs_and_deducted_abs;
					echo '<br>total_planned_leave_taken--------'.$total_planned_leave_taken;
					echo '<br>total_hlwp--------'.$total_hlwp;
					echo '<br>total_unplanned_leave_taken--------'.$total_unplanned_leave_taken;
					
					
					echo '<br><br><br>ppl_given_for_plwp--------'.$ppl_given_for_plwp;
					echo '<br>ppl_given_for_hlwp--------'.$ppl_given_for_hlwp;
					echo '<br>ppl_given_for_ulwp--------'.$ppl_given_for_ulwp;
					echo '<br><br>total_plwp_given--------'.$total_plwp_given;
					echo '<br>total_hlwp_given--------'.$total_hlwp_given;
					echo '<br>total_ulwp_given--------'.$total_ulwp_given;
					echo '<br><br>elegible_absent_after_plwp--------'.$elegible_absent_after_plwp;
					echo '<br>elegible_absent_after_hlwp--------'.$elegible_absent_after_hlwp;
					echo '<br>elegible_absent_after_ulwp--------'.$elegible_absent_after_ulwp;
					
					echo '<br><br><br>total_abs_and_deducted_abs--------'.$total_abs_and_deducted_abs;
					echo '<br>here.......total_absence_old--------'.$total_absence_old;
					echo '<br>here.......total_absence--------'.$total_absence;
					echo '<br>deducted_days--------'.$deducted_days;
					echo '<br>deducted_late_comings_days--------'.$deducted_late_comings_days;
					echo '<br>deducted_break_exceed_days--------'.$deducted_break_exceed_days;
					echo '<br>total_deducted_late_and_break--------'.$total_deducted_late_and_break;
					echo '<br>total_shift_mooment_unapproved_deduction--------'.$total_shift_mooment_unapproved_deduction;
					echo '<br>final_deducted_day--------'.$final_deducted_day;
					echo '<br>not_WeekOfDays--------'.$not_WeekOfDays;
					echo '<br>count_ppl'.$count_ppl;
					echo '<br>count_upl'.$count_upl;
					echo '<br>count_abs'.$count_abs;	*/
				
					$total_ppl_consumed = $ppl_given_for_plwp + $ppl_given_for_hlwp + $ppl_given_for_ulwp;
					
					/*echo '<br>in_total--------'.$in_total;
					echo '<br>finalwo--------'.$final_wo;
					echo '<br>total_ph--------'.$total_ph;
					echo '<br><br><br>in_total---'.$in_total.'<br>finalwo----'.$final_wo.'<br>total_ph-----'.$total_ph.'<br>total_uhl------'.$total_uhl.'<br>total_phl-------'.$total_phl;*/
					
					$total_present = $in_total + $final_wo + $total_ph + ($total_uhl * .5) + ($total_phl * .5) + ( $ha * .5 ) + ( $hlwp * .5 ) ;
					//echo '<br><br>total_present---'.$total_present.'<br>final_deducted_day---'.$final_deducted_day.'<br>total_ppl_consumed---'.$total_ppl_consumed.'<br>';
					$payDays = ($total_present - $final_deducted_day) + $total_ppl_consumed;
					//echo '<br>-leaves_earn----'.$leaves_earn;
					//echo '<br>payDays---'.$payDays;
					if($payDays < 15)
					{
						$leaves_earn = 0;
					}
					else if($payDays < 24)
					{
						$leaves_earn = 1;
					}
					else
					{
						$leaves_earn = 2;
					}
					//echo '<br>-leaves_earn1----'.$leaves_earn;
					/* Deduct leave earned half monthly */
					//echo '<br>HalfMonthlyLeavesEarned-----'.$HalfMonthlyLeavesEarned;
					if($HalfMonthlyLeavesEarned > 0)
					{
						$leaves_earn = $leaves_earn - $HalfMonthlyLeavesEarned;
					}
					if($leaves_earn < 0)
					{
						/* Remove leaves from paydays is leaves is less than 0 */
						$payDays = $payDays + $leaves_earn;
					}
					//echo '<br>payDays---'.$payDays;
					$ClosingLeavesBalance = ($total_avail_leave - $total_ppl_consumed) + $leaves_earn;

					$total_leave_taken = $total_absence + $total_upl + ($total_phl * .5 ) + ($total_uhl * .5 ) + $total_ppl + $abs + ($ha * .5);

					//echo '<br>total_leave_taken--------'.$total_leave_taken;
					//echo '<br>ClosingLeavesBalance--------'.$ClosingLeavesBalance;

					$current_date = date("Y-m-d H:i:s");

					/* get that record exist in the leave record table or not */
					$checkRecordExistence = $objCalculation -> fnCheckExistence($userid,$month,$year);

					/* get that record exist in the monthly report table or not */
					$checkMonthlyReportRecordExistence = $objCalculation -> fnCheckExistenceMonthlyReport($userid,$month,$year);
					
					
					

					/* check record exist in leave histy */
					if(isset($checkRecordExistence) &&  $checkRecordExistence != '')
					{
						//echo 'hello4';
						//echo $checkRecordExistence;
						$reason = array("p"=>$in_total,"total_plt"=>$total_late_coming,"ppl"=>$total_planned_leave_taken_with_sandwitch,"uhl"=>$total_uhl,"total_phl"=>$total_phl,"wo"=>$final_wo,"ph"=>$total_ph,"ha"=>$ha,"hlwp"=>$total_hlwp_given,"a"=>$abs,"plwp"=>$total_plwp_given,"ulwp"=>$total_ulwp_given,"upl"=>$total_unplanned_leave_taken_with_sandwitch,"total_present"=>$total_present,"deducted_days"=>$final_deducted_day,"payDays"=>$payDays,"OpeningLeavesBalance"=>$total_avail_leave,"leave_earn"=>$leaves_earn,"pl_consume"=>$total_ppl_consumed,"ClosingLeavesBalance"=>$ClosingLeavesBalance);
				
						$summary = array("id"=>$checkRecordExistence,"emp_id"=>$userid,"added_no_of_leaves"=>$leaves_earn,"date"=>$current_date,"reason"=>json_encode($reason),"opening_leave"=>$total_avail_leave,"closing_leave"=>$ClosingLeavesBalance,"ishalfmonthly"=>'0',"leave_added_date"=>$attendance_date);

						//echo '<br><br>----------<pre>'; print_r($summary);
						
						$insertSummary = $objCalculation -> fnUpdateHalfSummary($checkRecordExistence,$summary,$month,$year);

						//$insertRemainingLeaves = $objCalculation -> fnUpdateRemainingLeave($userid,$ClosingLeavesBalance);


						/* unset the id and save the record in summary log table always*/
						unset($summary['id']);
						$insertSummary1 = $objCalculation -> fnInsertHalfSummaryLog($summary,$month,$year);

						
					}
					else
					{
						//echo 'hello3';
						$reason = array("p"=>$in_total,"total_plt"=>$total_late_coming,"ppl"=>$total_planned_leave_taken_with_sandwitch,"uhl"=>$total_uhl,"total_phl"=>$total_phl,"wo"=>$final_wo,"ph"=>$total_ph,"ha"=>$ha,"hlwp"=>$total_hlwp_given,"a"=>$abs,"plwp"=>$total_plwp_given,"ulwp"=>$total_ulwp_given,"upl"=>$total_unplanned_leave_taken_with_sandwitch,"total_present"=>$total_present,"deducted_days"=>$final_deducted_day,"payDays"=>$payDays,"OpeningLeavesBalance"=>$total_avail_leave,"leave_earn"=>$leaves_earn,"pl_consume"=>$total_ppl_consumed,"ClosingLeavesBalance"=>$ClosingLeavesBalance);
				
						$summary = array("emp_id"=>$userid,"added_no_of_leaves"=>$leaves_earn,"date"=>$current_date,"reason"=>json_encode($reason),"opening_leave"=>$total_avail_leave,"closing_leave"=>$ClosingLeavesBalance,"ishalfmonthly"=>'0');

						//echo '<br><br>----------<pre>'; print_r($summary);
						
						$insertSummary = $objCalculation -> fnInsertHalfSummary($summary,$month,$year);

						
						/* unset the id and save the record in summary log table always*/
						
						$insertSummary1 = $objCalculation -> fnInsertHalfSummaryLog($summary,$month,$year);
						
						
					}
					/* check record exist in pms_attendance_report  table or not */					
					if(isset($checkMonthlyReportRecordExistence) &&  $checkMonthlyReportRecordExistence != '')
					{
						//echo 'hello1';
						/* if value exist update in pms_attendance_report table */
						$finalCalcHalfMonthly = array("id"=>$checkMonthlyReportRecordExistence,"employee_id"=>$userid,"month"=>$month,"year"=>$year,"present"=>$in_total,"break_exceeds"=>$break_exceed_days,"total_plt"=>$total_late_coming,"ppl"=>$total_planned_leave_taken_with_sandwitch,"uhl"=>$total_uhl,"wo"=>$final_wo,"ph"=>$total_ph,"ha"=>$ha,"hlwp"=>$total_hlwp_given,"abs"=>$abs,"plwp"=>$total_plwp_given,"ulwp"=>$total_ulwp_given,"nj"=>'0',"le"=>'0',"awol"=>'0',"total_present"=>$total_present,"pay_days"=>$payDays,"opening_leave"=>$total_avail_leave,"leave_earns"=>$leaves_earn,"pl_taken"=>$total_ppl_consumed,"eml_taken"=>'0',"phl_taken"=>$total_phl,"uhl_taken"=>$total_uhl,"total_leave_taken"=>$total_leave_taken,"closing_balance"=>$ClosingLeavesBalance,"upl"=>$total_unplanned_leave_taken_with_sandwitch,"deducted_days"=>$final_deducted_day);

						//echo '<br><br>----------<pre>'; print_r($finalCalcHalfMonthly);

						$insertRemainingLeaves = $objCalculation -> fnUpdateRemainingLeave($userid,$ClosingLeavesBalance);

						$insertHalfReport = $objCalculation -> fnUpdateMonthReport($finalCalcHalfMonthly);
						
					}
					else
					{
						/* Insert the records in  pms_attendance_report table*/
						//echo 'hello2';
						$finalCalcHalfMonthly = array("employee_id"=>$userid,"month"=>$month,"year"=>$year,"present"=>$in_total,"break_exceeds"=>$break_exceed_days,"total_plt"=>$total_late_coming,"ppl"=>$total_planned_leave_taken_with_sandwitch,"uhl"=>$total_uhl,"wo"=>$final_wo,"ph"=>$total_ph,"ha"=>$ha,"hlwp"=>$total_hlwp_given,"abs"=>$abs,"plwp"=>$total_plwp_given,"ulwp"=>$total_ulwp_given,"nj"=>'0',"le"=>'0',"awol"=>'0',"total_present"=>$total_present,"pay_days"=>$payDays,"opening_leave"=>$total_avail_leave,"leave_earns"=>$leaves_earn,"pl_taken"=>$total_ppl_consumed,"eml_taken"=>'0',"phl_taken"=>$total_phl,"uhl_taken"=>$total_uhl,"total_leave_taken"=>$total_leave_taken,"closing_balance"=>$ClosingLeavesBalance,"upl"=>$total_unplanned_leave_taken_with_sandwitch,"deducted_days"=>$final_deducted_day);

						//echo '<br><br>----------<pre>'; print_r($finalCalcHalfMonthly);
						
						$insertRemainingLeaves = $objCalculation -> fnUpdateRemainingLeave($userid,$ClosingLeavesBalance);

						$insertHalfReport = $objCalculation -> fnInsertMonthReport($finalCalcHalfMonthly);
					}

					
					
					//die;

					/*$reason = array("p"=>$in_total,"total_plt"=>$total_late_coming,"ppl"=>$total_planned_leave_taken_with_sandwitch,"uhl"=>$total_uhl,"total_phl"=>$total_phl,"wo"=>$final_wo,"ph"=>$total_ph,"ha"=>$ha,"hlwp"=>$total_hlwp_given,"a"=>$abs,"plwp"=>$total_plwp_given,"ulwp"=>$total_ulwp_given,"upl"=>$total_unplanned_leave_taken_with_sandwitch,"total_present"=>$total_present,"deducted_days"=>$final_deducted_day,"payDays"=>$payDays,"OpeningLeavesBalance"=>$total_avail_leave,"leave_earn"=>$leaves_earn,"pl_consume"=>$total_ppl_consumed,"ClosingLeavesBalance"=>$ClosingLeavesBalance);
				
					$summary = array("emp_id"=>$userid,"added_no_of_leaves"=>$leaves_earn,"date"=>$current_date,"reason"=>json_encode($reason),"opening_leave"=>$total_avail_leave,"closing_leave"=>$ClosingLeavesBalance,"ishalfmonthly"=>'0');
					//echo '<pre>'; print_r($summary); die;
					
					$insertSummary = $objCalculation -> fnInsertHalfSummary($summary,$month,$year);
					
					$insertSummary1 = $objCalculation -> fnInsertHalfSummaryLog($summary,$month,$year);
					

					$insertRemainingLeaves = $objCalculation -> fnUpdateRemainingLeave($userid,$ClosingLeavesBalance);

					$finalCalcHalfMonthly = array("employee_id"=>$userid,"month"=>$month,"year"=>$year,"present"=>$in_total,"break_exceeds"=>$break_exceed_days,"total_plt"=>$total_late_coming,"ppl"=>$total_planned_leave_taken_with_sandwitch,"uhl"=>$total_uhl,"wo"=>$final_wo,"ph"=>$total_ph,"ha"=>$ha,"hlwp"=>$total_hlwp_given,"abs"=>$abs,"plwp"=>$total_plwp_given,"ulwp"=>$total_ulwp_given,"nj"=>'0',"le"=>'0',"awol"=>'0',"total_present"=>$total_present,"pay_days"=>$payDays,"opening_leave"=>$total_avail_leave,"leave_earns"=>$leaves_earn,"pl_taken"=>$total_ppl_consumed,"eml_taken"=>'0',"phl_taken"=>$total_phl,"uhl_taken"=>$total_uhl,"total_leave_taken"=>$total_leave_taken,"closing_balance"=>$ClosingLeavesBalance,"upl"=>$total_unplanned_leave_taken_with_sandwitch,"deducted_days"=>$final_deducted_day);

					//echo '<pre>'; print_r($finalCalcHalfMonthly); 

					$insertHalfReport = $objCalculation -> fnInsertMonthReport($finalCalcHalfMonthly);*/
					//die;
				}
			}
		}

		function fnGetAttendanceShiftByUserAndDate($UserId, $Date)
		{
			$ShiftId = 0;
			$sSQL = "select shift_id from pms_attendance where user_id='".mysql_real_escape_string($UserId)."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($Date)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$ShiftId = $this->f("shift_id");
				}
			}
			
			return $ShiftId;
		}
	}
?>

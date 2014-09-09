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

			$attendance_month = $curYear."-".$curMonth;
			$actual_cur_month = Date('Y-m');

			if(date('Y-m-d') > date('Y-m-08') && ($attendance_month < $actual_cur_month))
			{
				return false;
			}

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
					else if(($intime1 < $ActualshiftTimings['starttime'] && $outime1 < $ActualshiftTimings['starttime']) || ($intime1 > $ActualshiftTimings['endtime'] && $outime1 > $ActualshiftTimings['endtime']) || ($outime1 < $intime1 && $intime1 > $ActualshiftTimings['endtime'] && $outime1 < $ActualshiftTimings['starttime']))
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
					else if(($intime2 < $ActualshiftTimings['starttime'] && $outime2 < $ActualshiftTimings['starttime']) || ($intime2 > $ActualshiftTimings['endtime'] && $outime2 > $ActualshiftTimings['endtime']) || ($outime2 < $intime2 && $intime2 > $ActualshiftTimings['endtime'] && $outime2 < $ActualshiftTimings['starttime']))
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
					else if(($intime3 < $ActualshiftTimings['starttime'] && $outime3 < $ActualshiftTimings['starttime']) || ($intime3 > $ActualshiftTimings['endtime'] && $outime3 > $ActualshiftTimings['endtime']) || ($outime3 < $intime3 && $intime3 > $ActualshiftTimings['endtime'] && $outime3 < $ActualshiftTimings['starttime']))
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
					else if(($intime4 < $ActualshiftTimings['starttime'] && $outime4 < $ActualshiftTimings['starttime']) || ($intime4 > $ActualshiftTimings['endtime'] && $outime4 > $ActualshiftTimings['endtime']) || ($outime4 < $intime4 && $intime4 > $ActualshiftTimings['endtime'] && $outime4 < $ActualshiftTimings['starttime']))
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
					else if(($intime5 < $ActualshiftTimings['starttime'] && $outime5 < $ActualshiftTimings['starttime']) || ($intime5 > $ActualshiftTimings['endtime'] && $outime5 > $ActualshiftTimings['endtime']) || ($outime5 < $intime5 && $intime5 > $ActualshiftTimings['endtime'] && $outime5 < $ActualshiftTimings['starttime']))
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

				$shiftmovement_break = $fullday_break = $halfday_break = '00:00:00';
				if(isset($arrEmployee['calculation_time'][$value]))
				{
					$objCalculationTime = json_decode($arrEmployee['calculation_time'][$value]);
					
					$objCalculationTime->sm_break_minutes;
					if(isset($objCalculationTime->sm_break_minutes))
						$shiftmovement_break = $objCalculationTime->sm_break_minutes.':00';

					$objCalculationTime->fullday_break_minutes;
					if(isset($objCalculationTime->fullday_break_minutes))
						$fullday_break = $objCalculationTime->fullday_break_minutes.':00';

					$objCalculationTime->halfday_break_minutes;
					if(isset($objCalculationTime->halfday_break_minutes))
						$halfday_break = $objCalculationTime->halfday_break_minutes.':00';
				}
				
				if($arrEmployee['leave_id'][$value] == '4' || $arrEmployee['leave_id'][$value] == '5' || $arrEmployee['leave_id'][$value] == '8' || $arrEmployee['leave_id'][$value] == '12')
				{
					/* Halfday break calculation */
					
					$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')) ,if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) AS totalbreak,ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('".$arrEmployee['break1_out'][$value].":00' < '".$arrEmployee['break1_in'][$value].":00' ,ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break1_in'][$value].":00'), '".$arrEmployee['break1_out'][$value].":00'),TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00')) ,if('".$arrEmployee['break2_out'][$value].":00' < '".$arrEmployee['break2_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break2_in'][$value].":00'), '".$arrEmployee['break2_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00'))),if('".$arrEmployee['break3_out'][$value].":00' < '".$arrEmployee['break3_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break3_in'][$value].":00'), '".$arrEmployee['break3_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00'))),if('".$arrEmployee['break4_out'][$value].":00' < '".$arrEmployee['break4_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break4_in'][$value].":00'), '".$arrEmployee['break4_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00'))),if('".$arrEmployee['break5_out'][$value].":00' < '".$arrEmployee['break5_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break5_in'][$value].":00'), '".$arrEmployee['break5_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00'))) AS complete_break, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')),if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) > '".$halfday_break."','1','0') as isExceeded";
					
					/*if($emp_designation == "20" || $emp_designation == "21" || $emp_designation == "22" || $emp_designation == "23" || $emp_designation == "24" || $emp_designation == "25" || $emp_designation == "26" || $emp_designation == "27" || $emp_designation == "28" )
					{
						// IT Support
						$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')) ,if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) AS totalbreak,ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('".$arrEmployee['break1_out'][$value].":00' < '".$arrEmployee['break1_in'][$value].":00' ,ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break1_in'][$value].":00'), '".$arrEmployee['break1_out'][$value].":00'),TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00')) ,if('".$arrEmployee['break2_out'][$value].":00' < '".$arrEmployee['break2_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break2_in'][$value].":00'), '".$arrEmployee['break2_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00'))),if('".$arrEmployee['break3_out'][$value].":00' < '".$arrEmployee['break3_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break3_in'][$value].":00'), '".$arrEmployee['break3_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00'))),if('".$arrEmployee['break4_out'][$value].":00' < '".$arrEmployee['break4_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break4_in'][$value].":00'), '".$arrEmployee['break4_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00'))),if('".$arrEmployee['break5_out'][$value].":00' < '".$arrEmployee['break5_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break5_in'][$value].":00'), '".$arrEmployee['break5_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00'))) AS complete_break, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')),if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) > '00:20:00','1','0') as isExceeded";
					}
					else
					{
						$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')) ,if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) AS totalbreak,ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('".$arrEmployee['break1_out'][$value].":00' < '".$arrEmployee['break1_in'][$value].":00' ,ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break1_in'][$value].":00'), '".$arrEmployee['break1_out'][$value].":00'),TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00')) ,if('".$arrEmployee['break2_out'][$value].":00' < '".$arrEmployee['break2_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break2_in'][$value].":00'), '".$arrEmployee['break2_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00'))),if('".$arrEmployee['break3_out'][$value].":00' < '".$arrEmployee['break3_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break3_in'][$value].":00'), '".$arrEmployee['break3_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00'))),if('".$arrEmployee['break4_out'][$value].":00' < '".$arrEmployee['break4_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break4_in'][$value].":00'), '".$arrEmployee['break4_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00'))),if('".$arrEmployee['break5_out'][$value].":00' < '".$arrEmployee['break5_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break5_in'][$value].":00'), '".$arrEmployee['break5_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00'))) AS complete_break, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')),if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) > '00:15:00','1','0') as isExceeded";
					}*/
				}
				else if($arrEmployee['leave_id'][$value] == '11' || $arrEmployee['leave_id'][$value] == '14')
				{
					$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')) ,if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) AS totalbreak,ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('".$arrEmployee['break1_out'][$value].":00' < '".$arrEmployee['break1_in'][$value].":00' ,ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break1_in'][$value].":00'), '".$arrEmployee['break1_out'][$value].":00'),TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00')) ,if('".$arrEmployee['break2_out'][$value].":00' < '".$arrEmployee['break2_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break2_in'][$value].":00'), '".$arrEmployee['break2_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00'))),if('".$arrEmployee['break3_out'][$value].":00' < '".$arrEmployee['break3_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break3_in'][$value].":00'), '".$arrEmployee['break3_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00'))),if('".$arrEmployee['break4_out'][$value].":00' < '".$arrEmployee['break4_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break4_in'][$value].":00'), '".$arrEmployee['break4_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00'))),if('".$arrEmployee['break5_out'][$value].":00' < '".$arrEmployee['break5_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break5_in'][$value].":00'), '".$arrEmployee['break5_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00'))) AS complete_break, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')),if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) > '".$shiftmovement_break."','1','0') as isExceeded";
				}
				else
				{
					$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')) ,if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) AS totalbreak,ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('".$arrEmployee['break1_out'][$value].":00' < '".$arrEmployee['break1_in'][$value].":00' ,ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break1_in'][$value].":00'), '".$arrEmployee['break1_out'][$value].":00'),TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00')) ,if('".$arrEmployee['break2_out'][$value].":00' < '".$arrEmployee['break2_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break2_in'][$value].":00'), '".$arrEmployee['break2_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00'))),if('".$arrEmployee['break3_out'][$value].":00' < '".$arrEmployee['break3_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break3_in'][$value].":00'), '".$arrEmployee['break3_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00'))),if('".$arrEmployee['break4_out'][$value].":00' < '".$arrEmployee['break4_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break4_in'][$value].":00'), '".$arrEmployee['break4_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00'))),if('".$arrEmployee['break5_out'][$value].":00' < '".$arrEmployee['break5_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break5_in'][$value].":00'), '".$arrEmployee['break5_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00'))) AS complete_break, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')),if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) > '".$fullday_break."','1','0') as isExceeded";
					
					/*if($emp_designation == "20" || $emp_designation == "21" || $emp_designation == "22" || $emp_designation == "23" || $emp_designation == "24" || $emp_designation == "25" || $emp_designation == "26" || $emp_designation == "27" || $emp_designation == "28" )
					{
						// IT Support
						$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')) ,if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) AS totalbreak,ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('".$arrEmployee['break1_out'][$value].":00' < '".$arrEmployee['break1_in'][$value].":00' ,ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break1_in'][$value].":00'), '".$arrEmployee['break1_out'][$value].":00'),TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00')) ,if('".$arrEmployee['break2_out'][$value].":00' < '".$arrEmployee['break2_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break2_in'][$value].":00'), '".$arrEmployee['break2_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00'))),if('".$arrEmployee['break3_out'][$value].":00' < '".$arrEmployee['break3_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break3_in'][$value].":00'), '".$arrEmployee['break3_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00'))),if('".$arrEmployee['break4_out'][$value].":00' < '".$arrEmployee['break4_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break4_in'][$value].":00'), '".$arrEmployee['break4_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00'))),if('".$arrEmployee['break5_out'][$value].":00' < '".$arrEmployee['break5_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break5_in'][$value].":00'), '".$arrEmployee['break5_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00'))) AS complete_break, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')),if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) > '00:45:00','1','0') as isExceeded";
					}
					else
					{
						$query = "Select ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')) ,if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) AS totalbreak,ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('".$arrEmployee['break1_out'][$value].":00' < '".$arrEmployee['break1_in'][$value].":00' ,ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break1_in'][$value].":00'), '".$arrEmployee['break1_out'][$value].":00'),TIMEDIFF('".$arrEmployee['break1_out'][$value].":00','".$arrEmployee['break1_in'][$value].":00')) ,if('".$arrEmployee['break2_out'][$value].":00' < '".$arrEmployee['break2_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break2_in'][$value].":00'), '".$arrEmployee['break2_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break2_out'][$value].":00','".$arrEmployee['break2_in'][$value].":00'))),if('".$arrEmployee['break3_out'][$value].":00' < '".$arrEmployee['break3_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break3_in'][$value].":00'), '".$arrEmployee['break3_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break3_out'][$value].":00','".$arrEmployee['break3_in'][$value].":00'))),if('".$arrEmployee['break4_out'][$value].":00' < '".$arrEmployee['break4_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break4_in'][$value].":00'), '".$arrEmployee['break4_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break4_out'][$value].":00','".$arrEmployee['break4_in'][$value].":00'))),if('".$arrEmployee['break5_out'][$value].":00' < '".$arrEmployee['break5_in'][$value].":00',ADDTIME(TIMEDIFF('24:00:00','".$arrEmployee['break5_in'][$value].":00'), '".$arrEmployee['break5_out'][$value].":00'), TIMEDIFF('".$arrEmployee['break5_out'][$value].":00','".$arrEmployee['break5_in'][$value].":00'))) AS complete_break, if(ADDTIME (ADDTIME (ADDTIME (ADDTIME (if('$outime1' < '$intime1' ,ADDTIME(TIMEDIFF('24:00:00','$intime1'), '$outime1'),TIMEDIFF('$outime1','$intime1')),if('$outime2' < '$intime2',ADDTIME(TIMEDIFF('24:00:00','$intime2'), '$outime2'), TIMEDIFF('$outime2','$intime2'))),if('$outime3' < '$intime3',ADDTIME(TIMEDIFF('24:00:00','$intime3'), '$outime3'), TIMEDIFF('$outime3','$intime3'))),if('$outime4' < '$intime4',ADDTIME(TIMEDIFF('24:00:00','$intime4'), '$outime4'), TIMEDIFF('$outime4','$intime4'))),if('$outime5' < '$intime5',ADDTIME(TIMEDIFF('24:00:00','$intime5'), '$outime5'), TIMEDIFF('$outime5','$intime5'))) > '00:40:00','1','0') as isExceeded";
					}*/
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
								$exceedTime = strtotime(Date('Y-m-d')." ".$totalBreak) - strtotime(Date('Y-m-d')." ".$halfday_break);
								/*if($emp_designation == "20" || $emp_designation == "21" || $emp_designation == "22" || $emp_designation == "23" || $emp_designation == "24" || $emp_designation == "25" || $emp_designation == "26" || $emp_designation == "27" || $emp_designation == "28" )
								{
									$exceedTime = strtotime(Date('Y-m-d')." ".$totalBreak) - strtotime(Date('Y-m-d')." 00:20:00");
								}
								else
								{
									$exceedTime = strtotime(Date('Y-m-d')." ".$totalBreak) - strtotime(Date('Y-m-d')." 00:15:00");
								}*/
								$Actual_exceedTime = gmdate("H:i:s", $exceedTime);
							}
						}
						else
						{
							if($breakExceed == '1')
							{
								$exceedTime = strtotime(Date('Y-m-d')." ".$totalBreak) - strtotime(Date('Y-m-d')." ".$fullday_break);
								/*if($emp_designation == "20" || $emp_designation == "21" || $emp_designation == "22" || $emp_designation == "23" || $emp_designation == "24" || $emp_designation == "25" || $emp_designation == "26" || $emp_designation == "27" || $emp_designation == "28" )
								{
									$exceedTime = strtotime(Date('Y-m-d')." ".$totalBreak) - strtotime(Date('Y-m-d')." 00:45:00");
								}
								else
								{
									$exceedTime = strtotime(Date('Y-m-d')." ".$totalBreak) - strtotime(Date('Y-m-d')." 00:40:00");
								}*/
								$Actual_exceedTime = gmdate("H:i:s", $exceedTime);
							}
						}
					}
				}
					//echo '<br>'.'totalBreak'.$totalBreak.'<br>';
					//echo 'breakExceed'.$breakExceed.'<br>';
				$differ = '00:00:00';
				$differ1 = '00:00:00';

				//$allowed = array(6,7,13,18,19,20,21,22,23,24,25,26,27,28,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,46);

				//if($checkTeamLeader == '7' || $checkTeamLeader == '13' || $checkTeamLeader == '6')
				//if(in_array($checkTeamLeader,$allowed))
				if($objCalculationTime->consider_inout_time == 1)
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

					$arrNewRecords = array("user_id"=>$value,"date"=>$date,"in_time"=>$arrEmployee['in_time'][$value].':00',"out_time"=>$arrEmployee['out_time'][$value].':00',"break1_in"=>$arrEmployee['break1_in'][$value].':00',"break1_out"=>$arrEmployee['break1_out'][$value].':00',"break2_in"=>$arrEmployee['break2_in'][$value].':00',"break2_out"=>$arrEmployee['break2_out'][$value].':00',"break3_in"=>$arrEmployee['break3_in'][$value].':00',"break3_out"=>$arrEmployee['break3_out'][$value].':00',"break4_in"=>$arrEmployee['break4_in'][$value].':00',"break4_out"=>$arrEmployee['break4_out'][$value].':00',"break5_in"=>$arrEmployee['break5_in'][$value].':00',"break5_out"=>$arrEmployee['break5_out'][$value].':00',"leave_id"=>$arrEmployee['leave_id'][$value],"is_late"=>$checklate,"total_break_time"=>$totalBreak,"isExceededBreak"=>$breakExceed,"late_time"=>$late_time,"official_total_working_hours"=>$differ,"total_working_hours"=>$differ1,"break_exceed_time"=>$Actual_exceedTime,"shift_id"=>$arrEmployee['shift_id'][$value],"complete_break"=>$completeBreak,"last_modified"=>Date('Y-m-d H:i:s'),"attendance_remarks"=>$arrEmployee['attendance_remarks'][$value]);
					$this->insertArray('pms_attendance',$arrNewRecords);
					//print_r($arrNewRecords);
				}
				else
				{
					//echo '<br>hello<br>';
					$arrNewRecords = array("id"=>$arrEmployee['hdnattendanceid'][$value],"user_id"=>$value,"date"=>$date,"in_time"=>$arrEmployee['in_time'][$value].':00',"out_time"=>$arrEmployee['out_time'][$value].':00',"break1_in"=>$arrEmployee['break1_in'][$value].':00',"break1_out"=>$arrEmployee['break1_out'][$value].':00',"break2_in"=>$arrEmployee['break2_in'][$value].':00',"break2_out"=>$arrEmployee['break2_out'][$value].':00',"break3_in"=>$arrEmployee['break3_in'][$value].':00',"break3_out"=>$arrEmployee['break3_out'][$value].':00',"break4_in"=>$arrEmployee['break4_in'][$value].':00',"break4_out"=>$arrEmployee['break4_out'][$value].':00',"break5_in"=>$arrEmployee['break5_in'][$value].':00',"break5_out"=>$arrEmployee['break5_out'][$value].':00',"leave_id"=>$arrEmployee['leave_id'][$value],"is_late"=>$checklate,"total_break_time"=>$totalBreak,"isExceededBreak"=>$breakExceed,"late_time"=>$late_time, "official_total_working_hours"=>$differ ,"total_working_hours"=>$differ1,"break_exceed_time"=>$Actual_exceedTime,"shift_id"=>$arrEmployee['shift_id'][$value],"complete_break"=>$completeBreak,"last_modified"=>Date('Y-m-d H:i:s'),"attendance_remarks"=>$arrEmployee['attendance_remarks'][$value]);
					//echo '<pre>'; print_r($arrNewRecords);
					$this->updateArray('pms_attendance',$arrNewRecords);
					//print_r($arrNewRecords);
				}

				/* Insert half monthly report for the employee */
				$this->fnCalculateHalfMonthlyReport($arrNewRecords["user_id"],$curDay,$curMonth,$curYear,$emp_designation, $objCalculationTime);
				//die;
				
				/* Changes the marking of the leave balance, if the leave is earned */
				$this->fnAutoLeaveStatusUpdation($arrNewRecords["user_id"],$curDay,$curMonth,$curYear);

				/* Insert monthly report for the employee */
				$this->fnCalculateMonthlyReport($arrNewRecords["user_id"],$curDay,$curMonth,$curYear,$emp_designation, $objCalculationTime);

				/* Send mail if absent for 3 days */
				$this->fnSendAbsentMail($arrNewRecords["user_id"]);

				//echo '<pre>';
				//echo '<br>';
				//print_r($arrNewRecords);
				//echo '<br>';
			}
			//print_r($arrNewRecords);

			return true;
		}
		
		function fnAutoLeaveStatusUpdation($userId, $curDate, $curMonth, $curYear)
		{
			$db = new DB_Sql();
			$mb = new DB_Sql();
			
			$arrAllLeaveDates = array();

			$monthYr = $curYear."-".$curMonth;

			include_once("class.calculation.php");
			include_once("class.leave.php");
			include_once("class.employee.php");

			$objCalculation = new calculation();
			$objLeave = new leave();
			$objEmployee = new employee();
			
			$checkFromDate = $curYear."-".$curMonth."-01";

			/* Fetch attendance month and year for saving the data */
			$sSQL = "select opening_leave_balance_month, opening_leave_balance_year from pms_employee where id='".mysql_real_escape_string($userId)."'";
			$db->query($sSQL);
			if($db->num_rows() > 0)
			{
				if($db->next_record())
					$checkFromDate = $db->f("opening_leave_balance_year")."-".$db->f("opening_leave_balance_month")."-01";
			}

			$sSQL = "select date_format(a.date, '%Y-%m-%d') as attendance_date, date_format(a.date, '%m') as attendance_month, date_format(a.date, '%Y') as attendance_year, a.leave_id, a.in_time, a.out_time from pms_attendance a INNER JOIN pms_employee e  ON a.user_id = e.id where a.leave_id in (1,2,3,4,5,6,7,8,12,17) and a.user_id='".mysql_real_escape_string($userId)."' and date_format(a.date,'%Y-%m-%d') >= '".$checkFromDate."' and ((e.status='0' and date_format(e.date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($curYear)."-".mysql_real_escape_string($curMonth)."-".mysql_real_escape_string($curDate)."') or (e.status='1' and date_format(e.relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($curYear)."-".mysql_real_escape_string($curMonth)."-".mysql_real_escape_string($curDate)."')) and date_format(a.date,'%Y-%m-%d') not in (select date_format(lwp_date,'%Y-%m-%d') from pms_approved_lwp where user_id='".mysql_real_escape_string($userId)."' and date_format(lwp_date,'%Y-%m-%d') >= '".$checkFromDate."' and approval_status='1') order by a.date desc";

			$db->query($sSQL);
			if($db->num_rows() > 0)
			{
				while($db->next_record())
				{
					$temparr = array("date" => $db->f("attendance_date"), "month" => $db->f("attendance_month"), "year" => $db->f("attendance_year"), "leave_id" => $db->f("leave_id"), "in_time" => $db->f("in_time"), "out_time" => $db->f("out_time"), "isWoPh" => 0);
					$arrAllLeaveDates[$db->f("attendance_date")] = $temparr;
				}
			}

			$arrLwp = array();
			
			$sSQL = "select date_format(lwp_date,'%Y-%m-%d') as lwp_dt from pms_approved_lwp where user_id='".mysql_real_escape_string($userId)."' and date_format(lwp_date,'%Y-%m-%d') >= '".$checkFromDate."' and approval_status='1'";
			$db->query($sSQL);
			if($db->num_rows() > 0)
			{
				while($db->next_record())
				{
					$arrLwp[] = $db->f("lwp_dt");
				}
			}

			$weekOfDays = $objCalculation->fnGetWeekOfDates($userId,$curMonth,$curYear);
			
			$arrPhAndWo = array();
			$arrSandwitchPhAndWo = array();

			if(count($weekOfDays))
			{
				foreach($weekOfDays as $curWeekOfDays)
				{
					$curWeekOffDate = date('Y-m-d',strtotime($curWeekOfDays["date"]));
					
					$arrPhAndWo[$curWeekOffDate] = $curWeekOffDate;
					
					/* Fetch if prev / next dates are leaves / public holidays */
					$prev_date = date('Y-m-d', strtotime('-1 day', strtotime($curWeekOffDate)));
					$next_date = date('Y-m-d', strtotime('+1 day', strtotime($curWeekOffDate)));

					/* Check if off marked */
					while($this->fnCheckOffMarked($userId, $prev_date))
					{
						/* of taking PH /WO, check for the date previous than that */
						$prev_date = date('Y-m-d', strtotime('-1 day', strtotime($prev_date)));
					}
					
					/* Check for leave status */
					$prevLeaveMarked = $this->fnCheckLeaveMarked($userId, $prev_date);
					
					/* Check if off marked */
					while($this->fnCheckOffMarked($userId, $next_date))
					{
						/* of taking PH /WO, check for the date previous than that */
						$next_date = date('Y-m-d', strtotime('+1 day', strtotime($next_date)));
					}
					
					/* Check for leave status */
					$nextLeaveMarked = $this->fnCheckLeaveMarked($userId, $next_date);
					
					if($prevLeaveMarked == true && $nextLeaveMarked == true)
					{
						/* Mark it as sandwich */
						$sSQL = "select date_format(a.date, '%Y-%m-%d') as attendance_date, date_format(a.date, '%m') as attendance_month, date_format(a.date, '%Y') as attendance_year, a.leave_id, a.in_time, a.out_time from pms_attendance a INNER JOIN pms_employee e  ON a.user_id = e.id where a.user_id='".mysql_real_escape_string($userId)."' and date_format(a.date,'%Y-%m-%d') = '".mysql_real_escape_string($curWeekOffDate)."' and ((e.status='0' and date_format(e.date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($curYear)."-".mysql_real_escape_string($curMonth)."-".mysql_real_escape_string($curDate)."') or (e.status='1' and date_format(e.relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($curYear)."-".mysql_real_escape_string($curMonth)."-".mysql_real_escape_string($curDate)."'))";
						$db->query($sSQL);
						if($db->num_rows() > 0)
						{
							if($db->next_record())
							{
								/* Check leave id for the prev date */
								$tempLeaveId = $db->f("leave_id");

								if(isset($arrAllLeaveDates[$prev_date]["leave_id"]))
									$tempLeaveId = $arrAllLeaveDates[$prev_date]["leave_id"];
								else
								{
									/* Fetch leave ID from attendance */
									$sSQL = "select a.leave_id from pms_attendance a INNER JOIN pms_employee e  ON a.user_id = e.id where a.user_id='".mysql_real_escape_string($userId)."' and date_format(a.date,'%Y-%m-%d') = '".mysql_real_escape_string($prev_date)."' and ((e.status='0' and date_format(e.date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($curYear)."-".mysql_real_escape_string($curMonth)."-".mysql_real_escape_string($curDate)."') or (e.status='1' and date_format(e.relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($curYear)."-".mysql_real_escape_string($curMonth)."-".mysql_real_escape_string($curDate)."'))";
									$mb->query($sSQL);
									if($mb->num_rows() > 0)
									{
										if($mb->next_record())
										{
											$tempLeaveId = $mb->f("leave_id");
										}
									}
								}

								$arrAllLeaveDates[$db->f("attendance_date")] = array("date" => $db->f("attendance_date"), "month" => $db->f("attendance_month"), "year" => $db->f("attendance_year"), "leave_id" => $tempLeaveId, "in_time" => $db->f("in_time"), "out_time" => $db->f("out_time"), "isWoPh" => 1);
																
								$arrSandwitchPhAndWo[$db->f("attendance_date")] = $db->f("attendance_date");
							}
						}
					}
				}
			}
			
			$EmployeeInfo = $objEmployee->fnGetEmployeeById($userId);
			
			$phDays = $objCalculation->fnGetPhs($userId,$curMonth,$curYear,$EmployeeInfo['rel_date_by_manager'],$EmployeeInfo['d_of_join']);
			if(count($phDays) > 0)
			{
				foreach($phDays as $ph)
				{
					$curWeekOffDate = date('Y-m-d',strtotime($ph["holidaydate"]));
					
					$arrPhAndWo[$curWeekOffDate] = $curWeekOffDate;
					
					/* Fetch if prev / next dates are leaves / public holidays */
					$prev_date = date('Y-m-d', strtotime('-1 day', strtotime($curWeekOffDate)));
					$next_date = date('Y-m-d', strtotime('+1 day', strtotime($curWeekOffDate)));

					/* Check if off marked */
					while($this->fnCheckOffMarked($userId, $prev_date))
					{
						/* of taking PH /WO, check for the date previous than that */
						$prev_date = date('Y-m-d', strtotime('-1 day', strtotime($prev_date)));
					}
					
					/* Check for leave status */
					$prevLeaveMarked = $this->fnCheckLeaveMarked($userId, $prev_date);
					
					/* Check if off marked */
					while($this->fnCheckOffMarked($userId, $next_date))
					{
						/* of taking PH /WO, check for the date previous than that */
						$next_date = date('Y-m-d', strtotime('+1 day', strtotime($next_date)));
					}
					
					/* Check for leave status */
					$nextLeaveMarked = $this->fnCheckLeaveMarked($userId, $next_date);
					
					if($prevLeaveMarked == true && $nextLeaveMarked == true)
					{
						/* Mark it as sandwich */
						$sSQL = "select date_format(a.date, '%Y-%m-%d') as attendance_date, date_format(a.date, '%m') as attendance_month, date_format(a.date, '%Y') as attendance_year, a.leave_id, a.in_time, a.out_time from pms_attendance a INNER JOIN pms_employee e  ON a.user_id = e.id where a.user_id='".mysql_real_escape_string($userId)."' and date_format(a.date,'%Y-%m-%d') = '".mysql_real_escape_string($curWeekOffDate)."' and ((e.status='0' and date_format(e.date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($curYear)."-".mysql_real_escape_string($curMonth)."-".mysql_real_escape_string($curDate)."') or (e.status='1' and date_format(e.relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($curYear)."-".mysql_real_escape_string($curMonth)."-".mysql_real_escape_string($curDate)."'))";
						$db->query($sSQL);
						if($db->num_rows() > 0)
						{
							if($db->next_record())
							{
								/* Check leave id for the prev date */
								$tempLeaveId = $db->f("leave_id");

								if(isset($arrAllLeaveDates[$prev_date]["leave_id"]))
									$tempLeaveId = $arrAllLeaveDates[$prev_date]["leave_id"];
								else
								{
									/* Fetch leave ID from attendance */
									$sSQL = "select a.leave_id from pms_attendance a INNER JOIN pms_employee e  ON a.user_id = e.id where a.user_id='".mysql_real_escape_string($userId)."' and date_format(a.date,'%Y-%m-%d') = '".mysql_real_escape_string($prev_date)."' and ((e.status='0' and date_format(e.date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($curYear)."-".mysql_real_escape_string($curMonth)."-".mysql_real_escape_string($curDate)."') or (e.status='1' and date_format(e.relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($curYear)."-".mysql_real_escape_string($curMonth)."-".mysql_real_escape_string($curDate)."'))";
									$mb->query($sSQL);
									if($mb->num_rows() > 0)
									{
										if($mb->next_record())
										{
											$tempLeaveId = $mb->f("leave_id");
										}
									}
								}

								$arrAllLeaveDates[$db->f("attendance_date")] = array("date" => $db->f("attendance_date"), "month" => $db->f("attendance_month"), "year" => $db->f("attendance_year"), "leave_id" => $tempLeaveId, "in_time" => $db->f("in_time"), "out_time" => $db->f("out_time"), "isWoPh" => 1);
								
								$arrSandwitchPhAndWo[$db->f("attendance_date")] = $db->f("attendance_date");
							}
						}
					}
				}
			}
			
			arsort($arrAllLeaveDates);

			$attendance_date = $curYear."-".$curMonth."-".$curDate;

			/* Fetch all the leaves and check */
			while(count($arrAllLeaveDates) > 0)
			{
				$curDateArr = array_pop($arrAllLeaveDates);
				
				$isContinuous = false;
				
				$curDate = $curDateArr["date"];

				/* Get opeaning leave balance for the given month and year */
				$monthYr = $curDateArr["month"]."-".$curDateArr["year"];

				if(in_array($curDateArr["leave_id"],array(1,2,3,6,7,17)))
				{
					if($curDateArr["isWoPh"] == 1)
					{
						$start_date = $curDateArr["date"];
						
						if(!in_array($start_date,$arrLwp))
						{					
							/* Update leave status in atendance */
							$arrInfo["user_id"] = $userId;
							$arrInfo["date"] = $start_date;
							$arrInfo["leave_id"] = $curDateArr["leave_id"];

							$pendingLeaves = $objCalculation->fnGetBalanceToMarkLeave($userId,$start_date);

							if($pendingLeaves >= 1)
							{
								$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."' and in_time='00:00:00' and out_time='00:00:00'";
								$mb->query($sSQL);
								if($mb->num_rows() > 0)
								{
									$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date_format(date,'%Y-%m-%d') = '".$arrInfo['date']."'";
									$mb->query($sSQL);
								}
							}
							else
							{
								if($pendingLeaves > 0)
								{
									/* Check if LWP awailable */
									$unpaid_leaves = $this->fnGetLeaveWithoutPayTillDate($userId, $start_date);
									if($unpaid_leaves < 3)
									{
										if((3-$unpaid_leaves) >= 1)
										{
											if($curDateArr["leave_id"] == 1)
												$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle('PLWP');
											else
												$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle('ULWP');

											$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."' and in_time='00:00:00' and out_time='00:00:00'";
											$mb->query($sSQL);
											if($mb->num_rows() > 0)
											{
												$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date_format(date,'%Y-%m-%d') = '".$arrInfo['date']."'";
												$mb->query($sSQL);
											}
										}
									}
									else
									{
										/* Mark absent */
										$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle('A');
										$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."' and in_time='00:00:00' and out_time='00:00:00'";
										$mb->query($sSQL);
										if($mb->num_rows() > 0)
										{
											$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date_format(date,'%Y-%m-%d') = '".$arrInfo['date']."'";
											$mb->query($sSQL);
										}
									}
								}
								else
								{
									/* Mark absent */
									$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle('A');
									$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."' and in_time='00:00:00' and out_time='00:00:00'";
									$mb->query($sSQL);
									if($mb->num_rows() > 0)
									{
										$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date_format(date,'%Y-%m-%d') = '".$arrInfo['date']."'";
										$mb->query($sSQL);
									}
								}
							}

						}
						if(isset($arrAllLeaveDates[$start_date]))
							unset($arrAllLeaveDates[$start_date]);
					}
					else
					{
						/* Check if leave is added and approved */
						$sSQL ="SELECT date_format(start_date,'%Y-%m-%d') as start_date , date_format(end_date,'%Y-%m-%d') as end_date, date_format(approved_date_manager, '%Y-%m-%d') as approved_date_manager, date_format(manager_delegate_date, '%Y-%m-%d') as manager_delegate_date, status_manager, manager_delegate_status, isemergency FROM pms_leave_form WHERE '".mysql_real_escape_string($curDate)."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d') and employee_id = '".mysql_real_escape_string($userId)."' and (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1')) order by id desc";
						$db->query($sSQL);
						if($db->num_rows() > 0)
						{
							if($db->next_record())
							{
								$isContinuous = false;

								$start_date = $db->f("start_date");
								$end_date = $db->f("end_date");
/*
								if($attendance_date > $start_date)
									$start_date = $attendance_date;
*/	

								while($start_date <= $end_date && in_array($curDateArr["leave_id"],array(1,2,3,6,7,17)))
								{
									if(in_array($start_date,$arrPhAndWo) && !in_array($start_date, $arrSandwitchPhAndWo))
									{
									}
									else
									{
										if(!in_array($start_date,$arrLwp))
										{

											//if(($curDateArr["leave_id"] == "3" && ($curDateArr["in_time"] == "00:00:00" || $curDateArr["in_time"] == "") && ($curDateArr["out_time"] == "00:00:00" || $curDateArr["out_time"] == "")) || $curDateArr["leave_id"] != "3" && in_array($start_date, array_keys($arrAllLeaveDates)))
											if(($curDateArr["leave_id"] == "3" && ($curDateArr["in_time"] == "00:00:00" || $curDateArr["in_time"] == "") && ($curDateArr["out_time"] == "00:00:00" || $curDateArr["out_time"] == "")) || $curDateArr["leave_id"] != "3")
											{
												$curLeaveStatus = 'UPL';

												if($db->f("isemergency") == "0")
												{
													if($db->f("status_manager") == '1' && ($db->f("manager_delegate_status") == '0' || $db->f("manager_delegate_status") == ''))
													{
														$next_monday_date = date('Y-m-d', strtotime('next monday',strtotime($db->f("approved_date_manager"))));

														if($start_date >= $next_monday_date)
															$curLeaveStatus = 'PPL';
													}
													else if($db->f("status_manager") == '0' && $db->f("manager_delegate_status") == '1')
													{
														$next_monday_date = date('Y-m-d', strtotime('next monday',strtotime($db->f("manager_delegate_date"))));

														if($start_date >= $next_monday_date)
															$curLeaveStatus = 'PPL';
													}
													else if($db->f("status_manager") == '1' && $db->f("manager_delegate_status") == '1')
													{
														if($db->f("approved_date_manager") < $db->f("manager_delegate_date"))
															$next_monday_date = date('Y-m-d', strtotime('next monday',strtotime($db->f("approved_date_manager"))));
														else
															$next_monday_date = date('Y-m-d', strtotime('next monday',strtotime($db->f("manager_delegate_date"))));

														if($start_date >= $next_monday_date)
															$curLeaveStatus = 'PPL';
													}
												}

												/* Update leave status in atendance */
												$arrInfo["user_id"] = $userId;
												$arrInfo["date"] = $start_date;
												$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle($curLeaveStatus);

												$pendingLeaves = $objCalculation->fnGetBalanceToMarkLeave($userId,$start_date);

												if($pendingLeaves >= 1)
												{
													$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."' and in_time='00:00:00' and out_time='00:00:00'";
													$mb->query($sSQL);
													if($mb->num_rows() > 0)
													{
														$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date_format(date,'%Y-%m-%d') = '".$arrInfo['date']."'";
														$mb->query($sSQL);
																			
														$isContinuous = true;
													}
												}
												else
												{
													if($pendingLeaves > 0 || ($isContinuous == true && $pendingLeaves == 0))
													{
														/* Check if LWP awailable */
														$unpaid_leaves = $this->fnGetLeaveWithoutPayTillDate($userId, $start_date);
														if($unpaid_leaves < 3)
														{
															if((3-$unpaid_leaves) >= 1)
															{
																if($curLeaveStatus == 'PPL')
																	$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle('PLWP');
																else
																	$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle('ULWP');

																$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."' and in_time='00:00:00' and out_time='00:00:00'";
																$mb->query($sSQL);
																if($mb->num_rows() > 0)
																{
																	$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date_format(date,'%Y-%m-%d') = '".$arrInfo['date']."'";
																	$mb->query($sSQL);
																}
															}
														}
														else
														{
															/* Mark absent */
															$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle('A');
															$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."' and in_time='00:00:00' and out_time='00:00:00'";
															$mb->query($sSQL);
															if($mb->num_rows() > 0)
															{
																$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date_format(date,'%Y-%m-%d') = '".$arrInfo['date']."'";
																$mb->query($sSQL);
															}
														}
													}
													else
													{
														/* Mark absent */
														$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle('A');
														$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."' and in_time='00:00:00' and out_time='00:00:00'";
														$mb->query($sSQL);
														if($mb->num_rows() > 0)
														{
															$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date_format(date,'%Y-%m-%d') = '".$arrInfo['date']."'";
															$mb->query($sSQL);
														}
													}
												}
											}
										}
									}

									if(isset($arrAllLeaveDates[$start_date]))
										unset($arrAllLeaveDates[$start_date]);
										
									$start_date = date ("Y-m-d", strtotime("+1 day", strtotime($start_date)));

									if(isset($arrAllLeaveDates[$start_date]))
										$curDateArr = $arrAllLeaveDates[$start_date];
								}
							}
						}
						else
						{
							if(!in_array($curDate,$arrLwp))
							{
								/* Mark absent */
								$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle('A');
								$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($userId)."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($curDate)."' and in_time='00:00:00' and out_time='00:00:00'";
								$mb->query($sSQL);
								if($mb->num_rows() > 0)
								{
									$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$userId."' and date_format(date,'%Y-%m-%d') = '".$curDate."'";
									$mb->query($sSQL);
								}
							}
							
							if(isset($arrAllLeaveDates[$curDate]))
								unset($arrAllLeaveDates[$curDate]);
								
							$start_date = date ("Y-m-d", strtotime("+1 day", strtotime($curDate)));

							if(isset($arrAllLeaveDates[$start_date]))
								$curDateArr = $arrAllLeaveDates[$start_date];
						}
					}
				}
				else if(in_array($curDateArr["leave_id"],array(4,5,8,12)))
				{
					/* Check for half leave */
					$sSQL = "SELECT date_format(start_date,'%Y-%m-%d') as start_date, date_format(approved_date_manager, '%Y-%m-%d') as approved_date_manager, date_format(manager_delegate_date, '%Y-%m-%d') as manager_delegate_date, status_manager, manager_delegate_status FROM pms_half_leave_form WHERE '".mysql_real_escape_string($curDate)."' = DATE_FORMAT(start_date,'%Y-%m-%d') and employee_id = '".mysql_real_escape_string($userId)."' and (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1')) order by id desc";
					$db->query($sSQL);
					if($db->num_rows() > 0)
					{
						if($db->next_record())
						{
							$start_date = $curDate;
							
							$curLeaveStatus = 'UHL';

							if($db->f("status_manager") == '1' && ($db->f("manager_delegate_status") == '0' || $db->f("manager_delegate_status") == ''))
							{
								$next_monday_date = date('Y-m-d', strtotime('next monday',strtotime($db->f("approved_date_manager"))));

								if($start_date >= $next_monday_date)
									$curLeaveStatus = 'PHL';
							}
							else if($db->f("status_manager") == '0' && $db->f("manager_delegate_status") == '1')
							{
								$next_monday_date = date('Y-m-d', strtotime('next monday',strtotime($db->f("manager_delegate_date"))));
								
								if($start_date >= $next_monday_date)
									$curLeaveStatus = 'PHL';
							}
							else if($db->f("status_manager") == '1' && $db->f("manager_delegate_status") == '1')
							{
								if($db->f("approved_date_manager") < $db->f("manager_delegate_date"))
									$next_monday_date = date('Y-m-d', strtotime('next monday',strtotime($db->f("approved_date_manager"))));
								else
									$next_monday_date = date('Y-m-d', strtotime('next monday',strtotime($db->f("manager_delegate_date"))));
								
								if($start_date >= $next_monday_date)
									$curLeaveStatus = 'PHL';
							}
							
							/* Update leave status in atendance */
							$arrInfo["user_id"] = $userId;
							$arrInfo["date"] = $start_date;
							$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle($curLeaveStatus);

							$pendingLeaves = $objCalculation->fnGetBalanceToMarkLeave($userId,$start_date);

							if($pendingLeaves >= 0.5)
							{
								$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."'";
								$mb->query($sSQL);
								if($mb->num_rows() > 0)
								{
									$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date_format(date,'%Y-%m-%d') = '".$arrInfo['date']."'";
									$mb->query($sSQL);
								}
							}
							else
							{
								if($pendingLeaves > 0)
								{
									/* Check if LWP awailable */
									$unpaid_leaves = $this->fnGetLeaveWithoutPayTillDate($userId, $start_date);
									if($unpaid_leaves < 3)
									{
										if((3-$unpaid_leaves) >= 0.5)
										{
											$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle('HLWP');

											$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."'";
											$mb->query($sSQL);
											if($mb->num_rows() > 0)
											{
												$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date_format(date,'%Y-%m-%d') = '".$arrInfo['date']."'";
												$mb->query($sSQL);
											}
										}
									}
									/*else
									{
										$arrUnpaidLeaves[] = array("dt"=>$arrInfo["date"], "leave_marked"=>'HLWP');
									}*/
								}
								else
								{
									/* Mark absent */
									$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle('HA');
									$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."'";
									$mb->query($sSQL);
									if($mb->num_rows() > 0)
									{
										$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date_format(date,'%Y-%m-%d') = '".$arrInfo['date']."'";
										$mb->query($sSQL);
									}
								}
							}
							
							if(isset($arrAllLeaveDates[$start_date]))
								unset($arrAllLeaveDates[$start_date]);
								
							$start_date = date ("Y-m-d", strtotime("+1 day", strtotime($start_date)));
						}
					}
					else
					{
						/* Mark absent */
						$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle('HA');
						$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($userId)."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($curDate)."'";
						$mb->query($sSQL);
						if($mb->num_rows() > 0)
						{
							$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$userId."' and date_format(date,'%Y-%m-%d') = '".$curDate."'";
							$mb->query($sSQL);
						}
						
						if(isset($arrAllLeaveDates[$curDate]))
							unset($arrAllLeaveDates[$curDate]);
							
						$start_date = date ("Y-m-d", strtotime("+1 day", strtotime($curDate)));

						if(isset($arrAllLeaveDates[$start_date]))
							$curDateArr = $arrAllLeaveDates[$start_date];
					}
				}
			}
		}
		
		function fnCheckLeaveMarked($userId, $attendanceDate)
		{
			$leaveMarked = false;
			/* Check if leaves taken */
			$sSQL = "select id from pms_attendance where user_id='".mysql_real_escape_string($userId)."' and date_format(date, '%Y-%m-%d') = '".mysql_real_escape_string($attendanceDate)."' and leave_id in (1,2,3,6,7) and in_time ='00:00:00' and out_time = '00:00:00'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
				$leaveMarked = true;

			return $leaveMarked;
		}
		
		function fnCheckOffMarked($userId, $attendanceDate)
		{
			$offMarked = false;
			
			/* Check if ph / wo marked */
			$sSQL = "select id from pms_attendance where user_id='".mysql_real_escape_string($userId)."' and date_format(date, '%Y-%m-%d') = '".mysql_real_escape_string($attendanceDate)."' and leave_id in (9,10) and in_time ='00:00:00' and out_time = '00:00:00'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
				$offMarked = true;
			
			return $offMarked;
		}
		
		function fnGetLeavesTakenInMonth($userId, $startDate, $tillDate)
		{
			$totalLeaves = $leavesTaken = $halfLeavesTaken = 0;

			$sSQL = "select count(id) as leave_taken from pms_attendance where date_format(date, '%Y-%m-%d') between '".mysql_real_escape_string($startDate)."' and '".mysql_real_escape_string($tillDate)."' and leave_id in (1,2) and user_id='".mysql_real_escape_string($userId)."'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				if($this->next_record())
				{
					$leavesTaken = $this->f("leave_taken");
				}
			}

			$sSQL = "select count(id) as half_leave_taken from pms_attendance where date_format(date, '%Y-%m-%d') between '".mysql_real_escape_string($startDate)."' and '".mysql_real_escape_string($tillDate)."' and leave_id in (4,5) and user_id='".mysql_real_escape_string($userId)."'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				if($this->next_record())
				{
					$halfLeavesTaken = $this->f("half_leave_taken");
				}
			}

			$totalLeaves = $leavesTaken + ($halfLeavesTaken * 0.5);

			return $totalLeaves;
		}

		function fnUpdateLeavesWhenLeavesEarned($userId, $curDate, $curMonth, $curYear)
		{
			$mydb = new DB_Sql();
			$db = new DB_Sql();
			$mb = new DB_Sql();
			$mb1 = new DB_Sql();
			
			include_once("class.calculation.php");
			include_once("class.leave.php");
			
			$objCalculation = new calculation();
			$objLeave = new leave();
			
			$arrUnpaidLeaves = array();
			
			$monthYr = $curYear."-".$curMonth;
			
			/* Fetch the current available leave balance */
			//echo "<br/>UserId: ".$userId;
			$pendingLeaves = $this->fnGetLastLeaveBalance($userId,$monthYr);
			//echo "<br/>pendingLeaves: ".$pendingLeaves;

			/* Check if half monthly leave added for currentl month and year */
			$HalfMonthlyLeavesEarned = $objCalculation->fnHalfMonthlyLeaveEarned($userId,$curMonth,$curYear);
			$HalfMonthlyLeavesEarnedDate = $objCalculation->fnHalfMonthlyLeaveEarnedDate($userId,$curMonth,$curYear);
			
			/* Check half monthly earned */
			$MonthlyLeavesEarned = $objCalculation->fnGetMonthlyLeaveEarned($userId,$curMonth,$curYear);
			
			$cudDate = Date('Y-m-d');
			
			/* If the user has leave balance then check for the leaves that are not paid */
			//if($pendingLeaves > 0)
			//{
				/* Fetch all the leaves that are not paid */
				$sSQL = "select date_format(a.date, '%Y-%m-%d') as attendance_date, a.leave_id from pms_attendance a INNER JOIN pms_employee e  ON a.user_id = e.id where a.leave_id in (3,6,7,8,12) and a.user_id='".mysql_real_escape_string($userId)."' and date_format(a.date,'%Y-%m-%d') >= '".mysql_real_escape_string($curYear)."-".mysql_real_escape_string($curMonth)."-01' and ((e.status='0' and date_format(e.date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($curYear)."-".mysql_real_escape_string($curMonth)."-".mysql_real_escape_string($curDate)."') or (e.status='1' and date_format(e.relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($curYear)."-".mysql_real_escape_string($curMonth)."-".mysql_real_escape_string($curDate)."')) order by a.date";

				$mydb->query($sSQL);
				if($mydb->num_rows() > 0)
				{
					while($mydb->next_record())
					{
						$attendance_date = $mydb->f("attendance_date");
						
						/* Check if leave is added and approved */
						$sSQL ="SELECT date_format(start_date,'%Y-%m-%d') as start_date , date_format(end_date,'%Y-%m-%d') as end_date, date_format(approved_date_manager, '%Y-%m-%d') as approved_date_manager, date_format(manager_delegate_date, '%Y-%m-%d') as manager_delegate_date, status_manager, manager_delegate_status, isemergency FROM pms_leave_form WHERE '".mysql_real_escape_string($attendance_date)."' BETWEEN DATE_FORMAT(start_date,'%Y-%m-%d') AND DATE_FORMAT(end_date,'%Y-%m-%d') and employee_id = '".mysql_real_escape_string($userId)."' and (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1'))";
						$db->query($sSQL);
						if($db->num_rows() > 0)
						{
							if($db->next_record())
							{
								$start_date = $db->f("start_date");
								$end_date = $db->f("end_date");

//echo "<br/>start_date: ".$start_date;
//echo "<br/>end_date: ".$end_date;
								
								if($attendance_date > $start_date)
									$start_date = $attendance_date;

//echo "<br/>start_date: ".$start_date;
//echo "<br/>end_date: ".$end_date;

								while($start_date <= $end_date)
								{
									/* Adjusting prev dates do not consider half month leave earned */
									
									/* Fetch leave bal */
									$pendingLeaves = $this->fnGetLastLeaveBalance($userId,$monthYr);
									//echo "<br/>================pendingLeaves: ".$pendingLeaves;
									//echo "<br/>HalfMonthlyLeavesEarnedDate: ".$HalfMonthlyLeavesEarnedDate;
									
									if($start_date < $cudDate && $HalfMonthlyLeavesEarnedDate != "" && $start_date < $HalfMonthlyLeavesEarnedDate)
									{
										/* Deduct halfmonthly leaves earned */
										$pendingLeaves = $pendingLeaves - $HalfMonthlyLeavesEarned;
										//echo "<br/>---------------------pendingLeaves: ".$pendingLeaves;
									}
									
									/* Check if monthly leave earned, for that month remove that as well from leave balance */
									$pendingLeaves = $pendingLeaves - $MonthlyLeavesEarned;
									
									$curLeaveStatus = 'UPL';
									/* Check if planned / unplanned */
									/*echo "<br/>".$sSQL = "select attendance from pms_roster r INNER JOIN pms_roster_detail rd ON r.id = rd.rosterid where date_format(rostereddate,'%Y-%m-%d') = '".mysql_real_escape_string($start_date)."' and r.userid='".mysql_real_escape_string($userId)."' and attendance='PPL'";
									$mb->query($sSQL);
									if($mb->num_rows() > 0)
										$curLeaveStatus = 'PPL';*/
									if($db->f("isemergency") == "0")
									{
										if($db->f("status_manager") == '1' && ($db->f("manager_delegate_status") == '0' || $db->f("manager_delegate_status") == ''))
										{
											$next_monday_date = date('Y-m-d', strtotime('next monday',strtotime($db->f("approved_date_manager"))));
											
											if($start_date >= $next_monday_date)
												$curLeaveStatus = 'PPL';
										}
										else if($db->f("status_manager") == '0' && $db->f("manager_delegate_status") == '1')
										{
											$next_monday_date = date('Y-m-d', strtotime('next monday',strtotime($db->f("manager_delegate_date"))));
											
											if($start_date >= $next_monday_date)
												$curLeaveStatus = 'PPL';
										}
										else if($db->f("status_manager") == '1' && $db->f("manager_delegate_status") == '1')
										{
											if($db->f("approved_date_manager") < $db->f("manager_delegate_date"))
												$next_monday_date = date('Y-m-d', strtotime('next monday',strtotime($db->f("approved_date_manager"))));
											else
												$next_monday_date = date('Y-m-d', strtotime('next monday',strtotime($db->f("manager_delegate_date"))));
											
											if($start_date >= $next_monday_date)
												$curLeaveStatus = 'PPL';
										}
									}
									
									/* Update leave status in atendance */
									$arrInfo["user_id"] = $userId;
									$arrInfo["date"] = $start_date;
									$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle($curLeaveStatus);
									
									if($pendingLeaves >= 1)
									{
										$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."'";
										$mb1->query($sSQL);
										if($mb1->num_rows() > 0)
										{
											$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date = '".$arrInfo['date']."'";
											$mb1->query($sSQL);
										}
									}
									else
									{
										//if($pendingLeaves > 0)
										//{
											/* Check if LWP awailable */
											$unpaid_leaves = $this->fnGetUserLeavesWithoutPayByMonthAndYear($userId, $curMonth, $curYear);
											if($unpaid_leaves < 3)
											{
												if((3-$unpaid_leaves) >= 1)
												{
													if($curLeaveStatus == 'PPL')
														$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle('PLWP');
													else
														$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle('ULWP');

													$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."'";
													$mb1->query($sSQL);
													if($mb1->num_rows() > 0)
													{
														$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date = '".$arrInfo['date']."'";
														$mb1->query($sSQL);
													}
												}
											}
											else
											{
												/* Record data as can be marked for LWP */
												if($curLeaveStatus == 'PPL')
													$arrUnpaidLeaves[] = array("dt"=>$arrInfo["date"], "leave_marked"=>'PLWP');
												else
													$arrUnpaidLeaves[] = array("dt"=>$arrInfo["date"], "leave_marked"=>'ULWP');
											}
										//}
									}
									
									$start_date = date ("Y-m-d", strtotime("+1 day", strtotime($start_date)));
								}
							}
						}
					
						/* Check for half leave */
						$sSQL = "SELECT date_format(start_date,'%Y-%m-%d') as start_date, date_format(approved_date_manager, '%Y-%m-%d') as approved_date_manager, date_format(manager_delegate_date, '%Y-%m-%d') as manager_delegate_date, status_manager, manager_delegate_status FROM pms_half_leave_form WHERE '".mysql_real_escape_string($attendance_date)."' = DATE_FORMAT(start_date,'%Y-%m-%d') and employee_id = '".mysql_real_escape_string($userId)."' and (status_manager ='1' or (status_manager ='0' and deligateManagerId!='0' and manager_delegate_status ='1'))";
						$db->query($sSQL);
						if($db->num_rows() > 0)
						{
							if($db->next_record())
							{
								$start_date = $attendance_date;

								/* Fetch leave bal */
								$pendingLeaves = $this->fnGetLastLeaveBalance($userId,$monthYr);
								//echo "<br/>pendingLeaves: ".$pendingLeaves;

								if($start_date < $cudDate && $HalfMonthlyLeavesEarnedDate != "" && $start_date < $HalfMonthlyLeavesEarnedDate)
								{
									/* Deduct halfmonthly leaves earned */
									$pendingLeaves = $pendingLeaves - $HalfMonthlyLeavesEarned;
									//echo "<br/>pendingLeaves: ".$pendingLeaves;
								}

								/* Check if monthly leave earned, for that month remove that as well from leave balance */
								$pendingLeaves = $pendingLeaves - $MonthlyLeavesEarned;

								$curLeaveStatus = 'UHL';

								if($db->f("status_manager") == '1' && ($db->f("manager_delegate_status") == '0' || $db->f("manager_delegate_status") == ''))
								{
									$next_monday_date = date('Y-m-d', strtotime('next monday',strtotime($db->f("approved_date_manager"))));

									if($start_date >= $next_monday_date)
										$curLeaveStatus = 'PHL';
								}
								else if($db->f("status_manager") == '0' && $db->f("manager_delegate_status") == '1')
								{
									$next_monday_date = date('Y-m-d', strtotime('next monday',strtotime($db->f("manager_delegate_date"))));
									
									if($start_date >= $next_monday_date)
										$curLeaveStatus = 'PHL';
								}
								else if($db->f("status_manager") == '1' && $db->f("manager_delegate_status") == '1')
								{
									if($db->f("approved_date_manager") < $db->f("manager_delegate_date"))
										$next_monday_date = date('Y-m-d', strtotime('next monday',strtotime($db->f("approved_date_manager"))));
									else
										$next_monday_date = date('Y-m-d', strtotime('next monday',strtotime($db->f("manager_delegate_date"))));
									
									if($start_date >= $next_monday_date)
										$curLeaveStatus = 'PHL';
								}

								/* Update leave status in atendance */
								$arrInfo["user_id"] = $userId;
								$arrInfo["date"] = $start_date;
								$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle($curLeaveStatus);

								if($pendingLeaves >= 0.5)
								{
									$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."'";
									$mb1->query($sSQL);
									if($mb1->num_rows() > 0)
									{
										$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date = '".$arrInfo['date']."'";
										$mb1->query($sSQL);
									}
								}
								else
								{
									if($pendingLeaves > 0)
									{
										/* Check if LWP awailable */
										$unpaid_leaves = $this->fnGetUserLeavesWithoutPayByMonthAndYear($userId, $curMonth, $curYear);
										if($unpaid_leaves < 3)
										{
											if((3-$unpaid_leaves) >= 0.5)
											{
												$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle('HLWP');

												$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."'";
												$mb1->query($sSQL);
												if($mb1->num_rows() > 0)
												{
													$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date = '".$arrInfo['date']."'";
													$mb1->query($sSQL);
												}
											}
										}
										else
										{
											$arrUnpaidLeaves[] = array("dt"=>$arrInfo["date"], "leave_marked"=>'HLWP');
										}
									}
								}
							}
						}
					}
					
					/* Check if any LWP awailable */
					if(count($arrUnpaidLeaves) > 0)
					{
						foreach($arrUnpaidLeaves as $curUnpaidLeave)
						{
							/* Check if LWP awailable */
							if($curUnpaidLeave["leave_marked"] == "PLWP" || $curUnpaidLeave["leave_marked"] == "ULWP")
							{
								$unpaid_leaves = $this->fnGetUserLeavesWithoutPayByMonthAndYear($userId, $curMonth, $curYear);
								if($unpaid_leaves < 3)
								{
									if((3-$unpaid_leaves) >= 1)
									{
										$arrInfo["date"] = $curUnpaidLeave["dt"];
										$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle($curUnpaidLeave["leave_marked"]);
										$arrInfo["user_id"] = $userId;

										$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."'";
										$mb1->query($sSQL);
										if($mb1->num_rows() > 0)
										{
											$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date = '".$arrInfo['date']."'";
											$mb1->query($sSQL);
										}
									}
								}
							}
							else if ($curUnpaidLeave["leave_marked"] == "HLWP")
							{
								$unpaid_leaves = $this->fnGetUserLeavesWithoutPayByMonthAndYear($userId, $curMonth, $curYear);
								if($unpaid_leaves < 3)
								{
									if((3-$unpaid_leaves) >= 0.5)
									{
										$arrInfo["date"] = $curUnpaidLeave["dt"];
										$arrInfo["leave_id"] = $objLeave->fnGetLeaveTypeIdByTitle($curUnpaidLeave["leave_marked"]);
										$arrInfo["user_id"] = $userId;
										
										$sSQL = "select * from pms_attendance where user_id='".mysql_real_escape_string($arrInfo["user_id"])."' and date_format(date,'%Y-%m-%d') = '".mysql_real_escape_string($arrInfo["date"])."'";
										$mb1->query($sSQL);
										if($mb1->num_rows() > 0)
										{
											$sSQL = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."' where user_id='".$arrInfo['user_id']."' and date = '".$arrInfo['date']."'";
											$mb1->query($sSQL);
										}
									}
								}
							}
						}
					}
				}
			//}

		}

		/* Send mail if absent for 3 days */
		function fnSendAbsentMail($user)
		{
			//$arrDt = explode("-",$date);

			include_once("class.employee.php");
			include_once("class.designation.php");
			
			$objEmployee = new employee();
			$objDesignation = new designations();

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
						$checknextpresent = $this->fnCheckIfNextPresent($user, $lastDt);

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

								$employeeInfo = $objEmployee->fnGetEmployeeDetailById($user);

								if(count($employeeInfo) > 0)
								{
									/* Fetch details for the user designation */
									$arrDesignationInfo = $objDesignation->fnGetDesignationById($employeeInfo["designation"]);

									/* Fetch reporting head hierarchy */
									$arrHeads = $objEmployee->fnGetReportHeadHierarchy($user);

									if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && isset($arrDesignationInfo["second_reporting_head"]) && trim($arrDesignationInfo["second_reporting_head"]) != "" && trim($arrDesignationInfo["second_reporting_head"]) != "0")
									{
										if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
										{
											$AttritionInfo["tlid"] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
										}
										
										if(isset($arrHeads[$arrDesignationInfo["second_reporting_head"]]))
										{
											$AttritionInfo["managerid"] = $arrHeads[$arrDesignationInfo["second_reporting_head"]]["id"];
										}
									}
									else if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && trim($arrDesignationInfo["second_reporting_head"]) == "0")
									{
										if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
										{
											$AttritionInfo["managerid"] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
											$AttritionInfo["tlid"] = 0;
										}
									}
								}
								
								$this->insertArray("pms_attrition_process",$AttritionInfo);

								$tempContent = "Kindly be informed that <b>" . $employeeInfo["name"]."</b> is <b>ABSENT</b> since last 3 consecutive days. As per the process, HR would be sending the show cause notice/terminate to him/her.<br/><br/>";
								$tempContentFooter = "<br><br>HR would send the Show Cause Notice if we do not hear from you by EOD today.<br><br>Regards,<br>".SITEADMINISTRATOR;

								$Subject = "Attrition process";

								if(isset($AttritionInfo["tlid"]) && trim($AttritionInfo["tlid"]) != "" && trim($AttritionInfo["tlid"]) != "0")
								{
									$TeamLeaderInfo = $objEmployee->fnGetEmployeeDetailById($AttritionInfo["tlid"]);

									if(count($TeamLeaderInfo) > 0)
									{
										$content = "Dear ".$TeamLeaderInfo["name"].",<br><br>".$tempContent;

										$content .= "Please click either <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["tlapprovalcode"]."_Terminate_AP]'>(Send Notice/terminate)</a> or <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["tlapprovalcode"]."_Hold_AP]'>HOLD</a> to confirm your action. ".$tempContentFooter;

										sendmail($TeamLeaderInfo["email"], $Subject, $content);
									}
								}

								if(isset($AttritionInfo["managerid"]) && trim($AttritionInfo["managerid"]) != "" && trim($AttritionInfo["managerid"]) != "0")
								{
									$arrManager = $objEmployee->fnGetEmployeeDetailById($AttritionInfo["managerid"]);

									$content = "Dear ".$arrManager["name"].",<br><br>".$tempContent;
									$content .= "Please click either <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["managerapprovalcode"]."_Terminate_AP]'>(Send Notice/terminate)</a> or <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["managerapprovalcode"]."_Hold_AP]'>HOLD</a> to confirm your action.".$tempContentFooter;

									sendmail($arrManager["email"], $Subject, $content);
								}

								/* Send mail to HR */
								$MailTo = "hr@transformsolution.net";

								$content = "Dear HR,<br><br>".$tempContent;
								$content .= "Please click either <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["hrapprovalcode"]."_Terminate_AP]'>(Send Notice/terminate)</a> or <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["hrapprovalcode"]."_Hold_AP]'>HOLD</a> to confirm your action.".$tempContentFooter;

								sendmail($MailTo, $Subject, $content);

								/* Send mail to Admin */
								$MailTo = "admin@transformsolution.net";

								$content = "Dear Admin,<br><br>".$tempContent;
								$content .= "Please click either <a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["adminapprovalcode"]."_Terminate_AP]'>(Send Notice/terminate)</a>&nbsp;&nbsp;or&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$AttritionInfo["adminapprovalcode"]."_Hold_AP]'>HOLD</a> to confirm your action.".$tempContentFooter;

								sendmail($MailTo, $Subject, $content);
							}
						}
					}
				}
			}
		}

		function fnCheckIfNextPresent($userId, $attendanceDate)
		{
			$checknextpresent = 0;
			$sSQL = "select *, date_format(date,'%Y-%m-%d') as attendance_dt from pms_attendance where user_id='".mysql_real_escape_string($userId)."' and date_format(date,'%Y-%m-%d') =  date_format(DATE_ADD('".$attendanceDate."',INTERVAL 1 DAY),'%Y-%m-%d') and ((leave_id='0' and in_time='00:00:00') or leave_id in (9,10))";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					if($this->f("leave_id") == '9' || $this->f("leave_id") == '10')
					{
						$checknextpresent = $this->fnCheckIfNextPresent($userId, $this->f("attendance_dt"));
					}
					else
					{
						$checknextpresent = 1;
					}
				}
			}
			return $checknextpresent;
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

		function fnGetEmployees($curdate = '')
		{
			$arrEmployeeValues = array();
			
			/* Fetch all the designations that act as parent designations */
			include_once("class.designation.php");
			$objDesignation = new designations();
			
			$arrDesignation = $objDesignation->fnGetAllParentDesignations();
			$arrDesignation[] = 0;
			
			$desIds = implode(",",$arrDesignation);
			
			$cond = '';
			if(trim($curdate) != '')
				$cond = " and ((date_format(relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($curdate)."' and status='1')  or (status='0' and date_format(date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($curdate)."'))";

			$query = "SELECT id as employee_id ,name as employee_name FROM `pms_employee` WHERE `designation` IN($desIds) $cond";
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
			/* THIS FUNCTION IS CURRENTLY NOT USED ANY WHERE */
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
				/*$query = "SELECT attendance.*,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in,DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out,DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in,DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id, time_format(d.fullday_minimum_working_hour,'%H:%i') as fullday_minimum_working_hour, time_format(d.fullday_break_minutes,'%H:%i') as fullday_break_minutes, time_format(d.halfday_minimum_working_hour,'%H:%i') as halfday_minimum_working_hour, time_format(d.halfday_break_minutes,'%H:%i') as halfday_break_minutes, time_format(d.sm_minimum_working_hour,'%H:%i') as sm_minimum_working_hour, time_format(d.sm_break_minutes,'%H:%i') as sm_break_minutes FROM `pms_employee` AS employee INNER JOIN pms_designation d ON employee.designation = d.id LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation NOT IN(6,8,17,18,19,44) and (employee.status='0' or date_format(relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($date)."') order by employee.name";*/
				
				$query = "SELECT attendance.*,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in,DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out,DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in,DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id, time_format(d.fullday_minimum_working_hour,'%H:%i') as fullday_minimum_working_hour, time_format(d.fullday_break_minutes,'%H:%i') as fullday_break_minutes, time_format(d.halfday_minimum_working_hour,'%H:%i') as halfday_minimum_working_hour, time_format(d.halfday_break_minutes,'%H:%i') as halfday_break_minutes, time_format(d.sm_minimum_working_hour,'%H:%i') as sm_minimum_working_hour, time_format(d.sm_break_minutes,'%H:%i') as sm_break_minutes, d.consider_break_exceed, d.consider_late_commings, d.consider_inout_time, employee.shiftid as emp_shift, attendance.shift_id as shiftid FROM `pms_employee` AS employee INNER JOIN pms_designation d ON employee.designation = d.id LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE ((employee.status='0' and date_format(date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($date)."') or (employee.status='1' and date_format(relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($date)."')) order by employee.name";
			}
			else
			{
				/*$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in,DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out,DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in,DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date')  WHERE (employee.`teamleader` = '$id' OR employee.id='$id') AND employee.designation NOT IN(6,8,17,18,19,44) and ((employee.status='0' and date_format(date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($date)."') or (employee.status='1' and date_format(relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($date)."')) order by employee.name";*/
				/*$query = "SELECT attendance.*,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in,DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out,DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in,DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id, time_format(d.fullday_minimum_working_hour,'%H:%i') as fullday_minimum_working_hour, time_format(d.fullday_break_minutes,'%H:%i') as fullday_break_minutes, time_format(d.halfday_minimum_working_hour,'%H:%i') as halfday_minimum_working_hour, time_format(d.halfday_break_minutes,'%H:%i') as halfday_break_minutes, time_format(d.sm_minimum_working_hour,'%H:%i') as sm_minimum_working_hour, time_format(d.sm_break_minutes,'%H:%i') as sm_break_minutes, d.consider_break_exceed, d.consider_late_commings, d.consider_inout_time FROM `pms_employee` AS employee INNER JOIN pms_designation d ON employee.designation = d.id LEFT JOIN  `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date')  WHERE (employee.`teamleader` = '$id' OR employee.id='$id') AND ((employee.status='0' and date_format(date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($date)."') or (employee.status='1' and date_format(relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($date)."')) order by employee.name";*/
				$query = "SELECT attendance.*,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in,DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out,DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in,DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id, time_format(d.fullday_minimum_working_hour,'%H:%i') as fullday_minimum_working_hour, time_format(d.fullday_break_minutes,'%H:%i') as fullday_break_minutes, time_format(d.halfday_minimum_working_hour,'%H:%i') as halfday_minimum_working_hour, time_format(d.halfday_break_minutes,'%H:%i') as halfday_break_minutes, time_format(d.sm_minimum_working_hour,'%H:%i') as sm_minimum_working_hour, time_format(d.sm_break_minutes,'%H:%i') as sm_break_minutes, d.consider_break_exceed, d.consider_late_commings, d.consider_inout_time, employee.shiftid as emp_shift, attendance.shift_id as shiftid FROM `pms_employee` AS employee INNER JOIN pms_designation d ON employee.designation = d.id LEFT JOIN  `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date')  WHERE (employee.`teamleader` = '$id') AND ((employee.status='0' and date_format(date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($date)."') or (employee.status='1' and date_format(relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($date)."')) order by employee.name";
			}
			$db->query($query);
			if($db->num_rows() > 0)
			{
				while($db->next_record())
				{
					$tmprow = $db->fetchrow();

					//$tmprow["shiftid"] = $tmprow["shift_id"];

					if($tmprow["leave_id"] == "13")
					{
						$shiftid = $objRoster->fnGetRosteredShiftByUserAndDate($db->f("employee_id"), $date);

						$tmprow["shift_id"] = $shiftid;
					}

					if(isset($tmprow["shift_id"]) && (trim($tmprow["shift_id"]) == "" || trim($tmprow["shift_id"]) == "0"))
					{
						$tmprow["shift_id"] = $tmprow["emp_shift"];
					}
					else if(!isset($tmprow["shift_id"]))
						$tmprow["shift_id"] = $tmprow["emp_shift"];
				
					$arrEmployeeValues[$db->f("employee_id")] = $tmprow;
					/*if($id != $db->f("employee_id"))
					{
						$tmpData = $this->fnGetEmployeeDetails1($db->f("employee_id"),$date);
						//$arrEmployeeValues = array_merge($arrEmployeeValues,$tmpData);
						$arrEmployeeValues = $arrEmployeeValues + $tmpData;
						//print_r($arrEmployeeValues);
					}*/
				}
			}
			return $arrEmployeeValues;
		}

		function fnGetEmployeeDetails2($id,$date)
		{
			$db = new DB_Sql();
			$arrEmployeeValues = array();

			$time=strtotime($date);
			$month=date("Y-m",$time);

			include_once('includes/class.roster.php');
			$objRoster = new roster();

			//$query = "SELECT id as employee_id ,name as employee_name FROM `pms_employee`  WHERE (`teamleader` = '$id' OR id='$id') AND designation NOT IN(6,8,17)";
			if($id == '')
			{
				$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in,DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out,DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in,DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id,late_time as late_time,break_exceed_time as break_exceed_time FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') where ((employee.status='0' and date_format(date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($date)."') or (employee.status='1' and date_format(relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($date)."')) order by employee.name";
			}
			else
			{
				$query = "SELECT *,DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time,DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time,DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in,DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out,DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in,DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out,DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in,DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out,DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in,DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out,DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in,DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out,employee.id as employee_id ,employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN  `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date')  WHERE (employee.`teamleader` = '$id' OR employee.id='$id') and ((employee.status='0' and date_format(date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($date)."') or (employee.status='1' and date_format(relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($date)."')) order by employee.name ASC";
			}
			//echo $query;
			//die;
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
			$arrShiftIdDetails = array('starttime'=>'', 'endtime'=>'');
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
			
			$sSQL = "SELECT  a.id as aid, e.id as eid, e.name, l.title, l.color as colorcode, date_format(a.`date`,'%Y-%m-%d') as startdate FROM pms_attendance a INNER JOIN pms_employee e ON a.user_id = e.id INNER JOIN pms_leave_type l ON a.leave_id = l.id where date_format(a.`date`,'%Y-%m-%d') BETWEEN '$start' AND '$end' AND e.id IN($ids) and (a.leave_id NOT IN (0,9,10) OR ( a.leave_id = 9 AND date_format(a.`date`,'%w') != 0) OR (a.leave_id = 10 AND date_format(a.`date`,'%Y-%m-%d') NOT IN (select date_format(holidaydate,'%Y-%m-%d') from pms_holidays))) and ((e.status='0' and date_format(date_of_joining,'%Y-%m-%d') <= date_format(a.`date`,'%Y-%m-%d')) or (date_format(relieving_date_by_manager,'%Y-%m-%d') >= date_format(a.`date`,'%Y-%m-%d') and e.status='1'))";

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
			$sSQL = "SELECT a.id as aid, e.name, date_format(a.`date`,'%Y-%m-%d') as startdate, a.is_late, a.isExceededBreak, time_format(a.late_time,'%H:%i') as late_time, time_format(a.break_exceed_time,'%H:%i') as break_exceed_time FROM pms_attendance a INNER JOIN pms_employee e ON a.user_id = e.id  where date_format(a.`date`,'%Y-%m-%d') BETWEEN '$start' AND '$end' AND e.id  = '$ids' and (a.is_late='1' || a.isExceededBreak='1') and (e.status='0' or date_format(relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($end)."')";

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

			if(isset($arrInfo["leave_id"]))
				$defaultLeaveType = $originalLeaveType = $arrInfo["leave_id"];
			else
				$defaultLeaveType = $originalLeaveType = "";

			$originalPendingLeaves = $this->fnGetLastLeaveBalance($arrInfo["user_id"]);

			if(isset($arrInfo["leave_id"]) && $arrInfo["leave_id"] != "" && $arrInfo["leave_id"] != "9" && $arrInfo["leave_id"] != "10" && $arrInfo["leave_id"] != "14")
			{

				if(isset($arrInfo['end_dt']) && $arrInfo['end_dt'] != '')
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
								$query = "update pms_attendance set leave_id = '".$arrInfo['leave_id']."', last_modified = '".Date('Y-m-d H:i:s')."', in_time = '00:00:00', out_time = '00:00:00', break1_in = '00:00:00', break1_out = '00:00:00', break2_in = '00:00:00', break2_out = '00:00:00', break3_in = '00:00:00', break3_out = '00:00:00', break4_in = '00:00:00', break4_out = '00:00:00', break5_in = '00:00:00', break5_out = '00:00:00' where user_id='".$arrInfo['user_id']."' and date = '".$arrInfo['date']."'";
								$run = mysql_query($query);
								//$this->updateArray('pms_attendance',$arrInfo);
							}
						}
						else
						{
							$arrInfo["last_modified"]=Date('Y-m-d H:i:s');
							$arrInfo["in_time"]='00:00:00';
							$arrInfo["out_time"]='00:00:00';
							$arrInfo["break1_in"]='00:00:00';
							$arrInfo["break1_out"]='00:00:00';
							$arrInfo["break2_in"]='00:00:00';
							$arrInfo["break2_out"]='00:00:00';
							$arrInfo["break3_in"]='00:00:00';
							$arrInfo["break3_out"]='00:00:00';
							$arrInfo["break4_in"]='00:00:00';
							$arrInfo["break4_out"]='00:00:00';
							$arrInfo["break5_in"]='00:00:00';
							$arrInfo["break5_out"]='00:00:00';
							
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
				if(isset($arrInfo['end_dt']) && $arrInfo['end_dt'] != '')
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
		
		function fnGetLeaveWithoutPayTillDate($userId, $attendanceDate)
		{
			$total_leaves_withoutpay = $total_unpaid_leaves = $total_unpaid_halfday_leaves = 0;

			$yearMonth = Date('Y-m');

			/* Fetch the month and year from when the leave balance is used */
			$sSQL = "select opening_leave_balance_month, opening_leave_balance_year from pms_employee where id='".mysql_real_escape_string($userId)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$yearMonth = $this->f("opening_leave_balance_year")."-".$this->f("opening_leave_balance_month");
				}
			}

			$startDate = $yearMonth.'-01';
			$endDate = date("Y-m-d",strtotime("-1 day", strtotime($attendanceDate)));

			/* Fetch LWP taken */
			$sSQL = "select * from pms_attendance where date_format(date,'%Y-%m-%d') between '".mysql_real_escape_string($startDate)."' and '".mysql_real_escape_string($endDate)."' and user_id='".mysql_real_escape_string($userId)."' and leave_id in (6,7) and date_format(date,'%Y-%m-%d') not in (select date_format(lwp_date,'%Y-%m-%d') from pms_approved_lwp where user_id='".mysql_real_escape_string($userId)."' and date_format(lwp_date,'%Y-%m-%d') >= '".mysql_real_escape_string($startDate)."')";
			$this->query($sSQL);
			$total_unpaid_leaves = $this->num_rows();

			/* Fetch HLWP taken */
			$sSQL = "select * from pms_attendance where date_format(date,'%Y-%m-%d') between '".mysql_real_escape_string($startDate)."' and '".mysql_real_escape_string($endDate)."' and user_id='".mysql_real_escape_string($userId)."' and leave_id = '8'";
			$this->query($sSQL);
			$total_unpaid_halfday_leaves = $this->num_rows();

			$total_leaves_withoutpay = $total_unpaid_leaves + ($total_unpaid_halfday_leaves * 0.5);

			return $total_leaves_withoutpay;
		}

		function fnGetUserLeavesWithoutPayByMonthAndYear($userid, $month, $year)
		{
			$total_leaves_withoutpay = $total_unpaid_leaves = $total_unpaid_halfday_leaves = 0;

			$FirstDayOfMonth = $year."-".$month."-01";

			//$sSQL = "select * from pms_attendance where date_format(date,'%Y') = '".mysql_real_escape_string($year)."' and date_format(date,'%m') = '".mysql_real_escape_string($month)."' and user_id='".mysql_real_escape_string($userid)."' and leave_id in (6,7)";
			$sSQL = "select * from pms_attendance where date_format(date,'%Y-%m-%d') >= '".mysql_real_escape_string($FirstDayOfMonth)."' and user_id='".mysql_real_escape_string($userid)."' and leave_id in (6,7) and date_format(date,'%Y-%m-%d') not in (select date_format(lwp_date,'%Y-%m-%d') from pms_approved_lwp where user_id='".mysql_real_escape_string($userid)."' and date_format(lwp_date,'%Y-%m-%d') >= '".mysql_real_escape_string($FirstDayOfMonth)."')";
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

			/*$sSQL = "select a.id as attendanceid, e.id as employeeid, e.name, time_format(official_total_working_hours,'%H:%i') as workhours, ishoursapproved, time_format(additionaltime,'%H:%i') as additionaltime, is_late, time_format(late_time,'%H:%i') as late_time, isExceededBreak, time_format(break_exceed_time,'%H:%i') as break_exceed_time from pms_attendance a INNER JOIN pms_employee e ON e.id = a.user_id where ((((a.official_total_working_hours >= '07:10:00' and a.leave_id='0' and isExceededBreak = '1') or (a.official_total_working_hours between '05:10:00' and '05:19:00' and a.leave_id='14' and isExceededBreak = '1') or (a.break_exceed_time <= '00:10' and isExceededBreak = '1'  and a.leave_id IN(4,5,8,12))) and a.user_id in (select id from pms_employee where designation in (5, 9, 10, 11, 12, 14, 15, 16 ,20 ,21, 22,23,24,25,26,27,28,30,31,32,33,34,35,36,37,38,39,40,41,42,43,46))) or (((a.official_total_working_hours < '07:20:00' and a.leave_id='0') or (a.official_total_working_hours < '05:20:00' and a.leave_id='14')) and a.user_id in  (select id from pms_employee where designation in (7,13)))) and date_format(a.date,'%Y-%m-%d') = '".mysql_real_escape_string($date)."' and a.leave_id not in(1,2,3,9,10) and ((e.status='0' and date_format(e.date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($date)."') or (e.status='1' and date_format(e.relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($date)."')) order by workhours desc";*/

			$sSQL = "select a.id as attendanceid, e.id as employeeid, e.name, time_format(official_total_working_hours,'%H:%i') as workhours, ishoursapproved, time_format(additionaltime,'%H:%i') as additionaltime, is_late, time_format(late_time,'%H:%i') as late_time, isExceededBreak, time_format(break_exceed_time,'%H:%i') as break_exceed_time from pms_attendance a INNER JOIN pms_employee e ON e.id = a.user_id INNER JOIN pms_designation d ON d.id = e.designation where ((((a.official_total_working_hours >= SUBTIME(fullday_minimum_working_hour,'00:10:00') and a.leave_id='0' and isExceededBreak = '1') or (a.official_total_working_hours between SUBTIME(sm_minimum_working_hour,'00:10:00') and sm_minimum_working_hour and a.leave_id='14' and isExceededBreak = '1') or (a.break_exceed_time <= '00:10:00' and isExceededBreak = '1'  and a.leave_id IN(4,5,8,12))) and consider_break_exceed='0') or (((a.official_total_working_hours < fullday_minimum_working_hour and a.leave_id='0') or (a.official_total_working_hours < sm_minimum_working_hour and a.leave_id='14')) and consider_break_exceed='1' and isExceededBreak = '1')) and date_format(a.date,'%Y-%m-%d') = '".mysql_real_escape_string($date)."' and a.leave_id not in(1,2,3,9,10) and ((e.status='0' and date_format(e.date_of_joining,'%Y-%m-%d') <= '".mysql_real_escape_string($date)."') or (e.status='1' and date_format(e.relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($date)."')) order by workhours desc";

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
			/* THIS FUNCTION IS NOT USED ANYWHERE CURRENTLY */
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
			/* THIS FUNCTION IS CURRENTLY NOT USED ANYWHERE */
			
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
			$count = $h_count = 0;

			$query = "SELECT count(id) as tot_count FROM `pms_attendance` WHERE `user_id`= '$id' and DATE_FORMAT(`date`,'%Y-%m-%d') between '$startdate' and '$enddate' and leave_id in(1,2)";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$count = $this->f("tot_count");
				}
			}

			$query = "SELECT count(id) as tot_count FROM `pms_attendance` WHERE `user_id`= '$id' and DATE_FORMAT(`date`,'%Y-%m-%d') between '$startdate' and '$enddate' and leave_id in(4,5)";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$h_count = $this->f("tot_count");
				}
			}
			
			$count = $count + ($h_count * 0.5);
			
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
			include_once('class.designation.php');

			$objEmployee = new employee();
			$objDesignation = new designations();

			$employeeInfo = $objEmployee->fnGetEmployeeDetailById($compensationInfo["userid"]);

			if(count($employeeInfo) > 0)
			{
				/* Fetch details for the user designation */
				$arrDesignationInfo = $objDesignation->fnGetDesignationById($employeeInfo["designation"]);

				/* Fetch reporting head hierarchy */
				$arrHeads = $objEmployee->fnGetReportHeadHierarchy($compensationInfo["userid"]);
				
				if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && isset($arrDesignationInfo["second_reporting_head"]) && trim($arrDesignationInfo["second_reporting_head"]) != "" && trim($arrDesignationInfo["second_reporting_head"]) != "0")
				{
					if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
					{
						$compensationInfo["firstreportingheadid"] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
					}
					
					if(isset($arrHeads[$arrDesignationInfo["second_reporting_head"]]))
					{
						$compensationInfo["secondreportingheadid"] = $arrHeads[$arrDesignationInfo["second_reporting_head"]]["id"];
					}
				}
				else if(isset($arrDesignationInfo["first_reporting_head"]) && trim($arrDesignationInfo["first_reporting_head"]) != "" && trim($arrDesignationInfo["first_reporting_head"]) != "0" && trim($arrDesignationInfo["second_reporting_head"]) == "0")
				{
					if(isset($arrHeads[$arrDesignationInfo["first_reporting_head"]]))
					{
						$compensationInfo["firstreportingheadid"] = $arrHeads[$arrDesignationInfo["first_reporting_head"]]["id"];
						$compensationInfo["secondreportingheadid"] = 0;
					}
				}
			}

			$compensationInfo["addedon"] = Date('Y-m-d H:i:s');
			$compensationInfo["approvedby_tl"] = 0;

			$compensationInfo["tlapprovalcode"] = compensationform_uid();

			/* Begin Block for delegation */
			include_once("class.leave.php");

			$objLeave = new leave();

			$checkDeligateReportingHead1Id = $objLeave->fnCheckDeligate($compensationInfo["firstreportingheadid"]);

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
			
			$tlInfo = $objEmployee->fnGetEmployeeDetailById($compensationInfo["firstreportingheadid"]);
			
			if(count($tlInfo) > 0)
			{
				$content = "Dear ".$tlInfo['name'].", <br /><br />".$employeeInfo["name"]." has added a compensation for his/her late coming on ".$compensationInfo["exceedondate"]."<br /><br />";
				
				if($compensationInfo["tlapprovalcode"] != "")
				{
					$content .= "Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$compensationInfo["tlapprovalcode"]."_Approve_C]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$compensationInfo["tlapprovalcode"]."_Reject_C]'>Reject</a></b> for letting us know your decision.";
				}
				$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
				sendmail($tlInfo['email'],$Subject,$content);
			}
			
			/* Send mail to delegated team leader */
			if($compensationInfo["delegatedtl_id"] != 0)
			{
				$DelegatedTL = $objEmployee->fnGetEmployeeDetailById($compensationInfo["delegatedtl_id"]);
				if(count($DelegatedTL) > 0)
				{
					$MailTo = $DelegatedTL["email"];

					$content = "Dear ".$DelegatedTL['name'].", <br /><br />".$curEmployee["name"]." has added a compensation for late comming on ".$compensationInfo["exceedondate"]."<br /><br />";
					
					if($compensationInfo["delegatedtlapprovalcode"] != "")
					{
						$content .= "Please click <b><a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$compensationInfo["delegatedtlapprovalcode"]."_Approve_C]'>Approve</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='mailto:transform.pms@gmail.com?subject=".$Subject." - [".$compensationInfo["delegatedtlapprovalcode"]."_Reject_C]'>Reject</a></b> for letting us know your decision.";
					}

					$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

					sendmail($MailTo, $Subject, $content);
				}
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
			/*$arrtemp = array();

			if($_SESSION["designation"] == "6" || $_SESSION["designation"] == "18" || $_SESSION["designation"] == "19")
			{
				// Get Delegated Manager id
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
				// Get delegated teamleader id
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
			}*/

			/*$sSQL = "select c.*, c.id as compensationid, date_format(a.date,'%d-%m-%Y') as attendancedate, date_format(c.compensation_date,'%d-%m-%Y') as compensation_date, time_format(c.compensation_fromtime,'%H:%i') as compensation_fromtime, time_format(c.compensation_totime,'%H:%i') as compensation_totime, time_format(a.late_time,'%H:%i') as late_time, time_format(a.break_exceed_time,'%H:%i') as break_exceed_time, e.name as employeename from pms_exceed_compensation c INNER JOIN pms_attendance a ON c.attendance_id = a.id INNER JOIN pms_employee e ON e.id = c.userid where (e.teamleader='".$_SESSION["id"]."' or c.userid in ($ids)) and (c.approvedby_tl='0' or (c.approvedby_tl='0' and c.delegatedtl_id!='0' and c.delegatedtl_status='0')) and e.status = '0'";*/
			
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

			$sSQL = "select c.*, c.id as compensationid, date_format(a.date,'%d-%m-%Y') as attendancedate, date_format(c.compensation_date,'%d-%m-%Y') as compensation_date, time_format(c.compensation_fromtime,'%H:%i') as compensation_fromtime, time_format(c.compensation_totime,'%H:%i') as compensation_totime, time_format(a.late_time,'%H:%i') as late_time, time_format(a.break_exceed_time,'%H:%i') as break_exceed_time, e.name as employeename from pms_exceed_compensation c INNER JOIN pms_attendance a ON c.attendance_id = a.id INNER JOIN pms_employee e ON e.id = c.userid where (c.firstreportingheadid='".$_SESSION["id"]."' or c.delegatedtl_id='".$_SESSION["id"]."' or c.userid in ($ids)) and (c.approvedby_tl='0' or (c.approvedby_tl='0' and c.delegatedtl_id!='0' and c.delegatedtl_status='0')) and e.status = '0'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$tempCompensation = $this->fetchrow();

					/*$approvedby_tl = "Pending";
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

					$tempCompensation["delegatedtl_status"] = $approvedby_delegatedtl;*/

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

			/*if($_SESSION["designation"] == "6" || $_SESSION["designation"] == "18" || $_SESSION["designation"] == "19")
			{
				// Get Delegated Manager id 
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
				// Get delegated teamleader id 
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
			}*/
			
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

			$sSQL = "select c.*, c.id as compensationid, date_format(a.date,'%Y-%m-%d') as attendancedate, date_format(c.compensation_date,'%Y-%m-%d') as compensation_date, time_format(c.compensation_fromtime,'%H:%i') as compensation_fromtime, time_format(c.compensation_totime,'%H:%i') as compensation_totime, time_format(a.late_time,'%H:%i') as late_time, time_format(a.break_exceed_time,'%H:%i') as break_exceed_time, e.name as employeename, c.delegatedtl_id, c.delegatedtl_status, c.delegatedtl_comment from pms_exceed_compensation c INNER JOIN pms_attendance a ON c.attendance_id = a.id INNER JOIN pms_employee e ON e.id = c.userid where (c.firstreportingheadid='".$_SESSION["id"]."' or c.delegatedtl_id='".$_SESSION["id"]."' or c.userid in ($ids)) and c.id = '".$CompensationId."'";
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

					if($recInfo["firstreportingheadid"] != "" && $recInfo["firstreportingheadid"] != "0")
					{
						$TlInfo = $objEmployee->fnGetEmployeeById($recInfo["firstreportingheadid"]);

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
					if($recInfo["firstreportingheadid"] != "" && $recInfo["firstreportingheadid"] != "0")
					{
						$TlInfo = $objEmployee->fnGetEmployeeById($recInfo["firstreportingheadid"]);

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
			/* THIS FUNCTION IS CURRENTLY NOT BEING USED ANYWHERE */
			
			$arrEmployee = array();

			//$sSQL = "SELECT *, if(attendance.shift_id=0 or attendance.shift_id is null or attendance.shift_id = null , employee.shiftid, attendance.shift_id) as shift_id, DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time, DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time, DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in, DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out, DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in, DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out, DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in, DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out, DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in, DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out, DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in, DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out, employee.id as employee_id, employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation IN (6, 8, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28) order by employee.name";
			
			$sSQL = "SELECT attendance.*, if(attendance.shift_id=0 or attendance.shift_id is null or attendance.shift_id = null , employee.shiftid, attendance.shift_id) as shift_id, DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time, DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time, DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in, DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out, DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in, DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out, DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in, DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out, DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in, DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out, DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in, DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out, employee.id as employee_id, employee.name as employee_name,attendance.id as attendance_id, time_format(d.fullday_minimum_working_hour,'%H:%i') as fullday_minimum_working_hour, time_format(d.fullday_break_minutes,'%H:%i') as fullday_break_minutes, time_format(d.halfday_minimum_working_hour,'%H:%i') as halfday_minimum_working_hour, time_format(d.halfday_break_minutes,'%H:%i') as halfday_break_minutes, time_format(d.sm_minimum_working_hour,'%H:%i') as sm_minimum_working_hour, time_format(d.sm_break_minutes,'%H:%i') as sm_break_minutes FROM `pms_employee` AS employee INNER JOIN pms_designation d ON employee.designation = d.id LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation IN (6,18,19,44) and (employee.status='0' or date_format(relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($date)."') order by employee.name";
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
			/* THIS FUNCTION IS CURRENTLY NOT BEING USED ANYWHERE */
			
			$arrEmployee = array();

			//$sSQL = "SELECT *, if(attendance.shift_id=0 or attendance.shift_id is null or attendance.shift_id = null , employee.shiftid, attendance.shift_id) as shift_id, DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time, DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time, DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in, DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out, DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in, DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out, DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in, DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out, DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in, DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out, DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in, DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out, employee.id as employee_id, employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation IN (19, 20, 22, 26) order by employee.name";

			$sSQL = "SELECT attendance.*, if(attendance.shift_id=0 or attendance.shift_id is null or attendance.shift_id = null , employee.shiftid, attendance.shift_id) as shift_id, DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time, DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time, DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in, DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out, DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in, DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out, DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in, DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out, DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in, DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out, DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in, DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out, employee.id as employee_id, employee.name as employee_name,attendance.id as attendance_id, time_format(d.fullday_minimum_working_hour,'%H:%i') as fullday_minimum_working_hour, time_format(d.fullday_break_minutes,'%H:%i') as fullday_break_minutes, time_format(d.halfday_minimum_working_hour,'%H:%i') as halfday_minimum_working_hour, time_format(d.halfday_break_minutes,'%H:%i') as halfday_break_minutes, time_format(d.sm_minimum_working_hour,'%H:%i') as sm_minimum_working_hour, time_format(d.sm_break_minutes,'%H:%i') as sm_break_minutes FROM `pms_employee` AS employee INNER JOIN pms_designation d ON employee.designation = d.id LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation IN (20, 22, 26) and (employee.status='0' or date_format(relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($date)."') order by employee.name";
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
			/* THIS FUNCTION IS CURRENTLY NOT BEING USED ANYWHERE */
			
			$arrEmployee = array();

			//$sSQL = "SELECT *, if(attendance.shift_id=0 or attendance.shift_id is null or attendance.shift_id = null , employee.shiftid, attendance.shift_id) as shift_id, DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time, DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time, DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in, DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out, DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in, DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out, DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in, DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out, DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in, DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out, DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in, DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out, employee.id as employee_id, employee.name as employee_name,attendance.id as attendance_id FROM `pms_employee` AS employee LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation IN ( 18, 21, 23, 27, 28) order by employee.name";

			$sSQL = "SELECT attendance.*, if(attendance.shift_id=0 or attendance.shift_id is null or attendance.shift_id = null , employee.shiftid, attendance.shift_id) as shift_id, DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time, DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time, DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in, DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out, DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in, DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out, DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in, DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out, DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in, DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out, DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in, DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out, employee.id as employee_id, employee.name as employee_name,attendance.id as attendance_id, time_format(d.fullday_minimum_working_hour,'%H:%i') as fullday_minimum_working_hour, time_format(d.fullday_break_minutes,'%H:%i') as fullday_break_minutes, time_format(d.halfday_minimum_working_hour,'%H:%i') as halfday_minimum_working_hour, time_format(d.halfday_break_minutes,'%H:%i') as halfday_break_minutes, time_format(d.sm_minimum_working_hour,'%H:%i') as sm_minimum_working_hour, time_format(d.sm_break_minutes,'%H:%i') as sm_break_minutes FROM `pms_employee` AS employee INNER JOIN pms_designation d ON employee.designation = d.id LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation IN (21, 23, 27, 28) and (employee.status='0' or date_format(relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($date)."') order by employee.name";
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
			/* THIS FUNCTION IS CURRENTLY NOT BEING USED ANYWHERE */
			
			$arrEmployee = array();

			$sSQL = "SELECT attendance.*, if(attendance.shift_id=0 or attendance.shift_id is null or attendance.shift_id = null , employee.shiftid, attendance.shift_id) as shift_id, DATE_FORMAT(attendance.in_time, '%H:%i') as attendance_in_time, DATE_FORMAT(attendance.out_time, '%H:%i') as attendance_out_time, DATE_FORMAT(attendance.break1_in, '%H:%i') as attendance_break1_in, DATE_FORMAT(attendance.break1_out, '%H:%i') as attendance_break1_out, DATE_FORMAT(attendance.break2_in, '%H:%i') as attendance_break2_in, DATE_FORMAT(attendance.break2_out, '%H:%i') as attendance_break2_out, DATE_FORMAT(attendance.break3_in, '%H:%i') as attendance_break3_in, DATE_FORMAT(attendance.break3_out, '%H:%i') as attendance_break3_out, DATE_FORMAT(attendance.break4_in, '%H:%i') as attendance_break4_in, DATE_FORMAT(attendance.break4_out, '%H:%i') as attendance_break4_out, DATE_FORMAT(attendance.break5_in, '%H:%i') as attendance_break5_in, DATE_FORMAT(attendance.break5_out, '%H:%i') as attendance_break5_out, employee.id as employee_id, employee.name as employee_name,attendance.id as attendance_id, time_format(d.fullday_minimum_working_hour,'%H:%i') as fullday_minimum_working_hour, time_format(d.fullday_break_minutes,'%H:%i') as fullday_break_minutes, time_format(d.halfday_minimum_working_hour,'%H:%i') as halfday_minimum_working_hour, time_format(d.halfday_break_minutes,'%H:%i') as halfday_break_minutes, time_format(d.sm_minimum_working_hour,'%H:%i') as sm_minimum_working_hour, time_format(d.sm_break_minutes,'%H:%i') as sm_break_minutes FROM `pms_employee` AS employee INNER JOIN pms_designation d ON employee.designation = d.id LEFT JOIN `pms_attendance` AS attendance ON (employee.id = attendance.user_id AND attendance.date = '$date') WHERE employee.designation IN (24, 25) and (employee.status='0' or date_format(relieving_date_by_manager,'%Y-%m-%d') >= '".mysql_real_escape_string($date)."') order by employee.name";
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

			include_once("class.designation.php");
			
			$objDesignation = new designations();
			
			$arrDes = $objDesignation->fnGetDesignationBreaksToBeDeducted();
			$arrDes[] = 0;

			$desIds = implode(",",$arrDes);
			
			/*$sSQL = "select a.user_id, count(a.id) as totalexceed, e.name from pms_attendance a LEFT JOIN pms_employee e ON e.id = a.user_id where a.isExceededBreak='1' and date_format(a.date,'%Y')='".mysql_real_escape_string($year)."' and date_format(a.date,'%m')='".mysql_real_escape_string($month)."' and a.ishoursapproved='0' and e.designation in (5,9,10,11,12,14,15,16,30,31,32,33,34,35,36,37,38,39,40,41,42,43,46) group by a.user_id having count(a.id) > 3";*/
			$sSQL = "select a.user_id, count(a.id) as totalexceed, e.name from pms_attendance a LEFT JOIN pms_employee e ON e.id = a.user_id where a.isExceededBreak='1' and date_format(a.date,'%Y')='".mysql_real_escape_string($year)."' and date_format(a.date,'%m')='".mysql_real_escape_string($month)."' and a.ishoursapproved='0' and e.designation in (".$desIds.") group by a.user_id having count(a.id) > 3";
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

			include_once("class.designation.php");
			
			$objDesignation = new designations();
			
			$arrDes = $objDesignation->fnGetDesignationBreaksToBeDeducted();
			$arrDes[] = 0;
			
			$desIds = implode(",",$arrDes);
			
			/*$sSQL = "select a.user_id, date_format(a.date,'%d-%m-%Y') as exceeddate, time_format(a.total_break_time, '%H:%i') as total_break_time, time_format(a.break_exceed_time, '%H:%i') as break_exceed_time, time_format(a.official_total_working_hours, '%H:%i') as official_total_working_hours, e.name from pms_attendance a LEFT JOIN pms_employee e ON e.id = a.user_id where a.isExceededBreak='1' and date_format(a.date,'%Y')='".mysql_real_escape_string($year)."' and date_format(a.date,'%m')='".mysql_real_escape_string($month)."' and a.ishoursapproved='1' and e.designation in (5,9,10,11,12,14,15,16,30,31,32,33,34,35,36,37,38,39,40,41,42,43,46) order by e.name, a.date";*/
			$sSQL = "select a.user_id, date_format(a.date,'%d-%m-%Y') as exceeddate, time_format(a.total_break_time, '%H:%i') as total_break_time, time_format(a.break_exceed_time, '%H:%i') as break_exceed_time, time_format(a.official_total_working_hours, '%H:%i') as official_total_working_hours, e.name from pms_attendance a LEFT JOIN pms_employee e ON e.id = a.user_id where a.isExceededBreak='1' and date_format(a.date,'%Y')='".mysql_real_escape_string($year)."' and date_format(a.date,'%m')='".mysql_real_escape_string($month)."' and a.ishoursapproved='1' and e.designation in (".$desIds.") order by e.name, a.date";
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

			include_once("class.designation.php");
			
			$objDesignation = new designations();
			
			$arrDes = $objDesignation->fnGetDesignationBreaksToBeDeducted();
			$arrDes[] = 0;
			
			$desIds = implode(",",$arrDes);
			
			/*$sSQL = "select a.user_id, count(a.id) as totallate, e.name from pms_attendance a LEFT JOIN pms_employee e ON e.id = a.user_id where a.is_late='1' and late_time > '00:04:00' and date_format(a.date,'%Y')='".mysql_real_escape_string($year)."' and date_format(a.date,'%m')='".mysql_real_escape_string($month)."' and e.designation in (5,9,10,11,12,14,15,16,30,31,32,33,34,35,36,37,38,39,40,41,42,43,46) group by a.user_id having count(a.id) > 3";*/
			$sSQL = "select a.user_id, count(a.id) as totallate, e.name from pms_attendance a LEFT JOIN pms_employee e ON e.id = a.user_id where a.is_late='1' and late_time > '00:04:00' and date_format(a.date,'%Y')='".mysql_real_escape_string($year)."' and date_format(a.date,'%m')='".mysql_real_escape_string($month)."' and e.designation in (".$desIds.") group by a.user_id having count(a.id) > 3";
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

		function fnGetLastLeaveBalance($UserId,$month_yr = "")
		{
			$currentLeaveBalance = $totalLeaves = $totalPendingLeaveCounts = $totalHalfdayLeaves = $totalPendingHalfdayLeaveCounts = $pendingLeaves = 0;
			$curDate = Date('Y-m-d');
			if(isset($month_yr) && trim($month_yr) != "")
				$firstDate = $month_yr."-01";
			else
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

		function fnCalculateHalfMonthlyReport($userid,$day,$month,$year,$emp_des, $calculationinfo = array())
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

			$full_working_hours = $sm_working_hours = '00:00:00';
			if(isset($calculationinfo->fullday_minimum_working_hour))
				$full_working_hours = $calculationinfo->fullday_minimum_working_hour.':00';

			if(isset($calculationinfo->sm_minimum_working_hour))
				$sm_working_hours = $calculationinfo->sm_minimum_working_hour.':00';

			$checkRecordExistence = $objCalculation->fnCheckHalfExistence($userid,$month,$year);
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
				$in_total = $objCalculation->fnGetHalfTotalPresents($userid,$day,$month,$year,$emp_des, $full_working_hours);

				/* Total spl */
				$total_spl = $objCalculation->fnGetHalfTotalLeave($userid,$day,$month,$year,'spl');

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
				$MovementDetails = $objCalculation->fnGetHalfTotalOfficialShiftMovementDays($userid,$day,$month,$year, $sm_working_hours);
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

				$non_week_of_Days = array();
				
				$absent_mark = 0;
				$count_ppl = 0;
				$count_upl = 0;
				$count_abs = 0;
				
				if(count($weekOfDays) > 0 )
				{
					
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

				$present_week_ph_total =  $in_total + $final_wo + $total_ph + $total_spl;

				$deducted_late_comings = '';
				$deducted_late_comings_days = '';
				//$break_exceed_days = '';
				$deducted_break_exceed_days = '';
				/*if($EmployeeInfo['designation'] == '6' || $EmployeeInfo['designation'] == '18' || $EmployeeInfo['designation'] == '19' || $EmployeeInfo['designation'] == '7' || $EmployeeInfo['designation'] == '8' || $EmployeeInfo['designation'] == '13' || $EmployeeInfo['designation'] == '17' || $EmployeeInfo['designation'] == '20' || $EmployeeInfo['designation'] == '21' || $EmployeeInfo['designation'] == '22' || $EmployeeInfo['designation'] == '23' || $EmployeeInfo['designation'] == '24' || $EmployeeInfo['designation'] == '25' || $EmployeeInfo['designation'] == '26' || $EmployeeInfo['designation'] == '27' || $EmployeeInfo['designation'] == '28' || $EmployeeInfo['designation'] == '44')
				{
					$total_late_coming = '0';
					$break_exceed_days = '0';
				}*/
				
				if(isset($calculationinfo->consider_break_exceed) && $calculationinfo->consider_break_exceed == '1')
					$break_exceed_days = '0';

				if(isset($calculationinfo->consider_late_commings) && $calculationinfo->consider_late_commings == '1')
					$total_late_coming = '0';

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

				$total_present = $in_total + $final_wo + $total_ph + $total_spl + ($total_uhl * .5) + ($total_phl * .5) ;
				//echo '<br>total_present-----'.$total_present.'-----final_deducted_day------'.$final_deducted_day.'total_ppl_consumed'.$total_ppl_consumed.'<br>';
				$payDays = ($total_present - $final_deducted_day) + $total_ppl_consumed;
				//echo $payDays;
				if($payDays >= '15')
				{
					//echo 'hello';
					/*if($month == '11' || $month == '12')
						$leaves_earn = 0.5;
					else*/
					
					$leaves_earn = 1;
					
					$current_date = date("Y-m-d H:i:s");
					$ClosingLeavesBalance = $total_avail_leave  + $leaves_earn;
					$reason = array("p"=>$in_total,"total_plt"=>$total_late_coming,"ppl"=>$total_ppl,"uhl"=>$total_uhl,"total_phl"=>$total_phl,"wo"=>$final_wo,"ph"=>$total_ph,"spl"=>$total_spl,"ha"=>$ha,"hlwp"=>$hlwp,"a"=>$abs,"plwp"=>$plwp,"ulwp"=>$ulwp,"upl"=>$total_upl,"total_present"=>$total_present,"deducted_days"=>$final_deducted_day,"payDays"=>$payDays,"OpeningLeavesBalance"=>$total_avail_leave,"leave_earn"=>$leaves_earn,"pl_consume"=>$total_ppl_consumed,"ClosingLeavesBalance"=>$ClosingLeavesBalance);


					$summary = array("emp_id"=>$userid,"added_no_of_leaves"=>$leaves_earn,"date"=>$current_date,"reason"=>json_encode($reason),"opening_leave"=>$total_avail_leave,"closing_leave"=>$ClosingLeavesBalance,"leave_added_date"=>$attendance_date,"ishalfmonthly"=>'1');

					$insertSummary = $objCalculation->fnInsertHalfSummary($summary,$month,$year);

					//echo '<br>closingLeaveBalance-------'.$ClosingLeavesBalance;
					$insertRemainingLeaves = $objCalculation->fnUpdateRemainingLeave($userid,$ClosingLeavesBalance);

					/* save the record in summary log table always*/
					$insertSummary1 = $objCalculation->fnInsertHalfSummaryLog($summary,$month,$year);
				}
			}
		}

		function fnCalculateMonthlyReport($userid,$day,$month,$year,$emp_des, $calculationinfo=array())
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
//echo "<br/>userid: ".$userid;
				$EmployeeInfo = $objEmployee->fnGetEmployeeById($userid);

				//print_r($EmployeeInfo);

				$full_working_hours = $sm_working_hours = '00:00:00';
				if(isset($calculationinfo->fullday_minimum_working_hour))
					$full_working_hours = $calculationinfo->fullday_minimum_working_hour.':00';

				if(isset($calculationinfo->sm_minimum_working_hour))
					$sm_working_hours = $calculationinfo->sm_minimum_working_hour.':00';

				if((($attendance_month == $actual_cur_month) && ($day == $actual_cur_month_last_date)) ||  ($attendance_month == $prev_month))
				{
					/* Total break exists */
					$break_exceed_days = $objCalculation->fnGetTotalBreaks($userid,$month,$year,$EmployeeInfo['designation']);

					/* Total late comings */
					$total_late_coming = $objCalculation->fnGetTotalLateComings($userid,$month,$year,$EmployeeInfo['designation']);

					/* Total presents */
					$in_total = $objCalculation->fnGetTotalPresents($userid,$month,$year,$EmployeeInfo['designation'], $full_working_hours, $sm_working_hours,$EmployeeInfo['rel_date_by_manager'],$EmployeeInfo['d_of_join']);

					/* Total spl */
					$total_spl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'spl',$EmployeeInfo['rel_date_by_manager'],$EmployeeInfo['d_of_join']);
					
					/* Total ppl */
					$total_ppl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ppl',$EmployeeInfo['rel_date_by_manager'],$EmployeeInfo['d_of_join']);

					/* Total uhl */
					$total_uhl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'uhl',$EmployeeInfo['rel_date_by_manager'],$EmployeeInfo['d_of_join']);

					/* Total php */
					$total_phl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'phl',$EmployeeInfo['rel_date_by_manager'],$EmployeeInfo['d_of_join']);

					/* Total wo */
					$wo = $objCalculation->fnGetTotalLeave($userid,$month,$year,'wo',$EmployeeInfo['rel_date_by_manager'],$EmployeeInfo['d_of_join']);

					/* Total ph */
					$total_ph = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ph',$EmployeeInfo['rel_date_by_manager'],$EmployeeInfo['d_of_join']);

					/* Total ha */
					$ha = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ha',$EmployeeInfo['rel_date_by_manager'],$EmployeeInfo['d_of_join']);

					/* Total hlwp */
					$hlwp = $objCalculation->fnGetTotalLeave($userid,$month,$year,'hlwp',$EmployeeInfo['rel_date_by_manager'],$EmployeeInfo['d_of_join']);

					/* Total abs */
					$abs = $objCalculation->fnGetTotalLeave($userid,$month,$year,'a',$EmployeeInfo['rel_date_by_manager'],$EmployeeInfo['d_of_join']);

					//$abs123 = $objCalculation->fnGetHalfTotalLeaveByIntimeOut($userid,$month,$year,'a');

					/* Total plwp */
					$plwp = $objCalculation->fnGetTotalLeave($userid,$month,$year,'plwp',$EmployeeInfo['rel_date_by_manager'],$EmployeeInfo['d_of_join']);

					/* Total ulwp */
					$ulwp = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ulwp',$EmployeeInfo['rel_date_by_manager'],$EmployeeInfo['d_of_join']);

					/* Total upl */
					$total_upl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'upl',$EmployeeInfo['rel_date_by_manager'],$EmployeeInfo['d_of_join']);

					/* Total smplt */
					$smplt = $objCalculation->fnGetTotalLeave($userid,$month,$year,'smplt',$EmployeeInfo['rel_date_by_manager'],$EmployeeInfo['d_of_join']);

					/* Total half day that not marked*/
					$checkHalfDays = $objCalculation->fnGetTotalHalfDays1($userid,$month,$year,$EmployeeInfo['designation'], $calculationinfo);

					/* Total movement details */
					$MovementDetails = $objCalculation->fnGetTotalOfficialShiftMovementDays($userid,$month,$year, $sm_working_hours);

					/* Get half monthly leaves earned */
					$HalfMonthlyLeavesEarned = $objCalculation->fnHalfMonthlyLeaveEarned($userid,$month,$year);

					/* Get Total ph of the month */
					$GetTotalPh = $objCalculation->fnGetTotalPhOfCurrentMonth($userid,$month,$year,$EmployeeInfo['rel_date_by_manager'],$EmployeeInfo['d_of_join']);

					if($GetTotalPh > $total_ph)
					{
						$RemainingPH = $GetTotalPh - $total_ph;
					}
					else
					{
						$RemainingPH = '0';
					}

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

					$phDays = $objCalculation->fnGetPhs($userid,$month,$year,$EmployeeInfo['rel_date_by_manager'],$EmployeeInfo['d_of_join']);

					$totalPhs = 0;
					if(count($phDays) > 0)
					{
						$totalPhs = count($phDays);
					}

					//echo '<pre>'; print_r($weekOfDays);print_r($phDays);

					//echo '<br><br>*************ph************** <br>';
					$non_ph_Days = array();
					$absent_mark = 0;
					$total_taken_ph_with_sendwitch = 0;
					$count_upl = 0;
					$count_abs = 0;
					$notTakenPh = 0;
					$notElegiblePh = 0;
					
					if(count($phDays) > 0)
					{
						
						foreach($phDays as $ph)
						{
							//echo '<pre>'; print_r($ph);
							$cur = $ph['holidaydate'];
							$checkLeaveTypeForDate = $objCalculation->fnCheckLeaveTypeForDate($userid,$cur);
							//$cur = '2013-01-06';
							//echo '<br>checkLeaveTypeForDate:'.$checkLeaveTypeForDate;
							$sendwitch_dates = array();
							if($checkLeaveTypeForDate == '1' || $checkLeaveTypeForDate == '2' || $checkLeaveTypeForDate == '3' || $checkLeaveTypeForDate == '6' || $checkLeaveTypeForDate == '7' || $checkLeaveTypeForDate == '10')
							{
								$total_taken_ph_with_sendwitch = $total_taken_ph_with_sendwitch + 1;
								$prev_date = date('Y-m-d', strtotime('-1 day', strtotime($cur)));
								$next_date = date('Y-m-d', strtotime('+1 day', strtotime($cur)));

								$temp = array();
								$temp1 = array();
								$sendwitch2 = array();
								$checkNextDay = $objCalculation->fnCheckNextDate($userid,$cur,$next_date,$temp);
								$count = 0;

								//echo '<br>';print_r($checkNextDay);
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
										$notElegiblePh = $notElegiblePh + 1;
									}
								}
							}
							else
							{
								$notTakenPh = $notTakenPh + 1;
							}
							//echo '<pre>'; print_r($sendwitch2);
						}
					}
					
					$EligiblePhForMonth = $totalPhs - $notElegiblePh ;

					/*echo '<br>totalPhs:'.$totalPhs;
					echo '<br>total_taken_ph_with_sendwitch:'.$total_taken_ph_with_sendwitch;
					echo '<br>notTakenPh:'.$notTakenPh;
					echo '<br>notElegiblePh:'.$notElegiblePh;
					echo '<br>EligiblePhForMonth:'.$EligiblePhForMonth;*/

					if($EligiblePhForMonth > $total_ph)
					{
						$carryForwardedPh = $EligiblePhForMonth - $total_ph;
					}
					else
					{
						$carryForwardedPh = '0';
					}
					/*echo '<br>carryForwardedPh:'.$carryForwardedPh;
					echo '<br>*************week of************** <br>';*/
						//echo 'hellooo';die;
					$non_week_of_Days = array();
					$absent_mark = 0;
					$count_ppl = 0;
					$count_upl = 0;
					$count_abs = 0;
						
					if(count($weekOfDays) > 0)
					{
						
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
					$opening_leave_balance_org = $total_avail_leave;
					//echo '<br>total_avail_leave------'.$total_avail_leave;
					if($HalfMonthlyLeavesEarned > 0)
					{
						$total_avail_leave = $total_avail_leave + $HalfMonthlyLeavesEarned;
					}
					//echo '<br>==================total_avail_leave------'.$total_avail_leave;
					$present_week_ph_total =  $in_total + $final_wo + $total_ph + $total_spl;

					$deducted_late_comings = '';
					$deducted_late_comings_days = '';
					//$break_exceed_days = '';
					$deducted_break_exceed_days = '';
					/*if($EmployeeInfo['designation'] == '6' || $EmployeeInfo['designation'] == '18' || $EmployeeInfo['designation'] == '19' || $EmployeeInfo['designation'] == '7' || $EmployeeInfo['designation'] == '8' || $EmployeeInfo['designation'] == '13' || $EmployeeInfo['designation'] == '17' || $EmployeeInfo['designation'] == '20' || $EmployeeInfo['designation'] == '21' || $EmployeeInfo['designation'] == '22' || $EmployeeInfo['designation'] == '23' || $EmployeeInfo['designation'] == '24' || $EmployeeInfo['designation'] == '25' || $EmployeeInfo['designation'] == '26' || $EmployeeInfo['designation'] == '27' || $EmployeeInfo['designation'] == '28' || $EmployeeInfo['designation'] == '44')
					{
						$total_late_coming = '0';
						$break_exceed_days = '0';
					}*/
					
					if(isset($calculationinfo->consider_break_exceed) && $calculationinfo->consider_break_exceed == '1')
					$break_exceed_days = '0';

					if(isset($calculationinfo->consider_late_commings) && $calculationinfo->consider_late_commings == '1')
						$total_late_coming = '0';
					
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
					echo '<br>count_abs'.$count_abs;*/	

					$total_ppl_consumed = $ppl_given_for_plwp + $ppl_given_for_hlwp + $ppl_given_for_ulwp;

					/*echo '<br>in_total--------'.$in_total;
					echo '<br>finalwo--------'.$final_wo;
					echo '<br>total_ph--------'.$total_ph;
					echo '<br><br><br>in_total---'.$in_total.'<br>finalwo----'.$final_wo.'<br>total_ph-----'.$total_ph.'<br>total_uhl------'.$total_uhl.'<br>total_phl-------'.$total_phl;*/

					$total_present = $in_total + $final_wo + $total_ph + $total_spl + ($total_uhl * .5) + ($total_phl * .5) + ( $ha * .5 ) + ( $hlwp * .5 ) ;
					//echo '<br><br>total_present---'.$total_present.'<br>final_deducted_day---'.$final_deducted_day.'<br>total_ppl_consumed---'.$total_ppl_consumed.'<br>';
					$payDays = ($total_present - $final_deducted_day) + $total_ppl_consumed;
					/*echo '<br>-leaves_earn----'.$leaves_earn;
					echo '<br>payDays---'.$payDays;*/
					 if($month == '11' || $month == '12')
					{
						if($payDays >= 15)
						{
							$leaves_earn = 1;
						}
					}
					else
					{
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
					}
					//echo '<br>-leaves_earn1----'.$leaves_earn;
					/* Deduct leave earned half monthly */
					//echo '<br>HalfMonthlyLeavesEarned-----'.$HalfMonthlyLeavesEarned;
					if($HalfMonthlyLeavesEarned > 0)
					{
						$leaves_earn = $leaves_earn - $HalfMonthlyLeavesEarned;
					}
					
					if($total_ppl_consumed > $opening_leave_balance_org && $leaves_earn < 0)
					{
						/* Remove leaves from paydays is leaves is less than 0 */
						$payDays = $payDays + $leaves_earn;
					}
					
					//echo '<br>payDays---'.$payDays;
					//echo '<br>total_avail_leave---'.$total_avail_leave;
					//echo '<br>total_ppl_consumed---'.$total_ppl_consumed;
					//echo '<br>leaves_earn---'.$leaves_earn;
					//echo '<br>carryForwardedPh---'.$carryForwardedPh;
					$ClosingLeavesBalance = ($total_avail_leave - $total_ppl_consumed) + $leaves_earn + $carryForwardedPh;

					$leaves_earn = $leaves_earn + $carryForwardedPh;

					if($month == '12' && $ClosingLeavesBalance > '12')
					{
						$ClosingLeavesBalance = '12';
					}
					
					//echo '<br>ClosingLeavesBalance---'.$ClosingLeavesBalance;

					$total_leave_taken = $total_absence + $total_upl + ($total_phl * .5 ) + ($total_uhl * .5 ) + $total_ppl + $abs + ($ha * .5);

					/*echo '<br>total_leave_taken--------'.$total_leave_taken;
					echo '<br>leaves_earn:'.$leaves_earn.'<br>carryForwardedPh:'.$carryForwardedPh.'<br>ClosingLeavesBalance--------'.$ClosingLeavesBalance;*/

					$current_date = date("Y-m-d H:i:s");

					/* get that record exist in the leave record table or not */
					$checkRecordExistence = $objCalculation->fnCheckExistence($userid,$month,$year);

					/* get that record exist in the monthly report table or not */
					$checkMonthlyReportRecordExistence = $objCalculation->fnCheckExistenceMonthlyReport($userid,$month,$year);

					/* check record exist in leave histy */
					if(isset($checkRecordExistence) &&  $checkRecordExistence != '')
					{
						//echo 'hello4';
						//echo $checkRecordExistence;
						$reason = array("p"=>$in_total,"total_plt"=>$total_late_coming,"ppl"=>$total_planned_leave_taken_with_sandwitch,"uhl"=>$total_uhl,"total_phl"=>$total_phl,"wo"=>$final_wo,"ph"=>$total_ph,"spl"=>$total_spl,"ha"=>$ha,"hlwp"=>$total_hlwp_given,"a"=>$abs,"plwp"=>$total_plwp_given,"ulwp"=>$total_ulwp_given,"upl"=>$total_unplanned_leave_taken_with_sandwitch,"total_present"=>$total_present,"deducted_days"=>$final_deducted_day,"payDays"=>$payDays,"OpeningLeavesBalance"=>$total_avail_leave,"leave_earn"=>$leaves_earn,"pl_consume"=>$total_ppl_consumed,"ClosingLeavesBalance"=>$ClosingLeavesBalance,"ph_carry_forward"=>$carryForwardedPh);

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
						$reason = array("p"=>$in_total,"total_plt"=>$total_late_coming,"ppl"=>$total_planned_leave_taken_with_sandwitch,"uhl"=>$total_uhl,"total_phl"=>$total_phl,"wo"=>$final_wo,"ph"=>$total_ph,"spl"=>$total_spl,"ha"=>$ha,"hlwp"=>$total_hlwp_given,"a"=>$abs,"plwp"=>$total_plwp_given,"ulwp"=>$total_ulwp_given,"upl"=>$total_unplanned_leave_taken_with_sandwitch,"total_present"=>$total_present,"deducted_days"=>$final_deducted_day,"payDays"=>$payDays,"OpeningLeavesBalance"=>$total_avail_leave,"leave_earn"=>$leaves_earn,"pl_consume"=>$total_ppl_consumed,"ClosingLeavesBalance"=>$ClosingLeavesBalance,"ph_carry_forward"=>$carryForwardedPh);

						$summary = array("emp_id"=>$userid,"added_no_of_leaves"=>$leaves_earn,"date"=>$current_date,"reason"=>json_encode($reason),"opening_leave"=>$total_avail_leave,"closing_leave"=>$ClosingLeavesBalance,"ishalfmonthly"=>'0',"leave_added_date"=>$attendance_date);

						//echo '<br><br>----------<pre>'; print_r($summary);

						$insertSummary = $objCalculation->fnInsertHalfSummary($summary,$month,$year);


						/* unset the id and save the record in summary log table always*/

						$insertSummary1 = $objCalculation->fnInsertHalfSummaryLog($summary,$month,$year);


					}
					/* check record exist in pms_attendance_report  table or not */
					if(isset($checkMonthlyReportRecordExistence) &&  $checkMonthlyReportRecordExistence != '')
					{
						//echo 'hello1';
						/* if value exist update in pms_attendance_report table */
						$finalCalcHalfMonthly = array("id"=>$checkMonthlyReportRecordExistence,"employee_id"=>$userid,"month"=>$month,"year"=>$year,"present"=>$in_total,"break_exceeds"=>$break_exceed_days,"total_plt"=>$total_late_coming,"ppl"=>$total_planned_leave_taken_with_sandwitch,"uhl"=>$total_uhl,"wo"=>$final_wo,"ph"=>$total_ph,"spl"=>$total_spl,"ha"=>$ha,"hlwp"=>$total_hlwp_given,"abs"=>$abs,"plwp"=>$total_plwp_given,"ulwp"=>$total_ulwp_given,"nj"=>'0',"le"=>'0',"awol"=>'0',"total_present"=>$total_present,"pay_days"=>$payDays,"opening_leave"=>$total_avail_leave,"leave_earns"=>$leaves_earn,"pl_taken"=>$total_ppl_consumed,"eml_taken"=>'0',"phl_taken"=>$total_phl,"uhl_taken"=>$total_uhl,"total_leave_taken"=>$total_leave_taken,"closing_balance"=>$ClosingLeavesBalance,"upl"=>$total_unplanned_leave_taken_with_sandwitch,"deducted_days"=>$final_deducted_day,"ph_carry_forward"=>$carryForwardedPh);

						//echo '<br><br>----------<pre>'; print_r($finalCalcHalfMonthly);

						$insertRemainingLeaves = $objCalculation->fnUpdateRemainingLeave($userid,$ClosingLeavesBalance);

						$insertHalfReport = $objCalculation->fnUpdateMonthReport($finalCalcHalfMonthly);

					}
					else
					{
						/* Insert the records in  pms_attendance_report table*/
						//echo 'hello2';
						$finalCalcHalfMonthly = array("employee_id"=>$userid,"month"=>$month,"year"=>$year,"present"=>$in_total,"break_exceeds"=>$break_exceed_days,"total_plt"=>$total_late_coming,"ppl"=>$total_planned_leave_taken_with_sandwitch,"uhl"=>$total_uhl,"wo"=>$final_wo,"ph"=>$total_ph,"spl"=>$total_spl,"ha"=>$ha,"hlwp"=>$total_hlwp_given,"abs"=>$abs,"plwp"=>$total_plwp_given,"ulwp"=>$total_ulwp_given,"nj"=>'0',"le"=>'0',"awol"=>'0',"total_present"=>$total_present,"pay_days"=>$payDays,"opening_leave"=>$total_avail_leave,"leave_earns"=>$leaves_earn,"pl_taken"=>$total_ppl_consumed,"eml_taken"=>'0',"phl_taken"=>$total_phl,"uhl_taken"=>$total_uhl,"total_leave_taken"=>$total_leave_taken,"closing_balance"=>$ClosingLeavesBalance,"upl"=>$total_unplanned_leave_taken_with_sandwitch,"deducted_days"=>$final_deducted_day,"ph_carry_forward"=>$carryForwardedPh);

						//echo '<br><br>----------<pre>'; print_r($finalCalcHalfMonthly);

						$insertRemainingLeaves = $objCalculation->fnUpdateRemainingLeave($userid,$ClosingLeavesBalance);

						$insertHalfReport = $objCalculation->fnInsertMonthReport($finalCalcHalfMonthly);
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

		function fnSaveLeaveBalanceLog()
		{
			$today = date("Y-m-d H:i:s");
			$query = "INSERT INTO pms_opening_leave_balance_log( emp_id, leave_balance,opening_balance,date) SELECT id, leave_bal,opening_leave_balance,now() FROM pms_employee";
			$this->query($query);
			return true;
		}
		
		function fnCopyOpeningBalance()
		{
			$curYear = Date('Y');
			$curMonth = Date('m');
			
			$query = "UPDATE `pms_employee` SET opening_leave_balance = leave_bal, opening_leave_balance_month = '".mysql_real_escape_string($curMonth)."', opening_leave_balance_year = '".mysql_real_escape_string($curYear)."'";
			$this->query($query);
			return true;
		}
		
		function fnGetTotalPenaltyBreakExceedsByUser($UserId)
		{
			$curMonthYear = Date('Y-m');
			$BreakExceedCount = 0;

			/* Fetch Break exceed deducted for the current month for the user, do not include approved break exceeds */
			$sSQL = "select count(id) as break_exceed_count from pms_attendance where date_format(date,'%Y-%m') = '$curMonthYear' and user_id='".mysql_real_escape_string($UserId)."' and isExceededBreak='1' and ishoursapproved='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$BreakExceedCount = $this->f("break_exceed_count");
				}
			}
			
			return $BreakExceedCount;
		}
		
		function fnGetTotalPenaltyLateComingByUser($UserId)
		{
			$curMonthYear = Date('Y-m');
			$LateComingCount = 0;
			
			/* Fetch late coming for the current month for the user */
			$sSQL = "select count(id) as late_coming_count from pms_attendance where date_format(date,'%Y-%m') = '$curMonthYear' and user_id='".mysql_real_escape_string($UserId)."' and is_late='1' and late_time > '00:04:00'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$LateComingCount = $this->f("late_coming_count");
				}
			}
			
			return $LateComingCount;
		}
		
		function fnGetTotalPenaltyLateComingRequestByUser($UserId)
		{
			include_once("class.employee.php");
			$objEmployee = new employee();

			$current_date = date('Y-m-d');
			$curMonthYear = Date('Y-m');
			$LateComingCompensationCount = 0;

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

			/* Fetch late coming for the current month for the user */
			$sSQL = "select count(c.id) as late_coming_compensation_count from pms_exceed_compensation c INNER JOIN pms_attendance a ON c.attendance_id = a.id INNER JOIN pms_employee e ON e.id = c.userid where (c.firstreportingheadid='".$UserId."' or c.delegatedtl_id='".$UserId."' or (c.userid in ($ids) and '".mysql_real_escape_string($UserId)."' = (select delegate from pms_leave_form where employee_id = c.firstreportingheadid and '$current_date' between DATE_FORMAT(`start_date`,'%Y-%m-%d') and DATE_FORMAT(`end_date`,'%Y-%m-%d') and (status_manager='1' or (status_manager='0' and deligateManagerId!=0 and manager_delegate_status='1')) ORDER BY `id` LIMIT 0,1))) and (c.approvedby_tl='0' and c.delegatedtl_status in (0,null)) and e.status = '0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$LateComingCompensationCount = $this->f("late_coming_compensation_count");
				}
			}
			
			return $LateComingCompensationCount;
		}
		
		function fnGetPublicHolidays($fromDate, $toDate, $userId)
		{
			$arrAllPublicHolidays = array();
			/* Check last date for which roster is created */
			$enddate = Date("Y-m-d");
			$sSQL = "select date_format(end_date,'%Y-%m-%d') as enddate from pms_roster where userid='".mysql_real_escape_string($userId)."' order by start_date desc";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$enddate = $this->f("enddate");
				}
			}

			/* Fetch leave type information, for public holidays */
			$leave_title = $leave_color = "";
			$sSQL  = "select title, color from pms_leave_type where id='10'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$leave_title = $this->f("title");
					$leave_color = $this->f("color");
				}
			}
			
			/* Get PH marked in attendance */
			$query = "SELECT id, date_format(date, '%Y-%m-%d') as startdate FROM `pms_attendance` WHERE DATE_FORMAT(date,'%Y-%m-%d') between '".mysql_real_escape_string($fromDate)."' AND '".mysql_real_escape_string($toDate)."' AND user_id = '".mysql_real_escape_string($userId)."' AND leave_id ='10' and date_format(date, '%Y-%m-%d') in (select date_format(holidaydate, '%Y-%m-%d') as holidaydate from pms_holidays where date_format(holidaydate,'%Y-%m-%d') between '".mysql_real_escape_string($fromDate)."' AND '".mysql_real_escape_string($toDate)."')";
			$this->query($query);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllPublicHolidays[] = array(
											'id' => $this->f("aid"),
											'title' => $leave_title,
											'start' => $this->f("startdate"),
											'color' => $leave_color
										);
				}
			}

			/* Fetch holidays from holiday table */
			$cond = "";
			if($enddate != "")
				$cond = " and date_format(holidaydate,'%Y-%m-%d') > '".mysql_real_escape_string($enddate)."'";


			$sSQL = "select id, date_format(holidaydate, '%Y-%m-%d') as holidaydate from pms_holidays where date_format(holidaydate,'%Y-%m-%d') between '".mysql_real_escape_string($fromDate)."' AND '".mysql_real_escape_string($toDate)."' $cond";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllPublicHolidays[] = array(
											'id' => $this->f("id"),
											'title' => $leave_title,
											'start' => $this->f("holidaydate"),
											'color' => $leave_color
										);
				}
			}

			return $arrAllPublicHolidays;
		}
	}
?>

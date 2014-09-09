<?php

	include('common.php');
	
	include_once('includes/class.attendance.php');
	
	$objAttendance = new attendance();
	
	$arrResult = array("exceedondate"=>"-", "exceedfortime"=>"-", "compensationtime"=>"-", "fromH"=>"-", "fromM"=>"-", "fromAMPM"=>"-", "toH"=>"-", "toM"=>"-", "toAMPM"=>"-");
	
	/* Set default compensation time */
	
	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "" && isset($_REQUEST["type"]) && trim($_REQUEST["type"]) != "")
	{
		$AttendanceInfo = $objAttendance->fnGetAttendanceById($_REQUEST["id"]);
		//print_r($AttendanceInfo);
		if(count($AttendanceInfo) > 0)
		{
			$curdate = Date('Y-m-d');
			
			$compensation_time = "00:00:00";

			if($_REQUEST["type"] == "1")
			{
				$compensation_time = $AttendanceInfo["late_time"];
			}
			else if($_REQUEST["type"] == "2")
			{
				$compensation_time = $AttendanceInfo["break_exceed_time"];
			}
			
			$time_to_compensate = fnConvertNearstHalfHour($compensation_time);
			
			$totime = date('Y-m-d H:i:s');
			$fromtime = date('Y-m-d H:i:s',strtotime("-".date('H',strtotime(date("Y-m-d")." ".$time_to_compensate))." hours", strtotime(date('Y-m-d H:i:s',strtotime("-".date('i',strtotime(date("Y-m-d")." ".$time_to_compensate))." minutes", strtotime($totime))))));
			
			$fromH = Date('h',strtotime($fromtime));
			$fromM = Date('i',strtotime($fromtime));
			$fromAMPM = Date('a',strtotime($fromtime));

			$toH = Date('h',strtotime($totime));
			$toM = Date('i',strtotime($totime));
			$toAMPM = Date('a',strtotime($totime));
			
			$arrResult = array("exceedondate"=>Date('Y-m-d',strtotime($AttendanceInfo["date"])), "exceedfortime"=>$compensation_time, "compensationtime"=>$time_to_compensate, "fromH"=>$fromH, "fromM"=>$fromM, "fromAMPM"=>$fromAMPM, "toH"=>$toH, "toM"=>$toM, "toAMPM"=>$toAMPM);
		}
	}

	function fnConvertNearstHalfHour($myTime)
	{
		$now = strtotime(Date('Y-m-d')." ".$myTime);
		if($now % (30 * 60) != 0)
		{
			$interval   = 30;   // in minutes

			// add interval to the time
			$time_w_interval = $now + ($interval * 60);

			// round DOWN to nearest half hour
			$rounded_time = floor($time_w_interval / ($interval * 60)) * ($interval * 60);

			// get the start & end times (formatted)
			$start_time = date("H:i:s", $rounded_time);
		}
		else
		{
			$start_time = $myTime;
		}
		return $start_time;
	}
	
	echo json_encode($arrResult);

?>

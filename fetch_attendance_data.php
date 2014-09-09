<?php

	//include('common.php');
	session_start();

	$year = date('Y');
	$month = date('m');

	$start = $_REQUEST["start"];
	$end = $_REQUEST["end"];
	$type = $_REQUEST["type"];

	include_once('includes/class.attendance.php');
	include_once('includes/class.employee.php');
	include_once('includes/class.designation.php');

	$objAttendance = new attendance();
	$objEmployee = new employee();
	$objDesignation = new designations();

	$arrEmployee = array();
	$arrBreaks = array();
	$arrPublicHolidays = array();

	if(isset($_REQUEST['requestedId']) && $_REQUEST['requestedId'] != '')
	{
		$arrEmployee[] = $_REQUEST['requestedId'];
	}
	else
	{
		$arrDesignation = $objDesignation->fnGetDesignationById($_SESSION['designation']);
		
		/*if(isset($_SESSION['designation']) && ($_SESSION['designation'] == "5" || $_SESSION['designation'] == "9" || $_SESSION['designation'] == "10" || $_SESSION['designation'] == "11" || $_SESSION['designation'] == "12" || $_SESSION['designation'] == "14" || $_SESSION['designation'] == "15" || $_SESSION['designation'] == "16" || $_SESSION['designation'] == "20" || $_SESSION['designation'] == "21" || $_SESSION['designation'] == "22" || $_SESSION['designation'] == "23" || $_SESSION['designation'] == "24" || $_SESSION['designation'] == "26" || $_SESSION['designation'] == "27" || $_SESSION['designation'] == "28" || $_SESSION['designation'] == '30' || $_SESSION['designation'] == '31' || $_SESSION['designation'] == '32' || $_SESSION['designation'] == '33' || $_SESSION['designation'] == '34' || $_SESSION['designation'] == '35' || $_SESSION['designation'] == '36' || $_SESSION['designation'] == '37' || $_SESSION['designation'] == '38' ||  $_SESSION['designation'] == '39' || $_SESSION['designation'] == '40' || $_SESSION['designation'] == '41' || $_SESSION['designation'] == '42' || $_SESSION['designation'] == '43'))*/
		/*if(isset($arrDesignation["consider_break_exceed"]) && $arrDesignation["consider_break_exceed"] == "0")
		{
			$arrEmployee[] = $_SESSION['id'];
			$arrBreaks = $objAttendance->fnGetBreaksAndLate($start, $end, $_SESSION['id']);
		}
		else
		{*/
			if($_SESSION['usertype'] == 'admin')
			{
				$arrEmployee = $objEmployee->fnGetAllemployeesReleavingDateWise($end, 0, $start);
			}
			else
			{
				$arrEmployee[] = $_SESSION['id'];
				if($type == "team")
				{
					$arrEmployee = $objEmployee->fnGetAllemployeesReleavingDateWise($end, $_SESSION['id'], $start);

					/* Get delegated teamleader id */
					$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);

					/* Get Delegated Manager id */
					$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($_SESSION['id']);

					$arrDelegatedEmployee = array();
					$arrtemp = array();
					if(count($arrDelegatedTeamLeaderId) > 0 )
					{
						foreach($arrDelegatedTeamLeaderId as $delegatesIds)
						{
							//echo $delegatesIds;
							$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
							$arrEmployee =$arrEmployee + $arrtemp ;
						}
					}
					if(count($arrDelegatedManagerId) > 0 )
					{
						foreach($arrDelegatedManagerId as $delegatesManagerIds)
						{
							//echo $delegatesIds;
							$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
							$arrEmployee =$arrEmployee + $arrtemp;
						}
					}
				}
				else
				{
					/* Fetch break exceed and late coming */
					$arrBreaks = $objAttendance->fnGetBreaksAndLate($start, $end, $_SESSION['id']);
					
					/* Fetch public holidays */
					$arrPublicHolidays = $objAttendance->fnGetPublicHolidays($start, $end, $_SESSION['id']);
				}
			/*}*/
		}
	}


	$displayIds = '0';
	if(count($arrEmployee) > 0)
	{
		$arrEmployee = array_filter($arrEmployee,'strlen');

		if(count($arrEmployee) > 0)
		{
			$displayIds = implode(',',$arrEmployee);
		}
	}

	$arrHighlights = $objAttendance->fetchAttendenceData($start, $end, $displayIds);

	$arrHighlights = array_merge($arrHighlights, $arrBreaks);
	$arrHighlights = array_merge($arrHighlights, $arrPublicHolidays);

	//print_r($arrHighlights);die;

	echo json_encode($arrHighlights);

?>

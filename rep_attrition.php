<?php 

	include('common.php');
	$tpl = new Template($app_path);

	ini_set("max_execution_time",0);

	$tpl->load_file('template.html','main');
	$tpl->load_file('rep_attrition.html','main_container');

	$PageIdentifier = "HrReportAttrition";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Attrition Report");
	$breadcrumb = '<li class="active">Attrition Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");

	if(isset($_REQUEST['info']))
	{
		switch($_REQUEST["info"])
		{
			case 'nod':
				$messageClass = "alert-error";
				$message = "Cannot search report for month before January, 2014.";
				break;
			case 'errp':
				$messageClass = "alert-error";
				$message = "No data found for future search.";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$curDate = Date('Y-m-d');
	$curYear = Date('Y');

	$arrYear = array($curYear+1, $curYear, $curYear-1);

	include_once('includes/class.employee.php');
	include_once('includes/class.attendance.php');

	$objEmployee = new employee();
	$objAttendance = new attendance();

	$tpl->set_var("MultipleTeamleaderBlock","");
	if(isset($_POST["action"]) && trim($_POST["action"]) == "AttritionSearch")
	{
		if($_POST["year"]."-".$_POST["month"] < '2014-01')
		{
			header("Location: rep_attrition.php?info=nod");
			exit;
		}
		else if($_POST["year"]."-".$_POST["month"] > Date('Y-m'))
		{
			header("Location: rep_attrition.php?info=errp");
			exit;
		}

		$_SESSION["SearchAttrition"]["month"] = $_POST["month"];
		$_SESSION["SearchAttrition"]["year"] = $_POST["year"];
		$_SESSION["SearchAttrition"]["reporting_head"] = $_POST["reporting_head"];
		$_SESSION["SearchAttrition"]["issingle"] = false;

		header("Location: rep_attrition.php");
		exit;
	}

	if(!isset($_SESSION["SearchAttrition"]["month"]))
		$_SESSION["SearchAttrition"]["month"] = Date('m');
	if(!isset($_SESSION["SearchAttrition"]["year"]))
		$_SESSION["SearchAttrition"]["year"] = $curYear;

	if(isset($_SESSION["SearchAttrition"]["month"]))
		$tpl->set_var("month", $_SESSION["SearchAttrition"]["month"]);
	else
		$_SESSION["SearchAttrition"]["month"] = 0;

	if(isset($_SESSION["SearchAttrition"]["year"]))
		$tpl->set_var("year", $_SESSION["SearchAttrition"]["year"]);
	else
		$_SESSION["SearchAttrition"]["year"] = 0;

	if(isset($_SESSION["SearchAttrition"]["reporting_head"]))
		$tpl->set_var("reporting_head", $_SESSION["SearchAttrition"]["reporting_head"]);
	else
		$_SESSION["SearchAttrition"]["reporting_head"] = 0;

	$tpl->set_var("DisplayReportingHeadHiddenBlock","");

	$arrReportingHead = $objEmployee->fnTeamLeaderExistForManager($_SESSION['id']);

	if(isset($_SESSION["SearchAttrition"]["reporting_head"]) && $_SESSION["SearchAttrition"]["reporting_head"] == '')
	{
		$_SESSION["SearchAttrition"]["reporting_head"] = '0';
	}

	$curMonth = date('m');
	$curYear = date('Y');

	$start = $_SESSION["SearchAttrition"]["year"].'-'.$_SESSION["SearchAttrition"]["month"].'-01';
	$tpl->set_var("leavers_for",date('F, Y', strtotime($start)));
	$tpl->set_var("joiners_for",date('F, Y', strtotime($start)));
	$tpl->set_var("ytd_joinees_till_date_text",date('F, Y', strtotime($start)));
	$end = $curYear.'-'.$curMonth.'-01';
	$start = explode ("-", $start);
	$end = explode ("-", $end);

	$end_d = $_SESSION["SearchAttrition"]["year"].'-'.$_SESSION["SearchAttrition"]["month"].'-01';
	$start_d = $curYear.'-01-01';
	$realMonthlyHeadCount = '0';
	$final_m_h_count = '0';

	$arrHighestHeadCount = $objEmployee->fnGetHighestReportingHead();
	$arrHighestHeadCount[] = 0;

	/* Display block for leavers type */
	$arrTerm = array("1"=>"Terminated","2"=>"Absconding","3"=>"Resigned");
	$tpl->set_var("FillLeaversTypeBlock","");
	$tpl->set_var("FillLeaversTypeForHeadsBlock","");
	foreach($arrTerm as $curTerm)
	{
		$tpl->set_var("leavers_type_text", $curTerm);
		$tpl->set_var("leavers_type_for_heads", $curTerm);

		$tpl->parse("FillLeaversTypeBlock", true);
		$tpl->parse("FillLeaversTypeForHeadsBlock", true);
	}

	if($_SESSION["SearchAttrition"]["reporting_head"] == '0')
	{
		/*
		 * BEGIN CALCULATE MONTHLY ATTRITION, NO REPORTING HEAD SELECTED
		 * */
		
		/* If reporting head is not selected */
		$arrEmployee = $objEmployee->fnGetEmployeesForHrAttrition($_SESSION["id"],$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"],$_SESSION["SearchAttrition"]["reporting_head"]);
		
		if((isset($_SESSION["SearchAttrition"]["year"]) && $_SESSION["SearchAttrition"]["year"] != '') || (isset($_SESSION["SearchAttrition"]["month"]) && $_SESSION["SearchAttrition"]["month"] != ''))
		{
			$month_start_date = $_SESSION["SearchAttrition"]["year"].'-'.$_SESSION["SearchAttrition"]["month"].'-01';
			$month_end_date = $_SESSION["SearchAttrition"]["year"].'-'.$_SESSION["SearchAttrition"]["month"].'-'.date('t',strtotime($month_start_date));
			
			/* Calculating monthly head count */
			while (strtotime($month_start_date) <= strtotime($month_end_date))
			{
				$daily_head_c = $objEmployee->fnGetHeadCountUsingUserId(implode(",",$arrHighestHeadCount),$month_start_date);

				$realMonthlyHeadCount += $daily_head_c;
				$month_start_date = date ("Y-m-d", strtotime("+1 day", strtotime($month_start_date)));
			}
		}
		
		/* If monthly head count is greater then 0, then only can calculate */
		if($realMonthlyHeadCount != 0)
		{
			$last_d_m = date('t',strtotime($month_end_date));
			$final_m_h_count = $realMonthlyHeadCount/$last_d_m;
		}
		
		/* Check if final monthly head count is created or not */
		if(isset($final_m_h_count) && $final_m_h_count != 0)
		{
			$no_of_l_month = count($arrEmployee);
			
			if($final_m_h_count != '0' && $final_m_h_count != '')
			{
				$monthly_attr_cal = ($no_of_l_month/$final_m_h_count)*100;
			}
			else
			{
				$monthly_attr_cal = 0 ;
			}
			$tpl->set_var("final_monthly_attrition",round($monthly_attr_cal,2));
			$tpl->set_var("display_average_head_count",round($final_m_h_count,2));
		}
		else
		{
			$tpl->set_var("final_monthly_attrition",0);
			$tpl->set_var("display_average_head_count",0);
		}
		
		/*
		 * END CALCULATE MONTHLY ATTRITION, NO REPORTING HEAD SELECTED
		 * */
		 
		 /* ------------------------------------------------------------------------------------ */
		 
		 /*
		 * BEGIN CALCULATE YEARLY ATTRITION, NO REPORTING HEAD SELECTED
		 * */

		$arrayHeadCount = array();
		/* Itrate through all the months */
		$tpl->set_var("yearly_attrition_till_date_text", date('F, Y',strtotime($end_d)));
		while(strtotime($start_d) <= strtotime($end_d))
		{
			/* Fetch first day and last day for current month in iteration */
			$first_date = Date('Y-m-01',strtotime($start_d));
			$last_date = Date('Y-m-t',strtotime($first_date));

			$realHeadCount = 0;

			/* Daily head count for the month */
			while (strtotime($first_date) <= strtotime($last_date))
			{
				$daily_head_count = $objEmployee->fnGetHeadCountUsingUserId(implode(",",$arrHighestHeadCount),$first_date);
				$realHeadCount += $daily_head_count;
				$first_date = date ("Y-m-d", strtotime("+1 day", strtotime($first_date)));
			}

			/* Calculate average for the month */
			$last_date_of_month = date('t',strtotime($last_date));
			$average_head_count = $realHeadCount/$last_date_of_month;

			if($average_head_count != 0 && $average_head_count != '')
			{
				$arrayHeadCount[] = $average_head_count;
			}

			$start_d = date("Y-m-d", strtotime("+1 month", strtotime($start_d)));
		}

		/* Calculate average for yearly head count */
		$final_Head_count = 0;
		if(count($arrayHeadCount) > 0)
		{
			$final_Head_count = array_sum($arrayHeadCount)/count($arrayHeadCount);
		}
		
		/* Calculate attrition percentage if final headcount is calculated */
		if(isset($final_Head_count) && $final_Head_count != '0')
		{
			$arrYearlyEmployee = $objEmployee->fnGetEmployeesForYearlyHrAttrition($_SESSION["id"],$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"],$_SESSION["SearchAttrition"]["reporting_head"]);

			$no_of_leavers = count($arrYearlyEmployee);
			$no_of_months_till_month = count($arrayHeadCount);

			$annual_attrition = (($no_of_leavers*(12/$no_of_months_till_month))/$final_Head_count)*100;
			$tpl->set_var("final_annual_attrition",round($annual_attrition,2));
		}
		else
		{
			$tpl->set_var("final_annual_attrition","00.00");
		}

		 /*
		 * END CALCULATE YEARLY ATTRITION, NO REPORTING HEAD SELECTED
		 * */
	}
	else
	{
		/* If reporting head are selected */
		
		/*
		 * BEGIN CALCULATE MONTHLY ATTRITION, REPORTING HEAD SELECTED
		 * */

		$tpl->set_var("MutilpleTeamleaderData","");
/*		
		if(count($_SESSION["SearchAttrition"]["reporting_head"]) > 0)
		{
			if((isset($_SESSION["SearchAttrition"]["year"]) && $_SESSION["SearchAttrition"]["year"] != '') || (isset($_SESSION["SearchAttrition"]["month"]) && $_SESSION["SearchAttrition"]["month"] != ''))
			{
				$month_start_d = $_SESSION["SearchAttrition"]["year"].'-'.$_SESSION["SearchAttrition"]["month"].'-01';
				$month_end_d = $_SESSION["SearchAttrition"]["year"].'-'.$_SESSION["SearchAttrition"]["month"].'-'.date('t',strtotime($month_start_d));

				$newHeadCountsArray = array();
				
				while(strtotime($month_start_d) <= strtotime($month_end_d))
				{
					$daily_reporting_c = $objEmployee->fnGetOverallHeadCount($_SESSION['SearchAttrition']['reporting_head'],$month_start_d);
					
					print_r($daily_reporting_c);
					
					if(count($daily_reporting_c) > 0 )
					{
						foreach($daily_reporting_c as $reporting)
						{
							$daily_head_c = $objEmployee->fnGetHeadCountUsingUserId($reporting,$month_start_d);
							
							if($daily_head_c != '0' && $daily_head_c != '')
							{
								$newHeadCountsArray[$reporting][] = $daily_head_c;
							}
						}
					}
				
					//print_r($newHeadCountsArray);
				
					$month_start_d = date ("Y-m-d", strtotime("+1 day", strtotime($month_start_d)));
				}
			}
		}

		if(count($daily_reporting_c) > 0)
		{
			$arrEmployee = $objEmployee->fnGetEmployeesForHrMultipleReportingAttrition($daily_reporting_c,$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"]);
			$arrEmployeeForYearlyAttrition = $objEmployee->fnGetEmployeesForHrMultipleReportingYearlyAttrition($daily_reporting_c,$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"]);
		}
		else
		{
			$arrEmployee = $objEmployee->fnGetEmployeesForHrAttrition($_SESSION["id"],$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"],$_SESSION["SearchAttrition"]["reporting_head"]);
			$arrEmployeeForYearlyAttrition = $objEmployee->fnGetEmployeesForHrMultipleReportingYearlyAttrition($daily_reporting_c,$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"]);
		}
		
		if(count($newHeadCountsArray) > 0)
		{
			$newCount = '0';
			foreach($newHeadCountsArray as $newRepHeadCounts)
			{
				$final_average_head_cou = array_sum($newRepHeadCounts)/count($newRepHeadCounts);
				$newCount += $final_average_head_cou;
			}
			$monthly_average_he_count = $newCount/count($newHeadCountsArray);
		}

		if(isset($monthly_average_he_count) && $monthly_average_he_count!= '')
		{
			$no_of_l_mon = count($arrEmployee);
			$monthly_attr_ra = ($no_of_l_mon/$monthly_average_he_count)*100;
			$tpl->set_var("final_monthly_attrition",round($monthly_attr_ra,2));
			$tpl->set_var("display_average_head_count",round($monthly_average_he_count,2));
		}
*/
		/*
		 * END CALCULATE MONTHLY ATTRITION, REPORTING HEAD SELECTED
		 * */
		 
		 /*
		 * BEGIN CALCULATE YEARLY ATTRITION, NO REPORTING HEAD SELECTED
		 * */
/*
		$countMonth = 0;
		$start_d = $curYear.'-01-01';
		$newOverallHeadCountsArray = array();
		while(strtotime($start_d) <= strtotime($end_d))
		{
			$first_date = date('Y-m-01',strtotime($start_d));
			$last_date = date('Y-m-t',strtotime($first_date));
			
			$realHeadCount = 0;
			
			while (strtotime($first_date) <= strtotime($last_date))
			{
				$daily_reporting_c = $objEmployee->fnGetOverallHeadCount($_SESSION['SearchAttrition']['reporting_head'],$month_start_d);
//echo "<br/>";
//print_r($daily_reporting_c);
				if(count($daily_reporting_c) > 0 )
				{
					foreach($daily_reporting_c as $reporting)
					{
//echo "<br/>reporting : ".$reporting;
						$daily_head_c = $objEmployee->fnGetHeadCountUsingUserId($reporting,$first_date);
						if($daily_head_c != '0' && $daily_head_c != '')
						{
							$newOverallHeadCountsArray[$reporting][] = $daily_head_c;
						}
					}
				}
				
				$first_date = date ("Y-m-d", strtotime("+1 day", strtotime($first_date)));
			}
			
			$start_d = date("Y-m-d", strtotime("+1 month", strtotime($start_d)));
			$countMonth++;
		}
		//print_r($newOverallHeadCountsArray);
		if(count($newOverallHeadCountsArray) > 0)
		{
			$newOverallCount = 0;
			foreach($newOverallHeadCountsArray as $newRepHeadCounts)
			{
				echo "<br/>";
				echo "<br/> ------------- last_date_of_month : ".count($newRepHeadCounts);
				echo "<br/> ------------- realHeadCount : ".array_sum($newRepHeadCounts);

				$final_average_head_cou = array_sum($newRepHeadCounts)/count($newRepHeadCounts);
				$newOverallCount += $final_average_head_cou;
			}
			echo "<br/>here";
//print_r($newRepHeadCounts);
			$yearl_ave_he_count = $newOverallCount/count($newOverallHeadCountsArray);
		}
		
		if(isset($yearl_ave_he_count) && $yearl_ave_he_count!= '')
		{
			$no_of_leavers = count($arrEmployeeForYearlyAttrition);
			echo "<hr/>";
			print_r($arrEmployeeForYearlyAttrition);
			echo "<hr/>";
			echo "<br/>no_of_leavers : ".$no_of_leavers = count($arrEmployeeForYearlyAttrition);
			echo "<br/>countMonth : ".$countMonth;
			echo "<br/>yearl_ave_he_count : ".$yearl_ave_he_count;
			if($yearl_ave_he_count != '0')
			{
				$annual_attrition = (($no_of_leavers*(12/$countMonth))/$yearl_ave_he_count)*100;
			}
			else
			{
				$annual_attrition = '0';
			}

			$tpl->set_var("final_annual_attrition",round($annual_attrition,2));
		}
*/
		/*
		 * END CALCULATE YEARLY ATTRITION, NO REPORTING HEAD SELECTED
		 * */

		$month_start_d = $_SESSION["SearchAttrition"]["year"].'-'.$_SESSION["SearchAttrition"]["month"].'-01';
		$daily_reporting_c = $objEmployee->fnGetOverallHeadCount($_SESSION['SearchAttrition']['reporting_head'],$month_start_d);
		if(count($daily_reporting_c) > 0)
		{
			$arrEmployee = $objEmployee->fnGetEmployeesForHrMultipleReportingAttrition($daily_reporting_c,$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"]);
			$arrEmployeeForYearlyAttrition = $objEmployee->fnGetEmployeesForHrMultipleReportingYearlyAttrition($daily_reporting_c,$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"]);
		}
		else
		{
			$arrEmployee = $objEmployee->fnGetEmployeesForHrAttrition($_SESSION["id"],$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"],$_SESSION["SearchAttrition"]["reporting_head"]);
			$arrEmployeeForYearlyAttrition = $objEmployee->fnGetEmployeesForHrMultipleReportingYearlyAttrition($daily_reporting_c,$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"]);
		}

		$daily_reporting_c = $objEmployee->fnGetOverallHeadCount($_SESSION['SearchAttrition']['reporting_head'],$month_start_d);
		

		$arrMonthlyCalculation = $arrYearlyCalculation = $arrAverageMonthlyHeadCount = array();
		foreach($_SESSION["SearchAttrition"]["reporting_head"] as $employeeId)
		{
			$arrLeaversTypeCountHeadWise = array("1"=>0, "2"=>0, "3"=>0);
			
			$repo_head_name = $objEmployee->fnGetEmployeeNameById($employeeId);
			$tpl->set_var("reporting_h",$repo_head_name);
			$tpl->set_var("reporting_h_id",$employeeId);

			/* Monthly attrition for each reporting head */
			if((isset($_SESSION["SearchAttrition"]["year"]) && $_SESSION["SearchAttrition"]["year"] != '') || (isset($_SESSION["SearchAttrition"]["month"]) && $_SESSION["SearchAttrition"]["month"] != ''))
			{
				$month_start_date = $_SESSION["SearchAttrition"]["year"].'-'.$_SESSION["SearchAttrition"]["month"].'-01';
				$month_end_date = $_SESSION["SearchAttrition"]["year"].'-'.$_SESSION["SearchAttrition"]["month"].'-'.date('t',strtotime($month_start_date));

				$realMonthlyHeadCount = 0;

				while(strtotime($month_start_date) <= strtotime($month_end_date))
				{
					$daily_head_c = $objEmployee->fnGetHeadCountUsingUserId($employeeId,$month_start_date);
					$realMonthlyHeadCount += $daily_head_c;
					$month_start_date = date ("Y-m-d", strtotime("+1 day", strtotime($month_start_date)));
				}
			}

			if(count($realMonthlyHeadCount) > 0)
			{
				$last_d_m = date('t',strtotime($month_end_date));
				$final_m_h_count = $realMonthlyHeadCount/$last_d_m;
				
				if(in_array($employeeId,$daily_reporting_c))
				{
					$arrAverageMonthlyHeadCount[$employeeId] = $final_m_h_count;
				}
			}
				
			if(isset($final_m_h_count))
			{
				/* Leavers for current head */
				$arrLeaversForCurrentHead = $objEmployee->fnGetEmployeesForHrMultipleReportingAttrition(array($employeeId),$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"]);
				$no_of_l_month = count($arrLeaversForCurrentHead);

				if(count($arrLeaversForCurrentHead) > 0)
				{
					foreach($arrLeaversForCurrentHead as $curLeaversForCurrentHead)
					{
						if(isset($curLeaversForCurrentHead["terminated_absconding_resigned"]) && isset($arrLeaversTypeCountHeadWise[$curLeaversForCurrentHead["terminated_absconding_resigned"]]))
							$arrLeaversTypeCountHeadWise[$curLeaversForCurrentHead["terminated_absconding_resigned"]]++;
					}
				}

				$monthly_attr_cal = 0;
				if($final_m_h_count != '0' && $final_m_h_count != '')
				{
					$monthly_attr_cal = ($no_of_l_month/$final_m_h_count)*100;
				}
				if(in_array($employeeId,$daily_reporting_c))
				{
					$arrMonthlyCalculation[$employeeId] = $monthly_attr_cal;
				}

				$tpl->set_var("month_attr",round($monthly_attr_cal,2));
			}
			else
			{
				$tpl->set_var("month_attr",'0');
			}

			$tpl->set_var("FillLeaversTypeDetailsForHeadsBlock","");
			foreach($arrLeaversTypeCountHeadWise as $k => $curLeaversTypeCountHeadWise)
			{
				$tpl->set_var("leavers_type_id", $k);
				$tpl->set_var("leavers_type_counts_for_heads", $curLeaversTypeCountHeadWise);
				$tpl->parse("FillLeaversTypeDetailsForHeadsBlock",true);
			}
			
			/* Yearly attrition for All reproting heads */
			$arrayHeadCount = array();

			$start_d = $curYear.'-01-01';
			while(strtotime($start_d) <= strtotime($end_d))
			{
				$first_date = date('Y-m-01',strtotime($start_d));
				$last_date = date('Y-m-t',strtotime($first_date));
				$realHeadCount = array();
				while (strtotime($first_date) <= strtotime($last_date))
				{
					$daily_head_count = $objEmployee->fnGetHeadCountUsingUserId($employeeId,$first_date);
					if($daily_head_count != 0 && $daily_head_count != '')
						$realHeadCount[] = $daily_head_count;
					$first_date = date ("Y-m-d", strtotime("+1 day", strtotime($first_date)));
				}

				$last_date_of_month = date('t',strtotime($last_date));
				$average_head_count = 0;
				if(count($realHeadCount) > 0)
					$average_head_count = array_sum($realHeadCount)/count($realHeadCount);
				if($average_head_count != '' && $average_head_count != '0')
				{
					$arrayHeadCount[] = $average_head_count;
				}
				
				if(count($arrayHeadCount) > 0)
				{
					$final_Head_count = array_sum($arrayHeadCount)/count($arrayHeadCount);
				}
				$start_d = date("Y-m-d", strtotime("+1 month", strtotime($start_d)));
			}

			$no_of_months_till_month = count($arrayHeadCount);
			if(isset($final_Head_count) && $final_Head_count != '0' && $no_of_months_till_month != '0')
			{
				$arrCurHeadEmployeeForYearlyAttrition = $objEmployee->fnGetEmployeesForHrMultipleReportingYearlyAttrition(array($employeeId),$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"]);
				
				$no_of_leavers = count($arrCurHeadEmployeeForYearlyAttrition);
				
				$no_of_leavers = count($arrCurHeadEmployeeForYearlyAttrition);
				
				$no_of_leavers = count($arrCurHeadEmployeeForYearlyAttrition);
				$annual_attrition = (($no_of_leavers*(12/$no_of_months_till_month))/$final_Head_count)*100;
				$tpl->set_var("yearly_attrition_till_date_text", date('F, Y',strtotime($end_d)));
				$tpl->set_var("year_attr",round($annual_attrition,2));

				if(in_array($employeeId,$daily_reporting_c))
					$arrYearlyCalculation[$employeeId] = $annual_attrition;
			}
			else
			{
				$tpl->set_var("year_attr","00.00");
			}
			$tpl->parse("MutilpleTeamleaderData",true);
		}
		$tpl->parse("MultipleTeamleaderBlock",true);

		/* Set monthly and yearly counts */
		$total_avg_monthly = $total_avg_yearly = $total_avg_monthly_headcount = 0;
		if(isset($arrMonthlyCalculation) && count($arrMonthlyCalculation) > 0)
		{
			$total_avg_monthly  = array_sum($arrMonthlyCalculation) / count($arrMonthlyCalculation);
		}

		$tpl->set_var("final_monthly_attrition",round($total_avg_monthly,2));

		if(isset($arrYearlyCalculation) && count($arrYearlyCalculation) > 0)
		{
			$total_avg_yearly  = array_sum($arrYearlyCalculation) / count($arrYearlyCalculation);
		}

		$tpl->set_var("final_annual_attrition",round($total_avg_yearly,2));

		if(isset($arrAverageMonthlyHeadCount) && count($arrAverageMonthlyHeadCount) > 0)
		{
			$total_avg_monthly_headcount  = array_sum($arrAverageMonthlyHeadCount) / count($arrAverageMonthlyHeadCount);
		}

		$tpl->set_var("display_average_head_count",round($total_avg_monthly_headcount,2));
	}

	/* Fill dropdown for search for year */
	$tpl->set_var("DisplayYearBlock","");
	if(count($arrYear) > 0)
	{
		foreach($arrYear as $curYr)
		{
			$tpl->set_var("curyr",$curYr);
			$tpl->parse("DisplayYearBlock",true);
		}
	}

	/* Display the list of leavers */
	$tpl->set_var("DisplayEmployeeBlog","");

	$employeeCount = array('1'=>'0','2'=>'0','3'=>'0','4'=>'0');
	$employeeTypeCount = array('1'=>'0','2'=>'0','3'=>'0');
	$tpl->set_var("total_leavers_count", count($arrEmployee));
	if(count($arrEmployee) > 0)
	{
		foreach($arrEmployee as $employees)
		{
			$tpl->set_var("doj",$employees['date_of_joining']);
			$tpl->set_var("e_code",$employees['employee_code']);
			$tpl->set_var("e_name",$employees['name']);
			if($employees['months_worked'] < '3')
			{
				$employeeCount['1'] = $employeeCount['1'] + 1;
			}
			else if($employees['months_worked']	>= '3' && $employees['months_worked'] < '6')
			{
				$employeeCount['2'] = $employeeCount['2'] + 1;
			}
			else if($employees['months_worked'] >= '6' && $employees['months_worked'] < '12')
			{
				$employeeCount['3'] = $employeeCount['3'] + 1;
			}
			else
			{
				$employeeCount['4'] = $employeeCount['4'] + 1;
			}

			$tpl->set_var("date_of_leaving",$employees['rel_date_by_manager']);
			
			$tpl->set_var("termination_resi","");
			if(isset($employees['terminated_absconding_resigned']) && isset($arrTerm[$employees['terminated_absconding_resigned']]))
				$tpl->set_var("termination_resi",$arrTerm[$employees['terminated_absconding_resigned']]);

			if(isset($employeeTypeCount[$employees['terminated_absconding_resigned']]))
				$employeeTypeCount[$employees['terminated_absconding_resigned']]++;

			$tpl->set_var("reason_leav",$employees['reason_of_leaving']);
			$tpl->set_var("no_of_month_work",$employees['months_worked']);
			$tpl->parse("DisplayEmployeeBlog",true);
		}
	}
	$tpl->set_var("month_work1",$employeeCount['1']);
	$tpl->set_var("month_work2",$employeeCount['2']);
	$tpl->set_var("month_work3",$employeeCount['3']);
	$tpl->set_var("month_work4",$employeeCount['4']);

	$tpl->set_var("FillReportingHeadsBlock","");

	/* Fetch total joiners for the month */
	$tpl->set_var("DisplayJoinersBlog","");
	$arrJoiners = $objEmployee->fnGetJoinersForMonth($_SESSION["SearchAttrition"]["reporting_head"],$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"]);

	$tpl->set_var("total_joiners_count",count($arrJoiners));
	if(count($arrJoiners) > 0)
	{
		foreach($arrJoiners as $curJoiner)
		{
			$tpl->set_var("joiners_doj", $curJoiner["date_of_joining"]);
			$tpl->set_var("joiners_e_code", $curJoiner["employee_code"]);
			$tpl->set_var("joiners_e_name", $curJoiner["name"]);
			
			$tpl->parse("DisplayJoinersBlog",true);
		}
	}
	
	/* Fetch total joinees till date */
	$arrJoiners = $objEmployee->fnGetJoinersYTD($_SESSION["SearchAttrition"]["reporting_head"],$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"]);
	$tpl->set_var("display_total_joinees_till_date",count($arrJoiners));
	
	$tpl->set_var("FillLeaversTypeDetailsBlock","");
	foreach($employeeTypeCount as $k => $curType)
	{
		$tpl->set_var("leavers_type_count_id", $k);
		$tpl->set_var("leavers_type_count", $curType);
		$tpl->parse("FillLeaversTypeDetailsBlock",true);
	}

	/* Reporting heads for search */
	$getAllReporintHeads = $objAttendance->fnGetEmployees($curDate);
	if(count($getAllReporintHeads) > 0)
	{
		foreach($getAllReporintHeads as $reporting_head)
		{
			$tpl->set_var("reporting_head_id", $reporting_head["employee_id"]);
			$tpl->set_var("reporting_head_title", $reporting_head["employee_name"]);

			$selected_heads = "";
			if(isset($_SESSION['SearchAttrition']['reporting_head']) && $_SESSION['SearchAttrition']['reporting_head'] != '0')
			{
				if(in_array($reporting_head["employee_id"],$_SESSION['SearchAttrition']['reporting_head']))
					$selected_heads = "selected='selected'";

				$tpl->set_var("selected_heads", $selected_heads);
			}

			$tpl->parse("FillReportingHeadsBlock",true);
		}
	}

	$tpl->pparse('main',false);

?>

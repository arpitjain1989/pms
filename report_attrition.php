<?php 

	include('common.php');
	$tpl = new Template($app_path);

	ini_set("max_execution_time",0);

	$tpl->load_file('template.html','main');
	$tpl->load_file('report_attrition.html','main_container');

	$PageIdentifier = "ReportAttrition";
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

	include_once('includes/class.attrition.php');
	$objAttrition = new attrition();

	include_once('includes/class.employee.php');
	$objEmployee = new employee();

	if(isset($_POST["action"]) && trim($_POST["action"]) == "AttritionSearch")
	{
		if($_POST["year"]."-".$_POST["month"] < '2014-01')
		{
			header("Location: report_attrition.php?info=nod");
			exit;
		}
		else if($_POST["year"]."-".$_POST["month"] > Date('Y-m'))
		{
			header("Location: report_attrition.php?info=errp");
			exit;
		}

		$_SESSION["SearchAttrition"]["month"] = $_POST["month"];
		$_SESSION["SearchAttrition"]["year"] = $_POST["year"];
		$_SESSION["SearchAttrition"]["reporting_head"] = $_POST["reporting_head"];
		$_SESSION["SearchAttrition"]["issingle"] = false;
		
		header("Location: report_attrition.php");
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
	$tpl->set_var("DisplayReportingHeadBlock","");

	$arrReportingHead = $objEmployee->fnTeamLeaderExistForManager($_SESSION['id']);
	
	$arrTl = array();
	$tpl->set_var("FillReportingHeads","");
	if(count($arrReportingHead) > 0 )
	{
		foreach($arrReportingHead as $curReportingHead)
		{
			if(isset($curReportingHead['tl_id']))
			{
				array_push($arrTl,$curReportingHead['tl_id']);
			}
			$tpl->set_var("reporting_head_id",$curReportingHead["tl_id"]);
			$tpl->set_var("reporting_head_name",$curReportingHead["tlName"]);
			
			$tpl->parse("FillReportingHeads",true);
		}
		$tpl->parse("DisplayReportingHeadBlock",false);
	}

	if(count($arrTl) > 0)
	{
		$_SESSION["SearchAttrition"]["tls"] = $arrTl;
	}
	$_SESSION["SearchAttrition"]["tls"][] = $_SESSION['id'];


	if(isset($_SESSION["SearchAttrition"]["reporting_head"]) && $_SESSION["SearchAttrition"]["reporting_head"] == '')
	{
		$_SESSION["SearchAttrition"]["reporting_head"] = '0';
	}
	
	$arrEmployee = $objEmployee->fnGetEmployeesForAttrition($_SESSION["id"],$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"],$_SESSION["SearchAttrition"]["reporting_head"]);

	$arrEmployeeMonthly = $objEmployee->fnGetEmployeesForMonthlyAttrition($_SESSION["id"],$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"],$_SESSION["SearchAttrition"]["reporting_head"]);

	$arrEmployeeForAttritionUsingMonth = $objEmployee->fnGetEmployeesForAttritionBetweenMonths($_SESSION["id"],$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"],$_SESSION["SearchAttrition"]["reporting_head"]);
	
	

	$curMonth = date('m');
	$curYear = date('Y');
	

	$start = $_SESSION["SearchAttrition"]["year"].'-'.$_SESSION["SearchAttrition"]["month"].'-01';
	$tpl->set_var("leavers_for",date('F, Y', strtotime($start)));
	$tpl->set_var("joiners_for",date('F, Y', strtotime($start)));
	$tpl->set_var("ytd_joinees_till_date_text",date('F, Y', strtotime($start)));
	$end = $curYear.'-'.$curMonth.'-01';
	$start = explode ("-", $start);
	$end = explode ("-", $end);
 
	$diff = 12*($end[0]-$start[0]);
	$diff -= ($start[1]-$end[1]);
	$actual_diff_month = $diff + 1;
	
	$end_d = $_SESSION["SearchAttrition"]["year"].'-'.$_SESSION["SearchAttrition"]["month"].'-01';
	$start_d = $curYear.'-01-01';

	/* For monthly attrition calculation */
	$realMonthlyHeadCount = '0';
	$final_m_h_count = '0';
	if((isset($_SESSION["SearchAttrition"]["year"]) && $_SESSION["SearchAttrition"]["year"] != '') || (isset($_SESSION["SearchAttrition"]["month"]) && $_SESSION["SearchAttrition"]["month"] != ''))
	{
		$month_start_date = $_SESSION["SearchAttrition"]["year"].'-'.$_SESSION["SearchAttrition"]["month"].'-01';
		$month_end_date = $_SESSION["SearchAttrition"]["year"].'-'.$_SESSION["SearchAttrition"]["month"].'-'.date('t',strtotime($month_start_date));

		//echo '<br>month_start_date:'.$month_start_date.'<br>month_end_date:'.$month_end_date;
		while (strtotime($month_start_date) <= strtotime($month_end_date))
		{
			//echo '<br>first_date:'.$month_start_date;
			//echo $_SESSION["SearchAttrition"]['reporting_head'];
			if($_SESSION["SearchAttrition"]['reporting_head'] != '' && $_SESSION["SearchAttrition"]['reporting_head'] != 'all' && $_SESSION["SearchAttrition"]['reporting_head'] != '0')
			{
				//echo 'hello'; die;
				$daily_head_c = $objEmployee->fnGetHeadCountUsingUserId($_SESSION["SearchAttrition"]['reporting_head'],$month_start_date);
			}
			else
			{
				//echo 'hello1'; die;
				$daily_head_c = $objEmployee->fnGetHeadCountUsingUserId($_SESSION['id'],$month_start_date);
			}

			//echo '<br>daily_head_count:'.$daily_head_c; 
			$realMonthlyHeadCount += $daily_head_c;
			//echo '<br>realMonthlyHeadCount:'.$realMonthlyHeadCount;die;
			$month_start_date = date ("Y-m-d", strtotime("+1 day", strtotime($month_start_date)));
		}
		//echo '<br>realMonthlyHeadCount:'.$realMonthlyHeadCount; die;
		if(count($realMonthlyHeadCount) > 0)
		{
			//echo '<br>--------here<br>';
			$last_d_m = date('t',strtotime($month_end_date));
			$final_m_h_count = $realMonthlyHeadCount/$last_d_m;
		}
		
			
			
		if(isset($final_m_h_count) && $final_m_h_count != '')
		{
			$no_of_l_month = count($arrEmployee);
			//echo '<br>final_m_h_count'.$final_m_h_count;
			//echo '<br>no_of_l_month'.$no_of_l_month;
			$monthly_attr_cal = ($no_of_l_month/$final_m_h_count)*100;
			//echo '<br>monthly_attr_cal:'.$monthly_attr_cal;
			$tpl->set_var("final_monthly_attrition",round($monthly_attr_cal,2));
			$tpl->set_var("display_average_head_count",round($final_m_h_count,2));
		}
		else
		{
			$tpl->set_var("final_monthly_attrition","00.00");
			$tpl->set_var("display_average_head_count",0);
		}
		//die;
		//$month_head_count = $objEmployee->fnGetHeadCountForMonthUsingUserId($_SESSION["SearchAttrition"]["reporting_head"],$_SESSION["SearchAttrition"]["year"],$_SESSION["SearchAttrition"]["month"]);
	}
	//$daily_head_count = $objEmployee->fnGetHeadCountUsingUserId($_SESSION['id'],$first_date);
	
	$arrayHeadCount = array();
	
	while(strtotime($start_d) <= strtotime($end_d))
	{
		$month_f = date ("m", strtotime($start_d));
		//$first_date = $_SESSION["SearchAttrition"]["year"].'-'.$month_f.'-01';
		//echo '<br>first_date:'.$first_date = '2014-3-01';
		$first_date = $_SESSION["SearchAttrition"]["year"].'-'.$month_f.'-01';
		$last_date = $_SESSION["SearchAttrition"]["year"].'-'.$month_f.'-'.date('t',strtotime($first_date));
		//echo '<br>-------------------------<br>first_date:'.$first_date.'<br>last_date:'.$last_date.'<br>----------------------';
		$realHeadCount = '';
		
		while (strtotime($first_date) <= strtotime($last_date))
		{
			//echo '<br>first_date:'.$first_date;
			//echo $_SESSION["SearchAttrition"]['reporting_head'];
			if($_SESSION["SearchAttrition"]['reporting_head'] != '' && $_SESSION["SearchAttrition"]['reporting_head'] != '0' && $_SESSION["SearchAttrition"]['reporting_head'] != 'all')
			{
				$daily_head_count = $objEmployee->fnGetHeadCountUsingUserId($_SESSION["SearchAttrition"]['reporting_head'],$first_date);
			}
			else
			{
				$daily_head_count = $objEmployee->fnGetHeadCountUsingUserId($_SESSION['id'],$first_date);
			}
			
			//echo '<br>daily_head_count:'.$daily_head_count;
			$realHeadCount += $daily_head_count;
			$first_date = date ("Y-m-d", strtotime("+1 day", strtotime($first_date)));
		}
		//echo '<br>realHeadCount:'.$realHeadCount;
		$last_date_of_month = date('t',strtotime($last_date));
		$tpl->set_var("yearly_attrition_till_date_text", date('F, Y',strtotime($last_date)));
		$average_head_count = $realHeadCount/$last_date_of_month;
		if($average_head_count != '')
		{
			$arrayHeadCount[] = $average_head_count;
		}
		if(count($arrayHeadCount) > 0)
		{
			//echo '<br>--------here<br>';
			$final_Head_count = array_sum($arrayHeadCount)/count($arrayHeadCount);
		}
		/*echo '<br>last_date_of_month:'.$last_date_of_month.'<br>average_head_count:'.$average_head_count;
		echo '<pre>'; print_r($arrayHeadCount);
		echo '<br>final_Head_count:'.$final_Head_count;
		echo "<br>month_f:$month_f\n";*/
		$start_d = date("Y-m-d", strtotime("+1 month", strtotime($start_d)));
		$first_date = date("Y-m-d", strtotime("+1 month", strtotime($first_date)));
	}
	//echo '<br>final_Head_count--------'.$final_Head_count;
	if(isset($final_Head_count) && $final_Head_count != '0')
	{
		$no_of_leavers = count($arrEmployeeForAttritionUsingMonth);
		$no_of_months_till_month = count($arrayHeadCount);
		$annual_attrition = (($no_of_leavers*(12/$no_of_months_till_month))/$final_Head_count)*100;
		$tpl->set_var("final_annual_attrition",round($annual_attrition,2));
	}
	else
	{
		$tpl->set_var("final_annual_attrition","00.00");
	}
	
	//echo '<pre>';print_r($arrEmployee);
	$tpl->set_var("DisplayYearBlock","");
	if(count($arrYear) > 0)
	{
		foreach($arrYear as $curYr)
		{
			$tpl->set_var("curyr",$curYr);
			$tpl->parse("DisplayYearBlock",true);
		}
	}

	$tpl->set_var("DisplayEmployeeBlog","");

	$employeeCount = array('1'=>'0','2'=>'0','3'=>'0','4'=>'0');
	$employeeTypeCount = array('1'=>'0','2'=>'0','3'=>'0');
	$arrTerm = array("1"=>"Terminated","2"=>"Absconding","3"=>"Resigned");
	$tpl->set_var("total_leavers_count", count($arrEmployee));
	if(count($arrEmployee) > 0)
	{
		//echo '<pre>'; print_r($arrEmployee);
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
	
	$tpl->set_var("FillLeaversTypeBlock","");
	foreach($arrTerm as $curTerm)
	{
		$tpl->set_var("leavers_type_text", $curTerm);
		$tpl->parse("FillLeaversTypeBlock", true);
	}

	$tpl->set_var("FillLeaversTypeDetailsBlock","");
	foreach($employeeTypeCount as $k => $curType)
	{
		$tpl->set_var("leavers_type_count_id", $k);
		$tpl->set_var("leavers_type_count", $curType);
		$tpl->parse("FillLeaversTypeDetailsBlock",true);
	}

	/* Fetch total joiners for the month */
	$tpl->set_var("DisplayJoinersBlog","");

	$r_head = array($_SESSION["id"]);
	if(isset($_SESSION["SearchAttrition"]["reporting_head"]) && $_SESSION["SearchAttrition"]["reporting_head"] != 'all' && $_SESSION["SearchAttrition"]["reporting_head"] != '0')
		$r_head = array($_SESSION["SearchAttrition"]["reporting_head"]);

	$arrJoiners = $objEmployee->fnGetJoinersForMonth($r_head,$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"]);

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
	$arrJoiners = $objEmployee->fnGetJoinersYTD($r_head,$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"]);
	$tpl->set_var("display_total_joinees_till_date",count($arrJoiners));
	
	$tpl->pparse('main',false);
?>

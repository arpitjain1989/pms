<?php 
	include('common.php');
	$tpl = new Template($app_path);

	ini_set("max_execution_time",0);

	$tpl->load_file('template.html','main');
	$tpl->load_file('rep_attrition_list.html','main_container');

	$PageIdentifier = "HrReportAttrition";
	include_once('userrights.php');

	$curDate = Date('Y-m-d');
	$curYear = Date('Y');

	$arrYear = array($curYear+1, $curYear, $curYear-1);

	$tpl->set_var("mainheading","Attrition Report");
	$breadcrumb = '<li class="active">Attrition Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.employee.php');
	$objEmployee = new employee();
	//echo '<pre>'; print_r($_SESSION); die;
	/*if(isset($_POST["action"]) && trim($_POST["action"]) == "AttritionSearch")
	{
		$_SESSION["SearchAttrition"]["month"] = $_POST["month"];
		$_SESSION["SearchAttrition"]["year"] = $_POST["year"];
		$_SESSION["SearchAttrition"]["reporting_head"] = $_POST["reporting_head"];
		$_SESSION["SearchAttrition"]["team_member"] = $_POST["team_member"];
		$_SESSION["SearchAttrition"]["shiftid"] = $_POST["shiftid"];
		$_SESSION["SearchAttrition"]["issingle"] = false;


		header("Location: rep_attrition.php");
		exit;
	}*/

	
	//echo 'hello'; print_r($_SESSION);
	/*if(!isset($_SESSION["SearchAttrition"]["start_date"]))
		$_SESSION["SearchAttrition"]["start_date"] = $curDate;
	if(!isset($_SESSION["SearchAttrition"]["end_date"]))
		$_SESSION["SearchAttrition"]["end_date"] = $curDate;*/
		
	if(!isset($_SESSION["SearchAttrition"]["month"]))
		$_SESSION["SearchAttrition"]["month"] = Date('m');
	if(!isset($_SESSION["SearchAttrition"]["year"]))
		$_SESSION["SearchAttrition"]["year"] = $curYear;
	
	/*if(isset($_SESSION["SearchAttrition"]["start_date"]))
		$tpl->set_var("start_date", $_SESSION["SearchAttrition"]["start_date"]);
	if(isset($_SESSION["SearchAttrition"]["end_date"]))
		$tpl->set_var("end_date", $_SESSION["SearchAttrition"]["end_date"]);*/

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

	
	/*if(isset($_SESSION["SearchAttrition"]["agents"]))
		$tpl->set_var("agents", $_SESSION["SearchAttrition"]["agents"]);
	else
		$_SESSION["SearchAttrition"]["agents"] = 0;*/
		
		
	$tpl->set_var("DisplayReportingHeadHiddenBlock","");
	$tpl->set_var("DisplayReportingHeadBlock","");
	//echo 'here<pre>'; print_r($_SESSION); die;
	$arrReportingHead = $objEmployee->fnTeamLeaderExistForManager($_SESSION['id']);
	//echo '<br>arrReportingHead:'.$arrReportingHead;
	//echo '<pre>'; print_r($_SESSION);
	
	
	/*if(count($arrTl) > 0)
	{
		$_SESSION["SearchAttrition"]["tls"] = $arrTl;
	}*/
	

	if(isset($_SESSION["SearchAttrition"]["reporting_head"]) && $_SESSION["SearchAttrition"]["reporting_head"] == '')
	{
		$_SESSION["SearchAttrition"]["reporting_head"] = '0';
	}
	//echo $_SESSION["SearchAttrition"]['reporting_head']; die;
	/*if($_SESSION["SearchAttrition"]['reporting_head'] != '')
	{
		echo $_SESSION["SearchAttrition"]['reporting_head']; die;
	}*/
	
	$arrEmployee = $objEmployee->fnGetEmployeesForYearlyHrAttrition($_SESSION["id"],$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"],$_SESSION["SearchAttrition"]["reporting_head"]);

	$tpl->set_var("DisplayEmployeeBlog","");

	$employeeCount = array('1'=>'0','2'=>'0','3'=>'0','4'=>'0');
	if(count($arrEmployee) > 0)
	{
		$arrTerm = array("0"=>"-","1"=>"Terminated","2"=>"Absconding","3"=>"Resigned");
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
			if(isset($employees['terminated_absconding_resigned']))
				$tpl->set_var("termination_resi",$arrTerm[$employees['terminated_absconding_resigned']]);
			$tpl->set_var("reason_leav",$employees['reason_of_leaving']);
			$tpl->set_var("no_of_month_work",$employees['months_worked']);
			$tpl->parse("DisplayEmployeeBlog",true);
		}
	}
	
	$tpl->set_var("month_work1",$employeeCount['1']);
	$tpl->set_var("month_work2",$employeeCount['2']);
	$tpl->set_var("month_work3",$employeeCount['3']);
	$tpl->set_var("month_work4",$employeeCount['4']);

	$tpl->pparse('main',false);
?>

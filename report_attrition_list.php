<?php 
	include('common.php');
	$tpl = new Template($app_path);

	ini_set("max_execution_time",0);

	$tpl->load_file('template.html','main');
	$tpl->load_file('report_attrition_list.html','main_container');

	$PageIdentifier = "ReportAttrition";
	include_once('userrights.php');

	$curDate = Date('Y-m-d');
	$curYear = Date('Y');

	$arrYear = array($curYear+1, $curYear, $curYear-1);

	$tpl->set_var("mainheading","Attrition Report");
	$breadcrumb = '<li class="active">Attrition Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.employee.php');
	$objEmployee = new employee();

	$arrEmployee = $objEmployee->fnGetEmployeesForAttritionBetweenMonths($_SESSION["id"],$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"],$_SESSION["SearchAttrition"]["reporting_head"]);

	$tpl->set_var("DisplayEmployeeBlog","");

	$employeeCount = array('1'=>'0','2'=>'0','3'=>'0','4'=>'0');
	if(count($arrEmployee) > 0)
	{
		$arrTerm = array("0"=>"-","1"=>"Terminated","2"=>"Absconding","3"=>"Resigned");

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

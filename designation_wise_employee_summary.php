<?php 
	
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('designation_wise_employee_summary.html','main_container');

	$PageIdentifier = "DesignationWiseEmployeeSummary";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Designation wise employee summary report");
	$breadcrumb = '<li class="active">Designation wise employee summary report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.employee.php');
	
	$objEmployee = new employee();

	$arrDesignationWiseEmployees = $objEmployee->fnFetchActiveEmployeesForDesignation();
	
	/* Export designation wise employee summery */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "export")
	{
		$filename = "DesignationWiseEmployeeSummary_".Date('Y-m-d_H-i').".xls";

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");;
		header("Content-Disposition: attachment;filename=".$filename);
		header("Content-Transfer-Encoding: binary ");
		
		xlsBOF();
		
		xlsWriteLabel(0,0,"Designation Wise Employee Summary for Date : ".Date('Y-m-d H:i'));
		
		xlsWriteLabel(1,0,"Designation");
		xlsWriteLabel(1,1,"No of Active Employees");
		
		$xlsRow = 2;
		
		if(count($arrDesignationWiseEmployees) > 0)
		{
			foreach($arrDesignationWiseEmployees as $curDesignationWiseEmployees)
			{
				xlsWriteLabel($xlsRow,0,$curDesignationWiseEmployees["designation_title"]);
				xlsWriteLabel($xlsRow,1,$curDesignationWiseEmployees["employee_count"]);

				$xlsRow++;
			}
		}
		else
		{
			xlsWriteLabel($xlsRow,1,"No data found.");
		}

		xlsEOF();
		exit;
	}

	$total_active_employees = 0;
	$tpl->set_var("FillDesignationWiseEmployeesBlock","");
	if(count($arrDesignationWiseEmployees) > 0)
	{
		foreach($arrDesignationWiseEmployees as $curDesignationWiseEmployees)
		{
			$tpl->set_var("designation_id", $curDesignationWiseEmployees["designation_id"]);
			$tpl->set_var("designation_name", $curDesignationWiseEmployees["designation_title"]);
			$tpl->set_var("employee_count", $curDesignationWiseEmployees["employee_count"]);

			$total_active_employees += $curDesignationWiseEmployees["employee_count"];

			$tpl->parse("FillDesignationWiseEmployeesBlock",true);
		}
	}

	$tpl->set_var("total_active_employees", $total_active_employees);

	$tpl->pparse("main",false);

?>

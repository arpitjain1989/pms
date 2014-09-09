<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('employee.html','main_container');

	$PageIdentifier = "Employee";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Employees");
	$breadcrumb = '<li class="active">Manage Employees</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.employee.php');
	include_once('includes/class.departments.php');
	include_once('includes/class.designation.php');
		
	$objEmployee = new employee();
	$arrEmployee = $objEmployee->fnGetAllEmployee();
	
	$objDepartments = new departments();
	$objDesignations = new designations();	

	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST['info']))
	{
		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Employee inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "Employee updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "Employee deleted successfully.";
				break;
			case 'invalid':
				$messageClass = "alert-error";
				$message = "Invalid CSV.";
				break;
			case 'norec':
				$messageClass = "alert-error";
				$message = "No records found.";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
		
		
		if($_REQUEST['info'] == 'upload')
		{
			if(isset($_REQUEST['err']) && trim($_REQUEST['err']) > 0 )
			{
				$tpl->set_var('message',$_REQUEST['err']." employees already exists, Could not upload ".$_REQUEST['err']." employees.");
				$tpl->set_var("message_class",$messageClass);
				$tpl->parse("DisplayMessageBlock",false);	
			}
			else
			{
				$tpl->set_var('message',"Employees updated successfully.");
				$tpl->set_var("message_class",$messageClass);
				$tpl->parse("DisplayMessageBlock",false);
			}
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] = 'delete')
	{
		$deltedepartment = $objEmployee->fnDeleteEmployee($_POST);
		if($deltedepartment)
		{
			header("Location: employee.php?info=delete");
		}
	}
	
	$tpl->set_var("FillEmployeeValues","");

	if(count($arrEmployee) >0)
	{
		foreach($arrEmployee as $arrEmployeevalue)
		{	
			$tpl->SetAllValues($arrEmployeevalue);
			if($arrEmployeevalue['status'] == '0')
			{
				$tpl->set_var("state","Active");
				$tpl->set_var("active","");
			}
			else
			{
				$tpl->set_var("state","De-Active");
				$tpl->set_var("active","activeornot");
			}
			$tpl->parse("FillEmployeeValues",true);
		}
	}
	else
	{
		$tpl->set_var("FillHideDelete","");
		$tpl->set_var("norecord","No records found.");
	}
	
	if(isset($_POST["action"]) && trim($_POST["action"]) == "uploadcsv")
	{
		$filename = $_FILES["clientcsv"]["name"];
		if($filename != "")
		{
			$arrfilename = explode(".", $filename);
			$ext = array_pop($arrfilename);
	
			echo "<pre>";
			if($ext == "csv")
			{
				$row = 0;
				$errcnt = 0;
				
				if (($handle = fopen($_FILES["clientcsv"]["tmp_name"], "r")) !== FALSE) 
				{
					while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
					{
						$arr = array();
						if($row > 0)
						{
							/*$arrdepartment = $objDepartments->fnGetDepartmentIdByName($data[5]);
							$arrdesiognation = $objEmployee->fnGetDesignationName($data[6]);*/
							
							$DepartmentId = $objDepartments->fnGetDepartmentIdByName(trim($data[5]));
							$DesignationId = $objDesignations->fnGetDesignationIdByName(trim($data[6]));
							$EmployeeId = $objEmployee->fnGetEmployeeIdByName(trim($data[7]));
							
							$arr["employee_code"] = ucwords(strtolower(trim($data[0])));
							$arr["name"] = strtolower(trim($data[1]));
							$arr["email"] = strtolower(trim($data[2]));
							$arr["contact"] = ucwords(strtolower(trim($data[3])));
							$arr["address"] = ucwords(strtolower(trim($data[4])));
							$arr["department"] = $DepartmentId;
							$arr["designation"] = $DesignationId;
							$arr["teamleader"] = $EmployeeId;
							$arr["password"] = trim($data[8]);
							if(strtolower(trim($data[9])) == 'active')
							{
								$arr["status"] = '0';
							}
							else if(strtolower(trim($data[9])) == 'deative')
							{
								$arr["status"] = '1';
							}
							$arr["role"] = ucwords(strtolower(trim($data[10])));
							
							if($objEmployee->fnGetEmployeeByEmail(trim($arr["email"])) === 0)
							{
								$objEmployee->fnInsertEmployee($arr);
							}
							else
							{
								$errcnt++;
							}
						}
						$row++;
					}
					fclose($handle);
				}
				
				if($row > 1)
				{
					header("Location: employee.php?info=upload&err=$errcnt");
					exit;
				}
				else
				{
					header("Location: employee.php?info=norec");
					exit;
				}
			}
			else
			{
				header("Location: employee.php?info=invalid");
				exit;
			}
		}
	}
	
	
	
	$tpl->pparse('main',false);
?>

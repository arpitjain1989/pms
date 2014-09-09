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
	
	/*if(isset($_POST["action"]) && trim($_POST["action"]) == "uploadcsv")
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
							
							/*$DepartmentId = $objDepartments->fnGetDepartmentIdByName(trim($data[5]));
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
	}*/
	/******************  Employee upload with other details then previous starting data   ***********************/
	/*if(isset($_POST["action"]) && trim($_POST["action"]) == "uploadcsv")
	{
		$filename = $_FILES["clientcsv"]["name"];
		if($filename != "")
		{
			$arrfilename = explode(".", $filename);
			$ext = array_pop($arrfilename);
	
			//echo "<pre>";
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
							$arr["email"] = strtolower(trim($data[0]));
							$arr["date_of_joining"] = trim($data[1]);
							//$arr["induction"] = trim($data[2]);
							if(strtolower(trim($data[2])) == 'attend')
							{
								$arr["induction"] = '1';
							}
							else if(strtolower(trim($data[2])) == 'pending')
							{
								$arr["induction"] = '2';
							}
							else
							{
								$arr["induction"] = '';
							}
							$arr["father_husband_name"] = trim($data[3]);
							$arr["current_address"] = trim($data[4]);
							$arr["location"] = trim($data[5]);
							$arr["city"] = trim($data[6]);
							$arr["zip"] = trim($data[7]);
							$arr["dob"] = trim($data[8]);
							$arr["emergency_contact_name"] = trim($data[9]);
							$arr["relation"] = trim($data[10]);
							$arr["phone_number"] = trim($data[11]);
							$arr["company_mobile_no"] = trim($data[12]);
							$arr["official_email"] = trim($data[13]);
							$arr["qualification"] = trim($data[14]);
							//echo '<br>Gender:'.trim($data[15]);
							if(trim($data[15]) == 'Male')
							{
								$arr["gender"] = '1';
							}
							else if(trim($data[15]) == 'Female')
							{
								$arr["gender"] = '2';
							}
							$arr["experience"] = trim($data[16]);
							$arr["old_designation"] = trim($data[17]);
							//$arr["id_card"] = trim($data[18]);
							if(strtolower(trim($data[18])) == 'Issued')
							{
								$arr["id_card"] = '1';
							}
							else if(strtolower(trim($data[18])) == 'not issued')
							{
								$arr["id_card"] = '2';
							}
							else
							{
								$arr["id_card"] = '';
							}
							$arr["blood_group"] = trim($data[19]);
							$arr["current_salary_ctc"] = trim($data[20]);
							$arr["start_ctc"] = trim($data[21]);
							$arr["retention_bonus_scheme"] = trim($data[22]);

							$arr["retention_amount"] = trim($data[23]);
							//$arr["offer_letter_issued"] = trim($data[25]);
							if(strtolower(trim($data[24])) == 'Yes')
							{
								$arr["offer_letter_issued"] = '1';
							}
							else if(strtolower(trim($data[24])) == 'No')
							{
								$arr["offer_letter_issued"] = '2';
							}
							else
							{
								$arr["offer_letter_issued"] = '';
							}
							$arr["pf_no"] = trim($data[25]);
							$arr["esic_no"] = trim($data[26]);
							$arr["icici_bank_ac_no"] = trim($data[27]);
							//$arr["terminated_absconding_resigned"] = trim($data[29]);
							if(strtolower(trim($data[28])) == 'terminated')
							{
								$arr["terminated_absconding_resigned"] = '1';
							}
							else if(strtolower(trim($data[28])) == 'absconding')
							{
								$arr["terminated_absconding_resigned"] = '2';
							}
							else if(strtolower(trim($data[28])) == 'resigned')
							{
								$arr["terminated_absconding_resigned"] = '3';
							}
							else
							{
								$arr["terminated_absconding_resigned"] = '';
							}
							$arr["date_of_resign_terminate"] = trim($data[29]);
							$arr["relieving_date_by_manager"] = trim($data[30]);
							$arr["reason_of_leaving"] = trim($data[31]);
							//$arr["issues_relieving_offer_letter"] = trim($data[33]);
							if(strtolower(trim($data[32])) == 'yes')
							{
								$arr["issues_relieving_offer_letter"] = '1';
							}
							else if(strtolower(trim($data[32])) == 'no')
							{
								$arr["issues_relieving_offer_letter"] = '2';
							}
							else
							{
								$arr["issues_relieving_offer_letter"] = '';
							}

							
							//echo '<pre>'; print_r($arr); die;
							//$arr["role"] = ucwords(strtolower(trim($data[10])));
							$id = $objEmployee->fnGetEmployeeByEmail(trim($arr["email"]));
							$arr["id"] = $id;
							//echo '<br>email'.$arr["email"];
							if($objEmployee->fnGetEmployeeByEmail(trim($arr["email"])) === 0)
							{
								//echo '<br>hello2';
								//$objEmployee->fnInsertEmployee($arr);
								//$objEmployee->fnUpdateEmployeeByExcel($arr);
								exit;
							}
							else
							{
								//$objEmployee->fnUpdateEmployee($arr);
								$objEmployee->fnUpdateEmployeeByExcel($arr);
								//$errcnt++;
							}
						}
						$row++;
					}
					//die;
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
				//echo 'hello1';
				header("Location: employee.php?info=invalid");
				exit;
			}
		}
	}*/


	/***************  Upload left employee relieving dates  **********************/
	if(isset($_POST["action"]) && trim($_POST["action"]) == "uploadcsv")
	{
		$filename = $_FILES["clientcsv"]["name"];
		if($filename != "")
		{
			$arrfilename = explode(".", $filename);
			$ext = array_pop($arrfilename);
	
			//echo "<pre>";
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
							$arr["employee_code"] = strtolower(trim($data[0]));
							$arr["name"] = strtolower(trim($data[1]));
							$arr["relieving_date_by_manager"] = strtolower(trim($data[2]));
							$arr["status"] = 1;

							$id = $objEmployee->fnGetEmployeeByEmployeeCode(trim($arr["employee_code"]));
							$arr["id"] = $id;
							
							//echo '<br>email'.$arr["email"];
							if($objEmployee->fnGetEmployeeByEmployeeCode(trim($arr["employee_code"])) === 0)
							{
								//echo '<br>hello2';
								//$objEmployee->fnInsertEmployee($arr);
								//$objEmployee->fnUpdateEmployeeByExcel($arr);
								exit;
							}
							else
							{
								//$objEmployee->fnUpdateEmployee($arr);
								$objEmployee->fnUpdateEmployeeUpdateLastDate($arr);
								//$errcnt++;
							}
						}
						$row++;
					}
					//die;
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
				//echo 'hello1';
				header("Location: employee.php?info=invalid");
				exit;
			}
		}
	}
	
	
	
	$tpl->pparse('main',false);
?>

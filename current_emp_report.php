<?php 

	include('common.php');
	$tpl = new Template($app_path);

	ini_set("max_execution_time",300);

	$tpl->load_file('template.html','main');
	$tpl->load_file('current_emp_report.html','main_container');

	$PageIdentifier = "CurrentEmployee";
	include_once('userrights.php');

	
	$tpl->set_var("mainheading","Employee Master");
	$breadcrumb = '<li class="active">Employee Master</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.employee.php');
	include_once('includes/class.designation.php');
	
	$objEmployee = new employee();
	$objDesignation = new designations();

	$arrEmployeeAllData = $objEmployee->fnGetAllActiveEmployeeDetails();
	
	$tpl->set_var("FillEmployeeDetails","");
	foreach($arrEmployeeAllData as $arrEmployee)
	{
		if(isset($arrEmployee['status']) && $arrEmployee['status'] == '0')
		{
			$tpl->set_var("stat","Active");
		}
		else
		{
			$tpl->set_var("stat","Active");
		}
		
		if(isset($arrEmployee['namaz']) && $arrEmployee['namaz'] == '0')
		{
			$tpl->set_var("namaz_status","No");
		}
		else
		{
			$tpl->set_var("namaz_status","Yes");
		}

		if(isset($arrEmployee['gender']))
		{
			if($arrEmployee['gender'] == '1')
			{
				$tpl->set_var("emp_gender","Male");
			}
			else if($arrEmployee['gender'] == '2')
			{
				$tpl->set_var("emp_gender","Female");
			}
		}

		if(isset($arrEmployee['id_card']))
		{
			if($arrEmployee['id_card'] == '1')
			{
				$tpl->set_var("emp_id_card","Yes");
			}
			else if($arrEmployee['id_card'] == '2')
			{
				$tpl->set_var("emp_id_card","No");
			}
		}
		if(isset($arrEmployee['retention_bonus_scheme']))
		{
			if($arrEmployee['retention_bonus_scheme'] == '1')
			{
				$tpl->set_var("emp_retention_bonus_scheme","Yes");
			}
			else if($arrEmployee['retention_bonus_scheme'] == '2')
			{
				$tpl->set_var("emp_retention_bonus_scheme","No");
			}
		}

		if(isset($arrEmployee['offer_letter_issued']))
		{
			if($arrEmployee['offer_letter_issued'] == '1')
			{
				$tpl->set_var("emp_offer_letter_issued","Yes");
			}
			else if($arrEmployee['offer_letter_issued'] == '2')
			{
				$tpl->set_var("emp_offer_letter_issued","No");
			}
		}

		if(isset($arrEmployee['terminated_absconding_resigned']))
		{
			if($arrEmployee['terminated_absconding_resigned'] == '1')
			{
				$tpl->set_var("emp_terminated_absconding_resigned","Terminated");
			}
			else if($arrEmployee['terminated_absconding_resigned'] == '2')
			{
				$tpl->set_var("emp_terminated_absconding_resigned","Absconding");
			}
			else if($arrEmployee['terminated_absconding_resigned'] == '3')
			{
				$tpl->set_var("emp_terminated_absconding_resigned","Resigned");
			}
		}

		if(isset($arrEmployee['issues_relieving_offer_letter']))
		{
			if($arrEmployee['issues_relieving_offer_letter'] == '1')
			{
				$tpl->set_var("emp_issues_relieving_offer_letter","Yes");
			}
			else if($arrEmployee['issues_relieving_offer_letter'] == '2')
			{
				$tpl->set_var("emp_issues_relieving_offer_letter","No");
			}
		}
		if(isset($arrEmployee['designation']))
		{
			$nowDesignation = $objDesignation->fnGetDesNameById($arrEmployee['designation']);
			if($nowDesignation)
			{
				$tpl->set_var("NowDesignation",$nowDesignation);
			}
		}
		$tpl->SetAllValues($arrEmployee);
		$tpl->parse("FillEmployeeDetails",true);
	}
	//echo '<pre>'; print_r($arrEmployeeAllData); die;
	/* Export to excel */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "export") 
	{
		$filename = "Current_Employee_List.xls";

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");;
		header("Content-Disposition: attachment;filename=".$filename);
		header("Content-Transfer-Encoding: binary ");
		
		xlsBOF();

		xlsWriteLabel(0,0,"Date of Joining of Transform Solution P. Ltd.");
		xlsWriteLabel(0,1,"New Emp. Code");
		xlsWriteLabel(0,2,"Name");
		xlsWriteLabel(0,3,"Designation");
		xlsWriteLabel(0,4,"Fathers / Husband Name");
		xlsWriteLabel(0,5,"Permanent Address");
		xlsWriteLabel(0,6,"Current Address");
		xlsWriteLabel(0,7,"Location");
		xlsWriteLabel(0,8,"City & Zip");
		xlsWriteLabel(0,9,"Date of Birth");
		xlsWriteLabel(0,10,"Emergency Contact Name");
		xlsWriteLabel(0,11,"Relation");
		xlsWriteLabel(0,12,"Emergency Contact Phone");
		xlsWriteLabel(0,13,"Phone No.");
		xlsWriteLabel(0,14,"Mobile");
		xlsWriteLabel(0,15,"Company Mobile No.");
		xlsWriteLabel(0,16,"personal email id");
		xlsWriteLabel(0,17,"Official Email");
		xlsWriteLabel(0,18,"Qualification");
		xlsWriteLabel(0,19,"Gender");
		xlsWriteLabel(0,20,"Experience");
		xlsWriteLabel(0,21,"Current Designation");
		xlsWriteLabel(0,22,"Old Designation");
		xlsWriteLabel(0,23,"ID Card");
		xlsWriteLabel(0,24,"Blood Group");
		xlsWriteLabel(0,25,"Current Salary / CTC");
		xlsWriteLabel(0,26,"Start CTC");
		xlsWriteLabel(0,27,"10% Deduction or Retention bonus Scheme");
		xlsWriteLabel(0,28,"Retention Amount");
		xlsWriteLabel(0,29,"Employee Reference Details");
		xlsWriteLabel(0,30,"Offer Letter Issued");
		xlsWriteLabel(0,31,"PF NO. GJ/SRT/ 35455");
		xlsWriteLabel(0,32,"ESIC NO.39/31084/90");
		xlsWriteLabel(0,33,"ICICI Bank Account no.");

		$xlsRow = 1;
		if(is_array($arrEmployeeAllData) && count($arrEmployeeAllData) > 0)
		{
			foreach($arrEmployeeAllData as $arrEmployee)
			{
				if(isset($arrEmployee['status']) && $arrEmployee['status'] == '0')
				{
					$stat = "Active";
				}
				else
				{
					$stat = "In-Active";
				}
				$emp_gender = '';
				if(isset($arrEmployee['gender']))
				{
					if($arrEmployee['gender'] == '1')
					{
						$emp_gender = "Male";
					}
					else if($arrEmployee['gender'] == '2')
					{
						$emp_gender = "Female";
					}
				}
				$emp_id_card = "";
				if(isset($arrEmployee['id_card']))
				{
					if($arrEmployee['id_card'] == '1')
					{
						$emp_id_card = "Yes";
					}
					else if($arrEmployee['id_card'] == '2')
					{
						$emp_id_card = "No";
					}
				}
				$emp_retention_bonus_scheme = "";
				if(isset($arrEmployee['retention_bonus_scheme']))
				{
					if($arrEmployee['retention_bonus_scheme'] == '1')
					{
						$emp_retention_bonus_scheme = "Yes";
					}
					else if($arrEmployee['retention_bonus_scheme'] == '2')
					{
						$emp_retention_bonus_scheme = "No";
					}
				}
				$emp_offer_letter_issued = "";
				if(isset($arrEmployee['offer_letter_issued']))
				{
					if($arrEmployee['offer_letter_issued'] == '1')
					{
						$emp_offer_letter_issued = "Yes";
					}
					else if($arrEmployee['offer_letter_issued'] == '2')
					{
						$emp_offer_letter_issued = "No";
					}
				}
				$emp_issues_relieving_offer_letter = "";
				if(isset($arrEmployee['issues_relieving_offer_letter']))
				{
					if($arrEmployee['issues_relieving_offer_letter'] == '1')
					{
						$emp_issues_relieving_offer_letter = "Yes";
					}
					else if($arrEmployee['issues_relieving_offer_letter'] == '2')
					{
						$emp_issues_relieving_offer_letter = "No";
					}
				}
				$nowDesignation = '';
				if(isset($arrEmployee['designation']))
				{
					$nowDesignation = $objDesignation->fnGetDesNameById($arrEmployee['designation']);
				}

				
				xlsWriteLabel($xlsRow,0,$arrEmployee["emp_date_joining"]);
				xlsWriteLabel($xlsRow,1,$arrEmployee["employee_code"]);
				xlsWriteLabel($xlsRow,2,$arrEmployee["name"]);
				xlsWriteLabel($xlsRow,3,$nowDesignation);
				xlsWriteLabel($xlsRow,4,$arrEmployee["father_husband_name"]);
				xlsWriteLabel($xlsRow,5,$arrEmployee["address"]);
				xlsWriteLabel($xlsRow,6,$arrEmployee["current_address"]);
				xlsWriteLabel($xlsRow,7,$arrEmployee["location"]);
				xlsWriteLabel($xlsRow,8,$arrEmployee["city"].' & '.$arrEmployee["zip"]);
				xlsWriteLabel($xlsRow,9,$arrEmployee["emp_dob"]);
				xlsWriteLabel($xlsRow,10,$arrEmployee["emergency_contact_name"]);
				xlsWriteLabel($xlsRow,11,$arrEmployee["relation"]);
				xlsWriteLabel($xlsRow,12,$arrEmployee["emergency_contact"]);
				xlsWriteLabel($xlsRow,13,$arrEmployee["contact"]);
				xlsWriteLabel($xlsRow,14,$arrEmployee["phone_number"]);
				xlsWriteLabel($xlsRow,15,$arrEmployee["company_mobile_no"]);
				xlsWriteLabel($xlsRow,16,$arrEmployee["email"]);
				xlsWriteLabel($xlsRow,17,$arrEmployee["official_email"]);
				xlsWriteLabel($xlsRow,18,$arrEmployee["qualification"]);
				xlsWriteLabel($xlsRow,19,$emp_gender);
				xlsWriteLabel($xlsRow,20,$arrEmployee["experience"]);
				xlsWriteLabel($xlsRow,21,$arrEmployee["curr_designation"]);
				xlsWriteLabel($xlsRow,22,$arrEmployee["old_designation"]);
				xlsWriteLabel($xlsRow,23,$emp_id_card);
				xlsWriteLabel($xlsRow,24,$arrEmployee["blood_group"]);
				xlsWriteLabel($xlsRow,25,$arrEmployee["current_salary_ctc"]);
				xlsWriteLabel($xlsRow,26,$arrEmployee["start_ctc"]);
				xlsWriteLabel($xlsRow,27,$emp_retention_bonus_scheme);
				xlsWriteLabel($xlsRow,28,$arrEmployee["retention_amount"]);
				xlsWriteLabel($xlsRow,29,$arrEmployee["employee_reference_detail"]);
				xlsWriteLabel($xlsRow,30,$emp_issues_relieving_offer_letter);
				xlsWriteLabel($xlsRow,31,$arrEmployee["pf_no"]);
				xlsWriteLabel($xlsRow,32,$arrEmployee["esic_no"]);
				xlsWriteLabel($xlsRow,33,$arrEmployee["icici_bank_ac_no"]);
				

				$xlsRow++;
			}
			
		}
		else
		{
			xlsWriteLabel($xlsRow,1,"No Records");
		}
		xlsEOF();

		exit;
	}

	$tpl->pparse('main',false);
?>

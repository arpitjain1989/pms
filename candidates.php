<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('candidates.html','main_container');

	$PageIdentifier = "Candidates";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Candidates");
	$breadcrumb = '<li class="active">Manage Candidates</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.candidate_list.php');
	include_once('includes/class.rct_source.php');
	include_once('includes/class.rct_division.php');
	include_once('includes/class.employee.php');
	include_once('includes/class.designation.php');
	include_once('includes/class.user_registration.php');
	
	
	$objCandidateList = new candidate_list();
	$arrCandidateList = $objCandidateList->fnGetAllCandidates();
	$objRctSource = new rct_source();
	$objRctDivision = new rct_division();
	$objEmployee = new employee();
	$objDesignation = new designations();
	$objUserRegistration = new user_registration();

	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");


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
							if(strtolower(trim($data[2])) == '1')
							{
								$arr["rctsource"] = '0';
								$reference_id = $objEmployee->fnGetEmployeeIdByName(trim($data[3]));
								$arr['reference_trans'] = $reference_id;
							}
							else
							{
									$source_name = $objRctSource->fnGetSourceIdByName(trim($data[3]));
									$arr["rctsource"] = $source_name;
									$arr['reference_trans'] = '0';
							}

							if(trim($data[4]) != '')
							{
								$division_id = $objRctDivision->fnGetDivisionIdByName(trim($data[4]));
								$arr["dev_id"] = $division_id;
							}
							else
							{
								$arr["dev_id"] = '';
							}

							if(trim($data[5]) != '')
							{
								$designationId = $objDesignation->fnGetDesignationIdByName(trim($data[5]));
								$arr["des_id"] = $designationId;
							}
							else
							{
								$arr["des_id"] = "";
							}
							//$arrdepartment = $objDepartments->fnGetDepartmentIdByName($data[5]);
							/*$arrdesiognation = $objEmployee->fnGetDesignationName($data[6]);*/
							
							//$DepartmentId = $objDepartments->fnGetDepartmentIdByName(trim($data[5]));
							//$DesignationId = $objDesignations->fnGetDesignationIdByName(trim($data[6]));
							//$EmployeeId = $objEmployee->fnGetEmployeeIdByName(trim($data[7]));
							
							$arr["date"] = ucwords(strtolower(trim($data[0])));
							$arr["name"] = strtolower(trim($data[1]));
							//$arr["source_type"] = strtolower(trim($data[2]));
							//$arr["source"] = ucwords(strtolower(trim($data[3])));
							//$arr["division"] = ucwords(strtolower(trim($data[4])));
							//$arr["post_applied_for"] = ucwords(strtolower(trim($data[5])));
							
							if(trim($data[6]) == 'Y')
							{
								$arr["recommend_test"] = '1';
							}
							else 
							{
								$arr["recommend_test"] = '';
							}
							
							$arr["status"] = ucwords(strtolower(trim($data[6])));

							if(trim($data[8]) == 'Y')
							{
								$arr["recommend_om_round"] = '1';
							}
							else 
							{
								$arr["recommend_om_round"] = '';
							}

							if(trim($data[9]) == 'Y')
							{
								$arr["om_status"] = '1';
							}
							else 
							{
								$arr["om_status"] = '';
							}

							if(trim($data[10]) == 'Future Prospect')
							{
								$arr["final_hr_status"] = '3';
								$arr["final_hr_remarks"] = ucwords(strtolower(trim($data[11])));
							}
							else if(trim($data[10]) == 'Hold')
							{
								$arr["final_hr_status"] = '5';
								$arr["final_hr_remarks"] = ucwords(strtolower(trim($data[11])));
							}
							else 
							{
								$arr["final_hr_status"] = '';
								$arr["final_hr_remarks"] = '';
							}
							
							//echo '<pre>'; print_r($arr); 
							
							$arr["isactive"] = '1';
							
							$objUserRegistration->fnInsertCandidates($arr);
						}
						$row++;
					}
					//die;
					fclose($handle);
				}
				
				if($row > 1)
				{
					header("Location: candidates.php?info=upload&err=$errcnt");
					exit;
				}
				else
				{
					header("Location: candidates.php?info=norec");
					exit;
				}
			}
			else
			{
				header("Location: candidates.php?info=invalid");
				exit;
			}
		}
	}
	
	if(isset($_REQUEST['info']))
	{

		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Candidate List inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "Candidate List updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "No records found.";
				break;
			case 'exist':
				$messageClass = "alert-success";
				$message = "Resume already exists.";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] = 'update')
	{
		$delteCandidateList = $objCandidateList->fnDeleteCandidateList($_POST);
		if($delteCandidateList)
		{
			header("Location: candidates.php?info=delete");
		}
		else
		{
			
		}
	}
	
	$tpl->set_var("FillRctSheetValues","");
	foreach($arrCandidateList as $arrCandidateListvalue)
	{
		if($arrCandidateListvalue['recommend_test'] == '0')
		{
			$tpl->set_var("recommend","yes");
		}
		else
		{
			$tpl->set_var("recommend","no");
		}
		$tpl->SetAllValues($arrCandidateListvalue);
		$tpl->parse("FillRctSheetValues",true);
	}

	$tpl->pparse('main',false);
?>

<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template1.html','main');
	$tpl->load_file('pers_history_form.html','main_container');

	//$PageIdentifier = "UserRegistration";
	//include_once('userrights.php');

	$tpl->set_var("mainheading","Personal History Form");
	//$breadcrumb = '<li class="active">Candidate Registration</li>';
	//$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.employee.php');
	include_once('includes/class.designation.php');
	
	$objEmployee = new employee();
	$objDesignation = new Designations();
	
	/*$message = "";
	$messageClass = "";

	
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST['info']))
	{
		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "User inserted successfully.";
				break;
			case 'exist':
				$messageClass = "alert-success";
				$message = "Resume name already exists.";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}*/

	if(isset($_REQUEST['id']) && $_REQUEST['id'] != '' )
	{
		$checkExistingId = $objEmployee->fnCheckEmployeeId($_REQUEST['id']);
		if(isset($checkExistingId) && $checkExistingId != '')
		{
			$tpl->set_var("e_id",$_REQUEST['id']);
			$getAllEmployeeDetails = $objEmployee->fnGetEmployeeDetailById($_REQUEST['id']);
			//echo '<pre>'; print_r($getAllEmployeeDetails);
			if(count($getAllEmployeeDetails) > 0)
			{
				if(isset($getAllEmployeeDetails['name']))
				{
					$tpl->set_var("e_name",$getAllEmployeeDetails['name']);
				}
				if(isset($getAllEmployeeDetails['designation']))
				{
					$getDesignation = $objDesignation->fnGetDesNameById($getAllEmployeeDetails['designation']);
					if(isset($getDesignation))
					{
						$tpl->set_var("e_des",$getDesignation);
					}
				}
				if(isset($getAllEmployeeDetails['teamleader']))
				{
					$getTeamleaderName = $objEmployee->fnGetReportingHeadById($getAllEmployeeDetails['id']);
					if(isset($getTeamleaderName))
					{
						$tpl->set_var("e_teamleader",$getTeamleaderName);
					}
				}
				if(isset($getAllEmployeeDetails['e_date_of_birth']))
				{
					$tpl->set_var("e_dob",$getAllEmployeeDetails['e_date_of_birth']);
				}
				if(isset($getAllEmployeeDetails['blood_group']))
				{
					$tpl->set_var("e_bg",$getAllEmployeeDetails['blood_group']);
				}
				if(isset($getAllEmployeeDetails['contact']))
				{
					$tpl->set_var("e_mobile",$getAllEmployeeDetails['contact']);
				}
				if(isset($getAllEmployeeDetails['phone_number']))
				{
					$tpl->set_var("e_phone",$getAllEmployeeDetails['phone_number']);
				}
				if(isset($getAllEmployeeDetails['official_email']))
				{
					$tpl->set_var("e_official_email",$getAllEmployeeDetails['official_email']);
				}
				if(isset($getAllEmployeeDetails['emergency_contact_name']))
				{
					$tpl->set_var("e_contact_name",$getAllEmployeeDetails['emergency_contact_name']);
				}
				if(isset($getAllEmployeeDetails['emergency_contact']))
				{
					$tpl->set_var("e_contact_number",$getAllEmployeeDetails['emergency_contact']);
				}
				if(isset($getAllEmployeeDetails['relation']))
				{
					$tpl->set_var("e_emer_relation",$getAllEmployeeDetails['relation']);
				}
				if(isset($getAllEmployeeDetails['official_email']))
				{
					$tpl->set_var("e_email",$getAllEmployeeDetails['official_email']);
				}
				if(isset($getAllEmployeeDetails['nationality']))
				{
					$tpl->set_var("e_nationality",$getAllEmployeeDetails['nationality']);
				}
				if(isset($getAllEmployeeDetails['native_place']))
				{
					$tpl->set_var("e_native_place",$getAllEmployeeDetails['native_place']);
				}
				if(isset($getAllEmployeeDetails['vehicle_number']))
				{
					$tpl->set_var("e_vehicle_no",$getAllEmployeeDetails['vehicle_number']);
				}
				if(isset($getAllEmployeeDetails['passport_details']))
				{
					$tpl->set_var("e_passport",$getAllEmployeeDetails['passport_details']);
				}
				if(isset($getAllEmployeeDetails['pan_card']))
				{
					$tpl->set_var("e_pancard",$getAllEmployeeDetails['pan_card']);
				}
				if(isset($getAllEmployeeDetails['location']))
				{
					$tpl->set_var("e_location",$getAllEmployeeDetails['location']);
				}
				if(isset($getAllEmployeeDetails['current_address']))
				{
					$tpl->set_var("e_com_address",$getAllEmployeeDetails['current_address']);
				}
				if(isset($getAllEmployeeDetails['address']))
				{
					$tpl->set_var("e_per_address",$getAllEmployeeDetails['address']);
				}
				if(isset($getAllEmployeeDetails['father_husband_name']))
				{
					$tpl->set_var("e_father_husband_name",$getAllEmployeeDetails['father_husband_name']);
				}
			}
		}
		else
		{
			die;
		}
	}

	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'add')
	{
		$insertdata = $objEmployee->fnUpdateEmployeePersonalHistory($_POST);
		if($insertdata)
		{
			header("Location: thank_you.php?info=succ&page=per_history&id=".$_POST['id']);
			exit;
		}
	}

	$tpl->pparse('main',false);
?>

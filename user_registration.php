<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template1.html','main');
	$tpl->load_file('user_registration.html','main_container');

	//$PageIdentifier = "UserRegistration";
	//include_once('userrights.php');

	$tpl->set_var("mainheading","Interview Application Form");
	//$breadcrumb = '<li class="active">Candidate Registration</li>';
	//$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.user_registration.php');
	include_once('includes/class.employee.php');
	
	$objUserRegistration = new user_registration();
	$objEmployee = new employee();

	$getAllRctSource = $objUserRegistration->fnGetAllRctSource();
	$getAllDesignations = $objUserRegistration->fnGetAllCurrentOpenings();
	$getAllDivision = $objUserRegistration->fnGetAllDivisions();
	$getAllEmployees = $objEmployee->fnGetAllEmployeeForReference();
	
	$message = "";
	$messageClass = "";

	$date = date('d-m-Y');
	$tpl->set_var("date",$date);
	
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
	}

	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'add')
	{
		$insertdata = $objUserRegistration->fnInsertRegistration($_POST,$_FILES);
		if($insertdata)
		{
			header("Location: thank_you.php?info=succ&id=".$insertdata);
			exit;
		}
	}

	$tpl->set_var("FillRCTSourceValues",'');
	if(count($getAllRctSource) > 0 )
	{
		foreach($getAllRctSource as $AllRctSource)
		{
			$tpl->setAllValues($AllRctSource);
			$tpl->parse('FillRCTSourceValues',true);
		}
	}

	$tpl->set_var("FillAllDesignation",'');
	if(count($getAllDesignations) > 0 )
	{
		foreach($getAllDesignations as $AllDesignation) 
		{
			$tpl->setAllValues($AllDesignation);
			$tpl->parse('FillAllDesignation',true);
		}
	}
	
	$tpl->set_var("FillAllDivision",'');
	if(count($getAllDivision) > 0 )
	{
		foreach($getAllDivision as $AllDivision) 
		{
			$tpl->setAllValues($AllDivision);
			$tpl->parse('FillAllDivision',true);
		}
	}

	$tpl->set_var("FillEmployeeReference",'');
	if(count($getAllEmployees) > 0)
	{
		foreach($getAllEmployees as $employees)
		{
			$tpl->setAllValues($employees);
			$tpl->parse('FillEmployeeReference',true);
		}
	}
	

	$tpl->pparse('main',false);
?>

<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('increment.html','main_container');

	$PageIdentifier = "IncrementDetails";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Increment Details");
	$breadcrumb = '<li class="active">Increment Details</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.increment.php');
	include_once('includes/class.employee.php');
	
	$objIncrement = new increment();
	$objEmployee = new employee();
	
	$tpl->set_var("FillPendingDocument","");
	$tpl->set_var("FillAllDocument","");
	
	$arrGetAllEmployeeDetails = $objEmployee->fnGetAllCurrentEmployeeDetails();
	//echo '<pre>'; print_r($arrGetAllEmployeeDetails);
	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST['info']))
	{

		switch($_REQUEST["info"])
		{
			case 'add':
				$messageClass = "alert-success";
				$message = "Document details inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "Document details updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "No records found.";
				break;
			case 'exist':
				$messageClass = "alert-success";
				$message = "Document details deleted successfully.";
				break;
		}
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	

	$tpl->set_var("FillEmployee","");
	foreach($arrGetAllEmployeeDetails as $employee)
	{
		$tpl->set_var("emp_id",$employee['id']);
		$tpl->SetAllValues($employee);
		$tpl->parse("FillEmployee",true);
	}

	$tpl->pparse('main',false);
?>

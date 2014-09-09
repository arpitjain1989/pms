<?php
	include('common.php');
	
	$tpl = new Template($app_path);
	//print_r($_SESSION);
	$tpl->load_file('template.html','main');
	$tpl->load_file('attrition_list.html','main_container');
	
	$PageIdentifier = "Attrition";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Attrition process");
	$breadcrumb = '<li class="active">Attrition process</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');
	include_once('includes/class.attrition.php');
	
	$objLeave = new leave();
	$objEmployee = new employee();
	$objAttrition = new attrition();

	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST['info']))
	{
		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Record updated successfully.";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}
	
	if($_SESSION['usertype'] == 'admin')
	{
		$arrEmployee = $objEmployee->fnGetAllemployees(0);
	}
	else
	{
		//echo $_SESSION['id'];
		/* Get delegated teamleader id */
		$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);

		/* Get Delegated Manager id */
		//$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($_SESSION['id']);
		
		
		$arrDelegatedEmployee = array();
		$arrtemp = array();
		if(count($arrDelegatedTeamLeaderId) > 0 )
		{
			foreach($arrDelegatedTeamLeaderId as $delegatesIds)
			{
				//echo $delegatesIds;
				$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
				$arrDelegatedEmployee =$arrDelegatedEmployee + $arrtemp ;
			}
		}
		/*if(count($arrDelegatedManagerId) > 0 )
		{
			foreach($arrDelegatedManagerId as $delegatesManagerIds)
			{
				//echo $delegatesIds;
				$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
				$arrDelegatedEmployee =$arrDelegatedEmployee + $arrtemp ;
			}
		}*/
		//print_r($arrDelegatedEmployee);
		$temp1 = $objEmployee->fnGetAllemployees($_SESSION['id']);
		$arrEmployee = $temp1 + $arrDelegatedEmployee;
		//$arrEmployee = $objEmployee->fnGetAllemployees($_SESSION['id']);
	}
	//print_r($arrEmployee);
	$arrEmployee[] = "";
	
	if(count($arrEmployee) > 0)
	{
		$arrEmployee = array_filter($arrEmployee,'strlen');
	}
	
	$ids = "0";
	if(count($arrEmployee) > 0)
	{
		$ids = implode(',',$arrEmployee);
	}
	$arrAttrition = $objAttrition->fnGetAllAttritions($ids);
	//echo '<pre>';print_r($arrAttrition);die;
	$tpl->set_var("FillLeaveRequestValues","");
	//$tpl->set_var("FillTeamLeaderName","");
	//$tpl->set_var("FillTeamLeaderNameValue","");
	/*if($_SESSION['designation'] == 6 || $_SESSION['designation'] == 0)
	{
		$tpl->parse("FillTeamLeaderName",false);
	}*/
	
	if(count($arrAttrition) > 0 )
	{
		foreach($arrAttrition as $attrition)
		{
			$tpl->setAllValues($attrition);
			if($attrition['headname'] == '' )
			{
				$tpl->set_var("headname","Admin");
			}
			/*if($_SESSION['designation'] == 6 || $_SESSION['designation'] == 0)
			{
				$tpl->parse("FillTeamLeaderNameValue",false);
			}*/
			$tpl->parse("FillLeaveRequestValues",true);
		}
	}
	
	$tpl->pparse('main',false);
?>

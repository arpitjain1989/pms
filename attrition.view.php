<?php
	include('common.php');
	$tpl = new Template($app_path);
	//print_r($_SESSION);
	$tpl->load_file('template.html','main');
	$tpl->load_file('attrition.view.html','main_container');

	$PageIdentifier = "Attrition";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View attrition detail");
	$breadcrumb = '<li><a href="attrition_list.php">Manage attration</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Attrition</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');
	include_once('includes/class.attrition.php');

	$objLeaves = new leave();
	$objEmployee = new employee();
	$objAttrition = new attrition();

	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'update')
	{
		$updateAttrition = $objAttrition->fnUpdateAttrition($_POST);
		if($updateAttrition)
		{
			header("Location: attrition_list.php?info=succ");
			exit;
		}
	}
	
	$arrAttrition = $objAttrition->fnGetAttrationDetailsById($_REQUEST['id']);
	
	$tpl->set_var("FillTemaLeaderEditable",'');
	$tpl->set_var("FillManagerEditable",'');
	$tpl->set_var("FillHrEditable",'');
	$tpl->set_var("FillAdminEditable",'');
	$tpl->set_var("FillTemaLeaderReadable",'');
	$tpl->set_var("FillManagerReadable",'');
	$tpl->set_var("FillHrReadable",'');
	$tpl->set_var("FillAdminReadable",'');
	$tpl->set_var("DisplayBackButtons",'');
	$tpl->set_var("DisplayActionButtons",'');

	$tpl->set_var('session_designation',$_SESSION['designation']);


	if(count($arrAttrition) > 0)
	{
		$reportingHead = $objEmployee->fnGetReportingHeadById($arrAttrition['userid']);
		$tpl->set_var('repHead',$reportingHead);
		$tpl->set_var('emplname',$arrAttrition['emp_name']);
		$tpl->set_var('attDate',$arrAttrition['att_date']);
		$tpl->set_var('tlhold',$arrAttrition['teamleader_holdtill']);
		$tpl->set_var('mnhold',$arrAttrition['man_holdtill']);
		$tpl->set_var('hrhold',$arrAttrition['hr_hold_till']);
		$tpl->set_var('adminhold',$arrAttrition['admin_hold_till']);
		$tpl->set_var('attrition_id',$arrAttrition['attrid']);
		
		$tpl->set_var('tlstatus',$arrAttrition['tlstatus']);
		$tpl->set_var('managerstatus',$arrAttrition['managerstatus']);
		$tpl->set_var('adminstatus',$arrAttrition['adminstatus']);
		$tpl->set_var('hrstatus',$arrAttrition['hrstatus']);

		$tpl->set_var('tlholdcomment',$arrAttrition['tlholdcomment']);
		$tpl->set_var('managerholdcomment',$arrAttrition['managerholdcomment']);
		$tpl->set_var('adminholdcomment',$arrAttrition['adminholdcomment']);
		$tpl->set_var('hrholdcomment',$arrAttrition['hrholdcomment']);

		$flagActionButton = false;

		if($_SESSION['admin_type'] == 'admin')
		{
			$tpl->set_var('session_userType',$_SESSION['admin_type']);
			$tpl->set_var('status',$arrAttrition['admin_status']);
			if($arrAttrition['admin_status'] == '2')
			{
				if($arrAttrition['admin_holdtill'] == '' || $arrAttrition['admin_holdtill'] == '00:00:00')
				{
					$tpl->parse("FillAdminEditable",false);
					$flagActionButton = true;
				}
				else
				{
					$tpl->parse("FillAdminReadable",false);
				}
			}
			
			if($arrAttrition['hr_status'] == '2')
				$tpl->parse("FillHrReadable",false);
			if($arrAttrition['manager_status'] == '2')
				$tpl->parse("FillManagerReadable",false);
			if($arrAttrition['tl_status'] == '2')
				$tpl->parse("FillTemaLeaderReadable",false);
		}
		else if($_SESSION['admin_type'] == 'hradmin')
		{
			$tpl->set_var('session_userType',$_SESSION['admin_type']);
			$tpl->set_var('status',$arrAttrition['hr_status']);
			if($arrAttrition['hr_status'] == '2')
			{
				if($arrAttrition['hr_hold_till'] == '' || $arrAttrition['hr_hold_till'] == '00:00:00')
				{
					$tpl->parse("FillHrEditable",false);
					$flagActionButton = true;
				}
				else
				{
					$tpl->parse("FillHrReadable",false);
				}
			}

			if($arrAttrition['admin_status'] == '2')
				$tpl->parse("FillAdminReadable",false);
			if($arrAttrition['manager_status'] == '2')
				$tpl->parse("FillManagerReadable",false);
			if($arrAttrition['tl_status'] == '2')
				$tpl->parse("FillTemaLeaderReadable",false);
		}
		else if($arrAttrition['managerid'] == $_SESSION["id"])
		{
			$tpl->set_var('session_userType',"manager");
			$tpl->set_var('status',$arrAttrition['manager_status']);
			if($arrAttrition['manager_status'] == '2')
			{
				if($arrAttrition['manager_holdtill'] == '' || $arrAttrition['manager_holdtill'] == '00:00:00')
				{
					$tpl->parse("FillManagerEditable",false);
					$flagActionButton = true;
				}
				else
				{
					$tpl->parse("FillManagerReadable",false);
				}
			}

			if($arrAttrition['admin_status'] == '2')
				$tpl->parse("FillAdminReadable",false);
			if($arrAttrition['hr_status'] == '2')
				$tpl->parse("FillHrReadable",false);
			if($arrAttrition['tl_status'] == '2')
				$tpl->parse("FillTemaLeaderReadable",false);
		}
		else if($arrAttrition['tlid'] == $_SESSION["id"])
		{
			$tpl->set_var('session_userType',"tl");
			$tpl->set_var('status',$arrAttrition['tl_status']);
			if($arrAttrition['tl_status'] == '2')
			{
				if($arrAttrition['tl_holdtill'] == '' || $arrAttrition['tl_holdtill'] == '00:00:00')
				{
					$tpl->parse("FillTemaLeaderEditable",false);
					$flagActionButton = true;
				}
				else
				{
					$tpl->parse("FillTemaLeaderReadable",false);
				}
			}

			if($arrAttrition['admin_status'] == '2')
				$tpl->parse("FillAdminReadable",false);
			if($arrAttrition['hr_status'] == '2')
				$tpl->parse("FillHrReadable",false);
			if($arrAttrition['manager_status'] == '2')
				$tpl->parse("FillManagerReadable",false);
		}
		else
		{
			if($arrAttrition['admin_status'] == '2')
				$tpl->parse("FillAdminReadable",false);
			if($arrAttrition['hr_status'] == '2')
				$tpl->parse("FillHrReadable",false);
			if($arrAttrition['manager_status'] == '2')
				$tpl->parse("FillManagerReadable",false);
			if($arrAttrition['tl_status'] == '2')
				$tpl->parse("FillTemaLeaderReadable",false);
		}

		if($flagActionButton)
			$tpl->parse("DisplayActionButtons",false);
		else
			$tpl->parse("DisplayBackButtons",false);
	}
	
	$tpl->pparse('main',false);
?>

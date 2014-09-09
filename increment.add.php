<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('increment.add.html','main_container');

	$PageIdentifier = "IncrementDetails";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add Increment");
	$breadcrumb = '<li><a href="increment.php">Manage Increment</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add Increment</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.employee.php');
	include_once('includes/class.increment.php');
	
	$objEmployee = new employee();
	$objIncrement = new increment();

	
	if(isset($_REQUEST['id']))
	{
		//echo $_REQUEST[id];
		$tpl->set_var('hdnid',"$_REQUEST[id]");
	}
	
	
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] =='add')
	{
		//echo '<pre>'; print_r($_POST); die;
		$addIncrement = $objIncrement->fnAddIncrement($_POST);
		$updateEmployeeCurSalary = $objEmployee->fnUpdateCurrCtc($_POST['userid'],$_POST['newsalary']);
		if($addIncrement)
		{
			header("Location: increment.php?info=add");
			exit;
		}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
		$tpl->set_var('FillIncrementHistory','');
		$IncrementDetails = $objIncrement->fnGetIncentiveDetailsById($_REQUEST['id']);
		$getAllIncrements = $objIncrement->fnGetAllIncrements($_REQUEST['id']);
		//echo 'hello<pre>'; print_r($IncrementDetails);
		if($IncrementDetails)
		{
			$tpl->SetAllValues($IncrementDetails);
		}
		if(count($getAllIncrements) > 0)
		{
			foreach($getAllIncrements as $increment)
			{
				$tpl->SetAllValues($increment);
				$tpl->parse('FillIncrementHistory',true);
			}
		}
		$tpl->set_var('action','add');
	}
	
	$tpl->pparse('main',false);
?>

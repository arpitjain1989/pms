<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('increment.view.html','main_container');

	$PageIdentifier = "IncrementDetails";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Increment Details");
	$breadcrumb = '<li><a href="increment.php">Manage Increment Details</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Increment Details</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.increment.php');
	
	$objIncrement = new increment();
	
	$IncrementDetails = $objIncrement->fnGetIncentiveDetailsById($_REQUEST['id']);

	$getAllIncrements = $objIncrement->fnGetAllIncrements($_REQUEST['id']);

	
	
	if($IncrementDetails)
	{
		$tpl->SetAllValues($IncrementDetails);
	}
	$tpl->set_var('FillIncrementHistory','');
	if(count($getAllIncrements) > 0)
	{
		foreach($getAllIncrements as $increment)
		{
			$tpl->SetAllValues($increment);
			$tpl->parse('FillIncrementHistory',true);
		}
	}

	$tpl->pparse('main',false);
?>

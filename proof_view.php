<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('proof_view.html','main_container');

	$PageIdentifier = "Proof";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Proof");
	$breadcrumb = '<li><a href="proof_list.php">Manage Proof</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Proof</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.proof.php');
	
	$objProof = new proof();
	
	$tpl->set_var("DisplayProofInformationBlock","");
	$tpl->set_var("DisplayNoProofBlock","");
	
	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$arrProof = $objProof->fnGetProofById($_REQUEST['id']);
		
		if(count($arrProof) > 0)
		{
			$arrTypeText = array("1"=>"ID Proof", "2"=>"Address Proof");
			$tpl->set_var("proof_type",$arrTypeText[$arrProof["type"]]);
			$tpl->SetAllValues($arrProof);
			$tpl->parse("DisplayProofInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoProofBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoProofBlock",false);
	}
	
	$tpl->pparse('main',false);
?>


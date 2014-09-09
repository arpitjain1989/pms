<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('proof.html','main_container');

	$PageIdentifier = "Proof";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add Proof");
	$breadcrumb = '<li><a href="proof_list.php">Manage Proof</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add Proof</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.proof.php');

	$objProof = new proof();

	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$proof = $objProof->fnGetProofById($_REQUEST["id"]);
		if(count($proof) > 0)
		{
			$tpl->SetAllValues($proof);
		}
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "SaveProof")
	{
		$proof_status = $objProof->fnSaveProof($_POST);

		if($proof_status == 1)
		{
			header("Location: proof_list.php?info=success");
			exit;
		}
		else if($proof_status == 0)
		{
			header("Location: proof_list.php?info=err");
			exit;
		}
	}

	$tpl->pparse('main',false);
?>

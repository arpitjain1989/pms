<?php 
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('quality_afd_view.html','main_container');

	$PageIdentifier = "QualityAFD";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Quality AFD");
	$breadcrumb = '<li><a href="quality_afd_list.php">Manage Quality AFD</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Quality AFD</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.quality_form.php');
	$objQualityForm = new quality_form();

	$tpl->set_var("DisplayQualityAfdInformationBlock","");
	$tpl->set_var("DisplayNoQualityAfdBlock","");

	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$arrQualityAfd = $objQualityForm->fnGetAfdById($_REQUEST['id']);
		
		if(count($arrQualityAfd) > 0)
		{
			$arrActive = array("0"=>"Active", "1"=>"Inactive");
			$tpl->SetAllValues($arrQualityAfd);
			$tpl->set_var("active_text", "");
			if(isset($arrQualityAfd["isactive"]) && trim($arrQualityAfd["isactive"]) != "")
				$tpl->set_var("active_text", $arrActive[$arrQualityAfd["isactive"]]);
			$tpl->parse("DisplayQualityAfdInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoQualityAfdBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoQualityAfdBlock",false);
	}

	$tpl->pparse('main',false);
?>

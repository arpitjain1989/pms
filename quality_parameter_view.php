<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('quality_parameter_view.html','main_container');

	$PageIdentifier = "QualityParameter";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Quality Parameter");
	$breadcrumb = '<li><a href="quality_parameter_list.php">Manage Quality Parameter</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Quality Parameter</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.quality_form.php');
	$objQualityForm = new quality_form();
	
	$tpl->set_var("DisplayQualityParameterInformationBlock","");
	$tpl->set_var("DisplayNoQualityParameterBlock","");
	
	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$arrQualityParameter = $objQualityForm->fnGetParameterById($_REQUEST['id']);
		
		if(count($arrQualityParameter) > 0)
		{
			$arrActive = array("0"=>"Active", "1"=>"Inactive");
			$tpl->SetAllValues($arrQualityParameter);
			$tpl->set_var("active_text", "");
			if(isset($arrQualityParameter["isactive"]) && trim($arrQualityParameter["isactive"]) != "")
				$tpl->set_var("active_text", $arrActive[$arrQualityParameter["isactive"]]);
			$tpl->parse("DisplayQualityParameterInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoQualityParameterBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoQualityParameterBlock",false);
	}
	
	$tpl->pparse('main',false);
?>

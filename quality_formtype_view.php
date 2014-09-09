<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('quality_formtype_view.html','main_container');

	$PageIdentifier = "QualityFormType";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Quality Form Type");
	$breadcrumb = '<li><a href="quality_fromtype_list.php">Manage Quality Form Type</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Quality Form Type</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.quality_form.php');
	$objQualityForm = new quality_form();
	
	$tpl->set_var("DisplayQualityFormInformationBlock","");
	$tpl->set_var("DisplayNoQualityFormBlock","");
	
	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$arrQualityForm = $objQualityForm->fnGetQualityFormTypeById($_REQUEST['id']);
		
		if(count($arrQualityForm) > 0)
		{
			$tpl->SetAllValues($arrQualityForm);
			$tpl->parse("DisplayQualityFormInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoQualityFormBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoQualityFormBlock",false);
	}
	
	$tpl->pparse('main',false);
?>

<?php

	include_once("common.php");
	include_once("includes/class.designation.php");

	$objDesignation = new designations();

	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "first_head")
	{

		$returnStr = "<select name='first_reporting_head' id='first_reporting_head' onchange='javascript: fnChangeFirstReportingHead();'>";
		$returnStr .= "<option value=''>Please Select</option>";

		$curDesignation = $objDesignation->fnGetDesignationById($_REQUEST["id"]);
		$arrDesignation = $objDesignation->fnGetDesignationHierarchy($_REQUEST["id"]);

		if(count($curDesignation) > 0)
		{
			$returnStr .= "<option value='".$curDesignation["id"]."'>".$curDesignation["title"]."</option>";
		}

		if(count($arrDesignation) > 0)
		{
			foreach($arrDesignation as $DesignationId => $DesignationTitle)
			{
				$returnStr .= "<option value='".$DesignationId."'>".$DesignationTitle."</option>";
			}
		}

		$returnStr .= "</select>";
	}
	else if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "second_head")
	{
		$returnStr = "<select name='second_reporting_head' id='second_reporting_head'>";
		$returnStr .= "<option value=''>Please Select</option>";

		$arrDesignation = $objDesignation->fnGetDesignationHierarchy($_REQUEST["id"]);

		if(count($arrDesignation) > 0)
		{
			foreach($arrDesignation as $DesignationId => $DesignationTitle)
			{
				$returnStr .= "<option value='".$DesignationId."'>".$DesignationTitle."</option>";
			}
		}

		$returnStr .= "</select>";
	}

	echo $returnStr;

?>

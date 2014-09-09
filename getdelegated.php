<?php

	if(session_id() == '')
		session_start();
		
	include_once("includes/class.employee.php");
	include_once("includes/class.designation.php");

	$objEmployee = new employee();
	$objDesignation = new designations();

	$displayVar = "";

	$date = date('Y-m-d');
	
	/* Get requested employee id */
	//$eid = $_REQUEST['empid'];
	//$curDesignation = $_REQUEST['des'];

	if(isset($_REQUEST['empid']) && trim($_REQUEST['empid']) != "")
	{
		$EmployeeInfo = $objEmployee->fnGetEmployeeById($_REQUEST['empid']);
		
		if(count($EmployeeInfo) > 0)
		{
			$arrDesignation = $objDesignation->fnGetDesignationById($EmployeeInfo["designation"]);
			
			/* Fetch reporting head hierarchy */
			$arrHeads = $objEmployee->fnGetReportHeadHierarchy($_REQUEST['empid']);

			/*if(isset($arrDesignation["allow_delegation"]) && trim($arrDesignation["allow_delegation"]) == "1" && isset($arrDesignation["delegation_designation"]) && trim($arrDesignation["delegation_designation"]) != "" && ((isset($arrHeads[$arrDesignation['first_reporting_head']]["id"]) && $_SESSION["id"] == $arrHeads[$arrDesignation['first_reporting_head']]["id"]) || (isset($arrHeads[$arrDesignation['second_reporting_head']]["id"]) && $_SESSION["id"] == $arrHeads[$arrDesignation['second_reporting_head']]["id"])))*/
			if(isset($arrDesignation["allow_delegation"]) && trim($arrDesignation["allow_delegation"]) == "1")
			{
				if(trim($arrDesignation["delegation_designation"]) == "")
					$arrDesignation["delegation_designation"] = 0;

				/* Fetch all the employees as per the delegation designation */
				$arrEmployees = $objEmployee->fnGetEmployeesByDesignation(trim($arrDesignation["delegation_designation"]));

				if(count($arrEmployees) > 0)
				{
					$displayVar .= '<div class="form-row row-fluid">
						<div class="span12">
							<div class="row-fluid">
								<label class="form-label span4">Delegated To:</label>
									<select name="delegate" id="delegate">';
					/* Fill dropdown for delegates */
					foreach($arrEmployees as $curEmployee)
					{
						/* Do not display employee whose leave is  */
						if($curEmployee["id"] != $_REQUEST['empid'])
						{
							$displayVar .= "<option value='".$curEmployee["id"]."'>".$curEmployee["name"]."</option>";
						}
					}
					$displayVar .='</select>
								</div>
							</div>
						</div>';
				}
			}
		}
	}

	echo $displayVar;

?>

<?php
	include_once("includes/class.employee.php");

	$objEmployee = new employee();

	$displayVar = "";
	
	/* Get requested employee id */
	$eid = $_REQUEST['empid'];
	$curDesignation = $_REQUEST['des'];
	//echo 'curDesignation'.$curDesignation;
	/* Get requested employee all details using id */
	if($eid != '')
	{
		$EmployeeInfo = $objEmployee->fnGetEmployeeById($eid);
	}
	//echo $EmployeeInfo['designation'];
	/* If session designation is 17 */
	if($curDesignation == '17')
	{
		/* If selected employee is a manager */
		if($EmployeeInfo['designation'] == '6')
		{
			$getAllManagers = $objEmployee->fnGetAllManagers($EmployeeInfo['emp_id'],$EmployeeInfo['designation'],$curDesignation);
			//print_r($getAllManagers );
			$displayVar .= '<div class="form-row row-fluid">
				<div class="span12">
					<div class="row-fluid">
						<label class="form-label span4">Delegated Manager:</label>
						<select name="delegate" id="delegate">';
							foreach($getAllManagers as $managers)
							{
								$displayVar .= "<option value='".$managers["id"]."'>".$managers["name"]."</option>";
							}
			$displayVar .='</select>
					</div>
				</div>
			</div>';
		}
		/* If selected employee is a teamleader */
		else if($EmployeeInfo['designation'] == '7' || $EmployeeInfo['designation'] == '13')
		{
			$getAllTeamLeaders = $objEmployee->fnGetAllTeamLeaders($EmployeeInfo['emp_id'],$EmployeeInfo['designation'],$curDesignation);
			//print_r($getAllManagers );
			$displayVar .= '<div class="form-row row-fluid">
				<div class="span12">
					<div class="row-fluid">
						<label class="form-label span4">Delegated TeamLeader:</label>
						<select name="delegate" id="delegate">';
						foreach($getAllTeamLeaders as $Teamleaders)
						{
							$displayVar .= "<option value='".$Teamleaders["id"]."'>".$Teamleaders["name"]."</option>";
						}
			$displayVar .='</select>
					</div>
				</div>
			</div>';
		}
		/* If selected empployee is a agent */
		else
		{
			
		}
	}
	else if($curDesignation == '6')
		{
			if(isset($EmployeeInfo))
			{ 
				if($EmployeeInfo['designation'] == '7' || $EmployeeInfo['designation'] == '13')
				{
					$getAllTeamLeaders = $objEmployee->fnGetAllTeamLeaders($EmployeeInfo['emp_id'],$EmployeeInfo['designation'],$curDesignation);
					//print_r($getAllManagers );
					$displayVar .= '<div class="form-row row-fluid">
						<div class="span12">
							<div class="row-fluid">
								<label class="form-label span4">Delegated TeamLeader:</label>
								<select name="delegate" id="delegate">';
								foreach($getAllTeamLeaders as $Teamleaders)
								{
									$displayVar .= "<option value='".$Teamleaders["id"]."'>".$Teamleaders["name"]."</option>";
								}
					$displayVar .='</select>
							</div>
						</div>
					</div>';
				}
				/* If selected empployee is a agent */
				else
				{
					
				}
			}
		}
	
	

	echo $displayVar;

?>

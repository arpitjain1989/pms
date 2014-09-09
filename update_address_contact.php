<?php

	include_once("common.php");

	include_once('includes/db_mysql.php');

	$db = new DB_Sql();

	if (($handle = fopen("extra/Employee data.csv", "r")) !== FALSE) 
	{
		$row = 0;
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
			if($row > 0)
			{
				$sSQL = "select * from pms_employee where employee_code='".mysql_real_escape_string(trim($data[0]))."'";
				$db->query($sSQL);
				if($db->num_rows())
				{
					if($db->next_record())
					{
						$updateArray["id"] = $db->f("id");
						$updateArray["address"] = trim($data[2]);
						$updateArray["contact"] = trim($data[3]);
						$updateArray["emergency_contact"] = trim($data[4]);

						//print_r($updateArray);

						$db->updateArray("pms_employee",$updateArray);
					}
				}
			}

			$row++;
			
		}
		fclose($handle);
	}

	echo "done";

?>

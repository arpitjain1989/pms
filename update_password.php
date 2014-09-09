<?php

	include_once("common.php");

	include_once('includes/db_mysql.php');

	$db = new DB_Sql();

	if (($handle = fopen("extra/Employee Passwords.csv", "r")) !== FALSE) 
	{
		$row = 0;
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
			if($row > 0)
			{
				$sSQL = "select * from pms_employee where id='".mysql_real_escape_string(trim($data[0]))."'";
				$db->query($sSQL);
				if($db->num_rows())
				{
					if($db->next_record())
					{
						$updateArray["id"] = $db->f("id");
						$updateArray["password"] = md5(trim($data[3]));

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

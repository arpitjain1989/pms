<?php

	include_once("includes/db_mysql.php");

	$db = new DB_Sql();
	$mb = new DB_Sql();
	
	$sSQL = "select id, name from pms_employee";
	$db->query($sSQL);
	if($db->num_rows())
	{
		while($db->next_record())
		{
			echo "<br>".$db->f("id")."==".$db->f("name");
			
			$arrUpdate["id"] = $db->f("id");
			$arrUpdate["name"] = ucwords(strtolower($db->f("name")));
			
			$mb->updateArray("pms_employee",$arrUpdate);
		}
	}

?>

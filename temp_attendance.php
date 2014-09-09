<?php
	
	error_reporting(E_ALL);
	set_time_limit(0);

	include_once('includes/db_mysql.php');

	$db = new DB_Sql();
	$db1 = new DB_Sql();
	$db2 = new DB_Sql();

	$sSQL = "select *, date_format(date,'%Y-%m-%d') as date from pms_attendance1";
	$db->query($sSQL);
	if($db->num_rows())
	{
		while($db->next_record())
		{
			unset($curRow);
			
			$curRow["user_id"] = $db->f("user_id");
			$curRow["date"] = $db->f("date");
			$curRow["in_time"] = $db->f("in_time");
			$curRow["out_time"] = $db->f("out_time");
			$curRow["break1_in"] = $db->f("break1_in");
			$curRow["break1_out"] = $db->f("break1_out");
			$curRow["break2_in"] = $db->f("break2_in");
			$curRow["break2_out"] = $db->f("break2_out");
			$curRow["break3_in"] = $db->f("break3_in");
			$curRow["break3_out"] = $db->f("break3_out");
			$curRow["break4_in"] = $db->f("break4_in");
			$curRow["break4_out"] = $db->f("break4_out");
			$curRow["break5_in"] = $db->f("break5_in");
			$curRow["break5_out"] = $db->f("break5_out");
			
			if($db->f("leave_id") == 13)
				$curRow["leave_id"] = 0;
			else
				$curRow["leave_id"] = $db->f("leave_id");
			
			$sSQL = "select * from pms_attendance where user_id='".$db->f("user_id")."' and date_format(date,'%Y-%m-%d') = '".$db->f("date")."'";
			$db1->query($sSQL);
			if($db1->num_rows())
			{
				if($db1->next_record())
				{
					$curRow["id"] = $db1->f("id");
					$db2->updateArray("pms_attendance",$curRow);
				}
				else
				{
					$db2->insertArray("pms_attendance",$curRow);
				}
			}
			else
			{
				$db2->insertArray("pms_attendance",$curRow);
			}
		}
	}

	echo "done";

?>

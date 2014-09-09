<?php

	$dt = Date('Y-m-d-H-i-s');

	if(isset($_REQUEST["action"]))
	{
		if($_REQUEST["action"] == "attendance")
		{
			exec("E:\\xampp\mysql\bin\mysqldump -uroot1 -proot1 pms pms_attendance | E:\\xampp\htdocs\pms\gzip -9 > Z:\\backup\attendance-".$dt.".sql.gz");
		}
		else if($_REQUEST["action"] == "full")
		{
			exec("E:\\xampp\mysql\bin\mysqldump -uroot1 -proot1 pms | E:\\xampp\htdocs\pms\gzip -9 > Z:\\backup\pms-full-".$dt.".sql.gz");
		}
		else
		{
			exec("E:\\xampp\mysql\bin\mysqldump -uroot1 -proot1 pms pms_attendance | E:\\xampp\htdocs\pms\gzip -9 > Z:\\backup\attendance-".$dt.".sql.gz");
		}
	}
	else
	{
		exec("E:\\xampp\mysql\bin\mysqldump -uroot1 -proot1 pms pms_attendance | E:\\xampp\htdocs\pms\gzip -9 > Z:\\backup\attendance-".$dt.".sql.gz");
	}
	
?>

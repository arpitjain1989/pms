<?php
	
	include_once('db_mysql.php');
	
	class clsModule extends DB_Sql
	{
		function __construct()
		{
		}

		function fnGetAllModules()
		{
			$arrModules = array();
			$query = "SELECT * FROM pms_module";
			$this->query($query);
			
			if($this->num_rows() > 0)
			{	
				while($this->next_record())
				{
					$arrModules[$this->f("id")] = str_replace(" ","",$this->f("title"));
				}
			}
			return $arrModules;
		}
		
	}
?>
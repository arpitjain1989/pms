<?php
	include_once('db_mysql.php');

	class clsLogin extends DB_Sql
	{
		function __construct()
		{
		}

		function fnCheckUser($uname,$pass)
		{
			//$md5value = md5($pass);
			$query = "SELECT * FROM `pms_admin` WHERE `username` = '".mysql_real_escape_string($uname)."'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				// AND `password` = '$md5value'

				if($this->next_record())
				{
					/*if($this->f("islogin") == 1)
					{
						if($this->fnCheckLastAccess("admin", $this->f("islogin")))
						return "-1";
					}*/
					
					if($this->f("password") == md5($pass))
					{
						$_SESSION["displayname"] = $this->f("name");
						$_SESSION["username"] = $this->f("username");
						$_SESSION["id"] = $this->f("id");
						$_SESSION["usertype"] = "admin";
						$_SESSION["admin_type"] = $this->f("admin_type");
						$_SESSION["designation"] = "0";

						/* Update the last activity when login */
						$updateInfo["id"] = $this->f("id");
						$updateInfo["last_activity"] = Date('Y-m-d H:i:s');
						$updateInfo["islogin"] = 1;

						$this->updateArray("pms_admin",$updateInfo);

						return true;
					}
					else
					{
						return false;
					}

				}
				else
				{
					return $this->fnCheckEmployee($uname,$pass);
				}

			}
			else
			{
				return $this->fnCheckEmployee($uname,$pass);
			}
		}

		function fnCheckEmployee($username, $password)
		{
			$sSQL = "select * from pms_employee where (username='".mysql_real_escape_string($username)."' or `email` = '".mysql_real_escape_string($username)."') and status='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					if($this->f("password") == md5($password))
					{
						$_SESSION["displayname"] = $this->f("name");
						$_SESSION["username"] = $this->f("username");
						$_SESSION["id"] = $this->f("id");
						$_SESSION["usertype"] = "employee";
						$_SESSION["designation"] = $this->f("designation");
						$_SESSION["teamleader"] = $this->f("teamleader");

						/* Update the last activity when login */
						$updateInfo["id"] = $this->f("id");
						$updateInfo["last_activity"] = Date('Y-m-d H:i:s');
						$updateInfo["islogin"] = 1;

						$this->updateArray("pms_employee",$updateInfo);
						
						$roleId = $this->f("role");

						$arrRoles = array();

						$sSQL = "select m.* from pms_roles r INNER JOIN pms_role_details rd ON r.id = rd.role_id INNER JOIN pms_module m ON m.id = rd.modules where r.id ='$roleId'";
						$this->query($sSQL);
						if($this->num_rows() > 0)
						{
							while($this->next_record())
							{
								$arrRoles[$this->f("id")] = str_replace(" ","",$this->f("title"));
							}
						}

						$_SESSION["userrights"] = $arrRoles;


						return true;
					}
					else
						return false;
				}
				else
					return false;
			}
			else
				return false;
		}
		
		function fnCheckLastAccess($UserType, $UserId)
		{
			$curDate = Date('Y-m-d H:i:s');
			
			/* Add condition for checking login status */
			
			if($UserType == "admin")
			{
				$sSQL = "select * from pms_admin where date_add(last_activity, INTERVAL 180 MINUTE) >= '".mysql_real_escape_string($curDate)."' and id='".mysql_real_escape_string($UserId)."'";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$updateInfo["id"] = $this->f("id");
						$updateInfo["last_activity"] = Date('Y-m-d H:i:s');

						$this->updateArray("pms_admin",$updateInfo);
						
						return true;
					}
					else
						return false;
				}
				else
					return false;
			}
			else if($UserType == "employee")
			{
				$sSQL = "select * from pms_employee where date_add(last_activity, INTERVAL 60 MINUTE) >= '".mysql_real_escape_string($curDate)."' and id='".mysql_real_escape_string($UserId)."'";
				$this->query($sSQL);
				if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$updateInfo["id"] = $this->f("id");
						$updateInfo["last_activity"] = Date('Y-m-d H:i:s');

						$this->updateArray("pms_employee",$updateInfo);

						return true;
					}
					else
						return false;
				}
				else
					return false;
			}
		}
		
		function fnLogout()
		{
			if($_SESSION["id"] != "" && $_SESSION["id"] != "0")
			{
				$updateInfo["id"] = $_SESSION["id"];
				$updateInfo["islogin"] = 0;

				if($_SESSION["usertype"] == "admin")
					$this->updateArray("pms_admin",$updateInfo);
				else if($_SESSION["usertype"] == "employee")
					$this->updateArray("pms_employee",$updateInfo);
			}
		}
	}
?>

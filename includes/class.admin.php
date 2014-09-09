<?php
	include_once('db_mysql.php');
	class admin extends DB_Sql
	{
		function __construct()
		{
		}
		
		function fnGetUserDetailByUsername($username)
		{
			$arrUserValues = array();
			$query = "SELECT `id` as user_id,`name` as user_name ,`email` as user_email,`phone` as user_phone,`username` as user_username,`password` as user_password FROM `pms_admin` WHERE `username` = '$username'";
			$this->query($query);
			
			if($this->num_rows() > 0)
			{	
				while($this->next_record())
				{
					$arrUserValues[] = $this->fetchrow();
				}
			}
			return $arrUserValues;
		}
		
		function fnUpdateUser($arrPost)
		{
		if($arrPost['password'] == '' )
		{
			$arrNewRecords = array("id"=>$arrPost['hdnuserid'],"name"=>$arrPost['name'],"email"=>$arrPost['email'],"phone"=>$arrPost['phonenumber'],"username"=>$arrPost['username']);
				$this->updateArray('pms_admin',$arrNewRecords);
		}
		else
		{
		 $encrypt_pass=md5($arrPost['password']); 
			$arrNewRecords = array("id"=>$arrPost['hdnuserid'],"name"=>$arrPost['name'],"email"=>$arrPost['email'],"phone"=>$arrPost['phonenumber'],"username"=>$arrPost['username'],"password"=>$encrypt_pass);
				$this->updateArray('pms_admin',$arrNewRecords);
		}	
		return true;
		}
		
		function fnUpdateSettings($arrPost)
		{
			$this->updateArray('pms_settings',$arrPost);
			return true;
		}
		
		function fnGetUserSettings()
		{
			$arrUserSetting = array();
			$query = "SELECT * FROM `pms_settings` WHERE `id` = '1'";
			$this->query($query);
			
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrUserSetting[] = $this->fetchrow();
				}
			}
			return $arrUserSetting;
		}
		
	}
?>
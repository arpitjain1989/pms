<?php

	include("common.php");
	include("includes/class.login.php");

	$objLogin = new clsLogin();

	$objLogin->fnLogout();

	unset($_SESSION);

	session_destroy();

	header("Location: index.php");
	exit;

?>

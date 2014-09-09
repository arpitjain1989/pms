<?php

	include('common.php');

	set_time_limit(0);

	include_once('includes/class.roster.php');
	
	$objRoster = new roster();

	$objRoster->fnFinalizeRoster();

	echo "done";

?>

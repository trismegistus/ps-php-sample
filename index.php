<?php
	require "db.php";
	require "auth.php";

	try
	{
		$db = new DB();
	}
	catch(Exception $e)
	{
		die($e->getMessage());
	}
	$user = auth($db);

	echo "logged in!";
?>

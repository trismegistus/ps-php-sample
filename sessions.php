<?php
	//normally, i'd avoid mixing php and html like this and use a template system

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
?>
<!DOCTYPE html5>

<html>
<head>
	<title>Session manager</title>
</head>

<body>
<?php
	if(!isset($_GET['action']))
	{
		//display current sessions
		$sessions = $user->getSessions();

		echo "<table>";

		foreach($sessions as $session)
		{
			echo "<tr>";
			echo "<td>" . $session . "</td>";

			//there's probably a better way to do this instead of a large number of forms
			echo "<td>" . 
			"<form action=\"sessions.php?action=endsession\" method=\"post\">" .
				"<input type=\"hidden\" name = \"session\" value=\"" . $session . "\"/>" .
				"<input type=\"submit\" value=\"Log out\"/>" .
			"</form>" .
			"</td>";
		}
		echo "</table>";

		echo "<br/>";
		echo "<a href=\"sessions.php?action=endothers\">Log out all other sessions</a>";
	}
	else
	{
		if($_GET['action'] == "endsession")
		{
			//invalidate a certain session
			if(!isset($_POST['session']))
			{
				echo "<p>missing session</p>";
			}
			else
			{
				try
				{
					$user->invalidateSession($_POST['session']);

					echo "<p>session logged out</p><br/>";
					echo "<a href=\"sessions.php\">Go back</a>";
				}
				catch(Exception $e)
				{
					die("<p>" . $e->getMessage() . "</p>");
				}
			}
		}
		else
		if($_GET['action'] == "endothers")
		{
			//invalidate all other sessions
			try
			{
				$user->invalidateOtherSessions($_COOKIE['user']);

				echo "<p>All other sessions logged out</p><br/>";
				echo "<a href=\"sessions.php\">Go back</a>";
			}
			catch(Exception $e)
			{
				die("<p>" . $e->getMessage() . "</p>");
			}
		}
	}
?>
</body>
</html>

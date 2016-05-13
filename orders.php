<?php
	//I usually avoid mixing php and html like this
	require "db.php";
	require "auth.php";
	require "orderItem.php";

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
	<title>Orders</title>
</head>

<body>
<?php
	if(!isset($_GET['action']))
	{
		$orders = $user->getCurrentOrders();

		echo <<<HERE
			<form action="orders.php?action=update" method="post">
			<table>
				<tr>
					<td>
						Item
					</td>
					<td>
						Quantity
					</td>

					<td>
						Unit Cost
					</td>

					<td>
						Total cost
					</td>
				</tr>
HERE;

		foreach($orders as $order)
		{
			echo "<tr><td>" . $order->product .
			"</td><td><input type=\"hidden\" name=\"ids[]\" value=\"" . $order->id . "\"/>" .
			"<input type=\"text\" name=\"quantities[]\" value=\"" . $order->quantity . "\"/>" .
			"</td>" . 
			"<td>" . $order->price . 
			"</td>" . 
			"<td>" . $order->price * $order->quantity . "</td></tr>";
		}

		echo "</table><br/><input type=\"submit\" value=\"Update\"/></form>";	
	}
?>
</body>
</html>

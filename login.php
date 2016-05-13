<?php
	require "db.php";

	function login($db, $username, $password)
	{
		try
		{
			//fetch the userid and password hash
			$statement = $db->prepare("SELECT id, hash FROM users WHERE username = ? LIMIT 1");
			if(!$statement) throw new Exception("Cannot prepare statement");

			$result = $statement->bind_param("s", $username);
			if(!$result) throw new Exception("cannot bind parameters");

			$result = $statement->bind_result($userid, $hash);
			if(!$result) throw new Exception("cannot bind results");

			$result = $statement->execute();
			if(!$result) throw new Exception("cannot execute statement");

			$result = $statement->store_result();
			if(!$result) throw new Exception("Cannot store results");
		
			if($statement->num_rows() == 0)
			{
				//user doesn't exist
				echo file_get_contents("invalidlogin.html");
			}
			else
			{
				//user does exist
				$result = $statement->fetch();
				if(!$result) throw new Exception("Cannot fetch results");

				if(!password_verify($password, $hash))
				{
					//wrong password
					echo file_get_contents("invalidlogin.html");
				}
				else
				{
					//right password
					//generate random bytes to act as a token
					$token = bin2hex(openssl_random_pseudo_bytes(32));

					try
					{
						//insert session token into the active sessions table
						$loginStatement = $db->prepare("INSERT INTO user_sessions (userid, token, created) VALUES(?, ?, NOW())");
						if(!$loginStatement) throw new Exception("Cannot prepare login statement");

						$result = $loginStatement->bind_param("is", $userid, $token);
						if(!$result) throw new Exception("cannot bind login parameters");

						$result = $loginStatement->execute();
						if(!$result) throw new Exception("cannot execute login statement");

						//cookie expires in a year
						setcookie("user", $token, time() + 60 * 60 * 24 * 356, "/php/", "dev.zieba.site");

						echo file_get_contents("loginsuccess.html");
					}
					finally
					{
						if($loginStatement)
						{
							$loginStatement->close();
						}
					}
				}
			}
		}
		finally
		{
			if($statement)
			{
				$statement->close();
			}
		}
	}

	function logout($db, $token)
	{
		try
		{
			//delete the token from the active sessions table
			$statement = $db->prepare("DELETE FROM user_sessions WHERE token = ?");
			if(!$statement) throw new Exception("Cannot prepare statement");

			$result = $statement->bind_param("s", $token);
			if(!$result) throw new Exception("Cannot bind parameters");

			$result = $statement->execute();
			if(!$result) throw new Exception("Cannot execute statement");

			echo "Logout successful";
		}
		finally
		{
			$statement->close();
		}
	}
	
	if(isset($_GET['action']))
	{
		try
		{
			$db = new DB();
			if($_GET['action'] == "login")
			{
				if(!isset($_POST['username']) || !isset($_POST['password']))
				{
					echo file_get_contents("invalidlogin.html");
				}
				else
				{
					login($db, $_POST['username'], $_POST['password']);
				}
			}
			else
			if($_GET['action'] == "logout")
			{
				if(isset($_COOKIE['user']) && is_string($_COOKIE['user']))
				{
					logout($db, $_COOKIE['user']);
				}
				else
				{
					//there's no need to display an error message for this
					//but logging it internally would be useful
					echo "Logout successful";
				}
			}
			else
			{
				echo "Invalid action";
			}
		}
		catch(Exception $e)
		{
			die($e->getMessage());
		}
		finally
		{
			if($db)
			{
				$db->close();
			}
		}
	}
	else
	{
		echo "Invalid action";
	}	
?>

<?php
	class User
	{
		private $userid;
		private $db;

		function __construct($id, $conn)
		{
			$this->userid = $id;
			$this->db = $conn;
		}

		//returns an array of all active sessions for the current user
		function getSessions()
		{
			try
			{
				$tokens = array();

				$statement = $this->db->executeStatement("SELECT token FROM user_sessions WHERE userid = ?", "i", $this->userid);

				$result = $statement->bind_result($token);
				if(!$result) throw new Exception("Cannot bind results " . $this->db->error);

				while($statement->fetch())
				{
					array_push($tokens, $token);
				}
			}
			finally
			{
				if($statement)
				{
					$statement->close();
				}
			}

			return $tokens;
		}

		//invalidates a session token, anybody using that token will be forced to login again
		function invalidateSession($token)
		{
			try
			{
				$statement = $this->db->executeStatement("DELETE FROM user_sessions WHERE token = ?", "s", $token);
			}
			finally
			{
				if($statement)
				{
					$statement->close();
				}
			}	
		}

		//invalidates all tokens except for $token
		function invalidateOtherSessions($token)
		{
			try
			{
				$statement = $this->db->executeStatement("DELETE FROM user_sessions WHERE token <> ?", "s", $token);
			}
			finally
			{
				if($statement)
				{
					$statement->close();
				}
			}
		}
	}

	//checks to see if the user is logged in, returns a User object if so, otherwise, display a login page
	function auth($db)
	{
		//if the cookie doesn't exist/isn't valid
		if(!isset($_COOKIE['user']) || ! is_string($_COOKIE['user']))
		{
			die(file_get_contents("login.html"));
		}
		else
		{
			try
			{
				//check to see if the token exists

				$statement = $db->executeStatement("SELECT userid FROM user_sessions WHERE token = ? LIMIT 1", "s", $_COOKIE['user']);

				$result = $statement->store_result();
				if(!$result) throw new Exception("Cannot store results " . $statement->error);

				if($statement->num_rows() != 0)
				{
					//the user is logged in
					$result = $statement->bind_result($userid);
					if(!$result) throw new Exception("Cannot bind result " . $statement->error);

					$result = $statement->fetch();
					if(!$result) throw new Exception("Cannot fetch results" . $statment->error);

					$user = new User($userid, $db);
					return $user;
				}
				else
				{
					die(file_get_contents("login.html"));
				}
			}
			catch(Exception $e)
			{
				die($e->getMessage());
			}
			finally
			{
				if($statement)
				{
					$statement->close();
				}
			}
		}
	}	
?>

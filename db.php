<?php
	class DB extends mysqli
	{
		function __construct()
		{
			//normally, I would not embed the db auth info in the code, but load it from a seperate config file
			parent::__construct("", "", "", "");

			if($this->connect_error)
			{
				throw new Exception($this->error);
			}
		}

		//prepares, binds parameters and executes a statement
		function executeStatement($prep, $types, ...$vars)
		{
			$statement = $this->prepare($prep);
			if(!$statement) throw new Exception("Cannot prepare statment " . $this->error);

			$result = $statement->bind_param($types, ...$vars);
			if(!$result) throw new Exception("Cannot bind parameters " . $this->error);

			$result = $statement->execute();
			if(!$result) throw new Exception("Cannot execute statement " . $this->error);

			return $statement;
		}
	}
?>

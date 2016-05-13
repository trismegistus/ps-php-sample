<?php
	require "db.php";

	class OrderItem
	{
		public $price;
		public $quantity;
		public $itemNumber;
		public $description;

		private $originalPrice;
		private $originalQuantity;

		private $db;
		private $orderId;

		function __construct($conn, $id)
		{
			$this->orderId = $id;
			$this->$db = $conn;

			$statement = $this->db->executeStatement("SELECT price, quantity, description, itemNumber FROM orders WHERE id = ? LIMIT 1", "i", $id);
			$statement->bind_result($this->originalPrice, $this->originalQuantity, $this->description, $this->itemNumber);
			$statement->fetch();

			$this->price = $this->originalPrice;
			$this->quantity = $this->originalQuantity;

			$statement->close();
		}

		function update()
		{
			$query = "UPDATE orders SET";
			$commaNeeded = false;

			if($this->price != $this->originalPrice)
			{
				$query .= " price = ?";
				$commaNeeded = true;
			}

			if($this->quantity != $this->originalQuantity)
			{
				if($commaNeeded) $query .= ",";
				$query .= " quantity = ?";
				$commaNeeded = true;
			}

			$query .= " WHERE id = ? LIMIT 1";

			$this->db->executeStatement($query, "i", $this->orderId);
		}

		static function createNewOrderItem($db, $userId, $itemNumber, $quantity)
		{
			
			$statement = $db->executeStatement("SELECT id from (INSERT INTO orders (price, quantity, description, itemNumber) VALUES(\
									SELECT price, description, ?, ? FROM products where itemNumber = ? LIMIT 1)\
									) WHERE (SELECT COUNT(itemNumber) FROM products WHERE itemNumber = ? LIMIT 1)");

			$statement->store_result();
			$rows = $statement->num_rows();

			if($rows == 0)
			{
				$statement->close();
				return null;
		
			}
			else
			{
				$statement->bind_result($id);
				$statement->fetch();

				return new OrderItem($db, $id);
			}
		}
	}
?>

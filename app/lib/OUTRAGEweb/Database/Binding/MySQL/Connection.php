<?php
/**
 *	Database connection class for OUTRAGEweb
 */


namespace OUTRAGEweb\Database\Binding\MySQL;

use \OUTRAGEweb\Cache;
use \OUTRAGEweb\Construct;
use \OUTRAGEweb\Construct\Ability;


class Connection
{
	/**
	 *	We want to declare that this is a Singleton object - we also need the
	 *	delegator, and all the associated delegations.
	 */
	use Ability\Singleton;
	use Ability\Delegator;
	use Ability\Delegation;
	
	
	/**
	 *	How many queries have we sent out?
	 */
	protected $count = 0;
	
	
	/**
	 *	What connection are we using?
	 */
	protected $connection = null;
	
	
	/**
	 *	Variables to do with transactions
	 */
	protected $transaction = false;
	protected $transaction_error = null;
	
	
	/**
	 *	Called when the connection has been initialised.
	 */
	public function __construct($config = null)
	{
		if(!$config)
			$config = $this->config->database->production;
		
		$this->connection = new \PDO("mysql:host=".$config["host"].";port=".$config["port"].";dbname=".$config["database"], $config["username"], $config["password"]);
	}
	
	
	/**
	 *	Provide an interface for the actual connection.
	 */
	public function getConnection()
	{
		return $this->connection;
	}
	
	
	/**
	 *	Return the amount of queries this connection has sent.
	 */
	public function count()
	{
		return $this->count;
	}
	
	
	/**
	 *	Create a nice and ordered select query.
	 */
	public function select()
	{
		return new Select($this);
	}
	
	
	/**
	 *	Send off a query.
	 */
	public function query($expression = null, array $arguments = null, $named = false)
	{
		$stmt = $this->connection->prepare($expression);
		
		if($arguments === null)
			$stmt->execute();
		else
			$stmt->execute($named ? $arguments : array_values($arguments));
		
		if($stmt->errorCode() !== "00000")
		{
			if($this->transaction)
				$this->transaction_error = true;
			
			throw new \Exception("Database error: [".implode(", ", $stmt->errorInfo())."] with query '".$stmt->queryString."'");
		}
		
		++$this->count;
		
		$result = new Result($stmt->queryString);
		
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC))
			$result->push(new \ArrayObject($item));
		
		$stmt->closeCursor();
		
		return $result;
	}
	
	
	/**
	 *	Describe a table.
	 */
	public function describe($table)
	{
		$cache = Cache\File::getInstance();
		$key = "__describe_".$table;
		
		if($cache->test($key))
			return array_keys($cache->load($key));
		
		$details = [];
		
		$stmt = $this->connection->prepare("DESCRIBE ".$this->quoteIdentifier($table));
		$stmt->execute();
		
		while($item = $stmt->fetch(\PDO::FETCH_ASSOC))
		{
			$description = [];
			$spec = [];
			
			if(preg_match("/^(.*?)(\((.*)\))?$/", $item["Type"], $spec))
			{
				$description["field"] = $item["Field"];
				$description["type"] = $spec[1];
				
				if(isset($spec[3]))
				{
					if($spec[1] == "enum")
					{
						if(!class_exists("\Services_JSON"))
							require APP_DIR."/lib/PEAR/Services/JSON.php";
						
						# bad things happen here
						$handler = new \Services_JSON(\SERVICES_JSON_LOOSE_TYPE);
						$description["values"] = $handler->decode("[ ".$spec[3]." ]");
					}
					else
					{
						$description["width"] = (integer) $spec[3];
					}
				}
				
				$description["default"] = $item["Default"];
				
				$details[$item["Field"]] = $description;
			}
		}
		
		if($details)
			$cache->save($key, $details);
		
		return array_keys($details);
	}
	
	
	/**
	 *	Insert values into a table.
	 */
	public function insert($table, $values = [], $fields = null)
	{
		$fragments = [];
		
		$fields = is_array($fields) ? $fields : $this->describe($table);
		$values = array_intersect_key($values, array_flip($fields));
		
		foreach($values as $field => $value)
		{
			if($value === null)
			{
				$fragments[] = "NULL";
				unset($values[$field]);
			}
			else
			{
				$fragments[] = "?";
			}
		}
		
		if(!count($fragments))
			return null;
		
		$keys = array_keys($values);
		$values = array_values($values);
		
		foreach($keys as &$key)
			$key = $this->quoteIdentifier($key);
		
		$this->query("INSERT INTO ".$this->quoteIdentifier($table)." (".implode(", ", $keys).") VALUES (".implode(", ", $fragments).")", $values);
		
		return $this->connection->lastInsertId();
	}
	
	
	/**
	 *	Update values in a table.
	 */
	public function update($table, $values = [], $where = null, $limit = null, $fields = null)
	{
		$fragments = [];
		
		$fields = is_array($fields) ? $fields : $this->describe($table);
		$values = array_intersect_key($values, array_flip($fields));
		
		foreach($values as $field => $value)
		{
			if($value === null)
			{
				$fragments[] = $this->quoteIdentifier($field)." = NULL";
				unset($values[$field]);
			}
			else
			{
				$fragments[] = $this->quoteIdentifier($field)." = ?";
			}
		}
		
		if(!count($fragments))
			return null;
		
		$query = "UPDATE ".$this->quoteIdentifier($table)." SET ".implode(", ", $fragments);
		
		if($where)
		{
			if(is_array($where))
			{
				$conditions = [];
				
				foreach($where as $field => $value)
				{
					if(is_int($field))
						$conditions[] = $value;
					else
						$conditions[] = $this->quoteIdentifier($field)." = ".$this->quote($value);
				}
				
				$query .= " WHERE ".implode(" AND ", $conditions);
			}
			else
			{
				$query .= " WHERE ".$where;
			}
		}
		
		if($limit)
			$query .= " LIMIT ".$this->quote($limit);
		
		$this->query($query, $values);
		return true;
	}
	
	
	/**
	 *	Delete values from a table.
	 */
	public function delete($table, $where = null, $limit = null)
	{
		if($where === null && $limit === null)
			return $this->query("TRUNCATE TABLE ".$this->quoteIdentifier($table));
		
		$query = "DELETE FROM ".$this->quoteIdentifier($table);
		
		if($where)
		{
			if(is_array($where))
			{
				$conditions = [];
				
				foreach($where as $field => $value)
				{
					if(is_int($field))
						$conditions[] = $value;
					else
						$conditions[] = $this->quoteIdentifier($field)." = ".$this->quote($value);
				}
				
				$query .= " WHERE ".implode(" AND ", $conditions);
			}
			else
			{
				$query .= " WHERE ".$where;
			}
		}
		
		if($limit)
			$query .= " LIMIT ".$this->quote($limit);
		
		return $this->query($query);
	}
	
	
	/**
	 *	Begin a transaction.
	 */
	public function begin()
	{
		$this->connection->beginTransaction();
		
		$this->transaction = true;
		$this->transaction_error = false;
		
		return $this;
	}
	
	
	/**
	 *	Commit a transaction.
	 */
	public function commit()
	{
		$this->connection->commit();
		
		$this->transaction = false;
		$this->transaction_error = null;
		
		return $this;
	}
	
	
	/**
	 *	Rollback a transaction.
	 */
	public function rollback()
	{
		$this->connection->rollback();
		
		$this->transaction = false;
		$this->transaction_error = null;
		
		return $this;
	}
	
	
	/**
	 *	Dealing with transactions: For ease of use, we can put all of the queries we want
	 *	in a single transaction in a Closure, and run it in the parent scope.
	 *
	 *	Any exceptions thrown in the course of the closure's execution will result in
	 *	the transaction being rolled back.
	 */
	public function transaction(Closure $callback)
	{
		$this->begin();
		
		try
		{
			$reflection = new \ReflectionFunction($callback);
			$reflection->invoke();
			
			if($this->transaction_error)
				return $this->rollback();
		}
		catch(Exception $exception)
		{
			return $this->rollback();
		}
		
		return $this->commit();
	}
	
	
	/**
	 *	Vendor specific value quoting.
	 */
	public function quote($value)
	{
		if(is_array($value))
		{
			foreach($value as $key => $item)
				$value[$key] = $this->quote($item);
			
			return $value;
		}
		
		if(is_int($value))
			return $value;
		
		return $this->connection->quote($value);
	}
	
	
	/**
	 *	Vendor specific value psuedo-statement control.
	 */
	public function quoteInto($expression, $value = null)
	{
		if($value !== null)
		{
			if(is_array($value) || $value instanceof Traversable)
			{
				$set = [];
				
				foreach($value as $item)
					$set[] = $this->quote($item);
				
				$value = implode(", ", $set);
			}
			else
			{
				$value = $this->quote($value);
			}
			
			return str_replace("?", $value, $expression);
		}
		
		return $expression;
	}
	
	
	/**
	 *	Vendor specific identifier quoting.
	 */
	public function quoteIdentifier($identifier)
	{
		if(preg_match("/[^\w:]/", $identifier))
		{
			/**
			 *	@todo: implement further parsing based on these rules:
			 *	
			 *	table AS alias
			 *	db.table AS alias
			 *	db.table
			 */
			
			return $identifier;
		}
		
		return "`".((string) $identifier)."`";
	}
	
	
	/**
	 *	Quotes a table reference.
	 */
	public function quoteTable($database, $table)
	{
		return $this->quoteIdentifier($database).".".$this->quoteIdentifier($table);
	}
}

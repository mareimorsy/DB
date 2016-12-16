<?php
//====================== Marei DB Class V 1.0 ======================
$db_config = [
	//current development environment
	"env" => "development",
	//Localhost
	"development" => [
						"host" => "localhost",
						"database" => "test",
						"username" => "root",
						"password" => ""
					 ],
	//Server
	"production"  => [
						"host" => "",
						"database" => "",
						"username" => "",
						"password" => ""
					 ]
];

class DB{
	private static $instance = null;
	private $dbh = null, $table, $columns, $sql, $bindValues, $getSQL,
	$where, $orWhere, $whereCount=0, $isOrWhere = false,
	$rowCount=0, $limit, $lastIDInserted = 0;

	// Initial values for pagination array
	private $pagination = ['previousPage' => null,'currentPage' => 1,'nextPage' => null,'lastPage' => null, 'totalRows' => null];

	private function __construct()
	{
		global $db_config;

		if ($db_config['env'] == "development") {
			$config = $db_config['development'];
		}elseif ($db_config['env'] == "production") {
			$config = $db_config['production'];
		}else{
			die("Environment must be either 'development' or 'production'.");
		}

		try {
			$this->dbh = new PDO("mysql:host=".$config['host'].";dbname=".$config['database'].";charset=utf8", $config['username'], $config['password'] );
			$this->dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
			$db_config = null;
		} catch (Exception $e) {
			die("Error establishing a database connection.");
		}

	}

	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new DB();
		}
		return self::$instance;
	}
	public function query($query, $args = [], $quick = false)
	{
		$this->resetQuery();
		$query = trim($query);
		$this->getSQL = $query;
		$this->bindValues = $args;

		if ($quick == true) {
			$stmt = $this->dbh->prepare($query);
			$stmt->execute($this->bindValues);
			$this->rowCount = $stmt->rowCount();
			return $stmt->fetchAll();
		}else{
			if (strpos( strtoupper($query), "SELECT" ) === 0 ) {
				$stmt = $this->dbh->prepare($query);
				$stmt->execute($this->bindValues);
				$this->rowCount = $stmt->rowCount();

				$rows = $stmt->fetchAll(PDO::FETCH_CLASS,'MareiObj');
				$collection= [];
				$collection = new MareiCollection;
				$x=0;
				foreach ($rows as $key => $row) {
					$collection->offsetSet($x++,$row);
				}

				return $collection;

			}else{
				$this->getSQL = $query;
				$stmt = $this->dbh->prepare($query);
				$stmt->execute($this->bindValues);
				return $stmt->rowCount();
			}
		}
	}
	public function exec()
	{
		//assimble query
			$this->sql .= $this->where;
			$this->getSQL = $this->sql;
			$stmt = $this->dbh->prepare($this->sql);
			$stmt->execute($this->bindValues);
			return $stmt->rowCount();
	}

	private function resetQuery()
	{
		$this->table = null;
		$this->columns = null;
		$this->sql = null;
		$this->bindValues = null;
		$this->limit = null;
		$this->getSQL = null;
		$this->where = null;
		$this->orWhere = null;
		$this->whereCount = 0;
		$this->isOrWhere = false;
		$this->rowCount = 0;
		$this->lastIDInserted = 0;
	}

	public function delete($table_name, $id=null)
	{
		$this->resetQuery();

		$this->sql = "DELETE FROM `{$table_name}`";
		
		if (isset($id)) {
			// if there is an ID
			if (is_integer($id)) {
				$this->sql .= " WHERE `id` = ?";
				$this->bindValues[] = $id;
			// if there is an Array
			}elseif (is_array($id)) {
				$arr = $id;
				$count_arr = count($arr);
				$x = 0;

				foreach ($arr as  $param) {
					if ($x == 0) {
						$this->where .= " WHERE ";
						$x++;
					}else{
						if ($this->isOrWhere) {
							$this->where .= " Or ";
						}else{
							$this->where .= " AND ";
						}
						
						$x++;
					}
					$count_param = count($param);

					if ($count_param == 1) {
						$this->where .= "`id` = ?";
						$this->bindValues[] =  $param[0];
					}elseif ($count_param == 2) {
						$operators = explode(',', "=,>,<,>=,>=,<>");
						$operatorFound = false;

						foreach ($operators as $operator) {
							if ( strpos($param[0], $operator) !== false ) {
								$operatorFound = true;
								break;
							}
						}

						if ($operatorFound) {
							$this->where .= $param[0]." ?";
						}else{
							$this->where .= "`".trim($param[0])."` = ?";
						}

						$this->bindValues[] =  $param[1];
					}elseif ($count_param == 3) {
						$this->where .= "`".trim($param[0]). "` ". $param[1]. " ?";
						$this->bindValues[] =  $param[2];
					}

				}
				//end foreach
			}
			// end if there is an Array
			$this->sql .= $this->where;

			$this->getSQL = $this->sql;
			$stmt = $this->dbh->prepare($this->sql);
			$stmt->execute($this->bindValues);
			return $stmt->rowCount();
		}// end if there is an ID or Array
		// $this->getSQL = "<b>Attention:</b> This Query will update all rows in the table, luckily it didn't execute yet!, use exec() method to execute the following query :<br>". $this->sql;
		// $this->getSQL = $this->sql;
		return $this;
	}

	public function update($table_name, $fields = [], $id=null)
	{
		$this->resetQuery();
		$set ='';
		$x = 1;

		foreach ($fields as $column => $field) {
			$set .= "`$column` = ?";
			$this->bindValues[] = $field;
			if ( $x < count($fields) ) {
				$set .= ", ";
			}
			$x++;
		}

		$this->sql = "UPDATE `{$table_name}` SET $set";
		
		if (isset($id)) {
			// if there is an ID
			if (is_integer($id)) {
				$this->sql .= " WHERE `id` = ?";
				$this->bindValues[] = $id;
			// if there is an Array
			}elseif (is_array($id)) {
				$arr = $id;
				$count_arr = count($arr);
				$x = 0;

				foreach ($arr as  $param) {
					if ($x == 0) {
						$this->where .= " WHERE ";
						$x++;
					}else{
						if ($this->isOrWhere) {
							$this->where .= " Or ";
						}else{
							$this->where .= " AND ";
						}
						
						$x++;
					}
					$count_param = count($param);

					if ($count_param == 1) {
						$this->where .= "`id` = ?";
						$this->bindValues[] =  $param[0];
					}elseif ($count_param == 2) {
						$operators = explode(',', "=,>,<,>=,>=,<>");
						$operatorFound = false;

						foreach ($operators as $operator) {
							if ( strpos($param[0], $operator) !== false ) {
								$operatorFound = true;
								break;
							}
						}

						if ($operatorFound) {
							$this->where .= $param[0]." ?";
						}else{
							$this->where .= "`".trim($param[0])."` = ?";
						}

						$this->bindValues[] =  $param[1];
					}elseif ($count_param == 3) {
						$this->where .= "`".trim($param[0]). "` ". $param[1]. " ?";
						$this->bindValues[] =  $param[2];
					}

				}
				//end foreach
			}
			// end if there is an Array
			$this->sql .= $this->where;

			$this->getSQL = $this->sql;
			$stmt = $this->dbh->prepare($this->sql);
			$stmt->execute($this->bindValues);
			return $stmt->rowCount();
		}// end if there is an ID or Array
		// $this->getSQL = "<b>Attention:</b> This Query will update all rows in the table, luckily it didn't execute yet!, use exec() method to execute the following query :<br>". $this->sql;
		// $this->getSQL = $this->sql;
		return $this;
	}

	public function insert( $table_name, $fields = [] )
	{
		$this->resetQuery();

		$keys = implode('`, `', array_keys($fields));
		$values = '';
		$x=1;
		foreach ($fields as $field => $value) {
			$values .='?';
			$this->bindValues[] =  $value;
			if ($x < count($fields)) {
				$values .=', ';
			}
			$x++;
		}
 
		$this->sql = "INSERT INTO `{$table_name}` (`{$keys}`) VALUES ({$values})";
		$this->getSQL = $this->sql;
		$stmt = $this->dbh->prepare($this->sql);
		$stmt->execute($this->bindValues);
		$this->lastIDInserted = $this->dbh->lastInsertId();

		return $this->lastIDInserted;
	}//End insert function

	public function lastId()
	{
		return $this->lastIDInserted;
	}

	public function table($table_name)
	{
		$this->resetQuery();
		$this->table = $table_name;
		return $this;
	}

	public function select($columns)
	{
		$columns = explode(',', $columns);
		foreach ($columns as $key => $column) {
			$columns[$key] = trim($column);
		}
		
		$columns = implode('`, `', $columns);
		

		$this->columns = "`{$columns}`";
		return $this;
	}

	public function where()
	{
		if ($this->whereCount == 0) {
			$this->where .= " WHERE ";
			$this->whereCount+=1;
		}else{
			$this->where .= " AND ";
		}

		$this->isOrWhere= false;

		// call_user_method_array('where_orWhere', $this, func_get_args());
		//Call to undefined function call_user_method_array()
		//echo print_r(func_num_args());
		$num_args = func_num_args();
		$args = func_get_args();
		if ($num_args == 1) {
			if (is_integer($args[0])) {
				$this->where .= "`id` = ?";
				$this->bindValues[] =  $args[0];
			}elseif (is_array($args[0])) {
				$arr = $args[0];
				$count_arr = count($arr);
				$x = 0;

				foreach ($arr as  $param) {
					if ($x == 0) {
						$x++;
					}else{
						if ($this->isOrWhere) {
							$this->where .= " Or ";
						}else{
							$this->where .= " AND ";
						}
						
						$x++;
					}
					$count_param = count($param);
					if ($count_param == 1) {
						$this->where .= "`id` = ?";
						$this->bindValues[] =  $param[0];
					}elseif ($count_param == 2) {
						$operators = explode(',', "=,>,<,>=,>=,<>");
						$operatorFound = false;

						foreach ($operators as $operator) {
							if ( strpos($param[0], $operator) !== false ) {
								$operatorFound = true;
								break;
							}
						}

						if ($operatorFound) {
							$this->where .= $param[0]." ?";
						}else{
							$this->where .= "`".trim($param[0])."` = ?";
						}

						$this->bindValues[] =  $param[1];
					}elseif ($count_param == 3) {
						$this->where .= "`".trim($param[0]). "` ". $param[1]. " ?";
						$this->bindValues[] =  $param[2];
					}
				}
			}
			// end of is array
		}elseif ($num_args == 2) {
			$operators = explode(',', "=,>,<,>=,>=,<>");
			$operatorFound = false;
			foreach ($operators as $operator) {
				if ( strpos($args[0], $operator) !== false ) {
					$operatorFound = true;
					break;
				}
			}

			if ($operatorFound) {
				$this->where .= $args[0]." ?";
			}else{
				$this->where .= "`".trim($args[0])."` = ?";
			}

			$this->bindValues[] =  $args[1];

		}elseif ($num_args == 3) {
			
			$this->where .= "`".trim($args[0]). "` ". $args[1]. " ?";
			$this->bindValues[] =  $args[2];
		}

		return $this;
	}

	public function orWhere()
	{
		if ($this->whereCount == 0) {
			$this->where .= " WHERE ";
			$this->whereCount+=1;
		}else{
			$this->where .= " OR ";
		}
		$this->isOrWhere= true;
		// call_user_method_array ( 'where_orWhere' , $this ,  func_get_args() );

		$num_args = func_num_args();
		$args = func_get_args();
		if ($num_args == 1) {
			if (is_integer($args[0])) {
				$this->where .= "`id` = ?";
				$this->bindValues[] =  $args[0];
			}elseif (is_array($args[0])) {
				$arr = $args[0];
				$count_arr = count($arr);
				$x = 0;

				foreach ($arr as  $param) {
					if ($x == 0) {
						$x++;
					}else{
						if ($this->isOrWhere) {
							$this->where .= " Or ";
						}else{
							$this->where .= " AND ";
						}
						
						$x++;
					}
					$count_param = count($param);
					if ($count_param == 1) {
						$this->where .= "`id` = ?";
						$this->bindValues[] =  $param[0];
					}elseif ($count_param == 2) {
						$operators = explode(',', "=,>,<,>=,>=,<>");
						$operatorFound = false;

						foreach ($operators as $operator) {
							if ( strpos($param[0], $operator) !== false ) {
								$operatorFound = true;
								break;
							}
						}

						if ($operatorFound) {
							$this->where .= $param[0]." ?";
						}else{
							$this->where .= "`".trim($param[0])."` = ?";
						}

						$this->bindValues[] =  $param[1];
					}elseif ($count_param == 3) {
						$this->where .= "`".trim($param[0]). "` ". $param[1]. " ?";
						$this->bindValues[] =  $param[2];
					}
				}
			}
			// end of is array
		}elseif ($num_args == 2) {
			$operators = explode(',', "=,>,<,>=,>=,<>");
			$operatorFound = false;
			foreach ($operators as $operator) {
				if ( strpos($args[0], $operator) !== false ) {
					$operatorFound = true;
					break;
				}
			}

			if ($operatorFound) {
				$this->where .= $args[0]." ?";
			}else{
				$this->where .= "`".trim($args[0])."` = ?";
			}

			$this->bindValues[] =  $args[1];

		}elseif ($num_args == 3) {
			
			$this->where .= "`".trim($args[0]). "` ". $args[1]. " ?";
			$this->bindValues[] =  $args[2];
		}

		return $this;
	}

	// private function where_orWhere()
	// {

	// }

	public function get()
	{
		$this->assimbleQuery();
		$this->getSQL = $this->sql;

		$stmt = $this->dbh->prepare($this->sql);
		$stmt->execute($this->bindValues);
		$this->rowCount = $stmt->rowCount();

		$rows = $stmt->fetchAll(PDO::FETCH_CLASS,'MareiObj');
		$collection= [];
		$collection = new MareiCollection;
		$x=0;
		foreach ($rows as $key => $row) {
			$collection->offsetSet($x++,$row);
		}

		return $collection;
	}
	// Quick get
	public function QGet()
	{
		$this->assimbleQuery();
		$this->getSQL = $this->sql;

		$stmt = $this->dbh->prepare($this->sql);
		$stmt->execute($this->bindValues);
		$this->rowCount = $stmt->rowCount();

		return $stmt->fetchAll();
	}


	private function assimbleQuery()
	{
		if ( $this->columns !== null ) {
			$select = $this->columns;
		}else{
			$select = "*";
		}

		$this->sql = "SELECT $select FROM `$this->table`";

		if ($this->where !== null) {
			$this->sql .= $this->where;
		}

		if ($this->limit !== null) {
			$this->sql .= $this->limit;
		}
	}

	public function limit($limit, $offset=null)
	{
		if ($offset ==null ) {
			$this->limit = " LIMIT {$limit}";
		}else{
			$this->limit = " LIMIT {$limit} OFFSET {$offset}";
		}

		return $this;
	}

	public function paginate($page, $limit)
	{
		// Start assimble Query
		$countSQL = "SELECT COUNT(*) FROM `$this->table`";
		if ($this->where !== null) {
			$countSQL .= $this->where;
		}
		// Start assimble Query

		$stmt = $this->dbh->prepare($countSQL);
		$stmt->execute($this->bindValues);
		$totalRows = $stmt->fetch(PDO::FETCH_NUM)[0];
		// echo $totalRows;

		$offset = ($page-1)*$limit;
		// Refresh Pagination Array
		$this->pagination['currentPage'] = $page;
		$this->pagination['lastPage'] = ceil($totalRows/$limit);
		$this->pagination['nextPage'] = $page + 1;
		$this->pagination['previousPage'] = $page-1;
		$this->pagination['totalRows'] = $totalRows;
		// if last page = current page
		if ($this->pagination['lastPage'] ==  $page) {
			$this->pagination['nextPage'] = null;
		}
		if ($page == 1) {
			$this->pagination['previousPage'] = null;
		}
		if ($page > $this->pagination['lastPage']) {
			return [];
		}

		$this->assimbleQuery();

		$sql = $this->sql . " LIMIT {$limit} OFFSET {$offset}";
		$this->getSQL = $sql;

		$stmt = $this->dbh->prepare($sql);
		$stmt->execute($this->bindValues);
		$this->rowCount = $stmt->rowCount();


		$rows = $stmt->fetchAll(PDO::FETCH_CLASS,'MareiObj');
		$collection= [];
		$collection = new MareiCollection;
		$x=0;
		foreach ($rows as $key => $row) {
			$collection->offsetSet($x++,$row);
		}

		return $collection;
	}

	public function count()
	{
		// Start assimble Query
		$countSQL = "SELECT COUNT(*) FROM `$this->table`";

		if ($this->where !== null) {
			$countSQL .= $this->where;
		}

		if ($this->limit !== null) {
			$countSQL .= $this->limit;
		}
		// End assimble Query

		$stmt = $this->dbh->prepare($countSQL);
		$stmt->execute($this->bindValues);

		$this->getSQL = $countSQL;

		return $stmt->fetch(PDO::FETCH_NUM)[0];
	}


	public function QPaginate($page, $limit)
	{
		// Start assimble Query
		$countSQL = "SELECT COUNT(*) FROM `$this->table`";
		if ($this->where !== null) {
			$countSQL .= $this->where;
		}
		// Start assimble Query

		$stmt = $this->dbh->prepare($countSQL);
		$stmt->execute($this->bindValues);
		$totalRows = $stmt->fetch(PDO::FETCH_NUM)[0];
		// echo $totalRows;

		$offset = ($page-1)*$limit;
		// Refresh Pagination Array
		$this->pagination['currentPage'] = $page;
		$this->pagination['lastPage'] = ceil($totalRows/$limit);
		$this->pagination['nextPage'] = $page + 1;
		$this->pagination['previousPage'] = $page-1;
		$this->pagination['totalRows'] = $totalRows;
		// if last page = current page
		if ($this->pagination['lastPage'] ==  $page) {
			$this->pagination['nextPage'] = null;
		}
		if ($page == 1) {
			$this->pagination['previousPage'] = null;
		}
		if ($page > $this->pagination['lastPage']) {
			return [];
		}

		$this->assimbleQuery();

		$sql = $this->sql . " LIMIT {$limit} OFFSET {$offset}";
		$this->getSQL = $sql;

		$stmt = $this->dbh->prepare($sql);
		$stmt->execute($this->bindValues);
		$this->rowCount = $stmt->rowCount();

		return $stmt->fetchAll();
	}

	public function PaginationInfo()
	{
		return $this->pagination;
	}

	public function getSQL()
	{
		return $this->getSQL;
	}

	public function getCount()
	{
		return $this->rowCount;
	}

	public function rowCount()
	{
		return $this->rowCount;
	}


}
// End Marei DB Class

//Start Marei Object Class
class MareiObj{

    public function toJSON()
    {
        return json_encode($this, JSON_NUMERIC_CHECK);
    }

    public function toArray()
    {
        return (array) $this;
    }

    public function __toString() {
        header("Content-Type: application/json;charset=utf-8");
        return json_encode($this, JSON_NUMERIC_CHECK);
    }
    
}
// End Marei Object Class

//Start Marei collection class
class MareiCollection implements ArrayAccess{

       public function offsetSet($offset, $value) {
               $this->$offset = $value;
       }

       public function toJSON()
       {
           return json_encode($this->toArray(), JSON_NUMERIC_CHECK);
       }

       public function toArray()
       {
        // return (array) get_object_vars($this);
        $array = [];
        foreach ($this as  $mareiObj) {
          $array[] = (array) $mareiObj;
        }
           return $array;
       }

       public function first($offset=0)
       {
           return isset($this->$offset) ? $this->$offset : null;
       }

       public function last($offset=null)
       {
           $offset = count($this);
           return isset($this->$offset) ? $this->$offset : null;
       }

       public function offsetExists($offset) {
           return isset($this->$offset);
       }

       public function offsetUnset($offset) {
           unset($this->$offset);
       }

       public function offsetGet($offset) {
           return isset($this->$offset) ? $this->$offset : null;
       }


      public function item($key) {
          return isset($this->$key) ? $this->$key : null;
      }

      public function __toString() {
          header("Content-Type: application/json;charset=utf-8");
          // return json_encode(get_object_vars($this));
          return  $this->toJSON();

      }

}
// End Marei Collection Class

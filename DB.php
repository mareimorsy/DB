<?php

class DB
{
	// connecting to database
	const HOST = 'localhost';
	const USER = 'root';
	const PASSWORD = '';
	const DB = 'mydb';

	private static $_instance = null;
	private $_connect, $_query, $_sql, $_results, $_errors = [], $_count;
	private $_whereCount = 0, $_bindValues = [], $_table_name = '', $_select_columns = '', $_fetch_result = [];

	
	private function __construct()
	{
		$this->_connect = new PDO("mysql:host=".self::HOST.";dbname=".self::DB, self::USER, self::PASSWORD);
	}

	public function getInstance()
	{
		if (!self::$_instance) {
			self::$_instance = new DB();
		}
		return self::$_instance;
	}

	public function getSQL(){
		return $this->_sql;
	}

	public function exec(){
		$this->_query = $this->_connect->prepare($this->_sql);
		$this->_query->execute($this->_bindValues);
		$this->_whereCount = 0;
		$this->_bindValues = [];
		$this->_table_name = '';
	}

	public function where($column, $operator = null, $value = null){
		$this->_whereCount +=1;
		$table_name = $this->_table_name;
		if ($this->_whereCount == 1) {
			$whereClause = " WHERE ";
		}elseif ($this->_whereCount > 1) {
			$whereClause = " AND ";
		}

		if (isset($operator) AND isset($value)) {
			//`{$table_name}`.`$col` = ?
			$this->_sql .= $whereClause."`{$table_name}`.`$column` $operator ?";
			array_push($this->_bindValues, $value);
		}elseif (isset($operator)) {
			$this->_sql .= $whereClause."`{$table_name}`.`$column` = ?";
			array_push($this->_bindValues, $operator);
		}else{
			$this->_sql .= $whereClause."`{$table_name}`.`id` = ?";
			array_push($this->_bindValues, $column);
		}

		return $this;
	}


	public function orWhere($column, $operator = null, $value = null){
		$this->_whereCount +=1;
		$table_name = $this->_table_name;
		if ($this->_whereCount == 1) {
			$whereClause = " WHERE ";
		}elseif ($this->_whereCount > 1) {
			$whereClause = " OR ";
		}

		if (isset($operator) AND isset($value)) {
			//`{$table_name}`.`$col` = ?
			$this->_sql .= $whereClause."`{$table_name}`.`$column` $operator ?";
			array_push($this->_bindValues, $value);
		}elseif (isset($operator)) {
			$this->_sql .= $whereClause."`{$table_name}`.`$column` = ?";
			array_push($this->_bindValues, $operator);
		}else{
			$this->_sql .= $whereClause."`{$table_name}`.`id` = ?";
			array_push($this->_bindValues, $column);
		}

		return $this;
	}

	// insert('$table_name', $fields = ['Column_name', 'value'])
	public function insert( $table_name, $fields = [] )
	{
		$keys = implode('`, `', array_keys($fields));
		$values = '';
		$x=1;

		foreach ($fields as $field) {
			$values .='?';
			if ($x < count($fields)) {
				$values .=', ';
			}
			$x++;
		}

		$sql = "INSERT INTO `{$table_name}` (`{$keys}`) VALUES ({$values})";
		$this->_sql = $sql;
		// _query -> stmt | _connect -> dbh
		$this->_query = $this->_connect->prepare($sql);
		$this->_query->execute(array_values($fields));
		
	}//End insert function


	//UPDATE `mytable` SET `FName` = 'Karim', `LName` = 'Morsy', `Age` = '26' WHERE `mytable`.`ID` = 16;

	public function update($table_name, $fields = [], $id=null)
	{
		$set ='';
		$x = 1;

		foreach ($fields as $column => $field) {
			$set .= "`$column` = ?";
			if ( $x < count($fields) ) {
				$set .= ", ";
			}
			$x++;
		}

		$this->_sql = "UPDATE `{$table_name}` SET $set";

		if (isset($id)) {
			if (is_integer($id)) {
				$this->_sql .= " WHERE `{$table_name}`.`id` = ?";
				array_push($fields, $id);
				$this->_query = $this->_connect->prepare($this->_sql);
				$this->_query->execute(array_values($fields));
			}elseif (is_array($id)) {
				$where = $id;

				if (is_array($where[0])) {
					$x=1;
					foreach ($where as $condition) {
						// if the where array contains 1 or more parameters
						if ($x == 1) {
							if (count($condition)==1) {
								$this->_sql .= " WHERE `{$table_name}`.`id` = ?";
								array_push($fields, $condition[0]);

							}elseif (count($condition)==2) {
								$col = $condition[0];
								$val = $condition[1];
								array_push($fields, $val);
								$this->_sql .= " WHERE `{$table_name}`.`$col` = ?";
							}elseif (count($condition)==3) {
								$col = $condition[0];
								$operator = $condition[1];
								$val = $condition[2];
								array_push($fields, $val);
								$this->_sql .= " WHERE `{$table_name}`.`$col` $operator ?";
							}
						}else{
							if (count($condition)==1) {
								$this->_sql .= " AND `{$table_name}`.`id` = ?";
								array_push($fields, $condition[0]);
							}elseif (count($condition)==2) {
								$col = $condition[0];
								$val = $condition[1];
								array_push($fields, $val);
								$this->_sql .= " AND `{$table_name}`.`$col` = ?";
							}elseif (count($condition)==3) {
								$col = $condition[0];
								$operator = $condition[1];
								$val = $condition[2];
								array_push($fields, $val);
								$this->_sql .= " AND `{$table_name}`.`$col` $operator ?";
							}
						}

						$x++;
					}



					//echo "<br>". $this->_sql;
				}else{
					// if Where is just 2 or 3 parameters and not an array of where parameters
					if (count($where)==2){
						$col = $where[0];
						$val = $where[1];
						array_push($fields, $val);
						$this->_sql = "UPDATE `{$table_name}` SET $set WHERE `{$table_name}`.`$col` = ?";
					}elseif (count($where)==3){
						$col = $where[0];
						$operator = $where[1];
						$val = $where[2];
						array_push($fields, $val);
						$this->_sql = "UPDATE `{$table_name}` SET $set WHERE `{$table_name}`.`$col` $operator ?";
					}
				}
				

			}

		// if id or where parameter exists
		$this->_query = $this->_connect->prepare($this->_sql);
		$this->_query->execute(array_values($fields));
		}
		$this->_table_name = $table_name;
		$this->_bindValues = array_values($fields);
		return $this;
	}

	//DELETE FROM `mytable` WHERE `mytable`.`ID` = 29
	public function delete($table_name, $id = null){
		$fields = [];
		$this->_sql = "DELETE FROM `{$table_name}`";

		if (isset($id)) {
			if (is_integer($id)) {
				$this->_sql .= " WHERE `{$table_name}`.`id` = ?";
				array_push($fields, $id);
				$this->_query = $this->_connect->prepare($this->_sql);
				$this->_query->execute(array_values($fields));
			}elseif (is_array($id)) {
				$where = $id;
				if (is_array($where[0])) {
					$x=1;
					foreach ($where as $condition) {
						// if the where array contains 1 or more parameters
						if ($x == 1) {
							if (count($condition)==1) {
								$this->_sql .= " WHERE `{$table_name}`.`id` = ?";
								array_push($fields, $condition[0]);

							}elseif (count($condition)==2) {
								$col = $condition[0];
								$val = $condition[1];
								array_push($fields, $val);
								$this->_sql .= " WHERE `{$table_name}`.`$col` = ?";
							}elseif (count($condition)==3) {
								$col = $condition[0];
								$operator = $condition[1];
								$val = $condition[2];
								array_push($fields, $val);
								$this->_sql .= " WHERE `{$table_name}`.`$col` $operator ?";
							}
						}else{
							if (count($condition)==1) {
								$this->_sql .= " AND `{$table_name}`.`id` = ?";
								array_push($fields, $condition[0]);
							}elseif (count($condition)==2) {
								$col = $condition[0];
								$val = $condition[1];
								array_push($fields, $val);
								$this->_sql .= " AND `{$table_name}`.`$col` = ?";
							}elseif (count($condition)==3) {
								$col = $condition[0];
								$operator = $condition[1];
								$val = $condition[2];
								array_push($fields, $val);
								$this->_sql .= " AND `{$table_name}`.`$col` $operator ?";
							}
						}

						$x++;
					}

				}else{
					// if Where is just 2 or 3 parameters and not an array of where parameters
					if (count($where)==2){
						$col = $where[0];
						$val = $where[1];
						array_push($fields, $val);
						$this->_sql = "DELETE FROM `{$table_name}` WHERE `{$table_name}`.`$col` = ?";
					}elseif (count($where)==3){
						$col = $where[0];
						$operator = $where[1];
						$val = $where[2];
						array_push($fields, $val);
						$this->_sql = "DELETE FROM `{$table_name}` WHERE `{$table_name}`.`$col` $operator ?";
					}
				}
				

			}

		// if id or where parameter exists
		$this->_query = $this->_connect->prepare($this->_sql);
		$this->_query->execute(array_values($fields));
		}
		$this->_table_name = $table_name;
		$this->_bindValues = array_values($fields);
		return $this;
	}//end of Delete


	public function query($sql,$params = [])
	{
		$this->_sql = $sql;
		$this->_query = $this->_connect->prepare($this->_sql);
		$this->_query->execute($params);
		return $this->_query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function table($table_name)
	{
		$this->_table_name = $table_name;
		return $this;
	}

	public function select($columns)
	{
		$this->_select_columns = $columns;
		return $this;
	}

	public function limit($limit, $offset=null)
	{
		if ($offset ==null ) {
			$this->_sql .= " LIMIT {$limit}";
		}else{
			$this->_sql .= " LIMIT {$limit} OFFSET {$offset}";
		}
		return $this;
	}

	public function get()
	{
		//save where and or where statements
		$original_sql = $this->_sql;

		$sql = "SELECT ";
		if ($this->_select_columns =='') {
			$sql .= "*";
		}else{
			$columns = explode(',', $this->_select_columns);

			foreach ($columns as $key => $column) {
				$columns[$key] = trim($column);
			}
			
			$columns = implode('`, `', $columns);
			$sql .="`{$columns}`";
		}

		$sql .= " FROM `{$this->_table_name}`".$original_sql;

		$this->_sql = $sql;


		$this->_query = $this->_connect->prepare($this->_sql);
		$this->_query->execute($this->_bindValues);

		// reset some properites to default
		$this->_whereCount = 0;
		$this->_bindValues = [];
		$this->_table_name = '';
		$_select_columns = '';
		// $_fetch_result = [];

		// $this->_fetch_result = $this->_query->fetchAll(PDO::FETCH_ASSOC);
		// return $this->_fetch_result;
		return $this->_query->fetchAll(PDO::FETCH_ASSOC);
	}


}//end of class
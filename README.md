# Welcome to Marei's DB class V 1.0
MareiDB class is a simple query builder class in PHP to increase your productivity, you can't imagine how much time you're gonna save if you're using this class! .
## Features
* Totally Secured :
This DB class uses PDO prepared statements to provide high levels of protection against SQL Injection attacks
* Easy Usage :
The syntax is really simple, and there are many ways to do the same query, so you can use the way you like ;)
* Well Documented :
Everything you wanna know about this class is here and organized very well, so you can find it easily.

## Usage
After downloading the class from [here](https://raw.githubusercontent.com/mareimorsy/DB/master/DB.php) save it into your root directory and then open it to adjust the basic configurations for your DB connection like host, database name, DB username and DB password. And also you can easily define your current development environment to `development` or `production`.
```php
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
```
To use the class, just include it into your project files like this
```php
include 'DB.php';
```
Then you have to instantiate the class like this
```php
$db = DB::getInstance();
```
Now, `$db` object is a new instance of DB class, we're gonna use this object to deal with our database, and you can create many objects as you want (don't worry about connections because i'm using Singleton design pattern so whenever you create new objects it returns the same connection) . 
### Insert values to a table
use the `insert()` method to insert values to a table, and it takes 2 parameters : the first one is `$table_name` and the second one is an associative array `$fields[]` so the key of that array is the column name in the table and the value of that array is the value that you wanna insert at that column.
```php
$db->insert('mytable',
	[
		'first_name' => 'Marei',
		'last_name' => 'Morsy',
		'age'	=> 22
	]);
```
To see the SQL query that have executed, use the `getSQL()` Method like this:
```php
echo $db->getSQL();
```
Output :
```sql
INSERT INTO `mytable` (`first_name`, `last_name`, `age`) VALUES (?, ?, ?)
```
#### Get the last ID inserted : 
You can get the last ID inserted using `lastId()` method, or you can get the return of `insert()` method like this : 
```php
$lastID = $db->insert('mytable',
	[
		'first_name' => 'Marei',
		'last_name' => 'Morsy',
		'age'	=> 22
	]);
echo $lastID;
```
And here is how to use `lastId()` after using `update()` method : 
```php
echo $db->lastId();
```
### Update table values
To update the table use `update()` method it holds 3 parameters : the first one is the table name, the second one is an associative array of the table values that you wanna update and the third parameter is optional, you can use it to state the update condition like WHERE clause in SQL.
DB class provides so many ways to do the same queries for example : the third parameter in `update()` method you can do one of the following methods : 
####Passing the id
You can pass the `$id` as a third parameter and DB class will understand that there's a field in the table called `id` and you wanna update the record that its id is the value of `$id` like this : 
```php
$db->update('mytable',
	[
		'first_name' => 'Mohammed',
		'last_name' => 'Gharib',
		'age'	=> 24
	],1);
```
SQL Query :
```sql
UPDATE `mytable` SET `first_name` = ?, `last_name` = ?, `age` = ? WHERE `mytable`.`id` = ?
```
but, what if the column name was not id?
####Passing the column name and value
you can pass an array of two items to `update` method as a third parameter : the first item in the array is the column name and the second item is the column value. The `update()` method in DB class will understand that you wanna update the table where the column name is equal to the value. Like this : 
```php
$db->update('mytable',
	[
		'first_name' => 'Ahmed',
		'last_name' => 'Hendy',
		'age'	=> 23
	],['id',1]);
```
SQL Query :
```sql
UPDATE `mytable` SET `first_name` = ?, `last_name` = ?, `age` = ? WHERE `mytable`.`id` = ?
```
but, what if we need to use another operator?
####Passing column name, operator and value
You can pass an array of three items to `update()` method as a third parameter.The first item of the array is the column name as string, the second one is the operator as a string and the third item is the value, like this :
```php
$db->update('mytable',
	[ 
		'first_name' => 'Zizo',
		'last_name' => 'Atia',
		'age'	=> 23
	],['age','>',22]);
```
SQL Query :
```sql
UPDATE `mytable` SET `first_name` = ?, `last_name` = ?, `age` = ? WHERE `mytable`.`age` > ?
```
you can also do the same query by only 2 items in the array like this :
```php
$db->update('mytable',
	[ 
		'first_name' => 'Ahmed',
		'last_name' => 'Mansour',
		'age'	=> 27
	],['age >= ',22]);
```
SQL Query :
```sql
UPDATE `mytable` SET `first_name` = ?, `last_name` = ?, `age` = ? WHERE age >= ?
```
but, what if we wanna add more than one where condition?
####passing more than one where condition
You can pass an array of arrays(nested array) as a third parameter to `update()` method, each array holds three items : the column name as a string, the operator and the value. The second and the third items are optional, so you can pass only the id as an array, or you can pass an array of two items : the column name and the value. And here is some examples of passing an array : 
##### Example 1 : 
```php
$db->update('mytable',
	[
		'first_name' => 'Omar',
		'last_name' => 'Saqr',
		'age'	=> 23
	],[ [1] ]);
```
SQL Query :
```sql
UPDATE `mytable` SET `first_name` = ?, `last_name` = ?, `age` = ? WHERE `mytable`.`id` = ?
```
##### Example 2 : 
```php
$db->update('mytable',
	[
		'first_name' => 'Ahmed',
		'last_name' => 'Helmy',
		'age'	=> 21
	],[ ['age',18], [1] ]);
```
SQL Query :
```sql
UPDATE `mytable` SET `first_name` = ?, `last_name` = ?, `age` = ? WHERE `mytable`.`age` = ? AND `mytable`.`id` = ?
```
##### Example 3 : 
```php
$db->update('mytable',
	[
		'first_name' => 'Ahmed',
		'last_name' => 'Ashraf',
		'age'	=> 21
	],[ ['age','>=', 18], [1] ]);
```
SQL Query :
```sql
UPDATE `mytable` SET `first_name` = ?, `last_name` = ?, `age` = ? WHERE `mytable`.`age` >= ? AND `mytable`.`id` = ?
```
Or you can do `[ ['age >= ', 18], [1] ]` to get the same result.
### Another way to update using `where()` method
`where()` method holds three parameters the second and the third are optional, if you passed only one parameter, the `where()` method will understand that there's a field called id and you wanna update the table where its id equals to that parameter like this : 
```php
$db->update('mytable',
	[
		'first_name' => 'Ashraf',
		'last_name' => 'Hefny',
		'age'	=> 28
	])->where(1)->exec();
```
SQL Query :
```sql
UPDATE `mytable` SET `first_name` = ?, `last_name` = ?, `age` = ? WHERE `mytable`.`id` = ?
```
We use `exec()` method to execute the query, that means you can use `getSQL()` method to check the query before you execute it without `exec()`.
You can use more than one `where()` method the same way like this :
```php
$db->update('mytable',
	[
		'first_name' => 'Osama',
		'last_name' => 'El-Zero',
		'age'	=> 30
	])->where(1)->where('first_name','Ashraf')->exec();
```
SQL Query :
```sql
UPDATE `mytable` SET `first_name` = ?, `last_name` = ?, `age` = ? WHERE `mytable`.`id` = ? AND `mytable`.`first_name` = ?
```
As you see, if you provide the where method with 2 parameters it will understand that you wanna update the table where the column name is the first parameter where it is equal to the value of the second parameter. And also if you noticed that the second where becomes 'AND' in the query.
```php
$db->update('mytable',
	[
		'first_name' => 'Ali',
		'last_name' => 'Hamdy',
		'age'	=> 30
	])->where(1)->where('age','>',20)->exec();
```
SQL Query :
```sql
UPDATE `mytable` SET `first_name` = ?, `last_name` = ?, `age` = ? WHERE `mytable`.`id` = ? AND `mytable`.`age` > ?
```
Now what if we wanted to add OR to our where clause?
###How to use `orWhere()` method?
`orWhere()` acts exactly like `where()` method and it takes the same parameters it's like 'OR' in SQL and you can use both methods together like this : 
```php
$db->update('mytable',
	[
		'first_name' => 'Muhammad',
		'last_name' => 'Mustafa',
		'age'	=> 21
	])->where('age','<=',20)->orWhere(1)->exec();
```
SQL Query :
```sql
UPDATE `mytable` SET `first_name` = ?, `last_name` = ?, `age` = ? WHERE `mytable`.`age` <= ? OR `mytable`.`id` = ?
```
And also you can pass an array of where clauses to `where()` or `orWhere()` method like this :
```php
->where([ ['first_name', 'Marei'], ['age >=', 18], [1] ])->exec();
```
SQL would be like this :
```sql
WHERE `first_name` = ? AND age >= ? AND id = ?
```
You can also use a combination of `where()` and `orWhere()` methods whith single caluse or with a group of where clauses like this :
```php
->where([ ['first_name', 'Marei'], ['age >=', 18]])->where(1)->orWhere([ [5], ['last_name', 'Morsy'] ])->exec();
```
SQL would be like this :
```sql
WHERE `first_name` = ? AND age >= ? AND `id` = ? OR `id` = ? Or `last_name` = ?
```
As you notice that you can use `where()` and `orWhere()` not only with `upadte()` method, but also with other query methods such as `delete()`, `update()` and `table()`.
###Delete values from table
use `delete()` method to delete rows from table, it holds 2 parameters, the first one is table name and the second one is optional, it acts exactly like the third parameter in `update()` method so, you can pass only the id as integer value, you can pass an array of the field name and the value, you can pass an array of the field name and parameter and value, you can pass an array of arrays of where clauses. And here are some examples of how to use `delete()` method : 
####Example 1 : 
```php
$db->delete('mytable',1);
```
SQL Query :
```sql
DELETE FROM `mytable` WHERE `mytable`.`id` = ?
```
####Example 2 : 
```php
$db->delete('mytable', ['first_name', 'Marei']);
```
SQL Query :
```sql
DELETE FROM `mytable` WHERE `mytable`.`first_name` = ?
```
####Example 3 : 
```php
$db->delete('mytable', ['age', '<', 18]);
```
SQL Query :
```sql
DELETE FROM `mytable` WHERE `mytable`.`age` < ?
```
####Example 4 : 
```php
$db->delete('mytable', [ ['age', '<', 18], [1] ]);
```
SQL Query :
```sql
DELETE FROM `mytable` WHERE `mytable`.`age` < ? AND `mytable`.`id` = ?
```
####Using `where()` with `Delete()` :
You can use `where()` and `orWhere()` with `delete()` like this : 
```php
$db->delete('mytable')->where(1)->exec();
```
SQL Query :
```sql
DELETE FROM `mytable` WHERE `mytable`.`id` = ?
```
To delete all rows from table : 
```php
$db->delete('mytable')->exec();
```
SQL Query :
```sql
DELETE FROM `mytable`
```
###Selection
Use `get()` method to retrieve data from table, but you have to define the table first using `table()` method, it takes the table name as only parameter like this : 
```php
$rows = $db->table('mytable')->get();
```
SQL Query :
```sql
SELECT * FROM `mytable`
```
It returns a collection called "MareiCollection" you can think of it like an array of objects, each object represents a row of the table, so you can use `foreach` to loop throw `$rows` array and get each row separately like this : 
 ```php
foreach ($rows as $row) {
	echo "$row->first_name <br>";
}
```
Output : 

```plain
Marei - Morsy 
Mohammed - Gharib 
Ahmed - Hendy 
```
and you can apply methods on "MareiCollection" like `first()`, `last()`, `toArray()`, `toJSON()`, `item()` and `list()` like this :
 ```php
$users = $db->table("users")->get()->toArray();
```
To get users as an array
```php
$users = $db->table("users")->get()->toJSON();
echo $users;
```
To get users as JSON and if you just echo the result, Marei DB class is smart enough to understand that you want to return a JSON, so you can get the same result in one single line like this :
```php
echo $db->table("users")->get();
```
To print users table as JSON
```php
echo $db->table("users")->get()->first();
```
To print the first row at users table as JSON
```php
echo $db->table("users")->get()->last();
```
To print the last row at users table as JSON
```php
echo $db->table("users")->get()->first()->first_name;
```
To print the first name of the first user, you can also do it like this :
```php
$first_user = $db->table("users")->get()->first();
echo $first_user->first_name;
```
Or you can do it like this :
```php
$first_user = $db->table("users")->get()->first()->toArray();
echo $first_user['first_name'];
```
If you want to get a specific row from `MareiCollection` use `item()` method and pass the item key like this :
```php
echo $db->table("users")->get()->item(0);
```
print the first row at users table as JSON

If you want to get a specific column of `MareiCollection`, like if you want firebase users' token as an array, use `list()` method and pass the column name like this :
```php
print_r( $db->table("users")->get()->list('token') );
```
print all tokens in the users table as an array
#### `Qget()` Method :
`Qget()` method works exactly like get method but without all `MareiCollecton` functionality like print the result as JSON and other methods like `toArray()`, `toJSON()`, `first()`, `last()` and `item()`. if you really care about performance `Qget()` is what you need to use. And you can use it like this :
```php
$users = $db->table("users")->Qget();
foreach ($users as $user) {
	echo $user->first_name;
}
```
To print the result of `Qget()` as JSON just use `json_encode($Qget_result);` like this :
```php
$users = $db->table("users")->Qget();
echo json_encode($users);
``` 
#### `select()` Method : 
If you want to select a specific column(s) use `select()` method, it holds column names as a string parameter separated by `,` like this : 
```php
$rows = $db->table('mytable')->select('first_name, last_name')->get();
```
SQL Query :
```sql
SELECT `first_name`, `last_name` FROM `mytable`
```
#### `limit()` Method : 
The `limit()` method makes it easy to code multi page results or pagination, and it is very useful on large tables. Returning a large number of records can impact on performance. It takes two parameters the first one is used to specify the number of records to return. And the second one is optional to pass the offset. And you can use it like this : 
```php
$rows = $db->table('mytable')->limit(10)->get();
```
SQL Query :
```sql
SELECT * FROM `mytable` LIMIT 10
```
It will return the first 10 records.
```php
$rows = $db->table('mytable')->limit(10, 20)->get();
```
SQL Query :
```sql
SELECT * FROM `mytable` LIMIT 10 OFFSET 20
```
It will return only 10 records, start on record 21 (OFFSET 20).
#### Easy pagination with `paginate()` method : 
Now after using `paginate()` method, pagination has never been easier!. You can use `paginate()` method with all selection methods like `table()` and `select()` instead of `get()`, it takes two parameters : the first one is page number starting from 1 as integer and the second one is used to specify the number of records to return `paginate($page, $limit)` and you can use it exactly like `get()` method and here is an example of how you can use it : 
```php
$rows = $db->table('mytable')->paginate(2, 5);
```
That means we want to return only 5 records from the second page and it will return only 5 records, start on record 6 up to 10. To get more information about what is going on behind the scenes, use `PaginationInfo()` method for more details like this: 
```php
print_r( $db->paginationInfo() );
```
Output : 
```plain
Array
(
    [previousPage] => 1
    [currentPage] => 2
    [nextPage] => 3
    [lastPage] => 5
)
```
It will return an associative array of useful information you might need to know like the current, previous, next and last page. And if there's no previous or next page its value would be null.
#### `Qpaginate()` Method :
`Qpaginate()` method works exactly like `paginate()` method but without all `MareiCollecton` functionality like print the result as JSON and other methods like `toArray()`, `toJSON()`, `first()`, `last()` and `item()`. if you really care about performance `Qget()` is what you need to use. And you can use it like this :
```php
$rows = $db->table('mytable')->paginate(2, 5);
```
####Using `where()` and `orWhere()` with selection : 
You can use `where()` or `orWhere()` methods with selection like this : 
```php
$rows = $db->table('mytable')->where(1)->get();
```
SQL Query :
```sql
SELECT * FROM `mytable` WHERE `mytable`.`id` = ?
```
####Order the result set
you can use `orderBy()` method to order the result set by a column name, `orderBy($column_name, $order)` takes two parameters, the first one is the column name as string and the second one is optional and it takes only two values `ASC` which is the default value to order the result set by asccending order, or `DESC` to order the result set by descending order like this :
```php
$rows = $db->table('mytable')->orderBy('id', 'DESC')->get();
```
To order the result set in descending order by id.
And you can use more than orderBy together like this :
```php
$rows = $db->table('mytable')
           ->orderBy('id', 'DESC')
	   ->orderBy('age', 'ASC')
	   ->get();
```
and ofcourse as you use `orderBy()` with `get()`, you can also use it with `paginate()`, `limit()`, `Qget()` and `Qpaginate()` methods.
####Count selected rows
Use `getCount()` method to get the total number of rows returned of the last query. and you can use it after selection like this : 
```php
echo $db->getCount();
```
###Using Raw Queries : 
I bet that you asked what if I wanted to execute more complected queries?
that's why I created `query()` method, it holds three parameters the first one is SQL query as a string, and the second one is optional and it's for the values that you wanna pass to query as an array. And here is how you can use `query()` method : 
```php
$sql = "SELECT * FROM mytable WHERE id = ?";
$rows = $db->query($sql, [1]);
```
SQL Query :
```sql
SELECT * FROM mytable WHERE id = 1
```
if you want to get rid of all `MareiCollection` functionally just pass true as a third parameter like this :
```php
$sql = "SELECT * FROM mytable WHERE id = ?";
$rows = $db->query($sql, [1], true);
```

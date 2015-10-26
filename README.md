# SimplePDOHelper
PDOHelper class is easy to use and reduce size of biolerplate queries.
It include following main functions `insert`, `udpate`, `delete`, `select`, `beginTransaction`, `endTransaction` and more.

#Why PDOHelper?

* No need to write queries
* No query syntax errors
* Easy and Simple
* Secured Prepared statements
* Can use direct values or bind them
* Can set PDO data type of value and its length


#How use?
###Create class of PDOHelper Class

````php
$db_connection = new PDO('mysql:host='.DB_HOST.';dbname='.DATABASE, DB_USER, DB_PASS,$pdo_options);
$pdo = new PDOHelper($db_connection);
```


###Insert Row
simple insert query with two parameters
```php
$pdo->insert($table_name, $tableData);
```
```php
//values of $bind_values will bind in prepared statment with '?'
// column_name    => "column_value"
//"username"      => "itboy",
$bind_values = array(
    "username"      => "itboy",                             //simple value
    "email"         => "my@mail.com",                      //simple value
);
$tableData = array(
    "?" => $bind_values,          //values of $bind_values will bind in prepared statment with '?'                          
    "time" => "NOW()"             //this value will include direct into query
);
```  

- **Don't worry about parameters order PDOHelper will handle it just give right value to right column.**  
- '*?*' array values will bind in prepared statement
- above `$tableData` variable is same as below query  
 ```php
INSERT INTO TABLE_NAME (username, email, time)` VALUES(?, ?, NOW())
```

** Now i don't need to explain it further if you have basic knowledge of prepared statements then you should know where is magic. **

####More complex insert query with PDO data_type and length
```php
$bind_values = array(
    "username"      => array("itboy", PDO::PARAM_STR, 5),   // value with data_type and length
    "user_active"   => array(1, PDO::PARAM_INT)             // this column is type of BIT(1) and will bind with int type
);
$tableData = array(
    "?"         => $bind_values,              //values of $bind_values will bind in prepared statment                          
    "time"      => "NOW()"                   //this value will include direct into query
    "email"     => "'my@mail.com'"          //this value will include direct into query
);
```
1. first i put email column value in `$tableData` array instead `$bind_values` now it will not bind and directl entered into email column
   this will work but this is not good method it can lead to sql injection google it for more information.
2. I changed `username` value to `array (column_value, PDO::DATA_TYPE, length)`. You can use PDO data types and set length of value
3. `"user_active"` is bit type column in DB if you direct insert value into it you will get exception so i set its type to `PDO::PARAM_INT`.

4. This $tableData is equal to following query  
```php
$query = INSERT INTO TABLE_NAME (username, user_active, time, email)` VALUES (?, ?, NOW(), 'my@mail.com');
```

###Update Row
Update function have one additional variable `$condition` and it is nullable.  
`$tableData` and `$bind_values` includes column and values same as in insert.  
```php
$pdo->update($table_name,$updateValues, $condition);
```
OR `$condition` null if you want to update all columns without any additional clause
```php
$pdo->update($table_name,$updateValues, null);
```
```php
$bind_values = array(
    'email' => 'my@mail.com'
);
$tableData = array(
    "username" => "'itboy'", // you can insert direct string but use bind value instead
    "?" => $bind_values     //bind values
);
$condition = array(
    "where" => "username = ? and email = ? LIMIT 1", //in where you can add addition clause as well like having or order
    "?" => array("itboy", "my@mail.com")
);
```
* `$condition` array just contain two elements first `"where"` and second `"?"`. Don't set `"?"`if there is no "?" in `"where"`.  
* You can also put `HAVING` and `ORDER` clause in `'where'` like i put `LIMIT 1`.  
* You cannot insert direct values as array element of `$condition` but you can put in `"where"` element like that
```php
$condition = array(
    "where" => "user_type = 1 and username = ? or email = 'my@mail.com' ",
    "?" => array("itboy", "my@mail.com")
););
```

###Delete Row
`$condition` variable is same as defined above  
```php
$deleteResult = $pdo->delete($table_name, $condition);
```

### Select
Nothing special in Select just make it little simple look at  
```php
$query = "SELECT * FROM user WHERE username=? and pass=?;
$params = array('itboy', 'mypass');
$result = $pdo->select($query, $params, PDO::FETCH_ASSOC)
```


Look at code for more info.

###Transactions
Transactions are simple i just wrapped the PDO transaction functions look at code.  
Functions  
```php
  $pdo->beginTransaction();
  $pdo->beginTransaction("UserRecordClass");
  $pdo->endTransaction();
  $pdo->beginTransaction();
  $pdo->commit();
  $pdo->rollback();
  ..
```

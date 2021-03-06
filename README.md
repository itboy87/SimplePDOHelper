# SimplePDOHelper
PDOHelper class is easy to use and reduce size of boilerplate code.
It include following main functions `insert`, `update`, `delete`, `select`, `beginTransaction`, `endTransaction` and more.

#Why PDOHelper?

* No need to write queries
* No query syntax errors
* Easy and Simple
* Secured Prepared statements
* Can use direct values or bind them
* Can set PDO data type of value and its length
* Chain method calling

#New Feature: 'TableData'
 * Now more easy and simple just write what need  
 * New functions `insertTD`, `updateTD`, `deleteTD`.
 * Idea about TableData comes into my mind from Android's ContentValues
 * Chain method supported
 * *putDirectParam* directly insert value into database
 * *putBindParam* bind the value with prepared statement
 
````php
//@Syntax
//putDirectParam(column_name, column_value, PDO_DATA_TYPE, LENGTH);
//putBindParam same as putDirectParam
//3 and 4 parameters are option

 $TD = new TableData($table_name);
 $TD->putDirectParam("time", "NOW()")
     ->putBindParam("username", "itboy87")
     ->putBindParam("email", "itboy@mail.com")
     ->putBindParam("password", "myPass", PDO::PARAM_STR, 5)
     ->putBindParam("user_active", 0, PDO::PARAM_INT);
 
 //condition
 //insertTD don't need condition
 $TD->setCondition("WHERE username = ? && password = ?")
     ->putConditionBindValue(1, "itboy87")
     ->putConditionBindValue(2, "myPass");
     
 //insert, update and delete
  $pdo->insertTD($TD);   
  $pdo->updateTD($TD));  
  $pdo->deleteTD($TD));
  
```

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
//values of $bind_values will bind in prepared statement with '?'
// column_name    => "column_value"
//"username"      => "itboy",
$bind_values = array(
    "username"      => "itboy",                             //simple value
    "email"         => "my@mail.com",                      //simple value
);
$tableData = array(
    "?" => $bind_values,          //values of $bind_values will bind in prepared statement with '?'                          
    "time" => "NOW()"             //this value will include direct into query
);
```  

**Don't worry about parameters order PDOHelper will handle it just give right value to right column.**   
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
    "?"         => $bind_values,              //values of $bind_values will bind in prepared statement                          
    "time"      => "NOW()"                   //this value will include direct into query
    "email"     => "'my@mail.com'"          //this value will include direct into query
);
```
1. first i put email column value in `$tableData` array instead `$bind_values` now it will not bind and directly entered into email column
   this will work but this is not good method it can lead to sql injection google it for more information.
2. I changed `username` value to `array (column_value, PDO::DATA_TYPE, length)`. You can use PDO data types and set length of value
3. `"user_active"` is bit type column in DB if you direct insert value into it you will get exception so i set its type to `PDO::PARAM_INT`.

4. This $tableData is equal to following query  
```php
$query = INSERT INTO TABLE_NAME (username, user_active, time, email)` VALUES (?, ?, NOW(), 'my@mail.com');
```

###Update Row
Update function have one additional variable `$condition` and it is NullAble.
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
    "condition" => "WHERE username = ? and email = ? LIMIT 1", //in "condition" you can add addition clause as well like having or order
    "?" => array("itboy", "my@mail.com")
);
```
* `$condition` array just contain two elements first `"condition"` and second `"?"`. Don't set `"?"`if there is no "?" in `"condition"`.
* You can also put `HAVING` and `ORDER` clause in `'condition'` like i put `LIMIT 1`.
* You cannot insert direct values as array element of `$condition` but you can put in `"condition"` element like that
```php
$condition = array(
    "condition" => "WHERE user_type = 1 and username = ? or email = 'my@mail.com' ",
    "?" => array("itboy", "my@mail.com")
);
```
* You can also set PDO data type and length of `$condition` bind values
```php
//without any key but if you provide them these will skipped
$condition_bind = array(
    array("itboy", PDO::PARAM_STR, 6),
    "my@mail.com"
);
$condition = array(
    "condition" => "WHERE user_type = 1 and username = ? or email = 'my@mail.com' ",
    "?" => $condition_bind
);
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
$params = array('itboy', 'myPass');
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

### Full Example
```php
//Create PDOHelper object
$db_connection = new PDO('mysql:host='.DB_HOST.';dbname='.DATABASE, DB_USER, DB_PASS,$pdo_options); 
$pdo = new PDOHelper($db_connection);

//table name
$table_name = "test";

//create table data variable
$bind_values = array(
    "username" => "itboy",                                //simple value
    "email" => "my@mail.com",                            //simple value
    "password" => array("myPass", PDO::PARAM_STR, 6),    //value to bind with type and length
    "user_active" => array(1, PDO::PARAM_INT)           // this column is type of BIT(1) and will bind with int type
);
$tableData = array(
    "?"             => $bind_values,
    "time"          => "NOW()",       //Direct value
    "user_type"     =>  1            //Direct value
);

//insert
$pdo->insert($table_name, $tableData);

//Create $condition variable for update and delete
//without any key but if you provide them these will skipped
$condition_bind = array(
    array("itboy", PDO::PARAM_STR, 6),
    "my@mail.com"
);
$condition = array(
    "condition" => "WHERE user_type = 1 and username = ? or email = 'my@mail.com' ",
    "?" => $condition_bind
);

//update
$pdo->$update($table_name, $tableData, $condition);

//delete
$pdo->$delete($table_name, $condition);

//Transaction
$pdo->beginTransaction("testOwner");  //owner string is optional

$outUpdate = $pdo->$update($table_name, $tableData, $condition);
$outDelete = $pdo->$delete($table_name, $condition);
$outInsert = $pdo->insert($table_name, $tableData);

// transaction will auto commit if success else it will rollback
$pdo->endTransaction($outUpdate && $outDelete && $outInsert);

```

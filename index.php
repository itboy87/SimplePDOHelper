<?php
/**
 * Created by PhpStorm.
 * User: itboy
 * Date: 10/25/2015
 * Time: 6:23 PM
 */

require_once "PDOHelper.php";

//Bind values @Syntax
//  $bind_values = array(
//      "column_name" => "column_value",
//      "column_name" => array("column_value"),
//      "column_name" => array("column_value", length)
//  )

//@Example
//if we pass value to column of type BIT without pdo type then exception will thrown

$table_name = "test";
$bind_values = array(
    "username" => "itboy", //simple value
    "email" => "my@mail.com", //simple value
    "password" => array("myPass", PDO::PARAM_STR, 6), //value to bind with type and length
    "user_active" => array(1, PDO::PARAM_INT) // this column is type of BIT(1) and will bind with int type
);

// '?' array of parameters to bind
$tableData = array(
    "?" => $bind_values,
    "time" => "NOW()"
);


/*
$db_connection = new PDO('mysql:host='.DB_HOST.';dbname='.DATABASE, DB_USER, DB_PASS,$pdo_options);
$pdo = new PDOHelper($db_connection);
*/
//PDOHelper object with PDO database connection instance
$pdo = new PDOHelper(DB::getInstance()); //getInstance return connection of PDO


echo "<h1>Queries</h1>";
echo "<p><i>\$pdo->insert(\$table_name, \$tableData)</i></p>";


$insertResult = $pdo->insert($table_name, $tableData);

$update_bind_params = array(
    'email' => 'my@mail.com'
);
$updateValues = array(
    "username" => "'itboy'", // you can insert direct string but use bind value instead
    "?" => $update_bind_params //bind values
);
$condition = array(
    "where" => "username = ? and email = ? LIMIT 1", //in where you can add addition clause as well like having or order
    "?" => array("itboy", "my@mail.com")
);


$updateResult = $pdo->update($table_name,$updateValues, $condition);
$deleteResult = $pdo->delete($table_name, $condition);

echo "<i>update_result: " . ($updateResult ? "TRUE" : "FALSE") . "</i><br>";
echo "<i>delete_result: " . ($deleteResult ? "TRUE" : "FALSE") . "</i>";
echo "<i>insert_result: " . ($insertResult ? "TRUE" : "FALSE") . "</i>";




echo "<h1>How Insert query work</h1>";

echo "<p style='font-weight: bold;'> 1- String of insert Query is created from \$tableData and simple values are also inserted with createPreparedInsertQuery function</p>";
$insertQuery = $pdo->createPreparedInsertQuery($table_name, $tableData);
echo "<p><i>" . $insertQuery . "</i></p>";

//output
//INSERT INTO test(username, email, password, user_active, time) VALUES(?, ?, ?, ?, NOW())


echo "<p style='font-weight: bold;'> 2- Array bind parameters are extracted from \$tableData</p>";
$bindParameters = $pdo->getQueryParamsToBind($tableData);
echo "<p><i>" . var_dump($bindParameters) . "</i></p>";

echo "<p style='font-weight: bold;'> 3- Last in insert method \$bindParameters are bind to query with bindParameters and executed check code for more info</p>";
echo "<p><i>a- </i></p>";

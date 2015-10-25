<?php
/**
 * Created by PhpStorm.
 * User: itboy
 * Date: 9/24/2015
 * Time: 9:56 AM
 */

class DB {

    private static $instance = NULL;

    private function __construct() {}

    public static function getInstance() {
        if (!isset(self::$instance)) {
            require_once ('/database/db_config.php');
            $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
            try{
                self::$instance = new PDO('mysql:host='.DB_HOST.';dbname='.DATABASE, DB_USER, DB_PASS,$pdo_options);
            }catch (PDOException $e){
                self::handle_sql_errors("database connection!",$e);
            }
        }

        return self::$instance;
    }

    public static function handle_sql_errors($query, $e)
    {
        $error["query"] = $query;
        $error["msg"] = $e->getMessage();
//        file_put_contents('error/PDO_DB_Errors.json',json_encode($error)."\r\n", FILE_APPEND);
        echo "<h4 style='color: #FF0000;'>Exception caught</h4>";
        var_dump($error);
        die;
    }

    private function __clone()
    {
    }
}
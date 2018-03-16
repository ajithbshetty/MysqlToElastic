<?php

/*
	Author: Ajith Shetty
*/

use Elasticsearch\ClientBuilder;

require_once("./config/config.php");

require_once("./config/db.php");

// Create mysql connection
$conn = new mysqli($server_name, $user_name, $password, $database);

// Building Elastic Search Client
require '../vendor/autoload.php';
$client = ClientBuilder::create()->build();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<pre>";

$tables_names = $conn->query("SELECT table_name FROM information_schema.tables where table_schema=schema()");
 
while($table = $tables_names->fetch_assoc()) {
    try{
        $table_name = $table['table_name'];
        $table_name = "Users";
        error_log("hello", 0);
        $qry = "select * from ".$table_name;
        $primary_key = NULL;
        $index_name = strtolower($table_name);
        $get_primary_key_qry = "SELECT column_name
                FROM information_schema.key_column_usage
                WHERE table_schema = schema()
                AND constraint_name = 'PRIMARY'
                AND table_name = '$table_name'";
        $result = $conn->query($get_primary_key_qry);
        while($row = $result->fetch_assoc()) {
            $primary_key = $row['column_name'];
        }

        $result = $conn->query($qry);

        if ($result->num_rows > 0) {
            // output data of each row
            $reccount = 0;
            while($row = $result->fetch_assoc()) {
                $id = "";
                if($primary_key !== NULL){
                    $id = $row[$primary_key];
                }
                $params = [
                    'index' => $index_name,
                    'type' => $index_name,
                    'id' => $id,
                    'body' => $row
                ];
                $response = $client->index($params);
                $reccount++;
            }

            echo "Data Migrated from MYSQL table `".$table_name."` To Elastic Search Index `".$index_name."`<br/><br/>";

            echo "Total number of Documents Inserted : ".$reccount."<br/><br/>";
        } 
        else {
            echo "No Records To importfrom MYSQL table `".$table_name."` To Elastic Search";
        }
    }
    catch(Exception $e){
        error_log($e, 0);
        echo "Something Went Wrong.! Check Error Logs for details.!";
    }
}
?>
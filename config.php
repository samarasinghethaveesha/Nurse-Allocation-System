<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nurse_allocation_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<?php

class Database{

public static $connection;

public static function setUpConnection(){

    if(!isset(Database::$connection)){
        Database::$connection = new mysqli("localhost","root","","nurse_allocation_system","3306");
    }
}

public static function iud($q){

    Database::setUpConnection();
    Database::$connection->query($q);

}

public static function search($q){

    Database::setUpConnection();
    $resultset = Database::$connection->query($q);
    return $resultset;

}

}

?>
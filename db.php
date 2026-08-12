<?php

/*
====================================================
DATABASE CONNECTION
====================================================
*/

$host = "localhost";
$username = "root";
$password = "";
$database = "academic_management";


/*
====================================================
CREATE CONNECTION
====================================================
*/

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);


/*
====================================================
CHECK CONNECTION
====================================================
*/

if ($conn->connect_error) {

    die(
        json_encode([
            "success" => false,
            "message" => "Database connection failed"
        ])
    );

}


/*
====================================================
SET UTF-8
====================================================
*/

$conn->set_charset("utf8mb4");

?>

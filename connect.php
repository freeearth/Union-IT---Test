<?php

$mysql_host = "localhost";
$mysql_port = 3306;
$mysql_db = "working_tasks";
$mysql_user = "root";
$mysql_password = "********";
global $mysqli;
$mysqli = new mysqli($mysql_host, $mysql_user, $mysql_password, $mysql_db);
if (mysqli_connect_errno()) { 
    printf("Подключение невозможно: %s\n", mysqli_connect_error()); 
    die(); 
} 

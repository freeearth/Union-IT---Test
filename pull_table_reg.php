<?php
/*
 * заполнения таблицы данными
 * 2009-09-10 до 2009-09-30
 */
require_once './connect.php';
//функция возвращает случайную дату в формате ГГГГ-ММ-ДД ЧЧ:ММ:СС, для заданного года и месяца
function generate_random_date ($year = "2009",$month = "09") {
    //день
    $day = rand(10,30);

    //час
    $hour = rand(0, 23);
    if ($hour<=9) {
        $hour = "0".$hour;
    }
    //минута
    $minute = rand(0, 59);
    if ($minute<=9) {
        $minute = "0".$minute;
    }
    //секунда
    $second = rand(0, 59);
    if ($second<=9) {
        $second = "0".$second;
    }

    //окончательная дата
    $datetime = $year."-".$month."-".$day." ".$hour.":".$minute.":".$second;
    return $datetime;
}

//генератор случайной строки
function generateRandomString($length = 10) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

global $mysqli;
$db_statement="INSERT INTO `regs` VALUES (?,?,?)";
for ($i = 0;$i<100000;$i++) {
    try {
        $stmt = $mysqli->prepare($db_statement);
        //пусть будет 100000 записей
        $stmt->bind_param("iss",$i,  generateRandomString(),generate_random_date());
        $stmt->execute();
        $stmt->close();
    }
    catch (Exception $e) {
        echo $e;
    }
}


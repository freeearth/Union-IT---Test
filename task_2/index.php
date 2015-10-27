<?php
require_once '../connect.php';
global $mysqli;
/* 
 * скрипт парсит access.log и записывает в бд Apache2
 * %h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"
 * дату, referer, число переходов
 */
try {
    //дата и время с лишней [ вначале
    //если выводим из архива, то tar -xf access.log.tar -O | awk '{print $4}'
    exec("awk '{print $4}' access.log",$datetime);
    //referer
    //tar -xf access.log.tar -O | awk -F\\\" '{print $4}'
    exec("awk -F\\\" '{print $4}' access.log",$referer);
    //requested_url - запрашиваемые ресурсы
    //tar -xf access.log.tar -O | awk -F\\\" '{print $7}'
    exec("awk '{print $7}' access.log",$requested_url);
    //url, переходы с которых не учитываюся
    $own_urls = array("passport.nikitaonline.ru","rzonline.ru");
    $access_data = array();
    foreach ($requested_url as $key=>$val) {
        if(strpos($val, "REFER_ID")!==false) {
            $buff = explode("/?",$val);
            //выделение GET переменной
            parse_str($buff[1]);
            //для всех, кроме own
            if ($referer[$key]!=="-"&&strpos($referer[$key],$own_urls[0])===false&&strpos($referer[$key],$own_urls[1])===false) {
                //текущая дата 
                $dt = DateTime::createFromFormat("d/M/Y:H:i:s", ltrim($datetime[$key],"["));
                $dt_i = $dt->format('Y-m-d');
                //если такой массив уже есть
                if (isset($access_data[$REFER_ID][$dt_i])) {
                    //ищем в массиве $access_data[$referer[$key]][$dt_i]['referer'] - есть ли уже такой referer
                    $key_referer = array_search($referer[$key],$access_data[$REFER_ID][$dt_i]['referer']);
                    //если такого referer нет
                    if ($key_referer == false) {
                        //referer
                        $access_data[$REFER_ID][$dt_i]['referer'][$key] = $referer[$key];
                        //начальное число переходов
                        $access_data[$REFER_ID][$dt_i]['count'][$key] = 1;
                    }
                    //если такой ключ уже есть
                    if ($key_referer !== false) {
                        //увеличиваем счетчик на 1
                        $access_data[$REFER_ID][$dt_i]['count'][$key_referer]++;
                    }
                }
                //если еще нет такого массива
                if (!isset($access_data[$REFER_ID][$dt_i])) {
                    //referer
                    $access_data[$REFER_ID][$dt_i]['referer'][$key] = $referer[$key];
                    //начальное число переходов
                    $access_data[$REFER_ID][$dt_i]['count'][$key] = 1;
                }
            }
        }
    }
    
    //запись в бд
    //выражение для вставки записей
    $db_statement_insert="INSERT INTO `referers_daily` VALUES (?,?,?,?)";
    //выражение для проверки 
    $db_statement_check = "SELECT COUNT(`count_`),`count_` FROM `referers_daily` WHERE `refer_id` = ? AND `referer` = ? AND `date_` = ?";
    //выражение для  обновления числа переходов, если за сегодняшний день уже есть запись 
    $db_statement_update = "UPDATE  `referers_daily` SET `count_`=? WHERE `refer_id` = ? AND `referer` = ? AND `date_` = ?";
    foreach($access_data as $key => $val) {
    //$key  - REFER_ID
        foreach ($val as $key_l=>$val_l) {
            //$key_l  - date
            foreach ($access_data[$key][$key_l]['referer'] as $key_n=>$val_n) {
                //$val_n - referer
                $count_ref = $access_data[$key][$key_l]['count'][$key_n];
                /*
                 * проверка - есть ли запись для REFER_ID, refer за дату ($key_l)
                 * если есть, то обновляем значение count - прибавляем текущее count ($count_ref)
                 * предполагается, что скрипт запускается раз в день и при ротации логов они очищаются
                 */
                $stmt = $mysqli->prepare($db_statement_check);
                if ($stmt) {
                    $stmt->bind_param("iss",$key/*REFER_ID*/,  $val_n/*referer*/,$key_l/*date*/);
                    //echo "id: ".$key." referer:".$val_n." date:".$key_l."";
                    $stmt->execute();
                    $stmt->bind_result($check_count,$count);
                    //если уже есть такая запись
                    if ($stmt->field_count) {
                        $stmt->fetch();
                        //echo " count:".$check_count."<br>";
                        $stmt->close();
                        //если такая запись есть
                        if ($check_count) {
                            //новое число переходов
                            $new_count = $count + $count_ref;
                            $stmt = $mysqli->prepare($db_statement_update);
                            if ($stmt) {
                                $stmt->bind_param("iiss",$new_count/*new count*/,$key/*REFER_ID*/,  $val_n/*referer*/,$key_l/*date*/);
                                $stmt->execute();
                                $stmt->close();
                            }
                            if (!$stmt) {
                                print_r($mysqli->error_list);die;
                            }
                        }
                        //если такой записи нет
                        if (!$check_count) {
                            if ($stmt) {
                                $stmt = $mysqli->prepare($db_statement_insert);
                                $stmt->bind_param("isis",$key/*REFER_ID*/,  $val_n/*referer*/,$count_ref/*referer count*/,$key_l/*date*/);
                                $stmt->execute();
                                $stmt->close();
                            }
                            if (!$stmt) {
                                print_r($mysqli->error_list);die;
                            }
                        }
                    }
                }
                if (!$stmt) {
                    print_r($mysqli->error_list);die;
                }
            }
        }
    }
}
catch (Exception $E) {
    echo $E;
}


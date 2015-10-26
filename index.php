<?php
    try {
        require_once './connect.php';
        global $mysqli;
        $date = new DateTime("2009-09-21 00:00:00");
        $i=0;
        while ($date->format('Y-m-d H:i:s')!=="2009-09-23 23:50:00") {
            $db_statement = "SELECT COUNT(*) AS numbers FROM `regs` WHERE `reg_date` BETWEEN ? AND ? +INTERVAL 10 MINUTE";
            $stmt = $mysqli->prepare($db_statement);
            //if (!$stmt) {
                //echo "error!<br>";
                //print_r($mysqli->error_list);die;
            //}
            if ($stmt) {
                //БИНДИМ 2 СТРОКОВЫХ ПАРАМЕТРА(s) -- МЕТЕОПАРАМЕТР И ДАТУ НАЧАЛА ИЗМЕРЕНИЯ
                $stmt->bind_param("ss",$date->format('Y-m-d H:i:s'),$date->format('Y-m-d H:i:s'));
                $stmt->execute();
                $stmt->bind_result($result_param);
                if ($stmt->field_count) {
                    $stmt->fetch();
                    $result['count'][$i]=$result_param;
                    $result['time_interval'][$i]=$date->format('Y-m-d H:i:s');
                    $date->add(DateInterval::createFromDateString("10 minute"));
                    $result['time_interval'][$i].="----".$date->format('Y-m-d H:i:s');
                }
            }
            $i++;
        }
    }
    catch (Exception $E) {
        echo $E;
    }
?>
<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <h3>Число регистраций за каждые 10 минут с 2009-09-21 до 2009-09-23</h3>
    <body>
        <table border="1" align="left" cellspacing="0">
            <th >Временной интервал</th><th>Число записей</th>
            <?php foreach ($result['time_interval'] as $key=>$val) { ?>    
            <tr>
                <td><?=$val?></td><td><?=$result['count'][$key]?></td>
            </tr>
            <?php }?>
        </table>
    </body>
</html>
